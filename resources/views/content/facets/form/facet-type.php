<fieldset>
  <label for="post_content"><?= __('app.type'); ?></label>
  <select name="facet_type">
    <?php foreach (App\Controllers\FormController::types() as $value) : ?>
      <option <?php if (!empty($type)) : ?><?php if ($value['type_code'] == $type) : ?>selected<?php endif; ?><?php endif; ?> value="<?= $value['type_code']; ?>">
        <?= __('app.' . $value['type_lang']); ?>
      </option>
    <?php endforeach; ?>
  </select>
</fieldset>