<main>
  <div class="flex justify-between items-center mb20">
    <h1 class="title"><?= __('app.add_fact'); ?></h1>
  </div>

  <form action="<?= url('add.item', method: 'post'); ?>" method="post">
    <?= $container->csrf()->field(); ?>

    <div class="form-field-group">
      <label for="title"><strong><?= __('app.title'); ?> <span class="red">*</span></strong></label>
      <input id="title" name="item_title" required="" type="text" class="form-input-wide" value="<?= htmlEncode($data['prefill_title'] ?? ''); ?>">
      <div class="hint">1 - 250 <?= __('app.characters'); ?></div>
    </div>

    <?= insert('/shared/form/content-type', ['type' => 'fact', 'allowPage' => true]); ?>

    <div class="form-field-group">
      <label for="category_id"><strong><?= __('app.category'); ?> <span class="red">*</span></strong></label>
      <?= insert('/shared/form/select/category', ['action' => 'add', 'root' => 'facts']); ?>
    </div>

    <?= insert('/shared/form/select/select-related'); ?>

    <?= insert('/shared/form/editor/toolbar-img', ['height' => '300px', 'id' => 0]); ?>

    <div class="form-field-group">
      <label for="item_note"><strong><?= __('app.note'); ?></strong></label>
      <input minlength="11" id="item_note" name="item_note" type="text">
      <div class="hint">&gt; 24 <?= __('app.characters'); ?></div>
    </div>

    <div class="form-field-group">
      <label for="source_url"><strong><?= __('app.source_url'); ?></strong></label>
      <input id="source_url" name="item_source_url" type="url" value="" placeholder="https://...">
      <div class="hint"><?= __('app.source_url_help'); ?></div>
    </div>

    <?php if ($container->user()->admin()) : ?>
      <div class="form-field-group">
        <label class="clear" for="item_published">
          <input type="checkbox" name="item_published" id="item_published"> <?= __('app.posted'); ?>
        </label>
      </div>
    <?php endif; ?>

    <div class="form-actions">
      <?= Html::sumbit(__('app.add')); ?>
    </div>
  </form>
</main>

<script src="/assets/js/cropper/cropper.min.js"></script>
<link rel="stylesheet" href="/assets/js/cropper/cropper.min.css" type="text/css">