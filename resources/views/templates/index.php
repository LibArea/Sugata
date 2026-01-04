<?= insert('/templates/header', ['meta' => $meta]); ?>

<main class="content">
  <?= insert('/_block/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

  <?php if ($childrens) : ?>
    <div class="categories-telo">
      <?php foreach ($childrens as $lt) : ?>
        <a class="text-xl" href="<?= urlDir($lt['facet_path']); ?>">
          <?= $lt['facet_title']; ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="flex justify-between mb20">
    <h2 class="uppercase-box"><?= $facet['facet_title']; ?></h2>
    <div class="tag-yellow box mb-none"><?= $facet['facet_info']; ?></div>
  </div>

  <?php foreach ($items as $item) : ?>
    <article id="<?= $item['item_id']; ?>">
      <h3 class="title">
        <a class="title-fact" href="/<?= Html::facets_puth($item['facet_list']); ?>/<?= $item['item_slug']; ?>.html">
          <?= $item['item_title']; ?>
        </a>
      </h3>

      <?php if ($img = \App\Content\Parser\Content::miniature($item['item_content'])) : ?>

        <img alt="<?= $item['item_title']; ?>" class="miniature" src="<?= $img; ?>">

        <?php $arr = \App\Content\Parser\Content::cut($item['item_content']);
        echo markdown($arr['content']); ?>

      <?php else : ?>

        <?php $arr = \App\Content\Parser\Content::cut($item['item_content']);
        echo markdown($arr['content']); ?>

      <?php endif; ?>

      <a class="read_more" href="/<?= Html::facets_puth($item['facet_list']); ?>/<?= $item['item_slug']; ?>.html"><?= __('app.read_more'); ?> →</a>
      <div class="fact_footer">
        <?= HTML::facetDir($item['facet_list']); ?>

        <span class="lowercase"><?= langDate($item['item_date']); ?></span>
      </div>
    </article>
  <?php endforeach; ?>
</main>

<?= insert('/templates/footer'); ?>