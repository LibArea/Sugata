<?php
$type = $data['type'];
$sw = $sw ?? '?';
?>

<main class="content">
  <h1 class="title"><?= __('app.search'); ?></h1>

  <?php if (!empty($data['results'])) : ?>
    <?php if ($data['tags']) : ?>
      <div class="mb1">
        <?php foreach ($data['tags'] as $tag) : ?>
          <a class="tag" href="<?= url('topic', ['slug' => $tag['slug']]); ?>"><?= $tag['title']; ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="gray mb1">
      <?= __('search.results_search'); ?> <?= $data['count']; ?>
      <span class="right"><?= round($data['time'], 3); ?> мс.</span>
    </div>

    <ol class="stories">
      <?php foreach ($data['results'] as $result) : ?>
        <li class="story">
          <div class="story_liner">
            <div class="link">
              <?php if (!empty($result['link'])) : ?>
                <a href="<?= htmlEncode($result['link']); ?>"><?= $result['title']; ?></a>
              <?php else : ?>
                <?= $result['title']; ?>
              <?php endif; ?>
            </div>
            <div class="story_content">
              <?php if ($type == 'comment') : ?>
                <?= fragment($result['comment_content'], 250); ?>
              <?php else : ?>
                <?= $result['content']; ?>
              <?php endif; ?>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>

    <?php $url = 'go?q=' . htmlEncode($data['q']) . '&cat=' . $data['type'] . ''; ?>
    <?= Html::pagination($data['pNum'], $data['pagesCount'], null, $url, '&'); ?>
  <?php else : ?>
    <p><?= __('app.no_content'); ?></p>
  <?php endif; ?>
</main>