<?php
/*
 * Карточка статьи в списке категории (журнальный вид).
 * Expects: $item (array), $preview (bool)
 */
$mod = !empty($preview) ? 'preview' : 'static';
$dir  = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
$path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], $mod);
?>
<article class="wiki-article-item">
  <h3 class="wiki-article-item__title">
    <a href="<?= $path; ?>"><?= htmlEncode($item['item_title']); ?></a>
    <?php if (!empty($preview)) : ?>
      <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
        <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
      </a>
    <?php endif; ?>
  </h3>
  <p class="wiki-article-item__excerpt"><?= htmlEncode(Parser::noHTML($item['item_content'], 320)); ?></p>
  <div class="wiki-article-item__meta">
    <?= Html::facetDir($item['facet_list'] ?? '', $mod); ?>
    <span class="lowercase"><?= langDate($item['item_date']); ?></span>
  </div>
</article>