<main>
  <h1 class="uppercase-box"><?= __('app.password_recovery'); ?></h1>
  <form class="mb20" action="<?= url('recover.send', method: 'post'); ?>" method="post">
    <?= $container->csrf()->field(); ?>

    <fieldset>
      <input type="email" required="" placeholder="<?= __('app.email'); ?>"  name="email">
    </fieldset>

    <?= insert('/_block/form/captcha'); ?>

    <div class="mt20">
      <?= Html::sumbit(__('app.reset')); ?>
    </div>
  </form>
</main>