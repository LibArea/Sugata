<main class="content">
  <nav class="nav">
    <?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
  </nav>

  <div class="flex justify-between items-center">
    <h1 class="title"><?= __('app.broken_links'); ?></h1>
    <a class="btn" href="<?= url('broken.links.check'); ?>"><?= __('app.broken_links_check'); ?></a>
  </div>

  <div class="mb1 gray">
    <?= __('app.broken_links_total'); ?>: <b><?= $data['total']; ?></b>
  </div>

  <?php if ($data['links']) : ?>
    <table class="data zebra">
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
            <td>
              <?php if ($link['link']) : ?>
                <code><?= htmlEncode($link['link']); ?></code>
              <?php else : ?>
                <span class="red"><?= __('app.broken_term'); ?></span>
              <?php endif; ?>
            </td>
            <td><?= $link['created_at'] ? langDate($link['created_at']) : '—'; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?= Html::pagination($data['pNum'], $data['pagesCount'], false, '/mod/admin/broken-links'); ?>
  <?php else : ?>
    <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.broken_links_none'), 'icon' => 'info']); ?>
  <?php endif; ?>
</main>