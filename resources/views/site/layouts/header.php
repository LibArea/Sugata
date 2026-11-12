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
  <link rel="stylesheet" href="/assets/css/app.css?<?= config('general', 'version'); ?>" type="text/css">
  <script defer src="/assets/js/random-facts.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/minisearch.min.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/search-index.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/wiki-search.js?<?= config('general', 'version'); ?>"></script>
</head>

<body>

<?php if (!empty($preview)) : ?>
  <div class="preview-bar" role="note">
    <?= __('app.admin_preview_bar'); ?>
  </div>
<?php endif; ?>

<header class="site-header">
  <div class="site-header__in">
    <a href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>" class="site-brand" aria-label="Sugata — главная">
      <?= htmlEncode(config('general', 'site_name')); ?>
    </a>

    <form class="header-search" role="search" method="get" action="<?= !empty($preview) ? url('search.go') : '/search/go'; ?>" onsubmit="return false;" autocomplete="off">
      <input type="text" name="q" id="wiki-search-input" aria-label="<?= __('app.find'); ?>" placeholder="<?= __('app.find_placeholder'); ?>" autocomplete="off">
      <button type="submit" aria-label="<?= __('app.find'); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg>
      </button>
      <div id="wiki-search-results" class="search-results" data-empty="<?= trim(strip_tags(__('search.no_results'))); ?>" hidden></div>
    </form>

    <div class="header-actions">
      <a href="<?= !empty($preview) ? rtrim(config('general', 'url'), '/') . '/mod/admin/preview/best' : '/best/'; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 3a2 2 0 0 1 2 2v15a1 1 0 0 1-1.496.868l-4.512-2.578a2 2 0 0 0-1.984 0l-4.512 2.578A1 1 0 0 1 5 20V5a2 2 0 0 1 2-2z"/></svg>
        <?= __('app.nav_featured'); ?>
      </a>
      <button type="button" class="theme-toggle" data-theme-toggle aria-label="<?= __('app.theme_toggle'); ?>" title="<?= __('app.theme_toggle'); ?>"></button>
    </div>
  </div>
</header>

<nav class="site-nav" aria-label="<?= __('app.main_nav'); ?>">
  <div class="site-nav__in">
    <a class="<?= (trim((string)($activeRoot ?? ''), '/') === '' ? 'active' : ''); ?>" href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>"><?= __('app.nav_home'); ?></a>
    <a class="<?= (trim((string)($activeRoot ?? ''), '/') === 'facts' ? 'active' : ''); ?>" href="<?= urlDir('facts', !empty($preview) ? 'preview' : 'static'); ?>"><?= __('app.nav_facts'); ?></a>
    <a class="<?= (trim((string)($activeRoot ?? ''), '/') === 'articles' ? 'active' : ''); ?>" href="<?= urlDir('articles', !empty($preview) ? 'preview' : 'static'); ?>"><?= __('app.nav_articles'); ?></a>
    <a href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>#home-categories"><?= __('app.nav_categories'); ?></a>
    <a class="random-link" href="<?= !empty($preview) ? url('random') : '/random'; ?>" data-random-fact><?= __('app.nav_random'); ?></a>
  </div>
</nav>
