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

        // Инфобокс: доступные шаблоны, текущий экземпляр и схемы полей
        $infoboxTemplates = \App\Models\TemplateModel::templates('infobox');
        $infoboxFields = [];
        foreach ($infoboxTemplates as $tpl) {
            $infoboxFields[(int)$tpl['template_id']] = \App\Models\TemplateModel::fields((int)$tpl['template_id']);
        }
        $infobox = \App\Models\TemplateModel::instance((int)$item['item_id']);

        $infoboxImage = '';
        if ($infobox && !empty($infobox['values']['image'])) {
            $infoboxImage = (string)$infobox['values']['image'];
        } elseif (!empty($item['item_thumb_img'])) {
            $infoboxImage = Img::PATH['thumbs'] . $item['item_thumb_img'];
        }
        $media = $infoboxImage !== '' ? \App\Models\TemplateModel::media($infoboxImage) : null;

        // Встроенные блоки: доступные шаблоны, их поля и уже созданные экземпляры
        $inlineTemplates = \App\Models\TemplateModel::templates('inline');
        $inlineFields = [];
        foreach ($inlineTemplates as $tpl) {
            $inlineFields[(int)$tpl['template_id']] = \App\Models\TemplateModel::fields((int)$tpl['template_id']);
        }
        $inlineInstances = \App\Models\TemplateModel::inlineInstances((int)$item['item_id']);

        // Правка существующего встроенного блока (?inline_id=N)
        $inlineEditId = (int)(Request::get('inline_id')->value() ?? 0);
        $inlineEdit = $inlineEditId > 0 ? \App\Models\TemplateModel::instanceById($inlineEditId) : null;
        if ($inlineEdit && (int)$inlineEdit['item_id'] !== (int)$item['item_id']) {
            $inlineEdit = null;
        }

        // Первое фото из текста: для кнопки «Взять из текста» в выборе фото
        $contentImage = '';
        $rawContent = (string)($item['item_content'] ?? '');
        if (preg_match('/:::\s*\{?\.?foto\}?(.*?):::/su', $rawContent, $fm)
            && preg_match('/!\[[^\]]*\]\(([^)\s]+)/u', $fm[1], $im)) {
            $contentImage = $im[1];
        } elseif (preg_match('/!\[[^\]]*\]\(([^)\s]+)/u', $rawContent, $im)) {
            $contentImage = $im[1];
        }

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
                    'infobox'            => $infobox,
                    'infobox_templates'  => $infoboxTemplates,
                    'infobox_fields'     => $infoboxFields,
                    'infobox_image'      => $infoboxImage,
                    'media'              => $media,
                    'inline_templates'   => $inlineTemplates,
                    'inline_fields'      => $inlineFields,
                    'inline_instances'   => $inlineInstances,
                    'inline_edit'        => $inlineEdit,
                    'content_image'      => $contentImage,

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
        $data['item_type'] = in_array($kind, ['fact', 'article', 'site', 'page'], true) ? $kind : 'fact';

        $redirect = url('item.form.edit', ['id' => (int)$data['item_id']]);

        // Post cover
        //$data['fact_content_img'] = $['fact_content_img'];
        if (!empty($data['images'])) {
            $thumb = Img::thumbImg($data['images'], $data, $redirect);
            if ($thumb !== false) {
                $data['item_thumb_img'] = $thumb;
            }
        }

        dbTransaction(function () use ($data) {
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

            // Инфобокс: экземпляр шаблона для записи
            $templateId = (int)($data['infobox_template'] ?? 0);
            $itemId = (int)$data['item_id'];

            if ($templateId > 0) {
                $fields = \App\Models\TemplateModel::fields($templateId);
                if ($fields) {
                    $input = is_array($data['infobox_' . $templateId] ?? null) ? $data['infobox_' . $templateId] : [];
                    $values = [];
                    foreach ($fields as $field) {
                        $key = (string)$field['field_key'];
                        $values[$key] = trim((string)($input[$key] ?? ''));
                    }

                    if (($values['image'] ?? '') === '' && !empty($data['item_thumb_img'])) {
                        $values['image'] = Img::PATH['thumbs'] . $data['item_thumb_img'];
                    }

                    \App\Models\TemplateModel::saveInstance($itemId, $templateId, $values);

                    // Метаданные фото: автор, источник, лицензия
                    $mediaPath = (string)($values['image'] ?? '');
                    $mediaAuthor = trim((string)($data['media_author'] ?? ''));
                    $mediaSource = trim((string)($data['media_source'] ?? ''));
                    $mediaLicense = trim((string)($data['media_license'] ?? ''));

                    if ($mediaPath !== '' && ($mediaAuthor !== '' || $mediaSource !== '' || $mediaLicense !== '')) {
                        \App\Models\TemplateModel::saveMedia($mediaPath, [
                            'author'  => $mediaAuthor,
                            'source'  => $mediaSource,
                            'license' => $mediaLicense,
                        ]);
                    }
                }
            } else {
                $existing = \App\Models\TemplateModel::instance($itemId);
                if ($existing) {
                    \App\Models\TemplateModel::deleteInstance((int)$existing['instance_id']);
                }
            }

            // Встроенные блоки: добавление нового экземпляра
            $inlineTemplateId = (int)($data['inline_template'] ?? 0);
            if ($inlineTemplateId > 0) {
                $inlineFields = \App\Models\TemplateModel::fields($inlineTemplateId);
                if ($inlineFields) {
                    $input = is_array($data['inline_' . $inlineTemplateId] ?? null) ? $data['inline_' . $inlineTemplateId] : [];
                    $values = [];
                    foreach ($inlineFields as $field) {
                        $key = (string)$field['field_key'];
                        $values[$key] = trim((string)($input[$key] ?? ''));
                    }

                    if (array_filter($values, static fn($v) => $v !== '') !== []) {
                        $inlineInstanceId = (int)($data['inline_instance_id'] ?? 0);

                        if ($inlineInstanceId > 0) {
                            \App\Models\TemplateModel::updateInstance($inlineInstanceId, $inlineTemplateId, $values);
                        } else {
                            \App\Models\TemplateModel::addInlineInstance($itemId, $inlineTemplateId, $values);
                        }
                    }
                }
            }

            return null;
        });

        Msg::redirect(__('msg.change_saved'), 'success', $redirect);
    }

    // Удаление встроенного блока записи
    public function inlineDelete(): void
    {
        $id = (int)Request::param('id')->asInt(0);
        $instance = $id > 0 ? \App\Models\TemplateModel::instanceById($id) : null;

        if ($instance) {
            $item = Validator::content((int)$instance['item_id']);
            if ($item) {
                \App\Models\TemplateModel::deleteInstance($id);
                Msg::redirect(__('msg.change_saved'), 'success', url('item.form.edit', ['id' => (int)$instance['item_id']]));
            }
        }

        Msg::redirect(__('msg.no_content'), 'error', url('homepage'));
    }

    // Список встроенных блоков записи (JSON для вставки маркера из редактора)
    public function inlineList(): void
    {
        $id = (int)Request::param('id')->asInt(0);
        $rows = $id > 0 ? \App\Models\TemplateModel::inlineInstances($id) : [];

        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'id'    => (int)$row['instance_id'],
                'code'  => (string)$row['template_code'],
                'title' => (string)$row['template_title'],
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
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

    // Список версий факта (история правок)
    public function history(): void
    {
        $item = Validator::content(Request::param('id')->asInt());

        if ($this->container->access()->author('item', $item) === false) {
            $this->container->redirect()->to(url('homepage'), status: 303);
        }

        $versions = \App\Models\VersionModel::list((int)$item['item_id'], 100);

        render(
            '/admin/pages/items/history',
            [
                'meta'  => Meta::get(__('app.item_history')),
                'data'  => [
                    'item'       => $item,
                    'sheet'      => 'history',
                    'versions'   => $versions,
                ]
            ],
        );
    }

    // Просмотр конкретной версии (контент снимка)
    public function historyShow(): void
    {
        $id      = Request::param('id')->asInt();
        $version = Request::param('version')->asInt();

        $item = Validator::content($id);
        if ($this->container->access()->author('item', $item) === false) {
            $this->container->redirect()->to(url('homepage'), status: 303);
        }

        $snap = \App\Models\VersionModel::get($id, $version);
        if (empty($snap)) {
            Msg::redirect(__('msg.version_not_found'), 'error', url('item.history', ['id' => $id]));
        }

        render(
            '/admin/pages/items/history-view',
            [
                'meta'  => Meta::get(__('app.item_history')),
                'data'  => [
                    'item'    => $item,
                    'version' => $snap,
                    'backUrl' => url('item.history', ['id' => $id]),
                ]
            ],
        );
    }

    // Построчный diff между двумя версиями
    public function historyDiff(): void
    {
        $id     = Request::param('id')->asInt();
        $fromV  = Request::param('from')->asInt();
        $toV    = Request::param('to')->asInt();

        $item = Validator::content($id);
        if ($this->container->access()->author('item', $item) === false) {
            $this->container->redirect()->to(url('homepage'), status: 303);
        }

        $compare = \App\Models\VersionModel::compare($id, $fromV, $toV);
        if (empty($compare['from']) || empty($compare['to'])) {
            Msg::redirect(__('msg.version_not_found'), 'error', url('item.history', ['id' => $id]));
        }

        render(
            '/admin/pages/items/history-diff',
            [
                'meta'  => Meta::get(__('app.item_diff')),
                'data'  => [
                    'item'    => $item,
                    'diff'    => $compare['diff'],
                    'from'    => $compare['from'],
                    'to'      => $compare['to'],
                    'backUrl' => url('item.history', ['id' => $id]),
                ]
            ],
        );
    }

    // Откат факта к выбранной версии
    public function rollback(): void
    {
        $id      = Request::param('id')->asInt();
        $version = Request::param('version')->asInt();

        $item = Validator::content($id);

        // Только автор/персонал
        if ($this->container->access()->author('item', $item) === false) {
            Msg::redirect(__('msg.went_wrong'), 'error');
        }

        $snap = \App\Models\VersionModel::get($id, $version);
        if (empty($snap)) {
            Msg::redirect(__('msg.version_not_found'), 'error', url('item.history', ['id' => $id]));
        }

        // Восстанавливаем из снимка данные факта.
        // ItemModel::edit сам создаст снимок текущего состояния (так откат попадёт в историю).
        $data = [
            'item_id'          => (int)$snap['item_id'],
            'item_title'       => $snap['item_title'],
            'item_slug'        => $snap['item_slug'],
            'item_type'        => $snap['item_type'],
            'item_note'        => $snap['item_note'],
            'item_content'     => $snap['item_content'],
            'item_thumb_img'   => $snap['item_thumb_img'],
            'item_source_url'  => $snap['source_url'],
            'item_published'   => $item['item_published'],
            'item_best'        => $item['item_best'],
            'user_id'          => null,
            'item_edit_comment'=> '',
        ];

        \App\Models\ItemModel::edit($data);

        Msg::redirect(__('msg.rollback_done'), 'success', url('item.form.edit', ['id' => $id]));
    }
}
