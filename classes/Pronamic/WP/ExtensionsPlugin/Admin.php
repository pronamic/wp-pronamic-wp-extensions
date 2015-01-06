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

		add_action( 'save_post', array( $this, 'save_extension_meta_extension' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_extension_meta_sale' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_extension_meta_github' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_extension_meta_bitbucket' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_extension_meta_wp_org' ), 10, 2 );
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
			'pronamic_wp_plugins_url', // id
			__( 'Plugins URL', 'pronamic_wp_extensions' ), // title
			array( $this, 'input_text' ), // callback
			'pronamic_wp_extensions', // page
			'pronamic_wp_extensions_general', // section
			array(
				'label_for' => 'pronamic_wp_plugins_url',
				'classes'   => array( 'regular-text', 'code' ),
			) // args
		);
                
		add_settings_field(
			'pronamic_wp_themes_url', // id
			__( 'Themes URL', 'pronamic_wp_extensions' ), // title
			array( $this, 'input_text' ), // callback
			'pronamic_wp_extensions', // page
			'pronamic_wp_extensions_general', // section
			array(
				'label_for' => 'pronamic_wp_themes_url',
				'classes'   => array( 'regular-text', 'code' ),
			) // args
		);

		// Register
		register_setting( 'pronamic_wp_extensions', 'pronamic_wp_plugins_url' );
		register_setting( 'pronamic_wp_extensions', 'pronamic_wp_themes_url' );
	}

	/**
	 * Input text
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

	public function lines_to_array( $value ) {
		if ( is_string( $value ) ) {
			$value = explode( "\r\n", $value );
		}

		return $value;
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
	public function add_meta_boxes( $post_type ) {
		if ( post_type_supports( $post_type, 'pronamic-extension' ) ) {
			add_meta_box(
				'pronamic_extension_extension',
				__( 'Extension', 'pronamic_wp_extensions' ),
				array( $this, 'meta_box_extension' ),
				$post_type,
				'normal',
				'high'
			);

			add_meta_box(
				'pronamic_extension_sale',
				__( 'Sale', 'pronamic_wp_extensions' ),
				array( $this, 'meta_box_extension_sale' ),
				$post_type,
				'normal',
				'high'
			);

			add_meta_box(
				'pronamic_extension_github',
				__( 'GitHub', 'pronamic_wp_extensions' ),
				array( $this, 'meta_box_extension_github' ),
				$post_type,
				'normal',
				'high'
			);

			add_meta_box(
				'pronamic_extension_bitbucket',
				__( 'Bitbucket', 'pronamic_wp_extensions' ),
				array( $this, 'meta_box_extension_bitbucket' ),
				$post_type,
				'normal',
				'high'
			);

			add_meta_box(
				'pronamic_extension_wp_org',
				__( 'WordPress.org', 'pronamic_wp_extensions' ),
				array( $this, 'meta_box_extension_wp_org' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Meta box for version control
	 */
	function meta_box_extension() {
		$this->plugin->display( 'admin/meta-box-extension.php' );
	}

	/**
	 * Meta box for sale
	 */
	function meta_box_extension_sale() {
		$this->plugin->display( 'admin/meta-box-sale.php' );
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
	public function save_extension_meta_extension( $post_id, $post ) {
		if ( ! $this->can_save( $post_id, 'pronamic_wp_extensions_meta_extension_nonce', 'pronamic_wp_extension_save_meta_extension' ) )
			return;
	
		$this->save_extension_meta( $post_id, array(
			'_pronamic_extension_stable_version'  => FILTER_SANITIZE_STRING,
			'_pronamic_extension_total_downloads' => FILTER_SANITIZE_STRING,
			'_pronamic_extension_is_private'      => FILTER_VALIDATE_BOOLEAN,
		) );
	}

	public function save_extension_meta_sale( $post_id, $post ) {
		if ( ! $this->can_save( $post_id, 'pronamic_wp_extensions_meta_sale_nonce', 'pronamic_wp_extension_save_meta_sale' ) )
			return;
	
		$this->save_extension_meta( $post_id, array(
			'_pronamic_extension_price'       => FILTER_SANITIZE_STRING,
			'_pronamic_extension_total_sales' => FILTER_SANITIZE_STRING,
			'_pronamic_extension_buy_url'     => FILTER_SANITIZE_STRING,
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
