<main class="content">
  <div class="flex justify-between items-center">
    <h1 class="title"><?= __('app.facts'); ?></h1>
    <a class="btn" href="<?= url('item.form.add'); ?>"><?= __('app.add_fact'); ?></a>
  </div>

  <nav class="nav">
    <?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
  </nav>

  <nav class="nav br-none">
    <?php
      $filters = [
        ['type' => 'my',         'title' => 'app.my_facts',  'active' => ($data['type'] ?? '') === 'my' || empty($data['type'])],
        ['type' => 'moderation', 'title' => 'app.moderation','active' => ($data['type'] ?? '') === 'moderation'],
        ['type' => 'all',        'title' => 'app.all_facts', 'active' => ($data['type'] ?? '') === 'all'],
        ['type' => 'articles',   'title' => 'app.articles',  'active' => ($data['type'] ?? '') === 'articles'],
        ['type' => 'page',       'title' => 'app.pages',     'active' => ($data['type'] ?? '') === 'page'],
      ];
    ?>
    <?php foreach ($filters as $f) : ?>
      <a class="<?= $f['active'] ? 'active' : ''; ?>" href="<?= url('facts', ['type' => $f['type']]); ?>"><?= __($f['title']); ?></a>
    <?php endforeach; ?>
  </nav>

  <?php if (!empty($data['items'])) : ?>
    <ol class="stories">
      <?php foreach ($data['items'] as $item) :
        $vDir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
        $vPath = trim($vDir[2] ?? '', '/');
        ?>
        <li class="story">
          <div class="story_liner">
            <div class="link"><a href="<?= urlItem($vPath, $item['item_slug'], 'preview'); ?>"><?= htmlEncode($item['item_title']); ?></a>
              <?php if (!$item['item_published']) : ?>
                <span class="red"><?= __('app.not_published'); ?></span>
              <?php endif; ?>
            </div>
            <div class="story_content"><?php $arr = Parser::cut($item['item_content']); echo markdown($arr['content'], 'text'); ?></div>
            <div class="byline">
              <?= HTML::facetDir($item['facet_list']); ?>
              <span class="divider">·</span>
              <span class="lowercase"><?= langDate($item['item_date']); ?></span>
              <span class="divider">·</span>
              <span class="brown"><?= $item['login']; ?></span>
              <?php if ($container->access()->author('item', $item) === true) : ?>
                <a class="ml5 gray" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
                  <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
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

  <?= Html::pagination($data['pNum'], $data['pagesCount'], false, $data['paginationUrl'] ?? '/'); ?>
</main>