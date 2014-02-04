<?php

class Pronamic_WP_ExtensionsPlugin_Api {
	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @var self
	 */
	protected static $instance = null;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize Pronamic WordPress Extensions API object
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		
		add_action( 'query_vars', array( $this, 'query_vars' ) );
		
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Initialize
	 */
	public function init() {	
		// Rewrite rules
		// @see http://wordpress.stackexchange.com/questions/5413/need-help-with-add-rewrite-rule
		add_rewrite_rule(
			// The regex to match the incoming URL
			// /api/plugins/info/1.0/
			'api/([^/]+)/([^/]+)/([^/]+)/?',
			// The resulting internal URL: `index.php` because we still use WordPress
			'index.php?pronamic_wp_extensions_api=true&pronamic_wp_extensions_api_module=$matches[1]&pronamic_wp_extensions_api_method=$matches[2]&pronamic_wp_extensions_api_version=$matches[3]',
			// This is a rather specific URL, so we add it to the top of the list
			// Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
			'top'
		);
	}

	/**
	 * Query vars
	 * 
	 * @param array $query_vars
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'pronamic_wp_extensions_api';
		$query_vars[] = 'pronamic_wp_extensions_api_module';
		$query_vars[] = 'pronamic_wp_extensions_api_method';
		$query_vars[] = 'pronamic_wp_extensions_api_version';

		return $query_vars;
	}

	/**
	 * Template redirect
	 */
	public function template_redirect() {
		$is_api = get_query_var( 'pronamic_wp_extensions_api' );

		if ( $is_api ) {
			$module = get_query_var( 'pronamic_wp_extensions_api_module' );
			$method = get_query_var( 'pronamic_wp_extensions_api_method' );
			$version = get_query_var( 'pronamic_wp_extensions_api_version' );

			switch( $module ) {
				case 'themes':
					$result = $this->themes_api( $method );

					break;
				case 'plugins':
					$result = $this->plugins_api( $method );
					
					break;
			}
			
			wp_send_json_error();
		}
		
	}

	//////////////////////////////////////////////////
	// Themes API
	//////////////////////////////////////////////////

	public function themes_api( $method ) {
		switch( $method ) {
			case 'info':
				return $this->themes_api_info();
			case 'update-check':
				return $this->themes_api_update_check();
		}
	}
	
	public function themes_api_info() {
		
	}

	public function themes_api_update_check() {
		if ( filter_has_var( INPUT_POST, 'themes' ) ) {
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
						'meta_query'       => array(
							array(
								'key'     => '_pronamic_extension_wp_org_slug',
								'value'   => 'bug #23268',
								'compare' => 'NOT EXISTS',
							),
						),
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
							
							$extension = new Pronamic_WP_ExtensionsPlugin_ExtensionInfo( $post );
			
							$stable_version  = $extension->get_version();
							$current_version = $theme['Version'];
								
							if ( version_compare( $stable_version, $current_version, '>' ) ) {
								$result              = new stdClass();
								$result->id          = $post->ID;
								$result->slug        = $post->post_name;
								$result->new_version = $stable_version;
								// $result->upgrade_notice = '';
								$result->url         = get_permalink( $post );
								$result->package     = $extension->get_download_link();
			
								$theme_updates[$file] = $result;
							}
						}
					}
				}
		
				$result = array(
					'themes' => $theme_updates
				);
			
				wp_send_json( $result );
			}
		}
	}

	//////////////////////////////////////////////////
	// Plugins API
	//////////////////////////////////////////////////

	public function plugins_api( $method ) {
		switch( $method ) {
			case 'info':
				return $this->plugins_api_info();
			case 'update-check':
				return $this->plugins_api_update_check();
		}
	}
	
	public function plugins_api_info() {
		$slug = filter_input( INPUT_GET, 'slug', FILTER_SANITIZE_STRING );
		
		$finder = new Pronamic_WP_ExtensionsPlugin_Finder();
		
		$plugin = $finder->by_slug( $slug, 'pronamic_plugin' );
		
		if ( false !== $plugin ) {
			$plugin_info = $plugin->get_info();
		
			wp_send_json( $plugin_info );
		}
	}
	
	public function plugins_api_update_check() {
		if ( filter_has_var( INPUT_POST, 'plugins' ) ) {
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
						'meta_query'       => array(
							array(
								'key'     => '_pronamic_extension_wp_org_slug',
								'value'   => 'bug #23268',
								'compare' => 'NOT EXISTS',
							),
						),
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
						
							$extension = new Pronamic_WP_ExtensionsPlugin_ExtensionInfo( $post );

							$stable_version  = $extension->get_version();
							$current_version = $plugin['Version'];
							
							if ( version_compare( $stable_version, $current_version, '>' ) ) {
								$result              = new stdClass();
								$result->id          = $post->ID;
								$result->slug        = $post->post_name;
								$result->new_version = $stable_version; 
								// $result->upgrade_notice = '';
								$result->url         = get_permalink( $post );
								$result->package     = $extension->get_download_link();

								$plugin_updates[$file] = $result;
							}
						}
					}
				}

				$result = array(
					'plugins' => $plugin_updates
				);

				wp_send_json( $result );
			}
		}
	}
	

	//////////////////////////////////////////////////

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
	
		return self::$instance;
	}
}
