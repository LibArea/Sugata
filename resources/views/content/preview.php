<main>
  <div class="nav-bar">
    <ul class="nav scroll-menu">
      <?= insert('/_block/navigation/nav', ['sheet' => $data['sheet']]); ?>
    </ul>
  </div>

  <div class="flex justify-between items-center admin-page-heading">
    <h1 class="title"><?= __('app.view'); ?></h1>
    <a class="btn btn-primary" href="<?= url('preview.site'); ?>" target="_blank" rel="noopener"><?= __('app.open_in_new_tab'); ?></a>
  </div>

  <div class="preview-frame-wrap">
    <iframe class="preview-frame" src="<?= url('preview.site'); ?>" title="<?= __('app.view'); ?>"></iframe>
  </div>
</main>