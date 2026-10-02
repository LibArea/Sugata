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
</head>

<body class="<?= modeDayNight(); ?><?= !empty($preview) ? ' wiki-preview' : ' wiki-site'; ?>">

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

    <nav class="wiki-header__nav" aria-label="<?= __('app.view'); ?>">
      <a href="<?= !empty($preview) ? url('preview.home') : url('homepage'); ?>"><?= __('app.home'); ?></a>
    </nav>
  </header>