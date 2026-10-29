<nav class="nav" aria-label="Меню">
  <?php foreach ($itemsMenu as $key => $item) :
    $css = empty($item['css']) ? false : $item['css'];
    $isActive = $item['id'] == $sheet ? 'active' : false;
    $class = ($css || $isActive) ? ' class="' . $isActive . ' ' .  $css . '"' : ''; ?>

    <?php if ($container->user()->tl() >= $item['tl']) : ?>
      <a<?= $class; ?> href="<?= url($item['url']); ?>">
        <?= __($item['title']); ?>
      </a>
    <?php endif; ?>
  <?php endforeach; ?>
</nav>