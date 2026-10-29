<?php
/*
 * Главный блок: главный/избранный факт дня на нативных классах.
 * Expects: $featured (array|null), $bestFact (array|null), $preview (bool), $mod (string)
 */
$preview = $preview ?? false;
$bestFact = $bestFact ?? ($featured ?? null);
$mod = $mod ?? ($preview ? 'preview' : 'static');
?>
<?php if (!empty($bestFact)) : ?>
  <section class="mb1">
    <?php
    $dir  = preg_split('/(@)/', (string)($bestFact['facet_list'] ?? ''));
    $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$bestFact['item_slug'], $mod);
    $blocks = preg_split('/\n\s*\n/', (string)$bestFact['item_content']);
    $keep = [];
    $len = 0;
    foreach ($blocks as $b) {
        $clean = trim(preg_replace('/[#>*_\[\]()!-]/u', '', $b));
        if ($len + mb_strlen($clean) > 800) break;
        $keep[] = $b;
        $len += mb_strlen($clean);
    }
    ?>
    <h2 class="title"><?= __('app.best_fact'); ?></h2>
    <h3><a href="<?= $path; ?>"><?= htmlEncode($bestFact['item_title']); ?></a></h3>
    <div><?= markdown(implode("\n\n", $keep), 'text', $preview); ?></div>
    <a class="bold" href="<?= $path; ?>"><?= __('app.read_more'); ?> →</a>
  </section>
<?php endif; ?>

<?php if (!empty($featured) && $featured['item_id'] !== ($bestFact['item_id'] ?? null)) : ?>
  <section class="mb1">
    <?php
    $dir  = preg_split('/(@)/', (string)($featured['facet_list'] ?? ''));
    $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$featured['item_slug'], $mod);
    ?>
    <h2 class="title"><?= __('app.fact_of_the_day'); ?></h2>
    <h3><a href="<?= $path; ?>"><?= htmlEncode($featured['item_title']); ?></a></h3>
    <p><?= htmlEncode(Parser::noHTML($featured['item_content'], 400)); ?></p>
    <a class="bold" href="<?= $path; ?>"><?= __('app.read_more'); ?> →</a>
  </section>
<?php endif; ?>