<?php
/*
 * Content type select (drives the cascading category select).
 * Выбор типа контента: управляет каскадным селектом категорий.
 *
 * Expects: $type (string текущий тип), optional $allowPage (bool)
 */
$allowPage = $allowPage ?? false;
?>
<div class="form-field-group">
  <label for="item_kind"><strong><?= __('app.type'); ?> <span class="red">*</span></strong></label>
  <select name="content_kind" id="item_kind" required>
    <option value="fact" <?= (($type ?? 'fact') === 'fact') ? 'selected' : ''; ?>><?= __('app.fact'); ?></option>
    <option value="article" <?= (($type ?? '') === 'article') ? 'selected' : ''; ?>><?= __('app.article'); ?></option>
    <option value="site" <?= (($type ?? '') === 'site') ? 'selected' : ''; ?>><?= __('app.site'); ?></option>
    <?php if ($allowPage || ($container->user()->tl() ?? 0) >= 10) : ?>
      <option value="page" <?= (($type ?? '') === 'page') ? 'selected' : ''; ?>><?= __('app.page'); ?></option>
    <?php endif; ?>
  </select>
  <div class="hint" id="kind_help"><?= (($type ?? 'fact') === 'article') ? __('app.article_kind_help') : __('app.fact_kind_help'); ?></div>
</div>