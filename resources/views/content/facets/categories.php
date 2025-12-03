<div class="categories-telo">
    <?php
    function internalRender($nodes, $level = 0)
    {
      foreach ($nodes as  $node) :
    ?>

		<?php if ($node['level'] == 0) : ?><div class="categories"><?php endif; ?>
         
               <?php $css = $node['level'] == 0 ? 'text-xl block' : 'text-sm black'; ?>
			   
			   
                      <a class="<?= $css; ?>" href="<?= urlDir($node['facet_path']); ?>" target="_blank" rel="noopener noreferrer"><?= $node['facet_title']; ?></a>

           
             

              <?php if (isset($node['children'])) {
                internalRender($node['children'], $node['level'] + 1);
              } ?>

        	<?php if ($node['level'] == 1) : ?></div><?php endif; ?>

	   
	   

    <?php endforeach;
    }

    echo internalRender($nodes);
    ?>
</div>