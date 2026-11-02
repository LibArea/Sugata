<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<main class="content">
  <?= insert('/shared/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

  <div class="flex justify-between items-center">
    <h1 class="title"><?= htmlEncode($facet['facet_title']); ?></h1>
    <?php if (!empty($facet['facet_info'])) : ?>
      <span class="tag"><?= htmlEncode($facet['facet_info']); ?></span>
    <?php endif; ?>
  </div>

  <?php if ($childrens) : ?>
    <div class="item-categories">
      <?php foreach ($childrens as $lt) : ?>
        <div class="categories-telo">
          <a href="<?= urlDir($lt['facet_path'], !empty($preview) ? 'preview' : 'static'); ?>">
            <?= htmlEncode($lt['facet_title']); ?>
          </a>
          <sup class="gray"><?= (int)$lt['facet_count']; ?></sup>
          <?php if (!empty($preview)) : ?>
            <a class="ml5 gray" href="<?= url('facet.form.edit', ['type' => 'category', 'id' => $lt['facet_id']]); ?>">
              <sup><svg class="icon"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg></sup>
            </a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?= insert('/site/components/story-list', ['items' => $items, 'preview' => !empty($preview)]); ?>

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
            'base' => trim((string)($facet['facet_path'] ?? ''), '/'),
            'ext'  => '.html',
        ]
    ); ?>
  <?php endif; ?>
</main>

<?= insert('/site/layouts/footer', ['preview' => !empty($preview)]); ?>