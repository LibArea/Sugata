<?php
/*
 * Связанные факты/статьи.
 * Ожидает: $related (массив item-записей), $title (заголовок блока), $preview (bool|null)
 */
if (empty($related)) {
    return;
}
$preview = $preview ?? false;
$linkMod = $preview ? 'preview' : 'static';
$accent  = $accent ?? false; // true — цветной бордюр слева (ручная связь)
$plain   = $plain ?? false;  // true — сниппет выводить плоским текстом (без markdown)
$thumb   = $thumb ?? false;  // true — показывать фото (превью из контента) слева
?>
<h3 class="uppercase-box"><?= $title; ?></h3>
<div class="wiki-fact-list wiki-mb4">
  <?php foreach ($related as $value) :
    $rDir = preg_split('/(@)/', (string)($value['facet_list'] ?? ''));
    $rPath = trim($rDir[2] ?? '', '/');
    ?>
    <article class="wiki-fact-item<?= $accent ? ' wiki-fact-item--related' : ''; ?>">
      <?php if ($thumb && ($tImg = Parser::miniature($value['item_content'] ?? ''))) : ?>
        <a class="wiki-fact-item__thumb" href="<?= urlItem($rPath, $value['item_slug'], $linkMod); ?>">
          <img alt="<?= htmlEncode($value['item_title']); ?>" src="<?= htmlEncode($tImg); ?>">
        </a>
      <?php endif; ?>
      <div class="wiki-fact-item__body">
        <h3>
          <a href="<?= urlItem($rPath, $value['item_slug'], $linkMod); ?>">
            <?= htmlEncode($value['item_title']); ?>
          </a>
        </h3>
        <?php if (!empty($value['item_content'])) : ?>
          <div class="wiki-fact-item__excerpt">
            <?php
              $arr = Parser::cut($value['item_content']);
              if ($plain) {
                  // Плоский текст (без форматирования) — для блока «Факты»
                  echo htmlEncode(strip_tags(markdown($arr['content'], 'mini')));
              } else {
                  echo markdown($arr['content'], 'mini');
              }
            ?>
          </div>
        <?php endif; ?>
        <div class="wiki-fact-item__meta">
          <?= Html::facetDir($value['facet_list'] ?? '', $linkMod); ?>
          <span class="lowercase"><?= langDate($value['item_date']); ?></span>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
</div>