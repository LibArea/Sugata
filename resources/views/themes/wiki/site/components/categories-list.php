<?php
/*
 * Портал: рубрики разделов (Факты/Статьи/Сайты).
 * Expects: $sections (array), $preview (bool)
 */
$mod      = !empty($preview) ? 'preview' : 'static';
$sections = $sections ?? [];
?>
<div class="grid grid--3">
  <?php foreach ($sections as $cat) : ?>
    <div class="card">
      <b><a href="<?= urlDir($cat['path'], $mod); ?>"><?= htmlEncode($cat['title']); ?></a></b>
      <?php if (!empty($cat['sub'])) : ?>
        <div class="hint" style="margin-top:8px;display:flex;flex-direction:column;gap:4px">
          <?php foreach ($cat['sub'] as $sub) : ?>
            <a href="<?= urlDir($sub['path'], $mod); ?>"><?= htmlEncode($sub['title']); ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
