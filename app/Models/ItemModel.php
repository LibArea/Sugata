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
            case 'page':
                $sort     = "item_is_deleted = 0 AND item_type = 'page' ORDER BY item_date DESC";
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

        $sql = "SELECT item_id	
							FROM facets_items_relation 
								LEFT JOIN items ON relation_item_id = item_id 
									WHERE $sort ";

        return DB::run($sql)->rowCount();
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
		
		if ($data['user_id']) {
			$user_id = json_decode($data['user_id'], true);
			$user_id = (int)$user_id[0]['id'];
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
                    item_user_id        = :item_user_id
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

    // Один случайный опубликованный факт
    public static function getRandomFact(): false|array
    {
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
                WHERE item_published = 1 AND item_is_deleted = 0 AND item_type = 'fact'
                ORDER BY RAND() LIMIT 1";

        return DB::run($sql)->fetch();
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
                    facet_type,
                    facet_slug
                        FROM facets  
                        INNER JOIN facets_items_relation ON relation_facet_id = facet_id
                            WHERE relation_item_id  = :item_id ";

        return DB::run($sql, ['item_id' => $item_id])->fetchAll();
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

}
