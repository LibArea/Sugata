<main class="content">
	<nav class="nav">
		<?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
	</nav>

	<h1 class="title"><?= __('app.password'); ?></h1>

	<form class="max-w-md mt1" action="<?= url('setting.edit.security', method: 'post'); ?>" method="post">
		<?= $container->csrf()->field(); ?>

		<div class="form-field-group">
			<label for="password"><strong><?= __('app.old_password'); ?></strong></label>
			<input id="password" name="password" type="password" required="">
			<span class="showPassword"><svg class="icon">
					<use xlink:href="#eye"></use>
				</svg></span>
		</div>

		<div class="form-field-group">
			<label for="password2"><strong><?= __('app.new_password'); ?></strong></label>
			<input id="password2" name="password2" type="password" required="">
			<div class="hint">8 - 32 <?= __('app.characters'); ?></div>
		</div>

		<div class="form-field-group">
			<label for="password3"><strong><?= __('app.confirm_password'); ?></strong></label>
			<input id="password3" name="password3" type="password" required="">
		</div>

		<input type="hidden" name="nickname" id="nickname" value="">
		<div class="form-actions">
			<?= Html::sumbit(__('app.edit')); ?>
		</div>
	</form>
</main>