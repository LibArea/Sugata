<?php

declare(strict_types=1);

namespace App\Content;

use App\Models\{FacetModel, ItemModel};

/**
 * Единая логика формирования данных страницы категории (каталога).
 * Используется и для предпросмотра, и для статической сборки, чтобы
 * не дублировать код в двух местах.
 */
class CatalogService
{
    public const PER_PAGE = 20;

    /**
     * Данные для страницы категории.
     *
     * @param int    $facetId
     * @param int    $page
     * @param string $mod  'static' | 'preview'
     */
    public static function data(int $facetId, int $page = 1, string $mod = 'static'): array
    {
        $childrenForFeed = FacetModel::childrenForFeed($facetId);
        $childrens       = FacetModel::getChildrens($facetId);

        foreach ($childrens as $id => $row) {
            $childrenFacet = FacetModel::childrenForFeed($row['facet_id']);
            $childrens[$id]['facet_count'] = ItemModel::feedItemCount($childrenFacet, $row['facet_id']);
        }

        $items = ItemModel::feedItem($childrenForFeed, $facetId, $page, self::PER_PAGE, 'all');
        $total = ItemModel::feedItemCount($childrenForFeed, $facetId, 'all');

        // Служебные страницы (item_type = page) выводим наравне с фактами,
        // иначе раздел "Информация" (info) останется пустым.
        $facet = FacetModel::get($facetId, 'id');
        if ($facet && $facet['facet_path'] === 'info') {
            $pages = ItemModel::feedItem($childrenForFeed, $facetId, 1, 50, 'page');
            if ($page === 1) {
                $items = array_merge($pages, $items);
            }
            $total += (int)ItemModel::feedItemCount($childrenForFeed, $facetId, 'page');
        }

        return [
            'items'      => $items,
            'childrens'  => $childrens,
            'pNum'       => $page,
            'pagesCount' => max(1, (int)ceil($total / self::PER_PAGE)),
            'mod'        => $mod,
        ];
    }
}