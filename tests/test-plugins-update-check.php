<?php

require '../../../load.php';

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

$options = array(
	'timeout'    => ( ( defined( 'DOING_CRON' ) && DOING_CRON ) ? 30 : 3 ),
	'body'       => $body,
	'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
);

$url = 'https://api.pronamic.eu/plugins/update-check/1.2/';

$response = wp_remote_post( $url, $options );

echo '<pre>';

var_dump( $response );

echo '</pre>';
