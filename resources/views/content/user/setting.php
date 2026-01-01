<?= insert('/global/aside', ['sheet' => $data['sheet']]); ?>

<main>
  <?= insert('/_block/navigation/breadcrumbs', [
    'list' => [
      [
        'name' => __('app.home'),
        'link' => url('homepage')
      ],
      [
        'name' => __('app.setting'),
        'link' => ''
      ],
    ],
    'sheet' =>  $data['sheet']
  ]);
  ?>

  <?= insert('/content/user/nav'); ?>

  <form class="max-w-md" action="<?= url('setting.edit.profile', method: 'post'); ?>" method="post">
    <?= $container->csrf()->field(); ?>

    <fieldset>
      <div class="form-label input-label"><label><?= __('app.nickname'); ?></label></div>
      <div class="form-element">
        <input minlength="3" maxlength="11" value="<?= $data['user']['login']; ?>" type="text" name="login">
        <div class="help">3 - 11 <?= __('app.characters'); ?></div>
      </div>
    </fieldset>

    <fieldset>
      <div class="form-label input-label"><label><?= __('app.email'); ?></label></div>
      <div class="form-element">
        <input maxlength="21" value="<?= $data['user']['email']; ?>" type="text" name="email">
      </div>
    </fieldset>

    <fieldset>
      <div class="form-label input-label"><label><?= __('app.website'); ?></label></div>
      <div class="form-element">
        <input minlength="3" value="<?= $data['user']['website']; ?>" type="text" name="website">
      </div>
    </fieldset>

    <fieldset>
      <input type="hidden" name="nickname" id="nickname" value="">
      <?= Html::sumbit(__('app.edit')); ?>
    </fieldset>
  </form>
</main>

