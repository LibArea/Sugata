<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview), 'activeRoot' => $activeRoot ?? '']); ?>

<?php
$mod        = !empty($preview) ? 'preview' : 'static';
$didYouKnow = $didYouKnow ?? [];
$bestFacts  = $bestFacts ?? [];
$sections   = $sections ?? [];
$items      = $items ?? [];
$stats      = $stats ?? [];
$featured   = $featuredBig ?? null;

$cardImg = function (array $item) {
    if (!empty($item['item_thumb_img'])) {
        return Img::PATH['thumbs'] . $item['item_thumb_img'];
    }

    $mini = trim((string)Parser::miniature((string)($item['item_content'] ?? '')));
    if ($mini !== '') {
        return $mini;
    }

    // Фото из карточки-инфобокса
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

// Корень раздела: facts / articles / web
$rootOf = function (array $item) {
    $dir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));

    return strtok(trim((string)($dir[2] ?? ''), '/'), '/');
};

// Название категории — последняя в списке фасетов
$catOf = function (array $item) {
    $dir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
    $last = end($dir);

    return $last !== false ? (string)$last : '';
};

$dayFact = $featured ?: ($bestFacts[0] ?? null);
$dayPath = $dayFact ? $hrefOf($dayFact) : '';
$dayText = $dayFact ? Parser::noHTML((string)($dayFact['item_content'] ?? ''), 180) : '';
?>

<div class="home-band">
  <div class="container">
    <div class="home-band__in">
      <div>
        <div class="home-kicker"><?= __('app.encyclopedia_name_short'); ?></div>
        <h1 class="article-title"><?= __('app.site_name_home'); ?></h1>
        <p class="article-sub"><?= __('app.intro_tagline'); ?></p>
      </div>

      <?php if ($dayFact) : ?>
        <article class="home-today">
          <div class="home-kicker"><?= __('app.theme_day'); ?></div>
          <?php $dayImg = trim((string)$cardImg($dayFact)); ?>
          <?php if ($dayImg !== '') : ?>
            <a class="home-today__img" href="<?= $dayPath; ?>" tabindex="-1" aria-hidden="true">
              <img src="<?= htmlEncode($dayImg); ?>" alt="<?= htmlEncode($dayFact['item_title']); ?>" loading="lazy">
            </a>
          <?php endif; ?>
          <h2><a href="<?= $dayPath; ?>"><?= htmlEncode($dayFact['item_title']); ?></a></h2>
          <p><?= htmlEncode($dayText); ?></p>
          <div class="home-today__meta">
            <span><?= htmlEncode($catOf($dayFact)); ?></span>
            <span><?= langDate($dayFact['item_date']); ?></span>
          </div>
        </article>
      <?php endif; ?>
    </div>
  </div>
</div>

<main class="container" id="home-main">

  <div class="home-kpi">
    <div><b><?= (int)($stats['facts'] ?? 0); ?></b><span><?= __('app.kpi_facts'); ?></span></div>
    <div><b><?= (int)($stats['categories'] ?? 0); ?></b><span><?= __('app.kpi_sections'); ?></span></div>
    <div><b><?= (int)($stats['articles'] ?? 0); ?></b><span><?= __('app.kpi_articles'); ?></span></div>
    <div><b><?= (int)($stats['sites'] ?? 0); ?></b><span><?= __('app.kpi_sites'); ?></span></div>
  </div>

  <?php if ($bestFacts) : ?>
    <div class="section-head">
      <h2><?= __('app.featured_title'); ?></h2>
      <a href="<?= urlDir('best', $mod); ?>"><?= __('app.all_collection'); ?> →</a>
    </div>
    <div class="card-grid card-grid--2">
      <?php foreach (array_slice($bestFacts, 0, 2) as $fact) :
        $factPath  = $hrefOf($fact);
        $factCat   = $catOf($fact);
        $factLabel = match ($rootOf($fact)) {
          'articles' => __('app.read_article'),
          'web'      => __('app.open_site'),
          default    => __('app.read_fact'),
        };
        ?>
        <article class="card">
          <?php $factImg = trim((string)$cardImg($fact)); ?>
          <?php if ($factImg !== '') : ?>
            <a class="card__img" href="<?= $factPath; ?>" tabindex="-1" aria-hidden="true">
              <img src="<?= htmlEncode($factImg); ?>" alt="<?= htmlEncode($fact['item_title']); ?>" loading="lazy">
            </a>
          <?php endif; ?>
          <div class="chips card__chips">
            <?php if ($factCat !== '') : ?><span class="chip"><?= htmlEncode($factCat); ?></span><?php endif; ?>
            <span class="chip"><?= langDate($fact['item_date']); ?></span>
          </div>
          <h3><a href="<?= $factPath; ?>"><?= htmlEncode($fact['item_title']); ?></a>
            <?php if (!empty($preview)) : ?>
              <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => (int)$fact['item_id']]); ?>">
                <svg class="icon" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
              </a>
            <?php endif; ?>
          </h3>
          <p><?= htmlEncode(Parser::noHTML((string)($fact['item_content'] ?? ''), 300)); ?></p>
          <p class="card__more"><a href="<?= $factPath; ?>"><?= $factLabel; ?> →</a></p>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($didYouKnow) : ?>
    <div class="section-head">
      <h2><?= __('app.facts_title'); ?></h2>
      <a href="<?= urlDir('facts', $mod); ?>"><?= __('app.all_facts'); ?> →</a>
    </div>
    <div class="home-num">
      <?php foreach (array_slice($didYouKnow, 0, 3) as $i => $dyk) : ?>
        <a href="<?= $hrefOf($dyk); ?>">
          <b><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT); ?></b>
          <h4><?= htmlEncode($dyk['item_title'] ?? ''); ?></h4>
          <p><?= htmlEncode(Parser::noHTML((string)($dyk['item_content'] ?? ''), 90)); ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($items) : ?>
    <div class="section-head">
      <h2><?= __('app.new_title'); ?></h2>
      <a href="<?= urlDir('facts', $mod); ?>"><?= __('app.all_facts'); ?> →</a>
    </div>
    <div class="home-list">
      <?php foreach (array_slice($items, 0, 3) as $item) : ?>
        <a class="home-list__item" href="<?= $hrefOf($item); ?>">
          <span class="home-list__date"><?= date('d.m', strtotime((string)$item['item_date'])); ?></span>
          <span>
            <span class="home-list__title"><?= htmlEncode($item['item_title'] ?? ''); ?></span>
            <span class="home-list__excerpt"><?= htmlEncode(Parser::noHTML((string)($item['item_content'] ?? ''), 120)); ?></span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($sections) : ?>
    <div class="section-head">
      <h2><?= __('app.categories_title'); ?></h2>
      <a href="<?= urlDir('facts', $mod); ?>"><?= __('app.whole_archive'); ?> →</a>
    </div>
    <div class="home-cats">
      <?php foreach ($sections as $cat) : ?>
        <a href="<?= urlDir($cat['path'], $mod); ?>">
          <b><?= htmlEncode($cat['title']); ?></b>
          <?php if (!empty($cat['help'])) : ?><span><?= htmlEncode($cat['help']); ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?= insert('/site/layouts/footer', ['preview' => !empty($preview)]); ?>
