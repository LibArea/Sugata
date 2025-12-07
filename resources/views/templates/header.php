<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?= $meta; ?>

  <link rel="icon" sizes="16x16" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/style.css?<?= config('general', 'version'); ?>" type="text/css">
</head>

<body>

  <header class="content">
    <a href="<?= url('homepage'); ?>">
	<h1 class="logo">Музей фактов</h1>
    </a>
  </header>