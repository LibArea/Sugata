<main class="content">
  <?php
    $item = $data['item'];
    $from = $data['from'];
    $to   = $data['to'];
    $diff = $data['diff'];
  ?>
  <div class="flex justify-between items-center mb1">
    <div>
      <p class="hint">
        ID <?= $item['item_id']; ?> · <a href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>"><?= htmlEncode($item['item_title']); ?></a>
        · <?= __('app.item_diff'); ?>
      </p>
      <h1 class="title"><?= __('app.item_diff'); ?>: v<?= (int)$from['version']; ?> → v<?= (int)$to['version']; ?></h1>
    </div>
    <div class="flex gap">
      <a class="btn" href="<?= htmlEncode($data['backUrl']); ?>"><?= __('app.back_to_history'); ?></a>
      <a class="btn" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>"><?= __('app.edit'); ?></a>
    </div>
  </div>

  <div class="box mb1">
    <b><?= __('app.version'); ?> v<?= (int)$from['version']; ?></b> —
      <?= langDate($from['created_at']); ?> (<?= htmlEncode($from['user_login'] ?? '—'); ?>)
    &nbsp;·&nbsp;
    <b><?= __('app.version'); ?> v<?= (int)$to['version']; ?></b> —
      <?= langDate($to['created_at']); ?> (<?= htmlEncode($to['user_login'] ?? '—'); ?>)
  </div>

  <?php if ($diff) : ?>
    <div class="box">
      <?php
        $stats = ['+' => 0, '-' => 0];
        foreach ($diff as $line) {
            if ($line['op'] === '+' || $line['op'] === '-') {
                $stats[$line['op']]++;
            }
        }
      ?>
      <div class="text-sm gray mb1">
        +<?= $stats['+']; ?> / −<?= $stats['-']; ?>
      </div>

      <?php foreach ($diff as $line) : ?>
        <?php
          $op   = $line['op'];
          $text = $line['text'];
          $cls  = '';
          if ($op === '+')      $cls = 'green';
          elseif ($op === '-')  $cls = 'red';
        ?>
        <div class="<?= $cls; ?>">
          <span class="gray"><?= $op === '=' ? ' ' : $op; ?></span>
          <span><?= $text === '' ? '&nbsp;' : htmlEncode(ltrim($text)); ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else : ?>
    <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_diff'), 'icon' => 'info']); ?>
  <?php endif; ?>
</main>