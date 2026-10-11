<?php

declare(strict_types=1);

namespace App\Models;

use Hleb\Base\Model;
use Hleb\Static\DB;

use Html;

class ItemModel extends Model
{
    public static function sorts($sheet)
    {
		$user_id = self::container()->user()->id();
		
        switch ($sheet) {
            case 'main':
                $sort     = "item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact' AND item_published = 1 ORDER BY item_id DESC";
                break;
            case 'all':
                $sort     = "item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact' ORDER BY item_date DESC";
                break;
            case 'all_site':
                $sort     = "item_published = 1 AND item_is_deleted = 0 AND item_type IN ('fact','article') ORDER BY item_date DESC";
                break;
            case 'page':
                $sort     = "item_is_deleted = 0 AND item_type = 'page' ORDER BY item_date DESC";
                break;
            case 'articles':
                $sort     = "item_published = 1 AND item_is_deleted = 0 AND item_type = 'article' ORDER BY item_date DESC";
                break;
            case 'moderation':
                $sort     = "item_published = 0 AND item_type = 'fact' ORDER BY item_date DESC";
                break;
            case 'my':
                $sort     = "item_type = 'fact' AND item_user_id = $user_id ORDER BY item_date DESC";
                break;
            case 'deleted':
                $sort     = "item_is_deleted = 1 ORDER BY item_id DESC";
                break;
            default:
                $sort = "item_published = 1 AND item_type = 'fact' ORDER BY item_id DESC";
        }

        return $sort;
    }
	
    public static function facets($facets, $topic_id)
    {
        if ($facets === false) {
            return '';
        }

        if ($topic_id === false) {
            return '';
        }

        $result = [];
        foreach ($facets as $ind => $row) {
            $result['9999'] = $topic_id;
            $result[$ind] = $row['facet_id'];
        }

        $enumeration = "relation_facet_id IN($topic_id) AND ";
        if ($result) {
            $enumeration = "relation_facet_id IN(" . implode(',', $result ?? []) . ") AND ";
        }

        return $enumeration;
    }

    // Получаем сайты по условиям
    public static function feedItem($childrens, $category_id, $page, $limit, $sort = 'main')
    {
        $facets = self::facets($childrens, $category_id);
        $sort   = $facets . self::sorts($sort);

        $start  = ($page - 1) * $limit;
        $sql = "SELECT DISTINCT
                    item_id,
                    item_title,
                    item_content,
	                item_slug,
					item_type,
			        item_published,
                    item_user_id,
                    item_date,
	                item_is_deleted,
					u.id, u.login, u.avatar, u.created_at, 
                    rel.*
  
                        FROM facets_items_relation 
                        LEFT JOIN items ON relation_item_id = item_id
                        LEFT JOIN (
                            SELECT  		 
                                relation_item_id,
                                GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@',  facet_title SEPARATOR '@') AS facet_list
                                FROM facets
                                LEFT JOIN facets_items_relation on facet_id = relation_facet_id
                                    GROUP BY relation_item_id
                        ) AS rel
                            ON rel.relation_item_id = item_id
							LEFT JOIN users u ON u.id = item_user_id
                                    WHERE  $sort LIMIT :start, :limit";

        return DB::run($sql, ['start' => $start, 'limit' => $limit])->fetchAll();
    }

    public static function feedItemCount($childrens, $category_id, $sort = 'main')
    {
        $facets = self::facets($childrens, $category_id);
        $sort   = $facets . self::sorts($sort);

        $sql = "SELECT COUNT(DISTINCT item_id) AS cnt
							FROM facets_items_relation 
								LEFT JOIN items ON relation_item_id = item_id 
									WHERE $sort ";

        $row = DB::run($sql)->fetch();
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Add a domain
     * Добавим домен
     *
     * @param array $params
     * @return mixed
     */
    public static function add(array $data)
    {
		$item_published = $data['item_published'] ?? false;
		$item_published = $item_published == 'on' ? 1 : 0;
		
		$params =  [
                'item_title'            => $data['item_title'],
                'item_content'          => $data['item_content'],
				'item_note'				=> $data['item_note'],
				'item_type'				=> $data['item_type'] ?? 'fact',
                'item_slug'             => $data['item_slug'],
				'item_published'  		=> $item_published,
                'item_user_id'          => self::container()->user()->id(),
            ];
		
		
        $sql = "INSERT INTO items(item_title, 
                            item_content, 
							item_note,
							item_type,
                            item_slug,
                            item_published,
                            item_user_id) 
                            
                       VALUES(:item_title, 
                       :item_content, 
					   :item_note,
					   :item_type,
                       :item_slug,
                       :item_published,
                       :item_user_id)";

        DB::run($sql, $params);

        $item_id =  DB::run("SELECT LAST_INSERT_ID() as item_id")->fetch();

        // Источник — отдельная таблица sources + связь article_sources
        if (!empty($data['item_source_url'])) {
            self::setItemSource((int)$item_id['item_id'], $data['item_source_url']);
        }

        return $item_id;
    }

    public static function edit($data)
    {
		$item_published = $data['item_published'] ?? 0;
		$item_published = $item_published == 'on' ? 1 : 0;
		
	//	$user_id = $data['user_id'] ?? self::container()->user()->id();
		
		if (!empty($data['user_id'])) {
			$user_id = json_decode($data['user_id'], true);
			$user_id = (int)($user_id[0]['id'] ?? 0);
			if ($user_id <= 0) {
				$user_id = self::container()->user()->id();
			}
		} else {
			$user_id = self::container()->user()->id();
		}	
		
		$params =  [
				'item_id'           	=> $data['item_id'],
                'item_title'            => $data['item_title'],
                'item_content'          => $data['item_content'],
				'item_note'				=> $data['item_note'],
				'item_type' 			=> $data['item_type'] ?? 'fact',
                'item_slug'             => $data['item_slug'],
				'item_thumb_img' 		=> $data['item_thumb_img'] ?? NULL,
				'item_modified' 		=> date("Y-m-d H:i:s"),
				'item_published'  		=> $item_published,
                'item_user_id'          => $user_id,
                'item_best'             => (!empty($data['item_best'])) ? 1 : 0,

            ];
			
        $sql = "UPDATE items 
                    SET item_title		= :item_title, 
                    item_content        = :item_content,
					item_note        	= :item_note,
					item_type 			= :item_type,
                    item_slug           = :item_slug,
					item_thumb_img 		= :item_thumb_img,
					item_modified 		= :item_modified,
                    item_published      = :item_published,
                    item_user_id        = :item_user_id,
                    item_best           = :item_best
                        WHERE item_id   = :item_id";

        DB::run($sql, $params);

        // Источник — отдельная таблица sources + связь article_sources
        if (array_key_exists('item_source_url', $data)) {
            self::setItemSource((int)$data['item_id'], (string)($data['item_source_url'] ?? ''));
        }

        return true;
    }

    // Сохранить/заменить источник факта в таблицах sources + article_sources
    public static function setItemSource(int $item_id, string $url): void
    {
        // Удаляем старую связь
        DB::run("DELETE FROM article_sources WHERE article_id = :id", ['id' => $item_id]);

        $url = trim($url);
        if ($url === '') {
            return;
        }

        // Источник по URL — найти или создать
        $source = DB::run("SELECT id, title FROM sources WHERE url = :url LIMIT 1", ['url' => $url])->fetch();
        if (empty($source)) {
            $domain = parse_url($url, PHP_URL_HOST) ?: '';
            DB::run("INSERT INTO sources (url, domain) VALUES (:url, :domain)", [
                'url' => $url,
                'domain' => $domain,
            ]);
            $source = DB::run("SELECT LAST_INSERT_ID() as id")->fetch();
        }

        DB::run("INSERT INTO article_sources (article_id, source_id) VALUES (:aid, :sid)", [
            'aid' => $item_id,
            'sid' => (int)$source['id'],
        ]);

        self::cleanupOrphanSources();
    }

    // Удаляет источники, на которые не ссылается ни один факт и которые
    // ещё не проверялись — чтобы таблица sources не накапливала мусор
    // после замены ссылки в факте.
    protected static function cleanupOrphanSources(): void
    {
        DB::run("DELETE FROM sources
                 WHERE last_checked_at IS NULL
                   AND NOT EXISTS (SELECT 1 FROM article_sources a_s WHERE a_s.source_id = sources.id)");
    }


    public static function addItemFacets(array $rows, int $item_id)
    {
        self::deleteRelation($item_id, 'item');

        foreach ($rows as $row) {
            $facet_id   = (int)($row['id'] ?? 0);
            if ($facet_id <= 0) {
                continue;
            }
            $sql = "INSERT INTO facets_items_relation (relation_facet_id, relation_item_id) 
                        VALUES ($facet_id, $item_id)";

            DB::run($sql);
        }

 
        return true;
    }

    /**
     * Сохранить связи "факт ↔ статья" для элемента.
     * Пишет обе направленные строки (зеркалирование), чтобы запрос был тривиальным.
     *
     * @param int   $item_id    id владельца связи
     * @param int[] $relatedIds массив id связанных элементов
     * @param string $relType  тип связи (по умолчанию 'related')
     */
    public static function setRelations(int $item_id, array $relatedIds, string $relType = 'related'): void
    {
        $item_id = (int)$item_id;
        $relatedIds = array_values(array_unique(array_filter(array_map('intval', $relatedIds))));
        $relatedIds = array_diff($relatedIds, [$item_id]); // нельзя связать с самим собой

        // Удаляем старые связи в обе стороны
        self::deleteItemRelations($item_id);

        foreach ($relatedIds as $rid) {
            if ($rid <= 0) {
                continue;
            }
            $rows = [
                ['item_id' => $item_id, 'related_item_id' => $rid],
                ['item_id' => $rid, 'related_item_id' => $item_id],
            ];
            foreach ($rows as $row) {
                DB::run(
                    "INSERT IGNORE INTO item_relations (item_id, related_item_id, relation_type, sort_order)
                     VALUES (:item_id, :related_item_id, :rel_type, 0)",
                    ['item_id' => $row['item_id'], 'related_item_id' => $row['related_item_id'], 'rel_type' => $relType]
                );
            }
        }
    }

    // Удаление всех связей элемента (в обе стороны)
    protected static function deleteItemRelations(int $item_id): void
    {
        DB::run(
            "DELETE FROM item_relations WHERE item_id = :id_a OR related_item_id = :id_b",
            ['id_a' => $item_id, 'id_b' => $item_id]
        );
    }

    /**
     * Оставляет из списка id только записи заданного типа (например, 'article').
     */
    public static function filterIdsByType(array $ids, string $type): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids) {
            return [];
        }

        $in = implode(',', array_fill(0, count($ids), '?'));
        $rows = DB::run(
            "SELECT item_id FROM items WHERE item_id IN ($in) AND item_type = ? AND item_is_deleted = 0",
            [...$ids, $type]
        )->fetchAll();

        return array_map('intval', array_column($rows, 'item_id'));
    }

    // ID связанных элементов (в обе стороны) для текущего item
    public static function getRelationIds(int $item_id): array
    {
        $rows = DB::run(
            "SELECT item_id AS a, related_item_id AS b FROM item_relations
             WHERE item_id = :id_a OR related_item_id = :id_b",
            ['id_a' => (int)$item_id, 'id_b' => (int)$item_id]
        )->fetchAll();

        $ids = [];
        foreach ($rows as $row) {
            $other = ((int)$row['a'] === (int)$item_id) ? (int)$row['b'] : (int)$row['a'];
            if ($other > 0 && $other !== (int)$item_id) {
                $ids[] = $other;
            }
        }
        return array_values(array_unique($ids));
    }

    // Связанные элементы (полные записи) для рендера блока
    public static function getRelatedItems(int $item_id, ?string $type = null): array
    {
        $ids = self::getRelationIds($item_id);
        if (!$ids) {
            return [];
        }

        $in = implode(',', array_map('intval', $ids));
        $typeCond = $type ? " AND item_type = :type " : '';

        $sql = "SELECT item_id, item_title, item_slug, item_type, item_date, item_content, item_published, item_is_deleted,
                       u.login,
                       rel.*
                FROM items
                LEFT JOIN (
                    SELECT relation_item_id,
                           GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list
                    FROM facets
                    LEFT JOIN facets_items_relation ON facet_id = relation_facet_id
                    GROUP BY relation_item_id
                ) AS rel ON rel.relation_item_id = item_id
                LEFT JOIN users u ON u.id = item_user_id
                WHERE item_id IN ($in) $typeCond
                  AND item_published = 1 AND item_is_deleted = 0
                ORDER BY item_date DESC";

        $params = [];
        if ($type) {
            $params['type'] = $type;
        }
        return DB::run($sql, $params)->fetchAll();
    }

    public static function deleteRelation(int $id, string $type)
    {
        $sql = "DELETE FROM facets_items_relation WHERE relation_item_id = :id";
        if ($type == 'topic') {
            $sql = "DELETE FROM facets_relation WHERE facet_parent_id = :id";
        } elseif ($type == 'matching') {
            $sql = "DELETE FROM facets_matching WHERE matching_parent_id = :id";
        }

        return DB::run($sql, ['id' => (int)$id]);
    }
	
    // Removing the cover
    // Удаление обложки
    public static function setItemThumbRemove(int $item_id)
    {
        $sql = "UPDATE items SET item_thumb_img = '' WHERE item_id = :item_id";

        return DB::run($sql, ['item_id' => $item_id]);
    }
	
    // Full post 
    // Полная версия поста  
    public static function getItem(string|int|null $params, string $name)
    {
        $user_id = self::container()->user()->id();
        $sort = $name == 'slug' ?  "item_slug = :params" : "item_id = :params";

        $sql = "SELECT 
                    item_id,
                    item_title,
                    item_slug,
                    item_date,
					item_type,
                    item_published,
                    item_user_id,
                    item_ip,
                    item_content,
					item_note,
                    s.title AS item_source_title,
                    s.url    AS item_source_url,
                    item_content_img,
                    item_thumb_img,
                    item_is_deleted,
                    item_best,
                    u.id,
                    u.login,
                    u.avatar,
					u.created_at,
					 rel.*

                        FROM items
						
                        LEFT JOIN article_sources a_s ON a_s.article_id = item_id
                        LEFT JOIN sources s ON s.id = a_s.source_id

                        LEFT JOIN
                        (
                            SELECT 
                                relation_item_id,
                                GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@',  facet_title SEPARATOR '@') AS facet_list
                                FROM facets  
                                LEFT JOIN facets_items_relation on facet_id = relation_facet_id
                                        GROUP BY relation_item_id
                        ) AS rel
                            ON rel.relation_item_id = item_id 
						
						
                        LEFT JOIN users u ON u.id = item_user_id
                            WHERE $sort";

        $data = ['params' => $params];

        return DB::run($sql, $data)->fetch();
    }
	
    public static function getPublishedByFacetPath(string $facetPath, string $slug)
    {
        $sql = "SELECT
                    i.item_id,
                    i.item_title,
                    i.item_content,
                    i.item_slug,
                    i.item_type,
                    i.item_published,
                    s.title AS item_source_title,
                    s.url    AS item_source_url,
                    i.item_thumb_img,
                    i.item_date,
                    rel.facet_list
                FROM items i
                INNER JOIN facets_items_relation fir ON fir.relation_item_id = i.item_id
                INNER JOIN facets f ON f.facet_id = fir.relation_facet_id
                LEFT JOIN article_sources a_s ON a_s.article_id = i.item_id
                LEFT JOIN sources s ON s.id = a_s.source_id
                LEFT JOIN (
                    SELECT
                        relation_item_id,
                        GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list
                    FROM facets
                    LEFT JOIN facets_items_relation ON facet_id = relation_facet_id
                    GROUP BY relation_item_id
                ) AS rel ON rel.relation_item_id = i.item_id
                WHERE i.item_slug = :slug
                    AND i.item_published = 1
                    AND i.item_is_deleted = 0
                    AND f.facet_type = 'category'
                    AND f.facet_path = :facet_path
                LIMIT 1";

        return DB::run($sql, ['facet_path' => $facetPath, 'slug' => $slug])->fetch();
    }

    public static function getItemAll()
    {
        $sql = "SELECT
                    item_id,
                    item_title,
					item_content,
                    item_slug,
                    item_type,
                    item_published,
					s.title AS item_source_title,
					s.url    AS item_source_url,
                    item_user_id,
                    item_modified,
                    item_date,
                    rel.*
                        FROM items
                        LEFT JOIN article_sources a_s ON a_s.article_id = item_id
                        LEFT JOIN sources s ON s.id = a_s.source_id
                        LEFT JOIN
                        (
                            SELECT 
                                relation_item_id,
                                GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@',  facet_title SEPARATOR '@') AS facet_list
                                FROM facets  
                                LEFT JOIN facets_items_relation on facet_id = relation_facet_id
                                        GROUP BY relation_item_id
                        ) AS rel
                            ON rel.relation_item_id = item_id 
    
                        WHERE item_published = 1 AND item_is_deleted = 0";

        return DB::run($sql)->fetchAll();
    }

    // Один случайный опубликованный факт.
    // Без ORDER BY RAND() (тяжёлый на больших таблицах):
    // берём случайный id из диапазона MIN..MAX и подтягиваем ближайший факт.
    public static function getRandomFact(): false|array
    {
        $range = DB::run(
            "SELECT MIN(item_id) AS min_id, MAX(item_id) AS max_id
             FROM items
             WHERE item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact'"
        )->fetch();

        $min = (int)($range['min_id'] ?? 0);
        $max = (int)($range['max_id'] ?? 0);
        if ($max <= $min) {
            return false;
        }

        $rand = random_int($min, $max);

        $sql = "SELECT item_id, item_title, item_slug, rel.*
                FROM items
                LEFT JOIN (
                    SELECT
                        relation_item_id,
                        GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list
                    FROM facets
                    LEFT JOIN facets_items_relation ON facet_id = relation_facet_id
                    GROUP BY relation_item_id
                ) AS rel ON rel.relation_item_id = item_id
                WHERE item_id >= :rand
                  AND item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact'
                ORDER BY item_id ASC
                LIMIT 1";

        $fact = DB::run($sql, ['rand' => $rand])->fetch();

        // Если за случайным id не оказалось факта (дыра) — берём ближайший снизу
        if (empty($fact)) {
            $sqlDown = str_replace('item_id >= :rand', 'item_id <= :rand', $sql);
            $sqlDown = str_replace('ORDER BY item_id ASC', 'ORDER BY item_id DESC', $sqlDown);
            $fact = DB::run($sqlDown, ['rand' => $rand])->fetch();
        }

        return $fact;
    }

    // N случайных фактов из ЖИВЫХ категорий (для random-facts.js).
    // Без ORDER BY RAND(): выбираем диапазон случайных id и подтягиваем факты,
    // добирая короткими запросами, пока не наберём $limit уникальных.
    public static function getRandomFacts(int $limit): false|array
    {
        $range = DB::run(
            "SELECT MIN(item_id) AS min_id, MAX(item_id) AS max_id
             FROM items
             WHERE item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact'"
        )->fetch();

        $min = (int)($range['min_id'] ?? 0);
        $max = (int)($range['max_id'] ?? 0);
        if ($max <= $min) {
            return [];
        }

        $result = [];
        $attempts = 0;
        $maxRounds = $limit * 4 + 10; // предохранитель от бесконечного цикла

        while (count($result) < $limit && $attempts < $maxRounds) {
            $attempts++;

            // Случайная точка в диапазоне id
            $rand = random_int($min, $max);

            $sql = "SELECT DISTINCT i.item_id, i.item_title, i.item_slug, rel.facet_list
                    FROM items i
                    INNER JOIN facets_items_relation fir ON fir.relation_item_id = i.item_id
                    INNER JOIN facets f ON f.facet_id = fir.relation_facet_id
                    LEFT JOIN (
                        SELECT
                            relation_item_id,
                            GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list
                        FROM facets
                        LEFT JOIN facets_items_relation ON facet_id = relation_facet_id
                        GROUP BY relation_item_id
                    ) AS rel ON rel.relation_item_id = i.item_id
                    WHERE i.item_id >= :rand
                      AND i.item_published = 1 AND i.item_is_deleted = 0 AND i.item_type = 'fact'
                      AND f.facet_type = 'category' AND f.facet_is_deleted = 0
                    ORDER BY i.item_id ASC
                    LIMIT 1";

            $fact = DB::run($sql, ['rand' => $rand])->fetch();
            if (empty($fact)) {
                continue;
            }

            if (isset($result[$fact['item_id']])) {
                continue;
            }

            $result[$fact['item_id']] = $fact;
        }

        return array_values($result);
    }

    /**
     * «Тема дня»: большой факт (контент >= минимальной длины) с фото.
     * Выбирается случайно из подходящих; фото — миниатюра или первая картинка контента.
     */
    public static function getFeaturedBig(): false|array
    {
        $minLength = (int)config('general', 'fact_day_min_length');
        if ($minLength <= 0) {
            $minLength = 1000;
        }

        $range = DB::run(
            "SELECT MIN(item_id) AS min_id, MAX(item_id) AS max_id
             FROM items
             WHERE item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact'
               AND CHAR_LENGTH(item_content) >= :min",
            ['min' => $minLength]
        )->fetch();

        $min = (int)($range['min_id'] ?? 0);
        $max = (int)($range['max_id'] ?? 0);
        if ($max <= $min) {
            // Нет «больших» — любой с фото, затем любой опубликованный
            return static::featuredFallback(100000);
        }

        for ($i = 0; $i < 40; $i++) {
            $rand = random_int($min, $max);

            $sql = "SELECT item_id, item_title, item_slug, item_content, item_thumb_img,
                           item_date, item_modified, rel.facet_list
                    FROM items
                    LEFT JOIN (
                        SELECT
                            relation_item_id,
                            GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list
                        FROM facets
                        LEFT JOIN facets_items_relation ON facet_id = relation_facet_id
                        GROUP BY relation_item_id
                    ) AS rel ON rel.relation_item_id = item_id
                    WHERE item_id >= :rand
                      AND item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact'
                      AND CHAR_LENGTH(item_content) >= :min
                      AND (item_thumb_img IS NOT NULL AND item_thumb_img <> '')
                    ORDER BY item_id ASC
                    LIMIT 1";

            $fact = DB::run($sql, ['rand' => $rand, 'min' => $minLength])->fetch();
            if (!empty($fact)) {
                return $fact;
            }
        }

        // Перебрали случайные точки — берём любой большой (первый попавшийся)
        return static::featuredFallback($minLength);
    }

    // Подстраховка: если нет больших с фото — любой большой, затем любой с фото
    protected static function featuredFallback(int $minLength): false|array
    {
        $types = [
            "CHAR_LENGTH(item_content) >= :min AND (item_thumb_img IS NOT NULL AND item_thumb_img <> '')",
            "CHAR_LENGTH(item_content) >= :min",
            "(item_thumb_img IS NOT NULL AND item_thumb_img <> '')",
            "1",
        ];

        foreach ($types as $cond) {
            $sql = "SELECT item_id, item_title, item_slug, item_content, item_thumb_img,
                           item_date, item_modified, rel.facet_list
                    FROM items
                    LEFT JOIN (
                        SELECT
                            relation_item_id,
                            GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list
                        FROM facets
                        LEFT JOIN facets_items_relation ON facet_id = relation_facet_id
                        GROUP BY relation_item_id
                    ) AS rel ON rel.relation_item_id = item_id
                    WHERE item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact'
                      AND $cond
                    ORDER BY item_id DESC
                    LIMIT 1";

            $params = str_contains($cond, ':min') ? ['min' => $minLength] : [];
            $fact = DB::run($sql, $params)->fetch();
            if (!empty($fact)) {
                return $fact;
            }
        }

        return false;
    }

    /**
     * «Знаете ли вы?»: несколько коротких фактов (контент < минимальной длины),
     * случайные, для аккордеона на главной.
     */
    public static function getFactsDidYouKnow(int $limit = 5): false|array
    {
        $minLength = (int)config('general', 'fact_day_min_length');
        if ($minLength <= 0) {
            $minLength = 1000;
        }

        $sql = "SELECT item_id, item_title, item_slug, item_content, rel.facet_list
                FROM items
                LEFT JOIN (
                    SELECT
                        relation_item_id,
                        GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list
                    FROM facets
                    LEFT JOIN facets_items_relation ON facet_id = relation_facet_id
                    GROUP BY relation_item_id
                ) AS rel ON rel.relation_item_id = item_id
                WHERE item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact'
                  AND CHAR_LENGTH(item_content) < :min
                ORDER BY RAND()
                LIMIT :limit";

        return DB::run($sql, ['min' => $minLength, 'limit' => $limit])->fetchAll();
    }

    /**
     * «Лучший факт»: один случайный из помеченных item_best = 1.
     * Фото — миниатюра или первая картинка в контенте.
     */
    public static function getBestFact(): false|array
    {
        $sql = "SELECT item_id, item_title, item_slug, item_content, item_thumb_img,
                       item_date, rel.facet_list
                FROM items
                LEFT JOIN (
                    SELECT
                        relation_item_id,
                        GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list
                    FROM facets
                    LEFT JOIN facets_items_relation ON facet_id = relation_facet_id
                    GROUP BY relation_item_id
                ) AS rel ON rel.relation_item_id = item_id
                WHERE item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact'
                  AND item_best = 1
                  AND (
                      (item_thumb_img IS NOT NULL AND item_thumb_img <> '')
                      OR POSITION('![' IN item_content) > 0
                  )
                ORDER BY (item_thumb_img IS NOT NULL AND item_thumb_img <> '') DESC, RAND()
                LIMIT 1";

        return DB::run($sql)->fetch();
    }

    // Счётчик «лучших» фактов
    public static function countBest(): int
    {
        return (int)DB::run(
            "SELECT COUNT(*) c FROM items
             WHERE item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact' AND item_best = 1"
        )->fetch()['c'];
    }

    // «Лучшие» факты с пагинацией (для страницы /best/)
    public static function getBestFacts(int $page = 1, int $limit = 20): false|array
    {
        $start = ($page - 1) * $limit;

        $sql = "SELECT item_id, item_title, item_slug, item_content, item_thumb_img,
                       item_date, rel.facet_list
                FROM items
                LEFT JOIN (
                    SELECT
                        relation_item_id,
                        GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list
                    FROM facets
                    LEFT JOIN facets_items_relation ON facet_id = relation_facet_id
                    GROUP BY relation_item_id
                ) AS rel ON rel.relation_item_id = item_id
                WHERE item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact'
                  AND item_best = 1
                ORDER BY item_best DESC, item_id DESC
                LIMIT :start, :limit";

        return DB::run($sql, ['start' => $start, 'limit' => $limit])->fetchAll();
    }

    /**
     * Topics by reference 
     * Темы по ссылке
     *
     * @param [type] $item_id
     */
    public static function getItemTopic($item_id): false|array
    {
        $sql = "SELECT
                    facet_id id,
                    facet_title as value,
                    facet_path,
                    facet_type,
                    facet_slug
                        FROM facets  
                        INNER JOIN facets_items_relation ON relation_facet_id = facet_id
                            WHERE relation_item_id  = :item_id ";

        $list = DB::run($sql, ['item_id' => $item_id])->fetchAll();

        // Формируем человекочитаемый путь: "facts / Непознанное / Религия / Буддизм"
        foreach ($list as $i => $row) {
            if (!empty($row['facet_path'])) {
                $list[$i]['value'] = $row['facet_path'] . ' / ' . $row['value'];
            }
        }

        return $list;
    }

    // Увеличить счётчик просмотров факта
    public static function incrementViews(int $item_id): void
    {
        DB::run("UPDATE items SET item_views = item_views + 1 WHERE item_id = :id", ['id' => $item_id]);
    }

    // Самые читаемые факты
    public static function getPopular(int $limit = 5): false|array
    {
        $sql = "SELECT
                    i.item_id,
                    i.item_title,
                    i.item_slug,
                    i.item_date,
                    i.item_views,
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
                WHERE i.item_published = 1 AND i.item_is_deleted = 0 AND i.item_type = 'fact'
                ORDER BY i.item_views DESC, i.item_id DESC
                LIMIT :limit";

        return DB::run($sql, ['limit' => $limit])->fetchAll();
    }

    /**
     * Факты, на которые ссылается текст статьи (внутренние ссылки вида /facts/.../slug.html).
     * Возвращает полные записи фактов (как getRelatedItems).
     */
    public static function getFactsFromContent(?string $content): array
    {
        if (empty($content)) {
            return [];
        }

        // Извлекаем все внутренние ссылки на факты: [текст](/facts/путь/slug.html)
        preg_match_all('#\((/facts/[a-z0-9\-/_]+\.html)\)#i', (string)$content, $m);
        if (empty($m[1])) {
            return [];
        }

        $slugs = [];
        foreach ($m[1] as $link) {
            $slug = basename((string)$link, '.html');
            if ($slug !== '' && $slug !== 'index') {
                $slugs[$slug] = true;
            }
        }
        if (!$slugs) {
            return [];
        }

        $in = implode(',', array_fill(0, count($slugs), '?'));
        $sql = "SELECT item_id, item_title, item_slug, item_type, item_date, item_content, item_published, item_is_deleted,
                       u.login,
                       rel.*
                FROM items
                LEFT JOIN (
                    SELECT relation_item_id,
                           GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list
                    FROM facets
                    LEFT JOIN facets_items_relation ON facet_id = relation_facet_id
                    GROUP BY relation_item_id
                ) AS rel ON rel.relation_item_id = item_id
                LEFT JOIN users u ON u.id = item_user_id
                WHERE item_slug IN ($in)
                  AND item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact'
                ORDER BY item_date DESC";

        return DB::run($sql, array_keys($slugs))->fetchAll();
    }

}
