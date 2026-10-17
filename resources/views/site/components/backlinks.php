<?php
/*
 * Обратные ссылки «Сюда ссылаются».
 * Ожидает: $backlinks (статьи, ссылающиеся на текущий факт), $preview (bool|null)
 * Оформлено в стиле существующего блока оглавления (wiki-toc).
 */
if (empty($backlinks)) {
    return;
}
$preview = $preview ?? false;
$linkMod = $preview ? 'preview' : 'static';
?>
<div class="wiki-toc wiki-backlinks" role="navigation" aria-label="<?= __('app.backlinks'); ?>">
  <div class="wiki-toc__title">
    <?= __('app.backlinks'); ?>
    <span class="text-sm gray-600">(<?= count($backlinks); ?>)</span>
  </div>
  <ol>
    <?php foreach ($backlinks as $bl) :
      $bDir = preg_split('/(@)/', (string)($bl['facet_list'] ?? ''));
      $bPath = trim($bDir[2] ?? '', '/'); ?>
      <li>
        <a class="wiki-toc__link" href="<?= urlItem($bPath, $bl['item_slug'], $linkMod); ?>">
          <?= htmlEncode($bl['item_title']); ?>
        </a>
        <?php if (!empty($bl['facet_list'])) : ?>
          <span class="text-sm gray-600"><?= Html::facetDir($bl['facet_list'] ?? '', $linkMod); ?></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</div>