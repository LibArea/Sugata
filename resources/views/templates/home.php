<?= insert('/templates/header', ['meta' => Meta::home()]); ?>

<main class="content">

  <?= insert('/content/facets/categories'); ?>

  <h2 class="uppercase-box"><?= __('app.latest_facts'); ?></h2>

  <?php foreach ($items as $item) :
    $dir = preg_split('/(@)/', (string)$item['facet_list'] ?? false);
    $path = '/' . $dir[2] . '/' .  $item['item_slug'] . '.html';
  ?>
    <article id="<?= $item['item_id']; ?>">
	   <div class="fact_telo">
      <h3 class="title">
        <a class="title-fact" href="<?= $path; ?>">
          <?= $item['item_title']; ?>
        </a>
      </h3>

      <?php if ($img = Parser::miniature($item['item_content'])) : ?>

        <img alt="<?= $item['item_title']; ?>" class="miniature" src="<?= $img; ?>">

        <?php $arr = Parser::cut($item['item_content']);
        echo markdown($arr['content']); ?>

      <?php else : ?>

        <?php $arr = Parser::cut($item['item_content']);
        echo markdown($arr['content']); ?>

      <?php endif; ?>

      </div>
      <div class="fact_footer">
        <?= HTML::facetDir($item['facet_list']); ?>

        <span class="lowercase"><?= langDate($item['item_date']); ?></span>
      </div>
    </article>
  <?php endforeach; ?>

</main>

<?= insert('/templates/footer'); ?>