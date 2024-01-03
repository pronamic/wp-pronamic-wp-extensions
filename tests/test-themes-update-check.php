<?php

require '../../../load.php';

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

$options = array(
	'timeout'    => ( ( defined( 'DOING_CRON' ) && DOING_CRON ) ? 30 : 3 ),
	'body'       => $body,
	'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
);

$url = 'https://api.pronamic.eu/themes/update-check/1.2/index.php';

$response = wp_remote_post( $url, $options );

var_dump( $response );
