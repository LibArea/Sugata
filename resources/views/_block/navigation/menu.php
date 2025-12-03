<div class="box bg-lightgray">
	   <ul class="menu">

		  <?php foreach ($itemsMenu as $key => $item) :
			$css = empty($item['css']) ? false : $item['css'];
			$isActive = $item['id'] == $sheet ? 'active' : false;
			$class = ($css || $isActive) ? ' class="' . $isActive . ' ' .  $css . '"'   : ''; ?>


		<?php if ($container->user()->tl() >= $item['tl']) : ?>
		
		      <?php if (!empty($item['br'])) : ?><br><?php endif; ?>
		
			<li<?= $class; ?>>
			  <a href="<?= url($item['url']); ?>">
				<?php if (!empty($item['icon'])) : ?><svg class="icon">
					<use xlink:href="/assets/svg/icons.svg#<?= $item['icon']; ?>"></use>
				  </svg><?php endif; ?>
				<?= __($item['title']); ?>
			  </a>
			  </li>
			   <?php endif; ?>
		<?php endforeach; ?>

		</ul>
</div>