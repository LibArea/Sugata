<?= insert('/admin/layout/header', ['meta' => $meta, 'type' => $type ?? false]); ?>

<body class="<?= modeDayNight(); ?><?= $container->user()->active() ? ' admin-mode' : ''; ?>">

  <nav class="navbar-container">
    <?php if ($container->user()->active()) : ?>
      <a class="navbar-logo" href="<?= url('homepage'); ?>" aria-hidden="true">W</a>
    <?php else : ?>
      <a class="navbar-logo" href="<?= url('homepage'); ?>"><?= config('general', 'site_name'); ?></a>
    <?php endif; ?>

    <form class="admin-navbar-search" method="get" action="/search/go" onsubmit="return false;" autocomplete="off">
      <div class="form-search-telo">
        <div class="table-cell w-100">
          <input type="text" name="q" id="wiki-search-input" placeholder="<?= __('app.find'); ?>" autocomplete="off" class="search">
        </div>
        <div class="table-cell">
          <button type="submit" class="btn-search"><?= __('app.find'); ?></button>
        </div>
      </div>
      <div id="wiki-search-results" class="wiki-search-results" data-empty="<?= trim(strip_tags(__('search.no_results'))); ?>" hidden></div>
    </form>

    <?php if ($container->user()->active()) : ?>
      <div class="navbar-links">
        <a href="<?= url('preview.site'); ?>" target="_blank" rel="noopener"><?= __('app.view_site'); ?></a>
        <a href="<?= config('general', 'url_html'); ?>" target="_blank" rel="noopener"><?= __('app.static_site'); ?></a>
        <a href="<?= url('logout'); ?>"><?= __('app.logout'); ?></a>
      </div>
    <?php endif; ?>
  </nav>

  <div class="w-100">
    <?= $content; ?>
  </div>

  <?= insert('/admin/layout/footer'); ?>