<link rel="stylesheet" href="/assets/js/editor/cherry-markdown.min.css" type="text/css">
<link as="font" href="/assets/js/editor/ch-icon.woff2">
<?php if (!empty($title)) : ?><?= $title; ?>:<?php endif; ?>

<div name="item_content" id="markdown-container"></div>
<textarea id="source" class="none"><?php if (!empty($content)) : ?><?= $content; ?><?php endif; ?></textarea>

<script src="/assets/js/editor/cherry-markdown.core.js"></script>
<!--script src="/assets/js/editor/editorjs-like.js"></script-->
<script nonce="<?= config('main', 'nonce'); ?>">
	async function fileUpload(file, callback) {
		const formData = new FormData();
		formData.append('file', file);

		try {
			const response = await fetch('/mod/admin/backend/upload/facet-telo/<?= $id; ?>', {
				method: 'POST',
				body: formData
			});
			const data = await response.json();
			callback(data.data.filePath);
		} catch (err) {
			console.error('Ошибка:', err);
		}
	};

	var customMenuA = Cherry.createMenuHook('{cut}', {
		iconName: '',
		onClick: function(selection) {
			return `{cut}`;
		}
	});

	var customMenuB = Cherry.createMenuHook('<?= __('app.spoiler'); ?>', {
		iconName: 'help',
		subMenuConfig: [{
				noIcon: true,
				name: 'ℹ️ note',
				onclick: (event) => {
					return cherry.insert('::: note \n <?= __('app.write_text'); ?> \n:::');
				}
			}, {
				noIcon: true,
				name: '💡 tip',
				onclick: (event) => {
					return cherry.insert('::: tip \n <?= __('app.write_text'); ?> \n:::');
				}
			},
			{
				noIcon: true,
				name: '⚠️ warning',
				onclick: (event) => {
					return cherry.insert('::: warning \n <?= __('app.write_text'); ?> \n:::');
				}
			},
			{
				noIcon: true,
				name: '📸 foto',
				onclick: (event) => {
					return cherry.insert('::: foto \n IMG  \n _<?= __('app.write_text'); ?>_ \n:::');
				}
			},
		]
	});

var customMenuC = Cherry.createMenuHook('factlink', {
	iconName: 'link',
	onClick: function(selection) {
		// Сохраняем выделенный текст, чтобы использовать его как подпись ссылки
		window.__selectedText = '';
		try {
			var cm = window.cherry && window.cherry.getCodeMirror ? window.cherry.getCodeMirror() : null;
			if (cm) {
				if (cm.state && cm.state.selection) {
					// CodeMirror 6
					var sel = cm.state.selection.main;
					window.__selectedText = cm.state.doc.sliceString(sel.from, sel.to);
				} else if (typeof cm.getSelection === 'function') {
					window.__selectedText = cm.getSelection() || '';
				}
			}
		} catch (e) {}
		var modal = document.getElementById('fact-search-modal');
		if (modal) modal.classList.add('block');
		var input = document.getElementById('fact-search-input');
		if (input) input.focus();
		return false;
	}
});

var cherry = new Cherry({
		id: 'markdown-container',
		value: document.getElementById("source").value,

		fileUpload: fileUpload,

		fileTypeLimitMap: {
			image: 'image/*',
		},
		multipleFileSelection: {
			image: false,
		},

        <?= insert('/shared/form/editor/localization'); ?>

		togglePreview: false,

		editor: {
			name: 'item_content',
			defaultModel: 'editOnly', // edit&preview|editOnly|previewOnly
			height: '<?= $height; ?>',
			showSuggestList: false,
			autoSave2Textarea: true, // синхронизировать контент в textarea перед отправкой
		},

		toolbars: {
			customMenu: {
				cut: customMenuA,
				help: customMenuB,
				factlink: customMenuC,
			},

			// Определим верхнюю панель инструментов
			toolbar: ['switchModel', '|', 'bold', 'italic', 'strikethrough', 'image', '|', 'cut', 'help', 'factlink'],
			
			// Выключить верхнюю панель
			// showToolbar: false,
			
			toolbarRight: ['fullScreen'],

			// Выключить верхнюю панель
			// showToolbar: false,
			// Определим боковую панель, по умолчанию она пуста
			// sidebar: ['togglePreview'],

			// Плавающую панель инструментов, при выделении текста
			bubble: ['bold', 'italic', 'strikethrough', 'quote', 'table', 'inlineCode', 'factlink'],
			// Панель подсказок
			float: ['h2', 'h3', '|', 'checklist', 'quote', 'table', 'code', 'image'],
},
});

	window.cherry = cherry;
</script>

<div id="fact-search-modal" class="fact-search-modal">
  <div class="fact-search-modal__box">
    <div class="flex justify-between items-center mb10">
      <b><?= __('app.fact_link'); ?></b>
      <button type="button" class="btn btn-small" id="fact-search-close">✕</button>
    </div>
    <input class="w-100 mb10" id="fact-search-input" type="text" placeholder="<?= __('app.fact_search_placeholder'); ?>" autocomplete="off">
    <div id="fact-search-results" class="fact-search-results"></div>
  </div>
</div>

<script nonce="<?= config('main', 'nonce'); ?>">
  (function () {
    var modal = document.getElementById('fact-search-modal');
    var input = document.getElementById('fact-search-input');
    var results = document.getElementById('fact-search-results');
    var closeBtn = document.getElementById('fact-search-close');
    if (!modal || !input || !results) return;

    function hide() {
      modal.classList.remove('block');
      results.innerHTML = '';
    }
    if (closeBtn) closeBtn.addEventListener('click', hide);
    modal.addEventListener('click', function (e) {
      if (e.target === modal) hide();
    });

    var t;
    input.addEventListener('input', function () {
      clearTimeout(t);
      t = setTimeout(search, 250);
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') hide();
    });

    function search() {
      var q = input.value.trim();
      if (q.length < 2) { results.innerHTML = ''; return; }

      fetch('/mod/admin/search/select/fact', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'q=' + encodeURIComponent(q)
      })
        .then(function (r) { return r.json(); })
        .then(function (list) {
          if (!Array.isArray(list) || !list.length) {
            results.innerHTML = '<div class="gray-600">—</div>';
            return;
          }
          var html = '';
          list.forEach(function (item) {
            html += '<button type="button" class="fact-search-item" data-url="' + (item.url || '') + '" data-title="' + (item.value || '').replace(/"/g, '&quot;') + '">' + (item.value || '') + '</button>';
          });
          results.innerHTML = html;

          results.querySelectorAll('.fact-search-item').forEach(function (btn) {
            btn.addEventListener('click', function () {
              var url = btn.getAttribute('data-url');
              // Подпись ссылки: выделенный текст, иначе заголовок статьи
              var sel = (window.__selectedText || '').trim();
              var title = sel !== '' ? sel : (btn.getAttribute('data-title') || url);
              var md = '[' + title + '](' + url + ')';

              var cm = window.cherry && window.cherry.getCodeMirror ? window.cherry.getCodeMirror() : null;
              if (cm && typeof cm.replaceSelection === 'function') {
                cm.replaceSelection(md);
              } else if (cm && typeof cm.dispatch === 'function' && cm.state) {
                // CodeMirror 6 (Cherry 0.11+): замена выделения через dispatch
                var tr = { changes: { from: cm.state.selection.main.from, to: cm.state.selection.main.to, insert: md } };
                cm.dispatch(tr);
              } else if (window.cherry && typeof window.cherry.insert === 'function') {
                window.cherry.insert(md);
              }
              hide();
            });
          });
        })
        .catch(function () { results.innerHTML = ''; });
    }
  })();
</script>