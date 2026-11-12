<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview), 'activeRoot' => 'web']); ?>

<?php
$mod = !empty($preview) ? 'preview' : 'static';
?>

<main class="container" id="article-main">
  <?= insert('/shared/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

  <header class="page-head">
    <h1 class="article-title"><?= htmlEncode($facet['facet_title']); ?></h1>
    <?php if (!empty($facet['facet_info'])) : ?>
      <p class="article-sub"><?= htmlEncode($facet['facet_info']); ?></p>
    <?php endif; ?>
    <?php if (!empty($childrens)) : ?>
      <div class="chips">
        <?php foreach ($childrens as $lt) : ?>
          <a class="chip" href="<?= urlDir($lt['facet_path'], $mod); ?>"><?= htmlEncode($lt['facet_title']); ?> <span class="hint"><?= (int)$lt['facet_count']; ?></span></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </header>

  <div class="rule"></div>

  <?php if ($items) : ?>
    <div class="rows">
      <?php foreach ($items as $item) :
        $dir  = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
        $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$item['item_slug'], $mod);
        $srcUrl = trim((string)($item['item_source_url'] ?? ''));
        $host = $srcUrl !== '' ? (string)parse_url($srcUrl, PHP_URL_HOST) : '';
        $host = preg_replace('~^www\.~i', '', $host);
        $screen = trim((string)($item['item_screen_img'] ?? ''));
        $tcy = trim((string)($item['item_yandex_tcy'] ?? ''));
        ?>
        <article class="row">
          <?php if ($screen !== '') : ?>
            <a class="row__shot" href="<?= $path; ?>" tabindex="-1" aria-hidden="true">
              <img src="<?= htmlEncode('/uploads/screens/' . $screen); ?>" alt="<?= htmlEncode($item['item_title']); ?>" loading="lazy">
            </a>
          <?php endif; ?>
          <div class="row__body">
            <h3><a href="<?= $path; ?>"><?= htmlEncode($item['item_title'] ?? ''); ?></a></h3>
            <p class="row__text"><?= htmlEncode(Parser::noHTML((string)($item['item_content'] ?? ''), 170)); ?></p>
            <div class="row__meta">
              <?php if ($host !== '') : ?><span><?= htmlEncode($host); ?></span><?php endif; ?>
              <?php if ($tcy !== '') : ?><span><?= __('app.tcy'); ?> <?= htmlEncode($tcy); ?></span><?php endif; ?>
            </div>
          </div>
          <div class="row__actions">
            <?php if ($srcUrl !== '') : ?>
              <a class="btn btn--sm" href="<?= htmlEncode($srcUrl); ?>" target="_blank" rel="nofollow noopener noreferrer"><?= __('app.open_site'); ?></a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <?php if (($pagesCount ?? 1) > 1) : ?>
      <?= Html::pagination(
          $pNum ?? 1,
          (int)($pagesCount ?? 1),
          null,
          '',
          '?',
          '',
          [
              'url'  => !empty($preview) ? '/mod/admin/preview' : '/',
              'base' => trim((string)($facet['facet_path'] ?? ''), '/'),
              'ext'  => '.html',
          ]
      ); ?>
    <?php endif; ?>
  <?php else : ?>
    <?= insert('/shared/no-content', ['type' => 'small', 'text' => __('app.no_content'), 'icon' => 'info']); ?>
  <?php endif; ?>
</main>

<?= insert('/site/layouts/footer', ['preview' => !empty($preview)]); ?>
