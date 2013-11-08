<?php

class Pronamic_WP_Extensions_ExtensionsPlugin {
	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @var Pronamic_WP_Extensions_ExtensionsPlugin
	 */
	protected static $instance = null;

	//////////////////////////////////////////////////

	/**
	 * Plugin file
	 * 
	 * @var string
	 */
	public $file;

	/**
	 * Extensions API
	 * 
	 * @var Pronamic_WP_Extensions_ExtensionsApi
	 */
	public $api;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize Pronamic WordPress Extensions plugin
	 * 
	 * @param string $file
	 */
	private function __construct( $file ) {
		$this->file = $file;

		add_action( 'init', array( $this, 'init' ) );

		add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );

		// API
		$this->api = Pronamic_WP_Extensions_ExtensionsApi::get_instance();
		
		// Admin
		if ( is_admin() ) {
			Pronamic_WP_Extensions_ExtensionsAdmin::get_instance( $this );
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Initialize
	 */
	public function init() {
		// Post types
		register_post_type( 'pronamic_plugin', array(
			'labels'             => array(
				'name'               => _x( 'Plugins', 'post type general name', 'pronamic_wp_extensions' ),
				'singular_name'      => _x( 'Plugin', 'post type singular name', 'pronamic_wp_extensions' ),
				'add_new'            => _x( 'Add New', 'plugin', 'pronamic_wp_extensions' ),
				'add_new_item'       => __( 'Add New Plugin', 'pronamic_wp_extensions' ),
				'edit_item'          => __( 'Edit Plugin', 'pronamic_wp_extensions' ),
				'new_item'           => __( 'New Plugin', 'pronamic_wp_extensions' ),
				'view_item'          => __( 'View Plugin', 'pronamic_wp_extensions' ),
				'search_items'       => __( 'Search Plugins', 'pronamic_wp_extensions' ),
				'not_found'          => __( 'No plugins found', 'pronamic_wp_extensions' ),
				'not_found_in_trash' => __( 'No plugins found in Trash', 'pronamic_wp_extensions' ),
				'parent_item_colon'  => __( 'Parent Plugin:', 'pronamic_wp_extensions' ),
				'menu_name'          => __( 'Plugins', 'pronamic_wp_extensions' )
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'rewrite'            => array( 'slug' => 'plugins' ),
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields' ),
		) );
	
		register_post_type( 'pronamic_theme', array(
			'labels'             => array(
				'name'               => _x( 'Themes', 'post type general name', 'pronamic_wp_extensions' ),
				'singular_name'      => _x( 'Theme', 'post type singular name', 'pronamic_wp_extensions' ),
				'add_new'            => _x( 'Add New', 'theme', 'pronamic_wp_extensions' ),
				'add_new_item'       => __( 'Add New Theme', 'pronamic_wp_extensions' ),
				'edit_item'          => __( 'Edit Theme', 'pronamic_wp_extensions' ),
				'new_item'           => __( 'New Theme', 'pronamic_wp_extensions' ),
				'view_item'          => __( 'View Theme', 'pronamic_wp_extensions' ),
				'search_items'       => __( 'Search Themes', 'pronamic_wp_extensions' ),
				'not_found'          => __( 'No themes found', 'pronamic_wp_extensions' ),
				'not_found_in_trash' => __( 'No themes found in Trash', 'pronamic_wp_extensions' ),
				'parent_item_colon'  => __( 'Parent Theme:', 'pronamic_wp_extensions' ),
				'menu_name'          => __( 'Themes', 'pronamic_wp_extensions' )
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'rewrite'            => array( 'slug' => 'themes' ),
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields' ),
		) );
	}

	//////////////////////////////////////////////////

	/**
	 * Posts where 'post_title__in', used by API
	 * 
	 * @param string $where
	 * @param WP_Query $query
	 */
	public function posts_where( $where, $query ) {
		$titles = $query->get( 'post_title__in' );
	
		if ( is_array( $titles ) && ! empty ( $titles )  ) {
			// @see https://github.com/WordPress/WordPress/blob/3.7/wp-includes/post.php#L3806
			$post_titles = implode( "', '", esc_sql( $titles ) );
	
			$where .= " AND post_title IN ('$post_titles')";
		}
	
		return $where;
	}

	//////////////////////////////////////////////////

	/**
	 * Display/iinclude the specified file
	 * 
	 * @param string $file
	 */
	public function display( $file ) {
		include plugin_dir_path( $this->file ) . $file; 
	}

	//////////////////////////////////////////////////

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance( $file = false ) {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self( $file );
		}
	
		return self::$instance;
	}
}
