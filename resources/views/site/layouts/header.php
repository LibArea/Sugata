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
  <link rel="stylesheet" href="/assets/css/site.css?<?= config('general', 'version'); ?>" type="text/css">
  <script defer src="/assets/js/random-facts.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/theme.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/minisearch.min.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/search-index.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/wiki-search.js?<?= config('general', 'version'); ?>"></script>
</head>

<body class="antialiased <?= !empty($preview) ? 'light wiki-preview' : 'wiki-site'; ?>">
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

<div class="site">
<?php if (!empty($preview)) : ?>
  <div class="preview-bar" role="note">
    <?= __('app.admin_preview_bar'); ?>
  </div>
<?php endif; ?>
<a class="skip-link" href="#home-main"><?= __('app.to_content', ['default' => 'Перейти к содержимому']); ?></a>

<header class="header wrap">
  <a href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>" class="brand" aria-label="Sugata — главная">
    <?= htmlEncode(config('general', 'site_name')); ?><span class="brand-dot">.</span>
    <span class="brand-caption"><?= __('app.brand_caption'); ?></span>
  </a>

  <div class="search-area">
    <form role="search" class="search-form" method="get" action="<?= !empty($preview) ? url('search.go') : '/search/go'; ?>" onsubmit="return false;" autocomplete="off">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search" aria-hidden="true"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg>
      <input type="text" name="q" id="wiki-search-input" aria-label="<?= __('app.find'); ?>" placeholder="<?= __('app.find_placeholder'); ?>" value="">
      <button type="submit" aria-label="<?= __('app.find'); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
      </button>
    </form>
    <div id="wiki-search-results" class="search-results" data-empty="<?= trim(strip_tags(__('search.no_results'))); ?>" hidden></div>
  </div>

  <div class="header-actions">
    <a href="<?= !empty($preview) ? rtrim(config('general', 'url'), '/') . '/mod/admin/preview/best' : '/best/'; ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bookmark" aria-hidden="true"><path d="M17 3a2 2 0 0 1 2 2v15a1 1 0 0 1-1.496.868l-4.512-2.578a2 2 0 0 0-1.984 0l-4.512 2.578A1 1 0 0 1 5 20V5a2 2 0 0 1 2-2z"/></svg>
      <span><?= __('app.nav_featured'); ?></span>
    </a>
    <button class="theme-button" data-theme-toggle type="button" aria-label="<?= __('app.theme_toggle'); ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-moon" aria-hidden="true"><path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"/></svg>
    </button>
  </div>
</header>

<nav class="nav-border" aria-label="<?= __('app.main_nav'); ?>">
  <div class="wrap nav-inner">
    <div class="nav-links">
      <a class="<?= (trim((string)($activeRoot ?? ''), '/') === '' ? 'active' : ''); ?>" href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>"><?= __('app.nav_home'); ?></a>
      <a class="<?= (trim((string)($activeRoot ?? ''), '/') === 'facts' ? 'active' : ''); ?>" href="<?= urlDir('facts', !empty($preview) ? 'preview' : 'static'); ?>"><?= __('app.nav_facts'); ?></a>
      <a class="<?= (trim((string)($activeRoot ?? ''), '/') === 'articles' ? 'active' : ''); ?>" href="<?= urlDir('articles', !empty($preview) ? 'preview' : 'static'); ?>"><?= __('app.nav_articles'); ?></a>
      <a href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>#home-categories"><?= __('app.nav_categories'); ?>
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
      </a>
    </div>
    <a class="random-button" href="<?= !empty($preview) ? url('random') : '/random'; ?>" data-random-fact>
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shuffle" aria-hidden="true"><path d="m18 14 4 4-4 4"/><path d="m18 2 4 4-4 4"/><path d="M2 18h1.973a4 4 0 0 0 3.3-1.7l5.454-8.6a4 4 0 0 1 3.3-1.7H22"/><path d="M2 6h1.972a4 4 0 0 1 3.6 2.2"/><path d="M22 18h-6.041a4 4 0 0 1-3.3-1.8l-.359-.45"/></svg> <?= __('app.nav_random'); ?>
    </a>
  </div>
</nav>