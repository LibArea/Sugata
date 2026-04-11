<main>
	<div class="nav-bar">
		<ul class="nav scroll-menu">
			<?= insert('/_block/navigation/nav', ['sheet' => $data['sheet']]); ?>
		</ul>
	</div>

	<h1 class="title"><?= __('app.password'); ?></h1>

	<form class="mt20" action="<?= url('setting.edit.security', method: 'post'); ?>" method="post">
		<?= $container->csrf()->field(); ?>
		<fieldset>
			<div class="form-label input-label"><label><?= __('app.old_password'); ?></label></div>
			<div class="form-element">
				<input id="password" name="password" type="password" required="">
				<span class="showPassword"><svg class="icon">
						<use xlink:href="#eye"></use>
					</svg></span>
			</div>
		</fieldset>

		<fieldset>
			<div class="form-label input-label"><label><?= __('app.new_password'); ?></label></div>
			<div class="form-element">
				<input name="password2" type="password" required="">
				<div class="help">8 - 32 <?= __('app.characters'); ?></div>
			</div>
		</fieldset>

		<fieldset>
			<div class="form-label input-label"><label><?= __('app.confirm_password'); ?></label></div>
			<div class="form-element">
				<input name="password3" type="password" required="">
			</div>
		</fieldset>

		<fieldset>
			<input type="hidden" name="nickname" id="nickname" value="">
			<?= Html::sumbit(__('app.edit')); ?>
		</fieldset>
	</form>
</main>