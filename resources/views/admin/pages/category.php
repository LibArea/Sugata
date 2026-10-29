<?php $category = $data['category'];  ?>

<main class="content">
  <nav class="nav">
    <?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
  </nav>

  <?= insert('/shared/navigation/breadcrumbs', ['list' => $data['breadcrumb']]); ?>

  <h1 class="title">
    <?= $category['facet_title']; ?>
    <?php if ($container->user()->admin()) : ?>
      <a class="gray" href="<?= url('facet.form.edit', ['type' => 'category', 'id' => $category['facet_id']]); ?>">
        <svg class="icon"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
      </a>
    <?php endif; ?>
  </h1>

  <?php if ($data['childrens']) : ?>
    <div class="item-categories">
      <?php foreach ($data['childrens'] as $lt) : ?>
        <div class="categories-telo">
          <a href="<?= urlDir($lt['facet_path']); ?>"><?= $lt['facet_title']; ?></a>
          <sup class="gray"><?= $lt['facet_count']; ?></sup>
          <?php if ($container->user()->admin()) : ?>
            <a class="ml5 gray" href="<?= url('facet.form.edit', ['type' => 'category', 'id' => $lt['facet_id']]); ?>">
              <sup><svg class="icon"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg></sup>
            </a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($data['low_matching'] && $data['grouping'] == 'dir' && !$data['geo']) : ?>
    <div class="flex items-center gap mb1">
      <span class="black"><?= __('app.related'); ?>:</span>
      <?php foreach ($data['low_matching'] as $rl) : ?>
        <a class="black" href="<?= urlDir($rl['facet_path']); ?>">@<?= $rl['value']; ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($data['items'])) : ?>
    <ol class="stories">
      <?php foreach ($data['items'] as $item) :
        $vDir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
        $vPath = trim($vDir[2] ?? '', '/');
        ?>
        <li class="story" id="<?= $item['item_id']; ?>">
          <div class="story_liner">
            <div class="link"><a href="<?= urlItem($vPath, $item['item_slug'], 'preview'); ?>"><?= htmlEncode($item['item_title']); ?></a>
              <?php if (!$item['item_published']) : ?>
                <span class="red text-sm"><?= __('app.not_published'); ?></span>
              <?php endif; ?>
            </div>
            <div class="story_content">
              <?php $arr = Parser::cut($item['item_content']); echo markdown($arr['content'], 'text'); ?>
            </div>
            <div class="byline">
              <?= HTML::facetDir($item['facet_list']); ?>
              <span class="divider">·</span>
              <span class="lowercase"><?= langDate($item['item_date']); ?></span>
              <span class="divider">·</span>
              <span class="brown"><?= $item['login']; ?></span>
              <?php if ($container->access()->author('item', $item) === true) : ?>
                <a class="ml5 gray" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
                  <svg class="icon text-sm"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php else : ?>
    <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
  <?php endif; ?>

  <?= Html::pagination($data['pNum'], $data['pagesCount'], false, '/'); ?>
</main>