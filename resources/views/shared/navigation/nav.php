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
?>

<?php foreach ($list as $key => $item) :
  $tl = $item['tl'] ?? 0;
  $css = empty($item['css']) ? false : $item['css'];
  $isActive = $item['id'] == $sheet ? 'active' : false;
  $class = ($css || $isActive) ? ' class="' . $isActive . ' ' .  $css . '"' : '';
?>
  <a<?= $class; ?> href="<?= $item['url']; ?>"<?= !empty($item['target']) ? ' target="' . $item['target'] . '" rel="noopener"' : ''; ?>>
    <?= __($item['title']); ?>
  </a>
<?php endforeach; ?>