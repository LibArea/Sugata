<ul itemscope itemtype="https://schema.org/BreadcrumbList" class="breadcrumb">
  <?php
  end($list);
  $last_item_key   = key($list);
  $show_last = true;
  foreach ($list as $key => $item) :
    if ($key != $last_item_key) :
  ?>
      <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
        <?php if (!empty($item['link'])) : ?>
          <a itemprop="item" href="<?= $item['link']; ?>"><span itemprop="name"><?= $item['name']; ?></span></a>
        <?php else : ?>
          <span itemprop="name"><?= $item['name']; ?></span>
        <?php endif; ?>
        <meta itemprop="position" content="<?= $key + 1; ?>">
      </li>
    <?php elseif ($show_last) : ?>
      <li><span aria-current="page"><?= $item['name']; ?></span></li>
  <?php endif;
  endforeach; ?>
</ul>