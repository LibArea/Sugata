<?= insert('/site/layouts/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<div class="content wiki-content">
  <div class="wiki-layout">
    <main class="wiki-layout__main">
      <?= insert('/shared/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

      <article class="wiki-article">
        <header class="wiki-article-header">
          <h1>
            <?= htmlEncode($item['item_title']); ?>
            <?php if (!empty($preview)) : ?>
              <a class="admin-icon-action" aria-label="<?= __('app.edit'); ?>" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
                <svg class="icon text-sm" aria-hidden="true"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
              </a>
            <?php endif; ?>
          </h1>
        </header>

        <?php if (!empty($item['item_thumb_img'])) : ?>
          <div class="box br-lightgray img-preview wiki-mb4">
            <img class="w-100" src="<?= htmlEncode(Img::PATH['thumbs'] . $item['item_thumb_img']); ?>" alt="<?= htmlEncode($item['item_title']); ?>">
          </div>
        <?php endif; ?>

        <div class="wiki-fact-meta">
          <?= Html::facetDir($item['facet_list'] ?? '', !empty($preview) ? 'preview' : 'static'); ?>
          <span class="lowercase"><?= langDate($item['item_date']); ?></span>
        </div>

        <?php if (!empty($faq)) : ?>
          <section class="wiki-faq" aria-label="FAQ">
            <h2 class="wiki-section-title"><?= __('app.faq'); ?></h2>
            <?php foreach ($faq as $qa) : ?>
              <details class="wiki-faq__item">
                <summary><?= htmlEncode($qa['question']); ?></summary>
                <div class="wiki-faq__answer"><?= markdown($qa['answer']); ?></div>
              </details>
            <?php endforeach; ?>
          </section>
        <?php endif; ?>

        <?= insert('/site/components/toc', ['item' => $item]); ?>

        <div class="wiki-fact-body">
          <?= markdown($item['item_content'], 'text', !empty($preview)); ?>
        </div>

        <?php $srcUrl = trim((string)($item['item_source_url'] ?? '')); ?>
        <?php if (!empty($item['item_source_title']) || $srcUrl !== '') : ?>
          <div class="mt20 mb20 gray-600">
            <?= __('app.source'); ?>:
            <?php if ($srcUrl !== '') : ?>
              <a class="gray-600" href="<?= htmlEncode($srcUrl); ?>" rel="nofollow"><?= htmlEncode(!empty($item['item_source_title']) ? $item['item_source_title'] : $srcUrl); ?></a>
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
        // РЎРІСЏР·Р°РЅРЅС‹Рµ СЃС‚Р°С‚СЊРё (СЂСѓС‡РЅР°СЏ СЃРІСЏР·СЊ: С„Р°РєС‚ в†” СЃС‚Р°С‚СЊСЏ). РЈ СЃС‚Р°С‚СЊРё РїРѕРєР°Р·С‹РІР°РµРј СЃРІСЏР·Р°РЅРЅС‹Рµ СЃС‚Р°С‚СЊРё.
        $related = $related ?? [];
        if (!empty($related)) :
          echo insert('/shared/related', ['related' => $related, 'title' => __('app.related_articles'), 'accent' => true, 'preview' => !empty($preview)]);
        endif;
      ?>

      <?php
        // Р¤Р°РєС‚С‹, РЅР° РєРѕС‚РѕСЂС‹Рµ СЃСЃС‹Р»Р°РµС‚СЃСЏ С‚РµРєСЃС‚ СЃС‚Р°С‚СЊРё.
        $linkedFacts = \App\Models\ItemModel::getFactsFromContent($item['item_content'] ?? '');
        if (!empty($linkedFacts)) :
          echo insert('/shared/related', ['related' => $linkedFacts, 'title' => __('app.related_facts'), 'plain' => true, 'thumb' => true, 'preview' => !empty($preview)]);
        endif;
      ?>

      <?php if (!empty($wikiTerms)) : ?>
        <h3 class="uppercase-box"><?= __('app.wiki_terms'); ?></h3>
        <div class="wiki-term-list">
          <?php foreach ($wikiTerms as $term) : ?>
            <a class="wiki-term" href="<?= urlItem($term['path'], $term['slug'], !empty($preview) ? 'preview' : 'static'); ?>">
              <?= htmlEncode($term['title']); ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </main>

    <aside class="wiki-layout__sidebar">
      <?= insert('/site/layouts/sidebar', ['sideNav' => $sideNav ?? [], 'sideRoot' => 'articles', 'preview' => !empty($preview), 'activePath' => $dir[2] ?? '']); ?>
    </aside>
  </div>
</div>

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