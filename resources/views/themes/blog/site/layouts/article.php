<?php
/*
 * Блог-layout просмотра статьи/факта/сайта/страницы.
 *
 * Отличается от вики-версии:
 *  - нет боковой toc-колонки (блог — узкая колонка чтения);
 *  - обложка крупно вверху, серифная типографика;
 *  - мета-строка вместо «чипов»;
 *  - related как сетка «ещё посты» (blog-grid).
 *
 * Параметры — те же, что в themes/wiki/site/layouts/article.php:
 *   $item, $type, $mod, $preview, $meta, $breadcrumb, $activeRoot,
 *   $toc, $related, $similar, $faq, $wikiTerms, $backlinks, $sideImg, $infobox.
 */

$mod        = !empty($mod) ? $mod : (!empty($preview) ? 'preview' : 'static');
$type       = (string)($type ?? 'facts');
$faq        = $faq ?? [];
$wikiTerms  = $wikiTerms ?? [];
$backlinks  = $backlinks ?? [];
$toc        = $toc ?? build_article_toc((string)($item['item_content'] ?? ''));
$activeRoot = $activeRoot ?? (($type === 'sites') ? 'web' : $type);
$sideImg    = (bool)($sideImg ?? false);
$infobox    = (bool)($infobox ?? false);

$facetPath = facet_list_path((string)($item['facet_list'] ?? ''));

// Обложка поста: thumb или первая картинка контента
$cover = trim((string)article_side_image($item));
?>

<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview), 'activeRoot' => $activeRoot]); ?>

<main class="container" id="article-main">

  <?= dispatch('site_article_before', fn() => '', ['item' => $item, 'type' => $type, 'mod' => $mod]); ?>

  <article class="post-body blog-article">

    <header class="post-head" id="article-top">
      <?php if ($cover !== '') : ?>
        <a class="post-cover" href="#article-top" tabindex="-1" aria-hidden="true">
          <img src="<?= htmlEncode($cover); ?>" alt="<?= htmlEncode($item['item_title']); ?>">
        </a>
      <?php endif; ?>

      <h1 class="article-title">
        <?= htmlEncode($item['item_title']); ?>
        <?php if (!empty($preview)) : ?>
          <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => (int)($item['item_id'] ?? 0)]); ?>">
            <svg class="icon" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
          </a>
        <?php endif; ?>
      </h1>

      <div class="post-meta">
        <?= Html::facetDir($item['facet_list'] ?? '', $mod); ?>
        <span><?= langDate($item['item_date']); ?></span>
        <span><?= __('app.encyclopedia_name'); ?></span>
      </div>

      <?php if (trim((string)Parser::noHTML((string)($item['item_content'] ?? ''), 90)) !== '') : ?>
        <p class="article-sub post-deck"><?= htmlEncode(Parser::noHTML((string)($item['item_content'] ?? ''), 160)); ?></p>
      <?php endif; ?>
    </header>

    <div class="article-body">
      <?php if ($infobox) : ?>
        <?= insert('/site/components/infobox', ['item' => $item, 'mod' => $mod, 'toc' => $toc]); ?>
      <?php endif; ?>

      <?= dispatch('site_article_body', fn() => insert('/site/components/article-body', ['item' => $item, 'mod' => $mod, 'faq' => $faq, 'noFigure' => $sideImg]), ['item' => $item, 'mod' => $mod, 'faq' => $faq]); ?>

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

      <div class="toolbar" style="margin-top:var(--s6)">
        <a class="btn" href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>">← <?= __('app.to_archive'); ?></a>
        <a class="btn" href="#article-top"><?= __('app.to_top'); ?> ↑</a>
      </div>
    </div>
  </article>

  <?php $relCards = article_related_cards($related ?? [], $similar ?? []); ?>
  <?php if ($relCards) : ?>
    <?= dispatch('site_article_related_before', fn() => '', ['item' => $item, 'type' => $type]); ?>
    <section id="article-related" aria-labelledby="related-title" style="margin-top:var(--s7)">
      <div class="section-head">
        <h2 id="related-title"><?= __('app.continue_discover'); ?></h2>
        <a href="<?= urlDir($activeRoot === 'web' ? 'web' : $type, $mod); ?>"><?= __('app.whole_archive'); ?> →</a>
      </div>
      <div class="blog-grid">
        <?php foreach ($relCards as $value) :
          $rPath = urlItem((string)$value['facets'] && ($rf = parse_facets((string)$value['facets'])) ? $rf[0]['path'] : '', (string)($value['slug'] ?? ''), $mod);
          $rRoot = strtok(facet_list_path((string)($value['facets'] ?? '')), '/');
          $readLabel = match ($rRoot) {
              'articles' => __('app.read_article'),
              'web'      => __('app.open_site'),
              default    => __('app.read_fact'),
          };
          ?>
          <article class="blog-card">
            <div class="blog-card__body">
              <div class="blog-card__meta">
                <span><?= Html::facetDir($value['facets'] ?? '', $mod); ?></span>
              </div>
              <h3><a href="<?= $rPath; ?>"><?= htmlEncode($value['title'] ?? ''); ?></a></h3>
              <p><?= htmlEncode(Parser::noHTML((string)($value['excerpt'] ?? ''), 160)); ?></p>
              <a class="blog-card__more" href="<?= $rPath; ?>"><?= $readLabel; ?> →</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?= dispatch('site_article_backlinks', fn() => insert('/site/components/backlinks', ['backlinks' => $backlinks, 'preview' => !empty($preview)]), ['backlinks' => $backlinks, 'preview' => !empty($preview)]); ?>

  <?php if (empty($preview) && $item['item_slug'] ?? null) : ?>
    <?php
      $ld = dispatch('site_article_ldjson', fn($d) => $d, ['data' => [
        '@context'      => 'https://schema.org',
        '@type'         => 'Article',
        'headline'      => $item['item_title'],
        'datePublished' => $item['item_date'],
        'dateModified'  => $item['item_modified'] ?? $item['item_date'],
        'url'           => config('general', 'url_html') . '/' . trim($facetPath, '/') . '/' . $item['item_slug'] . '.html',
        'image'         => !empty($item['item_thumb_img']) ? config('general', 'url_html') . '/' . $item['item_thumb_img'] : ($cover !== '' ? config('general', 'url_html') . $cover : null),
        'citation'      => !empty($item['item_source_url']) ? $item['item_source_url'] : null,
        'publisher'     => [
          '@type' => 'Organization',
          'name'  => config('general', 'site_name'),
        ],
      ]]);
      $d = is_array($ld) ? $ld : null;
    ?>
    <?php if ($d) : ?>
    <script type="application/ld+json">
    <?= json_encode(array_filter($d, fn($v) => $v !== null), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_LINE_TERMINATORS); ?>
    </script>
    <?php endif; ?>
  <?php endif; ?>
</main>

<?= insert('/site/layouts/footer', ['preview' => !empty($preview)]); ?>