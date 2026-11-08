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

$slugify = class_exists('\Cocur\Slugify\Slugify') ? new \Cocur\Slugify\Slugify() : null;
$toc = [];
foreach (explode("\n", (string)($item['item_content'] ?? '')) as $line) {
  if (preg_match('/^#{2,3}\s+(.+)$/u', trim($line), $m)) {
    $toc[] = [
      'text'  => trim($m[1] ?? ''),
      'anchor' => $slugify ? $slugify->slugify(html_entity_decode(strip_tags(trim($m[1] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8')) : trim($m[1] ?? ''),
    ];
  }
}
?>

<main class="wrap article-page" id="article-main">
  <?= insert('/shared/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

  <header class="article-title-block" id="article-top">
    <div class="eyebrow">
      <span class="small-square"></span> <?= Html::facetDir($item['facet_list'] ?? '', $mod); ?>
    </div>
    <h1><?= htmlEncode($item['item_title']); ?>
      <?php if (!empty($preview)) : ?>
        <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
          <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
        </a>
      <?php endif; ?>
    </h1>
    <p class="article-subtitle"><?= htmlEncode(Parser::noHTML($item['item_content'], 90)); ?></p>
    <div class="article-title-foot">
      <span><?= langDate($item['item_date']); ?></span>
      <span>
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open" aria-hidden="true"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
        <?= __('app.encyclopedia_name'); ?>
      </span>
    </div>
  </header>

  <div class="reading-layout">
    <aside class="article-sidebar">
      <nav aria-label="<?= __('app.article_toc_label'); ?>" class="article-toc">
        <span class="eyebrow"><?= __('app.article_toc_eyebrow'); ?></span>
        <?php if ($toc) : ?>
          <ol>
            <?php foreach ($toc as $i => $tl) : ?>
              <li><a href="#<?= htmlEncode($tl['anchor']); ?>"><span><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT); ?></span><?= htmlEncode($tl['text']); ?></a></li>
            <?php endforeach; ?>
          </ol>
        <?php endif; ?>
        <a class="toc-back" href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
          <?= __('app.back_archive'); ?>
        </a>
      </nav>
    </aside>

    <article class="reading-column">
      <?= insert('/site/components/article-body', ['item' => $item, 'mod' => $mod, 'faq' => $faq]); ?>

      <?php if ($wikiTerms) : ?>
        <div class="article-source">
          <span class="eyebrow"><?= __('app.wiki_terms'); ?></span>
          <p>
            <?php foreach ($wikiTerms as $term) : ?>
              <a href="<?= urlItem($term['path'], $term['slug'], $mod); ?>"><?= htmlEncode($term['title']); ?></a>
            <?php endforeach; ?>
          </p>
        </div>
      <?php endif; ?>

      <?php $srcUrl = trim((string)($item['item_source_url'] ?? '')); ?>
      <?php if (!empty($item['item_source_title']) || $srcUrl !== '') : ?>
        <div class="article-source">
          <span class="eyebrow"><?= __('app.source'); ?></span>
          <p>
            <?php if ($srcUrl !== '') : ?>
              <a href="<?= htmlEncode($srcUrl); ?>" rel="nofollow"><?= htmlEncode(!empty($item['item_source_title']) ? $item['item_source_title'] : $srcUrl); ?></a>
            <?php else : ?>
              <?= htmlEncode($item['item_source_title']); ?>
            <?php endif; ?>
          </p>
        </div>
      <?php endif; ?>

      <div class="article-bottom">
        <a href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>" class="read-link">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
          <?= __('app.to_archive'); ?>
        </a>
        <a href="#article-top" class="read-link">
          <?= __('app.to_top'); ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up" aria-hidden="true"><path d="m5 12 7-7 7 7"/><path d="M12 19V5"/></svg>
        </a>
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
    <section class="article-related" id="article-related" aria-labelledby="related-title">
      <div class="section-heading">
        <h2 id="related-title"><?= __('app.continue_discover'); ?></h2>
        <a href="<?= urlDir('articles', $mod); ?>">
          <?= __('app.whole_archive'); ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
        </a>
      </div>
      <div class="related-grid">
        <?php foreach ($relCards as $value) :
          $rDir = preg_split('/(@)/', (string)($value['facets'] ?? ''));
          $rPath = urlItem(isset($rDir[2]) ? trim($rDir[2], '/') : '', (string)($value['slug'] ?? ''), $mod);
          ?>
          <article>
            <span class="article-meta"><?= Html::facetDir($value['facets'] ?? '', $mod); ?></span>
            <h3><a href="<?= $rPath; ?>"><?= htmlEncode($value['title'] ?? ''); ?></a></h3>
            <p><?= htmlEncode(Parser::noHTML((string)($value['excerpt'] ?? ''), 130)); ?></p>
            <a class="read-link" href="<?= $rPath; ?>">
              <?= __('app.read_article'); ?>
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
            </a>
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