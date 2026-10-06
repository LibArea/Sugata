<main class="admin-main admin-list-page">

  <?php if ($container->user()->active()) : ?>

    <header class="admin-page-heading">
      <h1><?= __('app.facts'); ?></h1>
      <a class="btn btn-primary admin-create-link" href="/mod/admin/add/item"><?= __('app.add_fact'); ?></a>
    </header>

    <div class="nav-bar admin-nav-bar">
      <ul class="nav scroll-menu admin-nav">
        <?= insert('/_block/navigation/nav', ['sheet' => $data['sheet']]); ?>
      </ul>
    </div>

    <div class="nav-bar admin-nav-bar">
      <ul class="nav scroll-menu admin-nav admin-nav--filters">
        <?php
        $list =  [
          [
            'url'   => url('facts', ['type' => 'my']),
            'title' => 'app.my_facts',
            'id'    => 'my',
          ],
          [
            'url'   => url('facts', ['type' => 'moderation']),
            'title' => 'app.moderation',
            'id'    => 'moderation',
            'tl'     => 10,
          ],
          [
            'url'   => url('facts', ['type' => 'all']),
            'title' => 'app.all_facts',
            'id'    => 'all',
          ],
          [
            'url'   => url('facts', ['type' => 'page']),
            'title' => 'app.pages',
            'id'    => 'apage',
            'tl'     => 10,
          ],
        ];
        ?>

        <?php foreach ($list as $key => $item) :
          $tl = $item['tl'] ?? 0; ?>
          <?php if ($container->user()->tl() >= $tl) : ?>
            <?php if (is_current($item['url'])) : ?>
              <li class="active">
                <?php if (!empty($item['icon'])) : ?><i class="text-sm <?= $item['icon']; ?>"></i><?php endif; ?>
                <?= __($item['title']); ?>
              </li>
            <?php else : ?>
              <li<?php if (!empty($item['css'])) : ?> class="<?= $item['css']; ?>" <?php endif; ?>>
                <a href="<?= $item['url']; ?>">
                  <?php if (!empty($item['icon'])) : ?><i class="text-sm <?= $item['icon']; ?>"></i><?php endif; ?>
                  <?= __($item['title']); ?></a></li>
              <?php endif; ?>
            <?php endif; ?>
          <?php endforeach; ?>
      </ul>
    </div>

    <div>
      <?php if (!empty($data['items'])) : ?>

        <?php foreach ($data['items'] as $item) : ?>
          <article class="admin-item-card" id="<?= $item['item_id']; ?>">
            <div class="fact_telo">
              <h3 class="title admin-item-card__title">
                <a class="title-fact" href="<?= url('view', ['id' => $item['item_id']]); ?>">
                  <?= htmlEncode($item['item_title']); ?>
                </a>
                <?php if ($container->access()->author('item', $item) === true) : ?>
                  <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
                    <svg class="icon text-sm" aria-hidden="true">
                      <use xlink:href="/assets/svg/icons.svg#edit"></use>
                    </svg>
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
        <?= insert('/_block/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
      <?php endif; ?>

      <?= Html::pagination($data['pNum'], $data['pagesCount'], false, $data['paginationUrl'] ?? '/mod/admin/facts/all'); ?>
    </div>

  <?php else : ?>

    <h1 class="uppercase-box"><?= __('app.authorization'); ?></h1>

    <form class="mb20" action="<?= config('meta', 'url'); ?><?= url('authorization', method: 'post'); ?>" method="post">
      <?= $container->csrf()->field(); ?>

      <fieldset class="max-w-sm mb-max-w-full">
        <input class="w-100" name="email" type="email" placeholder="<?= __('app.email'); ?>" required="">
      </fieldset>

      <fieldset class="max-w-sm mb-max-w-full">
        <input class="w-100" id="password" name="password" type="password" placeholder="<?= __('app.password'); ?>" required="">
        <span class="showPassword">
          <svg class="icon">
            <use xlink:href="/assets/svg/icons.svg#eye"></use>
          </svg>
        </span>
      </fieldset>

      <fieldset class="flex gap-sm gray">
        <input id="rememberme" name="rememberme" type="checkbox" value="1">
        <label class="m0" for="rememberme"><?= __('app.remember_me'); ?></label>
      </fieldset>

      <?= Html::sumbit(__('app.sign_in')); ?>

      <a class="ml20 text-sm" href="<?= url('recover'); ?>"><?= __('app.forgot_password'); ?>?</a>
    </form>

  <?php endif; ?>

</main>