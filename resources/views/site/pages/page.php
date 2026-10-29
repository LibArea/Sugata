<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<main class="content">
  <?= insert('/shared/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

  <article>
    <h1 class="title"><?= htmlEncode($item['item_title']); ?>
      <?php if (!empty($preview)) : ?>
        <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
          <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
        </a>
      <?php endif; ?>
    </h1>

    <?php if (!empty($item['item_thumb_img'])) : ?>
      <div class="mb1">
        <img class="w-100" src="<?= htmlEncode(Img::PATH['thumbs'] . $item['item_thumb_img']); ?>" alt="<?= htmlEncode($item['item_title']); ?>">
      </div>
    <?php endif; ?>

    <div>
      <?= markdown($item['item_content'], 'text', !empty($preview)); ?>
    </div>

    <?php $srcUrl = trim((string)($item['item_source_url'] ?? '')); ?>
    <?php if (!empty($item['item_source_title']) || $srcUrl !== '') : ?>
      <div class="mt1 mb1 gray">
        <?= __('app.source'); ?>:
        <?php if ($srcUrl !== '') : ?>
          <a class="gray" href="<?= htmlEncode($srcUrl); ?>" rel="nofollow"><?= htmlEncode(!empty($item['item_source_title']) ? $item['item_source_title'] : $srcUrl); ?></a>
        <?php else : ?>
          <?= htmlEncode($item['item_source_title']); ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </article>
</main>

<?php if (!empty($preview)) : ?>
  <?= insert('/site/layouts/footer', ['preview' => true]); ?>
<?php else : ?>
  <!--#include virtual="/assets/footer.shtml"-->
<?php endif; ?>