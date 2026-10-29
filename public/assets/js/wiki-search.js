/**
 * Клиентский поиск по статике на MiniSearch с русским стеммером (Porter).
 * Индекс загружается из assets/js/search-index.js (window.__SEARCH_INDEX__).
 */
(function () {
  'use strict';

  if (typeof window.MiniSearch === 'undefined') return;
  if (typeof window.__SEARCH_INDEX__ === 'undefined') return;

  // ---- Русский стеммер (Porter-style, снежный) ----
  // Упрощённая реализация русского стеммера (алгоритм Портера/снежный).
  var PERFECTIVE_GERUND_1 = /((ив|ивши|ившись|ыв|ывши|ывшись)|((?<=[ая])(в|вши|вшись)))$/;
  var PERFECTIVE_GERUND_2 = /(ив|ивши|ившись|ыв|ывши|ывшись)$/;
  var ADJECTIVE = /(ее|ие|ые|ое|ими|ыми|ей|ий|ый|ой|ем|им|ым|ом|его|ого|ему|ому|их|ых|ую|юю|ая|яя|ою|ею)$/;
  var PARTICIPLE = /((ивш|ывш|ующ)|((?<=[ая])(ем|нн|вш|ющ|щ)))$/;
  var VERB = /((ила|ыла|ена|ейте|уйте|ите|или|ыли|ей|уй|ил|ыл|им|ым|ен|ило|ыло|ено|ят|ует|уют|ит|ыт|ены|ить|ыть|ишь|ую|ю)|((?<=[ая])(ла|на|ете|йте|ли|й|л|ем|н|ло|но|ет|ют|ны|ть|ешь|нно)))$/;
  var NOUN = /(а|ев|ов|ие|ье|е|иями|ями|ами|еи|ии|и|ией|ей|ой|ий|й|иям|ям|ием|ем|ам|ом|о|у|ах|иях|ях|ы|ь|ию|ью|ю|ия|ья|я)$/;
  var RVRE = /^(.*?[аеиоуыэюя])(.*)$/;
  var DERIVATIONAL = /[^аеиоуыэюя][аеиоуыэюя]+[^аеиоуыэюя]+[аеиоуыэюя].*(?<=о)сть?$/;

  function stems(word) {
    // нижний регистр
    word = word.toLowerCase().replace(/ё/g, 'е');
    var m = RVRE.exec(word);
    if (!m) return word;
    var pre = m[1];
    var rv = m[2];

    // РЕ: удаление окончания
    var wordAfterRegex = function (regex) {
      var match = rv.match(regex);
      if (match) {
        rv = rv.replace(regex, '');
        return true;
      }
      return false;
    };

    // шаг 1
    if (wordAfterRegex(PERFECTIVE_GERUND_1)) {
      // nothing
    } else {
      wordAfterRegex(PERFECTIVE_GERUND_2);
    }
    // (не обрабатываем отдельно — упрощение для точности)

    var wordCopy = pre + rv;
    m = RVRE.exec(wordCopy);
    if (!m) return word;
    pre = m[1];
    rv = m[2];

    // после ре — приставка: удаление окончания прилагательного
    if (wordAfterRegex(ADJECTIVE)) {
      wordAfterRegex(PARTICIPLE);
    } else {
      // глагол
      if (wordAfterRegex(VERB)) {
        // 
      } else {
        // существительное
        wordAfterRegex(NOUN);
      }
    }

    // шаг 3: деривационный суффикс
    if (DERIVATIONAL.test(pre + rv)) {
      rv = rv.replace(/ость?$/, '');
    }

    // шаг 4: финальная — убрать конечные и/ь
    rv = rv.replace(/(и|ь)$/, '');

    return pre + rv;
  }

  function russianStemTerm(term) {
    var tokens = term.toLowerCase().replace(/ё/g, 'е').split(/[^a-zа-яё0-9]+/);
    return tokens.filter(function (t) { return t.length > 0; }).map(stems).join(' ');
  }

  // ---- MiniSearch ----
  var docs = window.__SEARCH_INDEX__;
  var miniSearch = new window.MiniSearch({
    fields: ['title', 'text'],
    storeFields: ['id', 'title', 'path', 'slug', 'url'],
    searchOptions: {
      boost: { title: 3, text: 1 },
      fuzzy: 0.2,
      prefix: true,
      combineWith: 'OR'
    },
    // Индексируем и ищем стемленными терминами для русского
    processTerm: function (term) {
      return stems(term).slice(0, 32);
    }
  });
  miniSearch.addAll(docs);
  window.__WikiSearch = {
    engine: miniSearch,
    stem: stems,
    processTerm: russianStemTerm,
    search: function (q) {
      var processed = russianStemTerm(q);
      return miniSearch.search(processed || q);
    }
  };

  // ---- UI: выпадающие результаты поиска ----
  var input = document.getElementById('wiki-search-input');
  var box = document.getElementById('wiki-search-results');
  if (input && box) {
    input.addEventListener('input', function () {
      var q = input.value.trim();
      if (q.length < 2) {
        box.hidden = true;
        box.innerHTML = '';
        return;
      }
      var results = window.__WikiSearch.search(q).slice(0, 10);
      if (!results.length) {
        var emptyText = box.getAttribute('data-empty') || 'Ничего не найдено';
        box.innerHTML = '<div class="wiki-search-item wiki-search-empty">' + emptyText + '</div>';
        box.hidden = false;
        return;
      }
      var html = '';
      results.forEach(function (r) {
        var title = r.title || '(без названия)';
        html += '<a class="wiki-search-item" href="' + (r.url || '#') + '">'
          + '<span class="wiki-search-item__title">' + escapeHtml(title) + '</span>'
          + '<span class="wiki-search-item__path">' + escapeHtml(r.path || '') + '</span>'
          + '</a>';
      });
      box.innerHTML = html;
      box.hidden = false;
    });
    // Закрыть по клику вне
    document.addEventListener('click', function (e) {
      if (!input.contains(e.target) && !box.contains(e.target)) {
        box.hidden = true;
      }
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { box.hidden = true; }
    });
  }

  function escapeHtml(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
})();