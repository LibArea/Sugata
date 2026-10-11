<main>
  <div class="nav-bar">
    <ul class="nav scroll-menu">
      <?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
    </ul>
  </div>

  <h1 class="title"><?= __('app.add_fact'); ?></h1>

  <form class="mt20" action="<?= url('add.item', method: 'post'); ?>" method="post">
    <?= $container->csrf()->field(); ?>

    <fieldset class="form-big">
      <div class="form-label input-label"><label><?= __('app.title'); ?> <strong class="red">*</strong></label></div>
      <div class="form-element">
        <input minlength="11" id="title" name="item_title" required="" type="text" value="">
        <div class="help">11 - 250 <?= __('app.characters'); ?></div>
      </div>
    </fieldset>

    <?= insert('/shared/form/content-type', ['type' => 'fact', 'allowPage' => true]); ?>

    <fieldset class="form-big">
      <div class="form-label input-label"><label for="category_id"><?= __('app.category'); ?> <strong class="red">*</strong></label></div>
      <div class="form-element">
        <?= insert('/shared/form/select/category', ['action' => 'add', 'root' => 'facts']); ?>
      </div>
    </fieldset>

    <?= insert('/shared/form/select/select-related'); ?>

    <?= insert('/shared/form/editor/toolbar-img', ['height' => '300px', 'id' => 0]); ?>

    <fieldset class="form-big">
      <div class="form-label input-label"><label><?= __('app.note'); ?></label></div>
      <div class="form-element">
        <input minlength="11" id="item_note" name="item_note" type="text">
        <div class="help">> 24 <?= __('app.characters'); ?></div>
      </div>
    </fieldset>

    <fieldset class="form-big">
      <div class="form-label input-label"><label><?= __('app.source_url'); ?> </label></div>
      <div class="form-element">
        <input id="source_url" name="item_source_url" type="url" value="" placeholder="https://...">
        <div class="help"><?= __('app.source_url_help'); ?></div>
      </div>
    </fieldset>

    <?php if ($container->user()->admin()) : ?><br>
       <fieldset>
	   <label class="admin-publish-toggle" for="item_published">
        <input type="checkbox" name="item_published"> <?= __('app.posted'); ?>
		</label>
      </fieldset>
    <?php endif; ?>

    <?= Html::sumbit(__('app.add')); ?>
  </form>
</main>

<script src="/assets/js/cropper/cropper.min.js"></script>
<link rel="stylesheet" href="/assets/js/cropper/cropper.min.css" type="text/css">
