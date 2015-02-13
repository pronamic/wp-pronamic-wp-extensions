<div class="wrap">
	<?php screen_icon(); ?>

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'pronamic_wp_extensions' ); ?>

		<?php do_settings_sections( 'pronamic_wp_extensions' ); ?>

		<?php submit_button(); ?>
	</form>
</div>
