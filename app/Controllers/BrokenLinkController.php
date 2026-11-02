<?php

namespace App\Controllers;

use Hleb\Base\Controller;
use App\Models\BrokenLinkModel;
use App\Models\TermModel;
use Meta, Html;

class BrokenLinkController extends Controller
{
    public function index()
    {
        $page  = max(1, Html::pageNumber());
        $limit = 50;

        // Секция «Востребованные термины» (несозданные статьи) — из реестра терминов.
        $terms      = TermModel::wanted($page, $limit);
        $termsTotal = TermModel::wantedCount();

        // Секция «Жёсткие битые ссылки» — только URL-ссылки ([текст](/путь/slug.html)),
        // чтобы не дублировать вики-термины, показанные выше.
        $urls      = BrokenLinkModel::allUrls($page, $limit);
        $urlsTotal = BrokenLinkModel::countUrls();

        render(
            '/admin/pages/broken-links',
            [
                'meta'  => Meta::get(__('app.broken_links')),
                'data'  => [
                    'sheet'       => 'broken_links',
                    'terms'       => $terms,
                    'termsTotal'  => $termsTotal,
                    'urls'        => $urls,
                    'urlsTotal'   => $urlsTotal,
                    'pagesCount'  => max(1, (int)ceil(max($termsTotal, $urlsTotal) / $limit)),
                    'pNum'        => $page,
                ]
            ]
        );
    }
}