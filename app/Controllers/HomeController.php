<?php

declare(strict_types=1);

namespace App\Controllers;

use Hleb\Base\Controller;
use Hleb\Static\Request;
use App\Models\{ItemModel, FacetModel};
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
	
	public function dir()
    {
		$category = $this->checkRoute();

		$childrenForFeed =  FacetModel::childrenForFeed($category['facet_id']);
		
        $items      = ItemModel::feedItem($childrenForFeed, $category['facet_id'], Html::pageNumber(),  self::$limit);
        $pagesCount = ItemModel::feedItemCount($childrenForFeed,  $category['facet_id']);

		$tree = FacetModel::breadcrumb($category['facet_id']);

		$childrens = FacetModel::getChildrens($category['facet_id']); // отображение категорий дети 1 уровня
		
		$results = [];
		foreach ($childrens as $id => $row) {
			$childrenFacet =  FacetModel::childrenForFeed($row['facet_id']);
			$childrens[$id]['facet_count'] = ItemModel::feedItemCount($childrenFacet,  $row['facet_id']);
		}

        $category['facet_img'] = '';

        return render(
            'content/category',
            [
                'meta'  => Meta::category($category),
                'data'  => [
					'sheet' 			=> 'dir',


                    'count'             => $pagesCount,
                    'pagesCount'        => ceil($pagesCount / self::$limit),
                    'pNum'              => Html::pageNumber(),
                    'items'             => $items,
                    'category'          => $category, // текущая категория
                    'childrens'         => $childrens,

                    'breadcrumb'        => Html::breadcrumbDir($tree),

                    'low_matching'      => FacetModel::getLowMatching($category['facet_id']), // связанные деревья

                ]
            ]
        ); 
    }
	
	public function checkRoute()
	{
		$data = Request::getUri()->getPath();
		$element = explode("/", $data);

		$facet_slug = end($element);		
		$category = FacetModel::checkSlug($facet_slug);
		
		if (empty($category['facet_path'])) {
			Msg::redirect(__('msg.string_length', ['name' => 'facet_path']), 'success', url('admin.tools'));
			
			notEmptyOrView404([]);
		}
     
		return $category;
	}
}
