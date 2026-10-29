<main class="content">
  <?php $item = $data['item']; ?>
  <div class="flex justify-between items-center mb1">
    <div>
      <p class="hint">ID <?= $item['item_id']; ?> · <a href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>"><?= htmlEncode($item['item_title']); ?></a></p>
      <h1 class="title"><?= __('app.item_history'); ?></h1>
    </div>
    <a class="btn" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>"><?= __('app.edit'); ?></a>
  </div>

  <?php if ($data['versions']) : ?>
    <table class="data zebra">
      <thead>
        <tr>
          <th><?= __('app.version'); ?></th>
          <th><?= __('app.item_date'); ?></th>
          <th><?= __('app.author'); ?></th>
          <th>SLUG</th>
          <th><?= __('app.comment'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php
          $versions = array_values($data['versions']);
          $vc = count($versions);
        ?>
        <?php foreach ($versions as $i => $v) : ?>
          <?php $prev = $versions[$i + 1] ?? null; ?>
          <tr>
            <td><b>v<?= (int)$v['version']; ?></b></td>
            <td><?= langDate($v['created_at']); ?></td>
            <td><?= htmlEncode($v['user_login'] ?? '—'); ?></td>
            <td><code><?= htmlEncode($v['item_slug']); ?></code></td>
            <td class="gray"><?= htmlEncode($v['comment'] ?: '—'); ?></td>
            <td>
              <div class="flex gap items-center">
                <a class="btn" href="<?= url('item.history.show', ['id' => $item['item_id'], 'version' => $v['version']]); ?>"><?= __('app.view'); ?></a>
                <?php if ($prev) : ?>
                  <a class="btn" href="<?= url('item.history.diff', ['id' => $item['item_id'], 'from' => $v['version'], 'to' => $prev['version']]); ?>"><?= __('app.diff_prev'); ?></a>
                <?php endif; ?>
                <form class="inline" method="post" action="<?= url('item.rollback', ['id' => $item['item_id'], 'version' => $v['version']], method: 'post'); ?>" onsubmit="return confirm('<?= __('app.rollback_confirm'); ?>');">
                  <?= $container->csrf()->field(); ?>
                  <button type="submit" class="btn"><?= __('app.rollback'); ?></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="mt1 gray"><?= __('app.version_detail_note'); ?></div>
  <?php else : ?>
    <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_history'), 'icon' => 'info']); ?>
  <?php endif; ?>
</main>