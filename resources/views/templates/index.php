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

  <h2 class="uppercase-box"><?= __('app.facts'); ?></h2>

  <?php foreach ($items as $item) : ?>
    <article id="<?= $item['item_id']; ?>">
      <h3 class="title">
        <a class="title-fact" href="">
          <?= $item['item_title']; ?>
        </a>
      </h3>

      <?php $arr = \App\Content\Parser\Content::cut($item['item_content']);
      echo markdown($arr['content']); ?>

      <a class="read_more" href=""><?= __('app.read_more'); ?> →</a>
      <div class="fact_footer">
        <?= HTML::facetDir($item['facet_list']); ?>

        <span class="lowercase"><?= langDate($item['item_date']); ?></span>
      </div>
    </article>
  <?php endforeach; ?>
</main>

<?= insert('/templates/footer'); ?>