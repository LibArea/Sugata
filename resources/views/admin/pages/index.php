<main class="admin-main admin-list-page">

  <header class="admin-page-heading">
    <h1><?= __('app.facts'); ?></h1>
    <a class="btn btn-primary admin-create-link" href="<?= url('item.form.add'); ?>"><?= __('app.add_fact'); ?></a>
  </header>

  <div class="nav-bar admin-nav-bar">
    <ul class="nav scroll-menu admin-nav">
      <?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
    </ul>
  </div>

  <div class="nav-bar admin-nav-bar">
    <ul class="nav scroll-menu admin-nav admin-nav--filters">
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
        <?php if ($f['active']) : ?>
          <li class="active"><?= __($f['title']); ?></li>
        <?php else : ?>
          <li><a href="<?= url('facts', ['type' => $f['type']]); ?>"><?= __($f['title']); ?></a></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  </div>

  <div>
    <?php if (!empty($data['items'])) : ?>

      <?php foreach ($data['items'] as $item) : ?>
        <article class="admin-item-card" id="<?= $item['item_id']; ?>">
          <div class="fact_telo">
            <?php
              $vDir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
              $vPath = trim($vDir[2] ?? '', '/');
              ?>
              <h3 class="title admin-item-card__title">
                <a class="title-fact" href="<?= urlItem($vPath, $item['item_slug'], 'preview'); ?>">
                  <?= htmlEncode($item['item_title']); ?>
                </a>
              <?php if ($container->access()->author('item', $item) === true) : ?>
                <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
                  <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
                </a>
              <?php endif; ?>
              <?php if (!$item['item_published']) : ?>
                <span class="admin-status">
                  <?= __('app.not_published'); ?>
                </span>
              <?php endif; ?>
            </h3>

            <?php if ($img = Parser::miniature($item['item_content'])) : ?>

              <img alt="<?= htmlEncode($item['item_title']); ?>" class="miniature" src="<?= $img; ?>">

              <?php $arr = Parser::cut($item['item_content']);
              echo markdown($arr['content'], 'mini'); ?>

            <?php else : ?>

              <?php $arr = Parser::cut($item['item_content']);
              echo markdown($arr['content'], 'mini'); ?>

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
      <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
    <?php endif; ?>

    <?= Html::pagination($data['pNum'], $data['pagesCount'], false, $data['paginationUrl'] ?? '/'); ?>
  </div>
</main>