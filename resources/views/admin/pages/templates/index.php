<main class="content">
  <nav class="nav">
    <?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
  </nav>

  <div class="flex justify-between items-center mb20">
    <h1 class="title"><?= __('app.templates'); ?></h1>
    <a class="btn" href="<?= url('template.add'); ?>">+ <?= __('app.template_add'); ?></a>
  </div>

  <div class="hint mb20"><?= __('app.templates_help'); ?></div>

  <?php if (!empty($data['templates'])) : ?>
    <table class="data">
      <thead>
        <tr>
          <th><?= __('app.title'); ?></th>
          <th><?= __('app.template_code'); ?></th>
          <th><?= __('app.template_scope'); ?></th>
          <th><?= __('app.template_fields_count'); ?></th>
          <th><?= __('app.template_instances_count'); ?></th>
          <th><?= __('app.template_active'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data['templates'] as $tpl) : ?>
          <tr>
            <td>
              <a href="<?= url('template.edit', ['id' => (int)$tpl['template_id']]); ?>"><?= htmlEncode($tpl['template_title']); ?></a>
              <?php if (!empty($tpl['template_description'])) : ?>
                <div class="hint"><?= htmlEncode($tpl['template_description']); ?></div>
              <?php endif; ?>
            </td>
            <td><?= htmlEncode($tpl['template_code']); ?></td>
            <td><?= $tpl['template_scope'] === 'inline' ? __('app.template_scope_inline') : __('app.template_scope_infobox'); ?></td>
            <td><?= (int)$tpl['fields_count']; ?></td>
            <td><?= (int)$tpl['instances_count']; ?></td>
            <td><?= !empty($tpl['template_is_active']) ? __('app.yes') : __('app.no'); ?></td>
            <td>
              <div class="flex gap">
                <a class="btn" href="<?= url('template.edit', ['id' => (int)$tpl['template_id']]); ?>"><?= __('app.edit'); ?></a>
                <form action="<?= url('template.delete', method: 'post'); ?>" method="post" onsubmit="return confirm('<?= __('app.template_delete_confirm'); ?>');">
                  <?= $container->csrf()->field(); ?>
                  <input type="hidden" name="template_id" value="<?= (int)$tpl['template_id']; ?>">
                  <button class="btn btn--danger" type="submit"><?= __('app.delete'); ?></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else : ?>
    <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
  <?php endif; ?>
</main>
