<footer class="content text-sm lowercase mt20">
  © 2025 sugata — <?= __('app.facts'); ?>
</footer>

<script src="/assets/js/common.js?<?= config('general', 'version'); ?>"></script>

<?php if ($container->user()->active()) : ?>
  <script src="/assets/js/app.js?<?= config('general', 'version'); ?>"></script>
<?php endif; ?>

<?php if ($container->user()->admin()) : ?>
  <script src="/assets/js/admin.js?<?= config('general', 'version'); ?>"></script>
<?php endif; ?>

<script nonce="<?= config('main', 'nonce'); ?>">

  <?= Msg::get(); ?>

</script>

</body>

</html>