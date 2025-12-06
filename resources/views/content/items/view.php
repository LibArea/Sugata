<main>
	<?= insert('/_block/navigation/breadcrumbs', [
		'list' => [
			[
				'name' => __('app.home'),
				'link' => url('homepage')
			],
			[
				'name' => __('app.facts'),
				'link' => url('items')
			],
			[
				'name' => __('app.view'),
				'link' => url('items')
			],
		],
		'sheet' =>  $data['sheet']
	]);

	$item = $data['item'];
	

	?>

	<article>

		<?php if ($item['item_thumb_img']) : ?>
			<div class="box br-lightgray img-preview">
				<img class="w-100" src="<?= Img::PATH['thumbs'] . $item['item_thumb_img']; ?>" alt="<?= $item['item_title']; ?>">
			</div>
		<?php endif; ?>

		<h1 class="title"><?= $item['item_title']; ?></h1>

		<?= markdown($item['item_content']); ?>
		<i class="right gray-600"><?= $item['item_date']; ?></i>

	</article>

  <?php if ($data['similar']) : ?>
    <br>
    <h4 class="uppercase-box"><?= __('app.recommended'); ?></h4>
    <?php foreach ($data['similar'] as $value) : 
		$fields = json_decode($value['url'], true); ?>
		
				<article>
					<h3 class="title">
						<a class="title-fact" href="<?= url('view', ['id' => $fields['item_id']]); ?>">
							<?= $value['title']; ?>
						</a>
					</h3>
					<div class="fact_content">
					 <?= $value['snippet']; ?> <?= $value['snippet2']; ?>
					</div>
					<div class="fact_footer">
						<?= HTML::facetDir($fields['facets']); ?>

						<span class="lowercase"><?= langDate($value['added_at']); ?></span>
					</div>
				</article>

    <?php endforeach; ?>
  <?php endif; ?>

</main>