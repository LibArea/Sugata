<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview), 'activeRoot' => 'facts']); ?>

<?php
$mod = !empty($preview) ? 'preview' : 'static';
?>

<main class="container" id="article-main">
  <?= insert('/shared/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

  <header class="page-head">
    <h1 class="article-title"><?= htmlEncode($facet['facet_title']); ?></h1>
    <?php if (!empty($facet['facet_info'])) : ?>
      <p class="article-sub"><?= htmlEncode($facet['facet_info']); ?></p>
    <?php endif; ?>
    <div class="chips">
      <span class="chip"><?= (int)($pagesCount ?? 1); ?> <?= __('app.pages'); ?></span>
      <?php if (!empty($childrens)) : ?><span class="chip"><?= count($childrens); ?> <?= __('app.categories'); ?></span><?php endif; ?>
    </div>
  </header>

  <div class="rule"></div>

  <?php if ($childrens) : ?>
    <div class="cat-index">
      <?php foreach ($childrens as $lt) : ?>
        <span class="cat-index__item"><a href="<?= urlDir($lt['facet_path'], $mod); ?>"><?= htmlEncode($lt['facet_title']); ?></a><sup><?= (int)$lt['facet_count']; ?></sup></span>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($items) : ?>
    <div class="fact-rows">
      <?php foreach ($items as $item) :
        $dir  = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
        $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], $mod);
        $img  = !empty($item['item_thumb_img'])
          ? Img::PATH['thumbs'] . $item['item_thumb_img']
          : trim((string)Parser::miniature((string)($item['item_content'] ?? '')));
        ?>
        <article class="fact-row<?= $img === '' ? ' fact-row--text' : ''; ?>">
          <div class="fact-row__body">
            <div class="hint"><?= Html::facetDir($item['facet_list'] ?? '', $mod); ?></div>
            <h3><a href="<?= $path; ?>"><?= htmlEncode($item['item_title'] ?? ''); ?></a></h3>
            <p><?= htmlEncode(Parser::noHTML((string)($item['item_content'] ?? ''), 160)); ?></p>
            <a href="<?= $path; ?>"><?= __('app.read_fact'); ?> →</a>
          </div>
          <?php if ($img !== '') : ?>
            <a class="fact-row__thumb" href="<?= $path; ?>" tabindex="-1" aria-hidden="true">
              <img src="<?= htmlEncode($img); ?>" alt="<?= htmlEncode($item['item_title'] ?? ''); ?>" loading="lazy">
            </a>
          <?php endif; ?>
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
