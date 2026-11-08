<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview), 'activeRoot' => $activeRoot ?? '']); ?>

<?php
$mod       = !empty($preview) ? 'preview' : 'static';
$didYouKnow = $didYouKnow ?? [];
$bestFacts  = $bestFacts ?? [];
$featuredBig = $featuredBig ?? null;
$sections   = $sections ?? [];
$items      = $items ?? [];
$popular    = $popular ?? [];
$stats      = $stats ?? [];

// Картинка записи: миниатюра из базы или извлечённая из контента
$cardImg = function (array $item) use ($mod) {
  if (!empty($item['item_thumb_img'])) return Img::PATH['thumbs'] . $item['item_thumb_img'];
  return Parser::miniature($item['item_content'] ?? '');
};
// Лейбл картинки (первый сегмент пути раздела)
$labelOf = function (array $item) {
  $dir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
  return strtoupper(trim(str_replace('-', ' ', (string)($dir[2] ?? '')), '/'));
};
$hrefOf = function (array $item) use ($mod) {
  $dir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
  return urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], $mod);
};
?>

<main class="wrap" id="home-main">
  <section class="intro" id="home-intro">
    <div>
      <div class="eyebrow"><span class="small-square"></span> <?= __('app.intro_eyebrow'); ?></div>
      <h1><?= __('app.site_name_home'); ?></h1>
    </div>
    <p><?= __('app.intro_tagline'); ?></p>
  </section>

  <?php $lead = $bestFacts[0] ?? null; $portrait = $bestFacts[1] ?? null; $crow = $popular[0] ?? null; ?>
  <?php if ($lead) : ?>
    <section id="home-featured" aria-labelledby="featured-title">
      <div class="section-heading">
        <h2 id="featured-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bookmark" aria-hidden="true"><path d="M17 3a2 2 0 0 1 2 2v15a1 1 0 0 1-1.496.868l-4.512-2.578a2 2 0 0 0-1.984 0l-4.512 2.578A1 1 0 0 1 5 20V5a2 2 0 0 1 2-2z"/></svg>
          <?= __('app.featured_title'); ?>
        </h2>
        <a href="<?= !empty($preview) ? rtrim(config('general', 'url'), '/') . '/mod/admin/preview/best' : '/best/'; ?>">
          <?= __('app.all_collection'); ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
        </a>
      </div>

      <div class="featured-grid">
        <article class="lead-article">
          <a class="lead-image image-link" href="<?= $hrefOf($lead); ?>">
            <?php if ($img = $cardImg($lead)) : ?>
              <img src="<?= htmlEncode($img); ?>" alt="<?= htmlEncode($lead['item_title']); ?>" fetchpriority="high">
            <?php else : ?>
              <?php $tone = ['#d7cec0', '#bfc9dc', '#c8dcc8', '#d9cdc2']; ?>
              <div style="width:100%;height:100%;background:<?= $tone[array_rand($tone)]; ?>"></div>
            <?php endif; ?>
            <span class="image-label"><?= htmlEncode($labelOf($lead)); ?></span>
          </a>
          <div class="article-meta"><?= Html::facetDir($lead['facet_list'] ?? '', $mod); ?></div>
          <h3><a href="<?= $hrefOf($lead); ?>"><?= htmlEncode($lead['item_title']); ?></a></h3>
          <p><?= htmlEncode(Parser::noHTML($lead['item_content'], 220)); ?></p>
          <a class="read-link" href="<?= $hrefOf($lead); ?>">
            <?= __('app.read_article'); ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
          </a>
        </article>

        <?php if ($portrait) : ?>
        <div class="side-articles">
          <?php if ($portrait) : ?>
          <article class="portrait-article">
            <div>
              <div class="article-meta"><?= Html::facetDir($portrait['facet_list'] ?? '', $mod); ?></div>
              <h3><a href="<?= $hrefOf($portrait); ?>"><?= htmlEncode($portrait['item_title']); ?></a></h3>
              <p><?= htmlEncode(Parser::noHTML($portrait['item_content'], 160)); ?></p>
              <a class="read-link" href="<?= $hrefOf($portrait); ?>">
                <?= __('app.read_btn'); ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
              </a>
            </div>
            <?php if ($pImg = $cardImg($portrait)) : ?>
              <a class="image-link portrait" href="<?= $hrefOf($portrait); ?>">
                <img src="<?= htmlEncode($pImg); ?>" alt="<?= htmlEncode($portrait['item_title']); ?>">
              </a>
            <?php endif; ?>
          </article>
          <?php endif; ?>

          <?php if ($crow) : ?>
          <article class="crow-article">
            <div class="article-meta"><?= Html::facetDir($crow['facet_list'] ?? '', $mod); ?></div>
            <span class="fact-ornament" aria-hidden="true">«</span>
            <h3><a href="<?= $hrefOf($crow); ?>"><?= htmlEncode($crow['item_title']); ?></a></h3>
            <p><?= htmlEncode(Parser::noHTML((string)($crow['item_content'] ?? ''), 150)); ?></p>
            <a class="read-link" href="<?= $hrefOf($crow); ?>">
              <?= __('app.learn_more'); ?>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
            </a>
          </article>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($didYouKnow) : ?>
    <section class="did-you-know" id="home-facts" aria-labelledby="facts-title">
      <div class="fact-heading">
        <span class="eyebrow"><?= __('app.facts_eyebrow'); ?></span>
        <h2 id="facts-title"><?= __('app.facts_title'); ?></h2>
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open" aria-hidden="true"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
      </div>
      <?php foreach (array_slice($didYouKnow, 0, 3) as $i => $dyk) :
        $dp = $hrefOf($dyk);
        ?>
        <div class="fact-snippet">
          <span class="fact-number"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT); ?></span>
          <p><?= htmlEncode(Parser::noHTML($dyk['item_content'], 120)); ?></p>
          <a href="<?= $dp; ?>">
            <?= __('app.about_fact'); ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
          </a>
        </div>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <?php if ($items) : ?>
    <section id="home-new" class="new-section" aria-labelledby="new-title">
      <div class="section-heading">
        <h2 id="new-title"><?= __('app.new_title'); ?></h2>
        <a href="<?= urlDir('facts', $mod); ?>">
          <?= __('app.all_facts'); ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
        </a>
      </div>
      <div class="new-grid">
        <?php $ni = 0; ?>
        <?php foreach (array_slice($items, 0, 6) as $item) :
          $np = $hrefOf($item);
          $ni++;
          ?>
          <article class="new-card">
            <div class="article-meta"><?= Html::facetDir($item['facet_list'] ?? '', $mod); ?></div>
            <h3><a href="<?= $np; ?>"><?= htmlEncode($item['item_title']); ?></a></h3>
            <p><?= htmlEncode(Parser::noHTML($item['item_content'], 130)); ?></p>
            <a class="card-arrow" href="<?= $np; ?>" aria-label="<?= __('app.read_btn'); ?>: <?= htmlEncode($item['item_title']); ?>">
              <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
            </a>
            <span class="card-number" aria-hidden="true"><?= sprintf('%02d', $ni); ?></span>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($sections) : ?>
    <section id="home-categories" class="categories" aria-labelledby="categories-title">
      <div>
        <span class="eyebrow"><?= __('app.categories_eyebrow'); ?></span>
        <h2 id="categories-title"><?= __('app.categories_title'); ?></h2>
      </div>
      <div class="category-links">
        <?php foreach ($sections as $cat) : ?>
          <a href="<?= urlDir($cat['path'], $mod); ?>">
            <span><?= htmlEncode($cat['title']); ?><?php if (!empty($cat['help'])) : ?><small><?= htmlEncode($cat['help']); ?></small><?php endif; ?></span>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</main>

<?= insert('/site/layouts/footer', ['preview' => !empty($preview)]); ?>