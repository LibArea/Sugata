<?= insert('/templates/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<div class="content wiki-content">
  <div class="wiki-layout">
    <main class="wiki-layout__main">
      <?= insert('/_block/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

      <article>
        <header class="wiki-fact-header">
          <h1>
            <?= htmlEncode($item['item_title']); ?>
            <?php if (!empty($preview)) : ?>
              <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
                <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
              </a>
            <?php endif; ?>
          </h1>
        </header>

        <?php if (!empty($item['item_thumb_img'])) : ?>
          <div class="box br-lightgray img-preview wiki-mb4">
            <img class="w-100" src="<?= htmlEncode(Img::PATH['thumbs'] . $item['item_thumb_img']); ?>" alt="<?= htmlEncode($item['item_title']); ?>">
          </div>
        <?php endif; ?>

        <div class="wiki-fact-body">
          <?= markdown($item['item_content']); ?>
        </div>

        <?php if (!empty($item['item_source_title'])) : ?>
          <div class="flex justify-between mb20 gray-600">
            <div>
              <?= __('app.source'); ?>: <a class="gray-600" href="<?= htmlEncode($item['item_source_url'] ?? '#'); ?>" rel="nofollow"><?= htmlEncode($item['item_source_title']); ?></a>
            </div>
          </div>
        <?php endif; ?>
      </article>
    </main>

    <aside class="wiki-layout__sidebar">
      <?= insert('/templates/layout/sidebar', ['preview' => !empty($preview), 'activePath' => 'info']); ?>
    </aside>
  </div>
</div>

<?php if (!empty($preview)) : ?>
  <?= insert('/templates/footer', ['preview' => true]); ?>
<?php else : ?>
  <!--#include virtual="/assets/footer.shtml"-->
<?php endif; ?>