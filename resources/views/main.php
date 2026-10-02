<?= insert('/global/header', ['meta' => $meta, 'type' => $type ?? false]); ?>

<body class="<?= modeDayNight(); ?><?= $container->user()->active() ? ' admin-mode' : ''; ?>">

  <header class="content flex items-center justify-between<?= $container->user()->active() ? ' admin-topbar' : ''; ?>">
    <?php if ($container->user()->active()) : ?>
      <a class="admin-brand" href="<?= url('homepage'); ?>">
        <span class="admin-brand__mark" aria-hidden="true">W</span>
        <span>
          <span class="admin-brand__name"><?= config('general', 'site_name'); ?></span>
          <span class="admin-brand__badge">Workspace</span>
        </span>
      </a>
    <?php else : ?>
      <a href="<?= url('homepage'); ?>">
        <h1 class="logo"><?= config('general', 'site_name'); ?></h1>
      </a>
    <?php endif; ?>

    <?php if ($container->user()->active()) : ?>
      <form class="w-50 mb-none admin-search" method="get" action="<?= url('search.go'); ?>">
        <input data-id="topic" type="text" name="q" autocomplete="off" id="find" placeholder="<?= __('app.find'); ?>" aria-label="<?= __('app.find'); ?>" class="search w-100">
      </form>
      <div class="admin-actions">
        <a href="<?= config('general', 'url_html'); ?>" target="_blank" rel="noopener"><?= __('app.website'); ?></a>
        <a href="<?= url('logout'); ?>"><?= __('app.logout'); ?></a>
      </div>
    <?php endif; ?>
  </header>

  <div class="<?= $container->user()->active() ? 'admin-page' : 'content'; ?>">
    <div class="flex w-100 gap-lg<?= $container->user()->active() ? ' admin-page__layout' : ''; ?>">
      <?= $content; ?>
    </div>
  </div>

  <?= insert('/global/footer'); ?>