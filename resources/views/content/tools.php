<main>
  <div class="nav-bar">
    <ul class="nav scroll-menu">
      <?= insert('/_block/navigation/nav', ['sheet' => $data['sheet']]); ?>
    </ul>
  </div>

  <h1 class="title"><?= __('app.tools'); ?></h1>

  <h2 class="admin-tools-section"><?= __('app.tools_build_site'); ?></h2>

  <div class="admin-tools-row">
    <div class="admin-tools-col">
      <div class="admin-tools-col__title"><?= __('app.tools_light'); ?></div>
      <div class="admin-tools-grid">

        <div class="admin-tools-card">
          <a class="btn btn-primary" href="<?= url('update.html.incremental'); ?>"><?= __('app.run'); ?></a>
          <div class="admin-tools-card__label"><?= __('app.rebuild_view_incremental'); ?></div>
        </div>

        <div class="admin-tools-card">
          <a class="btn btn-primary" href="<?= url('update.transfer'); ?>"><?= __('app.run'); ?></a>
          <div class="admin-tools-card__label"><?= __('app.transfer photo'); ?></div>
        </div>

      </div>
    </div>

    <div class="admin-tools-col">
      <div class="admin-tools-col__title"><?= __('app.tools_heavy'); ?></div>
      <div class="admin-tools-grid">

        <div class="admin-tools-card">
          <a class="btn btn-primary" href="<?= url('update.html.dir'); ?>"><?= __('app.run'); ?></a>
          <div class="admin-tools-card__label"><?= __('app.rebuild_html_dir'); ?></div>
        </div>

        <div class="admin-tools-card">
          <a class="btn btn-primary" href="<?= url('update.html.view'); ?>"><?= __('app.run'); ?></a>
          <div class="admin-tools-card__label"><?= __('app.rebuild_view'); ?></div>
        </div>

      </div>
    </div>
  </div>

  <h2 class="admin-tools-section"><?= __('app.tools_resources'); ?></h2>

  <div class="admin-tools-grid">

    <div class="admin-tools-card">
      <a class="btn btn-primary" href="<?= url('update.css'); ?>"><?= __('app.run'); ?></a>
      <div class="admin-tools-card__label"><?= __('app.rebuild_css_title'); ?></div>
    </div>

    <div class="admin-tools-card">
      <a class="btn btn-primary" href="<?= url('update.path'); ?>"><?= __('app.run'); ?></a>
      <div class="admin-tools-card__label"><?= __('app.rebuild_title'); ?></div>
    </div>

    <div class="admin-tools-card">
      <a class="btn btn-primary" href="<?= url('update.indexing'); ?>"><?= __('app.run'); ?></a>
      <div class="admin-tools-card__label"><?= __('app.search_index'); ?></div>
    </div>

  </div>

  <h2 class="admin-tools-section"><?= __('app.tools_maintain'); ?></h2>

  <div class="admin-tools-grid">

    <div class="admin-tools-card">
      <a class="btn btn-primary" href="<?= url('update.sources'); ?>"><?= __('app.run'); ?></a>
      <div class="admin-tools-card__label"><?= __('app.sources_check'); ?></div>
    </div>

    <div class="admin-tools-card">
      <a class="btn btn-primary" href="<?= url('update.sitemap'); ?>"><?= __('app.run'); ?></a>
      <div class="admin-tools-card__label"><?= __('app.sitemap_build'); ?></div>
    </div>

  </div>

  <h2 class="admin-tools-section admin-tools-section--danger"><?= __('app.tools_danger'); ?></h2>

  <div class="admin-tools-grid">

    <div class="admin-tools-card admin-tools-card--danger">
      <a class="btn btn-primary" href="<?= url('deletion.dir'); ?>"><?= __('app.delete'); ?></a>
      <div class="admin-tools-card__label"><?= __('app.deletion_dir'); ?></div>
    </div>

  </div>

</main>