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

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
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
