/**
 * Dark / light theme toggle for the static site.
 * Stores choice in localStorage; on first visit follows system preference.
 * The "dark" class is applied to <body> (see _variables-dark.css).
 */
(function () {
  'use strict';

  var KEY = 'wiki-theme';
  var DARK = '\u2600\uFE0F';
  var LIGHT = '\uD83C\uDF19';

  function apply(theme) {
    var body = document.body;
    if (!body) return;
    body.classList.remove('dark');
    if (theme === 'dark') {
      body.classList.add('dark');
    }
  }

  function current() {
    return document.body && document.body.classList.contains('dark') ? 'dark' : 'light';
  }

  function toggle() {
    var next = current() === 'dark' ? 'light' : 'dark';
    apply(next);
    try { localStorage.setItem(KEY, next); } catch (e) {}
    updateIcon();
  }

  function updateIcon() {
    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
      var dark = current() === 'dark';
      btn.innerHTML = dark ? DARK : LIGHT;
      btn.setAttribute('aria-label', dark ? 'Light theme' : 'Dark theme');
      btn.title = dark ? 'Switch to light' : 'Switch to dark';
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var saved = null;
    try { saved = localStorage.getItem(KEY); } catch (e) {}
    if (saved) {
      apply(saved);
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      apply('dark');
    }

    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
      btn.addEventListener('click', toggle);
    });
    updateIcon();

    // Random fact: use the generated URL list if available, else fallback to /random
    document.querySelectorAll('[data-random-fact]').forEach(function (link) {
      link.addEventListener('click', function (e) {
        var list = window.__RANDOM_FACTS__;
        if (list && list.length) {
          e.preventDefault();
          window.location.href = list[Math.floor(Math.random() * list.length)];
        }
      });
    });
  });
})();