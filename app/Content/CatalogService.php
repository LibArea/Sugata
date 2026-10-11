<?php

declare(strict_types=1);

namespace App\Content;

use App\Models\{FacetModel, ItemModel};
use Html;

/**
 * Единая логика формирования данных страницы категории (каталога).
 * Используется и для предпросмотра, и для статической сборки, чтобы
 * не дублировать код в двух местах.
 */
class CatalogService
{
    public const PER_PAGE = 20;

    /** Кэш дерева на время одного запроса/запуска (sidebar + sections используют один getTree). */
    private static ?array $treeCache = null;

    /**
     * Дерево категорий из базы (только живые), с кэшем на запрос.
     */
    private static function tree(): array
    {
        if (self::$treeCache !== null) {
            return self::$treeCache;
        }

        $tree = FacetModel::getTree('category', 'all');
        $tree = array_values(array_filter(
            $tree,
            fn($f) => (int)($f['facet_is_deleted'] ?? 0) !== 1
                && trim($f['facet_path'] ?? '', '/') !== 'info' // служебная — не показываем
        ));

        return self::$treeCache = $tree;
    }

    /**
     * Дерево категорий для сайдбара (из базы, только живые).
     * Подготавливает данные в контроллере — шаблон только рендерит.
     */
    public static function sidebar(): array
    {
        return Html::builder(null, 0, self::tree());
    }

    /**
     * Категории для центральной страницы (раздел «Категория»).
     * Основной контейнер — facts (его дети = темы уровня 1).
     * Статьи (articles) на главной не выводим — у них свой корень.
     */
    public static function sections(): array
    {
        $nav  = Html::builder(null, 0, self::tree());

        // Определяем корень-контейнер
        $rootPath = null;
        foreach ($nav as $c) {
            if ($c['level'] == 0 && ($c['facet_path'] === 'facts' || trim($c['facet_path'], '/') === 'facts')) {
                $rootPath = 'facts';
                break;
            }
        }

        // Если корня-контейнера нет — секции = все корни
        $sectionLevel = ($rootPath === null) ? 0 : 1;

        $sections = [];
        foreach ($nav as $i => $cat) {
            if ($cat['level'] != $sectionLevel) continue;

            // Подкатегории — прямые дети уровня sectionLevel+1
            $children = [];
            for ($j = $i + 1; $j < count($nav) && $nav[$j]['level'] > $sectionLevel; $j++) {
                if ($nav[$j]['level'] == $sectionLevel + 1 && $nav[$j]['facet_parent_id'] == $cat['facet_id']) {
                    $children[] = [
                        'title' => $nav[$j]['facet_title'],
                        'path'  => trim($nav[$j]['facet_path'], '/'),
                    ];
                }
            }

            $help = (empty($children)) ? trim((string)($cat['facet_description'] ?? '')) : '';

            $sections[] = [
                'title' => $cat['facet_title'],
                'path'  => trim($cat['facet_path'], '/'),
                'help'  => $help,
                'sub'   => $children,
            ];
        }

        return $sections;
    }

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
            $childrens[$id]['facet_count'] = ItemModel::feedItemCount($childrenFacet, $row['facet_id'], 'all_site');
        }

        $items = ItemModel::feedItem($childrenForFeed, $facetId, $page, self::PER_PAGE, 'all_site');
        $total = ItemModel::feedItemCount($childrenForFeed, $facetId, 'all_site');

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