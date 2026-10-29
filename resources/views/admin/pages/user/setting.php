<main class="content">
  <nav class="nav">
    <?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
  </nav>

  <h1 class="title"><?= __('app.setting'); ?></h1>

  <form class="max-w-md mt1" action="<?= url('setting.edit.profile', method: 'post'); ?>" method="post">
    <?= $container->csrf()->field(); ?>

    <div class="form-field-group">
      <label for="login"><strong><?= __('app.nickname'); ?></strong></label>
      <input id="login" minlength="3" maxlength="11" value="<?= $data['user']['login']; ?>" type="text" name="login">
      <div class="hint">3 - 11 <?= __('app.characters'); ?></div>
    </div>

    <div class="form-field-group">
      <label for="email"><strong><?= __('app.email'); ?></strong></label>
      <input id="email" maxlength="21" value="<?= $data['user']['email']; ?>" type="text" name="email">
    </div>

    <div class="form-field-group">
      <label for="website"><strong><?= __('app.website'); ?></strong></label>
      <input id="website" minlength="3" value="<?= $data['user']['website']; ?>" type="text" name="website">
    </div>

    <input type="hidden" name="nickname" id="nickname" value="">
    <div class="form-actions">
      <?= Html::sumbit(__('app.edit')); ?>
    </div>
  </form>
</main>