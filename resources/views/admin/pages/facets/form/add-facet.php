<div class="form-field-group">
  <label for="facet_title"><strong><?= __('app.title'); ?> <span class="red">*</span></strong></label>
  <input id="facet_title" minlength="3" name="facet_title" required type="text" maxlength="64" value="">
  <div class="hint">3 - 64 <?= __('app.characters'); ?></div>
</div>

<div class="form-field-group">
  <label for="facet_slug"><strong><?= __('app.slug'); ?> <span class="red">*</span></strong></label>
  <input id="facet_slug" name="facet_slug" minlength="3" maxlength="32" required type="text" value="">
  <div class="hint">3 - 32 <?= __('app.characters'); ?></div>
</div>

<?php if (!empty($data['facets']) && is_array($data['facets'])) : ?>
  <div class="form-field-group">
    <label for="parent_category"><strong><?= __('app.parent_category'); ?></strong></label>
    <?php
    $tree = Html::builder(null, 0, $data['facets']);
    ?>
    <select name="facet_parent_id" id="parent_category">
      <option value="0">— <?= __('app.root'); ?> —</option>
      <?php foreach ($tree as $node) : ?>
        <?php if (trim($node['facet_path'], '/') === 'info') continue; ?>
        <option value="<?= (int)$node['facet_id']; ?>" style="padding-left: <?= $node['level']; ?>rem;">
          <?= str_repeat('— ', $node['level']) . htmlEncode($node['facet_title']); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <div class="hint"><?= __('app.parent_category_help'); ?></div>
  </div>
<?php endif; ?>

<?= Html::sumbit(__('app.add')); ?>