<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview), 'activeRoot' => $activeRoot ?? '']); ?>

<?php
$mod        = !empty($preview) ? 'preview' : 'static';
$didYouKnow = $didYouKnow ?? [];
$bestFacts  = $bestFacts ?? [];
$sections   = $sections ?? [];
$items      = $items ?? [];
$stats      = $stats ?? [];
$popular    = $popular ?? [];
$featured   = $featuredBig ?? null;

$cardImg = function (array $item) {
    if (!empty($item['item_thumb_img'])) {
        return Img::PATH['thumbs'] . $item['item_thumb_img'];
    }
    $mini = trim((string)Parser::miniature((string)($item['item_content'] ?? '')));
    if ($mini !== '') {
        return $mini;
    }
    $itemId = (int)($item['item_id'] ?? 0);
    if ($itemId > 0) {
        $inst = \App\Models\TemplateModel::instance($itemId);
        $img  = trim((string)($inst['values']['image'] ?? ''));
        if ($img !== '') {
            return $img;
        }
    }
    return '';
};

$hrefOf = function (array $item) use ($mod) {
    $dir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
    return urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], $mod);
};

$catOf = function (array $item) {
    $dir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
    $last = end($dir);
    return $last !== false ? (string)$last : '';
};

// Главный пост = featured (или первое избранное)
$lead = $featured ?: ($bestFacts[0] ?? null);
$leadPath = $lead ? $hrefOf($lead) : '';
$leadImg  = $lead ? trim((string)$cardImg($lead)) : '';
$leadText = $lead ? Parser::noHTML((string)($lead['item_content'] ?? ''), 260) : '';

// Лента = остальные избранные + новые записи (соединить, не дублируя по id)
$feed = [];
$seen = [];
foreach (array_merge($bestFacts, $items) as $p) {
    $id = (int)($p['item_id'] ?? 0);
    if ($id > 0 && isset($seen[$id])) {
        continue;
    }
    if ($id > 0) {
        $seen[$id] = true;
    }
    $feed[] = $p;
}
$feed = array_slice($feed, 0, 8);
?>

<div class="blog-hero">
  <div class="container">
    <?php if ($lead) : ?>
      <article class="blog-hero__post">
        <?php if ($leadImg !== '') : ?>
          <a class="blog-hero__img" href="<?= $leadPath; ?>" tabindex="-1" aria-hidden="true">
            <img src="<?= htmlEncode($leadImg); ?>" alt="<?= htmlEncode($lead['item_title']); ?>">
          </a>
        <?php endif; ?>
        <div class="blog-hero__body">
          <span class="blog-kicker"><?= __('app.theme_day'); ?></span>
          <h1><a href="<?= $leadPath; ?>"><?= htmlEncode($lead['item_title']); ?></a></h1>
          <p class="blog-hero__excerpt"><?= htmlEncode($leadText); ?></p>
          <div class="blog-post-meta">
            <?php $lc = $catOf($lead); if ($lc !== '') : ?><span><?= htmlEncode($lc); ?></span><?php endif; ?>
            <span><?= langDate($lead['item_date']); ?></span>
          </div>
        </div>
      </article>
    <?php endif; ?>
  </div>
</div>

<!-- Главная зона блога: лента + сайдбар -->
<div class="container">
  <div class="blog-layout">

    <!-- ОСНОВНОЙ СТОЛБЕЦ: лента постов -->
    <div class="blog-main">
      <div class="blog-section-head">
        <h2><?= __('app.featured_title'); ?></h2>
        <a href="<?= urlDir('best', $mod); ?>"><?= __('app.all_collection'); ?> →</a>
      </div>

      <?php if ($feed) : ?>
        <div class="blog-feed">
          <?php foreach ($feed as $post) :
            $pPath = $hrefOf($post);
            $pImg  = trim((string)$cardImg($post));
            $pCat  = $catOf($post);
            ?>
            <article class="blog-feed__item">
              <?php if ($pImg !== '') : ?>
                <a class="blog-feed__img" href="<?= $pPath; ?>" tabindex="-1" aria-hidden="true">
                  <img src="<?= htmlEncode($pImg); ?>" alt="<?= htmlEncode($post['item_title'] ?? ''); ?>" loading="lazy">
                </a>
              <?php endif; ?>
              <div class="blog-feed__body">
                <div class="blog-post-meta">
                  <?php if ($pCat !== '') : ?><span><?= htmlEncode($pCat); ?></span><?php endif; ?>
                  <span><?= langDate($post['item_date']); ?></span>
                </div>
                <h3><a href="<?= $pPath; ?>"><?= htmlEncode($post['item_title'] ?? ''); ?></a></h3>
                <p><?= htmlEncode(Parser::noHTML((string)($post['item_content'] ?? ''), 150)); ?></p>
                <a class="blog-more" href="<?= $pPath; ?>">Читать →</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else : ?>
        <p class="hint"><?= __('app.no_content'); ?></p>
      <?php endif; ?>
    </div>

    <!-- САЙДБАР -->
    <aside class="blog-aside">

      <!-- Популярное -->
      <?php if ($popular) : ?>
        <div class="blog-widget">
          <h4 class="blog-widget__title"><?= __('app.nav_featured'); ?></h4>
          <ol class="blog-popular">
            <?php foreach (array_slice($popular, 0, 5) as $i => $pop) : ?>
              <li>
                <b><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT); ?></b>
                <a href="<?= $hrefOf($pop); ?>"><?= htmlEncode($pop['item_title'] ?? ''); ?></a>
              </li>
            <?php endforeach; ?>
          </ol>
        </div>
      <?php endif; ?>

      <!-- Рубрики -->
      <?php if ($sections) : ?>
        <div class="blog-widget">
          <h4 class="blog-widget__title"><?= __('app.nav_categories'); ?></h4>
          <ul class="blog-cats">
            <?php foreach ($sections as $sec) :
              $catLink = isset($sec['link']) ? $sec['link'] : urlDir($sec['path'] ?? '', $mod);
              ?>
              <li>
                <a href="<?= $catLink; ?>"><?= htmlEncode($sec['name'] ?? ($sec['title'] ?? '')); ?></a>
                <?php if (!empty($sec['childrens'])) : ?>
                  <ul>
                    <?php foreach (array_slice($sec['childrens'], 0, 5) as $child) :
                      $childLink = isset($child['link']) ? $child['link'] : urlDir($child['path'] ?? '', $mod);
                      ?>
                      <li><a href="<?= $childLink; ?>"><?= htmlEncode($child['name'] ?? ($child['title'] ?? '')); ?></a></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <!-- Вы знали -->
      <?php if ($didYouKnow) : ?>
        <div class="blog-widget blog-widget--tint">
          <h4 class="blog-widget__title"><?= __('app.facts_title'); ?></h4>
          <ul class="blog-facts">
            <?php foreach (array_slice($didYouKnow, 0, 4) as $dyk) : ?>
              <li>
                <a href="<?= $hrefOf($dyk); ?>"><?= htmlEncode($dyk['item_title'] ?? ''); ?></a>
                <p><?= htmlEncode(Parser::noHTML((string)($dyk['item_content'] ?? ''), 70)); ?></p>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <!-- Статистика -->
      <?php if ($stats) : ?>
        <div class="blog-widget blog-widget--stats">
          <?php $statRows = [
            __('app.encyclopedia_name') => (int)($stats['facts'] ?? 0),
            __('app.nav_articles')      => (int)($stats['articles'] ?? 0),
            __('app.nav_sites')         => (int)($stats['sites'] ?? 0),
            __('app.nav_categories')    => (int)($stats['categories'] ?? 0),
          ]; ?>
          <?php foreach ($statRows as $label => $val) : ?>
            <div class="blog-stat"><span><?= $label; ?></span><b><?= number_format($val, 0, '.', ' '); ?></b></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </aside>
  </div>
</div>

<?= insert('/site/layouts/footer', ['preview' => !empty($preview)]); ?>