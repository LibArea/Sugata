<main class="content">
  <nav class="nav">
    <?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
  </nav>

  <div class="flex justify-between items-center">
    <h1 class="title"><?= __('app.sources'); ?></h1>
    <a class="btn" href="<?= url('update.sources'); ?>"><?= __('app.sources_check'); ?></a>
  </div>

  <nav class="nav br-none">
    <a class="<?= $data['tab'] == 'all' ? 'active' : ''; ?>" href="/mod/admin/sources"><?= __('app.all_sources'); ?> (<?= array_sum($data['stats']); ?>)</a>
    <a class="<?= $data['tab'] == 'errors' ? 'active' : ''; ?>" href="/mod/admin/sources?tab=errors"><?= __('app.source_errors'); ?> (<?= $data['errorsCount']; ?>)</a>
    <a class="<?= $data['tab'] == 'nosource' ? 'active' : ''; ?>" href="/mod/admin/sources?tab=nosource"><?= __('app.facts_no_source'); ?> (<?= $data['noSourceCount']; ?>)</a>
  </nav>

  <?php if ($data['tab'] == 'nosource') : ?>
    <?php if ($data['factsNoSource']) : ?>
      <table class="data zebra">
        <thead>
          <tr>
            <th><?= __('app.fact'); ?></th>
            <th><?= __('app.item_date'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data['factsNoSource'] as $fact) : ?>
            <tr>
              <td><a href="<?= url('item.form.edit', ['id' => $fact['item_id']]); ?>"><?= htmlEncode($fact['item_title']); ?></a></td>
              <td><?= langDate($fact['item_date']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?= Html::pagination($data['pNum'], $data['pagesCount'], false, '/mod/admin/sources?tab=nosource', '&'); ?>
    <?php else : ?>
      <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
    <?php endif; ?>
  <?php else : ?>
    <?php if ($data['sources']) : ?>
      <table class="data zebra">
        <thead>
          <tr>
            <th><?= __('app.source'); ?></th>
            <th><?= __('app.source_status'); ?></th>
            <th>HTTP</th>
            <th><?= __('app.source_checked_at'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data['sources'] as $source) : ?>
            <tr>
              <td>
                <?php if (!empty($source['title'])) : ?>
                  <strong><?= htmlEncode($source['title']); ?></strong>
                <?php endif; ?>
                <div><a href="<?= htmlEncode($source['url']); ?>" target="_blank" rel="noopener" class="gray"><?= htmlEncode($source['url']); ?></a></div>
                <?php if (!empty($source['facts'])) : ?>
                  <div class="text-sm gray">
                    <?php foreach ($source['facts'] as $fact) : ?>
                      <div><a href="<?= url('item.form.edit', ['id' => $fact['item_id']]); ?>"><?= htmlEncode($fact['item_title']); ?></a></div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <?php
                $label = match ($source['status']) {
                    'ok'       => 'green',
                    'broken'   => 'red',
                    'timeout'  => 'brown',
                    'redirect' => 'sky',
                    default    => 'gray',
                };
                ?>
                <span class="<?= $label; ?>"><?= __('app.source_status_' . $source['status']); ?></span>
              </td>
              <td><?= $source['http_code'] ?: '—'; ?></td>
              <td><?= $source['last_checked_at'] ? langDate($source['last_checked_at']) : '—'; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php
      $paginationPath = '/mod/admin/sources';
      $paginationSign = '?';
      if ($data['tab'] == 'errors') {
          $paginationPath = '/mod/admin/sources?tab=errors';
          $paginationSign = '&';
      }
      ?>
      <?= Html::pagination($data['pNum'], $data['pagesCount'], false, $paginationPath, $paginationSign); ?>
    <?php else : ?>
      <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
    <?php endif; ?>
  <?php endif; ?>
</main>