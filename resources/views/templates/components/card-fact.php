<?php
/*
 * Fact card for lists and feeds
 * Карточка факта для лент и списков
 *
 * Expects: $item (array), $preview (bool), optional $showThumb (bool)
 */
$showThumb = $showThumb ?? true;
$mod       = !empty($preview) ? 'preview' : 'static';

$dir  = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
$path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], $mod);
?>
<article class="wiki-fact-item" id="<?= (int)$item['item_id']; ?>">

  <div class="wiki-fact-item__body">
    <h3>
      <a href="<?= $path; ?>"><?= htmlEncode($item['item_title']); ?></a>

      <?php if (!empty($preview)) : ?>
        <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
          <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
        </a>
      <?php endif; ?>
    </h3>

    <p class="wiki-fact-item__excerpt"><?= htmlEncode(Parser::noHTML($item['item_content'], 200)); ?></p>

    <div class="wiki-fact-item__meta">
      <?= Html::facetDir($item['facet_list'], $mod); ?>
      <span class="lowercase"><?= langDate($item['item_date']); ?></span>
    </div>
  </div>

  <?php if ($showThumb && ($img = Parser::miniature($item['item_content']))) : ?>
    <a class="wiki-fact-item__thumb" href="<?= $path; ?>">
      <img alt="<?= htmlEncode($item['item_title']); ?>" src="<?= htmlEncode($img); ?>">
    </a>
  <?php endif; ?>

</article>