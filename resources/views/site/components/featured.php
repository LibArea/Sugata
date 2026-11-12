<?php
/*
 * Главный блок: главный/избранный факт дня.
 * Expects: $featured (array|null), $bestFact (array|null), $preview (bool), $mod (string)
 */
$preview  = $preview ?? false;
$bestFact = $bestFact ?? ($featured ?? null);
$mod      = $mod ?? ($preview ? 'preview' : 'static');
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
    <div class="section-head">
      <h2><?= __('app.best_fact'); ?></h2>
      <a href="<?= $path; ?>"><?= __('app.read_more'); ?> →</a>
    </div>
    <h3><a href="<?= $path; ?>"><?= htmlEncode($bestFact['item_title']); ?></a></h3>
    <div class="prose"><?= markdown(implode("\n\n", $keep), 'text', $preview); ?></div>
  </section>
<?php endif; ?>

<?php if (!empty($featured) && $featured['item_id'] !== ($bestFact['item_id'] ?? null)) : ?>
  <section class="mb1">
    <?php
    $dir  = preg_split('/(@)/', (string)($featured['facet_list'] ?? ''));
    $path = urlItem(isset($dir[2]) ? trim($dir[2], '/') : '', (string)$featured['item_slug'], $mod);
    ?>
    <div class="section-head">
      <h2><?= __('app.fact_of_the_day'); ?></h2>
      <a href="<?= $path; ?>"><?= __('app.read_more'); ?> →</a>
    </div>
    <h3><a href="<?= $path; ?>"><?= htmlEncode($featured['item_title']); ?></a></h3>
    <p><?= htmlEncode(Parser::noHTML($featured['item_content'], 400)); ?></p>
  </section>
<?php endif; ?>
