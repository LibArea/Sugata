<?php

declare(strict_types=1);

namespace App\Controllers;

use Hleb\Base\Controller;
use App\Models\ItemModel;
use Meta, Html;

class HomeController extends Controller
{
	static $limit = 25;
	
    public function index(): void
    {
        // $childrens, $category_id, $page, $sort, $limit
        $items      = ItemModel::feedItem(false, false, Html::pageNumber(), self::$limit, 'all');
        $pagesCount = ItemModel::feedItemCount(false, false, 'all');

        render(
            '/index',
            [
                'meta'  => Meta::get(__('app.tools')),
                'data'  => [
                    'sheet'         => 'facts',
                    'items'         => $items,
                    'count'         => $pagesCount,
                    'pagesCount'    => ceil($pagesCount / self::$limit),
                    'pNum'          => Html::pageNumber(),
                ]
            ]
        );
    }


}
