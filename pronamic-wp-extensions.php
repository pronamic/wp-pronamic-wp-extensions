<?php
/*
Plugin Name: Pronamic WordPress Extensions
Plugin URI: http://www.pronamic.eu/
Description: 
Version: 1.0.0
Author: Pronamic
Author URI: http://www.pronamic.eu/
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Network: true
*/

include 'src/Pronamic/WP/Extensions/Findable.php';
include 'src/Pronamic/WP/Extensions/ExtensionInfo.php';
include 'src/Pronamic/WP/Extensions/ExtensionsAdmin.php';
include 'src/Pronamic/WP/Extensions/ExtensionsApi.php';
include 'src/Pronamic/WP/Extensions/ExtensionsPlugin.php';
include 'src/Pronamic/WP/Extensions/PluginInfo.php';
include 'src/Pronamic/WP/Extensions/Finder.php';

/**
 * Bootstrap
 */
Pronamic_WP_Extensions_ExtensionsPlugin::get_instance( __FILE__ );
