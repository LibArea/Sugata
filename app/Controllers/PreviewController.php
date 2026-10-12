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
public function site(): void
    {
        $items = ItemModel::feedItem(false, false, 1, 5, 'main');
        $this->response()->setBody(view('/site/pages/home.php', [
            'meta' => Meta::home(),
            'items' => $items,
            'featuredBig' => ItemModel::getFeaturedBig(),
            'bestFact' => ItemModel::getBestFact(),
            'didYouKnow' => ItemModel::getFactsDidYouKnow(5),
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
            $backlinks = \App\Models\BacklinkModel::referring((int)$item['item_id'], $facetPath, $slug);
            $template = match (($item['item_type'] ?? 'fact')) {
                'article' => '/site/pages/articles/view.php',
                'page'    => '/site/pages/page.php',
                default   => $facetPath === 'info' ? '/site/pages/page.php' : '/site/pages/facts/view.php',
            };

            $this->response()->setBody(view($template, [
                'item' => $item,
                'similar' => $similar,
                'related' => ItemModel::getRelatedItems((int)$item['item_id'], 'article'),
                'dir' => $dir,
                'meta' => Meta::view($item, $facetPath, Parser::miniature($item['item_content'])),
                'breadcrumb' => $breadcrumb,
                'faq' => \App\Models\FaqModel::forItem((int)$item['item_id']),
                'backlinks' => $backlinks,
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

        // Категория статей (корень articles) — свой шаблон списка
        $isArticles = str_starts_with(trim($facetPath, '/'), 'articles/') || trim($facetPath, '/') === 'articles';
        $indexTemplate = $isArticles ? '/site/pages/articles/index.php' : '/site/pages/facts/index.php';

        $this->response()->setBody(view($indexTemplate, [
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
