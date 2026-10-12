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

        // Якорь ссылки: "/живой-путь/slug.html". Экранируем регэксп-спецсимволы пути.
        $anchor = '/' . trim($facet_path, '/') . '/' . $slug . '.html';
        $needle = preg_quote($anchor, '/');

        $sql = "SELECT
                    i.item_id,
                    i.item_title,
                    i.item_slug,
                    i.item_type,
                    i.item_date,
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
                  AND i.item_content LIKE :like
                ORDER BY i.item_date DESC
                LIMIT :limit";

        return DB::run($sql, [
            'item_id' => (int)$item_id,
            'like'    => '%' . $anchor . '%',
            'limit'   => (int)$limit,
        ])->fetchAll();
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