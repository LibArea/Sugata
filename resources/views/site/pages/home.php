<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<?php
$mod       = !empty($preview) ? 'preview' : 'static';
$featured  = $featuredBig ?? ($items[0] ?? null);
$bestFact  = $bestFact ?? null;
$rest      = array_slice($items, 1);
$didYouKnow = $didYouKnow ?? [];
?>

<div class="content wiki-content">
  <main class="wiki-layout__main wiki-home-main">

    <div class="wiki-welcome">
      <h1 class="wiki-welcome__title"><?= __('app.welcome_title'); ?></h1>
      <p class="wiki-welcome__text"><?= __('app.home_description'); ?></p>
    </div>

    <?php if ($bestFact) : ?>
      <section class="wiki-best">
        <?php
        $dir  = preg_split('/(@)/', (string)($bestFact['facet_list'] ?? ''));
        $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$bestFact['item_slug'], $mod);
        ?>
        <div class="wiki-best__wrap">
          <div class="wiki-best__body">
            <div class="wiki-best__label"><?= __('app.best_fact'); ?></div>
            <h2 class="wiki-best__title"><a href="<?= $path; ?>"><?= htmlEncode($bestFact['item_title']); ?></a></h2>

            <?php
            // Первые полные абзацы (до ~800 символов) с сохранением форматирования markdown
            $blocks = preg_split('/\n\s*\n/', (string)$bestFact['item_content']);
            $keep = [];
            $len = 0;
            foreach ($blocks as $b) {
                $clean = trim(preg_replace('/[#>*_\[\]()!-]/u', '', $b)); // грубая оценка длины текста
                if ($len + mb_strlen($clean) > 800) {
                    break;
                }
                $keep[] = $b;
                $len += mb_strlen($clean);
            }
            ?>
            <div class="wiki-best__text"><?= markdown(implode("\n\n", $keep), 'text', !empty($preview)); ?></div>
            <a class="wiki-featured__more" href="<?= $path; ?>"><?= __('app.read_more'); ?> →</a>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <?php if ($featured) : ?>
      <section class="wiki-featured">
        <h2 class="wiki-section-title"><?= __('app.fact_of_the_day'); ?></h2>

        <?php
        $dir  = preg_split('/(@)/', (string)($featured['facet_list'] ?? ''));
        $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$featured['item_slug'], $mod);
        $img  = !empty($featured['item_thumb_img']) ? Img::PATH['thumbs'] . $featured['item_thumb_img'] : Parser::miniature($featured['item_content']);
        ?>

        <article class="wiki-featured__card">
          <?php if ($img) : ?>
            <a class="wiki-featured__thumb" href="<?= $path; ?>">
              <img alt="<?= htmlEncode($featured['item_title']); ?>" src="<?= htmlEncode($img); ?>">
            </a>
          <?php endif; ?>

          <div class="wiki-featured__body">
            <h3><a href="<?= $path; ?>"><?= htmlEncode($featured['item_title']); ?></a></h3>
            <p><?= htmlEncode(Parser::noHTML($featured['item_content'], 400)); ?></p>
            <a class="wiki-featured__more" href="<?= $path; ?>"><?= __('app.read_more'); ?> →</a>
          </div>
        </article>
      </section>
    <?php endif; ?>

    <?php if ($didYouKnow) : ?>
      <section class="wiki-dyk">
        <h2 class="wiki-section-title"><?= __('app.did_you_know'); ?></h2>
        <?php foreach ($didYouKnow as $dyk) :
          $dir  = preg_split('/(@)/', (string)($dyk['facet_list'] ?? ''));
          $p = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$dyk['item_slug'], $mod);
        ?>
          <details class="wiki-faq__item">
            <summary><?= htmlEncode($dyk['item_title']); ?></summary>
            <div class="wiki-faq__answer">
              <?= htmlEncode(Parser::noHTML($dyk['item_content'], 300)); ?>
              <div><a class="wiki-featured__more" href="<?= $p; ?>"><?= __('app.read_more'); ?> →</a></div>
            </div>
          </details>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>

    <section class="wiki-sections">
      <h2 class="wiki-section-title"><?= __('app.categories_home'); ?></h2>
      <?= insert('/site/components/categories-list', ['sections' => $sections ?? [], 'preview' => !empty($preview)]); ?>
    </section>

    <?php if ($rest) : ?>
      <h2 class="wiki-section-title"><?= __('app.latest_facts'); ?></h2>

      <div class="wiki-fact-list">
        <?php foreach ($rest as $item) : ?>
          <?= insert('/site/components/card-fact', ['item' => $item, 'preview' => !empty($preview)]); ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </main>
</div>

<?php if (!empty($preview)) : ?>
  <?= insert('/site/layouts/footer', ['preview' => true]); ?>
<?php else : ?>
  <!--#include virtual="/assets/footer.shtml"-->
<?php endif; ?>