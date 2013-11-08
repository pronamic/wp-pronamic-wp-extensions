<?php

class Pronamic_WP_Extensions_ExtensionsAdmin {
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
	 * @var Pronamic_WP_Extensions_ExtensionsPlugin
	 */
	private $plugin;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize Pronamic WordPress Extensions admin
	 */
	private function __construct( Pronamic_WP_Extensions_ExtensionsPlugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	//////////////////////////////////////////////////

	/**
	 * Admin initialize
	 */
	public function admin_init() {
		// Settings - General
		add_settings_section(
			'pronamic_wp_extensions_general', // id
			__( 'General', 'pronamic_ideal' ), // title
			'__return_false', // callback
			'pronamic_wp_extensions' // page
		);
                
		add_settings_field(
			'pronamic_wp_plugins_path', // id
			__( 'Plugins Path', 'pronamic_ideal' ), // title
			array( $this, 'input_path' ), // callback
			'pronamic_wp_extensions', // page
			'pronamic_wp_extensions_general', // section
			array( 'label_for' => 'pronamic_wp_plugins_path' ) // args
		);
                
		add_settings_field(
			'pronamic_wp_themes_path', // id
			__( 'Themes Path', 'pronamic_ideal' ), // title
			array( $this, 'input_path' ), // callback
			'pronamic_wp_extensions', // page
			'pronamic_wp_extensions_general', // section
			array( 'label_for' => 'pronamic_wp_themes_path' ) // args
		);
                
		register_setting( 'pronamic_wp_extensions', 'pronamic_wp_plugins_path' );
		register_setting( 'pronamic_wp_extensions', 'pronamic_wp_themes_path' );
	}

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
			__( 'Pronamic Extensions', 'wpe' ),
			__( 'Pronamic Extensions', 'wpe' ),
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
				'pronamic_extension_version_control',
				__( 'Version Control', 'pronamic_wp_extensions' ),
				array( $this, 'meta_box_extension_version_control' ),
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
		}
	}

	/**
	 * Meta box for version control
	 */
	function meta_box_extension_version_control() {
		$this->plugin->display( 'admin/meta-box-version-control.php' );
	}
	
	/**
	 * Meta box for deploy
	 */
	function meta_box_extension_deploy() {
		$this->plugin->display( 'admin/meta-box-deploy.php' );
	}

	//////////////////////////////////////////////////

	/**
	 * Save post
	 * 
	 * @param string $post_id
	 * @param WP_Post $post
	 */
	public function save_post( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
	
		if ( ! isset( $_POST['pronamic_wp_extensions_nonce'] ) )
			return;
	
		if ( ! wp_verify_nonce( $_POST['pronamic_wp_extensions_nonce'], 'pronamic_wp_extension_save_post' ) )
			return;
	
		if ( ! current_user_can( 'edit_post', $post->ID ) )
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

	//////////////////////////////////////////////////

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance( Pronamic_WP_Extensions_ExtensionsPlugin $plugin ) {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self( $plugin );
		}
	
		return self::$instance;
	}
}
