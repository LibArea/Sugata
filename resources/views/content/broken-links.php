<main>
  <div class="nav-bar">
    <ul class="nav scroll-menu">
      <?= insert('/_block/navigation/nav', ['sheet' => $data['sheet']]); ?>
    </ul>
  </div>

  <div class="flex justify-between items-center mb20">
    <h1 class="title"><?= __('app.broken_links'); ?></h1>
    <div class="flex gap">
      <a class="btn btn-primary" href="<?= url('broken.links.check'); ?>"><?= __('app.broken_links_check'); ?></a>
    </div>
  </div>

  <div class="mb20 gray-600">
    <?= __('app.broken_links_total'); ?>: <b><?= $data['total']; ?></b>
  </div>

  <?php if ($data['links']) : ?>
    <table>
      <thead>
        <tr>
          <th><?= __('app.article'); ?></th>
          <th><?= __('app.broken_word'); ?></th>
          <th><?= __('app.broken_link'); ?></th>
          <th><?= __('app.broken_found_at'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data['links'] as $link) : ?>
          <tr>
            <td>
              <a href="<?= url('item.form.edit', ['id' => $link['item_id']]); ?>"><?= htmlEncode($link['article_title']); ?></a>
            </td>
            <td><?= htmlEncode($link['word'] ?: '—'); ?></td>
            <td><code><?= htmlEncode($link['link']); ?></code></td>
            <td><?= $link['created_at'] ? langDate($link['created_at']) : '—'; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?= Html::pagination($data['pNum'], $data['pagesCount'], false, '/mod/admin/broken-links'); ?>
  <?php else : ?>
    <?= insert('/_block/no-content', ['type' => 'small', 'text' => __('app.broken_links_none'), 'icon' => 'info']); ?>
  <?php endif; ?>
</main>