<?php
/*
 * Sidebar / Left navigation (Wikipedia style)
 * Сайдбар с навигацией по разделам
 *
 * Expects: $sideNav (array — дерево категорий из БД, подготовлено контроллером),
 *          $preview (bool), optional $activePath (string current facet path)
 */
$activePath = $activePath ?? '';
$sideMod    = !empty($preview) ? 'preview' : 'static';
$sideNav    = $sideNav ?? [];
?>

<nav class="wiki-sidebar" aria-label="<?= __('app.category'); ?>">
  <div class="wiki-sidebar__title"><?= __('app.category'); ?></div>
  <ul>
    <?php foreach ($sideNav as $i => $cat) :
      if ($cat['level'] != 0) continue;
      $active = ($activePath === $cat['facet_path']);

      // Прямые дети уровня 1 для этого корня
      $children = [];
      for ($j = $i + 1; $j < count($sideNav) && $sideNav[$j]['level'] > 0; $j++) {
          if ($sideNav[$j]['level'] == 1 && $sideNav[$j]['facet_parent_id'] == $cat['facet_id']) {
              $children[] = $sideNav[$j];
          }
      }
      ?>
      <li class="<?= $active ? 'active' : ''; ?>">
        <a href="<?= urlDir($cat['facet_path'], $sideMod); ?>"><?= htmlEncode($cat['facet_title']); ?></a>
        <?php if ($children) : ?>
          <ul>
            <?php foreach ($children as $child) : ?>
              <li class="<?= ($activePath === $child['facet_path']) ? 'active' : ''; ?>">
                <a href="<?= urlDir($child['facet_path'], $sideMod); ?>"><?= htmlEncode($child['facet_title']); ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>