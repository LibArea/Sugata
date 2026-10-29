<?php
$fs = $data['facet_inf'];
$url = url('redirect.facet', ['id' => $fs['facet_id']]);
?>

<main class="content">
  <nav class="nav">
    <?php foreach ([
      ['url' => url('facet.form.edit', ['type' => $data['type'], 'id' => $fs['facet_id']]), 'title' => __('app.edit_' . $data['type'])],
      ['url' => url('facet.form.logo.edit', ['type' => $data['type'], 'id' => $fs['facet_id']]), 'title' => __('app.logo')],
      ['url' => url('team.form.edit', ['type' => $data['type'], 'id' => $fs['facet_id']]), 'title' => __('app.team')],
    ] as $navItem) : ?>
      <a href="<?= $navItem['url']; ?>"><?= $navItem['title']; ?></a>
    <?php endforeach; ?>
    <a class="gray" href="<?= $url; ?>"><?= __('app.go_to'); ?></a>
  </nav>

  <?= insert('/shared/form/cropper/facet-logo', ['data' => $fs]); ?>

  <?php if ($fs['facet_type'] == 'blog') : ?>
    <?= insert('/shared/form/cropper/facet-cover', ['data' => $fs]); ?>
  <?php endif; ?>
</main>
<aside class="content">
  <div class="box">
    <h4 class="title"><?= __('app.help'); ?></h4>
    <?= __('help.edit_' . $data['type']); ?>
  </div>
</aside>

<script src="/assets/js/cropper/cropper.min.js"></script>
<link rel="stylesheet" href="/assets/js/cropper/cropper.min.css" type="text/css">