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
  <script src="/assets/js/random-facts.js?<?= config('general', 'version'); ?>"></script>
  <script src="/assets/js/theme.js?<?= config('general', 'version'); ?>"></script>
</head>

<body class="<?= !empty($preview) ? 'light wiki-preview' : 'wiki-site'; ?>">
<script>
  (function () {
    try {
      var t = localStorage.getItem('wiki-theme');
      if (t === 'dark') document.body.classList.add('dark');
      else if (!t && window.matchMedia('(prefers-color-scheme: dark)').matches) document.body.classList.add('dark');
    } catch (e) {}
  })();
</script>

  <header class="wiki-header content wiki-content">
    <div class="wiki-header__logo">
      <a href="<?= !empty($preview) ? url('preview.home') : url('homepage'); ?>">
        <?= htmlEncode(config('general', 'site_name')); ?>
      </a>
      <small><?= __('app.facts'); ?></small>
    </div>

    <div class="wiki-header__search">
      <form class="m0" method="get" action="<?= !empty($preview) ? url('search.go') : '/search/go'; ?>">
        <input type="text" name="q" placeholder="<?= __('app.find'); ?>" aria-label="<?= __('app.find'); ?>">
      </form>
    </div>

    <button type="button" class="wiki-theme-toggle" data-theme-toggle aria-label="Тёмная тема">🌙</button>

    <a class="wiki-random-btn" href="<?= !empty($preview) ? url('random') : '/random'; ?>" data-random-fact><?= __('app.random_fact'); ?></a>
  </header>