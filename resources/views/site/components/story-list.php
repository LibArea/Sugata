<?php
/*
 * Список записей карточками.
 * Expects: $items (array), $preview (bool)
 */
$preview = $preview ?? false;
$mod = $preview ? 'preview' : 'static';
?>
<div class="grid grid--3">
  <?php foreach ($items as $item) :
    $dir  = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
    $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], $mod);
    ?>
    <article class="card">
      <div class="hint"><?= Html::facetDir($item['facet_list'] ?? '', $mod); ?></div>
      <h3><a href="<?= $path; ?>"><?= htmlEncode($item['item_title']); ?></a></h3>
      <p><?= htmlEncode(Parser::noHTML($item['item_content'], 200)); ?></p>
      <div class="toolbar">
        <a href="<?= $path; ?>"><?= __('app.read_more'); ?> →</a>
        <span class="hint"><?= langDate($item['item_date']); ?></span>
        <?php if (!empty($preview)) : ?>
          <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
            <svg class="icon" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
          </a>
        <?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
</div>
