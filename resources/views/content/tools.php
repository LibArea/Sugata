<main>
  <?= insert('/_block/navigation/breadcrumbs-admin', ['sheet' => 'tools']); ?>

  <h1 class="uppercase-box"><?= __('app.tools'); ?> </h1>

  <b><?= __('app.rebuild_resources'); ?></b>

  <fieldset>
    <div class="form-label input-label"><label><?= __('app.rebuild_css_title'); ?></label></div>
    <div class="form-element">
      <a href="<?= url('update.css'); ?>"><button type="submit" name="action" class="btn btn-primary" value="submit"><?= __('app.rebuild'); ?></button></a>
    </div>
  </fieldset>

  <fieldset>
    <div class="form-label input-label"><label><?= __('app.rebuild_title'); ?></label></div>
    <div class="form-element">
      <a href="<?= url('update.path'); ?>"><button type="submit" name="action" class="btn btn-primary" value="submit"><?= __('app.rebuild'); ?></button></a>
    </div>
  </fieldset>


  <fieldset>
    <div class="form-label input-label"><label><?= __('app.search_index'); ?></label></div>
    <div class="form-element">
      <a href=""><button type="submit" name="action" class="btn btn-primary" value="submit"><?= __('app.rebuild'); ?></button></a>
    </div>
  </fieldset>


  <hr>

  <fieldset>
    <div class="form-label input-label"><label><?= __('app.rebuild_all'); ?></label></div>
    <div class="form-element">
      <a href="<?= url('update.all'); ?>"><button type="submit" name="action" class="btn btn-primary" value="submit"><?= __('app.rebuild'); ?></button></a>
    </div>
  </fieldset>

  <fieldset>
    <div class="form-label input-label red"><label><?= __('app.rebuild_dir'); ?></label></div>
    <div class="form-element">
      <a href="<?= url('update.dir'); ?>"><button type="submit" name="action" class="btn btn-primary" value="submit"><?= __('app.rebuild'); ?></button></a>
    </div>
  </fieldset>

  <fieldset>
    <div class="form-label input-label red"><label><?= __('app.rebuild_html'); ?></label></div>
    <div class="form-element">
      <a href="<?= url('update.html'); ?>"><button type="submit" name="action" class="btn btn-primary" value="submit"><?= __('app.rebuild_html'); ?></button></a>
    </div>
  </fieldset>


  <fieldset>
    <div class="form-label input-label red"><label class="red"><?= __('app.deletion_dir'); ?></label></div>
    <div class="form-element">
      <a href="<?= url('deletion.dir'); ?>"><button type="submit" name="action" class="btn btn-primary" value="submit"><?= __('app.delete'); ?></button></a>
    </div>
  </fieldset>


</main>

<?= insert('/global/aside', ['sheet' => $data['sheet']]); ?>