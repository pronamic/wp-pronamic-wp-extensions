<?php
/*
Plugin Name: Pronamic WordPress Extensions
Plugin URI: http://www.pronamic.eu/plugins/pronamic-wp-extensions/
Description: WordPress plugin wich allows your to create your own WordPress extensions directory.

Version: 1.0.0

Author: Pronamic
Author URI: http://www.pronamic.eu/

Text Domain: pronamic_wp_extensions
Domain Path: /languages/

License: GPL
*/

$dir = dirname( __FILE__ );

require_once $dir . '/classes/Pronamic/WP/ExtensionsPlugin/Findable.php';
require_once $dir . '/classes/Pronamic/WP/ExtensionsPlugin/ExtensionInfo.php';
require_once $dir . '/classes/Pronamic/WP/ExtensionsPlugin/Admin.php';
require_once $dir . '/classes/Pronamic/WP/ExtensionsPlugin/Api.php';
require_once $dir . '/classes/Pronamic/WP/ExtensionsPlugin/Plugin.php';
require_once $dir . '/classes/Pronamic/WP/ExtensionsPlugin/Finder.php';

/**
 * Bootstrap
 */
global $pronamic_wp_extensions_plugin;

$pronamic_wp_extensions_plugin = Pronamic_WP_ExtensionsPlugin_Plugin::get_instance( __FILE__ );
