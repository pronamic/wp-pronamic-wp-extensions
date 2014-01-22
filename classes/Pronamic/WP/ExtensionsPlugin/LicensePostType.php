<?php
/**
 * @author Stefan Boonstra
 */
class Pronamic_WP_ExtensionsPlugin_LicensePostType {

    /**
     * Instance of this class.
     *
     * @since 1.0.0
     *
     * @var Pronamic_WP_ExtensionsPlugin_LicensePostType
     */
    protected static $instance = null;

    //////////////////////////////////////////////////

    /**
     * Extensions plugin.
     *
     * @var Pronamic_WP_ExtensionsPlugin_Plugin
     */
    private $plugin;

    //////////////////////////////////////////////////

    /**
     * Array of License object generated this session. This used for mailing the license keys to the user on payment.
     *
     * @var array Array of Pronamic_WP_ExtensionsPlugin_License objects
     */
    private $products_with_generated_licenses = array();

    //////////////////////////////////////////////////

    /**
     * License post type.
     *
     * @const string
     */
    const POST_TYPE = 'pronamic_license';

    //////////////////////////////////////////////////

    /**
     * Meta key with which the WooCommerce order's license keys are stored.
     *
     * @const string
     */
    const WOOCOMMERCE_ORDER_LICENSE_KEYS_META_KEY = '_pronamic_wp_extensions_license_keys';

    //////////////////////////////////////////////////

    /**
     * Constructor.
     *
     * @param Pronamic_WP_ExtensionsPlugin_Plugin $plugin
     */
    private function __construct( Pronamic_WP_ExtensionsPlugin_Plugin $plugin ) {

        $this->plugin = $plugin;

        // Actions
        add_action( 'init', array( $this, 'init' ) );

        add_action( 'add_meta_boxes', array( $this, 'woocommerce_order_add_license_key_meta_box' ), 10, 2 );

        add_action( 'edit_user_profile', array( $this, 'add_license_keys_to_user_profile' ) );
        add_action( 'show_user_profile', array( $this, 'add_license_keys_to_user_profile' ) );

        add_action( 'woocommerce_order_status_pending_to_processing', array( $this, 'woocommerce_order_status_pending_to_processing_generate_licenses' ) );
        add_action( 'woocommerce_order_status_pending_to_complete', array( $this, 'woocommerce_order_status_pending_to_processing_generate_licenses' ) );

        add_action( 'woocommerce_email_order_meta', array( $this, 'woocommerce_email_order_meta_add_license_keys' ) );

        add_action( 'woocommerce_order_details_after_order_table', array( $this, 'woocommerce_order_details_after_order_table_add_license_keys' ) );

        add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'manage_pronamic_license_posts_custom_column' ), 10, 2 );

        // Filters
        add_filter( 'default_title', array( $this, 'maybe_generate_license_key' ) );

        add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'manage_pronamic_license_posts_columns' ) );
    }

    //////////////////////////////////////////////////

    /**
     * Initialize.
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
     * Adds a license keys meta box to the WooCommerce shop order post type.
     */
    public function woocommerce_order_add_license_key_meta_box() {

        add_meta_box(
            'pronamic_woocommerce_order_license_key_meta_box',
            __( 'License Keys', 'pronamic_wp_extensions' ),
            array( $this, 'woocommerce_order_license_key_meta_box' ),
            'shop_order',
            'normal',
            'default'
        );
    }

    /**
     * Renders the Pronamic WooCoommerce license keys meta box.
     *
     * @param WP_Post $post
     */
    public function woocommerce_order_license_key_meta_box( $post ) {

        $license_ids = get_post_meta( $post->ID, self::WOOCOMMERCE_ORDER_LICENSE_KEYS_META_KEY, true );

        if ( ! is_array( $license_ids ) ) {
            $license_ids = array( -1 );
        }

        $license_query = new WP_Query( array(
            'post_type'      => self::POST_TYPE,
            'post__in'       => $license_ids,
            'orderby'        => 'parent',
            'posts_per_page' => -1,
        ) );

        $this->plugin->display( 'admin/meta-box-order-license-keys.php', array( 'license_query' => $license_query ) );
    }

    //////////////////////////////////////////////////

    /**
     * Adds the user's license keys to the user profile page.
     *
     * @param WP_User $user
     */
    public function add_license_keys_to_user_profile( WP_User $user ) {

        $this->plugin->display( 'admin/user-profile-license-keys.php', array( 'user' => $user ) );
    }

    //////////////////////////////////////////////////

    /**
     * Called when a WooCommerce order gets its status updated.
     *
     * @param int $order_id
     */
    public function woocommerce_order_status_pending_to_processing_generate_licenses( $order_id ) {

        if ( ! class_exists( 'WC_Order' ) ) {
            return;
        }

        $order = new WC_Order( $order_id );

        $products = $order->get_items();

        // An empty array will make WP_Query drop the 'post__in' variable, -1 will make sure no products are retrieved when there are no product IDs
        $product_ids        = array( -1 );
        $product_quantities = array();

        foreach ( $products as $product ) {

            $product_ids[] = $product['product_id'];

            $product_quantities[ $product['product_id'] ] = $product['qty'];
        }

        // Get all products that have the license key term
        $licensed_products_query = new WP_Query( array(
            'post_type'      => 'product',
            'post__in'       => $product_ids,
            'posts_per_page' => -1,
            'tax_query'      => array(
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

            // When the product's quantity is greater than zero, prepare the array of licenses
            if ( $product_quantities[ $licensed_product->ID ] > 0 ) {

                $this->products_with_generated_licenses[ $licensed_product->ID ]['product']  = $licensed_product;
                $this->products_with_generated_licenses[ $licensed_product->ID ]['licenses'] = array();
            }

            // Generate as many licenses as the product has been purchased
            for ( $i = 0; $i < $product_quantities[ $licensed_product->ID ]; $i++ ) {

                $license = new Pronamic_WP_ExtensionsPlugin_License();

                $license->post_title  = Pronamic_WP_ExtensionsPlugin_License::generate_license_key();
                $license->post_status = 'publish';
                $license->post_type   = self::POST_TYPE;
                $license->post_parent = $extension_id;
                $license->post_author = get_current_user_id();

                $license_saved = $license->save();

                if ( $license_saved ) {

                    Pronamic_WP_ExtensionsPlugin_License::set_start_date( $license->ID, date( 'Y-m-d h:i:s' ) );
                    Pronamic_WP_ExtensionsPlugin_License::set_end_date( $license->ID, date( 'Y-m-d h:i:s', strtotime( '+ 1 year' ) ) );

                    $this->products_with_generated_licenses[ $licensed_product->ID ]['licenses'][] = $license;

                    $license_ids[] = $license->ID;
                }
            }
        }

        // Add the license IDs to the order
        update_post_meta( $order_id, self::WOOCOMMERCE_ORDER_LICENSE_KEYS_META_KEY, $license_ids );
    }

    /**
     * Add a table with the generated license keys to the WooCommerce order email.
     */
    public function woocommerce_email_order_meta_add_license_keys() {

        $this->plugin->display( 'public/emails/license-keys.php', array( 'products_with_generated_licenses' => $this->products_with_generated_licenses ) );
    }

    /**
     * Add a table with the order's generated license keys to the WooCommerce 'thank you' page.
     *
     * @param WC_order $order
     */
    public function woocommerce_order_details_after_order_table_add_license_keys( $order ) {

        $order_license_ids = get_post_meta( $order->id, self::WOOCOMMERCE_ORDER_LICENSE_KEYS_META_KEY, true );

        if ( ! is_array( $order_license_ids ) || count( $order_license_ids ) <= 0 ) {
            $order_license_ids = array( -1 );
        }

        $license_query = new WP_Query( array(
            'post_type'      => Pronamic_WP_ExtensionsPlugin_LicensePostType::POST_TYPE,
            'post__in'       => $order_license_ids,
            'posts_per_page' => -1,
        ) );

        $this->plugin->display( 'public/license-keys.php', array( 'license_query' => $license_query ) );
    }

    //////////////////////////////////////////////////

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
            return Pronamic_WP_ExtensionsPlugin_License::generate_license_key();
        }

        return $title;
    }

    //////////////////////////////////////////////////

    /**
     * Adds a custom table head to this post type's overview page.
     *
     * @param array $columns
     *
     * @return array $columns
     */
    public function manage_pronamic_license_posts_columns( $columns ) {

        $columns['extension'] = __( 'Extensions', 'pronamic_wp_extensions' );

        return $columns;
    }

    /**
     * Fills the custom columns with custom data.
     *
     * @param string $column_name
     * @param string $post_id
     */
    public function manage_pronamic_license_posts_custom_column( $column_name, $post_id ) {

        if ( $column_name === 'extension' ) {

            $parent_post_id = wp_get_post_parent_id( $post_id );

            echo '<a href="' . get_edit_post_link( $parent_post_id ) . '">' . get_the_title( $parent_post_id ) . '</a>';
        }
    }

    //////////////////////////////////////////////////

    /**
     * Return an instance of this class.
     *
     * @since 1.0.0
     *
     * @param Pronamic_WP_ExtensionsPlugin_Plugin $plugin
     *
     * @return Pronamic_WP_ExtensionsPlugin_LicensePostType A single instance of this class.
     */
    public static function get_instance( Pronamic_WP_ExtensionsPlugin_Plugin $plugin ) {
        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self( $plugin );
        }

        return self::$instance;
    }
}