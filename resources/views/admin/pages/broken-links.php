<main class="content">
  <nav class="nav">
    <?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
  </nav>

  <div class="flex justify-between items-center">
    <h1 class="title"><?= __('app.broken_links'); ?></h1>
    <a class="btn" href="<?= url('broken.links.check'); ?>"><?= __('app.broken_links_check'); ?></a>
  </div>

  <?php if ($data['termsTotal'] || $data['urlsTotal']) : ?>

    <!-- Секция 1: востребованные термины (несозданные статьи) -->
    <?php if ($data['termsTotal']) : ?>
      <h2 class="title"><?= __('app.terms_wanted'); ?></h2>
      <div class="mb1 gray"><?= __('app.terms_wanted_total'); ?>: <b><?= $data['termsTotal']; ?></b></div>
      <table class="data zebra">
        <thead>
          <tr>
            <th><?= __('app.terms_term'); ?></th>
            <th><?= __('app.terms_slug'); ?></th>
            <th><?= __('app.terms_links_count'); ?></th>
            <th><?= __('app.terms_in_articles'); ?></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data['terms'] as $t) :
            $sources = [];
            if (!empty($t['source_items'])) {
                foreach (explode('||', $t['source_items']) as $pair) {
                    if (preg_match('/^(\d+)::(.*)$/s', $pair, $pm)) {
                        $sources[] = [
                            'id'    => (int)$pm[1],
                            'title' => $pm[2],
                        ];
                    }
                }
            }
            ?>
            <tr>
              <td><?= htmlEncode($t['term']); ?></td>
              <td><code><?= htmlEncode($t['term_slug']); ?></code></td>
              <td><b><?= (int)$t['links_count']; ?></b></td>
              <td class="terms-sources">
                <?php if ($sources) : ?>
                  <?php foreach ($sources as $j => $src) : ?>
                    <?php if ($j > 0) : ?>; <?php endif; ?>
                    <a href="<?= url('item.form.edit', ['id' => $src['id']]); ?>"><?= htmlEncode($src['title']); ?></a>
                  <?php endforeach; ?>
                <?php else : ?>
                  <span class="gray">—</span>
                <?php endif; ?>
              </td>
              <td>
                <a class="btn" href="<?= url('item.form.add') . '?title=' . rawurlencode($t['term']); ?>"><?= __('app.terms_create'); ?></a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <!-- Секция 2: жёсткие битые ссылки (URL) -->
    <?php if ($data['urlsTotal']) : ?>
      <h2 class="title"><?= __('app.broken_urls'); ?></h2>
      <div class="mb1 gray"><?= __('app.broken_links_total'); ?>: <b><?= $data['urlsTotal']; ?></b></div>
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
          <?php foreach ($data['urls'] as $link) : ?>
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
    <?php endif; ?>

    <?= Html::pagination($data['pNum'], $data['pagesCount'], false, '/mod/admin/broken-links'); ?>
  <?php else : ?>
    <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.broken_links_none'), 'icon' => 'info']); ?>
  <?php endif; ?>
</main>