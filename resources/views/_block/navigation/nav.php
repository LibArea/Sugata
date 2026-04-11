<?php
$list = [
  [
    'id'    => 'view',
    'url'   => url('homepage'),
    'title' => 'app.view',
  ],
  [
    // 'tl'    => 1,
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
  $class = ($css || $isActive) ? ' class="' . $isActive . ' ' .  $css . '"'   : '';

?>

  <li<?= $class; ?>>
    <a href="<?= $item['url']; ?>">
      <?php if (!empty($item['icon'])) : ?><i class="text-sm <?= $item['icon']; ?>"></i><?php endif; ?>
      <?= __($item['title']); ?>
    </a>
    </li>

  <?php endforeach; ?>