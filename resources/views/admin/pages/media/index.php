<main class="content">
  <nav class="nav">
    <?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
  </nav>

  <div class="flex justify-between items-center mb20">
    <h1 class="title"><?= __('app.media'); ?></h1>
  </div>

  <div class="hint mb20"><?= __('app.media_screen_help'); ?></div>

  <?php if (!empty($data['rows'])) : ?>
    <form action="<?= url('media.save', method: 'post'); ?>" method="post">
      <?= $container->csrf()->field(); ?>

      <table class="data">
        <thead>
          <tr>
            <th></th>
            <th><?= __('app.media_path'); ?></th>
            <th><?= __('app.media_caption_label'); ?></th>
            <th><?= __('app.media_author_label'); ?></th>
            <th><?= __('app.media_source_label'); ?></th>
            <th><?= __('app.media_license_label'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data['rows'] as $row) : ?>
            <tr>
              <td>
                <img src="<?= htmlEncode($row['path']); ?>" alt="" style="width:64px;height:44px;object-fit:cover">
              </td>
              <td>
                <input type="hidden" name="path[]" value="<?= htmlEncode($row['path']); ?>">
                <input type="hidden" name="media_id[]" value="<?= (int)$row['media_id']; ?>">
                <input type="hidden" name="alt[]" value="<?= htmlEncode($row['alt'] ?? ''); ?>">
                <div class="hint" style="word-break:break-all"><?= htmlEncode($row['path']); ?></div>
              </td>
              <td>
                <input type="text" name="caption[]" value="<?= htmlEncode($row['caption'] ?? ''); ?>" style="width:180px">
              </td>
              <td>
                <input type="text" name="author[]" value="<?= htmlEncode($row['author'] ?? ''); ?>" style="width:160px">
              </td>
              <td>
                <input type="text" name="source[]" value="<?= htmlEncode($row['source'] ?? ''); ?>" style="width:180px">
              </td>
              <td>
                <input type="text" name="license[]" value="<?= htmlEncode($row['license'] ?? ''); ?>" style="width:140px">
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="form-actions">
        <?= Html::sumbit(__('app.save')); ?>
      </div>
    </form>
  <?php else : ?>
    <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
  <?php endif; ?>
</main>
