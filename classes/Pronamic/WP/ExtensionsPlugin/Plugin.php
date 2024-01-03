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

	
	/**
	 * Constructs and initialize Pronamic WordPress Extensions plugin
	 *
	 * @param string $file
	 */
	private function __construct( $file ) {
		$this->file = $file;

		add_action( 'init', [ $this, 'init' ] );

		add_filter( 'posts_where', [ $this, 'posts_where' ], 10, 2 );
		add_filter( 'pre_get_posts', [ $this, 'posts_order' ], 10, 2 );

		// @see https://github.com/WordPress/WordPress/blob/4.1/wp-includes/comment-template.php#L693-L701
		add_filter( 'get_comments_link', [ $this, 'get_comments_link' ], 10, 2 );
		// @see https://github.com/WordPress/WordPress/blob/4.1/wp-includes/comment-template.php#L669-L680
		add_filter( 'get_comment_link', [ $this, 'get_comment_link' ], 10, 3 );

		// API
		$this->api = Pronamic_WP_ExtensionsPlugin_Api::get_instance( $this );

		// Admin
		if ( is_admin() ) {
			Pronamic_WP_ExtensionsPlugin_Admin::get_instance( $this );
		}

		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'pronamic-wp-extensions/v1',
					'/plugins/(?P<slug>[\w-]+)',
					[
						'methods'             => 'PATCH',
						'callback'            => function ( $request ) {
							$slug = $request->get_param( 'slug' );

							$post = get_page_by_path( $slug, OBJECT, 'pronamic_plugin' );

							if ( null === $post ) {
								return new WP_Error( 'Could not find plugin.' );
							}

							if ( $request->has_param( 'version' ) ) {
								$version = $request->get_param( 'version' );

								update_post_meta( $post->ID, '_pronamic_extension_stable_version', $version );
							}

							return [
								'id'      => $post->ID,
								'slug'    => $slug,
								'version' => get_post_meta( $post->ID, '_pronamic_extension_stable_version', true ),
							];
						},
						'args'                => [
							'slug'    => [
								'description' => __( 'Plugin slug.' ),
								'type'        => 'string',
								'minLength'   => 1,
								'maxLength'   => 200,
								'required'    => true,
							],
							'version' => [
								'description' => __( 'Plugin version.' ),
								'type'        => 'string',
								'minLength'   => 1,
							],
						],
						'permission_callback' => function ( $request ) {
							$slug = $request->get_param( 'slug' );

							$post = get_page_by_path( $slug, OBJECT, 'pronamic_plugin' );

							if ( null === $post ) {
								return false;
							}

							return current_user_can( 'edit_post', $post->ID );
						},
					]
				);

				register_rest_route(
					'pronamic-wp-extensions/v1',
					'/themes/(?P<slug>[\w-]+)',
					[
						'methods'             => 'PATCH',
						'callback'            => function ( $request ) {
							$slug = $request->get_param( 'slug' );

							$post = get_page_by_path( $slug, OBJECT, 'pronamic_theme' );

							if ( null === $post ) {
								return new WP_Error( 'Could not find theme.' );
							}

							if ( $request->has_param( 'version' ) ) {
								$version = $request->get_param( 'version' );

								update_post_meta( $post->ID, '_pronamic_extension_stable_version', $version );
							}

							return [
								'id'      => $post->ID,
								'slug'    => $slug,
								'version' => get_post_meta( $post->ID, '_pronamic_extension_stable_version', true ),
							];
						},
						'args'                => [
							'slug'    => [
								'description' => __( 'Theme slug.' ),
								'type'        => 'string',
								'minLength'   => 1,
								'maxLength'   => 200,
								'required'    => true,
							],
							'version' => [
								'description' => __( 'Theme version.' ),
								'type'        => 'string',
								'minLength'   => 1,
							],
						],
						'permission_callback' => function ( $request ) {
							$slug = $request->get_param( 'slug' );

							$post = get_page_by_path( $slug, OBJECT, 'pronamic_theme' );

							if ( null === $post ) {
								return false;
							}

							return current_user_can( 'edit_post', $post->ID );
						},
					]
				);
			}
		);
	}

	
	/**
	 * Initialize
	 */
	public function init() {
		// Post types
		register_post_type(
			'pronamic_plugin',
			[
				'labels'             => [
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
					'menu_name'          => __( 'Plugins', 'pronamic_wp_extensions' ),
				],
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'capability_type'    => 'post',
				'has_archive'        => true,
				'show_in_rest'       => true,
				'rewrite'            => [ 'slug' => 'plugins' ],
				'menu_icon'          => 'dashicons-clipboard',
				'supports'           => [
					'title',
					'editor',
					'author',
					'thumbnail',
					'excerpt',
					'custom-fields',
					'comments',
					'page-attributes',
					'pronamic-extension',
				],
			] 
		);

		register_post_type(
			'pronamic_theme',
			[
				'labels'             => [
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
					'menu_name'          => __( 'Themes', 'pronamic_wp_extensions' ),
				],
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'capability_type'    => 'post',
				'has_archive'        => true,
				'show_in_rest'       => true,
				'rewrite'            => [ 'slug' => 'themes' ],
				'menu_icon'          => 'dashicons-clipboard',
				'supports'           => [
					'title',
					'editor',
					'author',
					'thumbnail',
					'excerpt',
					'custom-fields',
					'comments',
					'page-attributes',
					'pronamic-extension',
				],
			] 
		);

		// Taxonomies
		register_taxonomy(
			'pronamic_plugin_category',
			'pronamic_plugin',
			[
				'hierarchical' => true,
				'labels'       => [
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
				],
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => [ 'slug' => _x( 'plugin-category', 'slug', 'pronamic_wp_extensions' ) ],
			]
		);

		register_taxonomy(
			'pronamic_theme_category',
			'pronamic_theme',
			[
				'hierarchical' => true,
				'labels'       => [
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
				],
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => [ 'slug' => _x( 'theme-category', 'slug', 'pronamic_wp_extensions' ) ],
			]
		);

		// Permalinks
		add_rewrite_endpoint( 'comments', EP_PERMALINK );
		add_rewrite_endpoint( 'changelog', EP_PERMALINK );
		add_rewrite_endpoint( 'faq', EP_PERMALINK );
	}

	
	/**
	 * Posts where 'post_title__in', used by API
	 *
	 * @param string   $where
	 * @param WP_Query $query
	 */
	public function posts_where( $where, $query ) {
		$titles = $query->get( 'post_title__in' );

		if ( is_array( $titles ) && ! empty( $titles ) ) {
			// @see https://github.com/WordPress/WordPress/blob/3.7/wp-includes/post.php#L3806
			$post_titles = implode( "', '", esc_sql( $titles ) );

			$where .= " AND post_title IN ('$post_titles')";
		}

		return $where;
	}

	/**
	 * Change order on archive
	 *
	 * @param WP_Query $query
	 */
	function posts_order( $query ) {
		if ( is_admin() ) {
			return;
		}

		if ( ! $query->is_main_query() ) {
			return;
		}

		if ( ! $query->is_post_type_archive( 'pronamic_plugin' ) && ! $query->is_post_type_archive( 'pronamic_theme' ) ) {
			return;
		}

		$orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );

		switch ( $orderby ) {
			case 'name':
				$query->set( 'meta_key', '_pronamic_extension_total_downloads' );
				$query->set( 'orderby', 'name' );
				$query->set( 'order', 'ASC' );

				break;
			case 'popularity':
				$query->set( 'meta_key', '_pronamic_extension_total_downloads' );
				$query->set( 'orderby', 'meta_value_num' );

				break;
			case 'sales':
				$query->set( 'meta_key', '_pronamic_extension_total_sales' );
				$query->set( 'orderby', 'meta_value_num' );

				break;
			case 'price':
				$query->set( 'meta_key', '_pronamic_extension_price' );
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'order', 'ASC' );

				break;
			default:
				$query->set( 'orderby', 'menu_order' );
		}

		// Hide private extensions
		$meta_query = $query->get( 'meta_query', [] );
		$meta_query = is_array( $meta_query ) ? $meta_query : [];

		$meta_query['relation'] = 'OR';

		$meta_query[] = [
			'key'     => '_pronamic_extension_is_private',
			'value'   => 1,
			'compare' => '!=',
		];

		$meta_query[] = [
			'key'     => '_pronamic_extension_is_private',
			'compare' => 'NOT EXISTS',
		];

		$query->set( 'meta_query', $meta_query );
	}

	
	/**
	 * Get comments link
	 *
	 * @see https://github.com/WordPress/WordPress/blob/4.1/wp-includes/comment-template.php#L693-L701
	 */
	public function get_comments_link( $comments_link, $post_id ) {
		$post_type = get_post_type( $post_id );

		if ( post_type_supports( $post_type, 'pronamic-extension' ) ) {
			$comments_link = get_permalink() . 'comments/';
		}

		return $comments_link;
	}

	/**
	 * Get comment link
	 *
	 * @see https://github.com/WordPress/WordPress/blob/4.1/wp-includes/comment-template.php#L669-L680
	 */
	public function get_comment_link( $link, $comment, $args ) {
		$post_type = get_post_type( $comment->comment_post_ID );

		if ( post_type_supports( $post_type, 'pronamic-extension' ) ) {
			$link = get_comments_link( $comment->comment_post_ID );

			$link = $link . '#comment-' . $comment->comment_ID;
		}

		return $link;
	}

	
	/**
	 * Display/iinclude the specified file
	 *
	 * @param string $file
	 */
	public function display( $file, array $args = [] ) {
		extract( $args );

		include plugin_dir_path( $this->file ) . $file;
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
