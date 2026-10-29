<main>
    <h1><?= __('app.registration'); ?></h1>

    <p class="hint"><?= __('auth.login_info'); ?></p>

    <form action="<?= url('authorization', method: 'post'); ?>" method="post">
      <?= $container->csrf()->field(); ?>

      <div class="form-field-group">
        <label for="reg-nickname"><strong><?= __('app.nickname'); ?></strong></label>
        <input type="text" id="reg-nickname" name="nickname" required minlength="3" maxlength="11" class="form-input-wide" placeholder="Nickname">
      </div>

      <div class="form-field-group">
        <label for="reg-email"><strong><?= __('app.email'); ?></strong></label>
        <input type="email" id="reg-email" name="email" required autofocus class="form-input-wide" placeholder="name@example.com">
      </div>

      <div class="form-field-group">
        <label for="reg-password"><strong><?= __('app.password'); ?></strong></label>
        <input type="password" id="reg-password" name="password" required minlength="8" maxlength="32" class="form-input-wide">
      </div>

      <?= insert('/shared/form/captcha'); ?>

      <div class="form-actions">
        <?= Html::sumbit(__('app.registration')); ?>
      </div>
    </form>

    <hr>

    <p>
      <?= __('auth.no_account'); ?> <a href="<?= url('login'); ?>"><?= __('app.sign_in'); ?></a>
    </p>
</main>