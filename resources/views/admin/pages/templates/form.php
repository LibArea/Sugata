<?php
$template = $data['template'] ?? null;
$fields   = $data['fields'] ?? [];
$field    = $data['field'] ?? null;

$tid = (int)($template['template_id'] ?? 0);
$scope = (string)($template['template_scope'] ?? 'infobox');
?>

<main class="content">
  <nav class="nav">
    <?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
  </nav>

  <div class="flex justify-between items-center mb20">
    <div>
      <p class="hint"><a href="<?= url('templates'); ?>">← <?= __('app.templates'); ?></a></p>
      <h1 class="title"><?= $template ? __('app.template_edit') : __('app.template_add'); ?></h1>
    </div>
  </div>

  <form action="<?= url('template.save', method: 'post'); ?>" method="post">
    <?= $container->csrf()->field(); ?>
    <?php if ($tid > 0) : ?>
      <input type="hidden" name="template_id" value="<?= $tid; ?>">
    <?php endif; ?>

    <div class="form-field-group">
      <label for="template_title"><strong><?= __('app.title'); ?> <span class="red">*</span></strong></label>
      <input id="template_title" name="template_title" type="text" class="form-input-wide" required value="<?= htmlEncode($template['template_title'] ?? ''); ?>">
    </div>

    <div class="form-field-group">
      <label for="template_code"><strong><?= __('app.template_code'); ?> <span class="red">*</span></strong></label>
      <input id="template_code" name="template_code" type="text" required value="<?= htmlEncode($template['template_code'] ?? ''); ?>">
      <div class="hint"><?= __('app.template_code_help'); ?></div>
    </div>

    <div class="form-field-group">
      <label for="template_scope"><strong><?= __('app.template_scope'); ?></strong></label>
      <select id="template_scope" name="template_scope">
        <option value="infobox" <?= $scope === 'infobox' ? 'selected' : ''; ?>><?= __('app.template_scope_infobox'); ?></option>
        <option value="inline" <?= $scope === 'inline' ? 'selected' : ''; ?>><?= __('app.template_scope_inline'); ?></option>
      </select>
      <div class="hint"><?= __('app.template_scope_help'); ?></div>
    </div>

    <div class="form-field-group">
      <label for="template_description"><strong><?= __('app.template_description'); ?></strong></label>
      <input id="template_description" name="template_description" type="text" value="<?= htmlEncode($template['template_description'] ?? ''); ?>">
    </div>

    <div class="form-field-group">
      <label for="template_sort"><strong><?= __('app.template_sort'); ?></strong></label>
      <input id="template_sort" name="template_sort" type="number" value="<?= (int)($template['template_sort'] ?? 0); ?>">
    </div>

    <div class="form-field-group">
      <label class="clear" for="template_is_active">
        <input id="template_is_active" type="checkbox" name="template_is_active" <?= (!$template || !empty($template['template_is_active'])) ? 'checked' : ''; ?>>
        <span><?= __('app.template_active'); ?></span>
      </label>
    </div>

    <div class="form-actions">
      <?= Html::sumbit($template ? __('app.save') : __('app.add')); ?>
    </div>
  </form>

  <?php if ($tid > 0) : ?>
    <h2 class="title"><?= __('app.template_fields'); ?></h2>

    <?php if ($fields) : ?>
      <table class="data">
        <thead>
          <tr>
            <th><?= __('app.field_label'); ?></th>
            <th><?= __('app.field_key'); ?></th>
            <th><?= __('app.field_type'); ?></th>
            <th><?= __('app.field_required'); ?></th>
            <th><?= __('app.field_sort'); ?></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($fields as $f) : ?>
            <?php $isDeleted = !empty($f['field_is_deleted']); ?>
            <tr data-field-id="<?= (int)$f['field_id']; ?>"<?= $isDeleted ? ' style="opacity:.55"' : ''; ?>>
              <td>
                <?= htmlEncode($f['field_label']); ?>
                <?php if ($isDeleted) : ?>
                  <div class="hint"><?= __('app.field_deleted'); ?></div>
                <?php endif; ?>
              </td>
              <td><code><?= htmlEncode($f['field_key']); ?></code></td>
              <td><?= htmlEncode($f['field_type']); ?></td>
              <td><?= !empty($f['field_required']) ? __('app.yes') : '—'; ?></td>
              <td><?= (int)$f['field_sort']; ?></td>
              <td>
                <div class="flex gap">
                  <?php if ($isDeleted) : ?>
                    <form action="<?= url('template.field.restore', method: 'post'); ?>" method="post">
                      <?= $container->csrf()->field(); ?>
                      <input type="hidden" name="template_id" value="<?= $tid; ?>">
                      <input type="hidden" name="field_id" value="<?= (int)$f['field_id']; ?>">
                      <button class="btn" type="submit"><?= __('app.field_restore'); ?></button>
                    </form>
                  <?php else : ?>
                    <a class="btn" href="<?= url('template.edit', ['id' => $tid]); ?>?field_id=<?= (int)$f['field_id']; ?>"><?= __('app.edit'); ?></a>
                    <form action="<?= url('template.field.delete', method: 'post'); ?>" method="post" onsubmit="return confirm('<?= __('app.field_delete_confirm'); ?>');">
                      <?= $container->csrf()->field(); ?>
                      <input type="hidden" name="template_id" value="<?= $tid; ?>">
                      <input type="hidden" name="field_id" value="<?= (int)$f['field_id']; ?>">
                      <button class="btn btn--danger" type="submit"><?= __('app.delete'); ?></button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else : ?>
      <div class="hint mb20"><?= __('app.template_fields_empty'); ?></div>
    <?php endif; ?>

    <?php if (!empty($data['history'])) : ?>
      <h2 class="title"><?= __('app.schema_history'); ?></h2>
      <table class="data">
        <thead>
          <tr>
            <th><?= __('app.schema_history_action'); ?></th>
            <th><?= __('app.field_key'); ?></th>
            <th><?= __('app.schema_history_old'); ?></th>
            <th><?= __('app.schema_history_user'); ?></th>
            <th><?= __('app.schema_history_date'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data['history'] as $h) : ?>
            <tr>
              <td><?= __('app.schema_action_' . htmlEncode($h['action'])); ?></td>
              <td><code><?= htmlEncode($h['field_key']); ?></code></td>
              <td><?= !empty($h['old_key']) ? htmlEncode($h['old_key']) : '—'; ?></td>
              <td><?= htmlEncode($h['users_name'] ?? '—'); ?></td>
              <td><?= htmlEncode($h['created_at']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <h2 class="title"><?= $field ? __('app.field_edit') : __('app.field_add'); ?></h2>

    <form action="<?= url('template.field.save', method: 'post'); ?>" method="post">
      <?= $container->csrf()->field(); ?>
      <input type="hidden" name="template_id" value="<?= $tid; ?>">
      <?php if ($field) : ?>
        <input type="hidden" name="field_id" value="<?= (int)$field['field_id']; ?>">
      <?php endif; ?>

      <div class="form-field-group">
        <label for="field_label"><strong><?= __('app.field_label'); ?> <span class="red">*</span></strong></label>
        <input id="field_label" name="field_label" type="text" required value="<?= htmlEncode($field['field_label'] ?? ''); ?>">
      </div>

      <div class="form-field-group">
        <label for="field_key"><strong><?= __('app.field_key'); ?> <span class="red">*</span></strong></label>
        <input id="field_key" name="field_key" type="text" required value="<?= htmlEncode($field['field_key'] ?? ''); ?>">
        <div class="hint"><?= __('app.field_key_help'); ?></div>
      </div>

      <div class="form-field-group">
        <label for="field_type"><strong><?= __('app.field_type'); ?></strong></label>
        <select id="field_type" name="field_type">
          <?php foreach (['text', 'textarea', 'number', 'date', 'url', 'image', 'select', 'link', 'relation', 'gallery'] as $type) : ?>
            <option value="<?= $type; ?>" <?= (($field['field_type'] ?? 'text') === $type) ? 'selected' : ''; ?>><?= $type; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-field-group">
        <label for="field_sort"><strong><?= __('app.field_sort'); ?></strong></label>
        <input id="field_sort" name="field_sort" type="number" value="<?= (int)($field['field_sort'] ?? 0); ?>">
      </div>

      <div class="form-field-group">
        <label for="field_default"><strong><?= __('app.field_default'); ?></strong></label>
        <input id="field_default" name="field_default" type="text" value="<?= htmlEncode($field['field_default'] ?? ''); ?>">
      </div>

      <div class="form-field-group">
        <label for="field_hint"><strong><?= __('app.field_hint'); ?></strong></label>
        <input id="field_hint" name="field_hint" type="text" value="<?= htmlEncode($field['field_hint'] ?? ''); ?>">
      </div>

      <div class="form-field-group" id="field-options-wrap"<?= (($field['field_type'] ?? 'text') === 'select') ? '' : ' hidden'; ?>>
        <label for="field_options"><strong><?= __('app.field_options'); ?></strong></label>
        <textarea id="field_options" name="field_options" rows="4" placeholder="значение | Подпись"><?= htmlEncode($data['field_options_text'] ?? ''); ?></textarea>
        <div class="hint"><?= __('app.field_options_help'); ?></div>
      </div>

      <div class="form-field-group">
        <label class="clear" for="field_required">
          <input id="field_required" type="checkbox" name="field_required" <?= !empty($field['field_required']) ? 'checked' : ''; ?>>
          <span><?= __('app.field_required'); ?></span>
        </label>
      </div>

      <div class="form-actions">
        <?= Html::sumbit($field ? __('app.save') : __('app.add')); ?>
        <?php if ($field) : ?>
          <a class="btn" href="<?= url('template.edit', ['id' => $tid]); ?>"><?= __('app.cancel'); ?></a>
        <?php endif; ?>
      </div>
    </form>
  <?php endif; ?>
</main>

<script src="/assets/js/admin-templates.js?v=<?= (int)config('general', 'version'); ?>"></script>
