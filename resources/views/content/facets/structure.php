<main>

  <div class="nav-bar">
    <ul class="nav scroll-menu">
      <?= insert('/_block/navigation/nav', ['sheet' => $data['sheet']]); ?>
    </ul>
  </div>

  <h1 class="title"><?= __('app.structure'); ?></h1>

  <?php if (!empty($data['nodes'])) : ?>
    <?php foreach ($data['nodes'] as $topic) : ?>
      <div class="w-50 mb5">
        <?php $topic['level'] = $topic['level'] ?? null; ?>
        <?php if ($topic['level'] > 0) : ?>
          <?php $color = true; ?>
          <svg class="icon gray ml<?= $topic['level'] * 10; ?>">
            <use xlink:href="/assets/svg/icons.svg#corner-down-right"></use>
          </svg>
        <?php endif; ?>

        <?php if ($topic['level'] == 0) : ?>
          <?= Img::image($topic['facet_img'], htmlEncode($topic['facet_title']), 'w20 h20 mr5 br-gray', 'logo', 'max'); ?>
        <?php endif; ?>

        <a class="
		<?php if ($topic['level'] == 0) : ?>relative mt5 text-xl items-center hidden<?php endif; ?> 
			<?php if ($topic['level'] > 0) : ?> black<?php endif; ?>"

          href="<?= urlDir($topic['facet_path']); ?>">
          <?= htmlEncode($topic['facet_title']); ?></a>

        <a class="<?php if ($topic['level'] == 0) : ?>relative mt5 text-xl items-center hidden<?php endif; ?> <?php if ($topic['level'] > 0) : ?> black<?php endif; ?>" href="<?= url('facet.form.edit', ['type' => $data['type'], 'id' => $topic['facet_id']]); ?>">

          <sup><svg class="icon mr5">
              <use xlink:href="/assets/svg/icons.svg#edit"></use>
            </svg></sup>
        </a>

        <?php if ($topic['facet_is_deleted'] == 1) : ?>
          <span class="type-ban" data-id="<?= $topic['facet_id']; ?>" data-type="topic">
            <sup><svg class="icon red">
                <use xlink:href="/assets/svg/icons.svg#trash-2"></use>
              </svg></sup>
          </span>
        <?php else : ?>
          <span class="type-ban" data-id="<?= $topic['facet_id']; ?>" data-type="topic">
            <sup><svg class="icon gray-600">
                <use xlink:href="/assets/svg/icons.svg#trash"></use>
              </svg></sup>
          </span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php else : ?>
    <?php if ($data['type'] != 'all') : ?>
      <?= insert('/_block/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
    <?php endif; ?>
  <?php endif; ?>



  <a class="center block" href="<?= url('facet.form.add', ['type' => 'category']); ?>">
    <button type="submit" name="action" class="btn btn-primary" value="submit">+ <?= __('app.add'); ?></button>
  </a>

</main>