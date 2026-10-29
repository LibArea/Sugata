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

  <ol class="stories">
    <?php foreach ($items as $item) :
      $dir  = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
      $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], !empty($preview) ? 'preview' : 'static');
      $srcUrl = trim((string)($item['item_source_url'] ?? ''));
      ?>
      <li class="story">
        <div class="story_liner">
          <div class="link"><a href="<?= $path; ?>"><?= htmlEncode($item['item_title']); ?></a>
            <?php if ($srcUrl !== '') : ?>
              <a class="domain" href="<?= htmlEncode($srcUrl); ?>" target="_blank" rel="nofollow noopener noreferrer">
                <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#external"></use></svg>
              </a>
            <?php endif; ?>
          </div>
          <div class="story_content"><?= htmlEncode(Parser::noHTML($item['item_content'], 320)); ?></div>
          <div class="byline">
            <?= Html::facetDir($item['facet_list'] ?? '', !empty($preview) ? 'preview' : 'static'); ?>
            <span class="divider">·</span>
            <span class="lowercase"><?= langDate($item['item_date']); ?></span>
            <?php if (!empty($preview)) : ?>
              <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
                <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>

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