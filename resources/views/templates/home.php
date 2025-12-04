<?= insert('/templates/header', ['meta' => Meta::home()]); ?>

<main class="content">

    <?= insert('/content/facets/categories', ['nodes' => $nodes]); ?>

    <h2 class="uppercase-box"><?= __('app.latest_facts'); ?></h2>

	<?php foreach ($items as $item) : ?>
		<article id="<?= $item['item_id']; ?>">
		    <h3 class="title">
				<a class="title-fact" href="">
					<?= $item['item_title']; ?>
				</a>	
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

</main>

<?= insert('/templates/footer'); ?>