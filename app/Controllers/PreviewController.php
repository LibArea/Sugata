<?php

declare(strict_types=1);

namespace App\Controllers;

use Hleb\Base\Controller;
use Hleb\Static\Request;
use App\Models\{FacetModel, ItemModel, SearchModel};
use S2\Rose\Entity\ExternalId;
use Html, Meta, Parser;

class PreviewController extends Controller
{
    public function home(): void
    {
        // Админская страница: общая шапка/навигация + iframe с реальным видом сайта.
        render(
            '/content/preview',
            [
                'meta'  => Meta::get(__('app.view')),
                'data'  => [
                    'sheet' => 'view',
                ]
            ]
        );
    }

    // Рендер сайта для iframe (как после сборки)
    public function site(): void
    {
        $items = ItemModel::feedItem(false, false, 1, 5, 'main');
        $this->response()->setBody(view('/templates/home.php', [
            'meta' => Meta::home(),
            'items' => $items,
            'sections' => \App\Content\CatalogService::sections(),
            'preview' => true,
        ]));
    }

    public function index(): void
    {
        $requestPath = trim(Request::getUri()->getPath(), '/');
        $prefix = 'mod/admin/preview/';

        if (!str_starts_with($requestPath, $prefix)) {
            notEmptyOrView404([]);
            return;
        }

        $previewPath = trim(substr($requestPath, strlen($prefix)), '/');
        if ($previewPath === '') {
            notEmptyOrView404([]);
            return;
        }

        if (str_ends_with($previewPath, '.html')) {
            $articlePath = substr($previewPath, 0, -5);
            $separator = strrpos($articlePath, '/');
            if ($separator === false) {
                notEmptyOrView404([]);
                return;
            }

            $facetPath = substr($articlePath, 0, $separator);
            $slug = substr($articlePath, $separator + 1);
            $item = ItemModel::getPublishedByFacetPath($facetPath, $slug);
            if (empty($item)) {
                notEmptyOrView404([]);
                return;
            }

            $facet = FacetModel::getByPath($facetPath);
            if (empty($facet)) {
                notEmptyOrView404([]);
                return;
            }

            $dir = [$facet['facet_id'], 'category', $facetPath];
            $breadcrumb = Html::breadcrumbDir(FacetModel::breadcrumb((int)$facet['facet_id']), 'preview');
            $similar = SearchModel::PdoStorage()->getSimilar(new ExternalId($item['item_id'], 1), false, 1, 3, 3);
            $template = $facetPath === 'info' ? '/templates/page.php' : '/templates/view.php';

            $this->response()->setBody(view($template, [
                'item' => $item,
                'similar' => $similar,
                'dir' => $dir,
                'meta' => Meta::view($item, $facetPath, Parser::miniature($item['item_content'])),
                'breadcrumb' => $breadcrumb,
                'faq' => \App\Models\FaqModel::forItem((int)$item['item_id']),
                'sideNav' => \App\Content\CatalogService::sidebar(),
                'preview' => true,
            ]));
            return;
        }

        $facetPath = preg_replace('#/index\.html$#', '', $previewPath);
        // Статический пейджинг: /preview/catalog/page-2.html
        $pNum = 1;
        if (preg_match('#^(.+)/page-(\d+)\.html$#', $facetPath, $pm)) {
            $facetPath = $pm[1];
            $pNum = (int)$pm[2];
        }

        $facet = FacetModel::getByPath($facetPath);
        if (empty($facet)) {
            notEmptyOrView404([]);
            return;
        }

        $catalog = \App\Content\CatalogService::data((int)$facet['facet_id'], $pNum, 'preview');

        $this->response()->setBody(view('/templates/index.php', [
            'items' => $catalog['items'],
            'breadcrumb' => Html::breadcrumbDir(FacetModel::breadcrumb((int)$facet['facet_id']), 'preview'),
            'childrens' => $catalog['childrens'],
            'facet' => $facet,
            'meta' => Meta::category($facet),
            'pNum' => $catalog['pNum'],
            'pagesCount' => $catalog['pagesCount'],
            'sideNav' => \App\Content\CatalogService::sidebar(),
            'preview' => true,
        ]));
    }
}
