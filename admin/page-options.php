<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'pronamic_wp_extensions' ); ?>

		<?php do_settings_sections( 'pronamic_wp_extensions' ); ?>

		<?php submit_button(); ?>
	</form>

	<h2>Test</h2>

	<h3>Plugins</h3>

	<?php

	// https://codex.wordpress.org/Function_Reference/get_plugins
	// https://github.com/pronamic/wp-pronamic-wp-extensions/blob/develop/classes/Pronamic/WP/ExtensionsPlugin/Api.php#L226-L308
	// https://github.com/pronamic/wp-pronamic-client/blob/develop/includes/class-updater.php#L79
	$plugins = array(
		'pronamic-ideal/pronamic-ideal.php' => array(
			'Name'    => 'Pronamic iDEAL',
			'Version' => '1.0.0',
			'Author'  => 'Pronamic',
		),
		'pronamic-donations/pronamic-donations.php' => array(
			'Name'    => 'Pronamic Donations (deprecated)',
			'Version' => '1.0.0',
			'Author'  => 'Pronamic',
		),
		'dm-gravityforms/dm-gravityforms.php' => array(
			'Name'    => 'Daadkracht Marketing Gravity Forms',
			'Version' => '1.0.0',
			'Author'  => 'Pronamic',
		),
		'wp-gravityforms-nl/gravityforms-nl.php' => array(
			'Name'    => 'Gravity Forms (nl)',
			'Version' => '2.0.5',
			'Author'  => 'Pronamic',
		),
	);

	$body = array(
		'plugins' => json_encode( $plugins ),
	);

	$request = new WP_REST_Request( 'POST', '/pronamic-wp-extensions/v1/plugins/update-check' );

	$request->set_body_params( $body );

	$response = rest_do_request( $request );

	echo '<pre>';

	var_dump( $response );

	echo '</pre>';

	?>
	
	<h3>Themes</h3>

	<?php


	// https://codex.wordpress.org/Function_Reference/wp_get_themes
	// https://github.com/pronamic/wp-pronamic-wp-extensions/blob/develop/classes/Pronamic/WP/ExtensionsPlugin/Api.php#L115-L197
	// https://github.com/pronamic/wp-pronamic-client/blob/develop/includes/class-updater.php#L118
	$themes = array(
		'emg' => array(
			'Name'       => 'Eisma Media Groep',
			'Title'      => 'Eisma Media Groep',
			'Version'    => '1.0.0',
			'Author'     => 'Pronamic',
			'Author URI' => 'https://www.pronamic.eu/',
			'Template'   => null,
			'Stylesheet' => 'emg',
		),
		'dmb' => array(
			'Name'       => 'Daadkracht Marketing Bedrijvengids',
			'Title'      => 'Daadkracht Marketing Bedrijvengids',
			'Version'    => '1.0.0',
			'Author'     => 'Pronamic',
			'Author URI' => 'https://www.pronamic.eu/',
			'Template'   => null,
			'Stylesheet' => 'dmb',
		),
	);

	$body = array(
		'themes' => json_encode( $themes ),
	);

	$request = new WP_REST_Request( 'POST', '/pronamic-wp-extensions/v1/themes/update-check' );

	$request->set_body_params( $body );

	$response = rest_do_request( $request );

	echo '<pre>';

	var_dump( $response );

	echo '</pre>';

	?>
</div>
