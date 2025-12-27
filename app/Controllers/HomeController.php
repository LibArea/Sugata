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
		if ($this->container->user()->id()) {
			redirect(url('facts', ['type' => 'my']));
		}
		
        render(
            '/index',
            [
                'meta'  => Meta::get(__('app.admin')),
                'data'  => []
            ]
        );
    }

    public function facts($type): void
    {
        // $childrens, $category_id, $page, $sort, $limit, $type: all, my, moderation
        $items      = ItemModel::feedItem(false, false, Html::pageNumber(), self::$limit, $type);
        $pagesCount = ItemModel::feedItemCount(false, false, $type);

        render(
            '/content/items/index',
            [
                'meta'  => Meta::get(__('app.admin')),
                'data'  => [
                    'sheet'         => $type,
                    'items'         => $items,
                    'count'         => $pagesCount,
                    'pagesCount'    => ceil($pagesCount / self::$limit),
                    'pNum'          => Html::pageNumber(),
                ]
            ]
        );
    }
	

}
