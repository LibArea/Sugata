<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<main class="content">
  <?= insert('/shared/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

  <article>
    <?php $srcUrl = trim((string)($item['item_source_url'] ?? '')); ?>
    <h1 class="title"><?= htmlEncode($item['item_title']); ?>
      <?php if ($srcUrl !== '') : ?>
        <a href="<?= htmlEncode($srcUrl); ?>" target="_blank" rel="nofollow noopener noreferrer" aria-label="<?= __('app.open_site'); ?>">
          <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#external"></use></svg>
        </a>
      <?php endif; ?>
      <?php if (!empty($preview)) : ?>
        <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
          <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
        </a>
      <?php endif; ?>
    </h1>

    <div class="hint mb1">
      <?= Html::facetDir($item['facet_list'] ?? '', !empty($preview) ? 'preview' : 'static'); ?>
      <span class="lowercase"><?= langDate($item['item_date']); ?></span>
    </div>

    <?php if ($srcUrl !== '') : ?>
      <div class="mb1">
        <a class="btn" href="<?= htmlEncode($srcUrl); ?>" target="_blank" rel="nofollow noopener noreferrer">
          <?= __('app.visit_site'); ?>
        </a>
      </div>
    <?php endif; ?>

    <?php if (!empty($item['item_thumb_img'])) : ?>
      <div class="mb1">
        <img class="w-100" src="<?= htmlEncode(Img::PATH['thumbs'] . $item['item_thumb_img']); ?>" alt="<?= htmlEncode($item['item_title']); ?>">
      </div>
    <?php endif; ?>

    <?php if (!empty($faq)) : ?>
      <section class="mb1" aria-label="FAQ">
        <h2 class="title"><?= __('app.faq'); ?></h2>
        <?php foreach ($faq as $qa) : ?>
          <details>
            <summary><?= htmlEncode($qa['question']); ?></summary>
            <div><?= markdown($qa['answer']); ?></div>
          </details>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>

    <?= insert('/site/components/toc', ['item' => $item]); ?>

    <div>
      <?= markdown($item['item_content'], 'text', !empty($preview)); ?>
    </div>

    <?php if (!empty($item['item_source_title']) || $srcUrl !== '') : ?>
      <div class="mt1 mb1 gray">
        <?= __('app.source'); ?>:
        <?php if ($srcUrl !== '') : ?>
          <a class="gray" href="<?= htmlEncode($srcUrl); ?>" rel="nofollow noopener noreferrer"><?= htmlEncode(!empty($item['item_source_title']) ? $item['item_source_title'] : $srcUrl); ?></a>
        <?php else : ?>
          <?= htmlEncode($item['item_source_title']); ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </article>

  <?= insert('/site/components/backlinks', ['backlinks' => $backlinks ?? [], 'preview' => !empty($preview)]); ?>
</main>

<?php if (!empty($preview)) : ?>
  <?= insert('/site/layouts/footer', ['preview' => true]); ?>
<?php else : ?>
  <!--#include virtual="/assets/footer.shtml"-->
<?php endif; ?>