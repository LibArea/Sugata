<?php

namespace App\Controllers;

use Hleb\Base\Controller;
use App\Models\BrokenLinkModel;
use Meta, Html;

class BrokenLinkController extends Controller
{
    public function index()
    {
        $page   = max(1, Html::pageNumber());
        $limit  = 50;
        $links  = BrokenLinkModel::all($page, $limit);
        $total  = BrokenLinkModel::count();

        render(
            '/content/broken-links',
            [
                'meta'  => Meta::get(__('app.broken_links')),
                'data'  => [
                    'sheet'         => 'broken_links',
                    'links'         => $links,
                    'total'         => $total,
                    'pagesCount'    => max(1, (int)ceil($total / $limit)),
                    'pNum'          => $page,
                ]
            ]
        );
    }
}