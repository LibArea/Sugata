<?php

declare(strict_types=1);

namespace App\Models;

use Hleb\Base\Model;
use Hleb\Static\DB;

/**
 * Реестр терминов энциклопедии (аналог linktarget/pagelinks в MediaWiki).
 *
 * Строится полным проходом по всем статьям: из каждой извлекаются вики-ссылки
 * [[текст|Цель]] / [[Цель]] и записываются как «термин → статья-источник».
 * Термин, для которого есть статья с совпадающим slug, считается существующим
 * (item_id > 0); иначе — «красным» (item_id = 0) и попадает в список востребованных.
 *
 * Полная пересборка идемпотентна: TRUNCATE + вставка, повторный запуск безопасен.
 */
class TermModel extends Model
{
    // Полная пересборка реестра терминов из всех опубликованных статей.
    // Возвращает ['terms' => int, 'links' => int, 'wanted' => int].
    public static function rebuild(): array
    {
        DB::run("TRUNCATE TABLE terms_links");
        DB::run("TRUNCATE TABLE terms");

        $items = ItemModel::getItemAll();

        $linksCount = 0;
        $inserted   = 0;

        // Сначала регистрируем термины-статьи: каждая опубликованная статья может
        // быть целью вики-ссылки (по своему item_slug).
        foreach ($items as $item) {
            $slug = trim((string)$item['item_slug']);
            if ($slug === '') {
                continue;
            }
            if (self::insertTerm($item['item_title'] ?? '', $slug, (int)$item['item_id'])) {
                $inserted++;
            }
        }

        // Идём по статьям и для каждой вики-ссылки регистрируем связь.
        // Цель ссылки резолвим так же, как парсер (Parser::wikiLinks): если она
        // однозначно соответствует существующей статье, привязываем термин к ней,
        // иначе термин остаётся «красным» (item_id=0) и попадает в востребованные.
        $slugify = new \Cocur\Slugify\Slugify();
        foreach ($items as $item) {
            $sourceId = (int)$item['item_id'];
            foreach (\Parser::extractWikilinks((string)$item['item_content']) as $wl) {
                $target = trim($wl['target']);
                if ($target === '') {
                    continue;
                }

                // Явный путь [[текст|/путь/slug.html]] — не термин, пропускаем.
                if (str_starts_with($target, '/') && str_ends_with($target, '.html')) {
                    continue;
                }

                // Резолв цели в существующую статью (однозначное попадание).
                $resolvedItemId = self::resolveTargetToItem($slugify, $target);

                $termId = self::insertTerm($target, '', $resolvedItemId);
                if (!$termId) {
                    continue;
                }

                $ok = DB::run(
                    "INSERT IGNORE INTO terms_links (item_id, term_id) VALUES (:i, :t)",
                    ['i' => $sourceId, 't' => $termId]
                );
                if ($ok) {
                    $linksCount++;
                }
            }
        }

        // Востребованные (красные) термины: те, у которых item_id = 0.
        $wanted = (int)DB::run(
            "SELECT COUNT(*) c FROM terms WHERE item_id = 0"
        )->fetch()['c'];

        return ['terms' => $inserted, 'links' => $linksCount, 'wanted' => $wanted];
    }

    // Термин с числом входящих ссылок и списком статей, где он встречается
    // (для страницы «Востребованные термины»).
    public static function wanted(int $page = 1, int $limit = 50): false|array
    {
        $start = ($page - 1) * $limit;

        return DB::run(
            "SELECT t.term, t.term_slug, t.item_id, COUNT(tl.id) AS links_count,
                    GROUP_CONCAT(DISTINCT CONCAT(i.item_id, '::', i.item_title)
                        ORDER BY i.item_title SEPARATOR '||') AS source_items
             FROM terms t
             LEFT JOIN terms_links tl ON tl.term_id = t.term_id
             LEFT JOIN items i ON i.item_id = tl.item_id
             WHERE t.item_id = 0
             GROUP BY t.term_id
             ORDER BY links_count DESC, t.term ASC
             LIMIT :start, :limit",
            ['start' => $start, 'limit' => $limit]
        )->fetchAll();
    }

    // Общее число красных (несозданных) терминов.
    public static function wantedCount(): int
    {
        return (int)DB::run(
            "SELECT COUNT(*) c FROM terms WHERE item_id = 0"
        )->fetch()['c'];
    }

    // Вставляет термин в реестр, если его ещё нет. Возвращает term_id или null.
    // $slug — слагифицированная форма; если пусто — считается из $term.
    protected static function insertTerm(string $term, string $slug, int $itemId): ?int
    {
        $term = trim($term);
        if ($term === '') {
            return null;
        }

        // Проверка: существует ли уже такой термин
        $existing = DB::run(
            "SELECT term_id FROM terms WHERE term = :term LIMIT 1",
            ['term' => $term]
        )->fetch();
        if ($existing) {
            $termId = (int)$existing['term_id'];
            // Если термин был красным, а теперь появилась статья — связываем.
            if ($itemId > 0) {
                DB::run(
                    "UPDATE terms SET item_id = :item WHERE term_id = :tid AND item_id = 0",
                    ['item' => $itemId, 'tid' => $termId]
                );
            }
            return $termId;
        }

        if ($slug === '') {
            $slug = (string)(new \Cocur\Slugify\Slugify())->slugify($term);
        }
        $slug = substr($slug, 0, 90);

        DB::run(
            "INSERT INTO terms (term, term_slug, item_id) VALUES (:t, :s, :i)",
            ['t' => $term, 's' => $slug, 'i' => $itemId]
        );
        $row = DB::run("SELECT LAST_INSERT_ID() id")->fetch();

        return $row ? (int)$row['id'] : null;
    }

    /**
     * Резолвит цель вики-ссылки в id существующей статьи (0, если статьи нет).
     * Использует ту же логику однозначного резолва, что Parser::wikiLinks
     * (findByTerm: точный slug / слово в названии, без частичных вхождений).
     */
    protected static function resolveTargetToItem(\Cocur\Slugify\Slugify $slugify, string $target): int
    {
        $targetSlug = substr((string)$slugify->slugify($target), 0, 90);
        if ($targetSlug === '') {
            return 0;
        }

        $row = DB::run(
            "SELECT item_id FROM items
             WHERE item_published = 1 AND item_is_deleted = 0 AND item_slug = :slug
             LIMIT 1",
            ['slug' => $targetSlug]
        )->fetch();

        return $row ? (int)$row['item_id'] : 0;
    }
}