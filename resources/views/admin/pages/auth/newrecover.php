<main>
  <h1 class="uppercase-box"><?= __('app.password_recovery'); ?></h1>
  <form class="mb20" action="<?= url('new.pass', method: 'post'); ?>" method="post">
    <?= $container->csrf()->field(); ?>
    <fieldset>
      <label for="password"><?= __('app.new_password'); ?></label>
      <input id="password" type="password" name="password">
      <span class="showPassword"><svg class="icon">
          <use xlink:href="/assets/svg/icons.svg#eye"></use>
        </svg></span>
    </fieldset>
    <p>
      <input type="hidden" name="code" id="code" value="<?= $data['code']; ?>">
      <input type="hidden" name="user_id" id="user_id" value="<?= $data['user_id']; ?>">
      <?= Html::sumbit(__('app.reset')); ?>
      <?php if (config('general', 'invite') == false) : ?>
        <span class="mr5 ml5 text-sm"><a href="<?= url('register'); ?>"><?= __('app.registration'); ?></a></span>
      <?php endif; ?>
    </p>
  </form>
</main>