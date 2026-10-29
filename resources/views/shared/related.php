<?php
/*
 * Связанные факты/статьи (нативный класс stories base.css)
 * Ожидает: $related (массив item-записей), $title (заголовок блока), $preview (bool|null)
 */
if (empty($related)) {
    return;
}
$preview = $preview ?? false;
$linkMod = $preview ? 'preview' : 'static';
$accent  = $accent ?? false;
$plain   = $plain ?? false;
$thumb   = $thumb ?? false;
?>
<h3 class="title"><?= $title; ?></h3>
<ol class="stories related">
  <?php foreach ($related as $value) :
    $rDir = preg_split('/(@)/', (string)($value['facet_list'] ?? ''));
    $rPath = trim($rDir[2] ?? '', '/');
    // Корневой раздел — первый сегмент пути (facts/articles/web/info), для цвета полосы
    $rRoot = strtok($rPath, '/') ?: 'other';
    ?>
    <li class="story related--<?= htmlEncode($rRoot); ?>">
      <div class="story_liner">
        <?php if ($thumb && ($tImg = Parser::miniature($value['item_content'] ?? ''))) : ?>
          <a href="<?= urlItem($rPath, $value['item_slug'], $linkMod); ?>">
            <img alt="<?= htmlEncode($value['item_title']); ?>" src="<?= htmlEncode($tImg); ?>">
          </a>
        <?php endif; ?>

        <div class="link"><a href="<?= urlItem($rPath, $value['item_slug'], $linkMod); ?>"><?= htmlEncode($value['item_title']); ?></a></div>

        <?php if (!empty($value['item_content'])) : ?>
          <div class="story_content">
            <?php
              $arr = Parser::cut($value['item_content']);
              if ($plain) {
                  echo htmlEncode(strip_tags(markdown($arr['content'], 'text')));
              } else {
                  echo markdown($arr['content'], 'text');
              }
            ?>
          </div>
        <?php endif; ?>

        <div class="byline">
          <?= Html::facetDir($value['facet_list'] ?? '', $linkMod); ?>
          <span class="divider">·</span>
          <span class="lowercase"><?= langDate($value['item_date']); ?></span>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
</ol>