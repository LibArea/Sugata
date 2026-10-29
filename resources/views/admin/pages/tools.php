<main class="content">
  <nav class="nav">
    <?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
  </nav>

  <h1 class="title"><?= __('app.tools'); ?></h1>

  <h2 class="title"><?= __('app.tools_build_site'); ?></h2>
  <div class="checkbox-group">
    <div class="box">
      <a class="btn" href="<?= url('update.html.incremental'); ?>"><?= __('app.run'); ?></a>
      <div class="hint"><?= __('app.rebuild_view_incremental'); ?></div>
    </div>

    <div class="box">
      <a class="btn" href="<?= url('update.transfer'); ?>"><?= __('app.run'); ?></a>
      <div class="hint"><?= __('app.transfer photo'); ?></div>
    </div>

    <div class="box">
      <a class="btn" href="<?= url('update.html.dir'); ?>"><?= __('app.run'); ?></a>
      <div class="hint"><?= __('app.rebuild_html_dir'); ?></div>
    </div>

    <div class="box">
      <a class="btn" href="<?= url('update.html.view'); ?>"><?= __('app.run'); ?></a>
      <div class="hint"><?= __('app.rebuild_view'); ?></div>
    </div>
  </div>

  <h2 class="title"><?= __('app.tools_resources'); ?></h2>
  <div class="checkbox-group">
    <div class="box">
      <a class="btn" href="<?= url('update.css'); ?>"><?= __('app.run'); ?></a>
      <div class="hint"><?= __('app.rebuild_css_title'); ?></div>
    </div>

    <div class="box">
      <a class="btn" href="<?= url('update.path'); ?>"><?= __('app.run'); ?></a>
      <div class="hint"><?= __('app.rebuild_title'); ?></div>
    </div>

    <div class="box">
      <a class="btn" href="<?= url('update.indexing'); ?>"><?= __('app.run'); ?></a>
      <div class="hint"><?= __('app.search_index'); ?></div>
    </div>
  </div>

  <h2 class="title"><?= __('app.tools_maintain'); ?></h2>
  <div class="checkbox-group">
    <div class="box">
      <a class="btn" href="<?= url('update.sources'); ?>"><?= __('app.run'); ?></a>
      <div class="hint"><?= __('app.sources_check'); ?></div>
    </div>

    <div class="box">
      <a class="btn" href="<?= url('update.sitemap'); ?>"><?= __('app.run'); ?></a>
      <div class="hint"><?= __('app.sitemap_build'); ?></div>
    </div>

    <div class="box">
      <a class="btn btn-danger" href="<?= url('deletion.dir'); ?>"><?= __('app.delete'); ?></a>
      <div class="hint"><?= __('app.deletion_dir'); ?></div>
    </div>
  </div>
</main>