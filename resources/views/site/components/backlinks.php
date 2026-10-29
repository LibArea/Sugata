<?php
/*
 * Обратные ссылки «Сюда ссылаются» (нативные классы base.css).
 * Ожидает: $backlinks (статьи, ссылающиеся на текущий факт), $preview (bool|null)
 */
if (empty($backlinks)) {
    return;
}
$preview = $preview ?? false;
$linkMod = $preview ? 'preview' : 'static';
?>
<div role="navigation" aria-label="<?= __('app.backlinks'); ?>" class="mb1">
  <div class="title">
    <?= __('app.backlinks'); ?>
    <span class="text-sm gray">(<?= count($backlinks); ?>)</span>
  </div>
  <ol>
    <?php foreach ($backlinks as $bl) :
      $bDir = preg_split('/(@)/', (string)($bl['facet_list'] ?? ''));
      $bPath = trim($bDir[2] ?? '', '/'); ?>
      <li>
        <a href="<?= urlItem($bPath, $bl['item_slug'], $linkMod); ?>"><?= htmlEncode($bl['item_title']); ?></a>
        <?php if (!empty($bl['facet_list'])) : ?>
          <span class="text-sm gray"><?= Html::facetDir($bl['facet_list'] ?? '', $linkMod); ?></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</div>