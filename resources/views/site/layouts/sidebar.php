<?php
/*
 * Сайдбар с навигацией по разделам (нативный класс nav base.css)
 * Expects: $sideNav (array), $preview (bool), optional $activePath (string), optional $sideRoot (string)
 */
$activePath = $activePath ?? '';
$sideMod    = !empty($preview) ? 'preview' : 'static';
$sideNav    = $sideNav ?? [];

$sideRoot = $sideRoot ?? '';
if ($sideRoot !== '') {
    $keep = false;
    $filtered = [];
    foreach ($sideNav as $node) {
        if ($node['level'] == 0) {
            $keep = (trim($node['facet_path'] ?? '', '/') === $sideRoot);
            if ($keep) $filtered[] = $node;
            continue;
        }
        if ($keep) $filtered[] = $node;
    }
    $sideNav = $filtered;
}
?>
<nav class="nav" aria-label="<?= __('app.category'); ?>">
  <div class="title"><?= __('app.category'); ?></div>
  <?php foreach ($sideNav as $i => $cat) :
    if ($cat['level'] != 0) continue;
    $active = ($activePath === $cat['facet_path']);
    ?>
    <a class="<?= $active ? 'active' : ''; ?>" href="<?= urlDir($cat['facet_path'], $sideMod); ?>"><?= htmlEncode($cat['facet_title']); ?></a>
  <?php endforeach; ?>
</nav>