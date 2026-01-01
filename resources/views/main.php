<?= insert('/global/header', ['meta' => $meta, 'type' => $type ?? false]); ?>

<body class="<?= modeDayNight(); ?>">

  <header class="content flex items-center justify-between">
    <a href="<?= url('homepage'); ?>">
      <h1 class="logo"><?= config('general', 'site_name'); ?></h1>
    </a>

    <?php if ($container->user()->active()) : ?>
      <form class="w-50 mb-none" method="get" action="<?= url('search.go'); ?>">
        <input data-id="topic" type="text" name="q" autocomplete="off" id="find" placeholder="<?= __('app.find'); ?>" class="search w-100">
      </form>
      <div>
        <a class="ml20 text-sm gray-600" href="<?= config('general', 'url_html'); ?>" target="_blank" rel="noopener"><?= __('app.website'); ?></a>
        <a class="ml20 text-sm" href="<?= url('logout'); ?>"><?= __('app.logout'); ?></a>
      </div>
    <?php endif; ?>
  </header>

  <div class="content">
    <div class="flex w-100 gap-lg">
      <?= $content; ?>
    </div>
  </div>

  <?= insert('/global/footer'); ?>