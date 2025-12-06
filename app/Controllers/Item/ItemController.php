<?php

declare(strict_types=1);

namespace App\Controllers\Item;

use Hleb\Base\Controller;

use Hleb\Static\Request;
use App\Models\{ItemModel, SearchModel};
use Meta, Msg, Html, Validator;

use S2\Rose\Entity\ExternalId;

class ItemController extends Controller
{
    static $limit = 25;
	
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

		Html::pageNumber();
		
		/**
	     * Рекомендованный контент (см. getSimilar())
		 * @param ExternalId $externalId An id of indexed item to search other similar items
		 * @param bool       $includeFormatting Switch the snippets to HTML formatting if available
		 * @param int|null   $instanceId Id of instance where to search these similar items
		 * @param int        $minCommonWords Lower limit for common words. The less common words,
		 *                                   the more items are returned, but among them the proportion
		 *                                   of irrelevant items is increasing.
		 * @param int        $limit
		 */
		$storage = SearchModel::PdoStorage();
        $similar = $storage->getSimilar(new ExternalId($item['item_id'], 1), false, 1, 3, 3);

        render(
            '/content/items/view',
            [
                'meta'  => Meta::get(__('app.tools')),
                'data'  => [
                    'sheet'         => 'view',
                    'item'         	=> $item,
					'similar' 		=> $similar,
                ]
            ]
        );
    }
}
