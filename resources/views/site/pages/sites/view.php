<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview), 'activeRoot' => 'web']); ?>

<?php
$mod    = !empty($preview) ? 'preview' : 'static';
$dir    = $dir ?? [];
$srcUrl = trim((string)($item['item_source_url'] ?? ''));
$host   = $srcUrl !== '' ? (string)parse_url($srcUrl, PHP_URL_HOST) : '';
$host   = preg_replace('~^www\.~i', '', $host);
$screen = trim((string)($item['item_screen_img'] ?? ''));
$fav    = trim((string)($item['item_favicon_img'] ?? ''));
$tcy    = trim((string)($item['item_yandex_tcy'] ?? ''));

$relatedSites = $relatedSites ?? \App\Models\ItemModel::sitesByFacet(
    (int)($dir[0] ?? 0),
    (int)$item['item_id'],
    3
);
?>

<main class="container" id="article-main">
  <?= insert('/shared/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

  <header class="page-head" id="article-top">
    <h1 class="article-title"><?= htmlEncode($item['item_title']); ?>
      <?php if (!empty($preview)) : ?>
        <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
          <svg class="icon" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
        </a>
      <?php endif; ?>
    </h1>
    <p class="article-sub"><?= htmlEncode(Parser::noHTML($item['item_content'], 90)); ?></p>
    <div class="chips">
      <?= Html::facetDir($item['facet_list'] ?? '', $mod); ?>
      <span class="chip"><?= langDate($item['item_date']); ?></span>
      <?php if ($host !== '') : ?><span class="chip"><?= htmlEncode($host); ?></span><?php endif; ?>
      <?php if ($tcy !== '') : ?><span class="chip"><?= __('app.tcy'); ?> <?= htmlEncode($tcy); ?></span><?php endif; ?>
    </div>
  </header>

  <div class="rule"></div>

  <article class="article-body">
    <?php if ($screen !== '') : ?>
      <figure class="article-figure">
        <img src="<?= htmlEncode('/uploads/screens/' . $screen); ?>" alt="<?= htmlEncode($item['item_title']); ?>">
        <figcaption><?= __('app.screenshot_of'); ?> <?= htmlEncode($host); ?></figcaption>
      </figure>
    <?php endif; ?>

    <?php if ($srcUrl !== '') : ?>
      <div class="toolbar" style="margin-bottom:16px">
        <a class="btn" href="<?= htmlEncode($srcUrl); ?>" target="_blank" rel="nofollow noopener noreferrer"><?= __('app.visit_site'); ?></a>
        <?php if ($fav !== '') : ?>
          <img src="<?= htmlEncode('/uploads/favicons/' . $fav); ?>" alt="" width="16" height="16" loading="lazy">
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="prose">
      <?= markdown($item['item_content'], 'text', !empty($preview)); ?>
    </div>

    <div class="toolbar" style="margin-top:24px">
      <a class="btn" href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>">← <?= __('app.to_archive'); ?></a>
      <a class="btn" href="#article-top"><?= __('app.to_top'); ?> ↑</a>
    </div>
  </article>

  <?php if ($relatedSites) : ?>
    <section id="article-related" aria-labelledby="related-title">
      <div class="section-head">
        <h2 id="related-title"><?= __('app.related_sites'); ?></h2>
        <a href="<?= urlDir(trim((string)($dir[2] ?? 'web'), '/'), $mod); ?>"><?= __('app.whole_archive'); ?> →</a>
      </div>
      <div class="grid grid--3">
        <?php foreach ($relatedSites as $site) :
          $sDir = preg_split('/(@)/', (string)($site['facet_list'] ?? ''));
          $sPath = urlItem(isset($sDir[2]) ? trim($sDir[2], '/') : '', (string)($site['item_slug'] ?? ''), $mod);
          $sUrl = trim((string)($site['item_source_url'] ?? ''));
          $sHost = $sUrl !== '' ? preg_replace('~^www\.~i', '', (string)parse_url($sUrl, PHP_URL_HOST)) : '';
          ?>
          <article class="card">
            <div class="hint"><?= htmlEncode($sHost); ?></div>
            <h3><a href="<?= $sPath; ?>"><?= htmlEncode($site['item_title'] ?? ''); ?></a></h3>
            <p><?= htmlEncode(Parser::noHTML((string)($site['item_content'] ?? ''), 130)); ?></p>
            <a href="<?= $sPath; ?>"><?= __('app.read_more'); ?> →</a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?= insert('/site/components/backlinks', ['backlinks' => $backlinks ?? [], 'preview' => !empty($preview)]); ?>
</main>

<?= insert('/site/layouts/footer', ['preview' => !empty($preview)]); ?>
