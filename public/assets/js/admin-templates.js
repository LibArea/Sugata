/*
 * Шаблоны: перетаскивание полей мышью и сохранение порядка.
 * Подключается на странице /mod/admin/templates/edit/{id}.
 */
(function () {
  function init() {
    var rows = document.querySelectorAll('tr[data-field-id]');
    if (!rows.length) {
      return;
    }

    var dragged = null;

    Array.prototype.forEach.call(rows, function (row) {
      row.setAttribute('draggable', 'true');

      row.addEventListener('dragstart', function () {
        dragged = row;
        row.classList.add('dragging');
      });

      row.addEventListener('dragend', function () {
        row.classList.remove('dragging');
        dragged = null;
      });

      row.addEventListener('dragover', function (event) {
        event.preventDefault();
      });

      row.addEventListener('drop', function (event) {
        event.preventDefault();
        if (!dragged || dragged === row) {
          return;
        }

        var tbody = row.parentNode;
        var rect = row.getBoundingClientRect();
        var after = (event.clientY - rect.top) > rect.height / 2;

        tbody.insertBefore(dragged, after ? row.nextSibling : row);
        saveOrder(tbody);
      });
    });
  }

  function saveOrder(tbody) {
    var ids = Array.prototype.map.call(tbody.querySelectorAll('tr[data-field-id]'), function (row) {
      return row.getAttribute('data-field-id');
    });

    var token = document.querySelector('input[name="_token"]');
    var templateField = document.querySelector('input[name="template_id"]');

    var body = new FormData();
    ids.forEach(function (id) {
      body.append('order[]', id);
    });
    if (templateField) {
      body.append('template_id', templateField.value);
    }
    if (token) {
      body.append('_token', token.value);
    }

    fetch('/mod/admin/templates/fields/order', {
      method: 'POST',
      body: body,
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

/* Показывать редактор вариантов только для типа select */
(function () {
  function initTypeToggle() {
    var type = document.getElementById('field_type');
    var wrap = document.getElementById('field-options-wrap');
    if (!type || !wrap) return;

    var sync = function () {
      wrap.hidden = type.value !== 'select';
    };

    type.addEventListener('change', sync);
    sync();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTypeToggle);
  } else {
    initTypeToggle();
  }
})();