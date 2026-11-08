<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview), 'activeRoot' => 'articles']); ?>

<?php
$mod = !empty($preview) ? 'preview' : 'static';
?>

<main class="wrap" id="article-main">
  <?= insert('/shared/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

  <div class="category-heading">
    <h1><?= htmlEncode($facet['facet_title']); ?></h1>
    <?php if (!empty($facet['facet_info'])) : ?>
      <span class="eyebrow"><?= htmlEncode($facet['facet_info']); ?></span>
    <?php endif; ?>
  </div>

  <?php if ($childrens) : ?>
    <div class="category-links" style="margin-bottom:20px;">
      <?php foreach ($childrens as $lt) : ?>
        <a href="<?= urlDir($lt['facet_path'], $mod); ?>" style="padding:12px 0;font-size:18px;">
          <span><?= htmlEncode($lt['facet_title']); ?><small><?= (int)$lt['facet_count']; ?> <?= __('app.items'); ?></small></span>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($items) : ?>
    <div class="new-grid">
      <?php $ni = 0; ?>
      <?php foreach ($items as $item) :
        $dir  = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
        $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], $mod);
        $ni++;
        ?>
        <article class="new-card">
          <div class="article-meta"><?= Html::facetDir($item['facet_list'] ?? '', $mod); ?></div>
          <h3><a href="<?= $path; ?>"><?= htmlEncode($item['item_title'] ?? ''); ?></a></h3>
          <p><?= htmlEncode(Parser::noHTML((string)($item['item_content'] ?? ''), 130)); ?></p>
          <a class="card-arrow" href="<?= $path; ?>" aria-label="<?= __('app.read_btn'); ?>: <?= htmlEncode($item['item_title'] ?? ''); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
          </a>
          <span class="card-number" aria-hidden="true"><?= sprintf('%02d', $ni); ?></span>
        </article>
      <?php endforeach; ?>
    </div>

    <?php if (($pagesCount ?? 1) > 1) : ?>
      <?= Html::pagination(
          $pNum ?? 1,
          (int)($pagesCount ?? 1),
          null,
          '',
          '?',
          '',
          [
              'url'  => !empty($preview) ? '/mod/admin/preview' : '/',
              'base' => trim((string)($facet['facet_path'] ?? ''), '/'),
              'ext'  => '.html',
          ]
      ); ?>
    <?php endif; ?>
  <?php else : ?>
    <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
  <?php endif; ?>
</main>

<?= insert('/site/layouts/footer', ['preview' => !empty($preview)]); ?>