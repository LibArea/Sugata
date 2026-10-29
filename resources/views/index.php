<main>
    <h1><?= __('app.authorization'); ?></h1>

    <p class="hint"><?= __('auth.login_info'); ?></p>

    <form action="<?= config('meta', 'url'); ?><?= url('authorization', method: 'post'); ?>" method="post">
      <?= $container->csrf()->field(); ?>

      <div class="form-field-group">
        <label for="login-email"><strong><?= __('app.email'); ?></strong></label>
        <input type="email" id="login-email" name="email" required autofocus class="form-input-wide" placeholder="name@example.com">
      </div>

      <div class="form-field-group">
        <label for="login-password">
          <strong><?= __('app.password'); ?></strong>
          <a href="<?= url('recover'); ?>" class="form-field-hint-inline">(<?= __('app.forgot_password'); ?>?)</a>
        </label>
        <input type="password" id="login-password" name="password" required class="form-input-wide">
      </div>

      <div class="form-field-group">
        <label>
          <input type="checkbox" name="rememberme" value="1">
          <?= __('app.remember_me'); ?>
        </label>
      </div>

      <div class="form-actions">
        <?= Html::sumbit(__('app.sign_in')); ?>
      </div>
    </form>

    <hr>

    <p>
      <?= __('auth.no_account'); ?> <a href="<?= url('register'); ?>"><?= __('auth.registration'); ?></a>
    </p>
</main>