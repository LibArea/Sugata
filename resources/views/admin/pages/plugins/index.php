<main class="content">
  <div class="flex justify-between items-center mb20">
    <h1 class="title"><?= __('app.plugins'); ?></h1>
  </div>

  <div class="hint mb20">
    <?= __('app.plugins_help'); ?>
    <?php if (!empty($data['config_path'])) : ?>
      <br><small><?= __('app.config_path'); ?>: <code><?= htmlEncode($data['config_path']); ?></code></small>
    <?php endif; ?>
  </div>

  <?php if (!empty($data['hooks'])) : ?>
    <h2 class="title" style="font-size:18px"><?= __('app.hooks_available'); ?></h2>
    <table class="data mb20">
      <tbody>
        <?php foreach ($data['hooks'] as $hook => $desc) : ?>
          <tr>
            <td style="white-space:nowrap"><code><?= htmlEncode($hook); ?></code></td>
            <td><?= htmlEncode($desc); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <?php if (!empty($data['plugins'])) : ?>
    <table class="data">
      <thead>
        <tr>
          <th><?= __('app.title'); ?></th>
          <th><?= __('app.plugin_hooks_count'); ?></th>
          <th><?= __('app.plugin_active'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data['plugins'] as $plugin) : ?>
          <tr>
            <td>
              <?= htmlEncode($plugin['title']); ?>
              <?php if (!empty($plugin['description'])) : ?>
                <div class="hint"><?= htmlEncode($plugin['description']); ?></div>
              <?php endif; ?>
              <div class="hint">
                <code><?= htmlEncode($plugin['name']); ?></code>
                <?php if (!empty($plugin['version'])) : ?> · v<?= htmlEncode($plugin['version']); ?><?php endif; ?>
                <?php if (!empty($plugin['author'])) : ?> · <?= htmlEncode($plugin['author']); ?><?php endif; ?>
              </div>
            </td>
            <td><?= (int)$plugin['hooks_count']; ?></td>
            <td><?= $plugin['active'] ? __('app.yes') : __('app.no'); ?></td>
            <td>
              <form action="<?= url('plugin.toggle', method: 'post'); ?>" method="post">
                <?= $container->csrf()->field(); ?>
                <input type="hidden" name="plugin_name" value="<?= htmlEncode($plugin['name']); ?>">
                <button class="btn" type="submit">
                  <?= $plugin['active'] ? __('app.plugins_disable') : __('app.plugins_enable'); ?>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else : ?>
    <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
  <?php endif; ?>
</main>