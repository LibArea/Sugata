<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview), 'activeRoot' => 'facts']); ?>

<?php
$mod = !empty($preview) ? 'preview' : 'static';
?>

<main class="container" id="article-main">
  <?= insert('/shared/navigation/breadcrumbs', ['list' => $breadcrumb ?? []]); ?>

  <header class="page-head">
    <h1 class="article-title"><?= __('app.best_facts'); ?></h1>
    <p class="article-sub"><?= __('app.continue_discover'); ?></p>
  </header>

  <div class="rule"></div>

  <?php if ($items) : ?>
    <div class="grid grid--3">
      <?php foreach ($items as $item) :
        $dir  = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
        $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], $mod);
        ?>
        <article class="card">
          <div class="hint"><?= Html::facetDir($item['facet_list'] ?? '', $mod); ?></div>
          <h3><a href="<?= $path; ?>"><?= htmlEncode($item['item_title'] ?? ''); ?></a></h3>
          <p><?= htmlEncode(Parser::noHTML((string)($item['item_content'] ?? ''), 150)); ?></p>
          <a href="<?= $path; ?>"><?= __('app.read_article'); ?> →</a>
        </article>
      <?php endforeach; ?>
    </div>

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
