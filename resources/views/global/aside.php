<?php if ($container->user()->active()) : ?>
	<aside>
		<?= insert('/_block/navigation/menu', ['sheet' => $sheet, 'itemsMenu' => config('general', 'menu-admin')]); ?>
	</aside>
<?php else : ?>

<?php endif; ?>