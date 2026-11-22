<?php
$list = [
  [
    'id'    => 'tools',
    'url'   => url('tools'),
    'title' => 'app.tools',
  ],
  [
    'id'    => 'structure',
    'url'   => url('structure'),
    'title' => 'app.structure',
  ],
  [
    'id'    => 'templates',
    'url'   => url('templates'),
    'title' => 'app.templates',
  ],
  [
    'id'    => 'media',
    'url'   => url('media'),
    'title' => 'app.media',
  ],
  [
    'id'    => 'sources',
    'url'   => url('sources'),
    'title' => 'app.sources',
    'tl'    => 10,
  ],
  [
    'id'    => 'broken_links',
    'url'   => url('broken.links'),
    'title' => 'app.broken_links',
    'tl'    => 10,
  ],
  [
    'id'    => 'setting',
    'url'   => url('setting'),
    'title' => 'app.setting',
  ],
  [
    'id'    => 'add_fact',
    'url'   => '/mod/admin/add/item',
    'title' => 'app.add_fact',
  ],
];

// Системный пункт «Плагины» — в самом низу, над плагинными пунктами.
$pluginsMenu = [
  [
    'id'    => 'plugins',
    'url'   => url('plugins'),
    'title' => 'app.plugins',
  ],
];

// Плагины со своими админ-экранами добавляют пункты меню автоматически.
foreach (\App\Support\Hooks::adminPages() as $plugin) {
  $pluginsMenu[] = [
    'id'         => 'plugin_' . $plugin['id'],
    'url'        => $plugin['url'],
    'title'      => $plugin['title'],
    'css'        => 'icon-' . $plugin['icon'],
    'raw_title'  => true,
  ];
}

$showDivider = count($pluginsMenu) > 1;
?>

<?php foreach ($list as $key => $item) :
  $tl = $item['tl'] ?? 0;
  $css = empty($item['css']) ? false : $item['css'];
  $isActive = $item['id'] == $sheet ? 'active' : false;
  $class = ($css || $isActive) ? ' class="' . $isActive . ' ' .  $css . '"' : '';
?>
  <a<?= $class; ?> href="<?= $item['url']; ?>"<?= !empty($item['target']) ? ' target="' . $item['target'] . '" rel="noopener"' : ''; ?>>
    <?= !empty($item['raw_title']) ? htmlEncode($item['title']) : __($item['title']); ?>
  </a>
<?php endforeach; ?>

<?php if ($showDivider) : ?>
  <div class="nav-divider"></div>
<?php endif; ?>

<?php foreach ($pluginsMenu as $key => $item) :
  $tl = $item['tl'] ?? 0;
  $css = empty($item['css']) ? false : $item['css'];
  $isActive = $item['id'] == $sheet ? 'active' : false;
  $class = ($css || $isActive) ? ' class="' . $isActive . ' ' .  $css . '"' : '';
?>
  <a<?= $class; ?> href="<?= $item['url']; ?>"<?= !empty($item['target']) ? ' target="' . $item['target'] . '" rel="noopener"' : ''; ?>>
    <?= !empty($item['raw_title']) ? htmlEncode($item['title']) : __($item['title']); ?>
  </a>
<?php endforeach; ?>