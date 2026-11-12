<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview), 'activeRoot' => 'articles']); ?>

<?php
$mod     = !empty($preview) ? 'preview' : 'static';
$dir     = $dir ?? [];
$related = $related ?? [];
$similar = $similar ?? [];
$faq     = $faq ?? [];
$backlinks = $backlinks ?? [];

$wikiTerms = $wikiTerms ?? [];
$linkedFacts = $linkedFacts ?? [];

$toc = [];
$tocSeen = [];
foreach (explode("\n", (string)($item['item_content'] ?? '')) as $line) {
  if (preg_match('/^(#{1,6})\s+(.+)$/u', trim($line), $m)) {
    $level  = strlen($m[1]);
    $anchor = Parser::anchor($m[2], $tocSeen);
    if ($level >= 2 && $level <= 3) {
      $toc[] = [
        'text'   => trim($m[2]),
        'anchor' => $anchor,
        'level'  => $level,
      ];
    }
  }
}
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
      <span class="chip"><?= __('app.encyclopedia_name'); ?></span>
    </div>
  </header>

  <div class="rule"></div>

  <div class="article-layout article-layout--2">
    <aside class="toc article-sidebar">
      <nav aria-label="<?= __('app.article_toc_label'); ?>">
        <b><?= __('app.article_toc_eyebrow'); ?></b>
        <?php if ($toc) : ?>
          <ol>
            <?php foreach ($toc as $i => $tl) : ?>
              <li<?= (int)($tl['level'] ?? 2) >= 3 ? ' class="toc-sub"' : '' ?>><a href="#<?= htmlEncode($tl['anchor']); ?>"><?= htmlEncode($tl['text']); ?></a></li>
            <?php endforeach; ?>
          </ol>
        <?php endif; ?>
        <a class="toc-back" href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>"><?= __('app.back_archive'); ?></a>
      </nav>
    </aside>

    <article class="article-body">
      <?= insert('/site/components/article-body', ['item' => $item, 'mod' => $mod, 'faq' => $faq]); ?>

      <?php if ($wikiTerms) : ?>
        <div class="box">
          <div class="hint" style="margin-bottom:6px"><?= __('app.wiki_terms'); ?></div>
          <p style="margin:0">
            <?php foreach ($wikiTerms as $term) : ?>
              <a href="<?= urlItem($term['path'], $term['slug'], $mod); ?>"><?= htmlEncode($term['title']); ?></a>
            <?php endforeach; ?>
          </p>
        </div>
      <?php endif; ?>

      <?php $srcUrl = trim((string)($item['item_source_url'] ?? '')); ?>
      <?php if (!empty($item['item_source_title']) || $srcUrl !== '') : ?>
        <div class="box">
          <div class="hint" style="margin-bottom:6px"><?= __('app.source'); ?></div>
          <p style="margin:0">
            <?php if ($srcUrl !== '') : ?>
              <a href="<?= htmlEncode($srcUrl); ?>" rel="nofollow"><?= htmlEncode(!empty($item['item_source_title']) ? $item['item_source_title'] : $srcUrl); ?></a>
            <?php else : ?>
              <?= htmlEncode($item['item_source_title']); ?>
            <?php endif; ?>
          </p>
        </div>
      <?php endif; ?>

      <div class="toolbar" style="margin-top:24px">
        <a class="btn" href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>">← <?= __('app.to_archive'); ?></a>
        <a class="btn" href="#article-top"><?= __('app.to_top'); ?> ↑</a>
      </div>
    </article>
  </div>

  <?php
  $relCards = [];
  foreach ($related as $value) {
      $relCards[] = [
          'title'   => $value['item_title'] ?? '',
          'slug'    => $value['item_slug'] ?? '',
          'facets'  => $value['facet_list'] ?? '',
          'excerpt' => $value['item_content'] ?? '',
      ];
  }
  if (empty($relCards)) {
      foreach ($similar as $value) {
          $f = json_decode((string)($value['url'] ?? ''), true);
          $relCards[] = [
              'title'   => $value['title'] ?? '',
              'slug'    => $f['slug'] ?? '',
              'facets'  => $f['facets'] ?? '',
              'excerpt' => $value['snippet'] ?? '',
          ];
      }
  }
  ?>
  <?php if ($relCards) : ?>
    <section id="article-related" aria-labelledby="related-title">
      <div class="section-head">
        <h2 id="related-title"><?= __('app.continue_discover'); ?></h2>
        <a href="<?= urlDir('articles', $mod); ?>"><?= __('app.whole_archive'); ?></a>
      </div>
      <div class="grid grid--3">
        <?php foreach ($relCards as $value) :
          $rDir = preg_split('/(@)/', (string)($value['facets'] ?? ''));
          $rPath = urlItem(isset($rDir[2]) ? trim($rDir[2], '/') : '', (string)($value['slug'] ?? ''), $mod);
          $rRoot = strtok(trim((string)($rDir[2] ?? ''), '/'), '/');
          $readLabel = match ($rRoot) {
              'articles' => __('app.read_article'),
              'web'      => __('app.open_site'),
              default    => __('app.read_fact'),
          };
          ?>
          <article class="card">
            <div class="hint"><?= Html::facetDir($value['facets'] ?? '', $mod); ?></div>
            <h3><a href="<?= $rPath; ?>"><?= htmlEncode($value['title'] ?? ''); ?></a></h3>
            <p style="font-size:13px;margin-bottom:8px"><?= htmlEncode(Parser::noHTML((string)($value['excerpt'] ?? ''), 130)); ?></p>
            <a href="<?= $rPath; ?>"><?= $readLabel; ?> →</a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?= insert('/site/components/backlinks', ['backlinks' => $backlinks, 'preview' => !empty($preview)]); ?>

  <?php if (empty($preview)) : ?>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": <?= json_encode($item['item_title'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
    "datePublished": <?= json_encode($item['item_date'], JSON_UNESCAPED_SLASHES); ?>,
    "dateModified": <?= json_encode($item['item_modified'] ?? $item['item_date'], JSON_UNESCAPED_SLASHES); ?>,
    "url": <?= json_encode(config('general', 'url_html') . '/' . trim($dir[2] ?? '', '/') . '/' . $item['item_slug'] . '.html', JSON_UNESCAPED_SLASHES); ?>
    <?php if (!empty($item['item_thumb_img'])) : ?>
    ,"image": <?= json_encode(config('general', 'url_html') . '/' . $item['item_thumb_img'], JSON_UNESCAPED_SLASHES); ?>
    <?php endif; ?>
    <?php if (!empty($item['item_source_url'])) : ?>
    ,"citation": <?= json_encode($item['item_source_url'], JSON_UNESCAPED_SLASHES); ?>
    <?php endif; ?>
    ,"publisher": {
      "@type": "Organization",
      "name": <?= json_encode(config('general', 'site_name'), JSON_UNESCAPED_UNICODE); ?>
    }
  }
  </script>
  <?php endif; ?>
</main>

<?= insert('/site/layouts/footer', ['preview' => !empty($preview)]); ?>
