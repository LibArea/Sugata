<?php

declare(strict_types=1);

namespace App\Models;

use Hleb\Base\Model;
use Hleb\Static\DB;

use S2\Rose\Storage\Database\PdoStorage;

class SearchModel extends Model
{
	public static function get(array $arr,  int $page, int $limit)
	{
		$results = [];
		foreach ($arr as  $ind => $row) {
			$results[$ind] = $row;
		}

		$idSite = "item_id = 0";
		if ($results) $idSite = "item_id IN (" . implode(',', $results ?? []) . ")";

		// https://dev.mysql.com/doc/refman/8.4/en/string-functions.html#function_field
		$orderBy = "";
		if ($results) $orderBy = "ORDER BY field(item_id, " . implode(',', $results ?? []) . ")";

		$start = ($page - 1) * $limit;

		$sql = "SELECT * ,
		rel.*,
		votes_item_user_id, votes_item_item_id,
			fav.tid, fav.user_id, fav.action_type 
		
				FROM items 
				 LEFT JOIN ( SELECT  
								relation_item_id,  
								GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list  
								FROM facets  
								LEFT JOIN facets_items_relation on facet_id = relation_facet_id  
									GROUP BY relation_item_id  
						) AS rel ON rel.relation_item_id = item_id  
		
							LEFT JOIN favorites fav ON fav.tid = item_id AND fav.user_id = 1 AND fav.action_type = 'website'
							LEFT JOIN votes_item ON votes_item_item_id = item_id AND votes_item_user_id = 1
						
								WHERE $idSite  $orderBy LIMIT :start, :limit";

		return DB::run($sql, ['start' => $start, 'limit' => $limit])->fetchAll();
	}

	public static function getSearchItemTiem(int $id)
	{
		$sql = "SELECT item_favicon_img as favicon, item_dzen, item_vk, item_telegram FROM items  WHERE item_id = :id";


		return DB::run($sql, ['id' => $id])->fetch();
	}


	public static function getSearchAll()
	{
		$sql = "SELECT 
		            facet_id, 
                    facet_path, 
                    facet_count, 
                    facet_title,
                    facet_info,
                    facet_img
                        FROM facets WHERE facet_type = 'category'";

		return DB::run($sql)->fetchAll();
	}

	public static function setSearchLogs(array $params)
	{
		$sql = "INSERT INTO search_logs(request, 
                            action_type, 
                            add_ip,
                            user_id, 
                            count_results) 
                               VALUES(:request, 
                                   :action_type, 
                                   :add_ip,
                                   :user_id, 
                                   :count_results)";

		DB::run($sql, $params);
	}

	public static function getSearchLogs(int $limit)
	{
		$sql = "SELECT 
                    request, 
                    action_type,
                    add_date,
                    add_ip,
                    user_id, 
                    count_results
                        FROM search_logs ORDER BY id DESC LIMIT :limit";

		return DB::run($sql, ['limit' => $limit])->fetchAll();
	}

	public static function getIndexAll()
	{
		$sql = "SELECT item_id, 
						item_title, 
						item_content, 
						item_modified,
						item_date,
						item_slug,
						rel.*
							FROM items 
								LEFT JOIN ( SELECT  
											relation_item_id,  
											GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list  
											FROM facets  
											LEFT JOIN facets_items_relation on facet_id = relation_facet_id  
												GROUP BY relation_item_id  
									) AS rel ON rel.relation_item_id = item_id  
										WHERE item_is_deleted = 0 AND item_published = 1";

		return DB::run($sql)->fetchAll();
	}

	public static function PdoStorage()
	{
		$database = config('database', 'db.settings.list');
		
		// Array ( [0] => mysql:host=localhost [1] => port=3306 [2] => dbname=lwiki [3] => charset=utf8mb4_general_ci [user] => root [pass] => [options] => Array ( ) )
		$db = $database['mysql.name'];
		
		$host = substr(strstr($db[0], '='), 1);
		$port = substr(strstr($db[1], '='), 1);
		$dbname = substr(strstr($db[2], '='), 1);

		$pdo = new \PDO('mysql:host=' . $host . ':' . $port  .  ';dbname=' . $dbname . ';charset=utf8mb4', $db['user'], $db['pass']);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		return new PdoStorage($pdo, 'search_index_');
	}
	
    public static function getLastIDContent()
    {
        $sql = "SELECT MAX(CAST(external_id AS SIGNED)) as id
					FROM search_index_toc";

        $lastId = DB::run($sql)->fetch();

        return $lastId['id'];
    }
	
    public static function newIndexContent($lastId)
    {
        $sql = "SELECT post_id, post_title, post_content, post_slug, post_type  
						FROM posts 
							WHERE 
								post_is_deleted = 0 AND post_tl = 0 AND post_draft = 0 AND post_type != 'page' AND post_id > :lastId";

        return DB::run($sql, ['lastId' => $lastId])->fetchAll();
    }
	
    public static function getFacetsAll()
    {
        $sql = "SELECT facet_id, facet_slug, facet_title,	facet_info
                        FROM facets 
							WHERE 
								facet_type = 'topic' AND facet_is_deleted = 0";

        return DB::run($sql)->fetchAll();
    }
	
    public static function getContentsAll()
    {
        $sql = "SELECT post_id, post_title, post_content, post_slug, post_type  
						FROM posts 
							WHERE 
								post_is_deleted = 0 AND post_tl = 0 AND post_draft = 0 AND post_type != 'page'";

        return DB::run($sql)->fetchAll();
    }
	
    public static function getSearch(int $page, int $limit, string $query)
    {
        $start  = ($page - 1) * $limit;
        $sql = "SELECT DISTINCT 
                item_id, 
                item_title as title, 
                item_slug, 
                item_content as content,
                rel.*
                    FROM facets_items_relation  
                    LEFT JOIN items ON relation_item_id = item_id 
                    LEFT JOIN ( SELECT  
                            relation_item_id,  
                            GROUP_CONCAT(facet_type, '@', facet_slug, '@', facet_title SEPARATOR '@') AS facet_list  
                            FROM facets  
                            LEFT JOIN facets_items_relation on facet_id = relation_facet_id  
                                GROUP BY relation_item_id  
                    ) AS rel ON rel.relation_item_id = item_id  
                       
                            WHERE item_is_deleted = 0 
                                AND MATCH(item_title, item_content) AGAINST (:qa) LIMIT :start, :limit";

        return DB::run($sql, ['qa' => $query, 'start' => $start, 'limit' => $limit])->fetchAll();
    }
	
    public static function getSearchCount(string $query)
    {
        $sql = "SELECT item_id FROM items WHERE item_is_deleted = 0 AND MATCH(item_title, item_content) AGAINST (:qa)";

        return DB::run($sql, ['qa' => $query])->rowCount();
    }
	
    public static function getSearchTags(null|string $query, string $type, int $limit)
    {
        $sql = "SELECT 
                    facet_slug slug, 
                    facet_title title
                        FROM facets WHERE facet_type = :type AND (facet_title LIKE :qa1 OR facet_slug LIKE :qa2) LIMIT :limit";

        return DB::run($sql, ['type' => $type, 'qa1' => "%" . $query . "%", 'qa2' => "%" . $query . "%", 'limit' => $limit])->fetchAll();
    }
}
