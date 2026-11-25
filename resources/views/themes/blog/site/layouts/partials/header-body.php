<!-- Блог-шапка: логотип + поиск + переключатель темы -->
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
      <a href="<?= !empty($preview) ? url('best') : '/best/'; ?>"><?= __('app.nav_featured'); ?></a>
      <button type="button" class="theme-toggle" data-theme-toggle aria-label="<?= __('app.theme_toggle'); ?>" title="<?= __('app.theme_toggle'); ?>"></button>
    </div>
  </div>
</header>

<!-- Меню разделов: чипы -->
<nav class="site-nav" aria-label="<?= __('app.main_nav'); ?>">
  <div class="site-nav__in">
    <a class="<?= (trim((string)($activeRoot ?? ''), '/') === '' ? 'active' : ''); ?>" href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>"><?= __('app.nav_home'); ?></a>
    <a class="<?= (trim((string)($activeRoot ?? ''), '/') === 'facts' ? 'active' : ''); ?>" href="<?= urlDir('facts', !empty($preview) ? 'preview' : 'static'); ?>"><?= __('app.nav_facts'); ?></a>
    <a class="<?= (trim((string)($activeRoot ?? ''), '/') === 'articles' ? 'active' : ''); ?>" href="<?= urlDir('articles', !empty($preview) ? 'preview' : 'static'); ?>"><?= __('app.nav_articles'); ?></a>
    <a class="<?= (trim((string)($activeRoot ?? ''), '/') === 'web' ? 'active' : ''); ?>" href="<?= urlDir('web', !empty($preview) ? 'preview' : 'static'); ?>"><?= __('app.nav_sites'); ?></a>
    <a class="random-link" href="<?= !empty($preview) ? url('random') : '/random'; ?>" data-random-fact><?= __('app.nav_random'); ?></a>
  </div>
</nav>