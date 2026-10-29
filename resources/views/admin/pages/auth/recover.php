<main>
    <h1><?= __('app.password_recovery'); ?></h1>

    <p class="hint"><?= __('app.recover_info'); ?></p>

    <form action="<?= url('recover.send', method: 'post'); ?>" method="post">
      <?= $container->csrf()->field(); ?>

      <div class="form-field-group">
        <label for="recover-email"><strong><?= __('app.email'); ?></strong></label>
        <input type="email" id="recover-email" name="email" required autofocus class="form-input-wide" placeholder="name@example.com">
      </div>

      <?= insert('/shared/form/captcha'); ?>

      <div class="form-actions">
        <?= Html::sumbit(__('app.reset')); ?>
      </div>
    </form>
</main>