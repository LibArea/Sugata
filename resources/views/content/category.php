<?php $category = $data['category'];  ?>

<main>
  <div class="nav-bar">
    <ul class="nav scroll-menu">
      <?= insert('/_block/navigation/nav', ['sheet' => $data['sheet']]); ?>
    </ul>
  </div>

  <div class="item-cat wrap">
    <?= insert('/_block/navigation/breadcrumbs', ['list' => $data['breadcrumb']]); ?>

    <h1 class="title">
      <?= $category['facet_title']; ?>
      <?php if ($container->user()->admin()) : ?>

        <a class="gray-600" href="<?= url('facet.form.edit', ['type' => 'category', 'id' => $category['facet_id']]); ?>">
          <svg class="icon">
            <use xlink:href="/assets/svg/icons.svg#edit"></use>
          </svg>
        </a>

      <?php endif; ?>
    </h1>
  </div>

  <?php if ($data['childrens']) : ?>
    <div class="item-categories">

      <?php foreach ($data['childrens'] as $lt) : ?>
        <div>
          <a class="text-xl" href="<?= urlDir($lt['facet_path']); ?>">
            <?= $lt['facet_title']; ?>
          </a>

          <sup class="gray-600"><?= $lt['facet_count']; ?></sup>

          <?php if ($container->user()->admin()) : ?>
            <a class="ml5 gray-600" href="<?= url('facet.form.edit', ['type' => 'category', 'id' => $lt['facet_id']]); ?>">
              <sup><svg class="icon">
                  <use xlink:href="/assets/svg/icons.svg#edit"></use>
                </svg></sup>
            </a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($data['low_matching']) : ?>
    <?php if ($data['grouping'] == 'dir' && !$data['geo']) : ?>
      <div class="flex mb20 mt10 gap items-center">
        <div class="gray text-lg bg-yellow p5"> <?= __('app.related'); ?>:</div>
        <div class="flex gap-lg items-center">
          <?php foreach ($data['low_matching'] as $rl) : ?>

            <a class="text-lg black" href="<?= urlDir($rl['facet_path']); ?>">
              @<?= $rl['value']; ?>
            </a>

          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>


  <div>
    <?php if (!empty($data['items'])) : ?>

      <?php foreach ($data['items'] as $item) : ?>
        <article id="<?= $item['item_id']; ?>">
          <div class="fact_telo">
            <h3 class="title">
              <a class="title-fact" href="<?= url('view', ['id' => $item['item_id']]); ?>">
                <?= htmlEncode($item['item_title']); ?>
              </a>
              <?php if ($container->access()->author('item', $item) === true) : ?>
                <sup>
                  <a class="ml10 gray-600" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
                    <svg class="icon text-sm">
                      <use xlink:href="/assets/svg/icons.svg#edit"></use>
                    </svg>
                  </a>
                </sup>
              <?php endif; ?>
              <?php if (!$item['item_published']) : ?>
                <sup class="red text-sm">
                  <?= __('app.not_published'); ?>
                </sup>
              <?php endif; ?>
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
            <?= HTML::facetDir($item['facet_list']); ?>

            <span class="lowercase mr15"><?= langDate($item['item_date']); ?></span>

            <span class="lowercase brown"><?= $item['login']; ?></span>
          </div>
        </article>
      <?php endforeach; ?>

    <?php else : ?>
      <?= insert('/_block/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
    <?php endif; ?>

    <?= Html::pagination($data['pNum'], $data['pagesCount'], false, '/'); ?>
  </div>
</main>