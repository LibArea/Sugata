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
        // Р вЂ™ Р В°Р Т‘Р СР С‘Р Р…Р С”Р Вµ РІР‚вЂќ Р Р†РЎРѓР ВµР С–Р Т‘Р В° Р В°Р Т‘Р СР С‘Р Р…РЎРѓР С”Р В°РЎРЏ Р С•Р В±РЎвЂРЎР‚РЎвЂљР С”Р В° Р С‘ Р В°Р Т‘Р СР С‘Р Р…РЎРѓР С”Р С‘Р в„– Р Т‘Р С‘Р В·Р В°Р в„–Р Р….
        // Р В Р ВµР В°Р В»РЎРЉР Р…РЎвЂ№Р в„– Р Р†Р С‘Р Т‘ РЎРѓР В°Р в„–РЎвЂљР В° (Р С”Р В°Р С” Р С—Р С•РЎРѓР В»Р Вµ РЎРѓР В±Р С•РЎР‚Р С”Р С‘) РЎРѓР СР С•РЎвЂљРЎР‚Р С‘Р С РЎвЂљР С•Р В»РЎРЉР С”Р С• Р Р† Р С—РЎР‚Р ВµР Т‘Р С—РЎР‚Р С•РЎРѓР СР С•РЎвЂљРЎР‚Р Вµ (/mod/admin/preview).
        if ($type === 'all') {
            $this->factsAll();
            return;
        }

        $title = match ($type) {
            'my'         => __('app.my_facts'),
            'moderation' => __('app.moderation'),
            'articles'   => __('app.articles'),
            'deleted'    => __('app.deleted'),
            default      => __('app.facts'),
        };

        // $childrens, $category_id, $page, $sort, $limit, $type: my, moderation
        $items      = ItemModel::feedItem(false, false, Html::pageNumber(), self::$limit, $type);
        $pagesCount = ItemModel::feedItemCount(false, false, $type);

        render(
            '/admin/pages/index',
            [
                'meta'  => Meta::get(__('app.admin')),
'data'  => [
                    'sheet'         => 'view',
                    'type'          => $type,
                    'title'         => $title,
                    'items'         => $items,
                    'count'         => $pagesCount,
                    'pagesCount'    => ceil($pagesCount / self::$limit),
                    'pNum'          => Html::pageNumber(),
                    'paginationUrl' => '/mod/admin/facts/' . $type,
                ]
            ]
        );
    }

    // Р С™Р В°РЎвЂљР В°Р В»Р С•Р С– РЎвЂћР В°Р С”РЎвЂљР С•Р Р† Р Р† Р В°Р Т‘Р СР С‘Р Р…РЎРѓР С”Р С•Р С Р Р†Р С‘Р Т‘Р Вµ (Р Р†Р С”Р В»Р В°Р Т‘Р С”Р В° "Р вЂ™РЎРѓР Вµ РЎвЂћР В°Р С”РЎвЂљРЎвЂ№")
    private function factsAll(): void
    {
        $type       = 'all';
        $items      = ItemModel::feedItem(false, false, Html::pageNumber(), self::$limit, $type);
        $pagesCount = ItemModel::feedItemCount(false, false, $type);

        render(
            '/admin/pages/index',
            [
                'meta'  => Meta::get(__('app.admin')),
'data'  => [
                    'sheet'       => 'view',
                    'type'        => 'all',
                    'title'       => __('app.all_facts'),
                    'items'       => $items,
                    'count'       => $pagesCount,
                    'pagesCount'  => ceil($pagesCount / self::$limit),
                    'pNum'        => Html::pageNumber(),
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

		$childrens = FacetModel::getChildrens($category['facet_id']); // Р С•РЎвЂљР С•Р В±РЎР‚Р В°Р В¶Р ВµР Р…Р С‘Р Вµ Р С”Р В°РЎвЂљР ВµР С–Р С•РЎР‚Р С‘Р в„– Р Т‘Р ВµРЎвЂљР С‘ 1 РЎС“РЎР‚Р С•Р Р†Р Р…РЎРЏ
		
		foreach ($childrens as $id => $row) {
			$childrenFacet =  FacetModel::childrenForFeed($row['facet_id']);
			$childrens[$id]['facet_count'] = ItemModel::feedItemCount($childrenFacet,  $row['facet_id']);
		}

        $category['facet_img'] = '';

        return render(
            '/admin/pages/category',
            [
                'meta'  => Meta::category($category),
                'data'  => [
					'sheet' 			=> 'view',
                    'count'             => $pagesCount,
                    'pagesCount'        => ceil($pagesCount / self::$limit),
                    'pNum'              => Html::pageNumber(),
                    'items'             => $items,
                    'category'          => $category, // РЎвЂљР ВµР С”РЎС“РЎвЂ°Р В°РЎРЏ Р С”Р В°РЎвЂљР ВµР С–Р С•РЎР‚Р С‘РЎРЏ
                    'childrens'        => $childrens,
                    'breadcrumb'        => Html::breadcrumbDir($tree),
                    'low_matching'      => FacetModel::getLowMatching($category['facet_id']), // РЎРѓР Р†РЎРЏР В·Р В°Р Р…Р Р…РЎвЂ№Р Вµ Р Т‘Р ВµРЎР‚Р ВµР Р†РЎРЉРЎРЏ
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

		// Р вЂ™ Р С—РЎР‚Р ВµР Т‘Р С—РЎР‚Р С•РЎРѓР СР С•РЎвЂљРЎР‚Р Вµ РІР‚вЂќ Р Р…Р В° РЎРѓРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ РЎС“ Р С—РЎР‚Р ВµР Р†РЎРЉРЎР‹, Р С‘Р Р…Р В°РЎвЂЎР Вµ РІР‚вЂќ Р Р…Р В° РЎРѓРЎвЂљР В°РЎвЂљР С‘РЎвЂЎР ВµРЎРѓР С”Р С‘Р в„– URL
		if (str_contains((string)Request::getUri()->getPath(), '/mod/admin/preview')) {
			redirect(urlItem($facetPath, $item['item_slug'], 'preview'));
		}

		redirect('/' . $facetPath . '/' . $item['item_slug'] . '.html');
	}

	public function best(): void
	{
		$page      = max(1, Html::pageNumber());
		$limit     = 20;
		$items     = ItemModel::getBestFacts($page, $limit);
		$total     = ItemModel::countBest();

		// Р вЂ™ Р С—РЎР‚Р ВµР Т‘Р С—РЎР‚Р С•РЎРѓР СР С•РЎвЂљРЎР‚Р Вµ РІР‚вЂќ Р С—РЎР‚Р ВµР Р†РЎРЉРЎР‹-Р С—РЎС“РЎвЂљР С‘, Р С‘Р Р…Р В°РЎвЂЎР Вµ РІР‚вЂќ РЎРѓРЎвЂљР В°РЎвЂљР С‘РЎвЂЎР ВµРЎРѓР С”Р С‘Р Вµ (РЎРѓРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ Р В° /best/)
		$preview = str_contains((string)Request::getUri()->getPath(), '/mod/admin/preview');

		$mod = $preview ? 'preview' : 'static';

		$this->response()->setBody(view('/site/pages/best.php', [
			'items'     => $items,
			'total'     => $total,
			'pagesCount' => max(1, (int)ceil($total / $limit)),
			'pNum'      => $page,
			'sideNav'   => \App\Content\CatalogService::sidebar(),
			'meta'      => Meta::get(__('app.best_facts_page')),
			'preview'   => $preview,
		]));
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