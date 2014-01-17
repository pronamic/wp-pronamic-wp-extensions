<?php
/**
 * @author Stefan Boonstra
 */
class Pronamic_WP_ExtensionsPlugin_License {

    /**
     * Instance of this class.
     *
     * @since 1.0.0
     *
     * @var Pronamic_WP_ExtensionsPlugin_License
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
     * License post type
     *
     * @const string
     */
    const POST_TYPE = 'pronamic_license';

    //////////////////////////////////////////////////

    /**
     * Constructor
     *
     * @param Pronamic_WP_ExtensionsPlugin_Plugin $plugin
     */
    private function __construct( Pronamic_WP_ExtensionsPlugin_Plugin $plugin) {

        $this->plugin = $plugin;

        add_action( 'init', array( $this, 'init' ) );

        add_filter( 'default_title', array( $this, 'maybe_generate_license_key' ) );

        add_action( 'woocommerce_order_status_processing', array( $this, 'generate_licenses_for_woocommerce_products' ) );
    }

    //////////////////////////////////////////////////

    /**
     * Initialize
     */
    public function init() {

        register_post_type( self::POST_TYPE, array(
            'labels'             => array(
                'name'               => _x( 'Licenses', 'post type general name', 'pronamic_wp_extensions' ),
                'singular_name'      => _x( 'License', 'post type singular name', 'pronamic_wp_extensions' ),
                'add_new'            => _x( 'Add New', 'plugin', 'pronamic_wp_extensions' ),
                'add_new_item'       => __( 'Add New License', 'pronamic_wp_extensions' ),
                'edit_item'          => __( 'Edit License', 'pronamic_wp_extensions' ),
                'new_item'           => __( 'New License', 'pronamic_wp_extensions' ),
                'view_item'          => __( 'View License', 'pronamic_wp_extensions' ),
                'search_items'       => __( 'Search Licenses', 'pronamic_wp_extensions' ),
                'not_found'          => __( 'No licenses found', 'pronamic_wp_extensions' ),
                'not_found_in_trash' => __( 'No licenses found in Trash', 'pronamic_wp_extensions' ),
                'parent_item_colon'  => __( 'Parent License:', 'pronamic_wp_extensions' ),
                'menu_name'          => __( 'Licenses', 'pronamic_wp_extensions' )
            ),
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'capability_type'    => 'post',
            'has_archive'        => true,
            'rewrite'            => array( 'slug' => 'licenses' ),
            'supports'           => array( 'title' ),
        ) );

        // Add a license taxonomy to products
        if ( post_type_exists( 'product' ) ) {

            register_taxonomy( 'product_licensing', 'product',
                array(
                    'hierarchical' => true,
                    'labels'       => array(
                        'name'              => _x( 'Product Licensing', 'license general name', 'pronamic_wp_extensions' ),
                        'singular_name'     => _x( 'Product Licensing', 'license singular name', 'pronamic_wp_extensions' ),
                        'search_items'      => __( 'Search Product Licensing Types', 'pronamic_wp_extensions' ),
                        'all_items'         => __( 'All Product Licensing Types', 'pronamic_wp_extensions' ),
                        'parent_item'       => __( 'Parent Product Licensing Type', 'pronamic_wp_extensions' ),
                        'parent_item_colon' => __( 'Parent Product Licensing Type:', 'pronamic_wp_extensions' ),
                        'edit_item'         => __( 'Edit Product Licensing Type', 'pronamic_wp_extensions' ),
                        'update_item'       => __( 'Update Product Licensing Type', 'pronamic_wp_extensions' ),
                        'add_new_item'      => __( 'Add New Product Licensing Type', 'pronamic_wp_extensions' ),
                        'new_item_name'     => __( 'New Product Licensing Type Name', 'pronamic_wp_extensions' ),
                        'menu_name'         => __( 'Licensing', 'pronamic_wp_extensions' ),
                    ),
                    'show_ui'      => true,
                    'query_var'    => true,
                    'rewrite'      => array( 'slug' => _x( 'product-license', 'slug', 'pronamic_wp_extensions' ) ),
                )
            );

            // Insert the default License Key term
            wp_insert_term(
                __( 'License Key', 'pronamic_wp_extensions' ),
                'product_licensing',
                array( 'slug' => 'license-key' )
            );
        }
    }

    //////////////////////////////////////////////////

    /**
     * Generate a v4 UUID.
     *
     * @see https://gist.github.com/dahnielson/508447
     *
     * @return string $license_key
     */
    public function generate_license_key() {

        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Filters the title of a new license to be a uniquely generated license key
     *
     * @param string $title
     *
     * @return string $title
     */
    public function maybe_generate_license_key( $title ) {

        if ( ! function_exists( 'get_current_screen' ) ) {
            return $title;
        }

        $current_screen = get_current_screen();

        if ( $current_screen->post_type === self::POST_TYPE ) {
            return $this->generate_license_key();
        }

        return $title;
    }

    /**
     * Called when a WooCommerce order gets its status updated.
     *
     * @param int $order_id
     */
    public function generate_licenses_for_woocommerce_products( $order_id ) {

        if ( ! class_exists( 'WC_Order' ) ) {
            return;
        }

        $order = new WC_Order( $order_id );

        $products = $order->get_items();

        // An empty array will make WP_Query drop the 'post__in' variable, -1 will make sure no products are retrieved when there are no product IDs
        $product_ids = array( -1 );

        foreach ( $products as $product ) {

            $product_ids[] = $product['product_id'];
        }

        // Get all products that have the license key term
        $licensed_products_query = new WP_Query( array(
            'post_type' => 'product',
            'post__in'  => $product_ids,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_licensing',
                    'field'    => 'slug',
                    'terms'    => 'license-key',
                )
            ),
        ) );

        $license_ids = array();

        // Loop through licensed products, generating licenses
        while ( $licensed_products_query->have_posts() ) {

            $licensed_product = $licensed_products_query->next_post();

            $extension_id = get_post_meta( $licensed_product->ID, 'extension_id', true );

            $license_id = wp_insert_post( array(
                'post_title'  => $this->generate_license_key(),
                'post_status' => 'publish',
                'post_type'   => self::POST_TYPE,
                'post_parent' => $extension_id,
            ) );

            if ( ! is_wp_error( $license_id ) ) {
                $license_ids[] = $license_id;
            }
        }

        // Get the current user to add license IDs to
        $current_user = wp_get_current_user();

        if ( $current_user instanceof WP_User ) {

            $current_license_ids = get_user_meta( $current_user->ID, '_pronamic_extensions_license_keys', true );

            if ( is_string( $current_license_ids ) ) {
                $current_license_ids = array();
            }

            $license_ids = array_merge( $current_license_ids, $license_ids );

            update_user_meta( $current_user->ID, '_pronamic_extensions_license_keys', $license_ids );
        }
    }

    //////////////////////////////////////////////////



    //////////////////////////////////////////////////

    /**
     * Return an instance of this class.
     *
     * @since 1.0.0
     *
     * @param Pronamic_WP_ExtensionsPlugin_Plugin $plugin
     *
     * @return Pronamic_WP_ExtensionsPlugin_License A single instance of this class.
     */
    public static function get_instance( Pronamic_WP_ExtensionsPlugin_Plugin $plugin ) {
        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self( $plugin );
        }

        return self::$instance;
    }
}