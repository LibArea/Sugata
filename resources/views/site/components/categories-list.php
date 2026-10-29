<?php
/*
 * Портал: рубрики разделов (Факты/Статьи/Сайты) в несколько колонок.
 * Expects: $sections (array), $preview (bool)
 */
$mod      = !empty($preview) ? 'preview' : 'static';
$sections = $sections ?? [];
?>
<div class="portal-rubrics">
  <?php foreach ($sections as $cat) : ?>
    <div class="portal-rubric">
      <a class="portal-rubric__title" href="<?= urlDir($cat['path'], $mod); ?>"><?= htmlEncode($cat['title']); ?></a>
      <?php if (!empty($cat['sub'])) : ?>
        <div class="portal-rubric__sub">
          <?php foreach ($cat['sub'] as $sub) : ?>
            <a href="<?= urlDir($sub['path'], $mod); ?>"><?= htmlEncode($sub['title']); ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>