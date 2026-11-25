<?php
/*
 * Оглавление, собираемое из заголовков статьи.
 * Expects: $item (array)
 */
$slugify = new \Cocur\Slugify\Slugify();
$tocLines = [];
$seen = [];
foreach (explode("\n", (string)($item['item_content'] ?? '')) as $line) {
    if (preg_match('/^(#{2,3})\s+(.+)$/u', trim($line), $m)) {
        $text = trim($m[2]);
        $anchor = $slugify->slugify(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($anchor === '') {
            $anchor = 'section';
        }
        $base = $anchor;
        $n = 2;
        while (in_array($anchor, $seen, true)) {
            $anchor = $base . '-' . $n++;
        }
        $seen[] = $anchor;
        $tocLines[] = [
            'level'  => strlen($m[1]),
            'text'   => $text,
            'anchor' => $anchor,
        ];
    }
}
if (empty($tocLines)) {
    return;
}
?>
<nav class="toc" aria-label="<?= __('app.article_toc_label'); ?>">
  <b><?= __('app.content'); ?></b>
  <?php foreach ($tocLines as $tl) : ?>
    <a class="<?= $tl['level'] === 3 ? 'toc-sub' : ''; ?>" href="#<?= htmlEncode($tl['anchor']); ?>"><?= htmlEncode($tl['text']); ?></a>
  <?php endforeach; ?>
</nav>
