<?php if ($container->user()->active()) : ?>
	<aside>
	  <?= insert('/_block/navigation/menu', ['sheet' => $sheet, 'itemsMenu' => config('general', 'menu-admin')]); ?>
	  <div class="tag-yellow box mb-none center"><?= __('app.auth_yes'); ?></div>
	</aside>
<?php else : ?>

<?php endif; ?>