<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<main class="content">
  <h1 class="title"><?= __('app.best_facts'); ?></h1>

  <?php if ($items) :
    $mod = !empty($preview) ? 'preview' : 'static';
    ?>
    <div class="best-list">
      <?php foreach ($items as $item) :
        $dir  = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
        $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], $mod);
        $img  = !empty($item['item_thumb_img'])
            ? Img::PATH['thumbs'] . $item['item_thumb_img']
            : Parser::miniature($item['item_content'] ?? '');
        ?>
        <article class="best-item">
          <div class="article-text">
            <?php if ($img) : ?>
              <a href="<?= $path; ?>"><img class="article-thumb" alt="<?= htmlEncode($item['item_title']); ?>" src="<?= htmlEncode($img); ?>"></a>
            <?php endif; ?>
            <a class="article-title" href="<?= $path; ?>"><?= htmlEncode($item['item_title']); ?></a> —
            <?= htmlEncode(Parser::noHTML($item['item_content'], 260)); ?>
            <a class="more" href="<?= $path; ?>"><?= __('app.read_more'); ?> →</a>
            <div class="byline">
              <?= Html::facetDir($item['facet_list'] ?? '', $mod); ?>
              <span class="divider">·</span>
              <span class="lowercase"><?= langDate($item['item_date']); ?></span>
              <?php if (!empty($preview)) : ?>
                <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
                  <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <?php
    $pref = !empty($preview) ? '/mod/admin/preview/' : '/';
    $curPage = $pNum ?? 1;
    ?>
    <?php if (($pagesCount ?? 1) > 1) : ?>
      <?= Html::pagination(
        $pNum ?? 1,
        (int)($pagesCount ?? 1),
        null,
        '',
        '?',
        '',
        [
          'url'  => !empty($preview) ? '/mod/admin/preview' : '/',
          'base' => 'best',
          'ext'  => '.html',
        ]
      ); ?>
    <?php endif; ?>
  <?php else : ?>
    <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
  <?php endif; ?>
</main>

<?= insert('/site/layouts/footer', ['preview' => !empty($preview)]); ?>