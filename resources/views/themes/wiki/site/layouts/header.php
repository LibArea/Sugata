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
  <?= theme_assets('css'); ?>

  <script defer src="/assets/js/random-facts.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/minisearch.min.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/search-index.js?<?= config('general', 'version'); ?>"></script>
  <script defer src="/assets/js/wiki-search.js?<?= config('general', 'version'); ?>"></script>
  <?= theme_assets('js'); ?>
</head>

<body class="<?= \App\Content\Theme::current(); ?>">

<?php if (!empty($preview)) : ?>
  <a class="preview-bar" href="<?= url('facts', ['type' => 'my']); ?>" role="note">
    <?= __('app.admin_preview_bar'); ?>
  </a>
<?php endif; ?>

<?= insert('/site/layouts/partials/header-body', [
    'preview' => !empty($preview),
    'activeRoot' => $activeRoot ?? '',
]); ?>
