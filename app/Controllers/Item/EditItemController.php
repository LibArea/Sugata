<?php

declare(strict_types=1);

namespace App\Controllers\Item;

use Hleb\Static\Request;
use Hleb\Base\Controller;

use App\Models\ItemModel;
use App\Models\User\UserModel;
use Meta, Msg, Img, Validator;

class EditItemController extends Controller
{
    // Форма редактирование домена
    public function index()
    {
        $item = Validator::content(Request::param('id')->asInt());

        // Only the site author and staff can edit
        // Редактировать может только автор сайта и персонал
        if ($this->container->access()->author('item', $item) === false) {
            $this->container->redirect()->to(url('homepage'), status: 303);
        }

        render(
            '/content/items/edit',
            [
                'meta'  => Meta::get(__('app.edit_fact')),
                'data'  => [
                    'item'			=> $item,
                    'sheet'         => 'edit',
                    'type'          => 'item.edit',
                    'user'          => UserModel::get($item['item_user_id'], 'id'),
                    'category_arr'  => ItemModel::getItemTopic($item['item_id']),

                ]
            ],
        );
    }

    public function edit()
    {
        $data = Request::getParsedBody(); // allPost()

        Validator::publication($data, url('item.form.edit', ['id' => $data['item_id']]));

        $redirect = url('item.form.edit', ['id' => (int)$data['item_id']]);

        // Post cover
        //$data['fact_content_img'] = $['fact_content_img'];
        if (!empty($data['images'])) {
            $data['item_thumb_img'] = Img::thumbImg($data['images'], $data, $redirect);
        }

        ItemModel::edit($data);

        $facet_item = $data['facet_select'] ?? [];
        $topics     = json_decode($facet_item, true);
        if (!empty($topics)) {
            $arr = [];
            foreach ($topics as $row) {
                $arr[] = $row;
            }

            ItemModel::addItemFacets($arr, (int)$data['item_id']);
        }

        Msg::redirect(__('msg.change_saved'), 'success', $redirect);
    }

    public static function toggle($value): ?int
    {
        return $value === 'on' ? 1 : null;
    }

    /**
     * Cover Removal
     *
     * @return void
     */
    function thumbItemRemove()
    {
        $item = Validator::item(Request::param('id')->asInt());

        // Удалять может только автор
        // Only the author can delete it
        if ($this->container->access()->author('item', $item) == false) {
            Msg::redirect(__('msg.went_wrong'), 'error');
        }

        ItemModel::setItemThumbRemove($item['item_id']);
        Img::thumbItemRemove($item['item_thumb_img']);

        Msg::redirect(__('msg.cover_removed'), 'success', url('item.form.edit', ['id' => $item['item_id']]));
    }

    public function uploadContentImage()
    {
        $id         = Request::param('id')->asInt();

        $img = $_FILES['file'];
        if ($_FILES['file']['name']) {
            return json_encode(['data' => ['filePath' => Img::itemImg($img, 'facet-telo', $id)]]);
        }

        return false;
    }
}
