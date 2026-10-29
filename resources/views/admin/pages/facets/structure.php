<main class="content">
  <nav class="nav">
    <?= insert('/shared/navigation/nav', ['sheet' => $data['sheet']]); ?>
  </nav>

  <h1 class="title"><?= __('app.structure'); ?></h1>

  <?php if (!empty($data['nodes'])) : ?>
    <?php foreach ($data['nodes'] as $topic) : ?>
      <?php $topic['level'] = max(0, (int)($topic['level'] ?? 0)); ?>
      <div class="flex items-center gap" style="margin-inline-start: <?= $topic['level'] * 1.25; ?>rem;">
        <a class="<?= $topic['level'] == 0 ? 'bold' : ''; ?>" href="<?= urlDir($topic['facet_path']); ?>">
          <?= htmlEncode($topic['facet_title']); ?>
        </a>

        <sup>
          <a class="gray" href="<?= url('facet.form.edit', ['type' => $data['type'], 'id' => $topic['facet_id']]); ?>">
            <svg class="icon mr5"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
          </a>
        </sup>

        <?php if ($topic['facet_is_deleted'] == 1) : ?>
          <span class="type-return" data-id="<?= $topic['facet_id']; ?>" title="<?= __('app.restore'); ?>">
            <sup><svg class="icon red"><use xlink:href="/assets/svg/icons.svg#trash-2"></use></svg></sup>
          </span>
        <?php else : ?>
          <span class="type-ban" data-id="<?= $topic['facet_id']; ?>" data-type="topic" title="<?= __('app.delete'); ?>">
            <sup><svg class="icon gray"><use xlink:href="/assets/svg/icons.svg#trash"></use></svg></sup>
          </span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php else : ?>
    <?php if ($data['type'] != 'all') : ?>
      <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
    <?php endif; ?>
  <?php endif; ?>

  <a class="btn mt1" href="<?= url('facet.form.add', ['type' => 'category']); ?>">+ <?= __('app.add'); ?></a>
</main>