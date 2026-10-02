<?= insert('/templates/header', ['meta' => $meta, 'preview' => !empty($preview)]); ?>

<div class="content wiki-content">
  <div class="wiki-layout">
    <main class="wiki-layout__main">
      <?= insert('/_block/navigation/breadcrumbs', ['list' => $breadcrumb]); ?>

      <article>
        <header class="wiki-fact-header">
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

        <?= insert('/templates/components/toc', ['item' => $item]); ?>

        <div class="wiki-fact-body">
          <?= markdown($item['item_content']); ?>
        </div>

        <?php if (!empty($item['item_source_title'])) : ?>
          <div class="mt20 mb20 gray-600">
            <?= __('app.source'); ?>:
            <?php if (!empty($item['item_source_url'])) : ?>
              <a class="gray-600" href="<?= htmlEncode($item['item_source_url']); ?>" rel="nofollow"><?= htmlEncode($item['item_source_title']); ?></a>
            <?php else : ?>
              <?= htmlEncode($item['item_source_title']); ?>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </article>

      <fieldset class="copy">
        <input id="inputText" value="<?= isset($dir[2]) ? fact_slug($dir[2], $item['item_slug']) : ''; ?>">
        <button class="btn btn-primary" id="copyText">
          <svg class="icon" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M4 12.3V12c0-1.47.005-2.547.075-3.403.074-.904.216-1.482.454-1.949a4.85 4.85 0 0 1 2.12-2.12c.466-.237 1.045-.379 1.948-.453C9.51 4.001 10.675 4 12.3 4h4.512a2.073 2.073 0 0 0-.174-.49 2.4 2.4 0 0 0-1.048-1.048 2.439 2.439 0 0 0-.931-.239 13.48 13.48 0 0 0-1.026-.023H12.26c-1.576 0-2.814 0-3.81.081-1.014.083-1.856.255-2.619.644A6.65 6.65 0 0 0 2.925 5.83c-.389.763-.56 1.605-.644 2.62-.081.995-.081 2.233-.081 3.809v1.373c0 .392 0 .738.023 1.025.025.306.081.623.239.931a2.4 2.4 0 0 0 1.048 1.05c.162.082.326.136.49.173V12.3ZM10.08 6h-.035c-.52 0-.964 0-1.329.03-.382.03-.755.097-1.112.276a2.9 2.9 0 0 0-1.298 1.298c-.179.357-.246.73-.277 1.112C6 9.081 6 9.524 6 10.046v7.909c0 .52 0 .964.03 1.329.03.382.097.755.276 1.112a2.9 2.9 0 0 0 1.298 1.298c.357.179.73.246 1.112.277.365.029.808.029 1.33.029h6.909c.52 0 .964 0 1.329-.03.383-.03.755-.097 1.112-.276a2.9 2.9 0 0 0 1.298-1.298c.179-.357.246-.73.277-1.112.029-.365.029-.808.029-1.33v-7.909c0-.52 0-.964-.03-1.329-.03-.382-.097-.755-.276-1.112a2.9 2.9 0 0 0-1.298-1.298c-.357-.179-.73-.246-1.112-.277C17.919 6 17.476 6 16.954 6H10.08ZM8.408 7.916c.065-.032.179-.07.452-.093a17.65 17.65 0 0 1 1.22-.023h6.84c.565 0 .936 0 1.22.023.273.022.387.06.452.093a1.1 1.1 0 0 1 .492.492c.032.065.07.179.093.452.022.284.023.655.023 1.22v7.84c0 .565 0 .936-.023 1.22-.023.273-.06.387-.093.452a1.1 1.1 0 0 1-.492.492c-.065.032-.179.07-.452.093a17.65 17.65 0 0 1-1.22.023h-6.84c-.565 0-.936 0-1.22-.023-.273-.023-.387-.06-.452-.093a1.1 1.1 0 0 1-.492-.492c-.032-.065-.07-.18-.093-.452a17.057 17.057 0 0 1-.023-1.22v-7.84c0-.565 0-.936.023-1.22.022-.273.06-.387.093-.452a1.1 1.1 0 0 1 .492-.492Z" clip-rule="evenodd" />
          </svg>
        </button>
      </fieldset>

      <?php if ($similar) : ?>
        <h4 class="uppercase-box"><?= __('app.recommended'); ?></h4>
        <div class="wiki-fact-list wiki-mb4">
          <?php foreach ($similar as $value) :
            $fields = json_decode($value['url'], true); ?>
            <article class="wiki-fact-item">
              <div class="wiki-fact-item__body">
                <h3>
                  <a href="<?= urlItem(isset($fields['facets']) ? Html::facets_puth($fields['facets']) : '', $fields['slug'] ?? '', !empty($preview) ? 'preview' : 'static'); ?>">
                    <?= htmlEncode($value['title']); ?>
                  </a>
                </h3>
                <div class="wiki-fact-item__excerpt">
                  <?= $value['snippet']; ?> <?= $value['snippet2']; ?>
                </div>
                <div class="wiki-fact-item__meta">
                  <?= Html::facetDir($fields['facets'] ?? '', !empty($preview) ? 'preview' : 'static'); ?>
                  <span class="lowercase"><?= langDate($value['added_at']); ?></span>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </main>

    <aside class="wiki-layout__sidebar">
      <?= insert('/templates/layout/sidebar', ['preview' => !empty($preview), 'activePath' => $dir[2] ?? '']); ?>
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
  <?= insert('/templates/footer', ['preview' => true]); ?>
<?php else : ?>
  <!--#include virtual="/assets/footer.shtml"-->
<?php endif; ?>