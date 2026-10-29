<?php

declare(strict_types=1);

namespace App\Models;

use Hleb\Base\Model;
use Hleb\Static\DB;

/**
 * Обратные ссылки: статьи, которые ссылаются на данный факт.
 * Аналог раздела «Сюда ссылаются» в Википедии.
 *
 * Внутренние ссылки имеют вид [текст](/путь/slug.html).
 * Ищем по фрагменту "/slug.html" и пути категории, чтобы не подбирать
 * статьи с одинаковым slug из других категорий.
 */
class BacklinkModel extends Model
{
    /**
     * Статьи/факты, ссылающиеся на целевой элемент.
     *
     * @param int    $item_id     id целевого факта
     * @param string $facet_path  путь категории факта (например, "facts/nauka")
     * @param string $slug        slug целевого факта
     * @param int    $limit       максимум записей
     * @return array
     */
    public static function referring(int $item_id, string $facet_path, string $slug, int $limit = 50): array
    {
        if ($slug === '') {
            return [];
        }

        // Якорь жёсткой ссылки: "/живой-путь/slug.html".
        $anchor = '/' . trim($facet_path, '/') . '/' . $slug . '.html';

        // Статьи, ссылающиеся одним из способов:
        //  a) жёсткая ссылка [текст](anchor)
        //  b) вики-ссылка, чья ЦЕЛЬ резолвится в этот факт (по slug), вида [[Термин]] или [[текст|Термин]]
        $sql = "SELECT
                    i.item_id,
                    i.item_title,
                    i.item_slug,
                    i.item_type,
                    i.item_date,
                    i.item_content,
                    rel.facet_list
                FROM items i
                LEFT JOIN (
                    SELECT
                        relation_item_id,
                        GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list
                    FROM facets
                    LEFT JOIN facets_items_relation ON facet_id = relation_facet_id
                    GROUP BY relation_item_id
                ) AS rel ON rel.relation_item_id = i.item_id
                WHERE i.item_published = 1
                  AND i.item_is_deleted = 0
                  AND i.item_id <> :item_id
                  AND (
                      i.item_content LIKE :anchor_like
                      OR i.item_content LIKE :wikilink
                  )
                ORDER BY i.item_date DESC
                LIMIT :limit";

        $rows = DB::run($sql, [
            'item_id'      => (int)$item_id,
            'anchor_like'  => '%' . $anchor . '%',
            // ищем любые вики-ссылки; цель резолвится ниже
            'wikilink'     => '%[[%',
            'limit'        => (int)$limit,
        ])->fetchAll();

        // Отсеиваем: оставляем статьи, где либо есть жёсткая ссылка на anchor,
        // либо есть [[...]], чья цель (справа от | или сама) резолвится в этот факт.
        $result = [];
        foreach ($rows as $row) {
            $content = (string)($row['item_content'] ?? '');

            // Жёсткая ссылка — точно обратная
            if (mb_strpos($content, $anchor) !== false) {
                $result[] = $row;
                continue;
            }

            // Проверяем вики-ссылки [[...]]
            if (self::contentLinksTo($content, $slug)) {
                $result[] = $row;
            }
        }

        return array_slice($result, 0, $limit);
    }

    // Содержит ли контент вики-ссылку [[...|Цель]] или [[Цель]],
    // где цель резолвится (по слагифицированному slug) в текущий факт.
    protected static function contentLinksTo(string $content, string $slug): bool
    {
        if (!preg_match_all('/\[\[([^\[\]|]+?)(?:\|([^\[\]]+?))?\]\]/u', $content, $m, PREG_SET_ORDER)) {
            return false;
        }

        $slugify = new \Cocur\Slugify\Slugify();
        $targetSlug = $slugify->slugify($slug); // slug уже слагифицирован, но нормализуем

        foreach ($m as $match) {
            $target = trim((string)($match[2] ?? $match[1] ?? ''));
            if ($target === '') {
                continue;
            }
            $candidate = $slugify->slugify($target);

            // Цель указывает на этот факт: слагифицированная цель == слагу факта
            if ($candidate === $targetSlug) {
                return true;
            }
        }

        return false;
    }

    // Количество ссылающихся статей (для заголовка «N статей ссылаются сюда»)
    public static function countReferring(int $item_id, string $facet_path, string $slug): int
    {
        if ($slug === '') {
            return 0;
        }

        $anchor = '/' . trim($facet_path, '/') . '/' . $slug . '.html';

        $row = DB::run(
            "SELECT COUNT(*) c FROM items
             WHERE item_published = 1
               AND item_is_deleted = 0
               AND item_id <> :item_id
               AND item_content LIKE :like",
            ['item_id' => (int)$item_id, 'like' => '%' . $anchor . '%']
        )->fetch();

        return (int)($row['c'] ?? 0);
    }
}