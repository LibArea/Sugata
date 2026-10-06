<?php
$type = $data['type'];
$sw = $sw ?? '?';
?>

<main>

  <?php if (!empty($data['results'])) : ?>
    <div class="flex mb20">
      <?php foreach ($data['tags'] as $tag) : ?>
        <?php $url = url('topic', ['slug' => $tag['slug']]); ?>
        <a class="tag-yellow" href="<?= $url; ?>">
          <?= $tag['title']; ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($data['results'])) : ?>

    <div class="gray-600 flex items-center justify-between mb20">
      <?= __('search.results_search'); ?> <?= $data['count']; ?>
      <span><?= round($data['time'], 3); ?> мс.</span>
    </div>

    <?php foreach ($data['results'] as $result) : ?>

      <div class="mb20">
        <?php if (!empty($result['link'])) : ?>
          <a class="text-xl" href="<?= htmlEncode($result['link']); ?>">
            <?= $result['title']; ?>
          </a>
        <?php else : ?>
          <span class="text-xl"><?= $result['title']; ?></span>
        <?php endif; ?>
        <?php if ($type == 'comment') : ?>
          <?= fragment($result['comment_content'], 250); ?>
        <?php else : ?>
          <?php if (config('general', 'search_engine') == false) : ?>
            <div class="max-w-md"><?= fragment($result['content'], 250); ?></div>
          <?php else : ?>
            <div class="max-w-md"><?= $result['content']; ?></div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <?php $url = 'go?q=' . htmlEncode($data['q']) . '&cat=' . $data['type'] . ''; ?>

    <?= Html::pagination($data['pNum'], $data['pagesCount'], null, $url, '&'); ?>

  <?php else : ?>
    Результатов нет
  <?php endif; ?>
</main>