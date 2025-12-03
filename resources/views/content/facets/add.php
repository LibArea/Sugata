<main>
  <?= insert('/_block/navigation/breadcrumbs-admin', ['sheet' =>  $data['sheet']]); ?>
  <h1 class="text-xl"><?= __('app.add_' . $data['sheet']); ?> </h1>

  <form class="max-w-md" action="<?= url('add.facet', ['type' => $data['sheet']], method: 'post'); ?>" method="post" enctype="multipart/form-data">
    <?= $container->csrf()->field(); ?>
    <?= insert('/content/facets/form/add-facet'); ?>
  </form>
</main>