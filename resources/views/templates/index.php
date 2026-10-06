<?= insert('/templates/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<div class="content wiki-content">
  <div class="wiki-layout">
    <main class="wiki-layout__main">
      <?= insert('/_block/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

      <div class="flex justify-between items-center wiki-mb4">
        <h1 class="wiki-title wiki-mb0"><?= htmlEncode($facet['facet_title']); ?></h1>
        <?php if (!empty($facet['facet_info'])) : ?>
          <span class="tag-yellow box mb-none"><?= htmlEncode($facet['facet_info']); ?></span>
        <?php endif; ?>
      </div>

      <?php if ($childrens) : ?>
        <div class="item-categories wiki-mb4">
          <?php foreach ($childrens as $lt) : ?>
            <div class="categories-telo">
              <a class="text-xl" href="<?= urlDir($lt['facet_path'], !empty($preview) ? 'preview' : 'static'); ?>">
                <?= htmlEncode($lt['facet_title']); ?>
              </a>
              <sup class="gray-600"><?= (int)$lt['facet_count']; ?></sup>
              <?php if (!empty($preview)) : ?>
                <a class="ml5 gray-600" href="<?= url('facet.form.edit', ['type' => 'category', 'id' => $lt['facet_id']]); ?>">
                  <sup><svg class="icon"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg></sup>
                </a>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="wiki-fact-list">
        <?php foreach ($items as $item) : ?>
          <?= insert('/templates/components/card-fact', ['item' => $item, 'preview' => !empty($preview)]); ?>
        <?php endforeach; ?>
      </div>

      <?php if (($pagesCount ?? 1) > 1) : ?>
        <?php $baseUrl = ($facet['facet_path'] ?? '') . '/'; ?>
        <?php $curPage = $pNum ?? 1; ?>
        <?php $pref = !empty($preview) ? '/mod/admin/preview/' : '/'; ?>
        <nav class="wiki-pagination">
          <?php if ($curPage > 1) : ?>
            <a class="wiki-pagination__link" href="<?= $pref . $baseUrl . (($curPage == 2) ? 'index.html' : 'page-' . ($curPage - 1) . '.html'); ?>">← <?= __('app.page'); ?> <?= $curPage - 1; ?></a>
          <?php endif; ?>

          <span class="wiki-pagination__current"><?= $curPage; ?> / <?= $pagesCount; ?></span>

          <?php if ($curPage < $pagesCount) : ?>
            <a class="wiki-pagination__link" href="<?= $pref . $baseUrl; ?>page-<?= $curPage + 1; ?>.html"><?= __('app.page'); ?> <?= $curPage + 1; ?> →</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    </main>

    <aside class="wiki-layout__sidebar">
      <?= insert('/templates/layout/sidebar', ['sideNav' => $sideNav ?? [], 'preview' => !empty($preview), 'activePath' => $facet['facet_path'] ?? '']); ?>
    </aside>
  </div>
</div>

<?php if (!empty($preview)) : ?>
  <?= insert('/templates/footer', ['preview' => true]); ?>
<?php else : ?>
  <!--#include virtual="/assets/footer.shtml"-->
<?php endif; ?>