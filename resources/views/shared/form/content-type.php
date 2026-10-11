<?php
/*
 * Content type select (drives the cascading category select).
 * Выбор типа контента: управляет каскадным селектом категорий.
 *
 * Expects: $type (string текущий тип), optional $allowPage (bool)
 */
$allowPage = $allowPage ?? false;
?>
<fieldset class="form-big">
  <div class="form-label input-label"><label for="item_kind"><?= __('app.type'); ?> <strong class="red">*</strong></label></div>
  <div class="form-element">
    <select name="content_kind" id="item_kind" required>
      <option value="fact" <?= (($type ?? 'fact') === 'fact') ? 'selected' : ''; ?>><?= __('app.fact'); ?></option>
      <option value="article" <?= (($type ?? '') === 'article') ? 'selected' : ''; ?>><?= __('app.article'); ?></option>
      <?php if ($allowPage || ($container->user()->tl() ?? 0) >= 10) : ?>
        <option value="page" <?= (($type ?? '') === 'page') ? 'selected' : ''; ?>><?= __('app.page'); ?></option>
      <?php endif; ?>
    </select>
    <div class="help" id="kind_help"><?= (($type ?? 'fact') === 'article') ? __('app.article_kind_help') : __('app.fact_kind_help'); ?></div>
  </div>
</fieldset>