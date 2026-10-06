<?= insert('/templates/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<?php
$mod     = !empty($preview) ? 'preview' : 'static';
$featured = $items[0] ?? null;
$rest     = array_slice($items, 1);
?>

<div class="content wiki-content">
  <main class="wiki-layout__main wiki-home-main">

    <div class="wiki-welcome">
      <h1 class="wiki-welcome__title"><?= __('app.welcome_title'); ?></h1>
      <p class="wiki-welcome__text"><?= __('app.home_description'); ?></p>
    </div>

    <?php if ($featured) : ?>
      <section class="wiki-featured">
        <h2 class="wiki-section-title"><?= __('app.featured_fact'); ?></h2>

        <?php
        $dir  = preg_split('/(@)/', (string)($featured['facet_list'] ?? ''));
        $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$featured['item_slug'], $mod);
        $img  = Parser::miniature($featured['item_content']);
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

    <section class="wiki-sections">
      <h2 class="wiki-section-title"><?= __('app.category'); ?></h2>
      <?= insert('/templates/components/categories-list', ['sections' => $sections ?? [], 'preview' => !empty($preview)]); ?>
    </section>

    <?php if ($rest) : ?>
      <h2 class="wiki-section-title"><?= __('app.latest_facts'); ?></h2>

      <div class="wiki-fact-list">
        <?php foreach ($rest as $item) : ?>
          <?= insert('/templates/components/card-fact', ['item' => $item, 'preview' => !empty($preview)]); ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </main>
</div>

<?php if (!empty($preview)) : ?>
  <?= insert('/templates/footer', ['preview' => true]); ?>
<?php else : ?>
  <!--#include virtual="/assets/footer.shtml"-->
<?php endif; ?>