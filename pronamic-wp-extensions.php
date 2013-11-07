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

// @see https://github.com/WordPress/WordPress/blob/a8ef13972cccf91bfa9ed30a65c8e61a2f2c7977/wp-includes/update.php#L96

function pronamic_wp_extensions_init() {
	register_post_type( 'pronamic_plugin', array(
		'labels' => array(
			'name' => _x( 'Plugins', 'post type general name', 'pronamic_wp_extensions' ),
			'singular_name' => _x( 'Plugin', 'post type singular name', 'pronamic_wp_extensions' ),
			'add_new' => _x( 'Add New', 'plugin', 'pronamic_wp_extensions' ),
			'add_new_item' => __( 'Add New Plugin', 'pronamic_wp_extensions' ),
			'edit_item' => __( 'Edit Plugin', 'pronamic_wp_extensions' ),
			'new_item' => __( 'New Plugin', 'pronamic_wp_extensions' ),
			'view_item' => __( 'View Plugin', 'pronamic_wp_extensions' ),
			'search_items' => __( 'Search Plugins', 'pronamic_wp_extensions' ),
			'not_found' => __( 'No plugins found', 'pronamic_wp_extensions' ),
			'not_found_in_trash' => __( 'No plugins found in Trash', 'pronamic_wp_extensions' ),
			'parent_item_colon' => __( 'Parent Plugin:', 'pronamic_wp_extensions' ),
			'menu_name' => __( 'Plugins', 'pronamic_wp_extensions' )
		),
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'query_var' => true,
		'capability_type' => 'post',
		'has_archive' => true,
		'rewrite' => array( 'slug' => 'plugins' ),
		'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields' )
	) );

	register_post_type( 'pronamic_theme', array(
		'labels' => array(
			'name' => _x( 'Themes', 'post type general name', 'pronamic_wp_extensions' ),
			'singular_name' => _x( 'Theme', 'post type singular name', 'pronamic_wp_extensions' ),
			'add_new' => _x( 'Add New', 'theme', 'pronamic_wp_extensions' ),
			'add_new_item' => __( 'Add New Theme', 'pronamic_wp_extensions' ),
			'edit_item' => __( 'Edit Theme', 'pronamic_wp_extensions' ),
			'new_item' => __( 'New Theme', 'pronamic_wp_extensions' ),
			'view_item' => __( 'View Theme', 'pronamic_wp_extensions' ),
			'search_items' => __( 'Search Themes', 'pronamic_wp_extensions' ),
			'not_found' => __( 'No themes found', 'pronamic_wp_extensions' ),
			'not_found_in_trash' => __( 'No themes found in Trash', 'pronamic_wp_extensions' ),
			'parent_item_colon' => __( 'Parent Theme:', 'pronamic_wp_extensions' ),
			'menu_name' => __( 'Themes', 'pronamic_wp_extensions' )
		),
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'query_var' => true,
		'capability_type' => 'post',
		'has_archive' => true,
		'rewrite' => array( 'slug' => 'themes' ),
		'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields' )
	) );
}

add_action( 'init', 'pronamic_wp_extensions_init' );

function pronamic_wp_extensions_add_meta_boxes() {
	$screens = array( 'pronamic_plugin', 'pronamic_theme' );

	foreach ( $screens as $screen ) {
		add_meta_box(
			'pronamic_extension_version_control',
			__( 'Version Control', 'pronamic_wp_extensions' ),
			'pronamic_wp_extensions_version_control_meta_box',
			$screen,
			'normal',
			'high'
		);

		add_meta_box(
			'pronamic_extension_deploy',
			__( 'Deploy', 'pronamic_wp_extensions' ),
			'pronamic_wp_extensions_deploy_meta_box',
			$screen,
			'normal',
			'high'
		);
	}
}

add_action( 'add_meta_boxes', 'pronamic_wp_extensions_add_meta_boxes' );

function pronamic_wp_extensions_version_control_meta_box() {
	include 'admin/meta-box-version-control.php';
}

function pronamic_wp_extensions_deploy_meta_box() {
	include 'admin/meta-box-deploy.php';
}

function pronamic_wp_extensions_save_post( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	if ( !isset( $_POST['pronamic_wp_extensions_nonce'] ) )
		return;

	if ( !wp_verify_nonce( $_POST['pronamic_wp_extensions_nonce'], 'pronamic_wp_extension_save_post' ) )
		return;

	if ( !current_user_can( 'edit_post', $post->ID ) )
		return;

	// Save data
	$data = filter_input_array( INPUT_POST, array(
		'_pronamic_extension_stable_version' => FILTER_SANITIZE_STRING,
		'_pronamic_extension_github_user'    => FILTER_SANITIZE_STRING,
		'_pronamic_extension_github_repo'    => FILTER_SANITIZE_STRING,
		'_pronamic_extension_bitbucket_user' => FILTER_SANITIZE_STRING,
		'_pronamic_extension_bitbucket_repo' => FILTER_SANITIZE_STRING,
	) );

	foreach ( $data as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}
}

add_action( 'save_post', 'pronamic_wp_extensions_save_post', 10, 2 );

/**
 * @see https://github.com/WordPress/WordPress/blob/3.7.1/wp-admin/includes/plugin-install.php#L9
 * @see http://wordpress.stackexchange.com/questions/5413/need-help-with-add-rewrite-rule
 * 
 * @param unknown $wp_rewrite
 */
add_action( 'init', 'wpa5413_init' );
function wpa5413_init()
{
	// /api/plugins/info/1.0/
	
    // Remember to flush the rules once manually after you added this code!
    add_rewrite_rule(
        // The regex to match the incoming URL
        'api/([^/]+)/([^/]+)/([^/]+)/?',
        // The resulting internal URL: `index.php` because we still use WordPress
        // `pagename` because we use this WordPress page
        // `designer_slug` because we assign the first captured regex part to this variable
        'index.php?pronamic_wp_extensions_api=true&pronamic_wp_extensions_api_module=$matches[1]&pronamic_wp_extensions_api_method=$matches[2]&pronamic_wp_extensions_api_version=$matches[3]',
        // This is a rather specific URL, so we add it to the top of the list
        // Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
        'top' );
}

add_filter( 'query_vars', 'wpa5413_query_vars' );
function wpa5413_query_vars( $query_vars )
{
	$query_vars[] = 'pronamic_wp_extensions_api';
	$query_vars[] = 'pronamic_wp_extensions_api_module';
	$query_vars[] = 'pronamic_wp_extensions_api_method';
	$query_vars[] = 'pronamic_wp_extensions_api_version';
	return $query_vars;
}

function pronamic_wp_extensison_template_redirect() {
	$is_api = get_query_var( 'pronamic_wp_extensions_api' );

	if ( $is_api ) {
		$module = get_query_var( 'pronamic_wp_extensions_api_module' );
		$method = get_query_var( 'pronamic_wp_extensions_api_method' );
		$version = get_query_var( 'pronamic_wp_extensions_api_version' );

		if ( $module == 'themes' ) {
			// /api/themes/update-check/1.0/
			if ( $method == 'update-check' ) {
				$json = filter_input( INPUT_POST, 'themes', FILTER_UNSAFE_RAW );
				
				$themes = json_decode( $json, true );

				if ( is_array( $themes ) ) {
					global $wpdb;
				
					$titles = array();
					foreach ( $themes as $file => $theme ) {
						$titles[$file] = $theme['Title'];
					}

					$theme_updates = array();
				
					if ( ! empty( $titles ) ) {
						$theme_posts = get_posts( array(
							'post_type'        => 'pronamic_theme',
							'nopaging'         => true,
							'post_title__in'   => $titles,
							'suppress_filters' => false,
						) );
							
						$theme_names = array();
						foreach ( $theme_posts as $post ) {
							$theme_names[$post->post_title] = $post;
						}
				
						/*
						 * Theme array values
						* - Name
						* - PluginURI
						* - Version
						* - Description
						* - Author
						* - AuthorURI
						* - TextDomain
						* - DomainPath
						* - Network
						* - Title
						* - AuthorName
						*/
						foreach ( $themes as $file => $theme ) {
							if ( isset( $theme_names[$theme['Name']] ) ) {
								$post = $theme_names[$theme['Name']];
				
								$stable_version  = get_post_meta( $post->ID, '_pronamic_extension_stable_version', true );
								$current_version = $plugin['Version'];
									
								if ( version_compare( $stable_version, $current_version, '>' ) ) {
									$result              = new stdClass();
									$result->id          = $post->ID;
									$result->slug        = $post->post_name;
									$result->new_version = $stable_version;
									// $result->upgrade_notice = '';
									$result->url         = get_permalink( $post );
									$result->package     = get_permalink( $post );
				
									$theme_updates[$file] = $result;
								}
							}
						}
					}
						
					header('Content-Type: application/json');
				
					$result = array(
						'themes' => $theme_updates
					);
				
					echo json_encode( $result );
				
					exit;
				}
			}
		}

		if ( $module == 'plugins' ) {
			// /api/plugins/info/1.0/
			if ( $method == 'info' ) {
				$slug = filter_input( INPUT_GET, 'slug', FILTER_SANITIZE_STRING );
		
				$plugins = get_posts( array(
					'name'        => $slug,
					'post_type'   => 'pronamic_plugin',
					'post_status' => 'publish',
					'numberposts' => 1
				) );
		
				$plugin = array_shift( $plugins );
		
				if ( $plugin ) {
					$plugin_info = new stdClass();
					$plugin_info->name          = get_the_title( $plugin );
					$plugin_info->slug          = $plugin->post_name;;
					$plugin_info->version       = get_post_meta( $plugin->ID, '_pronamic_extension_stable_version', true );
					$plugin_info->download_link = sprintf( 'http://themes.pronamic.nl/plugins/%s/%s', $plugin_info->slug, $plugin_info->version );
					
					header('Content-Type: application/json');
					
					echo json_encode( $plugin_info );
					
					exit;
				} else {
					exit;
				}
			}
	
			// /api/plugins/update-check/1.0/
			if ( $method == 'update-check' ) {
				if ( isset( $_POST['plugins'] ) ) {
					$json = filter_input( INPUT_POST, 'plugins', FILTER_UNSAFE_RAW );
	
					$plugins = json_decode( $json, true );
	
					if ( is_array( $plugins ) ) {
						global $wpdb;
	
						$titles = array();
						foreach ( $plugins as $file => $plugin ) {
							$titles[$file] = $plugin['Name'];
						}
	
						$plugin_updates = array();
	
						if ( ! empty( $titles ) ) {
							$plugin_posts = get_posts( array(
								'post_type'        => 'pronamic_plugin',
								'nopaging'         => true,  
								'post_title__in'   => $titles,
								'suppress_filters' => false,
							) );
							
							$plugin_names = array();
							foreach ( $plugin_posts as $post ) {
								$plugin_names[$post->post_title] = $post;
							}
	
							/*
							 * Plugin array values
							 * - Name
							 * - PluginURI
							 * - Version
							 * - Description
							 * - Author
							 * - AuthorURI
							 * - TextDomain
							 * - DomainPath
							 * - Network
							 * - Title
							 * - AuthorName
							 */
							foreach ( $plugins as $file => $plugin ) {
								if ( isset( $plugin_names[$plugin['Name']] ) ) {
									$post = $plugin_names[$plugin['Name']];
	
									$stable_version  = get_post_meta( $post->ID, '_pronamic_extension_stable_version', true );
									$current_version = $plugin['Version'];
									
									if ( version_compare( $stable_version, $current_version, '>' ) ) {
										$result              = new stdClass();
										$result->id          = $post->ID;
										$result->slug        = $post->post_name;
										$result->new_version = $stable_version; 
										// $result->upgrade_notice = '';
										$result->url         = get_permalink( $post );
										$result->package     = get_permalink( $post );
		
										$plugin_updates[$file] = $result;
									}
								}
							}
						}
					
						header('Content-Type: application/json');
						
						$result = array(
							'plugins' => $plugin_updates
						);
	
						echo json_encode( $result );
						
						exit;
					}
				}
			
				exit;
			}
		}
	}
}

add_action( 'template_redirect', 'pronamic_wp_extensison_template_redirect' );

function themeslug_query_vars( $qvars ) {
	array_push( $qvars, 'post_title__in' );
	return $qvars;

}
add_filter( 'query_vars', 'themeslug_query_vars' , 10, 1 );

function pronamic_wp_extensions_posts_where( $where, $query ) {
	$titles = $query->get( 'post_title__in' );

	if ( is_array( $titles ) && ! empty ( $titles )  ) {
		// @see https://github.com/WordPress/WordPress/blob/3.7/wp-includes/post.php#L3806
		$post_titles = implode( "', '", esc_sql( $titles ) );

		$where .= " AND post_title IN ('$post_titles')";
	}

	return $where;
}

add_filter( 'posts_where', 'pronamic_wp_extensions_posts_where', 10, 2 );

