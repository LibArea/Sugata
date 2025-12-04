<main>
<?= insert('/_block/navigation/breadcrumbs', [
		'list' => [
			[
				'name' => __('app.home'),
				'link' => url('homepage')
			], [
				'name' => __('app.structure'),
				'link' => url('structure')
			], [
				'name' => __('app.add_category'),
				'link' => ''
			], 
		],
		'sheet' =>  $data['sheet']
	]); 
	?>
	
  <h1 class="uppercase-box"><?= __('app.add_' . $data['sheet']); ?> </h1>

  <form class="max-w-md" action="<?= url('add.facet', ['type' => $data['sheet']], method: 'post'); ?>" method="post" enctype="multipart/form-data">
    <?= $container->csrf()->field(); ?>
    <?= insert('/content/facets/form/add-facet'); ?>
  </form>
</main>