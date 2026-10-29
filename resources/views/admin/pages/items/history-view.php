<main class="content">
  <?php
    $item    = $data['item'];
    $version = $data['version'];
  ?>
  <div class="flex justify-between items-center mb1">
    <div>
      <p class="hint">
        ID <?= $item['item_id']; ?> · <a href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>"><?= htmlEncode($item['item_title']); ?></a>
        · <?= __('app.version'); ?> v<?= (int)$version['version']; ?>
      </p>
      <h1 class="title"><?= __('app.history_view'); ?></h1>
    </div>
    <div class="flex gap">
      <a class="btn" href="<?= htmlEncode($data['backUrl']); ?>"><?= __('app.back_to_history'); ?></a>
      <a class="btn" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>"><?= __('app.edit'); ?></a>
    </div>
  </div>

  <div class="box">
    <div class="mb1">
      <b><?= __('app.title'); ?>:</b> <?= htmlEncode($version['item_title']); ?><br>
      <b>SLUG:</b> <code><?= htmlEncode($version['item_slug']); ?></code><br>
      <b><?= __('app.item_date'); ?>:</b> <?= langDate($version['created_at']); ?><br>
      <b><?= __('app.author'); ?>:</b> <?= htmlEncode($version['user_login'] ?? '—'); ?>
      <?php if (!empty($version['comment'])) : ?>
        <br><b><?= __('app.comment'); ?>:</b> <?= htmlEncode($version['comment']); ?>
      <?php endif; ?>
    </div>
    <hr>
    <div>
      <?= markdown($version['item_content'] ?? '', 'text', true); ?>
    </div>
  </div>

  <div class="mt1">
    <form class="inline" method="post" action="<?= url('item.rollback', ['id' => $item['item_id'], 'version' => $version['version']], method: 'post'); ?>" onsubmit="return confirm('<?= __('app.rollback_confirm'); ?>');">
      <?= $container->csrf()->field(); ?>
      <button type="submit" class="btn"><?= __('app.rollback'); ?></button>
    </form>
  </div>
</main>