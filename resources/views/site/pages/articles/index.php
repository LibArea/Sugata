<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<main class="content">
  <?= insert('/shared/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

  <div class="flex justify-between items-center">
    <h1 class="title"><?= htmlEncode($facet['facet_title']); ?></h1>
    <?php if (!empty($facet['facet_info'])) : ?>
      <span class="tag"><?= htmlEncode($facet['facet_info']); ?></span>
    <?php endif; ?>
  </div>

  <?php if ($childrens) : ?>
    <div class="item-categories">
      <?php foreach ($childrens as $lt) : ?>
        <div class="categories-telo">
          <a href="<?= urlDir($lt['facet_path'], !empty($preview) ? 'preview' : 'static'); ?>">
            <?= htmlEncode($lt['facet_title']); ?>
          </a>
          <sup class="gray"><?= (int)$lt['facet_count']; ?></sup>
          <?php if (!empty($preview)) : ?>
            <a class="ml5 gray" href="<?= url('facet.form.edit', ['type' => 'category', 'id' => $lt['facet_id']]); ?>">
              <sup><svg class="icon"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg></sup>
            </a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?= insert('/site/components/story-list', ['items' => $items, 'preview' => !empty($preview)]); ?>

  <?php if (($pagesCount ?? 1) > 1) : ?>
    <?php $baseUrl = ($facet['facet_path'] ?? '') . '/'; ?>
    <?php $curPage = $pNum ?? 1; ?>
    <?php $pref = !empty($preview) ? '/mod/admin/preview/' : '/'; ?>
    <nav class="pagination">
      <?php if ($curPage > 1) : ?>
        <a href="<?= $pref . $baseUrl . (($curPage == 2) ? 'index.html' : 'page-' . ($curPage - 1) . '.html'); ?>">← <?= __('app.page'); ?> <?= $curPage - 1; ?></a>
      <?php endif; ?>
      <span class="current"><?= $curPage; ?> / <?= $pagesCount; ?></span>
      <?php if ($curPage < $pagesCount) : ?>
        <a href="<?= $pref . $baseUrl; ?>page-<?= $curPage + 1; ?>.html"><?= __('app.page'); ?> <?= $curPage + 1; ?> →</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</main>

<?php if (!empty($preview)) : ?>
  <?= insert('/site/layouts/footer', ['preview' => true]); ?>
<?php else : ?>
  <!--#include virtual="/assets/footer.shtml"-->
<?php endif; ?>