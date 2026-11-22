<main class="content">
  <div class="flex justify-between items-center mb20">
    <div>
      <h1 class="title"><?= htmlEncode($data['title'] ?? ''); ?></h1>
    </div>
  </div>

  <div class="hint mb20"><?= htmlEncode($data['text'] ?? ''); ?></div>
</main>