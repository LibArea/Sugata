<fieldset>
  <div class="form-label input-label"><label><?= __('app.type'); ?></label></div>
  <div class="form-element">
  <select name="item_type">
    <?php foreach (config('general', 'allowed_types') as $value) : 
		$tl = $value['tl'] ?? 1; ?>
      <?php if ($container->user()->tl() >= $tl) : ?>
        <option <?php if (!empty($type)) : ?><?php if ($value['type'] == $type) : ?>selected<?php endif; ?><?php endif; ?> value="<?= $value['type']; ?>">
          <?= __($value['title']); ?>
        </option>
      <?php endif; ?>
    <?php endforeach; ?>
  </select>
  </div>
</fieldset>