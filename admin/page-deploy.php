<div class="wrap">
	<?php screen_icon(); ?>

	<h2><?php echo get_admin_page_title(); ?></h2>

	<?php 
	
	if ( filter_has_var( INPUT_POST, 'deploy' ) ) {
		include 'deploy-zip.php';
	} elseif ( filter_has_var( INPUT_POST, 'deploy_2' ) ) {
		include 'deploy-2.php';
	} elseif ( filter_has_var( INPUT_POST, 'deploy_3' ) ) {
		include 'deploy-3.php';
	} else {
		include 'deploy-input.php';
	}

	?>

</div>