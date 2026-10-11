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

        // Готовый URL факта (для копирования и вставки в другие статьи)
        $facetPath = '';
        if (!empty($item['facet_list'])) {
            $chunks = array_chunk(preg_split('/(@)/', (string)$item['facet_list']), 4);
            $facetPath = trim($chunks[0][2] ?? '', '/');
        }
        $itemUrl = ($facetPath !== '' && !empty($item['item_slug']))
            ? '/' . $facetPath . '/' . $item['item_slug'] . '.html'
            : '';

        render(
            '/admin/pages/items/edit',
            [
                'meta'  => Meta::get(__('app.edit_fact')),
                'data'  => [
                    'item'			=> $item,
                    'sheet'         => 'edit',
                    'type'          => 'item.edit',
                    'user'          => UserModel::get($item['item_user_id'], 'id'),
                    'category_arr'  => ItemModel::getItemTopic($item['item_id']),
                    'related_arr'   => self::relatedArr((int)$item['item_id']),
                    'faq'           => \App\Models\FaqModel::forItem((int)$item['item_id']),
                    'item_url'      => $itemUrl,

                ]
            ],
        );
    }

public function edit()
    {
        $data = Request::getParsedBody(); // allPost()

        Validator::publication($data, url('item.form.edit', ['id' => $data['item_id']]));

        // Тип контента: селект «Факт / Статья / Страница» (поле content_kind)
        $kind = (string)($data['content_kind'] ?? $data['item_type'] ?? 'fact');
        $data['item_type'] = in_array($kind, ['fact', 'article', 'page'], true) ? $kind : 'fact';

        $redirect = url('item.form.edit', ['id' => (int)$data['item_id']]);

        // Post cover
        //$data['fact_content_img'] = $['fact_content_img'];
        if (!empty($data['images'])) {
            $thumb = Img::thumbImg($data['images'], $data, $redirect);
            if ($thumb !== false) {
                $data['item_thumb_img'] = $thumb;
            }
        }

        ItemModel::edit($data);

        // FAQ: пары вопрос/ответ из формы
        $faqRows = [];
        $questions = $data['faq_question'] ?? [];
        $answers   = $data['faq_answer'] ?? [];
        foreach ($questions as $i => $q) {
            $faqRows[] = [
                'question'   => trim((string)$q),
                'answer'     => trim((string)($answers[$i] ?? '')),
                'sort_order' => $i,
            ];
        }
        \App\Models\FaqModel::replace((int)$data['item_id'], $faqRows);

        // Категории: зависимый селект присылает массив id (facet_select[])
        $facetIds = facetIdsFromInput($data['facet_select'] ?? []);
        if ($facetIds) {
            $arr = [];
            foreach ($facetIds as $id) {
                $arr[] = ['id' => $id];
            }
            ItemModel::addItemFacets($arr, (int)$data['item_id']);
        }

        // Связанные статьи (поле related_ids). Отбираем только статьи (факты нельзя связать).
        $relatedIds = facetIdsFromInput($data['related_ids'] ?? []);
        $relatedIds = \App\Models\ItemModel::filterIdsByType($relatedIds, 'article');
        ItemModel::setRelations((int)$data['item_id'], $relatedIds);

        Msg::redirect(__('msg.change_saved'), 'success', $redirect);
    }

    public static function toggle($value): ?int
    {
        return $value === 'on' ? 1 : null;
    }

    // Связанные элементы в формате для Tagify: [['id'=>..,'value'=>..,'item_type'=>..], ...]
    protected static function relatedArr(int $item_id): array
    {
        $list = ItemModel::getRelatedItems($item_id, 'article');
        $out = [];
        foreach ($list as $r) {
            $out[] = [
                'id'        => (int)$r['item_id'],
                'value'     => $r['item_title'],
                'item_type' => $r['item_type'],
            ];
        }
        return $out;
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

        $img = $_FILES['file'] ?? null;
        if ($img && !empty($img['name'])) {
            $path = Img::itemImg($img, 'facet-telo', $id);
            if ($path === false) {
                return json_encode(['error' => __('msg.upload_invalid_image')]);
            }
            return json_encode(['data' => ['filePath' => $path]]);
        }

        return json_encode(['error' => __('msg.upload_invalid_image')]);
    }
}
