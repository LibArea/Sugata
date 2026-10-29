<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<?php
$mod       = !empty($preview) ? 'preview' : 'static';
$didYouKnow = $didYouKnow ?? [];
?>

<main class="portal-home">
  <div class="home-intro">
    <h1 class="home-intro__title"><?= __('app.welcome_title'); ?></h1>
    <p class="hint"><?= __('app.home_description'); ?></p>
  </div>

  <!-- Две колонки под поиском -->
  <div class="portal-cols2">
    <!-- Левая: лучший факт с фото -->
    <section class="portal-col portal-col--best">
      <?php if ($bestFact) :
        $dir = preg_split('/(@)/', (string)($bestFact['facet_list'] ?? ''));
        $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$bestFact['item_slug'], $mod);
        $img = !empty($bestFact['item_thumb_img'])
            ? Img::PATH['thumbs'] . $bestFact['item_thumb_img']
            : Parser::miniature($bestFact['item_content'] ?? '');
        ?>
        <div class="portal-block-title"><b><?= __('app.best_fact'); ?></b></div>
        <article class="portal-best">
          <?php if ($img) : ?>
            <a class="portal-best__thumb" href="<?= $path; ?>">
              <img alt="<?= htmlEncode($bestFact['item_title']); ?>" src="<?= htmlEncode($img); ?>">
            </a>
          <?php endif; ?>
          <div class="portal-best__body">
            <div class="portal-best__title"><a href="<?= $path; ?>"><?= htmlEncode($bestFact['item_title']); ?></a></div>
            <div class="portal-best__excerpt"><?= htmlEncode(Parser::noHTML($bestFact['item_content'], 260)); ?></div>
            <a class="portal-more" href="<?= $path; ?>"><?= __('app.read_more'); ?> →</a>
          </div>
        </article>
      <?php endif; ?>
    </section>

    <!-- Правая: категории фактов -->
    <section class="portal-col portal-col--cats">
      <div class="portal-block-title"><b><?= __('app.categories_home'); ?></b></div>
      <?= insert('/site/components/categories-list', ['sections' => $sections ?? [], 'preview' => !empty($preview)]); ?>
    </section>
  </div>

  <!-- Нижний ряд: Знаете ли вы? + Последние факты -->
  <div class="portal-cols2 portal-cols2--bottom">
    <?php if ($didYouKnow) : ?>
      <section class="portal-col">
        <div class="portal-block-title"><b><?= __('app.did_you_know'); ?></b></div>
        <?php foreach ($didYouKnow as $dyk) :
          $dir = preg_split('/(@)/', (string)($dyk['facet_list'] ?? ''));
          $p = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$dyk['item_slug'], $mod);
          ?>
          <details class="home-didyouknow__item">
            <summary><?= htmlEncode($dyk['item_title']); ?></summary>
            <div class="home-didyouknow__answer"><?= htmlEncode(Parser::noHTML($dyk['item_content'], 200)); ?></div>
          </details>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>

    <?php if ($items) : ?>
      <section class="portal-col">
        <div class="portal-block-title"><b><?= __('app.latest_facts'); ?></b></div>
        <div class="portal-latest">
          <?php foreach (array_slice($items, 0, 3) as $item) :
            $dir  = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
            $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], $mod);
            ?>
            <article class="portal-feature-item">
              <div class="portal-feature-item__title"><a href="<?= $path; ?>"><?= htmlEncode($item['item_title']); ?></a></div>
              <div class="portal-feature-item__excerpt"><?= htmlEncode(Parser::noHTML($item['item_content'], 150)); ?></div>
              <div class="portal-feature-item__meta">
                <?= Html::facetDir($item['facet_list'] ?? '', $mod); ?>
                <span class="divider">·</span>
                <span class="lowercase"><?= langDate($item['item_date']); ?></span>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </div>
</main>

<?php if (!empty($preview)) : ?>
  <?= insert('/site/layouts/footer', ['preview' => true]); ?>
<?php else : ?>
  <!--#include virtual="/assets/footer.shtml"-->
<?php endif; ?>