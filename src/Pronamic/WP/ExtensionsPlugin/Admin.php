<?php

class Pronamic_WP_ExtensionsPlugin_Admin {
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
	 * Extensions plugin
	 * 
	 * @var Pronamic_WP_ExtensionsPlugin_Plugin
	 */
	private $plugin;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize Pronamic WordPress Extensions admin
	 */
	private function __construct( Pronamic_WP_ExtensionsPlugin_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_action( 'save_post', array( $this, 'save_extension_meta_version' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_extension_meta_github' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_extension_meta_bitbucket' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_extension_meta_wp_org' ), 10, 2 );

		add_action( 'admin_init', array( $this, 'maybe_deploy' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Admin initialize
	 */
	public function admin_init() {
		// Settings - General
		add_settings_section(
			'pronamic_wp_extensions_general', // id
			__( 'General', 'pronamic_wp_extensions' ), // title
			'__return_false', // callback
			'pronamic_wp_extensions' // page
		);
                
		add_settings_field(
			'pronamic_wp_plugins_path', // id
			__( 'Plugins Path', 'pronamic_wp_extensions' ), // title
			array( $this, 'input_path' ), // callback
			'pronamic_wp_extensions', // page
			'pronamic_wp_extensions_general', // section
			array( 'label_for' => 'pronamic_wp_plugins_path' ) // args
		);
                
		add_settings_field(
			'pronamic_wp_themes_path', // id
			__( 'Themes Path', 'pronamic_wp_extensions' ), // title
			array( $this, 'input_path' ), // callback
			'pronamic_wp_extensions', // page
			'pronamic_wp_extensions_general', // section
			array( 'label_for' => 'pronamic_wp_themes_path' ) // args
		);

		// Settings - Bitbucket
		add_settings_section(
			'pronamic_wp_extensions_bitbucket', // id
			__( 'Bitbucket', 'pronamic_wp_extensions' ), // title
			'__return_false', // callback
			'pronamic_wp_extensions' // page
		);
                
		add_settings_field(
			'pronamic_wp_bitbucket_username', // id
			__( 'Bitbucket Username', 'pronamic_wp_extensions' ), // title
			array( $this, 'input_text' ), // callback
			'pronamic_wp_extensions', // page
			'pronamic_wp_extensions_bitbucket', // section
			array(
				'label_for' => 'pronamic_wp_bitbucket_username',
				'classes'   => array( 'regular-text', 'code' ),
			) // args
		);
                
		add_settings_field(
			'pronamic_wp_bitbucket_password', // id
			__( 'Bitbucket Password', 'pronamic_wp_extensions' ), // title
			array( $this, 'input_text' ), // callback
			'pronamic_wp_extensions', // page
			'pronamic_wp_extensions_bitbucket', // section
			array(
				'label_for' => 'pronamic_wp_bitbucket_password',
				'classes'   => array( 'regular-text', 'code' ),
			) // args
		);

		// Register
		register_setting( 'pronamic_wp_extensions', 'pronamic_wp_plugins_path' );
		register_setting( 'pronamic_wp_extensions', 'pronamic_wp_themes_path' );
		register_setting( 'pronamic_wp_extensions', 'pronamic_wp_bitbucket_username' );
		register_setting( 'pronamic_wp_extensions', 'pronamic_wp_bitbucket_password' );
	}

	/**
	 * Input path
	 * 
	 * @param array $args
	 */
	public function input_text( $args ) {
		$name = $args['label_for'];
		
		$classes = array();
		if ( isset( $args['classes'] ) ) {
			$classes = $args['classes'];
		}

		printf(
			'<input name="%s" id="%s" type="text" class="%s" value="%s" />',
			esc_attr( $name ),
			esc_attr( $name ),
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( get_option( $name ) )
		);
	}

	/**
	 * Input path
	 * 
	 * @param array $args
	 */
	public function input_path( $args ) {
		echo ABSPATH;
		
		$name = $args['label_for'];

		printf(
			'<input name="%s" id="%s" type="text" class="%s" value="%s" />',
			esc_attr( $name ),
			esc_attr( $name ),
			esc_attr( 'regular-text code' ),
			esc_attr( get_option( $name ) )
		);
		
		echo '/';
	}

	//////////////////////////////////////////////////

	/**
	 * Admin menu
	 */
	public function admin_menu() {
		add_options_page(
			__( 'Pronamic Extensions', 'pronamic_wp_extensions' ),
			__( 'Pronamic Extensions', 'pronamic_wp_extensions' ),
			'manage_options',
			'pronamic_wp_extensions',
			array( $this, 'page_options' )
		);
	}
	
	/**
	 * Page options
	 */
	public function page_options() {
		$this->plugin->display( 'admin/page-options.php' );
	}

	//////////////////////////////////////////////////

	/**
	 * Add meta boxes
	 */
	public function add_meta_boxes() {
		$screens = array( 'pronamic_plugin', 'pronamic_theme' );

		foreach ( $screens as $screen ) {
			add_meta_box(
				'pronamic_extension_version',
				__( 'Version', 'pronamic_wp_extensions' ),
				array( $this, 'pronamic_extension_version' ),
				$screen,
				'normal',
				'high'
			);

			add_meta_box(
				'pronamic_extension_github',
				__( 'GitHub', 'pronamic_wp_extensions' ),
				array( $this, 'meta_box_extension_github' ),
				$screen,
				'normal',
				'high'
			);

			add_meta_box(
				'pronamic_extension_bitbucket',
				__( 'Bitbucket', 'pronamic_wp_extensions' ),
				array( $this, 'meta_box_extension_bitbucket' ),
				$screen,
				'normal',
				'high'
			);

			add_meta_box(
				'pronamic_extension_wp_org',
				__( 'WordPress.org', 'pronamic_wp_extensions' ),
				array( $this, 'meta_box_extension_wp_org' ),
				$screen,
				'normal',
				'high'
			);
	
			add_meta_box(
				'pronamic_extension_deploy',
				__( 'Deploy', 'pronamic_wp_extensions' ),
				array( $this, 'meta_box_extension_deploy' ),
				$screen,
				'normal',
				'high'
			);
	
			add_meta_box(
				'pronamic_extension_downloads',
				__( 'Downloads', 'pronamic_wp_extensions' ),
				array( $this, 'meta_box_extension_downloads' ),
				$screen,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Meta box for version control
	 */
	function pronamic_extension_version() {
		$this->plugin->display( 'admin/meta-box-version.php' );
	}

	/**
	 * Meta box for GitHub
	 */
	function meta_box_extension_github() {
		$this->plugin->display( 'admin/meta-box-github.php' );
	}

	/**
	 * Meta box for Bitbucket
	 */
	function meta_box_extension_bitbucket() {
		$this->plugin->display( 'admin/meta-box-bitbucket.php' );
	}

	/**
	 * Meta box for WordPress.org
	 */
	function meta_box_extension_wp_org() {
		$this->plugin->display( 'admin/meta-box-wp-org.php' );
	}

	/**
	 * Meta box for deploy
	 */
	function meta_box_extension_deploy() {
		$this->plugin->display( 'admin/meta-box-deploy.php' );
	}
	
	/**
	 * Meta box for downloads
	 */
	function meta_box_extension_downloads() {
		$this->plugin->display( 'admin/meta-box-downloads.php' );
	}

	//////////////////////////////////////////////////

	/**
	 * Can save
	 * 
	 * @param string $nonce
	 * @param string $action
	 * @param string $post_id
	 * @return boolean
	 */
	private function can_save( $post_id, $nonce, $action ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return false;
		
		if ( ! filter_has_var( INPUT_POST, $nonce ) )
			return false;
		
		if ( ! wp_verify_nonce( filter_input( INPUT_POST, $nonce, FILTER_SANITIZE_STRING ), $action ) )
			return false;
		
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return false;

		return true;
	}
	
	private function save_extension_meta( $post_id, $definition ) {
		// Save data
		$data = filter_input_array( INPUT_POST, $definition );
		
		foreach ( $data as $key => $value ) {
			if ( empty( $value ) ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Save post
	 * 
	 * @param string $post_id
	 * @param WP_Post $post
	 */
	public function save_extension_meta_version( $post_id, $post ) {
		if ( ! $this->can_save( $post_id, 'pronamic_wp_extensions_meta_version_nonce', 'pronamic_wp_extension_save_meta_version' ) )
			return;
	
		$this->save_extension_meta( $post_id, array(
			'_pronamic_extension_stable_version' => FILTER_SANITIZE_STRING,
		) );
	}

	public function save_extension_meta_github( $post_id, $post ) {
		if ( ! $this->can_save( $post_id, 'pronamic_wp_extensions_meta_github_nonce', 'pronamic_wp_extension_save_meta_github' ) )
			return;
	
		$this->save_extension_meta( $post_id, array(
			'_pronamic_extension_github_user' => FILTER_SANITIZE_STRING,
			'_pronamic_extension_github_repo' => FILTER_SANITIZE_STRING,
		) );
	}

	public function save_extension_meta_bitbucket( $post_id, $post ) {
		if ( ! $this->can_save( $post_id, 'pronamic_wp_extensions_meta_bitbucket_nonce', 'pronamic_wp_extension_save_meta_bitbucket' ) )
			return;
	
		$this->save_extension_meta( $post_id, array(
			'_pronamic_extension_bitbucket_user' => FILTER_SANITIZE_STRING,
			'_pronamic_extension_bitbucket_repo' => FILTER_SANITIZE_STRING,
		) );
	}

	public function save_extension_meta_wp_org( $post_id, $post ) {
		if ( ! $this->can_save( $post_id, 'pronamic_wp_extensions_meta_wp_org_nonce', 'pronamic_wp_extension_save_meta_wp_org' ) )
			return;
	
		$this->save_extension_meta( $post_id, array(
			'_pronamic_extension_wp_org_slug' => FILTER_SANITIZE_STRING,
		) );
	}

	//////////////////////////////////////////////////

	/**
	 * Maybe deploy
	 */
	public function maybe_deploy() {
		if ( filter_has_var( INPUT_POST, 'pronamic_extension_deploy' ) ) {
			$post_id = filter_input( INPUT_POST, 'post_ID', FILTER_SANITIZE_STRING );

			$post    = get_post( $post_id );

			$version = get_post_meta( $post_id, '_pronamic_extension_stable_version', true );
			
			$deploy_path = false;
			
			switch ( $post->post_type ) {
				case 'pronamic_plugin':
					$deploy_path = ABSPATH . get_option( 'pronamic_wp_plugins_path' ) . DIRECTORY_SEPARATOR . $post->post_name;
			
					break;
				case 'pronamic_theme':
					$deploy_path = ABSPATH . get_option( 'pronamic_wp_themes_path' ) . DIRECTORY_SEPARATOR . $post->post_name;
			
					break;
			}
			
			$download_url = sprintf(
				'https://%s:%s@bitbucket.org/%s/%s/get/%s.zip',
				get_option( 'pronamic_wp_bitbucket_username' ),
				get_option( 'pronamic_wp_bitbucket_password' ),
				get_post_meta( $post->ID, '_pronamic_extension_bitbucket_user', true ),
				get_post_meta( $post->ID, '_pronamic_extension_bitbucket_repo', true ),
				$version
			);
			
			$deploy_file = $deploy_path . DIRECTORY_SEPARATOR . $post->post_name . '.' . $version . '.zip';
			
			if ( filter_has_var( INPUT_POST, 'pronamic_extension_deploy' ) ) {
				// Download
				$tmpfname = wp_tempnam( $download_url );
			
				$response = wp_remote_get( $download_url, array( 'timeout' => 300, 'stream' => true, 'filename' => $tmpfname ) );

				if ( is_wp_error( $response ) ) {
					unlink( $tmpfname );
					
					var_dump( $response );

					exit;
				}

				if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
					unlink( $tmpfname );
					
					var_dump( $response );
					
					exit;
				}
			
				$zip = new ZipArchive();
			
				$result = $zip->open( $tmpfname );
					
				if ( $result === true ) {
					$old_dir = $zip->getNameIndex( 0 );
					$new_dir = $post->post_name . '/';

					$i = 0;
					while ( $item_name = $zip->getNameIndex( $i ) ) {					
						$new_name = str_replace( $old_dir, $new_dir, $item_name );

						$zip->renameIndex( $i, $new_name );

						$i++;
					}

					$zip->close();
				} else {
					echo 'failed, code:' . $res;
				}

				$moved = rename( $tmpfname, $deploy_file );
				
				$url = add_query_arg( array(
					'post'     => $post_id,
					'action'   => 'edit',
					'deployed' => $moved ? 'true' : 'false',
				) );
				
				wp_redirect( $url );
				
				exit;
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
	public static function get_instance( Pronamic_WP_ExtensionsPlugin_Plugin $plugin ) {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self( $plugin );
		}
	
		return self::$instance;
	}
}
