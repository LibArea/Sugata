<nav class="breadcrumbs" aria-label="<?= __('app.breadcrumbs'); ?>" itemscope itemtype="https://schema.org/BreadcrumbList">
  <?php
  $items = [];
  foreach ($list as $key => $item) {
      $items[] = $item;
  }
  $lastIdx = count($items) - 1;
  foreach ($items as $i => $item) :
  ?>
    <?php if ($i > 0) : ?>
      <span aria-hidden="true">/</span>
    <?php endif; ?>
    <?php if ($i < $lastIdx) : ?>
      <a itemprop="item" href="<?= $item['link'] ?? '#'; ?>" itemscope itemtype="https://schema.org/ListItem">
        <span itemprop="name"><?= $item['name']; ?></span>
        <meta itemprop="position" content="<?= $i + 1; ?>">
      </a>
    <?php else : ?>
      <span aria-current="page" itemscope itemtype="https://schema.org/ListItem">
        <span itemprop="name"><?= $item['name']; ?></span>
        <meta itemprop="position" content="<?= $i + 1; ?>">
      </span>
    <?php endif; ?>
  <?php endforeach; ?>
</nav>