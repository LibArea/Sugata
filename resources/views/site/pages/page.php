<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview), 'activeRoot' => $activeRoot ?? '']); ?>

<?php
$mod  = !empty($preview) ? 'preview' : 'static';
$dir  = $dir ?? [];
$related = $related ?? [];
$tocRaw = $toc ?? [];
?>

<main class="container" id="article-main">
  <?= insert('/shared/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

  <header class="page-head" id="article-top">
    <h1 class="article-title"><?= htmlEncode($item['item_title']); ?>
      <?php if (!empty($preview)) : ?>
        <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
          <svg class="icon" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
        </a>
      <?php endif; ?>
    </h1>
    <p class="article-sub"><?= __('app.article_subtitle'); ?></p>
    <div class="chips">
      <?= Html::facetDir($item['facet_list'] ?? '', $mod); ?>
      <span class="chip"><?= langDate($item['item_date']); ?></span>
      <span class="chip"><?= __('app.encyclopedia_name'); ?></span>
    </div>
  </header>

  <div class="rule"></div>

  <div class="article-layout article-layout--2">
    <aside class="toc article-sidebar">
      <nav aria-label="<?= __('app.article_toc_label'); ?>">
        <b><?= __('app.article_toc_eyebrow'); ?></b>
        <a class="toc-back" href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>"><?= __('app.back_archive'); ?></a>
      </nav>
    </aside>

    <article class="article-body">
      <?= insert('/site/components/article-body', ['item' => $item, 'mod' => $mod, 'faq' => $faq ?? []]); ?>

      <?php $srcUrl = trim((string)($item['item_source_url'] ?? '')); ?>
      <?php if (!empty($item['item_source_title']) || $srcUrl !== '') : ?>
        <div class="box">
          <div class="hint" style="margin-bottom:6px"><?= __('app.source'); ?></div>
          <p style="margin:0">
            <?php if ($srcUrl !== '') : ?>
              <a href="<?= htmlEncode($srcUrl); ?>" rel="nofollow"><?= htmlEncode(!empty($item['item_source_title']) ? $item['item_source_title'] : $srcUrl); ?></a>
            <?php else : ?>
              <?= htmlEncode($item['item_source_title']); ?>
            <?php endif; ?>
          </p>
        </div>
      <?php endif; ?>

      <div class="toolbar" style="margin-top:24px">
        <a class="btn" href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>">← <?= __('app.to_archive'); ?></a>
        <a class="btn" href="#article-top"><?= __('app.to_top'); ?> ↑</a>
      </div>
    </article>
  </div>

  <?php if ($related) : ?>
    <section id="article-related" aria-labelledby="related-title">
      <div class="section-head">
        <h2 id="related-title"><?= __('app.continue_discover'); ?></h2>
        <a href="<?= urlDir('facts', $mod); ?>"><?= __('app.whole_archive'); ?></a>
      </div>
      <div class="grid grid--3">
        <?php foreach ($related as $value) :
          $rDir = preg_split('/(@)/', (string)($value['facet_list'] ?? ''));
          $rPath = urlItem(isset($rDir[2]) ? trim($rDir[2], '/') : '', (string)($value['item_slug'] ?? ''), $mod);
          $rRoot = strtok(trim((string)($rDir[2] ?? ''), '/'), '/');
          $readLabel = match ($rRoot) {
              'articles' => __('app.read_article'),
              'web'      => __('app.open_site'),
              default    => __('app.read_fact'),
          };
          ?>
          <article class="card">
            <div class="hint"><?= Html::facetDir($value['facet_list'] ?? '', $mod); ?></div>
            <h3><a href="<?= $rPath; ?>"><?= htmlEncode($value['item_title'] ?? ''); ?></a></h3>
            <p style="font-size:13px;margin-bottom:8px"><?= htmlEncode(Parser::noHTML((string)($value['item_content'] ?? ''), 130)); ?></p>
            <a href="<?= $rPath; ?>"><?= $readLabel; ?> →</a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</main>

<?= insert('/site/layouts/footer', ['preview' => !empty($preview)]); ?>
