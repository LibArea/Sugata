<main>

  <div class="nav-bar">
    <ul class="nav scroll-menu">
      <?= insert('/_block/navigation/nav', ['sheet' => $data['sheet']]); ?>
    </ul>
  </div>

  <a class="right" href="<?= url('setting.security'); ?>"><?= __('app.password'); ?></a>

  <h1 class="title"><?= __('app.setting'); ?></h1>



  <form class="max-w-md mt20" action="<?= url('setting.edit.profile', method: 'post'); ?>" method="post">
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