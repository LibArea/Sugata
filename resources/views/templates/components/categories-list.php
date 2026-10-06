<?php
/*
 * Compact category navigation (Wikipedia portal style)
 * Компактная навигация по разделам на главной
 *
 * Expects: $sections (array — категории из базы, подготовлены контроллером),
 *          $preview (bool)
 */
$mod      = !empty($preview) ? 'preview' : 'static';
$sections = $sections ?? [];
?>
<div class="wiki-sections__grid">
  <?php foreach ($sections as $cat) : ?>
    <div class="wiki-sections__item">
      <h3><a href="<?= urlDir($cat['path'], $mod); ?>"><?= htmlEncode($cat['title']); ?></a></h3>

      <?php if (!empty($cat['help'])) : ?>
        <p class="wiki-sections__help"><?= htmlEncode($cat['help']); ?>...</p>
      <?php endif; ?>

      <?php if (!empty($cat['sub'])) : ?>
        <ul class="wiki-sections__sub">
          <?php foreach ($cat['sub'] as $sub) : ?>
            <li><a href="<?= urlDir($sub['path'], $mod); ?>"><?= htmlEncode($sub['title']); ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>