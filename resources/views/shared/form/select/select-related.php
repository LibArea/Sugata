<?php
/*
 * Связанные факты и статьи (связь "факт ↔ статья").
 *
 * Ожидает: $data['related_arr'] (массив уже выбранных связанных элементов)
 *          для edit-формы — в формате [['id' => X, 'value' => 'Заголовок', 'item_type' => 'fact'], ...]
 */
$relatedSelected = [];
if (!empty($data['related_arr'])) {
    foreach ($data['related_arr'] as $r) {
        if (is_array($r) && !empty($r['id'])) {
            $relatedSelected[] = [
                'id'        => $r['id'],
                'value'     => $r['value'] ?? $r['item_title'] ?? ('#' . $r['id']),
                'item_type' => $r['item_type'] ?? 'fact',
            ];
        }
    }
}
?>
<fieldset class="form-big">
  <div class="form-label input-label"><label><?= __('app.related_items'); ?></label></div>
  <div class="form-element">
    <input name="related_ids" id="related_ids" placeholder="<?= __('app.related_items_help'); ?>">
    <div class="help"><?= __('app.related_items_help'); ?></div>
  </div>
</fieldset>

<script src="/assets/js/tag/tagify.min.js"></script>
<link rel="stylesheet" href="/assets/js/tag/tagify.css" type="text/css">

<script nonce="<?= config('main', 'nonce'); ?>">
  document.addEventListener("DOMContentLoaded", function () {
    var input = document.querySelector('#related_ids');
    if (!input || typeof Tagify === 'undefined') return;

    var tagify_related = new Tagify(input, {
      skipInvalid: true,
      enforceWhitelist: true,
      maxTags: 30,
      dropdown: { maxItems: 30, classname: 'tagify--related', enabled: 1, closeOnSelect: true, position: 'text' },
      whitelist: <?= json_encode(array_map(static fn ($r) => ['id' => (string)$r['id'], 'value' => (string)$r['value'], 'item_type' => $r['item_type'] ?? 'fact'], $relatedSelected), JSON_UNESCAPED_UNICODE); ?>
    });

    var controller = null;

    // AJAX-поиск по вводу: не зависит от dropdown:show, поэтому работает с пустым стартовым списком
    tagify_related.on('input', function (e) {
      var value = (e.detail.value || '').trim();

      if (value.length < 2) {
        tagify_related.settings.whitelist = [];
        tagify_related.dropdown.hide();
        return;
      }

      if (controller) controller.abort();
      controller = new AbortController();
      tagify_related.loading(true);

      const ctxType = document.querySelector('[name="content_kind"]')?.value || 'fact';
      const targetType = 'article';
      const excludeId = document.querySelector('input[name="item_id"]')?.value || '';
      fetch('/mod/admin/search/select/item', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ q: value, root: targetType, exclude_id: excludeId }),
        signal: controller.signal
      })
        .then(function (r) { return r.json(); })
        .then(function (list) {
          tagify_related.loading(false);
          tagify_related.settings.whitelist = Array.isArray(list) ? list : [];
          tagify_related.dropdown.show.call(tagify_related, value);
        })
        .catch(function () {
          tagify_related.loading(false);
        });
    });

    <?php foreach ($relatedSelected as $r) : ?>
      tagify_related.addTags([{
        id: '<?= (int)$r['id']; ?>',
        value: '<?= htmlspecialchars((string)$r['value'], ENT_QUOTES); ?>'
      }]);
    <?php endforeach; ?>
  });
</script>