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
     * Разделы для центральной страницы-портала.
     * Каждый корневой раздел (facts, articles, web и т.п.) — это «модуль»,
     * его прямые дети — темы-ссылки внутри модуля.
     * Служебный раздел info на главной не показываем.
     */
    public static function sections(): array
    {
        $nav = Html::builder(null, 0, self::tree());

        $sections = [];
        foreach ($nav as $i => $cat) {
            if ($cat['level'] != 0) {
                continue;
            }

            // Служебная категория — не показываем
            if (trim($cat['facet_path'] ?? '', '/') === 'info') {
                continue;
            }

            // Прямые дети текущего корня (темы уровня 1)
            $children = [];
            for ($j = $i + 1; $j < count($nav) && $nav[$j]['level'] > 0; $j++) {
                if ($nav[$j]['level'] == 1 && $nav[$j]['facet_parent_id'] == $cat['facet_id']) {
                    $children[] = [
                        'title' => $nav[$j]['facet_title'],
                        'path'  => trim($nav[$j]['facet_path'], '/'),
                    ];
                }
            }

            $sections[] = [
                'title' => $cat['facet_title'],
                'path'  => trim($cat['facet_path'], '/'),
                'help'  => trim((string)($cat['facet_description'] ?? '')),
                'sub'   => $children,
            ];
        }

        return $sections;
    }

    /**
     * Шаблон страницы отдельной записи (факт/статья/сайт/страница).
     * Централизованный выбор — используется и static, и preview.
     *
     * @param array  $item  запись
     * @param string $dir2  третий сегмент facet_list (@path@dir2) или '' если нет
     */
    public static function itemTemplate(array $item, string $dir2 = ''): string
    {
        return match (($item['item_type'] ?? 'fact')) {
            'article' => '/site/pages/articles/view.php',
            'page'    => '/site/pages/page.php',
            'site'     => '/site/pages/sites/view.php',
            default   => ($dir2 === 'info') ? '/site/pages/page.php' : '/site/pages/facts/view.php',
        };
    }

    /**
     * Шаблон страницы категории (список записей).
     * Распознаёт корень articles (статьи) и web (сайты) по пути.
     */
    public static function categoryTemplate(string $facetPath): string
    {
        $path = trim((string)$facetPath, '/');

        if ($path === 'articles' || str_starts_with($path, 'articles/')) {
            return '/site/pages/articles/index.php';
        }

        if ($path === 'web' || str_starts_with($path, 'web/')) {
            return '/site/pages/sites/index.php';
        }

        return '/site/pages/facts/index.php';
    }

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