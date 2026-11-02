<?php

declare(strict_types=1);

namespace App\Models;

use Hleb\Base\Model;
use Hleb\Static\DB;

/**
 * История версий фактов/статей.
 *
 * Хранит снимок (контент + slug + ключевые метаданные) ДО каждой правки,
 * позволяя смотреть историю и откатываться к любой версии.
 *
 * Экономия места: полными (item_content) хранятся только версия №1 (фундамент)
 * и последние N версий статьи (задаётся config('general','history_full_versions')).
 * Все более старые версии конвертируются в накопительный diff относительно
 * версии №1: item_content обнуляется, а в item_content_diff (сжатый blob)
 * кладётся набор хунков, восстанавливающих контент этой версии из контента v1.
 * При чтении такой версии контент восстанавливается: v1 + apply(diff).
 */
class VersionModel extends Model
{
    // Число последних версий каждой статьи, хранимых ПОЛНОСТЬЮ (из конфига).
    public static function fullVersionsCount(): int
    {
        $n = (int)config('general', 'history_full_versions');

        return $n > 0 ? $n : 10;
    }

    // Создать снимок текущего состояния факта (новой версией).
    // Вызывается ПЕРЕД обновлением items (внутри той же транзакции).
    public static function snapshot(array $item, ?string $comment = null, ?int $user_id = null): void
    {
        $item_id = (int)$item['item_id'];

        $user_id = $user_id ?? self::container()->user()->id();

        $next = self::maxVersion($item_id) + 1;

        DB::run(
            "INSERT INTO item_history
                (item_id, version, item_title, item_slug, item_type, item_note,
                 item_content, item_content_diff, is_full_content, item_thumb_img,
                 facet_ids, source_url, comment, user_id)
             VALUES
                (:item_id, :version, :title, :slug, :type, :note,
                 :content, NULL, 1, :thumb,
                 :facets, :source, :comment, :user_id)",
            [
                'item_id'  => $item_id,
                'version'  => $next,
                'title'    => (string)($item['item_title'] ?? ''),
                'slug'     => (string)($item['item_slug'] ?? ''),
                'type'     => (string)($item['item_type'] ?? 'fact'),
                'note'     => ($item['item_note'] ?? null) !== '' ? (string)$item['item_note'] : null,
                'content'  => (string)($item['item_content'] ?? ''),
                'thumb'    => ($item['item_thumb_img'] ?? null) !== '' ? (string)$item['item_thumb_img'] : null,
                'facets'   => self::facetIdsJson($item),
                'source'   => ($item['item_source_url'] ?? null) !== '' ? (string)$item['item_source_url'] : null,
                'comment'  => $comment !== '' && $comment !== null ? $comment : null,
                'user_id'  => $user_id,
            ]
        );

        // Версии, выпавшие из окна последних N, конвертируем в накопительный diff.
        self::pruneToDiffs($item_id, $next);
    }

    // Максимальный номер версии для факта (0, если версий нет)
    public static function maxVersion(int $item_id): int
    {
        $row = DB::run(
            "SELECT COALESCE(MAX(version), 0) v FROM item_history WHERE item_id = :id",
            ['id' => $item_id]
        )->fetch();

        return (int)($row['v'] ?? 0);
    }

    // Список версий факта: новые сверху.
    // Не тянет тяжёлые поля контента/diff — только метаданные для списка.
    public static function list(int $item_id, int $limit = 50): false|array
    {
        return DB::run(
            "SELECT h.version_id, h.item_id, h.version, h.item_title, h.item_slug,
                    h.item_type, h.item_note, h.item_thumb_img, h.facet_ids,
                    h.source_url, h.comment, h.user_id, h.created_at,
                    u.login AS user_login
             FROM item_history h
             LEFT JOIN users u ON u.id = h.user_id
             WHERE h.item_id = :id
             ORDER BY h.version DESC
             LIMIT :limit",
            ['id' => $item_id, 'limit' => $limit]
        )->fetchAll();
    }

    // Одна версия по номеру (или false).
    // Контент diff-версий восстанавливается из накопительного diff.
    public static function get(int $item_id, int $version): false|array
    {
        $row = DB::run(
            "SELECT * FROM item_history
             WHERE item_id = :id AND version = :version
             LIMIT 1",
            ['id' => $item_id, 'version' => $version]
        )->fetch();

        if (!$row) {
            return false;
        }

        return self::hydrateContent($row);
    }

    // Есть ли вообще история у факта
    public static function hasHistory(int $item_id): bool
    {
        return self::maxVersion($item_id) > 0;
    }

    // Построчный diff содержимого двух версий.
    // Возвращает [['op' => '='|'-'|'+', 'text' => string], ...] + метаданные версий.
    public static function compare(int $item_id, int $fromVersion, int $toVersion): array
    {
        $from = self::get($item_id, $fromVersion);
        $to   = self::get($item_id, $toVersion);

        $oldText = (string)($from['item_content'] ?? '');
        $newText = (string)($to['item_content'] ?? '');

        $diff = \LineDiff::diff($oldText, $newText);

        return [
            'diff' => $diff,
            'from' => $from,
            'to'   => $to,
        ];
    }

    // Категории факта из снимка в виде JSON-массива id
    protected static function facetIdsJson(array $item): ?string
    {
        $ids = [];
        if (!empty($item['facet_list'])) {
            $chunks = array_chunk(preg_split('/(@)/', (string)$item['facet_list']), 4);
            foreach ($chunks as $chunk) {
                $id = (int)($chunk[0] ?? 0);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        return $ids ? json_encode($ids, JSON_UNESCAPED_UNICODE) : null;
    }

    /**
     * Конвертирует версии, выпавшие из окна последних N, в накопительный diff
     * относительно версии №1 (которая хранится полной всегда).
     *
     * @param int $item_id        id статьи
     * @param int $newMaxVersion  номер только что добавленной (самой новой) версии
     */
    protected static function pruneToDiffs(int $item_id, int $newMaxVersion): void
    {
        $k = self::fullVersionsCount();

        // Последние $k версий (с учётом новой) остаются полными.
        // Старше окна и > 1 — кандидаты на конвертацию в diff.
        $threshold = $newMaxVersion - $k;
        if ($threshold < 2) {
            return;
        }

        // База — контент версии №1 (всегда полная).
        $base = DB::run(
            "SELECT item_content FROM item_history WHERE item_id = :id AND version = 1 LIMIT 1",
            ['id' => $item_id]
        )->fetch();
        if (!$base) {
            return;
        }
        $baseText = (string)($base['item_content'] ?? '');

        $rows = DB::run(
            "SELECT version_id, version, item_content, is_full_content
             FROM item_history
             WHERE item_id = :id AND version BETWEEN 2 AND :threshold",
            ['id' => $item_id, 'threshold' => $threshold]
        )->fetchAll();

        foreach ($rows as $row) {
            if ((int)($row['is_full_content'] ?? 1) !== 1) {
                continue; // уже diff
            }

            $ops  = \LineDiff::diff($baseText, (string)($row['item_content'] ?? ''));
            $data = gzcompress(serialize(self::diffToHunks($ops)));

            DB::run(
                "UPDATE item_history
                 SET item_content_diff = :diff, item_content = '', is_full_content = 0
                 WHERE version_id = :vid",
                ['diff' => $data, 'vid' => (int)$row['version_id']]
            );
        }
    }

    // Восстанавливает полный контент строки снимка: если строка полная — как есть,
    // если diff — применяет накопительные хунки к контенту версии №1.
    protected static function hydrateContent(array $row): array
    {
        if (!isset($row['is_full_content']) || (int)$row['is_full_content'] === 1) {
            return $row;
        }

        $base = DB::run(
            "SELECT item_content FROM item_history WHERE item_id = :id AND version = 1 LIMIT 1",
            ['id' => (int)$row['item_id']]
        )->fetch();

        $hunks = @unserialize(@gzuncompress((string)($row['item_content_diff'] ?? '')));

        $row['item_content'] = self::applyHunks(
            (string)($base['item_content'] ?? ''),
            is_array($hunks) ? $hunks : []
        );

        return $row;
    }

    /**
     * Преобразует построчный diff (последовательность '='|'-'|'+') в компактные
     * хунки замен: [['p' => стартовая позиция в базе, 'd' => удаляемые строки,
     * 'a' => добавляемые строки], ...]. Позиции — в координатах базы (v1).
     */
    protected static function diffToHunks(array $ops): array
    {
        $hunks   = [];
        $basePos = 0;
        $i       = 0;
        $n       = count($ops);

        while ($i < $n) {
            while ($i < $n && $ops[$i]['op'] === '=') {
                $basePos++;
                $i++;
            }
            if ($i >= $n) {
                break;
            }

            $hunkStart = $basePos;
            $del = [];
            $add = [];
            while ($i < $n && ($ops[$i]['op'] === '-' || $ops[$i]['op'] === '+')) {
                if ($ops[$i]['op'] === '-') {
                    $del[] = $ops[$i]['text'];
                } else {
                    $add[] = $ops[$i]['text'];
                }
                $i++;
            }

            $hunks[] = ['p' => $hunkStart, 'd' => $del, 'a' => $add];
            $basePos += count($del);
        }

        return $hunks;
    }

    /**
     * Применяет хунки (в координатах базы) к базовому тексту, получая контент версии.
     * Применение идёт снизу вверх, чтобы позиции остальных хунков не смещались.
     */
    protected static function applyHunks(string $baseText, array $hunks): string
    {
        if ($baseText === '') {
            $lines = [];
        } else {
            $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $baseText));
        }

        for ($i = count($hunks) - 1; $i >= 0; $i--) {
            $h   = $hunks[$i];
            $pos = max(0, (int)($h['p'] ?? 0));
            $del = $h['d'] ?? [];
            $add = $h['a'] ?? [];

            array_splice($lines, $pos, count($del), $add);
        }

        return implode("\n", $lines);
    }
}