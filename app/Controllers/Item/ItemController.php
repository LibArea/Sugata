<?php

declare(strict_types=1);

namespace App\Controllers\Item;

use Hleb\Base\Controller;

use Hleb\Static\Request;
use App\Models\ItemModel;
use Meta, Msg, Html, Validator;

class ItemController extends Controller
{
    static $limit = 10;
	
    public function index()
    {
        // $childrens, $category_id, $page, $sort, $limit
        $items      = ItemModel::feedItem(false, false, Html::pageNumber(), self::$limit, 'all');
        $pagesCount = ItemModel::feedItemCount(false, false, 'all');

        render(
            '/content/items/all',
            [
                'meta'  => Meta::get(__('app.tools')),
                'data'  => [
                    'sheet'         => 'facts',
                    'items'         => $items,
                    'count'			=> $pagesCount,
                    'pagesCount'    => ceil($pagesCount / self::$limit),
                    'pNum'			=> Html::pageNumber(),
                ]
            ]
        );
    }

    public function view()
    {
        $id = Request::param('id')->asInt();
        $item = ItemModel::getItem($id, 'id');

        render(
            '/content/items/view',
            [
                'meta'  => Meta::get(__('app.tools')),
                'data'  => [
                    'sheet'         => 'view',
                    'item'         	=> $item,

                ]
            ]
        );
    }
}
