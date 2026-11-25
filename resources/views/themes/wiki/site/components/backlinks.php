<?php
/*
 * Обратные ссылки «Сюда ссылаются».
 * Ожидает: $backlinks (статьи, ссылающиеся на текущий факт), $preview (bool|null)
 */
if (empty($backlinks)) {
    return;
}
$preview = $preview ?? false;
$linkMod = $preview ? 'preview' : 'static';
?>
<section class="box backlinks" aria-label="<?= __('app.backlinks'); ?>">
  <div class="hint" style="margin-bottom:8px"><?= __('app.backlinks'); ?> (<?= count($backlinks); ?>)</div>
  <ol style="margin:0;padding-left:20px">
    <?php foreach ($backlinks as $bl) :
      $bDir = preg_split('/(@)/', (string)($bl['facet_list'] ?? ''));
      $bPath = trim($bDir[2] ?? '', '/'); ?>
      <li>
        <a href="<?= urlItem($bPath, $bl['item_slug'], $linkMod); ?>"><?= htmlEncode($bl['item_title']); ?></a>
        <?php if (!empty($bl['facet_list'])) : ?>
          <span class="hint"><?= Html::facetDir($bl['facet_list'] ?? '', $linkMod); ?></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</section>
