<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview), 'hero' => true]); ?>

<?php
$mod       = !empty($preview) ? 'preview' : 'static';
$didYouKnow = $didYouKnow ?? [];
$bestFact   = $bestFact ?? null;
$sections   = $sections ?? [];
$items      = $items ?? [];
$popular    = $popular ?? [];
?>

<main class="portal-home">
  <div class="home-intro">
    <h1 class="home-intro__title"><?= __('app.welcome_title'); ?></h1>
    <p class="hint"><?= __('app.home_description'); ?></p>
  </div>

  <div class="portal-columns">
    <!-- Левая (широкая): избранная статья -->
    <section class="col col--wide">
      <h2 class="block-title"><?= __('app.featured_article'); ?></h2>
      <?php if ($bestFact) :
        $dir = preg_split('/(@)/', (string)($bestFact['facet_list'] ?? ''));
        $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$bestFact['item_slug'], $mod);
        $img = !empty($bestFact['item_thumb_img'])
            ? Img::PATH['thumbs'] . $bestFact['item_thumb_img']
            : Parser::miniature($bestFact['item_content'] ?? '');
        ?>
        <div class="article-text">
          <?php if ($img) : ?>
            <a href="<?= $path; ?>"><img class="article-thumb" alt="<?= htmlEncode($bestFact['item_title']); ?>" src="<?= htmlEncode($img); ?>"></a>
          <?php endif; ?>
          <a class="article-title" href="<?= $path; ?>"><?= htmlEncode($bestFact['item_title']); ?></a> —
          <?= htmlEncode(Parser::noHTML($bestFact['item_content'], 260)); ?>
          <a class="more" href="<?= $path; ?>"><?= __('app.read_more'); ?> →</a>
        </div>
      <?php endif; ?>
    </section>

    <!-- Средняя: категории -->
    <section class="col">
      <h2 class="block-title"><?= __('app.categories_home'); ?></h2>
      <div class="article-text">
        <?php foreach ($sections as $cat) : ?>
          <div class="news-item"><a href="<?= urlDir($cat['path'], !empty($preview) ? 'preview' : 'static'); ?>"><?= htmlEncode($cat['title']); ?></a></div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Правая: последние факты -->
    <aside class="col">
      <?php if ($items) : ?>
        <h2 class="block-title"><?= __('app.latest_facts'); ?></h2>
        <div class="article-text">
          <?php foreach (array_slice($items, 0, 5) as $item) :
            $dir  = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
            $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], $mod);
            ?>
            <div class="news-item"><a href="<?= $path; ?>"><?= htmlEncode($item['item_title']); ?></a></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </aside>
  </div>

  <?php if ($didYouKnow) : ?>
    <section class="portal-dyk">
      <h2 class="block-title"><?= __('app.did_you_know'); ?></h2>
      <div class="portal-dyk__list">
        <?php foreach ($didYouKnow as $dyk) :
          $dir = preg_split('/(@)/', (string)($dyk['facet_list'] ?? ''));
          $p = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$dyk['item_slug'], $mod);
          ?>
          <details class="home-didyouknow__item">
            <summary><?= htmlEncode($dyk['item_title']); ?></summary>
            <div class="home-didyouknow__answer"><?= htmlEncode(Parser::noHTML($dyk['item_content'], 200)); ?></div>
            <a class="more" href="<?= $p; ?>"><?= __('app.read_more'); ?> →</a>
          </details>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</main>

<?= insert('/site/layouts/footer', ['preview' => !empty($preview)]); ?>