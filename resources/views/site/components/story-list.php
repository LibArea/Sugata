<?php
/*
 * Список записей на нативном классе stories (base.css)
 * Expects: $items (array), $preview (bool)
 */
$preview = $preview ?? false;
$mod = $preview ? 'preview' : 'static';
?>
<ol class="stories">
  <?php foreach ($items as $item) :
    $dir  = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
    $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], $mod);
    ?>
    <li class="story">
      <div class="story_liner">
        <div class="link"><a href="<?= $path; ?>"><?= htmlEncode($item['item_title']); ?></a></div>
        <div class="story_content"><?= htmlEncode(Parser::noHTML($item['item_content'], 200)); ?></div>
        <div class="byline">
          <?= Html::facetDir($item['facet_list'] ?? '', $mod); ?>
          <span class="divider">·</span>
          <span class="lowercase"><?= langDate($item['item_date']); ?></span>
          <?php if (!empty($preview)) : ?>
            <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
              <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
</ol>