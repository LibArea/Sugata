<?php
/*
 * Тело статьи в разметке референса Sugata:
 *   article-lead → article-figure → article-summary → prose-section (01 / …) →
 *   article-callout → article-source → article-bottom
 *
 * Ожидает: $item, $mod ('static'|'preview'), $faq (array), $backlinks не нужен.
 */

$mod  = $mod ?? 'static';
$faq  = $faq ?? [];
$noFigure = $noFigure ?? false;
$content = (string)($item['item_content'] ?? '');

/* ---------- 1. Вырезаем блок фото :::{.foto} … ::: ----------
 * Если фото уже показано в инфобоксе (noFigure), блок не вырезаем:
 * его отрисует Parser как <figure class="foto"> с подписью и правами.
 */
$figure = null;
if (empty($noFigure) && preg_match('/:::\{\.foto\}(.*?):::/su', $content, $fm)) {
    $block = $fm[1];
    $content = str_replace($fm[0], '', $content);
    if (preg_match('/!\[([^\]]*)\]\(([^)]+)\)/u', $block, $im)) {
        $caption = '';
        if (preg_match('/\*([^*]+)\*/u', $block, $cm)) {
            $caption = trim($cm[1]);
        }
        $figure = ['alt' => $im[1], 'src' => $im[2], 'caption' => $caption];
    }
}

/* ---------- 2. Цитаты (>) не трогаем — их рендерит CommonMark в <blockquote> ---------- */

/* ---------- 3. Лид — первый абзац до первого заголовка ---------- */
$lead = '';
$lines = preg_split('/\R/u', $content);
$buf = [];
$rest = [];
$i = 0;
$n = count($lines);
for (; $i < $n; $i++) {
    $l = $lines[$i];
    if (preg_match('/^#{1,6}\s+/u', trim($l))) {
        break;
    }
    $buf[] = $l;
}
$lead = trim(implode("\n", $buf));
$rest = array_slice($lines, $i);

/* ---------- 4. Секции по заголовкам ---------- */
$sections = [];
$curTitle = null;
$curLevel = 2;
$curLines = [];
foreach ($rest as $l) {
    if (preg_match('/^(#{1,6})\s+(.+)$/u', trim($l), $hm)) {
        if ($curTitle !== null) {
            $sections[] = ['title' => $curTitle, 'level' => $curLevel, 'body' => trim(implode("\n", $curLines))];
        }
        $curTitle = trim($hm[2]);
        $curLevel = strlen($hm[1]);
        $curLines = [];
        continue;
    }
    if ($curTitle !== null) {
        $curLines[] = $l;
    } else {
        // текст до первого заголовка после лида — добавim в лид
        if (trim($l) !== '') {
            $lead .= ($lead !== '' ? "\n\n" : '') . trim($l);
        }
    }
}
if ($curTitle !== null) {
    $sections[] = ['title' => $curTitle, 'level' => $curLevel, 'body' => trim(implode("\n", $curLines))];
}

/* ---------- 5. Сводка «Коротко о главном» ---------- */
$summary = [];
$dir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
$cats = [];
foreach (array_chunk($dir, 4) as $row) {
    if (($row[1] ?? '') === 'category' && !empty($row[3])) {
        $cats[] = $row[3];
    }
}
if (!empty($cats)) {
    $summary[] = ['k' => __('app.summary_section'), 'v' => implode(' · ', $cats)];
}
if (!empty($item['item_date'])) {
    $summary[] = ['k' => __('app.summary_published'), 'v' => langDate($item['item_date'])];
}
?>

<?php if ($lead !== '') :
  $leadHtml = trim((string) markdown($lead, 'text', $mod === 'preview'));
  $leadSingle = substr_count($leadHtml, '<p>') === 1
      && str_starts_with($leadHtml, '<p>')
      && str_ends_with($leadHtml, '</p>');
  if ($leadSingle) {
      $leadHtml = substr($leadHtml, 3, -4);
  }
  ?>
  <?php if ($leadSingle) : ?>
    <p class="article-lead"><?= $leadHtml; ?></p>
  <?php else : ?>
    <div class="article-lead"><?= $leadHtml; ?></div>
  <?php endif; ?>
<?php endif; ?>

<?php if ($figure && empty($noFigure)) : ?>
  <figure class="article-figure">
    <img src="<?= htmlEncode($figure['src']); ?>" alt="<?= htmlEncode($figure['alt']); ?>" fetchpriority="high">
    <?php if ($figure['caption'] !== '') : ?>
      <figcaption><span><?= htmlEncode($figure['caption']); ?></span></figcaption>
    <?php endif; ?>
  </figure>
<?php endif; ?>

<?php if (!empty($summary) || $faq) : ?>
  <div class="article-summary">
    <span class="eyebrow"><?= __('app.summary_title'); ?></span>
    <dl>
      <?php foreach ($summary as $s) : ?>
        <div><dt><?= htmlEncode($s['k']); ?></dt><dd><?= htmlEncode($s['v']); ?></dd></div>
      <?php endforeach; ?>
      <?php foreach ($faq as $qa) : ?>
        <div><dt><?= htmlEncode($qa['question']); ?></dt><dd><?= markdown($qa['answer']); ?></dd></div>
      <?php endforeach; ?>
    </dl>
  </div>
<?php endif; ?>

<?php if ($sections) : ?>
  <?php $anchorSeen = []; ?>
  <?php foreach ($sections as $idx => $sec) : ?>
    <?php
      $secAnchor = Parser::anchor((string)$sec['title'], $anchorSeen);
      $secTag = (int)($sec['level'] ?? 2) >= 3 ? 'h3' : 'h2';
    ?>
    <section class="prose-section" id="<?= htmlEncode($secAnchor); ?>">
      <span class="section-index"><?= sprintf('%02d', $idx + 1); ?> / <?= htmlEncode(mb_strtoupper($sec['title'], 'UTF-8')); ?></span>
      <<?= $secTag ?>><?= htmlEncode($sec['title']); ?></<?= $secTag ?>>
      <?= markdown($sec['body'], 'text', $mod === 'preview'); ?>
    </section>
  <?php endforeach; ?>
<?php else : ?>
  <div class="prose"><?= markdown($content, 'text', $mod === 'preview'); ?></div>
<?php endif; ?>