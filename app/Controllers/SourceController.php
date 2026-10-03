<?php

namespace App\Controllers;

use Hleb\Base\Controller;
use Hleb\Static\Request;
use App\Models\SourceModel;
use Meta, Html;

class SourceController extends Controller
{
    public function index()
    {
        $page   = max(1, Html::pageNumber());
        $limit  = 50;
        $tab    = Request::get('tab')->value() ?? 'all';
        $stats  = SourceModel::stats();

        $sources = [];
        $factsNoSource = [];
        $total = 0;

        if ($tab === 'errors') {
            $sources = SourceModel::errors($page, $limit);
            $total   = SourceModel::errorsCount();
        } elseif ($tab === 'nosource') {
            $factsNoSource = SourceModel::factsWithoutSource($page, $limit);
            $total         = SourceModel::factsWithoutSourceCount();
        } else {
            $sources = SourceModel::all($page, $limit);
            $total   = SourceModel::count();
        }

        // Для всех вкладок с источниками подтягиваем связанные факты
        foreach ($sources as $key => $source) {
            $sources[$key]['facts'] = SourceModel::facts((int)$source['id']);
        }

        render(
            '/content/sources',
            [
                'meta'  => Meta::get(__('app.sources')),
                'data'  => [
                    'sheet'         => 'sources',
                    'tab'           => $tab,
                    'sources'       => $sources,
                    'factsNoSource' => $factsNoSource,
                    'stats'         => $stats,
                    'errorsCount'   => SourceModel::errorsCount(),
                    'noSourceCount' => SourceModel::factsWithoutSourceCount(),
                    'pagesCount'    => max(1, (int)ceil($total / $limit)),
                    'pNum'          => $page,
                ]
            ]
        );
    }
}