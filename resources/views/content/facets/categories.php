<div class="item-categories wrap">
    <?php foreach (config('general', 'categories') as $cat) : ?>
      <div class="categories-telo">
        <a class="text-xl block" href="<?= urlDir($cat['path']); ?>">
          <?= $cat['title']; ?>
        </a>
        <?php if (!empty($cat['sub'])) : ?>
          <div class="flex gap">
            <?php foreach ($cat['sub'] as $sub) : ?>
              <a class="text-sm black" href="<?= urlDir($sub['path']); ?>">
                <?= $sub['title']; ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($cat['help'])) : ?>
          <div class="text-sm gray-600 mb-none"><?= $cat['help']; ?>...</div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
</div>