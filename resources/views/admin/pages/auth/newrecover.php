<main>
    <h1><?= __('app.password_recovery'); ?></h1>

    <p class="hint"><?= __('app.recover_info'); ?></p>

    <form action="<?= url('new.pass', method: 'post'); ?>" method="post">
      <?= $container->csrf()->field(); ?>

      <div class="form-field-group">
        <label for="password"><strong><?= __('app.new_password'); ?></strong></label>
        <input id="password" type="password" name="password" required minlength="8" maxlength="32" class="form-input-wide">
        <div class="hint">8 - 32 <?= __('app.characters'); ?></div>
      </div>

      <input type="hidden" name="code" id="code" value="<?= $data['code']; ?>">
      <input type="hidden" name="user_id" id="user_id" value="<?= $data['user_id']; ?>">

      <div class="form-actions">
        <?= Html::sumbit(__('app.reset')); ?>
        <?php if (config('general', 'invite') == false) : ?>
          <a class="ml5 text-sm" href="<?= url('register'); ?>"><?= __('app.registration'); ?></a>
        <?php endif; ?>
      </div>
    </form>
</main>