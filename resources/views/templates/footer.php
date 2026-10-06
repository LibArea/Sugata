<br>
<footer class="content wiki-content">
  <div>
    © 2026 sugata — <span class="lowercase"><?= __('app.facts'); ?></span><br>
    <?= __('app.page_rebuilt'); ?> <?= date('d.m.Y H:i:s'); ?>
  </div>
  <div class="right mb-none"><a href="/info/about.html">О сайте</a></div>
</footer>

<?php if ((int)config('general', 'metrika_id') > 0) : ?>
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=<?= (int)config('general', 'metrika_id'); ?>', 'ym');

    ym(<?= (int)config('general', 'metrika_id'); ?>, 'init', {ssr:true, webvisor:true, clickmap:true, referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/<?= (int)config('general', 'metrika_id'); ?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
<?php endif; ?>

</body>

</html>