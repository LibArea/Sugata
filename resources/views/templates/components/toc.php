<?php
/*
 * Table of contents built from markdown headings
 * Оглавление, собираемое из заголовков статьи
 *
 * Expects: $item (array)
 * Renders nothing when the article has no headings.
 */
$tocLines = [];
foreach (explode("\n", (string)($item['item_content'] ?? '')) as $line) {
    if (preg_match('/^(#{2,3})\s+(.+)$/u', trim($line), $m)) {
        $tocLines[] = [
            'level' => strlen($m[1]),
            'text'  => trim($m[2]),
        ];
    }
}
if (empty($tocLines)) {
    return;
}
?>
<div class="wiki-toc" role="navigation" aria-label="TOC">
  <div class="wiki-toc__title"><?= __('app.content'); ?></div>
  <ol>
    <?php foreach ($tocLines as $tl) : ?>
      <li style="margin-inline-start: <?= ($tl['level'] - 2) * 1; ?>rem;">
        <?= htmlEncode($tl['text']); ?>
      </li>
    <?php endforeach; ?>
  </ol>
</div>