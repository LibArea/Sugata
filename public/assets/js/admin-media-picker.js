/*
 * Выбор фото для полей шаблона (инфобокс).
 * Загрузка, галерея загруженных файлов, «взять из текста», очистка.
 */
(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  function csrfToken() {
    var token = document.querySelector('input[name="_token"]');
    return token ? token.value : null;
  }

  ready(function () {
    var pickers = document.querySelectorAll('.media-picker');
    if (!pickers.length) {
      return;
    }

    var modal = null;

    function buildModal() {
      modal = document.createElement('div');
      modal.className = 'media-modal';
      modal.innerHTML =
        '<div class="media-modal__box">' +
          '<div class="media-modal__head"><b>Фото</b>' +
            '<button type="button" class="btn media-modal__close">×</button></div>' +
          '<div class="media-modal__grid"></div>' +
        '</div>';
      document.body.appendChild(modal);

      modal.addEventListener('click', function (event) {
        if (event.target === modal || event.target.classList.contains('media-modal__close')) {
          modal.style.display = 'none';
        }
      });
    }

    function setValue(picker, path) {
      var input = picker.querySelector('.media-picker__input');
      var preview = picker.querySelector('.media-picker__preview');

      if (input) {
        input.value = path || '';
      }

      if (preview) {
        preview.innerHTML = path ? '<img src="' + path + '" alt="">' : '';
      }
    }

    function renderGallery(picker, items) {
      if (!modal) {
        buildModal();
      }

      var grid = modal.querySelector('.media-modal__grid');
      grid.innerHTML = '';

      if (!items.length) {
        grid.innerHTML = '<div class="hint">Пусто — загрузите фото кнопкой «Загрузить».</div>';
      }

      items.forEach(function (item) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'media-modal__item';
        button.innerHTML = '<img src="' + item.path + '" alt=""><span>' + (item.caption || item.alt || '') + '</span>';

        button.addEventListener('click', function () {
          setValue(picker, item.path);
          modal.style.display = 'none';
        });

        grid.appendChild(button);
      });

      modal.style.display = 'flex';
    }

    function openGallery(picker) {
      fetch('/mod/admin/media/list', {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (response) { return response.json(); })
        .then(function (data) { renderGallery(picker, data.items || []); })
        .catch(function () { renderGallery(picker, []); });
    }

    Array.prototype.forEach.call(pickers, function (picker) {
      var fileInput = picker.querySelector('.media-picker__file');

      picker.querySelector('.media-picker__upload').addEventListener('click', function () {
        fileInput.click();
      });

      picker.querySelector('.media-picker__choose').addEventListener('click', function () {
        openGallery(picker);
      });

      picker.querySelector('.media-picker__clear').addEventListener('click', function () {
        setValue(picker, '');
      });

      var fromText = picker.querySelector('.media-picker__text');
      if (fromText) {
        fromText.addEventListener('click', function () {
          setValue(picker, picker.getAttribute('data-from-text') || '');
        });
      }

      fileInput.addEventListener('change', function () {
        if (!fileInput.files || !fileInput.files[0]) {
          return;
        }

        var body = new FormData();
        body.append('file', fileInput.files[0]);

        var token = csrfToken();
        if (token) {
          body.append('_token', token);
        }

        fetch('/mod/admin/backend/upload/facet-telo/' + picker.getAttribute('data-item'), {
          method: 'POST',
          body: body,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(function (response) { return response.json(); })
          .then(function (data) {
            var path = data && data.data ? data.data.filePath : null;
            if (path) {
              setValue(picker, path);
            }
          })
          .catch(function () { /* загрузка не удалась — путь остаётся прежним */ });

        fileInput.value = '';
      });
    });
  });
})();
