<main>
    <h1><?= __('app.password_recovery'); ?></h1>

    <p class="hint"><?= __('app.recover_info'); ?></p>

    <form action="<?= url('new.pass', method: 'post'); ?>" method="post">
      <?= $container->csrf()->field(); ?>

      <div class="form-field-group">
        <label for="new-password"><strong><?= __('app.new_password'); ?></strong></label>
        <input type="password" id="new-password" name="password" required minlength="8" maxlength="32" class="form-input-wide">
        <div class="hint">8 - 32 <?= __('app.characters'); ?></div>
      </div>

      <input type="hidden" name="code" id="code" value="<?= $data['code'] ?? ''; ?>">
      <input type="hidden" name="user_id" id="user_id" value="<?= (int)($data['user_id'] ?? 0); ?>">

      <div class="form-actions">
        <?= Html::sumbit(__('app.reset')); ?>
      </div>
    </form>
</main>