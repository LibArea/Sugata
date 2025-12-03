<fieldset class="form-big">
  <div class="form-label input-label"><label><?= __('app.title'); ?> <strong class="red">*</strong></label></div>
  <div class="form-element">
    <input id="facet_title" minlength="3" name="facet_title" required type="text" minlength="3" maxlength="64" value="">
    <div class="help">3 - 64 <?= __('app.characters'); ?></div>
  </div>
</fieldset>

<fieldset>
  <div class="form-label input-label"><label><?= __('app.slug'); ?> <strong class="red">*</strong></label></div>
  <div class="form-element">
    <input id="facet_slug" name="facet_slug" minlength="3" maxlength="32" required type="text" value="">
    <div class="help">3 - 32 <?= __('app.characters'); ?></div>
  </div>
</fieldset>

<?= Html::sumbit(__('app.add')); ?>