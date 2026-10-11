<?php
/*
 * Зависимый выбор категорий.
 *
 * Родительский контрол — переключатель типа (Факт/Статья/Страница, поле content_kind).
 * При смене типа список категорий перезаполняется с сервера (только нужный корень).
 *
 * Ожидает: $root (facts|articles|info), $action ('add'|'edit'),
 *          для edit — $data['category_arr'] (массив выбранных категорий).
 */
$selRoot = $root ?? 'facts';

// Предвыбранные id (для редактирования)
$selectedIds = [];
if (($action ?? 'add') === 'edit' && !empty($data['category_arr'])) {
    foreach ($data['category_arr'] as $c) {
        if (is_array($c) && !empty($c['id'])) {
            $selectedIds[] = (int)$c['id'];
        }
    }
}
?>
<select id="category_id" name="facet_select[]" multiple size="8" class="category-select" required></select>
<input type="hidden" id="category_root" value="<?= htmlEncode($selRoot); ?>">
<div class="help"><?= __('app.category_select_help'); ?></div>

<script nonce="<?= config('main', 'nonce'); ?>">
  (function () {
    var selectEl = document.getElementById('category_id');
    var rootEl   = document.getElementById('category_root');
    if (!selectEl) return;

    var selectedIds = <?= json_encode(array_map('strval', $selectedIds)); ?>;

    function fill(list) {
      selectEl.innerHTML = '';
      (list || []).forEach(function (item) {
        var opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.value;

        // Разбор значения для группировки по корню (сортировка: короткие пути выше)
        if (selectedIds.indexOf(String(item.id)) !== -1) {
          opt.selected = true;
        }
        selectEl.appendChild(opt);
      });
    }

    function loadCategories(keepSelected) {
      var root = rootEl ? rootEl.value : 'facts';
      fetch('/mod/admin/search/select/category', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ q: '', root: root })
      })
        .then(function (r) { return r.json(); })
        .then(function (list) { fill(list); })
        .catch(function () {});
    }

    loadCategories();

    // Смена типа контента перезаполняет список категорий нужного корня
    document.addEventListener('change', function (e) {
      if (e.target && e.target.name === 'content_kind') {
        var kind = e.target.value;
        if (rootEl) rootEl.value = (kind === 'article') ? 'articles' : (kind === 'page' ? 'info' : 'facts');
        selectedIds = [];
        loadCategories(false);
      }
    });
  })();
</script>
