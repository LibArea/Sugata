<?php

declare(strict_types=1);

namespace App\Controllers\Item;

use Hleb\Base\Controller;

use Hleb\Static\Request;
use App\Models\ItemModel;
use Meta, Msg, Validator;

use App\Traits\Slug;

class AddItemController extends Controller
{
    use Slug;

    /**
     * Add Form
     * Форма добавление 
     */
    public function index()
    {
        render(
            '/content/items/add',
            [
                'meta'  => Meta::get(__('app.add_fact')),
                'data'  => [
                    'sheet'    => 'add_fact',

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

        $data['item_slug'] = $this->getSlug($data['item_title']);

        $item_last = ItemModel::add($data);

        // Facets (categories are called here) for the site 
        // Фасеты (тут называются категории) для сайта
        $post_fields    = Request::getParsedBody() ?? [];
        $facet_post     = $post_fields['facet_select'] ?? [];
        $topics         = json_decode($facet_post, true);

        if (!empty($topics)) {
            $arr = [];
            foreach ($topics as $row) {
                $arr[] = $row;
            }
            ItemModel::addItemFacets($arr, $item_last['item_id']);
        }

        Msg::redirect(__('app.site_added'), 'success', url('items'));
    }
}
