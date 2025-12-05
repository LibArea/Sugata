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
        switch ($sheet) {
            case 'main':
                $sort     = "item_is_deleted = 0 AND item_published = 1 ORDER BY item_id DESC";
                break;
            case 'all':
                $sort     = "item_is_deleted = 0 ORDER BY item_date DESC";
                break;
            case 'deleted':
                $sort     = "item_is_deleted = 1 ORDER BY item_id DESC";
                break;
            default:
                $sort = 'item_published = 1 ORDER BY item_id DESC';
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
			        item_published,
                    item_user_id,
                    item_date,
                    item_is_deleted,

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

  /*  public static function goItems($limit, $page = 1, $sort = 'main')
    {
		$sort   = self::sorts($sort);
		$start  = ($page - 1) * self::$limit;
		
        $sql = "SELECT DISTINCT
                    item_id,
                    item_title,
                    item_content,
	                item_slug,
			        item_published,
                    item_user_id,
                    item_date,
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
                                    WHERE $sort LIMIT :start, :limit";
				
        return DB::run($sql, ['start' => $start, 'limit' => self::$limit])->fetchAll();
    }
*/




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
				'item_source_title'		=> $data['item_source_title'],
				'item_source_url'		=> $data['item_source_url'],
                'item_slug'             => $data['item_slug'],
				'item_published'  		=> $item_published,
                'item_user_id'          => self::container()->user()->id(),
            ];
		
		
        $sql = "INSERT INTO items(item_title, 
                            item_content, 
							item_note,
							item_source_title,
							item_source_url,
                            item_slug,
                            item_published,
                            item_user_id) 
                            
                       VALUES(:item_title, 
                       :item_content, 
					   :item_note,
					   :item_source_title,
					:item_source_url,
                       :item_slug,
                       :item_published,
                       :item_user_id)";

        DB::run($sql, $params);

        $item_id =  DB::run("SELECT LAST_INSERT_ID() as item_id")->fetch();

        return $item_id;
    }

    public static function edit($data)
    {
		$item_published = $data['item_published'] == 'on' ? 1 : 0;
		
		$params =  [
				'item_id'           	=> $data['item_id'],
                'item_title'            => $data['item_title'],
                'item_content'          => $data['item_content'],
				'item_note'				=> $data['item_note'],
				'item_source_title'		=> $data['item_source_title'],
				'item_source_url'		=> $data['item_source_url'],
                'item_slug'             => $data['item_slug'],
				'item_thumb_img' 		=> $data['item_thumb_img'] ?? NULL,
				'item_modified' 		=> date("Y-m-d H:i:s"),
				'item_published'  		=> $item_published,
                'item_user_id'          => self::container()->user()->id(),

            ];
			
        $sql = "UPDATE items 
                    SET item_title		= :item_title, 
                    item_content        = :item_content,
					item_note        	= :item_note,
					item_source_title	= :item_source_title,
					item_source_url		= :item_source_url,
                    item_slug           = :item_slug,
					item_thumb_img 		= :item_thumb_img,
					item_modified 		= :item_modified,
                    item_published      = :item_published,
                    item_user_id        = :item_user_id
                        WHERE item_id   = :item_id";

        return  DB::run($sql, $params);
    }


    public static function addItemFacets(array $rows, int $item_id)
    {
        self::deleteRelation($item_id, 'item');

        foreach ($rows as $row) {
            $facet_id   = $row['id'];
            $sql = "INSERT INTO facets_items_relation (relation_facet_id, relation_item_id) 
                        VALUES ($facet_id, $item_id)";

            DB::run($sql);
        }

 
        return true;
    }

    public static function deleteRelation(int $id, string $type)
    {
        $sql = "DELETE FROM facets_items_relation WHERE relation_item_id = $id";
        if ($type == 'topic') {
            $sql = "DELETE FROM facets_relation WHERE facet_parent_id = $id";
        } elseif ($type == 'matching') {
            $sql = "DELETE FROM facets_matching WHERE matching_parent_id = $id";
        }

        return DB::run($sql);
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
                    item_published,
                    item_user_id,
                    item_ip,
                    item_content,
					item_note,
					item_source_title,
					item_source_url,
                    item_content_img,
                    item_thumb_img,
                    item_is_deleted,
                    u.id,
                    u.login,
                    u.avatar,
					u.created_at

                        FROM Items
                        LEFT JOIN users u ON u.id = item_user_id
                            WHERE $sort";

        $data = ['params' => $params];

        return DB::run($sql, $data)->fetch();
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

}
