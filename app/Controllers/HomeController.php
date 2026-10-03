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
        // В админке — всегда админская обёртка и админский дизайн.
        // Реальный вид сайта (как после сборки) смотрим только в предпросмотре (/mod/admin/preview).
        if ($type === 'all') {
            $this->factsAll();
            return;
        }

        // $childrens, $category_id, $page, $sort, $limit, $type: my, moderation
        $items      = ItemModel::feedItem(false, false, Html::pageNumber(), self::$limit, $type);
        $pagesCount = ItemModel::feedItemCount(false, false, $type);

        render(
            '/content/index',
            [
                'meta'  => Meta::get(__('app.admin')),
                'data'  => [
                    'sheet'         => 'view',
                    'items'         => $items,
                    'count'         => $pagesCount,
                    'pagesCount'    => ceil($pagesCount / self::$limit),
                    'pNum'          => Html::pageNumber(),
                    'paginationUrl' => '/mod/admin/facts/' . $type,
                ]
            ]
        );
    }

    // Каталог фактов в админском виде (вкладка "Все факты")
    private function factsAll(): void
    {
        $type       = 'all';
        $items      = ItemModel::feedItem(false, false, Html::pageNumber(), self::$limit, $type);
        $pagesCount = ItemModel::feedItemCount(false, false, $type);

        render(
            '/content/index',
            [
                'meta'  => Meta::get(__('app.admin')),
                'data'  => [
                    'sheet'         => 'view',
                    'items'         => $items,
                    'count'         => $pagesCount,
                    'pagesCount'    => ceil($pagesCount / self::$limit),
                    'pNum'          => Html::pageNumber(),
                    'paginationUrl' => '/mod/admin/facts/all',
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
					'sheet' 			=> 'view',


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
	
	public function random(): void
	{
		$item = ItemModel::getRandomFact();
		if (empty($item)) {
			notEmptyOrView404([]);
			return;
		}

		$dir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
		$facetPath = trim($dir[2] ?? '', '/');

		// В предпросмотре — на страницу превью, иначе — на статический URL
		if (str_contains((string)Request::getUri()->getPath(), '/mod/admin/preview')) {
			redirect(urlItem($facetPath, $item['item_slug'], 'preview'));
		}

		redirect('/' . $facetPath . '/' . $item['item_slug'] . '.html');
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
