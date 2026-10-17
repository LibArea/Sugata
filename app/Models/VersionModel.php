<?php

declare(strict_types=1);

namespace App\Models;

use Hleb\Base\Model;
use Hleb\Static\DB;

/**
 * История версий фактов/статей.
 * Хранит полный снимок (контент + slug + ключевые метаданные) ДО каждой правки,
 * позволяя смотреть историю и откатываться к любой версии.
 */
class VersionModel extends Model
{
    // Создать снимок текущего состояния факта (новой версией).
    // Вызывается ПЕРЕД обновлением items.
    public static function snapshot(array $item, ?string $comment = null, ?int $user_id = null): void
    {
        $item_id  = (int)$item['item_id'];

        $user_id  = $user_id ?? self::container()->user()->id();

        $next     = self::maxVersion($item_id) + 1;

        DB::run(
            "INSERT INTO item_history
                (item_id, version, item_title, item_slug, item_type, item_note,
                 item_content, item_thumb_img, facet_ids, source_url, comment, user_id)
             VALUES
                (:item_id, :version, :title, :slug, :type, :note,
                 :content, :thumb, :facets, :source, :comment, :user_id)",
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

    // Список версий факта: новые сверху
    public static function list(int $item_id, int $limit = 50): false|array
    {
        return DB::run(
            "SELECT h.*, u.login AS user_login
             FROM item_history h
             LEFT JOIN users u ON u.id = h.user_id
             WHERE h.item_id = :id
             ORDER BY h.version DESC
             LIMIT :limit",
            ['id' => $item_id, 'limit' => $limit]
        )->fetchAll();
    }

    // Одна версия по номеру (или false)
    public static function get(int $item_id, int $version): false|array
    {
        $row = DB::run(
            "SELECT * FROM item_history
             WHERE item_id = :id AND version = :version
             LIMIT 1",
            ['id' => $item_id, 'version' => $version]
        )->fetch();

        return $row ?: false;
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
}