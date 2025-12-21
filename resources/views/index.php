<main>

  <?php if ($container->user()->active()) : ?>

    <?= insert('/_block/navigation/breadcrumbs', [
      'list' => [
        [
          'name' => __('app.home'),
          'link' => url('homepage')
        ],
        [
          'name' => __('app.facts'),
          'link' => ''
        ]
      ],
      'sheet' =>  $data['sheet']
    ]);
    ?>

    <h1 class="uppercase-box"><?= __('app.facts'); ?> </h1>

    <div>
      <?php if (!empty($data['items'])) : ?>

        <?php foreach ($data['items'] as $item) : ?>
          <article id="<?= $item['item_id']; ?>">
            <h3 class="title">
              <a class="title-fact" href="<?= url('view', ['id' => $item['item_id']]); ?>">
                <?= $item['item_title']; ?>
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

            <?php $arr = \App\Content\Parser\Content::cut($item['item_content']);
            echo markdown($arr['content']); ?>

            <a class="read_more" href=""><?= __('app.read_more'); ?> →</a>
            <div class="fact_footer">
              <?= HTML::facetDir($item['facet_list']); ?>

              <span class="lowercase"><?= langDate($item['item_date']); ?></span>
            </div>
          </article>
        <?php endforeach; ?>

      <?php else : ?>
        <?= insert('/_block/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
      <?php endif; ?>

      <?= Html::pagination($data['pNum'], $data['pagesCount'], false, '/'); ?>
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
<?= insert('/global/aside', ['sheet' => $data['sheet']]); ?>