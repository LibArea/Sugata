<?php

declare(strict_types=1);

namespace App\Controllers;

use Hleb\Base\Controller;
use App\Models\{ItemModel, FacetModel};
use Meta, Html;

class ViewingController extends Controller
{
    static $limit = 10;

    public function index(): void
    {
        render(
            '/content/viewing-home',
            [
                'meta'  => Meta::home(),
                'data'  => [
                    'sheet'    => 'home',
                    'items'    =>  ItemModel::feedItem(false, false, 1, 5),
                ]
            ]
        );
    }

    public function viewingHome(): void
    {
        insert(
            '/templates/home',
            [
                'meta'  => Meta::home(),
                'data'  => [
                    'sheet'    => 'home',
                    'items'    =>  ItemModel::feedItem(false, false, 1, 5),
                    'tree'    => Html::builder(0, 0, FacetModel::getTree()),
                ]
            ]
        );
    }
}
