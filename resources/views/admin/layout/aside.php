<?php if ($container->user()->active()) : ?>
	<aside class="mb-none">
	  <?= insert('/shared/navigation/menu', ['sheet' => $sheet, 'itemsMenu' => config('general', 'menu-admin')]); ?>
	  <div class="alert alert-info mb-none"><?= __('app.auth_yes'); ?></div>
	</aside>
<?php else : ?>

<?php endif; ?>