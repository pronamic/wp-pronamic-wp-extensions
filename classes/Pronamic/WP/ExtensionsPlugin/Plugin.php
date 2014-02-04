<?php

class Pronamic_WP_ExtensionsPlugin_Plugin {
	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @var Pronamic_WP_ExtensionsPlugin_Plugin
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
	 * @var Pronamic_WP_ExtensionsPlugin_Api
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
		$this->api = Pronamic_WP_ExtensionsPlugin_Api::get_instance( $this );
		
		// Admin
		if ( is_admin() ) {
			Pronamic_WP_ExtensionsPlugin_Admin::get_instance( $this );
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
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields' ),
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
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields' ),
		) );
		
		// Taxonomies
		register_taxonomy( 'pronamic_plugin_category', 'pronamic_plugin',
			array(
				'hierarchical' => true,
				'labels'       => array(
					'name'              => _x( 'Plugin Category', 'category general name', 'pronamic_wp_extensions' ),
					'singular_name'     => _x( 'Plugin Category', 'category singular name', 'pronamic_wp_extensions' ),
					'search_items'      => __( 'Search Plugin Categories', 'pronamic_wp_extensions' ),
					'all_items'         => __( 'All Plugin Categories', 'pronamic_wp_extensions' ),
					'parent_item'       => __( 'Parent Plugin Category', 'pronamic_wp_extensions' ),
					'parent_item_colon' => __( 'Parent Plugin Category:', 'pronamic_wp_extensions' ),
					'edit_item'         => __( 'Edit Plugin Category', 'pronamic_wp_extensions' ),
					'update_item'       => __( 'Update Plugin Category', 'pronamic_wp_extensions' ),
					'add_new_item'      => __( 'Add New Plugin Category', 'pronamic_wp_extensions' ),
					'new_item_name'     => __( 'New Plugin Category Name', 'pronamic_wp_extensions' ),
					'menu_name'         => __( 'Categories', 'pronamic_wp_extensions' ),
				),
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => array( 'slug' => _x( 'plugin-category', 'slug', 'pronamic_wp_extensions' ) ),
			)
        );

		register_taxonomy( 'pronamic_theme_category', 'pronamic_theme',
			array(
				'hierarchical' => true,
				'labels'       => array(
					'name'              => _x( 'Theme Category', 'category general name', 'pronamic_wp_extensions' ),
					'singular_name'     => _x( 'Theme Category', 'category singular name', 'pronamic_wp_extensions' ),
					'search_items'      => __( 'Search Theme Categories', 'pronamic_wp_extensions' ),
					'all_items'         => __( 'All Theme Categories', 'pronamic_wp_extensions' ),
					'parent_item'       => __( 'Parent Theme Category', 'pronamic_wp_extensions' ),
					'parent_item_colon' => __( 'Parent Theme Category:', 'pronamic_wp_extensions' ),
					'edit_item'         => __( 'Edit Theme Category', 'pronamic_wp_extensions' ),
					'update_item'       => __( 'Update Theme Category', 'pronamic_wp_extensions' ),
					'add_new_item'      => __( 'Add New Theme Category', 'pronamic_wp_extensions' ),
					'new_item_name'     => __( 'New Theme Category Name', 'pronamic_wp_extensions' ),
					'menu_name'         => __( 'Categories', 'pronamic_wp_extensions' ),
				),
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => array( 'slug' => _x( 'theme-category', 'slug', 'pronamic_wp_extensions' ) ),
			)
        );
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
	public function display( $file, array $args = array() ) {
		extract( $args );

		include plugin_dir_path( $this->file ) . $file; 
	}

	//////////////////////////////////////////////////

    /**
     * Get download path
     * 
     * @return string
     */
    public function get_downloads_path( $type ) {
		$path = false;

		switch ( $type ) {
			case 'pronamic_plugin':
				$path = get_option( 'pronamic_wp_plugins_path' );

				break;
			case 'pronamic_theme':
				$path = get_option( 'pronamic_wp_themes_path' );

				break;
		}
		
		$path = ABSPATH . DIRECTORY_SEPARATOR . $path;

		return $path;
	}

    /**
     * Get download URL
     * 
     * @return string
     */
    public function get_downloads_url( $type ) {
		$url = false;

		switch ( $type ) {
			case 'pronamic_plugin':
				$url = get_option( 'pronamic_wp_plugins_url' );

				break;
			case 'pronamic_theme':
				$url = get_option( 'pronamic_wp_themes_url' );

				break;
		}

		return $url;
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
