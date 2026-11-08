<footer class="wrap footer">
  <div>
    <a class="footer-brand" href="<?= !empty($preview) ? url('preview.site') : url('homepage'); ?>"><?= htmlEncode(config('general', 'site_name')); ?>.</a>
    <p><?= __('app.footer_tagline'); ?></p>
  </div>
  <p class="footer-note"><?= __('app.footer_note'); ?></p>
  <a href="#home-main">
    <?= __('app.to_start'); ?>
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right up-arrow" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
  </a>
</footer>
</div>

<?php if ((int)config('general', 'metrika_id') > 0) : ?>
<!-- Yandex.Metrika counter -->
<script>
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=<?= (int)config('general', 'metrika_id'); ?>', 'ym');

    ym(<?= (int)config('general', 'metrika_id'); ?>, 'init', {ssr:true, webvisor:true, clickmap:true, referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/<?= (int)config('general', 'metrika_id'); ?>" style="position:absolute; left:-9999px;" alt=""></div></noscript>
<!-- /Yandex.Metrika counter -->
<?php endif; ?>

<script>
(function () {
  var overlay = null;
  function close() {
    if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay);
    overlay = null;
  }
  function open(src) {
    close();
    overlay = document.createElement('div');
    overlay.className = 'wiki-lightbox';
    var img = document.createElement('img');
    img.src = src;
    img.alt = '';
    overlay.appendChild(img);
    overlay.addEventListener('click', close);
    document.body.appendChild(overlay);
  }
  document.addEventListener('click', function (e) {
    var t = e.target;
    if (t && t.tagName === 'IMG' && t.closest && t.closest('.foto')) {
      // Открываем увеличенное изображение при клике на фото в блоке .foto
      e.preventDefault();
      open(t.currentSrc || t.src);
    }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') close();
  });
})();
</script>

</body>

</html>