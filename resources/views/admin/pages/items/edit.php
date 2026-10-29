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