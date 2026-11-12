<?php

declare(strict_types=1);

namespace App\Models;

use Hleb\Base\Model;
use Hleb\Static\DB;

/**
 * Шаблоны оформления: схемы (templates, template_fields),
 * экземпляры на записях (template_instances) и метаданные медиа.
 */
class TemplateModel extends Model
{
    // Активные шаблоны указанного назначения: infobox | inline
    public static function templates(string $scope = 'infobox'): array
    {
        $sql = "SELECT template_id, template_code, template_title, template_description
                FROM templates
                WHERE template_is_active = 1 AND template_scope = :scope
                ORDER BY template_sort ASC, template_title ASC";

        return DB::run($sql, ['scope' => $scope])->fetchAll();
    }

    // Шаблон по коду
    public static function byCode(string $code): ?array
    {
        $row = DB::run(
            "SELECT template_id, template_code, template_title, template_scope
             FROM templates
             WHERE template_code = :code AND template_is_active = 1",
            ['code' => $code]
        )->fetch();

        return $row ?: null;
    }

    // Схема полей шаблона
    public static function fields(int $template_id, bool $withDeleted = false): array
    {
        $sql = "SELECT field_id, field_key, field_label, field_type, field_required, field_sort,
                       field_default, field_hint, field_is_deleted
                FROM template_fields
                WHERE template_id = :template_id";

        if (!$withDeleted) {
            $sql .= " AND field_is_deleted = 0";
        }

        $sql .= " ORDER BY field_sort ASC, field_id ASC";

        return DB::run($sql, ['template_id' => $template_id])->fetchAll();
    }

    // Экземпляр шаблона на записи (для инфобокса — единственный)
    public static function instance(int $item_id, string $slot = 'infobox'): ?array
    {
        $row = DB::run(
            "SELECT i.instance_id, i.template_id, i.item_id, i.instance_slot, i.instance_anchor,
                    i.instance_sort, i.instance_values, t.template_code, t.template_title
             FROM template_instances i
             JOIN templates t ON t.template_id = i.template_id
             WHERE i.item_id = :item_id
               AND i.instance_slot = :slot
               AND i.instance_is_active = 1
             ORDER BY i.instance_sort ASC, i.instance_id ASC
             LIMIT 1",
            ['item_id' => $item_id, 'slot' => $slot]
        )->fetch();

        if (!$row) {
            return null;
        }

        $row['values'] = self::decodeValues((string)($row['instance_values'] ?? ''));
        $row['fields'] = self::fields((int)$row['template_id']);

        return $row;
    }

    // Создание или обновление инфобокса записи
    public static function saveInstance(int $item_id, int $template_id, array $values, string $slot = 'infobox', ?string $anchor = null, int $sort = 0): int
    {
        $json = json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $current = self::instance($item_id, $slot);

        if ($current) {
            DB::run(
                "UPDATE template_instances
                 SET template_id = :template_id, instance_values = :values, instance_anchor = :anchor, instance_sort = :sort
                 WHERE instance_id = :id",
                [
                    'template_id' => $template_id,
                    'values'      => $json,
                    'anchor'      => $anchor,
                    'sort'        => $sort,
                    'id'          => (int)$current['instance_id'],
                ]
            );

            return (int)$current['instance_id'];
        }

        DB::run(
            "INSERT INTO template_instances (template_id, item_id, instance_slot, instance_anchor, instance_sort, instance_values)
             VALUES (:template_id, :item_id, :slot, :anchor, :sort, :values)",
            [
                'template_id' => $template_id,
                'item_id'     => $item_id,
                'slot'        => $slot,
                'anchor'      => $anchor,
                'sort'        => $sort,
                'values'      => $json,
            ]
        );

        return (int)DB::run("SELECT LAST_INSERT_ID() AS id")->fetch()['id'];
    }

    // Удаление экземпляра (например, при отключении инфобокса)
    public static function deleteInstance(int $instance_id): void
    {
        DB::run("DELETE FROM template_instances WHERE instance_id = :id", ['id' => $instance_id]);
    }

    // Обновление существующего экземпляра (правка встроенного блока)
    public static function updateInstance(int $instance_id, int $template_id, array $values): void
    {
        DB::run(
            "UPDATE template_instances
             SET template_id = :template_id, instance_values = :values
             WHERE instance_id = :id",
            [
                'template_id' => $template_id,
                'values'      => json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'id'          => $instance_id,
            ]
        );
    }

    // Встроенные блоки записи (slot = inline)
    public static function inlineInstances(int $item_id): array
    {
        $sql = "SELECT i.instance_id, i.template_id, i.instance_values, i.instance_sort,
                       t.template_code, t.template_title
                FROM template_instances i
                JOIN templates t ON t.template_id = i.template_id
                WHERE i.item_id = :item_id
                  AND i.instance_slot = 'inline'
                  AND i.instance_is_active = 1
                ORDER BY i.instance_sort ASC, i.instance_id ASC";

        $rows = DB::run($sql, ['item_id' => $item_id])->fetchAll();

        foreach ($rows as $key => $row) {
            $rows[$key]['values'] = self::decodeValues((string)($row['instance_values'] ?? ''));
        }

        return $rows;
    }

    // Экземпляр по id (для рендера маркера в тексте)
    public static function instanceById(int $instance_id): ?array
    {
        $row = DB::run(
            "SELECT i.instance_id, i.template_id, i.item_id, i.instance_slot, i.instance_values,
                    t.template_code, t.template_title, t.template_scope
             FROM template_instances i
             JOIN templates t ON t.template_id = i.template_id
             WHERE i.instance_id = :id AND i.instance_is_active = 1",
            ['id' => $instance_id]
        )->fetch();

        if (!$row) {
            return null;
        }

        $row['values'] = self::decodeValues((string)($row['instance_values'] ?? ''));
        $row['fields'] = self::fields((int)$row['template_id']);

        return $row;
    }

    // Создание встроенного блока (по умолчанию — в конец списка)
    public static function addInlineInstance(int $item_id, int $template_id, array $values): int
    {
        $sort = (int)DB::run(
            "SELECT COALESCE(MAX(instance_sort), 0) + 10 AS next_sort
             FROM template_instances
             WHERE item_id = :item_id AND instance_slot = 'inline'",
            ['item_id' => $item_id]
        )->fetch()['next_sort'];

        return self::saveInstance($item_id, $template_id, $values, 'inline', null, $sort);
    }

    // Значения экземпляра в виде массива
    public static function decodeValues(string $json): array
    {
        if ($json === '') {
            return [];
        }

        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }

    // Все шаблоны с числом полей и экземпляров (для админки)
    public static function allTemplates(): array
    {
        $sql = "SELECT t.template_id, t.template_code, t.template_title, t.template_scope,
                       t.template_description, t.template_sort, t.template_is_active, t.template_created,
                       (SELECT COUNT(*) FROM template_fields f WHERE f.template_id = t.template_id AND f.field_is_deleted = 0) AS fields_count,
                       (SELECT COUNT(*) FROM template_instances i WHERE i.template_id = t.template_id) AS instances_count
                FROM templates t
                ORDER BY t.template_scope ASC, t.template_sort ASC, t.template_title ASC";

        return DB::run($sql)->fetchAll();
    }

    // Один шаблон по id
    public static function template(int $id): ?array
    {
        $row = DB::run(
            "SELECT template_id, template_code, template_title, template_scope, template_description,
                    template_sort, template_is_active
             FROM templates
             WHERE template_id = :id",
            ['id' => $id]
        )->fetch();

        return $row ?: null;
    }

    // Создание или обновление шаблона
    public static function saveTemplate(array $data): int
    {
        $id = (int)($data['template_id'] ?? 0);

        $params = [
            'code'        => trim((string)($data['template_code'] ?? '')),
            'title'       => trim((string)($data['template_title'] ?? '')),
            'scope'       => in_array($data['template_scope'] ?? '', ['infobox', 'inline'], true) ? $data['template_scope'] : 'infobox',
            'description' => trim((string)($data['template_description'] ?? '')),
            'sort'        => (int)($data['template_sort'] ?? 0),
            'active'      => !empty($data['template_is_active']) ? 1 : 0,
        ];

        if ($id > 0) {
            DB::run(
                "UPDATE templates
                 SET template_code = :code, template_title = :title, template_scope = :scope,
                     template_description = :description, template_sort = :sort, template_is_active = :active
                 WHERE template_id = :id",
                $params + ['id' => $id]
            );

            return $id;
        }

        DB::run(
            "INSERT INTO templates (template_code, template_title, template_scope, template_description, template_sort, template_is_active)
             VALUES (:code, :title, :scope, :description, :sort, :active)",
            $params
        );

        return (int)DB::run("SELECT LAST_INSERT_ID() AS id")->fetch()['id'];
    }

    // Удаление шаблона вместе с полями и экземплярами
    public static function deleteTemplate(int $id): void
    {
        DB::run("DELETE FROM template_instances WHERE template_id = :id", ['id' => $id]);
        DB::run("DELETE FROM template_fields WHERE template_id = :id", ['id' => $id]);
        DB::run("DELETE FROM templates WHERE template_id = :id", ['id' => $id]);
    }

    // Создание или обновление поля шаблона
    public static function saveField(array $data, ?int $userId = null): int
    {
        $id = (int)($data['field_id'] ?? 0);

        $params = [
            'template_id' => (int)($data['template_id'] ?? 0),
            'key'         => trim((string)($data['field_key'] ?? '')),
            'label'       => trim((string)($data['field_label'] ?? '')),
            'type'        => in_array($data['field_type'] ?? '', ['text', 'textarea', 'number', 'date', 'url', 'image', 'select', 'link', 'relation', 'gallery'], true) ? $data['field_type'] : 'text',
            'required'    => !empty($data['field_required']) ? 1 : 0,
            'sort'        => (int)($data['field_sort'] ?? 0),
            'default'     => trim((string)($data['field_default'] ?? '')),
            'hint'        => trim((string)($data['field_hint'] ?? '')),
        ];

        if ($id > 0) {
            $existing = self::field($id);

            DB::run(
                "UPDATE template_fields
                 SET field_key = :key, field_label = :label, field_type = :type, field_required = :required,
                     field_sort = :sort, field_default = :default, field_hint = :hint
                 WHERE field_id = :id",
                [
                    'key'      => $params['key'],
                    'label'    => $params['label'],
                    'type'     => $params['type'],
                    'required' => $params['required'],
                    'sort'     => $params['sort'],
                    'default'  => $params['default'],
                    'hint'     => $params['hint'],
                    'id'       => $id,
                ]
            );

            if ($existing && (string)$existing['field_key'] !== $params['key']) {
                // Переименование ключа: переносим значения во всех экземплярах
                self::migrateKey((int)$existing['template_id'], (string)$existing['field_key'], (string)$params['key']);
                self::logField((int)$params['template_id'], $id, 'rename', (string)$params['key'], (string)$existing['field_key'], $userId);
            } elseif ($existing) {
                self::logField((int)$params['template_id'], $id, 'update', (string)$params['key'], null, $userId);
            }

            if ($params['type'] === 'select' && array_key_exists('field_options', $data)) {
                self::saveOptions($id, (string)$data['field_options']);
            }

            return $id;
        }

        DB::run(
            "INSERT INTO template_fields (template_id, field_key, field_label, field_type, field_required, field_sort, field_default, field_hint)
             VALUES (:template_id, :key, :label, :type, :required, :sort, :default, :hint)",
            $params
        );

        $fieldId = (int)DB::run("SELECT LAST_INSERT_ID() AS id")->fetch()['id'];
        self::logField((int)$params['template_id'], $fieldId, 'create', (string)$params['key'], null, $userId);

        if ($params['type'] === 'select' && array_key_exists('field_options', $data)) {
            self::saveOptions($fieldId, (string)$data['field_options']);
        }

        return $fieldId;
    }

    /**
     * Перенос значений при переименовании ключа поля.
     * Значения во всех экземплярах шаблона переезжают под новый ключ.
     */
    public static function migrateKey(int $template_id, string $oldKey, string $newKey): void
    {
        if ($oldKey === '' || $newKey === '' || $oldKey === $newKey) {
            return;
        }

        $rows = DB::run(
            "SELECT instance_id, instance_values FROM template_instances WHERE template_id = :template_id",
            ['template_id' => $template_id]
        )->fetchAll();

        foreach ($rows as $row) {
            $values = self::decodeValues((string)($row['instance_values'] ?? ''));

            if (!array_key_exists($oldKey, $values)) {
                continue;
            }

            if (!array_key_exists($newKey, $values)) {
                $values[$newKey] = $values[$oldKey];
            }

            unset($values[$oldKey]);

            DB::run(
                "UPDATE template_instances SET instance_values = :values WHERE instance_id = :id",
                [
                    'values' => json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'id'     => (int)$row['instance_id'],
                ]
            );
        }
    }

    // Мягкое удаление поля: значения остаются в экземплярах
    public static function deleteField(int $id, ?int $userId = null): void
    {
        $field = self::field($id);

        if (!$field) {
            return;
        }

        DB::run("UPDATE template_fields SET field_is_deleted = 1 WHERE field_id = :id", ['id' => $id]);
        self::logField((int)$field['template_id'], $id, 'delete', (string)$field['field_key'], null, $userId);
    }

    // Восстановление удалённого поля
    public static function restoreField(int $id, ?int $userId = null): void
    {
        $field = self::field($id);

        if (!$field) {
            return;
        }

        DB::run("UPDATE template_fields SET field_is_deleted = 0 WHERE field_id = :id", ['id' => $id]);
        self::logField((int)$field['template_id'], $id, 'restore', (string)$field['field_key'], null, $userId);
    }

    // Запись в журнал изменений схемы
    public static function logField(int $template_id, ?int $field_id, string $action, string $key, ?string $oldKey = null, ?int $userId = null): void
    {
        DB::run(
            "INSERT INTO template_field_history (template_id, field_id, action, field_key, old_key, user_id)
             VALUES (:template_id, :field_id, :action, :field_key, :old_key, :user_id)",
            [
                'template_id' => $template_id,
                'field_id'    => $field_id,
                'action'      => $action,
                'field_key'   => $key,
                'old_key'     => $oldKey,
                'user_id'     => $userId,
            ]
        );
    }

    // Последние изменения схемы шаблона
    public static function history(int $template_id, int $limit = 15): array
    {
        return DB::run(
            "SELECT h.history_id, h.action, h.field_key, h.old_key, h.created_at, u.name AS users_name
             FROM template_field_history h
             LEFT JOIN users u ON u.id = h.user_id
             WHERE h.template_id = :template_id
             ORDER BY h.history_id DESC
             LIMIT :limit",
            ['template_id' => $template_id, 'limit' => $limit]
        )->fetchAll();
    }

    // Переупорядочивание полей шаблона: ids в нужном порядке
    public static function reorderFields(int $template_id, array $ids): void
    {
        $sort = 10;
        foreach ($ids as $field_id) {
            $field_id = (int)$field_id;
            if ($field_id <= 0) {
                continue;
            }

            DB::run(
                "UPDATE template_fields SET field_sort = :sort WHERE field_id = :id AND template_id = :template_id",
                ['sort' => $sort, 'id' => $field_id, 'template_id' => $template_id]
            );

            $sort += 10;
        }
    }

    // Поле по id
    public static function field(int $id): ?array
    {
        $row = DB::run(
            "SELECT field_id, template_id, field_key, field_label, field_type, field_required, field_sort, field_default, field_hint
             FROM template_fields
             WHERE field_id = :id",
            ['id' => $id]
        )->fetch();

        return $row ?: null;
    }

    // Варианты select-поля
    public static function options(int $field_id): array
    {
        return DB::run(
            "SELECT option_id, option_value, option_label
             FROM template_field_options
             WHERE field_id = :id
             ORDER BY option_sort ASC, option_id ASC",
            ['id' => $field_id]
        )->fetchAll();
    }

    /**
     * Замена вариантов select-поля.
     * Каждая строка: «значение | подпись» или просто «значение».
     */
    public static function saveOptions(int $field_id, string $lines): void
    {
        DB::run("DELETE FROM template_field_options WHERE field_id = :id", ['id' => $field_id]);

        $sort = 10;
        foreach (preg_split('/\R/u', $lines) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $line, 2));
            $value = $parts[0];
            $label = $parts[1] ?? $parts[0];

            if ($value === '') {
                continue;
            }

            DB::run(
                "INSERT INTO template_field_options (field_id, option_value, option_label, option_sort)
                 VALUES (:field_id, :value, :label, :sort)",
                ['field_id' => $field_id, 'value' => $value, 'label' => $label, 'sort' => $sort]
            );

            $sort += 10;
        }
    }

    // Варианты в виде списка: значение => подпись
    public static function optionsMap(int $field_id): array
    {
        $map = [];

        foreach (self::options($field_id) as $option) {
            $map[(string)$option['option_value']] = (string)$option['option_label'];
        }

        return $map;
    }

    // Библиотека загруженных изображений: files + media_meta (по пути)
    public static function mediaLibrary(int $limit = 60): array
    {
        // Две выборки без SQL-джойна: у files и media_meta разные кодировки/коллации.
        $files = DB::run(
            "SELECT file_path AS path FROM files
             WHERE file_is_deleted = 0 AND file_path LIKE '/uploads/%'
             ORDER BY file_id DESC
             LIMIT :limit",
            ['limit' => $limit]
        )->fetchAll();

        $meta = DB::run(
            "SELECT media_path AS path, media_caption AS caption, media_alt AS alt
             FROM media_meta
             ORDER BY media_id DESC
             LIMIT :limit",
            ['limit' => $limit]
        )->fetchAll();

        $byPath = [];
        foreach ($meta as $row) {
            $byPath[(string)$row['path']] = $row;
        }

        $result = [];
        $seen = [];

        foreach ($meta as $row) {
            $path = (string)$row['path'];
            if ($path === '' || isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;
            $result[] = $row;
        }

        foreach ($files as $row) {
            $path = (string)$row['path'];
            if ($path === '' || isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;
            $result[] = ['path' => $path, 'caption' => null, 'alt' => null];
        }

        return array_slice($result, 0, $limit);
    }

    // Все медиафайлы с правами (для экрана «Медиафайлы»)
    public static function mediaRows(int $limit = 300): array
    {
        $meta = DB::run(
            "SELECT media_id, media_path AS path, media_alt AS alt, media_caption AS caption,
                    media_author AS author, media_source_url AS source, media_license AS license
             FROM media_meta
             ORDER BY media_id DESC
             LIMIT :limit",
            ['limit' => $limit]
        )->fetchAll();

        $files = DB::run(
            "SELECT file_path AS path
             FROM files
             WHERE file_is_deleted = 0 AND file_path LIKE '/uploads/%'
             ORDER BY file_id DESC
             LIMIT :limit",
            ['limit' => $limit]
        )->fetchAll();

        $seen = [];
        $result = [];

        foreach ($meta as $row) {
            $path = (string)$row['path'];
            if ($path === '' || isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;
            $result[] = $row;
        }

        foreach ($files as $row) {
            $path = (string)$row['path'];
            if ($path === '' || isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;
            $result[] = [
                'media_id' => 0,
                'path'     => $path,
                'alt'      => '',
                'caption'  => '',
                'author'   => '',
                'source'   => '',
                'license'  => '',
            ];
        }

        return $result;
    }

    // Пакетное сохранение прав на фото (экран «Медиафайлы»)
    public static function saveMediaBatch(array $rows): int
    {
        $saved = 0;

        foreach ($rows as $row) {
            $path = trim((string)($row['path'] ?? ''));
            if ($path === '') {
                continue;
            }

            $params = [
                'alt'     => trim((string)($row['alt'] ?? '')),
                'caption' => trim((string)($row['caption'] ?? '')),
                'author'  => trim((string)($row['author'] ?? '')),
                'source'  => trim((string)($row['source'] ?? '')),
                'license' => trim((string)($row['license'] ?? '')),
            ];

            $id = (int)($row['media_id'] ?? 0);

            if ($id > 0) {
                DB::run(
                    "UPDATE media_meta
                     SET media_alt = :alt, media_caption = :caption, media_author = :author,
                         media_source_url = :source, media_license = :license
                     WHERE media_id = :id",
                    $params + ['id' => $id]
                );
            } else {
                $exists = self::media($path);

                if ($exists) {
                    DB::run(
                        "UPDATE media_meta
                         SET media_alt = :alt, media_caption = :caption, media_author = :author,
                             media_source_url = :source, media_license = :license
                         WHERE media_id = :id",
                        $params + ['id' => (int)$exists['media_id']]
                    );
                } else {
                    DB::run(
                        "INSERT INTO media_meta (media_path, media_alt, media_caption, media_author, media_source_url, media_license)
                         VALUES (:path, :alt, :caption, :author, :source, :license)",
                        $params + ['path' => $path]
                    );
                }
            }

            $saved++;
        }

        return $saved;
    }

    // Метаданные медиафайла по пути (автор, источник, лицензия)
    public static function media(string $path): ?array
    {
        $row = DB::run(
            "SELECT media_id, file_id, media_path, media_alt, media_caption, media_author, media_source_url, media_license
             FROM media_meta
             WHERE media_path = :path
             LIMIT 1",
            ['path' => $path]
        )->fetch();

        return $row ?: null;
    }

    // Сохранение метаданных медиафайла
    public static function saveMedia(string $path, array $data): void
    {
        $exists = self::media($path);

        if ($exists) {
            DB::run(
                "UPDATE media_meta
                 SET media_alt = :alt, media_caption = :caption, media_author = :author,
                     media_source_url = :source, media_license = :license
                 WHERE media_id = :id",
                [
                    'alt'     => $data['alt'] ?? null,
                    'caption' => $data['caption'] ?? null,
                    'author'  => $data['author'] ?? null,
                    'source'  => $data['source'] ?? null,
                    'license' => $data['license'] ?? null,
                    'id'      => (int)$exists['media_id'],
                ]
            );

            return;
        }

        DB::run(
            "INSERT INTO media_meta (file_id, media_path, media_alt, media_caption, media_author, media_source_url, media_license)
             VALUES (:file_id, :path, :alt, :caption, :author, :source, :license)",
            [
                'file_id' => $data['file_id'] ?? null,
                'path'    => $path,
                'alt'     => $data['alt'] ?? null,
                'caption' => $data['caption'] ?? null,
                'author'  => $data['author'] ?? null,
                'source'  => $data['source'] ?? null,
                'license' => $data['license'] ?? null,
            ]
        );
    }
}
