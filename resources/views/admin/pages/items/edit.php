<?php
$item = $data['item'];
// Тип контента: для фактов/статей — свои корни, для страниц (инфо) — info
$itemType = (string)($item['item_type'] ?? 'fact');
$root = match ($itemType) {
    'article' => 'articles',
    'page'    => 'info',
    default   => 'facts',
};
?>

<main class="admin-main admin-edit-page">
  <header class="admin-page-heading">
    <div>
      <p class="admin-record-id">ID <?= $item['item_id']; ?> · <span class="lowercase"><?= langDate($item['item_date']); ?></span></p>
      <h1 class="title"><?= __('app.edit_fact'); ?>
        <?php if (!empty($data['item_url'])) : ?>
          <a class="btn btn-small" target="_blank" rel="noopener" href="<?= htmlEncode(config('general', 'url') . '/mod/admin/preview' . $data['item_url']); ?>">
            <?= __('app.view'); ?>
          </a>
        <?php endif; ?>
      </h1>
    </div>
  </header>

  <div class="admin-edit-card">
  <form class="admin-edit-form" action="<?= url('edit.item', method: 'post'); ?>" method="post">
    <?= $container->csrf()->field(); ?>

    <fieldset class="form-big">
      <div class="form-label input-label"><label for="title"><?= __('app.title'); ?> <strong class="red">*</strong></label></div>
      <div class="form-element">
        <input id="title" name="item_title" required="" type="text" value="<?= htmlEncode($item['item_title']); ?>">
        <div class="help">11 - 250 <?= __('app.characters'); ?></div>
      </div>
    </fieldset>

    <?php if ($container->user()->admin()) : ?>
      <?= insert('/shared/form/content-type', ['type' => $itemType, 'allowPage' => true]); ?>
    <?php else : ?>
      <input type="hidden" name="content_kind" value="<?= htmlEncode($itemType); ?>">
    <?php endif; ?>

    <fieldset class="form-big">
      <div class="form-label input-label"><label for="category_id"><?= __('app.category'); ?> <strong class="red">*</strong></label></div>
      <div class="form-element">
        <?= insert('/shared/form/select/category', ['action' => 'edit', 'root' => $root, 'data' => $data]); ?>
      </div>
    </fieldset>

    <?= insert('/shared/form/select/select-related', ['data' => $data]); ?>

    <fieldset>
      <div class="form-label input-label"><label for="item_slug">SLUG (URL) <strong class="red">*</strong></label></div>
      <div class="form-element">
        <input id="item_slug" minlength="5" maxlength="250" value="<?= $item['item_slug']; ?>" type="text" required name="item_slug">
        <div class="help">> 5 <?= __('app.characters'); ?></div>
      </div>
    </fieldset>

    <?php if (!empty($data['item_url'])) : ?>
    <fieldset>
      <div class="form-label input-label"><label><?= __('app.item_url_copy'); ?></label></div>
      <div class="form-element">
        <div class="flex gap-sm">
          <input id="item_url_copy" type="text" readonly value="<?= htmlEncode($data['item_url']); ?>">
          <button type="button" class="btn btn-primary" id="item_url_copy_btn"><?= __('app.copy'); ?></button>
        </div>
        <div class="help"><?= __('app.item_url_copy_help'); ?></div>
      </div>
    </fieldset>
    <?php endif; ?>

    <?= insert('/shared/form/editor/toolbar-img', ['height'  => '300px', 'content' => $item['item_content'], 'type' => 'item_content', 'id' => $item['item_id']]); ?>

    <fieldset class="form-big">
      <div class="form-label input-label"><label for="item_note"><?= __('app.note'); ?></label></div>
      <div class="form-element">
        <input minlength="11" id="item_note" name="item_note" type="text" value="<?= $item['item_note']; ?>">
        <div class="help">> 24 <?= __('app.characters'); ?></div>
      </div>
    </fieldset>

    <fieldset class="form-big">
      <div class="form-label input-label"><label for="item_source_url"><?= __('app.source_url'); ?> </label></div>
      <div class="form-element">
        <input id="item_source_url" name="item_source_url" type="url" value="<?= htmlEncode($item['item_source_url'] ?? ''); ?>" placeholder="https://...">
        <div class="help"><?= __('app.source_url_help'); ?></div>
      </div>
    </fieldset>

    <fieldset class="form-big">
      <div class="form-label input-label"><label><?= __('app.faq'); ?></label></div>
      <div class="form-element">
        <div id="faq-items">
          <?php foreach ($data['faq'] ?? [] as $i => $qa) : ?>
            <div class="faq-row">
              <input class="faq-q mb5" type="text" name="faq_question[]" placeholder="<?= __('app.faq_question'); ?>" value="<?= htmlEncode($qa['question']); ?>">
              <textarea class="faq-a mb5" name="faq_answer[]" rows="3" placeholder="<?= __('app.faq_answer'); ?>"><?= htmlEncode($qa['answer']); ?></textarea>
              <div><button type="button" class="btn btn-small faq-row-del"><?= __('app.faq_remove'); ?></button></div>
            </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-primary" id="faq-add">+ <?= __('app.faq_add'); ?></button>
        <div class="help"><?= __('app.faq_help'); ?></div>
      </div>
    </fieldset>

    <?php if ($container->user()->admin()) : ?>
      <?= insert('/shared/form/select/user', ['user' => $data['user']]); ?>

      <fieldset class="admin-publish-field">
        <label class="admin-publish-toggle" for="item_published">
          <input id="item_published" type="checkbox" name="item_published" <?php if ($item['item_published'] == 1) : ?>checked <?php endif; ?>>
          <span class="red"><?= __('app.posted'); ?></span>
        </label>
      </fieldset>

      <?php if ($itemType !== 'article') : ?>
        <fieldset class="admin-publish-field">
          <label class="admin-publish-toggle" for="item_best">
            <input id="item_best" type="checkbox" name="item_best" <?php if (!empty($item['item_best'])) : ?>checked <?php endif; ?>>
            <span class="green"><?= __('app.best_fact'); ?></span>
          </label>
        </fieldset>
      <?php endif; ?>
    <?php endif; ?>

    <input type="hidden" name="item_id" value="<?= $item['item_id']; ?>">
    <?= Html::sumbit(__('app.edit')); ?>
  </form>
  </div>
</main>

<script src="/assets/js/cropper/cropper.min.js"></script>
<link rel="stylesheet" href="/assets/js/cropper/cropper.min.css" type="text/css">

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

    var wrap = document.getElementById('faq-items');
    var addBtn = document.getElementById('faq-add');
    if (!wrap || !addBtn) return;

    function makeRow(q, a) {
      var row = document.createElement('div');
      row.className = 'faq-row';
      row.innerHTML =
        '<input class="faq-q mb5" type="text" name="faq_question[]" placeholder="' + '<?= __('app.faq_question'); ?>' + '" value="' + (q || '') + '">' +
        '<textarea class="faq-a mb5" name="faq_answer[]" rows="3" placeholder="' + '<?= __('app.faq_answer'); ?>' + '">' + (a || '') + '</textarea>' +
        '<div><button type="button" class="btn btn-small faq-row-del">' + '<?= __('app.faq_remove'); ?>' + '</button></div>';
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
