<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<main class="content">
  <?= insert('/shared/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

  <article>
    <h1 class="title"><?= htmlEncode($item['item_title']); ?>
      <?php if (!empty($preview)) : ?>
        <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
          <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
        </a>
      <?php endif; ?>
    </h1>

    <div class="hint mb1">
      <?= Html::facetDir($item['facet_list'] ?? '', !empty($preview) ? 'preview' : 'static'); ?>
      <span class="lowercase"><?= langDate($item['item_date']); ?></span>
    </div>

    <?php if (!empty($item['item_thumb_img'])) : ?>
      <div class="mb1">
        <img class="w-100" src="<?= htmlEncode(Img::PATH['thumbs'] . $item['item_thumb_img']); ?>" alt="<?= htmlEncode($item['item_title']); ?>">
      </div>
    <?php endif; ?>

    <?php if (!empty($faq)) : ?>
      <section class="mb1" aria-label="FAQ">
        <h2 class="title"><?= __('app.faq'); ?></h2>
        <?php foreach ($faq as $qa) : ?>
          <details>
            <summary><?= htmlEncode($qa['question']); ?></summary>
            <div><?= markdown($qa['answer']); ?></div>
          </details>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>

    <?= insert('/site/components/toc', ['item' => $item]); ?>

    <div>
      <?= markdown($item['item_content'], 'text', !empty($preview)); ?>
    </div>

    <?php $srcUrl = trim((string)($item['item_source_url'] ?? '')); ?>
    <?php if (!empty($item['item_source_title']) || $srcUrl !== '') : ?>
      <div class="mt1 mb1 gray">
        <?= __('app.source'); ?>:
        <?php if ($srcUrl !== '') : ?>
          <a class="gray" href="<?= htmlEncode($srcUrl); ?>" rel="nofollow"><?= htmlEncode(!empty($item['item_source_title']) ? $item['item_source_title'] : $srcUrl); ?></a>
        <?php else : ?>
          <?= htmlEncode($item['item_source_title']); ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </article>

  <?php if (empty($preview)) : ?>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": <?= json_encode($item['item_title'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
    "datePublished": <?= json_encode($item['item_date'], JSON_UNESCAPED_SLASHES); ?>,
    "dateModified": <?= json_encode($item['item_modified'] ?? $item['item_date'], JSON_UNESCAPED_SLASHES); ?>,
    "url": <?= json_encode(config('general', 'url_html') . '/' . trim($dir[2] ?? '', '/') . '/' . $item['item_slug'] . '.html', JSON_UNESCAPED_SLASHES); ?>
    <?php if (!empty($item['item_thumb_img'])) : ?>
    ,"image": <?= json_encode(config('general', 'url_html') . '/' . $item['item_thumb_img'], JSON_UNESCAPED_SLASHES); ?>
    <?php endif; ?>
    <?php if (!empty($item['item_source_url'])) : ?>
    ,"citation": <?= json_encode($item['item_source_url'], JSON_UNESCAPED_SLASHES); ?>
    <?php endif; ?>
    ,"publisher": {
      "@type": "Organization",
      "name": <?= json_encode(config('general', 'site_name'), JSON_UNESCAPED_UNICODE); ?>
    }
  }
  </script>
  <?php endif; ?>

  <?php
    $related = $related ?? [];
    if (!empty($related)) :
      echo insert('/shared/related', ['related' => $related, 'title' => __('app.related_articles'), 'accent' => true, 'preview' => !empty($preview)]);
    endif;

    $linkedFacts = \App\Models\ItemModel::getFactsFromContent($item['item_content'] ?? '');
    if (!empty($linkedFacts)) :
      echo insert('/shared/related', ['related' => $linkedFacts, 'title' => __('app.related_facts'), 'plain' => true, 'thumb' => true, 'preview' => !empty($preview)]);
    endif;
  ?>

  <?php if (!empty($wikiTerms)) : ?>
    <h3 class="title"><?= __('app.wiki_terms'); ?></h3>
    <?php foreach ($wikiTerms as $term) : ?>
      <span class="tag"><a href="<?= urlItem($term['path'], $term['slug'], !empty($preview) ? 'preview' : 'static'); ?>"><?= htmlEncode($term['title']); ?></a></span>
    <?php endforeach; ?>
  <?php endif; ?>

  <?= insert('/site/components/backlinks', ['backlinks' => $backlinks ?? [], 'preview' => !empty($preview)]); ?>
</main>

<script nonce="<?= config('main', 'nonce'); ?>">
  let text = document.getElementById("inputText");
  let btn = document.getElementById("copyText");
  if (text && btn) {
    btn.onclick = function() {
      text.select();
      document.execCommand("copy");
    }
  }
</script>

<?php if (!empty($preview)) : ?>
  <?= insert('/site/layouts/footer', ['preview' => true]); ?>
<?php else : ?>
  <!--#include virtual="/assets/footer.shtml"-->
<?php endif; ?>