<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?= $meta; ?>

  <link rel="icon" sizes="16x16" href="/favicon.ico" type="image/x-icon">
  <link rel="icon" sizes="120x120" href="/favicon-120.png" type="image/png">
  <link rel="icon" sizes="144x144" href="/favicon-144.png" type="image/png">
  <link rel="stylesheet" href="/assets/css/style.css?<?= config('general', 'version'); ?>" type="text/css">
  <script defer src="/assets/js/random-facts.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/theme.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/minisearch.min.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/search-index.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/wiki-search.js?<?= config('general', 'version'); ?>"></script>
</head>

<body class="<?= !empty($preview) ? 'light wiki-preview' : 'wiki-site'; ?>">
<?php if (!empty($preview)) : ?>
  <div class="preview-bar" role="note">
    <?= __('app.admin_preview_bar'); ?>
  </div>
<?php endif; ?>
<script>
  (function () {
    try {
      var t = localStorage.getItem('wiki-theme');
      var dark = t === 'dark' || (!t && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
      if (dark) document.body.classList.add('dark');
      document.body.setAttribute('data-theme', dark ? 'dark' : 'light');
    } catch (e) {}
  })();
</script>

<div class="portal-wrap">
<?php $searchAction = !empty($preview) ? url('search.go') : '/search/go'; ?>

<?php if (!empty($hero)) : ?>
  <!-- Главная: строка сервисов справа -->
  <div class="portal-topbar">
    <a href="<?= urlDir('facts', !empty($preview) ? 'preview' : 'static'); ?>"><?= __('app.nav_categories'); ?></a>
    <a href="<?= !empty($preview) ? rtrim(config('general', 'url'), '/') . '/mod/admin/preview/best' : '/best/'; ?>"><?= __('app.nav_featured'); ?></a>
    <a href="<?= !empty($preview) ? url('random') : '/random'; ?>" data-random-fact><?= __('app.nav_random'); ?></a>
    <button type="button" class="theme-toggle" data-theme-toggle aria-label="Тёмная тема" title="Тёмная тема">
      <svg class="icon-moon" viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      <svg class="icon-sun" viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M12 2a1 1 0 0 1 1 1v2a1 1 0 0 1-2 0V3a1 1 0 0 1 1-1zm0 16a1 1 0 0 1 1 1v2a1 1 0 0 1-2 0v-2a1 1 0 0 1 1-1zM4 11a1 1 0 0 1 1-1h2a1 1 0 0 1 0 2H5a1 1 0 0 1-1-1zm12 0a1 1 0 0 1 1-1h2a1 1 0 0 1 0 2h-2a1 1 0 0 1-1-1zM3.5 4.5a1 1 0 0 1 1.41 0l1.41 1.41a1 1 0 1 1-1.41 1.42l-1.42-1.42a1 1 0 0 1 0-1.41zm12.72 12.72a1 1 0 0 1 1.41 0l1.41 1.41a1 1 0 1 1-1.41 1.42l-1.41-1.42a1 1 0 0 1 0-1.41zM21 11a1 1 0 0 1-1 1h-2a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1zM7 11a1 1 0 0 1-1 1H3a1 1 0 0 1 0-2h3a1 1 0 0 1 1 1z"/></svg>
    </button>
  </div>

  <!-- Главная: центр — логотип и поиск -->
  <div class="hero">
    <a class="hero-logo" href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>"><?= htmlEncode(config('general', 'site_name')); ?></a>

    <form class="hero-search" method="get" action="<?= $searchAction; ?>" onsubmit="return false;" autocomplete="off">
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

    <div class="under-search"><?= __('app.search_adv'); ?></div>
  </div>

<?php else : ?>
  <!-- Внутренние страницы: компактная шапка -->
  <div class="portal-topbar">
    <a href="<?= urlDir('facts', !empty($preview) ? 'preview' : 'static'); ?>"><?= __('app.nav_categories'); ?></a>
    <a href="<?= !empty($preview) ? rtrim(config('general', 'url'), '/') . '/mod/admin/preview/best' : '/best/'; ?>"><?= __('app.nav_featured'); ?></a>
    <a href="<?= !empty($preview) ? url('random') : '/random'; ?>" data-random-fact><?= __('app.nav_random'); ?></a>
    <button type="button" class="theme-toggle" data-theme-toggle aria-label="Тёмная тема" title="Тёмная тема">
        <svg class="icon-moon" viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="icon-sun" viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M12 2a1 1 0 0 1 1 1v2a1 1 0 0 1-2 0V3a1 1 0 0 1 1-1zm0 16a1 1 0 0 1 1 1v2a1 1 0 0 1-2 0v-2a1 1 0 0 1 1-1zM4 11a1 1 0 0 1 1-1h2a1 1 0 0 1 0 2H5a1 1 0 0 1-1-1zm12 0a1 1 0 0 1 1-1h2a1 1 0 0 1 0 2h-2a1 1 0 0 1-1-1zM3.5 4.5a1 1 0 0 1 1.41 0l1.41 1.41a1 1 0 1 1-1.41 1.42l-1.42-1.42a1 1 0 0 1 0-1.41zm12.72 12.72a1 1 0 0 1 1.41 0l1.41 1.41a1 1 0 1 1-1.41 1.42l-1.41-1.42a1 1 0 0 1 0-1.41zM21 11a1 1 0 0 1-1 1h-2a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1zM7 11a1 1 0 0 1-1 1H3a1 1 0 0 1 0-2h3a1 1 0 0 1 1 1z"/></svg>
    </button>
  </div>

  <div class="portal-header">
    <a class="portal-logo" href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>">
      <?= htmlEncode(config('general', 'site_name')); ?>
    </a>

    <form class="portal-search" method="get" action="<?= $searchAction; ?>" onsubmit="return false;" autocomplete="off">
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
  </div>
<?php endif; ?>
</div>