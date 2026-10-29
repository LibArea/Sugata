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

  <fieldset class="copy">
    <label for="inputText"><?= __('app.copy'); ?> URL</label>
    <input id="inputText" type="text" readonly value="<?= isset($dir[2]) ? fact_slug($dir[2], $item['item_slug']) : ''; ?>">
    <button class="btn" id="copyText" aria-label="<?= __('app.copy'); ?> URL">
      <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
        <path fill-rule="evenodd" d="M4 12.3V12c0-1.47.005-2.547.075-3.403.074-.904.216-1.482.454-1.949a4.85 4.85 0 0 1 2.12-2.12c.466-.237 1.045-.379 1.948-.453C9.51 4.001 10.675 4 12.3 4h4.512a2.073 2.073 0 0 0-.174-.49 2.4 2.4 0 0 0-1.048-1.048 2.439 2.439 0 0 0-.931-.239 13.48 13.48 0 0 0-1.026-.023H12.26c-1.576 0-2.814 0-3.81.081-1.014.083-1.856.255-2.619.644A6.65 6.65 0 0 0 2.925 5.83c-.389.763-.56 1.605-.644 2.62-.081.995-.081 2.233-.081 3.809v1.373c0 .392 0 .738.023 1.025.025.306.081.623.239.931a2.4 2.4 0 0 0 1.048 1.05c.162.082.326.136.49.173V12.3ZM10.08 6h-.035c-.52 0-.964 0-1.329.03-.382.03-.755.097-1.112.276a2.9 2.9 0 0 0-1.298 1.298c-.179.357-.246.73-.277 1.112C6 9.081 6 9.524 6 10.046v7.909c0 .52 0 .964.03 1.329.03.382.097.755.276 1.112a2.9 2.9 0 0 0 1.298 1.298c.357.179.73.246 1.112.277.365.029.808.029 1.33.029h6.909c.52 0 .964 0 1.329-.03.383-.03.755-.097 1.112-.276a2.9 2.9 0 0 0 1.298-1.298c.179-.357.246-.73.277-1.112.029-.365.029-.808.029-1.33v-7.909c0-.52 0-.964-.03-1.329-.03-.382-.097-.755-.276-1.112a2.9 2.9 0 0 0-1.298-1.298c-.357-.179-.73-.246-1.112-.277C17.919 6 17.476 6 16.954 6H10.08ZM8.408 7.916c.065-.032.179-.07.452-.093a17.65 17.65 0 0 1 1.22-.023h6.84c.565 0 .936 0 1.22.023.273.022.387.06.452.093a1.1 1.1 0 0 1 .492.492c.032.065.07.179.093.452.022.284.023.655.023 1.22v7.84c0 .565 0 .936-.023 1.22-.023.273-.06.387-.093.452a1.1 1.1 0 0 1-.492.492c-.065.032-.179.07-.452.093a17.65 17.65 0 0 1-1.22.023h-6.84c-.565 0-.936 0-1.22-.023-.273-.023-.387-.06-.452-.093a1.1 1.1 0 0 1-.492-.492c-.032-.065-.07-.18-.093-.452a17.057 17.057 0 0 1-.023-1.22v-7.84c0-.565 0-.936.023-1.22.022-.273.06-.387.093-.452a1.1 1.1 0 0 1 .492-.492Z" clip-rule="evenodd" />
      </svg>
    </button>
  </fieldset>

  <?php
    $related = $related ?? [];
    if ($related) :
      $relTitle = (($item['item_type'] ?? 'fact') === 'fact')
          ? __('app.related_articles')
          : __('app.related_facts');
      echo insert('/shared/related', ['related' => $related, 'title' => $relTitle, 'accent' => true, 'preview' => !empty($preview)]);
    endif;
  ?>

  <?php if ($similar) : ?>
    <h3 class="title"><?= __('app.recommended'); ?></h3>
    <ol class="stories">
      <?php foreach ($similar as $value) :
        $fields = json_decode($value['url'], true); ?>
        <li class="story">
          <div class="story_liner">
            <div class="link"><a href="<?= urlItem(isset($fields['facets']) ? Html::facets_puth($fields['facets']) : '', $fields['slug'] ?? '', !empty($preview) ? 'preview' : 'static'); ?>"><?= htmlEncode($value['title']); ?></a></div>
            <div class="story_content"><?= preg_replace('/\[\[([^\]|]+)(?:\|[^\]]+)?\]\]/', '$1', $value['snippet']); ?> <?= $value['snippet2']; ?></div>
            <div class="byline">
              <?= Html::facetDir($fields['facets'] ?? '', !empty($preview) ? 'preview' : 'static'); ?>
              <span class="divider">·</span>
              <span class="lowercase"><?= langDate($value['added_at']); ?></span>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
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