<?php

declare(strict_types=1);

namespace App\Controllers;

use Hleb\Base\Controller;
use Hleb\Static\Request;
use App\Models\TemplateModel;
use Meta, Msg;

/**
 * Управление шаблонами оформления: схемы шаблонов и их поля.
 */
class TemplateAdminController extends Controller
{
    // Список шаблонов
    public function index(): void
    {
        render(
            '/admin/pages/templates/index',
            [
                'meta' => Meta::get(__('app.templates')),
                'data' => [
                    'sheet'     => 'templates',
                    'templates' => TemplateModel::allTemplates(),
                ],
            ]
        );
    }

    // Форма шаблона: новый или существующий (+ поля)
    public function form(): void
    {
        $id = (int)Request::param('id')->asInt(0);
        $template = $id > 0 ? TemplateModel::template($id) : null;

        if ($id > 0 && !$template) {
            Msg::redirect(__('msg.no_content'), 'error', url('templates'));
        }

        $fieldId = (int)(Request::get('field_id')->value() ?? 0);
        $field = $fieldId > 0 ? TemplateModel::field($fieldId) : null;

        $optionsText = '';
        if ($field && (string)$field['field_type'] === 'select') {
            $lines = [];
            foreach (TemplateModel::options((int)$field['field_id']) as $option) {
                $lines[] = $option['option_value'] . ' | ' . $option['option_label'];
            }
            $optionsText = implode("\n", $lines);
        }

        render(
            '/admin/pages/templates/form',
            [
                'meta' => Meta::get(__('app.templates')),
                'data' => [
                    'sheet'    => 'templates',
                    'template' => $template,
                    'fields'   => $template ? TemplateModel::fields((int)$template['template_id'], true) : [],
                    'history'  => $template ? TemplateModel::history((int)$template['template_id']) : [],
                    'field'    => $field,
                    'field_options_text' => $optionsText,
                ],
            ]
        );
    }

    // Сохранение шаблона
    public function save(): void
    {
        $data = Request::getParsedBody();

        if (trim((string)($data['template_title'] ?? '')) === '' || trim((string)($data['template_code'] ?? '')) === '') {
            Msg::redirect(__('msg.no_content'), 'error', url('templates'));
        }

        $id = TemplateModel::saveTemplate($data);

        Msg::redirect(__('msg.change_saved'), 'success', url('template.edit', ['id' => $id]));
    }

    // Удаление шаблона
    public function delete(): void
    {
        $data = Request::getParsedBody();
        $id = (int)($data['template_id'] ?? 0);

        if ($id > 0) {
            TemplateModel::deleteTemplate($id);
        }

        Msg::redirect(__('msg.change_saved'), 'success', url('templates'));
    }

    // Сохранение поля шаблона
    public function fieldSave(): void
    {
        $data = Request::getParsedBody();
        $templateId = (int)($data['template_id'] ?? 0);

        if ($templateId <= 0 || trim((string)($data['field_key'] ?? '')) === '' || trim((string)($data['field_label'] ?? '')) === '') {
            Msg::redirect(__('msg.no_content'), 'error', url('template.edit', ['id' => $templateId]));
        }

        TemplateModel::saveField($data, $this->container->user()->id());

        Msg::redirect(__('msg.change_saved'), 'success', url('template.edit', ['id' => $templateId]));
    }

    // Удаление поля шаблона
    public function fieldDelete(): void
    {
        $data = Request::getParsedBody();
        $templateId = (int)($data['template_id'] ?? 0);

        if ((int)($data['field_id'] ?? 0) > 0) {
            TemplateModel::deleteField((int)$data['field_id'], $this->container->user()->id());
        }

        Msg::redirect(__('msg.change_saved'), 'success', url('template.edit', ['id' => $templateId]));
    }

    // Восстановление удалённого поля шаблона
    public function fieldRestore(): void
    {
        $data = Request::getParsedBody();
        $templateId = (int)($data['template_id'] ?? 0);

        if ((int)($data['field_id'] ?? 0) > 0) {
            TemplateModel::restoreField((int)$data['field_id'], $this->container->user()->id());
        }

        Msg::redirect(__('msg.change_saved'), 'success', url('template.edit', ['id' => $templateId]));
    }

    // Новый порядок полей (drag & drop)
    public function fieldsOrder(): void
    {
        $data = Request::getParsedBody();
        $templateId = (int)($data['template_id'] ?? 0);
        $ids = $data['order'] ?? [];

        if ($templateId > 0 && is_array($ids)) {
            TemplateModel::reorderFields($templateId, $ids);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'template_id' => $templateId]);
    }

    // Библиотека загруженных изображений (JSON для выбора фото)
    public function mediaList(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['items' => TemplateModel::mediaLibrary(60)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Экран «Медиафайлы»: права на фото
    public function media(): void
    {
        render(
            '/admin/pages/media/index',
            [
                'meta' => Meta::get(__('app.media')),
                'data' => [
                    'sheet' => 'media',
                    'rows'  => TemplateModel::mediaRows(300),
                ],
            ]
        );
    }

    // Сохранение прав на фото
    public function mediaSave(): void
    {
        $data = Request::getParsedBody();

        $paths    = $data['path'] ?? [];
        $ids      = $data['media_id'] ?? [];
        $alts     = $data['alt'] ?? [];
        $captions = $data['caption'] ?? [];
        $authors  = $data['author'] ?? [];
        $sources  = $data['source'] ?? [];
        $licenses = $data['license'] ?? [];

        $rows = [];
        foreach ($paths as $i => $path) {
            $rows[] = [
                'path'    => (string)$path,
                'media_id' => (int)($ids[$i] ?? 0),
                'alt'     => (string)($alts[$i] ?? ''),
                'caption' => (string)($captions[$i] ?? ''),
                'author'  => (string)($authors[$i] ?? ''),
                'source'  => (string)($sources[$i] ?? ''),
                'license' => (string)($licenses[$i] ?? ''),
            ];
        }

        TemplateModel::saveMediaBatch($rows);

        Msg::redirect(__('msg.change_saved'), 'success', url('media'));
    }
}
