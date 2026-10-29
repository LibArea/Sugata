<?php

declare(strict_types=1);

namespace App\Controllers\Item;

use Hleb\Base\Controller;

use Hleb\Static\Request;
use App\Models\ItemModel;
use Meta, Msg, Validator;

use Cocur\Slugify\Slugify;

class AddItemController extends Controller
{
    /**
     * Add Form
     * Форма добавление 
     */
    public function index()
    {
        // Необязательный предзаполненный заголовок (при переходе по красной ссылке [[Термин]])
        $prefillTitle = trim((string)Request::get('title')->value() ?? '');

        render(
            '/admin/pages/items/add',
            [
                'meta'  => Meta::get(__('app.add_fact')),
                'data'  => [
                    'sheet'         => 'add_fact',
                    'prefill_title' => $prefillTitle,
                ]
            ],
        );
    }

    /**
     * Checks and directly adding 
     *
     * @return void
     */
    public function add()
    {
        $data = Request::getParsedBody();

        Validator::publication($data, url('item.form.add'));

        // Тип контента: селект «Факт / Статья / Страница» (поле content_kind)
        $kind = (string)($data['content_kind'] ?? $data['item_type'] ?? 'fact');
        $data['item_type'] = in_array($kind, ['fact', 'article', 'site', 'page'], true) ? $kind : 'fact';

        error_log('[ADDBUG] content_kind=' . var_export($data['content_kind'] ?? null, true)
            . ' item_type после=' . $data['item_type']
            . ' facet_select=' . var_export($data['facet_select'] ?? null, true));

        $data['item_slug'] = self::getSlug($data['item_title']);

        $item_last = ItemModel::add($data);

        // Категории: зависимый селект присылает массив id (facet_select[])
        $facetIds = facetIdsFromInput($data['facet_select'] ?? []);

        if ($facetIds) {
            $arr = [];
            foreach ($facetIds as $id) {
                $arr[] = ['id' => $id];
            }
            ItemModel::addItemFacets($arr, (int)$item_last['item_id']);
        }

        // Связанные факты/статьи (поле related_ids)
        $relatedIds = facetIdsFromInput($data['related_ids'] ?? []);
        if ($relatedIds) {
            ItemModel::setRelations((int)$item_last['item_id'], $relatedIds);
        }

        Msg::redirect(__('app.site_added'), 'success', url('homepage'));
    }
	
    private static function getSlug($title)
    {
        $slugify = new Slugify();
        $uri = $slugify->slugify($title);

        return substr($uri, 0, 90);
    }
}
