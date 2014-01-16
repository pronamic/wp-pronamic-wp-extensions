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
     * Error code 001: No license key provided.
     *
     * @const string
     */
    const NO_LICENSE_KEY = '001';

    /**
     * Error code 002: No slug provided.
     *
     * @const string
     */
    const NO_SLUG = '002';

    /**
     * Error code 003: No product type provided.
     *
     * @const string
     */
    const NO_PRODUCT_TYPE = '003';

    /**
     * Error code 004: No site URL provided.
     *
     * @const string
     */
    const NO_SITE_URL = '004';

    /**
     * Error code 005: License key expired.
     *
     * @const string
     */
    const LICENSE_KEY_EXPIRED = '005';

    /**
     * Error code 006: License key not active.
     *
     * @const string
     */
    const LICENSE_KEY_NOT_ACTIVE = '006';

    /**
     * Error code 007: License code already activated.
     *
     * @const string
     */
    const LICENSE_KEY_ALREADY_ACTIVATED = '007';

    /**
     * Error code 008: License code already activated.
     *
     * @const string
     */
    const LICENSE_KEY_COULD_NOT_BE_ACTIVATED = '008';

    /**
     * Error code 009: License code already activated.
     *
     * @const string
     */
    const LICENSE_KEY_COULD_NOT_BE_DEACTIVATED = '009';

    /**
     * Error code 010: License key does not exist.
     *
     * @const string
     */
    const INVALID_LICENSE_KEY = '010';

    /**
     * Error code 011: Product slug invalid.
     *
     * @const string
     */
    const INVALID_PRODUCT_SLUG = '011';

    /**
     * Error code 012: Product type invalid.
     *
     * @const string
     */
    const INVALID_PRODUCT_TYPE = '012';

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
     *
     * @return array $query_vars
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
			$module  = get_query_var( 'pronamic_wp_extensions_api_module' );
			$method  = get_query_var( 'pronamic_wp_extensions_api_method' );
			$version = get_query_var( 'pronamic_wp_extensions_api_version' );

			switch( $module ) {
				case 'themes':
					$this->themes_api( $method );

					break;
				case 'plugins':
					$this->plugins_api( $method );
					
					break;
                case 'licenses':
                    $this->licenses_api( $method );

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
				$this->themes_api_info();

                break;
			case 'update-check':
				$this->themes_api_update_check();

                break;
		}
	}
	
	public function themes_api_info() {
		
	}

	public function themes_api_update_check() {
		if ( filter_has_var( INPUT_POST, 'themes' ) ) {
			$json = filter_input( INPUT_POST, 'themes', FILTER_UNSAFE_RAW );
			
			$themes = json_decode( $json, true );
		
			if ( is_array( $themes ) ) {
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
				$this->plugins_api_info();

                break;
			case 'update-check':
				$this->plugins_api_update_check();

                break;
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
    // Licenses API
    //////////////////////////////////////////////////

    /**
     * The license API
     *
     * @param $method
     */
    public function licenses_api( $method ) {
        switch( $method ) {
            case 'activate':
                $this->licenses_api_activate();
                break;
            case 'deactivate':
                $this->licenses_api_deactivate();
                break;
            case 'check':
                $this->licenses_api_check();
        }
    }

    /**
     * Checks if a license key is correct for a certain plugin., then sets the license key as activated.
     */
    public function licenses_api_activate() {

        $license_key  = filter_input( INPUT_POST, 'license_key' , FILTER_SANITIZE_STRING );
        $slug         = filter_input( INPUT_POST, 'slug'        , FILTER_SANITIZE_STRING );
        $product_type = filter_input( INPUT_POST, 'product_type', FILTER_SANITIZE_STRING );
        $site         = filter_input( INPUT_POST, 'site'        , FILTER_VALIDATE_URL );

        if ( strlen( $license_key ) <= 0 ) {
            wp_send_json( array( 'success' => false, 'error_code' => self::NO_LICENSE_KEY ) );
        }

        if ( strlen( $slug ) <= 0 ) {
            wp_send_json( array( 'success' => false, 'error_code' => self::NO_SLUG ) );
        }

        if ( strlen( $product_type ) <= 0 ) {
            wp_send_json( array( 'success' => false, 'error_code' => self::NO_PRODUCT_TYPE ) );
        }

        if ( ! isset( $site ) ) {
            wp_send_json( array( 'success' => false, 'error_code' => self::NO_SITE_URL ) );
        }

        $license_query = new WP_Query( array(
            'post_type'      => 'pronamic_license',
            's'              => $license_key,
            'posts_per_page' => 1,
        ) );

        // Check if any licenses were found
        if ( $license_query->have_posts() ) {

            $license = $license_query->next_post();

            $active_sites       = get_post_meta( $license->ID, '_pronamic_extensions_license_active_sites', true );
            $license_start_date = get_post_meta( $license->ID, '_pronamic_extensions_license_start_date'  , true );
            $license_end_date   = get_post_meta( $license->ID, '_pronamic_extensions_license_end_date'    , true );

            if ( strtotime( $license_start_date ) > time() ||
                 strtotime( $license_end_date )   < time() ) {

                wp_send_json( array( 'success' => false, 'error_code' => self::LICENSE_KEY_EXPIRED ) );
            }

            if ( is_array( $active_sites ) &&
                 array_key_exists( $site, $active_sites ) ) {

                // Exit with success early as the license has already been activated
                wp_send_json( array( 'success' => true, 'error_code' => self::LICENSE_KEY_ALREADY_ACTIVATED ) );
            }

            // The post parent of a license is the product
            if ( is_numeric( $license->post_parent ) &&
                 $license->post_parent > 0 ) {

                $product = get_post( $license->post_parent );

                if ( $product instanceof WP_Post ) {

                    $original_slug = get_post_meta( $product->ID, '_pronamic_extension_wp_org_slug', true );

                    // Check if the license key is used for the correct product
                    if ( $original_slug !== $slug ) {

                        wp_send_json( array( 'success' => false, 'error_code' => self::INVALID_PRODUCT_SLUG ) );
                    }

                    // Check if the license key is used for the correct product type
                    if ( $product_type !== $product->post_type ) {

                        wp_send_json( array( 'success' => false, 'error_code' => self::INVALID_PRODUCT_TYPE ) );
                    }

                    $active_sites[ $site ] = array( 'activation_date' => date( 'Y-m-d h:i:s' ) );

                    if ( update_post_meta( $license->ID, '_pronamic_extensions_license_active_sites', $active_sites ) ) {
                        wp_send_json( array( 'success' => true ) );
                    } else {
                        wp_send_json( array( 'success' => true, 'error_code' => self::LICENSE_KEY_COULD_NOT_BE_ACTIVATED ) );
                    }
                }
            }
        }

        wp_send_json( array( 'success' => false, 'error_code' => self::INVALID_LICENSE_KEY ) );
    }

    /**
     * Sets the passed license key as deactivated.
     */
    public function licenses_api_deactivate() {

        $license_key = filter_input( INPUT_POST, 'license_key', FILTER_SANITIZE_STRING );
        $site        = filter_input( INPUT_POST, 'site'       , FILTER_VALIDATE_URL );

        if ( strlen( $license_key ) <= 0 ) {
            wp_send_json( array( 'success' => false, 'error_code' => self::NO_LICENSE_KEY ) );
        }

        if ( ! isset( $site ) ) {
            wp_send_json( array( 'success' => false, 'error_code' => self::NO_SITE_URL ) );
        }

        $license_query = new WP_Query( array(
            'post_type'      => 'pronamic_license',
            's'              => $license_key,
            'posts_per_page' => 1,
        ) );

        // Check if any licenses were found
        if ( $license_query->have_posts() ) {

            $license = $license_query->next_post();

            $active_sites = get_post_meta( $license->ID, '_pronamic_extensions_license_active_sites', true );

            if ( is_array( $active_sites ) &&
                 array_key_exists( $site, $active_sites ) ) {

                unset( $active_sites[ $site ] );

                // Remove site from active sites
                if ( update_post_meta( $license->ID, '_pronamic_extensions_license_active_sites', $active_sites ) ) {
                    wp_send_json( array( 'success' => true ) );
                } else {
                    wp_send_json( array( 'success' => false, 'error_code' => self::LICENSE_KEY_COULD_NOT_BE_DEACTIVATED ) );
                }
            }

            wp_send_json( array( 'success' => false, 'error_code' => self::LICENSE_KEY_NOT_ACTIVE ) );
        }

        wp_send_json( array( 'success' => false, 'error_code' => self::INVALID_LICENSE_KEY ) );
    }

    /**
     * Check if a license key is valid and active.
     */
    public function licenses_api_check() {

        $license_key = filter_input( INPUT_POST, 'license_key', FILTER_SANITIZE_STRING );
        $site        = filter_input( INPUT_POST, 'site'       , FILTER_VALIDATE_URL );

        if ( strlen( $license_key ) <= 0 ) {
            wp_send_json( array( 'success' => false, 'error_code' => self::NO_LICENSE_KEY ) );
        }

        if ( ! isset( $site ) ) {
            wp_send_json( array( 'success' => false, 'error_code' => self::NO_SITE_URL ) );
        }

        $license_query = new WP_Query( array(
            'post_type'      => 'pronamic_license',
            's'              => $license_key,
            'posts_per_page' => 1,
        ) );

        // Check if any licenses were found
        if ( $license_query->have_posts() ) {

            $license = $license_query->next_post();

            $active_sites       = get_post_meta( $license->ID, '_pronamic_extensions_license_active_sites', true );
            $license_start_date = get_post_meta( $license->ID, '_pronamic_extensions_license_start_date'  , true );
            $license_end_date   = get_post_meta( $license->ID, '_pronamic_extensions_license_end_date'    , true );

            if ( strtotime( $license_start_date ) > time() ||
                 strtotime( $license_end_date )   < time() ) {

                wp_send_json( array( 'success' => false, 'error_code' => self::LICENSE_KEY_EXPIRED ) );
            }

            if ( ! is_array( $active_sites ) ||
                 ! array_key_exists( $site, $active_sites ) ) {

                wp_send_json( array( 'success' => false, 'error_code' => self::LICENSE_KEY_NOT_ACTIVE ) );
            }

            wp_send_json( array( 'success' => true ) );
        }

        wp_send_json( array( 'success' => false, 'error_code' => self::INVALID_LICENSE_KEY ) );
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
