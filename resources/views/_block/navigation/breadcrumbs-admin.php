<?php 
$arr = [
  
	[
        'name' => __('app.home'),
        'link' => url('homepage')
      ],
  
      [
        'name' => __('app.' . $sheet),
        'link' => $sheet
      ],
    ]; 
?>

<?= insert('/_block/navigation/breadcrumbs', ['list' => $arr]); ?>