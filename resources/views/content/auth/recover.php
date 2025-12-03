<main>
  <h1><?= __('app.password_recovery'); ?></h1>
  <form class="form max-w-sm" action="<?= url('recover.send', method: 'post'); ?>" method="post">
    <?= $container->csrf()->field(); ?>

    <fieldset>
      <input type="email" required="" placeholder="<?= __('app.email'); ?>"  name="email">
    </fieldset>

    <?= insert('/_block/form/captcha'); ?>

    <div class="mt20">
      <?= Html::sumbit(__('app.reset')); ?>
      <?php if (config('general', 'invite') == false) : ?>
        <span class="mr5 ml15 text-sm"><a href="<?= url('register'); ?>"><?= __('app.registration'); ?></a></span>
      <?php endif; ?>
      <span class="mr5 ml15 text-sm"><a href="<?= url('login'); ?>"><?= __('app.sign_in'); ?></a></span>
    </div>
  </form><div class="mt20">
  <p><?= __('app.agree_rules'); ?>.</p>
  <p><?= __('help.recover_info'); ?></p></div>
</main>