<?php
$item = $data['item'];
// Тип контента: для фактов/статей — свои корни, для страниц (инфо) — info
$itemType = (string)($item['item_type'] ?? 'fact');
$root = match ($itemType) {
    'article' => 'articles',
    'site'    => 'web',
    'page'    => 'info',
    default   => 'facts',
};
?>

<main>
  <div class="flex justify-between items-center mb20">
    <div>
      <p class="hint">ID <?= $item['item_id']; ?> · <span class="lowercase"><?= langDate($item['item_date']); ?></span></p>
      <h1 class="title"><?= __('app.edit_fact'); ?></h1>
    </div>
    <div class="flex gap">
      <?php if (!empty($data['item_url'])) : ?>
        <a class="btn" target="_blank" rel="noopener" href="<?= htmlEncode(config('general', 'url') . '/mod/admin/preview' . $data['item_url']); ?>">
          <?= __('app.view'); ?>
        </a>
      <?php endif; ?>
      <a class="btn" href="<?= url('item.history', ['id' => $item['item_id']]); ?>">
        <?= __('app.item_history'); ?>
      </a>
    </div>
  </div>

  <form action="<?= url('edit.item', method: 'post'); ?>" method="post">
    <?= $container->csrf()->field(); ?>

    <div class="form-field-group">
      <label for="title"><strong><?= __('app.title'); ?> <span class="red">*</span></strong></label>
      <input id="title" name="item_title" required="" type="text" class="form-input-wide" value="<?= htmlEncode($item['item_title']); ?>">
      <div class="hint">1 - 250 <?= __('app.characters'); ?></div>
    </div>

    <?php if ($container->user()->admin()) : ?>
      <?= insert('/shared/form/content-type', ['type' => $itemType, 'allowPage' => true]); ?>
    <?php else : ?>
      <input type="hidden" name="content_kind" value="<?= htmlEncode($itemType); ?>">
    <?php endif; ?>

    <div class="form-field-group">
      <label for="category_id"><strong><?= __('app.category'); ?> <span class="red">*</span></strong></label>
      <?= insert('/shared/form/select/category', ['action' => 'edit', 'root' => $root, 'data' => $data]); ?>
    </div>

    <?= insert('/shared/form/select/select-related', ['data' => $data]); ?>

    <div class="form-field-group">
      <label for="item_slug"><strong>SLUG (URL) <span class="red">*</span></strong></label>
      <input id="item_slug" minlength="5" maxlength="250" value="<?= $item['item_slug']; ?>" type="text" required name="item_slug">
      <div class="hint">&gt; 5 <?= __('app.characters'); ?></div>
    </div>

    <?php if (!empty($data['item_url'])) : ?>
    <div class="form-field-group">
      <label><strong><?= __('app.item_url_copy'); ?></strong></label>
      <div class="flex gap">
        <input id="item_url_copy" type="text" readonly value="<?= htmlEncode($data['item_url']); ?>">
        <button type="button" class="btn" id="item_url_copy_btn"><?= __('app.copy'); ?></button>
      </div>
      <div class="hint"><?= __('app.item_url_copy_help'); ?></div>
    </div>
    <?php endif; ?>

    <?= insert('/shared/form/editor/toolbar-img', ['height'  => '300px', 'content' => $item['item_content'], 'type' => 'item_content', 'id' => $item['item_id']]); ?>

    <div class="form-field-group">
      <label for="item_note"><strong><?= __('app.note'); ?></strong></label>
      <input minlength="11" id="item_note" name="item_note" type="text" value="<?= $item['item_note']; ?>">
      <div class="hint">&gt; 24 <?= __('app.characters'); ?></div>
    </div>

    <div class="form-field-group">
      <label for="item_source_url"><strong><?= __('app.source_url'); ?></strong></label>
      <input id="item_source_url" name="item_source_url" type="url" value="<?= htmlEncode($item['item_source_url'] ?? ''); ?>" placeholder="https://...">
      <div class="hint"><?= __('app.source_url_help'); ?></div>
    </div>

    <div class="form-field-group">
      <label><strong><?= __('app.faq'); ?></strong></label>
      <div id="faq-items">
        <?php foreach ($data['faq'] ?? [] as $i => $qa) : ?>
          <div class="faq-row">
            <input class="faq-q mb5" type="text" name="faq_question[]" placeholder="<?= __('app.faq_question'); ?>" value="<?= htmlEncode($qa['question']); ?>">
            <textarea class="faq-a mb5" name="faq_answer[]" rows="3" placeholder="<?= __('app.faq_answer'); ?>"><?= htmlEncode($qa['answer']); ?></textarea>
            <div><button type="button" class="btn faq-row-del"><?= __('app.faq_remove'); ?></button></div>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn" id="faq-add">+ <?= __('app.faq_add'); ?></button>
      <div class="hint"><?= __('app.faq_help'); ?></div>
    </div>

    <?php
    $ibTemplates = $data['infobox_templates'] ?? [];
    $ibFields    = $data['infobox_fields'] ?? [];
    $ib          = $data['infobox'] ?? null;
    $ibValues    = $ib['values'] ?? [];
    $ibCurrent   = (int)($ib['template_id'] ?? 0);
    ?>
    <div class="form-field-group">
      <label for="infobox_template"><strong><?= __('app.infobox'); ?></strong></label>
      <select id="infobox_template" name="infobox_template">
        <option value="0"><?= __('app.infobox_none'); ?></option>
        <?php foreach ($ibTemplates as $tpl) : ?>
          <option value="<?= (int)$tpl['template_id']; ?>" <?= $ibCurrent === (int)$tpl['template_id'] ? 'selected' : ''; ?>><?= htmlEncode($tpl['template_title']); ?></option>
        <?php endforeach; ?>
      </select>
      <div class="hint"><?= __('app.infobox_help'); ?></div>

      <?php foreach ($ibTemplates as $tpl) :
        $tid = (int)$tpl['template_id']; ?>
        <div class="infobox-fields" data-template="<?= $tid; ?>" <?= $ibCurrent === $tid ? '' : 'hidden'; ?>>
          <?php foreach ($ibFields[$tid] ?? [] as $field) :
            $fieldKey  = (string)$field['field_key'];
            $fieldVal  = (string)($ibValues[$fieldKey] ?? (string)($field['field_default'] ?? ''));
            $fieldName = 'infobox_' . $tid . '[' . $fieldKey . ']';
            ?>
            <div class="form-field-group">
              <label><?= htmlEncode($field['field_label']); ?><?= !empty($field['field_required']) ? ' <span class="red">*</span>' : ''; ?></label>
              <?php if (($field['field_type'] ?? 'text') === 'image') : ?>
                <div class="media-picker"
                     data-name="<?= htmlEncode($fieldName); ?>"
                     data-item="<?= (int)$item['item_id']; ?>"
                     data-from-text="<?= htmlEncode($data['content_image'] ?? ''); ?>">
                  <input type="hidden" name="<?= htmlEncode($fieldName); ?>" value="<?= htmlEncode($fieldVal); ?>" class="media-picker__input">
                  <div class="media-picker__preview">
                    <?php if ($fieldVal !== '') : ?>
                      <img src="<?= htmlEncode($fieldVal); ?>" alt="">
                    <?php else : ?>
                      <span class="hint"><?= __('app.media_none'); ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="flex gap media-picker__actions">
                    <button type="button" class="btn media-picker__upload"><?= __('app.media_upload'); ?></button>
                    <button type="button" class="btn media-picker__choose"><?= __('app.media_choose'); ?></button>
                    <?php if (!empty($data['content_image'])) : ?>
                      <button type="button" class="btn media-picker__text"><?= __('app.media_from_text'); ?></button>
                    <?php endif; ?>
                    <button type="button" class="btn media-picker__clear"><?= __('app.media_clear'); ?></button>
                  </div>
                  <input type="file" accept="image/*" class="none media-picker__file">
                </div>
              <?php elseif (($field['field_type'] ?? 'text') === 'link') : ?>
                <input type="text" name="<?= htmlEncode($fieldName); ?>" value="<?= htmlEncode($fieldVal); ?>" placeholder="/facts/… или https://…">
                <div class="hint"><?= __('app.field_link_help'); ?></div>
              <?php elseif (($field['field_type'] ?? 'text') === 'date') : ?>
                <input type="date" name="<?= htmlEncode($fieldName); ?>" value="<?= htmlEncode($fieldVal); ?>">
              <?php elseif (($field['field_type'] ?? 'text') === 'select') : ?>
                <?php $selectOptions = \App\Models\TemplateModel::optionsMap((int)$field['field_id']); ?>
                <select name="<?= htmlEncode($fieldName); ?>">
                  <option value="">—</option>
                  <?php foreach ($selectOptions as $optValue => $optLabel) : ?>
                    <option value="<?= htmlEncode($optValue); ?>" <?= (string)$fieldVal === (string)$optValue ? 'selected' : ''; ?>><?= htmlEncode($optLabel); ?></option>
                  <?php endforeach; ?>
                </select>
              <?php elseif (($field['field_type'] ?? 'text') === 'textarea') : ?>
                <textarea name="<?= htmlEncode($fieldName); ?>" rows="3"><?= htmlEncode($fieldVal); ?></textarea>
              <?php else : ?>
                <input type="text" name="<?= htmlEncode($fieldName); ?>" value="<?= htmlEncode($fieldVal); ?>">
              <?php endif; ?>
              <?php if (!empty($field['field_hint'])) : ?>
                <div class="hint"><?= htmlEncode($field['field_hint']); ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="form-field-group">
      <label><strong><?= __('app.media_meta'); ?></strong></label>
      <?php if (!empty($data['infobox_image'])) : ?>
        <div class="hint mb5"><?= htmlEncode($data['infobox_image']); ?></div>
      <?php endif; ?>
      <input name="media_author" type="text" placeholder="<?= __('app.media_author'); ?>" value="<?= htmlEncode($data['media']['media_author'] ?? ''); ?>">
      <input name="media_source" type="text" placeholder="<?= __('app.media_source'); ?>" value="<?= htmlEncode($data['media']['media_source_url'] ?? ''); ?>">
      <input name="media_license" type="text" placeholder="<?= __('app.media_license'); ?>" value="<?= htmlEncode($data['media']['media_license'] ?? ''); ?>">
      <div class="hint"><?= __('app.media_help'); ?></div>
    </div>

    <div class="form-field-group">
      <label><strong><?= __('app.inline_blocks'); ?></strong></label>
      <div class="hint mb5"><?= __('app.inline_blocks_help'); ?></div>

      <?php foreach ($data['inline_instances'] ?? [] as $bl) : ?>
        <?php $marker = '{{' . $bl['template_code'] . ':' . (int)$bl['instance_id'] . '}}'; ?>
        <div class="box mb5">
          <div class="flex justify-between items-center">
            <div>
              <b><?= htmlEncode($bl['template_title']); ?></b>
              <code><?= htmlEncode($marker); ?></code>
            </div>
            <div class="flex gap">
              <a class="btn" href="<?= url('item.form.edit', ['id' => (int)$item['item_id']]); ?>?inline_id=<?= (int)$bl['instance_id']; ?>"><?= __('app.edit'); ?></a>
              <a class="btn" href="<?= url('inline.delete', ['id' => (int)$bl['instance_id']]); ?>" onclick="return confirm('<?= __('app.inline_delete_confirm'); ?>');"><?= __('app.delete'); ?></a>
            </div>
          </div>
          <div class="hint"><?= htmlEncode(implode(' · ', array_filter(array_map('strval', $bl['values'] ?? [])))); ?></div>
        </div>
      <?php endforeach; ?>

      <?php
      $inlineTemplates = $data['inline_templates'] ?? [];
      $inlineEdit      = $data['inline_edit'] ?? null;
      $inlineEditTid   = (int)($inlineEdit['template_id'] ?? 0);
      $inlineEditVals  = $inlineEdit['values'] ?? [];
      ?>
      <?php if ($inlineTemplates) : ?>
        <?php if ($inlineEdit) : ?>
          <div class="hint mb5"><?= __('app.inline_edit_hint'); ?>
            <a href="<?= url('item.form.edit', ['id' => (int)$item['item_id']]); ?>"><?= __('app.cancel'); ?></a>
          </div>
          <input type="hidden" name="inline_instance_id" value="<?= (int)$inlineEdit['instance_id']; ?>">
        <?php endif; ?>
        <select id="inline_template" name="inline_template">
          <option value="0"<?= $inlineEditTid === 0 ? ' selected' : ''; ?>><?= __('app.inline_none'); ?></option>
          <?php foreach ($inlineTemplates as $tpl) : ?>
            <option value="<?= (int)$tpl['template_id']; ?>"<?= $inlineEditTid === (int)$tpl['template_id'] ? ' selected' : ''; ?>><?= htmlEncode($tpl['template_title']); ?></option>
          <?php endforeach; ?>
        </select>

        <?php foreach ($inlineTemplates as $tpl) :
          $itid = (int)$tpl['template_id']; ?>
          <div class="inline-fields" data-template="<?= $itid; ?>" hidden>
            <?php foreach (($data['inline_fields'][$itid] ?? []) as $field) :
              $iKey = (string)$field['field_key'];
              $iVal = $inlineEditTid === $itid ? (string)($inlineEditVals[$iKey] ?? '') : '';
              ?>
              <div class="form-field-group">
                <label><?= htmlEncode($field['field_label']); ?></label>
                <?php if (($field['field_type'] ?? 'text') === 'textarea') : ?>
                  <textarea name="inline_<?= $itid; ?>[<?= htmlEncode($iKey); ?>]" rows="3"><?= htmlEncode($iVal); ?></textarea>
                <?php else : ?>
                  <input type="text" name="inline_<?= $itid; ?>[<?= htmlEncode($iKey); ?>]" value="<?= htmlEncode($iVal); ?>">
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>

        <div class="hint"><?= __('app.inline_add_help'); ?></div>
      <?php endif; ?>
    </div>

    <?php if ($container->user()->admin()) : ?>
      <?= insert('/shared/form/select/user', ['user' => $data['user']]); ?>

      <div class="form-field-group">
        <label class="clear" for="item_published">
          <input id="item_published" type="checkbox" name="item_published" <?php if ($item['item_published'] == 1) : ?>checked <?php endif; ?>>
          <span class="red"><?= __('app.posted'); ?></span>
        </label>
        <?php if ($itemType !== 'article') : ?>
          <label class="clear" for="item_best">
            <input id="item_best" type="checkbox" name="item_best" <?php if (!empty($item['item_best'])) : ?>checked <?php endif; ?>>
            <span class="green"><?= __('app.best_fact'); ?></span>
          </label>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="form-field-group">
      <label for="item_edit_comment"><strong><?= __('app.edit_comment'); ?></strong></label>
      <input id="item_edit_comment" name="item_edit_comment" type="text" value="" placeholder="<?= __('app.edit_comment_help'); ?>">
    </div>

    <input type="hidden" name="item_id" value="<?= $item['item_id']; ?>">
    <div class="form-actions">
      <?= Html::sumbit(__('app.edit')); ?>
    </div>
  </form>
</main>

<script src="/assets/js/cropper/cropper.min.js"></script>
<link rel="stylesheet" href="/assets/js/cropper/cropper.min.css" type="text/css">
<script src="/assets/js/admin-media-picker.js?v=<?= (int)config('general', 'version'); ?>"></script>

<script nonce="<?= config('main', 'nonce'); ?>">
  (function () {
    var copyBtn = document.getElementById('item_url_copy_btn');
    var copyInput = document.getElementById('item_url_copy');
    if (copyBtn && copyInput) {
      copyBtn.addEventListener('click', function () {
        copyInput.select();
        document.execCommand('copy');
      });
    }

    var ibSelect = document.getElementById('infobox_template');
    if (ibSelect) {
      var syncInfobox = function () {
        var value = ibSelect.value;
        document.querySelectorAll('.infobox-fields').forEach(function (box) {
          box.hidden = box.getAttribute('data-template') !== value;
        });
      };
      ibSelect.addEventListener('change', syncInfobox);
      syncInfobox();
    }

    var inlineSelect = document.getElementById('inline_template');
    if (inlineSelect) {
      var syncInline = function () {
        var inlineValue = inlineSelect.value;
        document.querySelectorAll('.inline-fields').forEach(function (box) {
          box.hidden = box.getAttribute('data-template') !== inlineValue;
        });
      };
      inlineSelect.addEventListener('change', syncInline);
      syncInline();
    }

    var wrap = document.getElementById('faq-items');
    var addBtn = document.getElementById('faq-add');
    if (!wrap || !addBtn) return;

    function makeRow(q, a) {
      var row = document.createElement('div');
      row.className = 'faq-row';
      row.innerHTML =
        '<input class="faq-q mb5" type="text" name="faq_question[]" placeholder="' + '<?= __('app.faq_question'); ?>' + '" value="' + (q || '') + '">' +
        '<textarea class="faq-a mb5" name="faq_answer[]" rows="3" placeholder="' + '<?= __('app.faq_answer'); ?>' + '">' + (a || '') + '</textarea>' +
        '<div><button type="button" class="btn faq-row-del">' + '<?= __('app.faq_remove'); ?>' + '</button></div>';
      row.querySelector('.faq-row-del').addEventListener('click', function () {
        row.remove();
      });
      return row;
    }

    addBtn.addEventListener('click', function () {
      wrap.appendChild(makeRow('', ''));
    });

    wrap.querySelectorAll('.faq-row').forEach(function (row) {
      var del = row.querySelector('.faq-row-del');
      if (del) {
        del.addEventListener('click', function () {
          row.remove();
        });
      }
    });
  })();
</script>