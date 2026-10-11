<?php

declare(strict_types=1);

namespace App\Models;

use Hleb\Base\Model;
use Hleb\Static\DB;

class FormModel extends Model
{
    /**
     * Find content for forms
     * Поиск контента для форм
     *
     * @param null|string $search
     * @param string $type
     */
    public static function get(null|string $search, string $type, null|string $root = null, int $excludeId = 0)
    {
        $field_id   = $type . '_id';
        $field_name = '';
        $sql = '';

        switch ($type) {
            case 'post':
                $field_name = 'post_title';
                $sql = "SELECT post_id, post_title FROM posts WHERE post_title LIKE :post_title AND post_is_deleted = 0 AND post_hidden = 0 AND post_tl = 0 AND post_type = 'post' ORDER BY post_id DESC LIMIT 500";
                break;

            case 'user':
            case 'team':
                $field_id = 'id';
                $field_name = 'login';
                $sql = "SELECT id, login, trust_level, activated FROM users WHERE activated = 1 AND login LIKE :login";
                if ($type == 'team') {
                    $sql .= " AND id !=" . self::container()->user()->id();
                }
                break;

            case 'poll':
                $field_name = 'poll_title';
                $sql = "SELECT poll_id, poll_title FROM polls WHERE poll_title LIKE :poll_title AND poll_is_deleted = 0";

                if (!self::container()->user()->admin()) {
                    $sql .= " AND poll_user_id = " . self::container()->user()->id();
                }
                break;

            case 'fact':
                $field_id = 'item_id';
                $field_name = 'item_title';
                $sql = "SELECT i.item_id, i.item_title, i.item_slug, i.item_type, rel.facet_list
                        FROM items i
                        LEFT JOIN (
                            SELECT
                                relation_item_id,
                                GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list
                            FROM facets
                            LEFT JOIN facets_items_relation ON facet_id = relation_facet_id
                            GROUP BY relation_item_id
                        ) AS rel ON rel.relation_item_id = i.item_id
                        WHERE i.item_title LIKE :item_title
                          AND i.item_published = 1 AND i.item_is_deleted = 0 AND i.item_type = 'fact'
                        ORDER BY i.item_id DESC LIMIT 200";
                break;

            // Поиск для поля «Связанное»: у факта — статьи, у статьи — факты.
            // Целевой тип приходит в $root ('fact'|'article'); без него — оба типа.
            case 'item':
			
			
			
 	
                $field_id = 'item_id';
                $field_name = 'item_title';
                // Поле «Связанное»: целевой тип приходит в $root (article — для факта, fact — для статьи)
                $kindCondition = '';
                if ($root === 'fact' || $root === 'article') {
                    $kindCondition = "AND i.item_type = :item_kind";
                }
                // Исключаем текущую запись (саму статью/факт) из выдачи
                $excludeCondition = $excludeId > 0 ? " AND i.item_id != :exclude_id" : '';
                $sql = "SELECT i.item_id, i.item_title, i.item_slug, i.item_type, rel.facet_list
                        FROM items i
                        LEFT JOIN (
                            SELECT
                                relation_item_id,
                                GROUP_CONCAT(facet_id, '@', facet_type, '@', facet_path, '@', facet_title SEPARATOR '@') AS facet_list
                            FROM facets
                            LEFT JOIN facets_items_relation ON facet_id = relation_facet_id
                            GROUP BY relation_item_id
                        ) AS rel ON rel.relation_item_id = i.item_id
                        WHERE i.item_title LIKE :item_title
                          AND i.item_published = 1 AND i.item_is_deleted = 0 $kindCondition$excludeCondition
                        ORDER BY i.item_id DESC LIMIT 200";
                $bind = [$field_name => "%" . $search . "%"];
                if ($kindCondition !== '') {
                    $bind['item_kind'] = $root;
                }
                if ($excludeId > 0) {
                    $bind['exclude_id'] = $excludeId;
                }
                break;

            case 'association':
				$field_id = 'id';
                $field_name = 'name';
                $sql = "SELECT id, name FROM geo_associations WHERE name LIKE :name";
                break;

            case 'country':
				$field_id = 'id';
                $field_name = 'name';
                $sql = "SELECT id, name FROM geo_countries WHERE name LIKE :name";
                break;
            
			case 'district':
				$field_id = 'id';
                $field_name = 'name';
                $sql = "SELECT id, name FROM geo_districts WHERE name LIKE :name";
                break;

			case 'region':
				$field_id = 'id';
                $field_name = 'name';
                $sql = "SELECT id, name FROM geo_regions WHERE name LIKE :name";
                break;

			case 'city':
				$field_id = 'id';
                $field_name = 'name';
                $sql = "SELECT id, name FROM geo_cities WHERE name LIKE :name";
                break;

            default:
                $field_id = 'facet_id';
                $field_name = 'facet_title';
                $condition = '';

                if (!self::container()->user()->admin() && $type == 'blog') {
                    $blog = FacetModel::getFacetsUser('blog');
                    $teams = FacetModel::getTeamFacets('blog');

                    $resultUsers = [];
                    foreach (array_merge($teams, $blog) as $ind => $row) {
                        $resultUsers[$ind] = $row['facet_id'];
                    }

                    $condition =  "AND facet_id IN(" . implode(',', $resultUsers ?? []) . ")";
                }

                // Фильтр по корню дерева категорий: факты (facts) или статьи (articles)
                $rootCondition = '';
                if ($type === 'category' && $root) {
                    $root = trim($root, '/');
                    if ($root !== '') {
                        $rootCondition = "AND (facet_path = :root OR facet_path LIKE :root_like) ";
                    }
                }

      $tl = self::container()->user()->tl();
      $sql = "SELECT facet_id, facet_title, facet_type, facet_path FROM facets 
              WHERE facet_title LIKE :facet_title AND facet_tl <= $tl AND facet_is_deleted = 0 AND facet_type = '$type' $rootCondition $condition ORDER BY facet_count DESC LIMIT 200";
      $bind = [$field_name => "%" . $search . "%"];
      if ($rootCondition !== '') {
          $bind['root']      = $root;
          $bind['root_like'] = $root . '/%';
      }
                break;
        }

        $lists = DB::run($sql, $bind ?? [$field_name => "%" . $search . "%"])->fetchAll();

        $response = [];
        foreach ($lists as $list) {
            $row = array(
                "id"    => $list[$field_id],
                "value" => $list[$field_name],
            );

            // Показываем полный путь категории, чтобы различать факты/статьи
            if ($type == 'category' && !empty($list['facet_path'])) {
                $row['value'] = $list['facet_path'] . ' / ' . $list[$field_name];
            }

            // Для фактов добавляем готовый URL: /path/slug.html
            if ($type == 'fact' && !empty($list['facet_list']) && !empty($list['item_slug'])) {
                $chunks = array_chunk(preg_split('/(@)/', (string)$list['facet_list']), 4);
                $path = trim($chunks[0][2] ?? '', '/');
                if ($path !== '') {
                    $row['url'] = '/' . $path . '/' . $list['item_slug'] . '.html';
                }
            }

            $response[] = $row;
        }

        return json_encode($response);
    }
}
