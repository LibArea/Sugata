<nav class="nav mt1">
  <?= insert(
    '/shared/navigation/nav',
    [
      'list' => [
        ['url' => url('setting'), 'title' => 'app.setting', 'id' => 'settings'],
        ['url' => url('setting.security'), 'title' => 'app.password', 'id' => 'security'],
      ],
      'sheet' => $sheet
    ],
  );
  ?>
</nav>