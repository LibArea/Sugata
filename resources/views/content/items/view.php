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
		<i><?= $item['item_date']; ?></i>

	</article>

</main>