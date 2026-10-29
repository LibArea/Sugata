<main class="content">
  <?= insert('/shared/navigation/breadcrumbs', ['list' => $data['breadcrumb']]);
  $item = $data['item'];
  $dir = preg_split('/(@)/', (string)$item['facet_list'] ?? false);
  ?>

  <h2 class="title"><?= __('app.view'); ?></h2>

  <article>
    <?php if (!empty($item['item_thumb_img'])) : ?>
      <div class="mb1">
        <img class="w-100" src="<?= Img::PATH['thumbs'] . $item['item_thumb_img']; ?>" alt="<?= htmlEncode($item['item_title']); ?>">
      </div>
    <?php endif; ?>

    <h1 class="title"><?= htmlEncode($item['item_title']); ?>
      <sup>
        <a class="ml5 gray" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
          <svg class="icon text-sm"><use xlink:href="/assets/svg/icons.svg#edit"></use></svg>
        </a>
      </sup>
    </h1>

    <?= markdown($item['item_content'], 'text', true); ?>

    <?php if (!empty($item['item_source_title'])) : ?>
      <div class="mt1 mb1 gray">
        <?= __('app.source') ?>: <a class="gray" href="<?= $item['item_source_url']; ?>" rel="nofollow"><?= $item['item_source_title']; ?></a>
      </div>
    <?php endif; ?>
  </article>

  <?php if (!empty($data['related'])) : ?>
    <h3 class="title"><?= $relTitle ?? __('app.related_articles'); ?></h3>
    <ol class="stories">
      <?php foreach ($data['related'] as $value) :
        $rDir = preg_split('/(@)/', (string)($value['facet_list'] ?? ''));
        $rPath = trim($rDir[2] ?? '', '/'); ?>
        <li class="story">
          <div class="story_liner">
            <div class="link"><a href="<?= urlItem($rPath, $value['item_slug']); ?>"><?= htmlEncode($value['item_title']); ?></a></div>
            <div class="story_content"><?php $arr = Parser::cut((string)($value['item_content'] ?? '')); echo markdown($arr['content'], 'text'); ?></div>
            <div class="byline">
              <?= HTML::facetDir($value['facet_list'] ?? ''); ?>
              <span class="divider">·</span>
              <span class="lowercase"><?= langDate($value['item_date']); ?></span>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>

  <?php if ($data['similar']) : ?>
    <h3 class="title"><?= __('app.recommended'); ?></h3>
    <ol class="stories">
      <?php foreach ($data['similar'] as $value) :
        $fields = json_decode($value['url'], true); ?>
        <li class="story">
          <div class="story_liner">
            <div class="link"><a href="<?= urlItem(isset($fields['facets']) ? trim(explode('@', $fields['facets'])[2] ?? $fields['facets'], '/') : '', $fields['slug'] ?? '', 'preview'); ?>"><?= $value['title']; ?></a></div>
            <div class="story_content"><?= $value['snippet']; ?> <?= $value['snippet2']; ?></div>
            <div class="byline">
              <?= HTML::facetDir($fields['facets']); ?>
              <span class="divider">·</span>
              <span class="lowercase"><?= langDate($value['added_at']); ?></span>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>

</main>