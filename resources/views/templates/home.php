<?= insert('/templates/header', ['meta' => Meta::home()]); ?>

<main class="content">

  <div class="item-categories">
    <?php foreach (config('general', 'categories') as $cat) : ?>
      <div class="categories-telo">
        <a class="text-xl block" href="<?= urlDir($cat['path'], 'static'); ?>">
          <?= $cat['title']; ?>
        </a>
        <?php if (!empty($cat['sub'])) : ?>
          <div class="flex gap">
            <?php foreach ($cat['sub'] as $sub) : ?>
              <a class="text-sm black" href="<?= urlDir($sub['path'], 'static'); ?>">
                <?= $sub['title']; ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($cat['help'])) : ?>
          <div class="text-sm gray-600 mb-none"><?= $cat['help']; ?>...</div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <h2 class="uppercase-box"><?= __('app.latest_facts'); ?></h2>

  <?php foreach ($items as $item) :
    $dir = preg_split('/(@)/', (string)$item['facet_list'] ?? false);
    $path = '/' . $dir[2] . '/' .  $item['item_slug'] . '.html';
  ?>
    <article id="<?= $item['item_id']; ?>">
      <div class="fact_telo">
        <h3 class="title">
          <a class="title-fact" href="<?= $path; ?>">
            <?= htmlEncode($item['item_title']); ?>
          </a>
        </h3>

        <?php if ($img = Parser::miniature($item['item_content'])) : ?>

          <img alt="<?= htmlEncode($item['item_title']); ?>" class="miniature" src="<?= $img; ?>">

          <?php $arr = Parser::cut($item['item_content']);
          echo markdown($arr['content']); ?>

        <?php else : ?>

          <?php $arr = Parser::cut($item['item_content']);
          echo markdown($arr['content']); ?>

        <?php endif; ?>

      </div>
      <div class="fact_footer">
        <?= HTML::facetDir($item['facet_list'], 'static'); ?>

        <span class="lowercase"><?= langDate($item['item_date']); ?></span>
      </div>
    </article>
  <?php endforeach; ?>

</main>

<?= insert('/templates/footer'); ?>