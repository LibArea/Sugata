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

// --- Подсветка вики-ссылок [[...]] в превью (синяя — статья есть, красная — нет) ---
if (typeof window.__wikiLinkStatus === 'undefined') {
	window.__wikiLinkStatus = {}; // цель -> 1|0
	window.__wikiLinkTimer = null;
}

// Собрать уникальные цели из markdown-строки
function collectWikiTargets(md) {
	var targets = {};
	var re = /\[\[([^\[\]|]+?)(?:\|([^\[\]]+?))?\]\]/g;
	var m;
	while ((m = re.exec(md)) !== null) {
		var target = (m[2] || m[1] || '').trim();
		if (target) targets[target] = 1;
	}
	return Object.keys(targets);
}

// Запросить статусы целей у сервера
function refreshWikiStatus(cherryInst, md) {
	var list = collectWikiTargets(md);
	if (!list.length) { window.__wikiLinkStatus = {}; return; }
	var body = list.map(function (t) { return 'terms[]=' + encodeURIComponent(t); }).join('&');
	fetch('/mod/admin/editor/check-wikilinks', {
		method: 'POST',
		credentials: 'same-origin',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: body
	}).then(function (r) {
		if (!r.ok) throw new Error('HTTP ' + r.status);
		return r.json();
	}).then(function (res) {
		window.__wikiLinkStatus = res || {};
		// Перекрасить уже отрисованные спаны напрямую (Cherry кэширует по data-sign и не перерисовывает их)
		window.__cherryPreviewDom = window.__cherryPreviewDom
			|| document.querySelector('#markdown-container .cherry-previewer')
			|| document.getElementById('markdown-container')
			|| document;
		repaintWikiLinks();
	}).catch(function (e) {
		console.error('check-wikilinks error:', e && e.message ? e.message : e);
	});
}

// Хук: красит [[...]] в превью согласно статусу
var WikiLinkHook = Cherry.createSyntaxHook('wikiLink', Cherry.constants.HOOKS_TYPE_LIST.SEN, {
	makeHtml(str) {
		var that = this;
		return str.replace(this.RULE.reg, function (whole, m1, m2) {
			var text = (m1 || '').trim();
			var target = (m2 || m1 || '').trim();
			var ok = window.__wikiLinkStatus[target] === 1;
			return '<span class="wikilink ' + (ok ? 'wikilink-ok' : 'wikilink-missing') + '" data-target="' + target.replace(/"/g, '&quot;') + '" title="' + (ok ? 'Существует' : 'Статья не создана') + '">' + text + '</span>';
		});
	},
	rule(str) {
		return { reg: /\[\[([^\[\]|]+?)(?:\|([^\[\]]+?))?\]\]/g };
	}
});

// Перекрасить уже отрисованные спаны .wikilink по текущему статусу (без перерендера Cherry)
function repaintWikiLinks() {
	var root = window.__cherryPreviewDom;
	if (!root) return;
	var spans = root.querySelectorAll('.wikilink');
	for (var i = 0; i < spans.length; i++) {
		var t = spans[i].getAttribute('data-target') || '';
		var ok = window.__wikiLinkStatus[t] === 1;
		var cls = 'wikilink wikilink-' + (ok ? 'ok' : 'missing');
		spans[i].className = cls;
		spans[i].setAttribute('title', ok ? 'Существует' : 'Статья не создана');
	}
}

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

		togglePreview: true,

		engine: {
			customSyntax: {
				wikiLink: {
					syntaxClass: WikiLinkHook,
					force: true,
					before: 'fontEmphasis',
				},
			},
		},

		editor: {
			name: 'item_content',
			defaultModel: 'edit&preview',
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

	// Первичная проверка статусов вики-ссылок сразу после загрузки
	var initialMd = (document.getElementById('source') || {}).value || '';
	refreshWikiStatus(cherry, initialMd);

	// Пересборка при изменении текста (с дебаунсом)
	function getCurrentMd() {
		var cm = cherry.getCodeMirror ? cherry.getCodeMirror() : null;
		if (cm && cm.state && cm.state.doc) return cm.state.doc.toString();
		return '';
	}
	cherry.$event && cherry.$event.on('afterChange', function (evt) {
		clearTimeout(window.__wikiLinkTimer);
		window.__wikiLinkTimer = setTimeout(function () {
			refreshWikiStatus(cherry, getCurrentMd());
		}, 400);
	});
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