<?php
/*
Plugin Name: Pronamic WordPress Extensions
Plugin URI: http://www.pronamic.eu/
Description: 
Version: 1.0.0
Author: Pronamic
Author URI: http://www.pronamic.eu/
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
