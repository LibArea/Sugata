<?= insert('/templates/header', ['meta' => $meta]); ?>

<main class="content">

   <?= insert('/_block/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

   <article>
    <?php if (!empty($item['item_thumb_img'])) : ?>
      <div class="box br-lightgray img-preview">
        <img class="w-100" src="<?= Img::PATH['thumbs'] . $item['item_thumb_img']; ?>" alt="<?= $item['item_title']; ?>">
      </div>
    <?php endif; ?>

    <h1 class="title"><?= $item['item_title']; ?></h1>

    <?= markdown($item['item_content']); ?>

    <div class="flex justify-between mb20 gray-600">
      <?php if (!empty($item['item_source_title'])) : ?>
        <div>
          <?= __('app.source') ?>: <a class="gray-600" href="<?= $item['item_source_url']; ?>" rel="nofollow"><?= $item['item_source_title']; ?></a>
        </div>
      <?php endif; ?>
    </div>
  </article>

</main>

<?= insert('/templates/footer'); ?>