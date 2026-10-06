<main>
  <div class="nav-bar">
    <ul class="nav scroll-menu">
      <?= insert('/_block/navigation/nav', ['sheet' => $data['sheet']]); ?>
    </ul>
  </div>

  <div class="flex justify-between items-center mb20">
    <h1 class="title"><?= __('app.sources'); ?></h1>
    <a class="btn btn-primary" href="<?= url('update.sources'); ?>"><?= __('app.sources_check'); ?></a>
  </div>

  <div class="nav-bar">
    <ul class="nav scroll-menu admin-nav admin-nav--filters">
      <li class="<?= $data['tab'] == 'all' ? 'active' : ''; ?>">
        <a href="/mod/admin/sources"><?= __('app.all_sources'); ?> (<?= array_sum($data['stats']); ?>)</a>
      </li>
      <li class="<?= $data['tab'] == 'errors' ? 'active' : ''; ?>">
        <a href="/mod/admin/sources?tab=errors"><?= __('app.source_errors'); ?> (<?= $data['errorsCount']; ?>)</a>
      </li>
      <li class="<?= $data['tab'] == 'nosource' ? 'active' : ''; ?>">
        <a href="/mod/admin/sources?tab=nosource"><?= __('app.facts_no_source'); ?> (<?= $data['noSourceCount']; ?>)</a>
      </li>
    </ul>
  </div>

  <?php if ($data['tab'] == 'nosource') : ?>

    <?php if ($data['factsNoSource']) : ?>
      <table>
        <thead>
          <tr>
            <th><?= __('app.fact'); ?></th>
            <th><?= __('app.item_date'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data['factsNoSource'] as $fact) : ?>
            <tr>
              <td>
                <a href="<?= url('item.form.edit', ['id' => $fact['item_id']]); ?>"><?= htmlEncode($fact['item_title']); ?></a>
              </td>
              <td><?= langDate($fact['item_date']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?= Html::pagination($data['pNum'], $data['pagesCount'], false, '/mod/admin/sources?tab=nosource', '&'); ?>
    <?php else : ?>
      <?= insert('/_block/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
    <?php endif; ?>

  <?php else : ?>

    <?php if ($data['sources']) : ?>
      <table>
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
                  <div class="source-title"><?= htmlEncode($source['title']); ?></div>
                <?php endif; ?>
                <a href="<?= htmlEncode($source['url']); ?>" target="_blank" rel="noopener" class="source-url"><?= htmlEncode($source['url']); ?></a>
                <?php if (!empty($source['facts'])) : ?>
                  <div class="text-sm gray-600 source-facts-list">
                    <?php foreach ($source['facts'] as $fact) : ?>
                      <div><a href="<?= url('item.form.edit', ['id' => $fact['item_id']]); ?>" class="source-fact-link"><?= htmlEncode($fact['item_title']); ?></a></div>
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
                    default    => 'gray-600',
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
      <?= insert('/_block/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
    <?php endif; ?>

  <?php endif; ?>
</main>