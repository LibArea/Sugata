<?php
/*
 * Sidebar / Left navigation (Wikipedia style)
 * Сайдбар с навигацией по разделам
 *
 * Expects: $preview (bool), optional $activePath (string current facet path)
 */
$activePath = $activePath ?? '';
$sideNav    = config('general', 'categories');
$sideMod    = !empty($preview) ? 'preview' : 'static';
?>

<nav class="wiki-sidebar" aria-label="<?= __('app.category'); ?>">
  <div class="wiki-sidebar__title"><?= __('app.category'); ?></div>
  <ul>
    <?php foreach ($sideNav as $cat) : ?>
      <li class="<?= ($activePath === $cat['path']) ? 'active' : ''; ?>">
        <a href="<?= urlDir($cat['path'], $sideMod); ?>"><?= htmlEncode($cat['title']); ?></a>
        <?php if (!empty($cat['sub'])) : ?>
          <ul>
            <?php foreach ($cat['sub'] as $sub) : ?>
              <li class="<?= ($activePath === $sub['path']) ? 'active' : ''; ?>">
                <a href="<?= urlDir($sub['path'], $sideMod); ?>"><?= htmlEncode($sub['title']); ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>