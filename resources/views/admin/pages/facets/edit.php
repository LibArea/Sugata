<?php
$fs = $data['facet_inf'];
$url = url('redirect.facet', ['id' => $fs['facet_id']]);
?>

<main>
  <?= insert('/shared/navigation/breadcrumbs', [
    'list' => [
      [
        'name' => __('app.home'),
        'link' => url('homepage')
      ],
      [
        'name' => __('app.structure'),
        'link' => url('structure')
      ],
      [
        'name' => __('app.edit_category'),
        'link' => ''
      ],
    ],
    'sheet' =>  $data['sheet']
  ]);
  ?>
  <h1 class="uppercase-box"><?= __('app.edit_' . $data['sheet']); ?> </h1>

  <form class="max-w-md" action="<?= url('edit.facet', ['type' => $fs['facet_type']], method: 'post'); ?>" method="post" enctype="multipart/form-data">
    <?= $container->csrf()->field(); ?>

    <fieldset>
      <label for="facet_title"><?= __('app.title'); ?><sup class="red">*</sup></label>
      <input minlength="3" maxlength="64" type="text" name="facet_title" value="<?= htmlEncode($fs['facet_title']); ?>">
      <div class="help">3 - 64 <?= __('app.characters'); ?></div>
    </fieldset>

    <fieldset>
      <label for="facet_slug"><?= __('app.slug'); ?><sup class="red">*</sup></label>
      <input minlength="3" maxlength="32" type="text" name="facet_slug" value="<?= $fs['facet_slug']; ?>">
      <div class="help">3 - 32 <?= __('app.characters'); ?> (a-z-0-9)</div>
    </fieldset>

    <?php if (!empty($data['facets_tree']) && is_array($data['facets_tree'])) : ?>
      <fieldset>
        <div class="form-label input-label"><label><?= __('app.parent_category'); ?></label></div>
        <div class="form-element">
          <?php
          $editTree = Html::builder(null, 0, $data['facets_tree']);
          $curParent = (int)($data['facet_parent_id'] ?? 0);
          ?>
          <select name="facet_parent_id" class="w-100">
            <option value="0">— <?= __('app.root'); ?> —</option>
            <?php foreach ($editTree as $node) : ?>
              <?php if ((int)$node['facet_id'] === (int)$fs['facet_id']) continue; ?>
              <?php if (trim($node['facet_path'], '/') === 'info') continue; ?>
              <option value="<?= (int)$node['facet_id']; ?>" <?= ((int)$node['facet_id'] === $curParent) ? 'selected' : ''; ?>>
                <?= str_repeat('— ', $node['level']) . htmlEncode($node['facet_title']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="help"><?= __('app.parent_category_help'); ?></div>
        </div>
      </fieldset>
    <?php endif; ?>

    <?php if ($container->user()->admin()) : ?>

      <?= insert('/shared/form/select/low-facets', [
        'data'          => $data,
        'action'        => 'edit',
        'type'          => $fs['facet_type'],
        'title'         => __('app.children'),
        'help'          => __('app.necessarily'),
        'red'           => 'red'
      ]); ?>
    <?php endif; ?>

    <fieldset>
      <label for="facet_description"><?= __('app.meta_description'); ?><sup class="red">*</sup></label>
      <textarea class="add max-w-md" rows="6" minlength="3" name="facet_description"><?= $fs['facet_description']; ?></textarea>
      <div class="help">> 3 <?= __('app.characters'); ?></div>
    </fieldset>

    <fieldset>
      <?= __('app.information'); ?><sup class="red">*</sup>
      <textarea class="add max-w-md block" rows="6" name="facet_info"><?= $fs['facet_info']; ?></textarea>
      <div class="mb20 help">Markdown, > 14 <?= __('app.characters'); ?></div>

      <?= insert('/shared/form/select/user', ['user' => $data['user']]); ?>
    </fieldset>

    <input type="hidden" name="facet_id" value="<?= $fs['facet_id']; ?>">
    <?= Html::sumbit(__('app.edit')); ?>
  </form>
</main>

<script src="/assets/js/tag/tagify.min.js"></script>
<link rel="stylesheet" href="/assets/js/tag/tagify.css" type="text/css">
<script src="/assets/js/cropper/cropper.min.js"></script>
<link rel="stylesheet" href="/assets/js/cropper/cropper.min.css" type="text/css">