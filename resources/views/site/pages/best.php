<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<div class="content wiki-content">
  <div class="wiki-layout">
    <main class="wiki-layout__main">
      <h1 class="wiki-title"><?= __('app.best_facts'); ?></h1>

      <?php if ($items) : ?>
        <div class="wiki-fact-list">
          <?php foreach ($items as $item) : ?>
            <?= insert('/site/components/card-fact', ['item' => $item, 'preview' => !empty($preview), 'showThumb' => true]); ?>
          <?php endforeach; ?>
        </div>

        <?php
        $pref = !empty($preview) ? '/mod/admin/preview/' : '/';
        $curPage = $pNum ?? 1;
        ?>
        <?php if (($pagesCount ?? 1) > 1) : ?>
          <nav class="wiki-pagination">
            <?php if ($curPage > 1) : ?>
              <a class="wiki-pagination__link" href="<?= $pref; ?>best<?= $curPage == 2 ? '' : '/page-' . ($curPage - 1); ?>">в†ђ <?= __('app.page'); ?> <?= $curPage - 1; ?></a>
            <?php endif; ?>
            <span class="wiki-pagination__current"><?= $curPage; ?> / <?= $pagesCount; ?></span>
            <?php if ($curPage < $pagesCount) : ?>
              <a class="wiki-pagination__link" href="<?= $pref; ?>best/page-<?= $curPage + 1; ?>"><?= __('app.page'); ?> <?= $curPage + 1; ?> в†’</a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
      <?php else : ?>
        <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
      <?php endif; ?>
    </main>

    <aside class="wiki-layout__sidebar">
      <?= insert('/site/layouts/sidebar', ['sideNav' => $sideNav ?? [], 'preview' => !empty($preview)]); ?>
    </aside>
  </div>
</div>

<?php if (!empty($preview)) : ?>
  <?= insert('/site/layouts/footer', ['preview' => true]); ?>
<?php else : ?>
  <!--#include virtual="/assets/footer.shtml"-->
<?php endif; ?>