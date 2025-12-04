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
			]
		],
		'sheet' =>  $data['sheet']
	]);
	?>

	<h1 class="uppercase-box"><?= __('app.facts'); ?> </h1>

	<div>
		<?php if (!empty($data['items'])) : ?>

			<?php foreach ($data['items'] as $item) : ?>
				<article id="<?= $item['item_id']; ?>">
					<h3 class="title">
						<a class="title-fact" href="<?= url('view', ['id' => $item['item_id']]); ?>">
							<?= $item['item_title']; ?>
						</a>
						<sup>
							<a class="ml10 gray-600" href="<?= url('item.form.edit', ['id' => $item['item_id']]); ?>">
								<svg class="icon text-sm">
									<use xlink:href="/assets/svg/icons.svg#edit"></use>
								</svg>
							</a>
						</sup>
						<?php if (!$item['item_published']) : ?>
							<sup class="red text-sm">
								<?= __('app.not_published'); ?>
							</sup>
						<?php endif; ?>
					</h3>

					<?php $arr = \App\Content\Parser\Content::cut($item['item_content']);
					echo markdown($arr['content']); ?>

					<a class="read_more" href=""><?= __('app.read_more'); ?> →</a>
					<div class="fact_footer">
						<?= HTML::facetDir($item['facet_list']); ?>

						<span class="lowercase"><?= langDate($item['item_date']); ?></span>
					</div>
				</article>
			<?php endforeach; ?>

		<?php else : ?>
			<?= insert('/_block/no-content', ['type' => 'small', 'text' => __('app.no_websites'), 'icon' => 'info']); ?>
		<?php endif; ?>

		<?= Html::pagination($data['pNum'], $data['pagesCount'], false, '/'); ?>
	</div>
</main>

<?= insert('/global/aside', ['sheet' => $data['sheet']]); ?>