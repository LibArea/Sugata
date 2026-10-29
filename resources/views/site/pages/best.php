<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<main class="content">
  <h1 class="title"><?= __('app.best_facts'); ?></h1>

  <?php if ($items) : ?>
    <?= insert('/site/components/story-list', ['items' => $items, 'preview' => !empty($preview), 'showThumb' => true]); ?>

    <?php
    $pref = !empty($preview) ? '/mod/admin/preview/' : '/';
    $curPage = $pNum ?? 1;
    ?>
    <?php if (($pagesCount ?? 1) > 1) : ?>
      <nav class="pagination">
        <?php if ($curPage > 1) : ?>
          <a href="<?= $pref; ?>best<?= $curPage == 2 ? '' : '/page-' . ($curPage - 1); ?>">← <?= __('app.page'); ?> <?= $curPage - 1; ?></a>
        <?php endif; ?>
        <span class="current"><?= $curPage; ?> / <?= $pagesCount; ?></span>
        <?php if ($curPage < $pagesCount) : ?>
          <a href="<?= $pref; ?>best/page-<?= $curPage + 1; ?>"><?= __('app.page'); ?> <?= $curPage + 1; ?> →</a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php else : ?>
    <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
  <?php endif; ?>
</main>

<?php if (!empty($preview)) : ?>
  <?= insert('/site/layouts/footer', ['preview' => true]); ?>
<?php else : ?>
  <!--#include virtual="/assets/footer.shtml"-->
<?php endif; ?>