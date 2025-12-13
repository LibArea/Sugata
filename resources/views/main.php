<?= insert('/global/header', ['meta' => $meta, 'type' => $type ?? false]); ?>

<body class="<?= modeDayNight(); ?>">

  <header class="content flex items-center justify-between">
    <a href="<?= url('homepage'); ?>">
      <h1 class="logo"><?= config('general', 'site_name'); ?></h1>
    </a>

    <?php if ($container->user()->active()) : ?>
      <div>
        <a class="ml20 text-sm gray-600" href="<?= config('general', 'url_html'); ?>" target="_blank" rel="noopener">Сайт</a>
        <a class="ml20 text-sm" href="<?= url('logout'); ?>">Выйти</a>
      </div>
    <?php endif; ?>
  </header>

  <div class="content">
    <div class="flex w-100 gap">
      <?= $content; ?>
    </div>
  </div>

  <?= insert('/global/footer'); ?>