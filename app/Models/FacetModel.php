<?php

declare(strict_types=1);

namespace App\Models;

use Hleb\Base\Model;
use Hleb\Static\DB;


class FacetModel extends Model
{
    /**
     * Cell information (id, slug)
     * Информация по фасету (id, slug)
     *
     * @param string|integer $params
     * @param string $name
     * @return mixed
     */
    public static function get(string|int $params, string $name, string $type = 'category')
    {
       // Except for the staff and if it is not allowed in the catalog
        // Кроме персонала и если он не разрешен в каталоге
        $display = 'facet_is_deleted = 0 AND';
        if (self::container()->user()->tl() == 10) $display = '';

        $sort = "facet_id = :params";
        if ($name == 'slug') $sort = "facet_slug = :params";


        $sql = "SELECT 
                    facet_id,
                    facet_title,
                    facet_description,
                    facet_type,
                    facet_info,
                    facet_slug,
					facet_path,
                    facet_img,
                    facet_cover_art,
                    facet_date,
                    facet_merged_id,
                    facet_top_level,
                    facet_user_id,
                    facet_tl,
                    facet_post_related,
                    facet_focus_count,
                    facet_count,
                    facet_is_deleted
                        FROM facets WHERE facet_type=:type AND $display $sort";

        return DB::run($sql, ['params' => $params, 'type' => $type])->fetch();
    }
	
    // All facets
    // Все фасеты
    public static function getFacetsAll(int $page = 1, int $limit = 1000, string $type = 'category')
    {
        $start  = ($page - 1) * $limit;
        $sql    = "SELECT 
                    facet_id,
                    facet_title,
                    facet_description,
                    facet_slug,
                    facet_img,
                    facet_user_id,
                    facet_top_level,
                    facet_focus_count,
                    facet_count,
                    signed_facet_id, 
                    signed_user_id,
                    facet_type,
					facet_path,
                    facet_is_deleted
                        FROM facets 
                            LEFT JOIN facets_signed ON signed_facet_id = facet_id
                                WHERE facet_type = :type ORDER BY facet_count DESC LIMIT :start, :limit";

        return DB::run($sql, ['start' => $start, 'limit' => $limit, 'type' => $type])->fetchAll();
    }
	
    /**
     * Theme Tree
     * Дерево тем
     *
     * @param string $type
     * @param string $sort
     */
    public static function getTree(string $type = 'category', string $sort = 'all'): false|array
    {
        $sort = $sort === 'ban' ? 'AND facet_is_deleted = 1' : '';

        $sql = "SELECT
                facet_id,
                facet_slug,
                facet_img,
                facet_title,
                facet_sort,
				facet_path,
                facet_type,
                facet_parent_id,
                facet_chaid_id,
				facet_seo_title,
				facet_info,
				facet_description,
                facet_is_deleted,
                rel.*
                    FROM facets 
                    LEFT JOIN
                    (
                        SELECT 
                            matching_parent_id,
                            GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS matching_list
                            FROM facets
                            LEFT JOIN facets_matching on facet_id = matching_chaid_id 
                            GROUP BY matching_parent_id
                        ) AS rel
                            ON rel.matching_parent_id = facet_id

                        LEFT JOIN facets_relation on facet_id = facet_chaid_id 
                            WHERE facet_type = :type $sort ORDER BY facet_sort DESC";

        return DB::run($sql, ['type' => $type])->fetchAll();
    }

    public static function breadcrumb(int $facet_id)
    {
        $sql = "with recursive
            n (facet_id, facet_path, facet_slug, facet_title, lvl) as (
                select facet_id, facet_path, facet_slug, facet_title, 1 from facets where facet_id = :id
         
         union all
            select c.facet_id, c.facet_path, c.facet_slug, c.facet_title, n.lvl + 1
                from n
                    join facets_relation r on r.facet_chaid_id = n.facet_id
                    join facets c on c.facet_id = r.facet_parent_id
        )
        select facet_id id, facet_path path, facet_slug link, facet_title name from n where lvl <= 5 ORDER BY lvl DESC";

        return DB::run($sql, ['id' => $facet_id])->fetchAll();
    }


    /**
     * Down the structure  (CHILDREN)
     * Вниз по структуре связанных деревьев (ДЕТИ)
     *
     * @param  int $facet_id
     */
    public static function getLowMatching(int $facet_id): false|array
    {
        $sql = "SELECT 
                    facet_id as id,
                    facet_title value,
                    facet_slug,
					facet_path,
                    facet_img,
                    facet_type,
                    matching_chaid_id,
                    matching_parent_id
                        FROM facets
                        LEFT JOIN facets_matching on facet_id = matching_chaid_id 
                        WHERE matching_parent_id = :facet_id";

        return DB::run($sql, ['facet_id' => $facet_id])->fetchAll();
    }
	
    // Down the structure of the main trees (CHILDREN)
    // Вниз по структуре основных деревьев (ДЕТИ)
    /**
     * @param  int $facet_id
     * @internal
     */
    public static function getLowLevelList(int $facet_id)
    {
        $sql = "SELECT 
                    facet_id id,
                    facet_title value,
                    facet_slug,
					facet_path,
                    facet_img,
                    facet_type,
                    facet_chaid_id,
                    facet_parent_id
                        FROM facets
                            LEFT JOIN facets_relation on facet_id = facet_chaid_id 
                                WHERE facet_parent_id = :facet_id";

        return DB::run($sql, ['facet_id' => $facet_id])->fetchAll();
    }
	
    public static function types()
    {
        return  DB::run('SELECT type_id, type_code, type_lang FROM facets_types')->fetchAll();
    }
	
    // Let's check the uniqueness of id
    // Проверим уникальность id
    public static function uniqueById(int $facet_id)
    {
        $sql = "SELECT facet_id, facet_slug, facet_type, facet_user_id, facet_is_deleted FROM facets WHERE facet_id = :id";

        return DB::run($sql, ['id' => $facet_id])->fetch();
    }
	
    public static function edit(array $params)
    {
        $sql = "UPDATE facets 
                    SET facet_title         = :facet_title,  
                    facet_description       = :facet_description, 
                    facet_info              = :facet_info, 
                    facet_slug              = :facet_slug, 
                    facet_user_id           = :facet_user_id, 
                    facet_top_level         = :facet_top_level, 
                    facet_post_related      = :facet_post_related, 
                    facet_type              = :facet_type,
					facet_is_comments 		= :facet_is_comments
                        WHERE facet_id      = :facet_id";

        return  DB::run($sql, $params);
    }
	
    public static function add(array $params)
    {
        $sql = "INSERT INTO facets(facet_title, 
                        facet_description, 
                        facet_slug, 
                        facet_img,
                        facet_user_id,
                        facet_type) 
                            VALUES(:facet_title, 
                                :facet_description, 
                                :facet_slug, 
                                :facet_img, 
                                :facet_user_id,
                                :facet_type)";

        DB::run($sql, $params);

        return  DB::run("SELECT LAST_INSERT_ID() as facet_id")->fetch();
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
	
    // Let's check the uniqueness of slug depending on the type of tree
    // Проверим уникальность slug в зависимости от типа дерева
    public static function uniqueSlug(string $facet_slug, string $facet_type)
    {
        $sql = "SELECT facet_slug, facet_type FROM facets WHERE facet_slug = :slug AND facet_type = :type";

        return DB::run($sql, ['slug' => $facet_slug, 'type' => $facet_type])->fetch();
    }

    public static function rebuildPath(int $facet_id, string $facet_path)
    {
        $sql = "UPDATE facets SET facet_path = :facet_path WHERE facet_id = :facet_id";

        return  DB::run($sql, ['facet_id' => $facet_id, 'facet_path' => $facet_path]);
    }	
	
    // Main trees
    // Основные деревья
    public static function addLowFacetRelation(array $rows, int $topic_id)
    {
        self::deleteRelation($topic_id, 'topic');

        foreach ($rows as $row) {
            $facet_id   = $row['id'];
            if ($topic_id == $row['id']) return true;
            $sql = "INSERT INTO facets_relation (facet_parent_id, facet_chaid_id) 
                        VALUES ($topic_id, $facet_id)";

            DB::run($sql);
        }

        return true;
    }
	
    // Cross -links
    // Перекрестные связи
    public static function addLowFacetMatching(array $rows, int $topic_id)
    {
        self::deleteRelation($topic_id, 'matching');

        foreach ($rows as $row) {
            $facet_id   = $row['id'];
            if ($topic_id == $row['id']) return true;
            $sql = "INSERT INTO facets_matching (matching_parent_id, matching_chaid_id) 
                        VALUES ($topic_id, $facet_id)";

            DB::run($sql);
        }

        return true;
    }

	// Children are down (up to level 5).
	// Дети вниз (до 5 уровня). 
    public static function childrenForFeed(int $facet_id): false|array
    {
        $sql = "with recursive
            n (facet_id, lvl) as (
                select facet_id,  1 from facets where facet_id = :id
         
         union all
            select c.facet_id, n.lvl + 1  
                from n
                    join facets_relation r on r.facet_parent_id = n.facet_id
						join facets c on c.facet_id = r.facet_chaid_id
        )
        select facet_id from n where lvl <= 5 ORDER BY lvl DESC";

        return DB::run($sql, ['id' => $facet_id])->fetchAll();
    }	
	
	
    /**
     * Getting subcategories based on nested sites
     * Получаем подкатегории с учетов вложенных сайтов
     *
     * @param integer $facet_id
     * @param string|bool $grouping
	 * @param array|bool $geo
     */
   public static function getChildrens(int $facet_id): false|array
    {


        $sql = "SELECT 
                  facet_id,
				  facet_path,
                  MAX(facet_title) as facet_title
             
                      FROM facets_relation 
                          LEFT JOIN facets on facet_id = facet_chaid_id 
                          LEFT JOIN facets_items_relation on facet_chaid_id = relation_facet_id 
                          LEFT JOIN items on item_id = relation_item_id 
						  

						  
                              WHERE facet_parent_id = :facet_id  GROUP BY facet_id";

        return DB::run($sql, ['facet_id' => $facet_id])->fetchAll();
    }
	
}
