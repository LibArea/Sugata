<main>
  <?= insert('/_block/navigation/breadcrumbs', [
    'list' => [
      [
        'name' => __('app.home'),
        'link' => url('homepage')
      ],
      [
        'name' => __('app.add_fact'),
        'link' => ''
      ],
    ],
    'sheet' =>  $data['sheet']
  ]);
  ?>

  <h1 class="uppercase-box"><?= __('app.add_fact'); ?></h1>

  <form action="<?= url('add.item', method: 'post'); ?>" method="post">
    <?= $container->csrf()->field(); ?>

    <fieldset class="form-big">
      <div class="form-label input-label"><label><?= __('app.title'); ?> <strong class="red">*</strong></label></div>
      <div class="form-element">
        <input minlength="11" id="title" name="item_title" required="" type="text" value="">
        <div class="help">11 - 250 <?= __('app.characters'); ?></div>
      </div>
    </fieldset>

    <fieldset class="form-big">
      <div class="form-label input-label"><label><?= __('app.category'); ?> <strong class="red">*</strong></label></div>
      <div class="form-element">
        <?= insert('/_block/form/select/category', ['category' => [], 'action' => 'add']); ?>
      </div>
    </fieldset>

    <?php if ($container->user()->admin()) : ?>
		<?= insert('/_block/form/content-type', ['tl' => 9]); ?>
	<?php endif; ?>

    <?= insert('/_block/form/thumb-foto', ['item' => []]); ?>

    <?= insert('/_block/form/editor/toolbar-img', ['height' => '300px', 'id' => 0]); ?>

    <fieldset class="form-big">
      <div class="form-label input-label"><label><?= __('app.note'); ?></label></div>
      <div class="form-element">
        <input minlength="11" id="item_note" name="item_note" type="text">
        <div class="help">> 24 <?= __('app.characters'); ?></div>
      </div>
    </fieldset>

    <fieldset class="form-big">
      <div class="form-label input-label"><label><?= __('app.source_title'); ?> </label></div>
      <div class="form-element">
        <input minlength="11" id="source_title" name="item_source_title" type="text" value="">
        <div class="help">11 - 250 <?= __('app.characters'); ?></div>
      </div>
    </fieldset>

    <fieldset class="form-big">
      <div class="form-label input-label"><label><?= __('app.source_url'); ?> </label></div>
      <div class="form-element">
        <input minlength="11" id="source_url" name="item_source_url" type="text" value="">
        <div class="help">11 - 250 <?= __('app.characters'); ?></div>
      </div>
    </fieldset>

    <?php if ($container->user()->admin()) : ?>
    <fieldset>
      <input type="checkbox" name="item_published"> <?= __('app.posted'); ?>
    </fieldset>
	<?php endif; ?>

    <?= Html::sumbit(__('app.add')); ?>
  </form>
</main>

<script src="/assets/js/tag/tagify.min.js"></script>
<link rel="stylesheet" href="/assets/js/tag/tagify.css" type="text/css">
<script src="/assets/js/cropper/cropper.min.js"></script>
<link rel="stylesheet" href="/assets/js/cropper/cropper.min.css" type="text/css">