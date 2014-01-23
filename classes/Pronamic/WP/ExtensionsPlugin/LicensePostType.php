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
     * Array of License objects that have been generated this session. This array is used for mailing the license keys
     * to the user on payment.
     *
     * @var array Array of Pronamic_WP_ExtensionsPlugin_License objects
     */
    private $generated_licenses;

    /**
     * Array of License objects that have been extended this session. This array is used for mailing the extended
     * license keys to the user on payment.
     *
     * @var array Array of Pronamic_WP_ExtensionsPlugin_License objects
     */
    private $extended_licenses;

    //////////////////////////////////////////////////

    /**
     * License post type.
     *
     * @const string
     */
    const POST_TYPE = 'pronamic_license';

    //////////////////////////////////////////////////

    /**
     * Meta key that stores whether or not an order's licensed products have been managed.
     *
     * @const string
     */
    const WOOCOMMERCE_ORDER_LICENSED_PRODUCTS_MANAGED_META_KEY = '_pronamic_wp_extensions_licensed_products_managed';

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

        add_action( 'add_meta_boxes', array( $this, 'woocommerce_order_add_license_key_meta_box' ) );

        add_action( 'edit_user_profile', array( $this, 'add_license_keys_to_user_profile' ) );
        add_action( 'show_user_profile', array( $this, 'add_license_keys_to_user_profile' ) );

        add_action( 'init', array( $this, 'woocommerce_maybe_add_license_to_cart_to_be_extended' ) );

        add_action( 'woocommerce_add_order_item_meta', array( $this, 'woocommerce_add_order_item_meta' ), 10, 2 );

        add_action( 'woocommerce_order_status_pending_to_processing', array( $this, 'woocommerce_order_status_pending_to_processing_manage_licensed_products' ) );
        add_action( 'woocommerce_order_status_pending_to_complete'  , array( $this, 'woocommerce_order_status_pending_to_processing_manage_licensed_products' ) );

        add_action( 'woocommerce_email_order_meta', array( $this, 'woocommerce_email_order_meta_add_license_keys' ) );

        add_action( 'woocommerce_order_details_after_order_table', array( $this, 'woocommerce_order_details_after_order_table_add_license_keys' ) );

        add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'manage_pronamic_license_posts_custom_column' ), 10, 2 );

        // Filters
        add_filter( 'default_title', array( $this, 'maybe_generate_license_key' ) );

        add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'manage_pronamic_license_posts_columns' ) );

        add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'woocommerce_get_cart_item_from_session_add_license_id' ), 10, 2 );

        add_filter( 'woocommerce_in_cart_product_title'    , array( $this, 'woocommerce_in_cart_product_title' )    , 10, 2 );
        add_filter( 'woocommerce_order_table_product_title', array( $this, 'woocommerce_order_table_product_title' ), 10, 2 );
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
            'supports'           => array( 'title', 'author' ),
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

        $this->plugin->display( 'admin/meta-box-order-license-keys.php', array( 'licenses' => self::get_woocommerce_order_licenses( $post->ID ) ) );
    }

    /**
     * Returns a WooCommerce order's generated and extended licenses. The returned is built up as follows:
     *
     * { 'generated': [ WP_Post, ... ], 'extended': [ WP_Post, ... ] }
     *
     * @param int $order_id
     *
     * @return array $licenses
     */
    public static function get_woocommerce_order_licenses( $order_id ) {

        $order_license_ids = get_post_meta( $order_id, self::WOOCOMMERCE_ORDER_LICENSE_KEYS_META_KEY, true );

        if ( ! is_array( $order_license_ids ) ) {

            $order_license_ids = array( 'generated' => array( -1 ), 'extended' => array( -1 ) );
        }

        if ( ! isset( $order_license_ids['generated'] ) ||
            ! is_array( $order_license_ids['generated'] ) ||
            count( $order_license_ids['generated'] ) <= 0) {

            $order_license_ids['generated'] = array( -1 );
        }

        if ( ! isset( $order_license_ids['extended'] ) ||
            ! is_array( $order_license_ids['extended'] ) ||
            count( $order_license_ids['extended'] ) <= 0) {

            $order_license_ids['extended'] = array( -1 );
        }

        $generated_license_query = new WP_Query( array(
            'post_type'      => Pronamic_WP_ExtensionsPlugin_LicensePostType::POST_TYPE,
            'post__in'       => $order_license_ids['generated'],
            'orderby'        => 'parent',
            'posts_per_page' => -1,
        ) );

        $extended_license_query = new WP_Query( array(
            'post_type'      => Pronamic_WP_ExtensionsPlugin_LicensePostType::POST_TYPE,
            'post__in'       => $order_license_ids['extended'],
            'orderby'        => 'parent',
            'posts_per_page' => -1,
        ) );

        return array( 'generated' => $generated_license_query->get_posts(), 'extended' => $extended_license_query->get_posts() );
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
    // WooCommerce cart functions
    //////////////////////////////////////////////////

    /**
     * Adds a licensed product to the WooCommerce cart when requested to.
     */
    public function woocommerce_maybe_add_license_to_cart_to_be_extended() {

        global $woocommerce;

        if ( is_admin() ||
             ! isset( $woocommerce ) ||
             ! filter_input( INPUT_GET, 'pronamic_extensions_add_product_to_cart_to_be_extended', FILTER_VALIDATE_BOOLEAN ) ) {
            return;
        }

        $redirect_url = remove_query_arg( 'pronamic_extensions_add_product_to_cart_to_be_extended', wp_get_referer() );

        $product_id = filter_input( INPUT_GET, 'pronamic_extensions_woocommerce_product_id', FILTER_VALIDATE_INT );
        $license_id = filter_input( INPUT_GET, 'pronamic_extensions_license_id'            , FILTER_VALIDATE_INT );

        if ( ! $product_id || ! $license_id ) {
            wp_redirect( add_query_arg( 'license_added_to_cart', 0, $redirect_url ) );

            die();
        }

        $success = $woocommerce->cart->add_to_cart( $product_id, 1, '', '', array( 'license_id' => $license_id ) );

        if ( $success ) {

            $cart = $woocommerce->cart->get_cart();

            $last_item = end( $cart );
            $last_key  = key( $cart );

            $last_item['license_id'] = $license_id;

            $woocommerce->cart->cart_contents[ $last_key ] = $last_item;
        }

        wp_redirect(
            add_query_arg(
                'license_added_to_cart',
                $success ? 1 : 0,
                $redirect_url
            )
        );

        die();
    }

    /**
     * Hook into where WooCommerce gets its products from the session and add a license ID where necessary.
     *
     * @param array $item
     * @param array $values
     *
     * @return array mixed
     */
    public function woocommerce_get_cart_item_from_session_add_license_id( $item, $values ) {

        if ( isset( $values['license_id'] ) && is_numeric( $values['license_id'] ) ) {

            $item['license_id'] = $values['license_id'];
        }

        return $item;
    }

    /**
     * Hook into where WooCommerce outputs the cart items' titles and add the license key where necessary.
     *
     * @param string $title
     * @param array  $values
     *
     * @return string $title
     */
    public function woocommerce_in_cart_product_title( $title, $values ) {

        if ( isset( $values['license_id'] ) && is_numeric( $values['license_id'] ) ) {

            $license = new Pronamic_WP_ExtensionsPlugin_License( $values['license_id'] );

            if ( strlen( $license->post_title ) > 0 ) {

                $title .= ' &ndash; ' . __( 'Extend License', 'pronamic_wp_extensions' ) . ': ' . $license->post_title;
            }
        }

        return $title;
    }

    /**
     * Hook into where WooCommerce converts the cart items into order items and add add a license ID where necessary.
     *
     * @param int   $item_id
     * @param array $values
     */
    public function woocommerce_add_order_item_meta( $item_id, $values ) {

        if ( isset( $values['license_id'] ) && is_numeric( $values['license_id'] ) ) {

            woocommerce_add_order_item_meta( $item_id, '_license_id', $values['license_id'] );
        }
    }

    //////////////////////////////////////////////////

    /**
     * Hook into where WooCommerce outputs the information about the received order and add the license key where necessary.
     *
     * @param string $title
     * @param array  $item
     *
     * @return string $title
     */
    public function woocommerce_order_table_product_title( $title, $item ) {

        if ( isset( $item['license_id'] ) && is_numeric( $item['license_id'] ) ) {

            $license = new Pronamic_WP_ExtensionsPlugin_License( $item['license_id'] );

            if ( isset( $license->post_title ) ) {

                $title  = '<a href="' . get_permalink( $item['product_id'] ) . '">' . $item['name'] . '</a> &ndash; ';
                $title .= __( 'Extend License', 'pronamic_wp_extensions' ) . ': <a href="' . get_edit_user_link() . '#license-keys">' . $license->post_title . '</a>';
            }
        }

        return $title;
    }

    //////////////////////////////////////////////////
    // WooCommerce manage sale of licensed products
    //////////////////////////////////////////////////

    /**
     * Called when a WooCommerce order gets its status updated. Checks whether a new license key is purchased, or an
     * existing license is being extended.
     *
     * @param int $order_id
     */
    public function woocommerce_order_status_pending_to_processing_manage_licensed_products( $order_id ) {

        $licensed_products_managed = get_post_meta( $order_id, self::WOOCOMMERCE_ORDER_LICENSED_PRODUCTS_MANAGED_META_KEY, true );

        if ( $licensed_products_managed ) {
            return;
        }

        if ( ! class_exists( 'WC_Order' ) ) {
            return;
        }

        $order = new WC_Order( $order_id );

        $products = $order->get_items();

        // An empty array will make WP_Query drop the 'post__in' variable, -1 will make sure no products are retrieved when there are no product IDs
        $product_ids_for_generating_licenses  = array( -1 );
        $license_ids_for_extending_licenses   = array( -1 );
        $product_quantity                     = array();

        // Differentiate between products for which licenses need to be generated and products that were used for buying a license extension
        foreach ( $products as $product ) {

            if ( isset( $product['license_id'] ) && is_numeric( $product['license_id'] ) && $product['license_id'] > 0 ) {

                $license_ids_for_extending_licenses[] = $product['license_id'];

                $product_quantity[ $product['license_id'] ] = $product['qty'];

            } else {

                $product_ids_for_generating_licenses[] = $product['product_id'];

                $product_quantity[ $product['product_id'] ] = $product['qty'];
            }

        }

        // Get all products that have the license key term
        $licensed_products_query = new WP_Query( array(
            'post_type'      => 'product',
            'post__in'       => $product_ids_for_generating_licenses,
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_licensing',
                    'field'    => 'slug',
                    'terms'    => 'license-key',
                )
            ),
        ) );

        // Get all licenses that need to be extended
        $licenses_query = new WP_Query( array(
            'post_type'      => self::POST_TYPE,
            'post__in'       => $license_ids_for_extending_licenses,
            'posts_per_page' => -1,
        ) );

        $this->generated_licenses = array();
        $this->extended_licenses  = array();

        $license_ids = array( 'generated' => array(), 'extended' => array() );

        // Generate licenses
        while ( $licensed_products_query->have_posts() ) {

            $licensed_product = $licensed_products_query->next_post();

            $generated_license_ids = $this->generate_license( $licensed_product, $product_quantity[ $licensed_product->ID ] );

            $license_ids['generated'] = array_merge( $license_ids['generated'], $generated_license_ids );
        }

        // Extend licenses
        while ( $licenses_query->have_posts() ) {

            $license = $licenses_query->next_post();

            $license_ids['extended'][] = $this->extend_license( $license, $product_quantity[ $license->ID ] );
        }

        // Save the generated and extended license IDs to the order for later reference
        update_post_meta( $order_id, self::WOOCOMMERCE_ORDER_LICENSE_KEYS_META_KEY, $license_ids );

        // Mark order as managed
        update_post_meta( $order_id, self::WOOCOMMERCE_ORDER_LICENSED_PRODUCTS_MANAGED_META_KEY, true );
    }

    /**
     * Generates one or more licenses from the passed licensed WooCommerce product and the passed quantity.
     *
     * Adds every license it generates to the $this->generated_licenses array.
     *
     * @param WP_Post $licensed_product
     * @param int     $quantity
     *
     * @return array $license_ids
     */
    public function generate_license( WP_Post $licensed_product, $quantity ) {

        $license_ids = array();

        if ( ! is_numeric( $quantity ) ) {
            return $license_ids;
        }

        $extension_id = get_post_meta( $licensed_product->ID, 'extension_id', true );

        // Generate as many licenses as the product has been purchased
        for ( $i = 0; $i < $quantity; $i++ ) {

            $license = new Pronamic_WP_ExtensionsPlugin_License();

            $license->post_title  = Pronamic_WP_ExtensionsPlugin_License::generate_license_key();
            $license->post_status = 'publish';
            $license->post_type   = self::POST_TYPE;
            $license->post_parent = $extension_id;
            $license->post_author = get_current_user_id();

            $license_saved = $license->save();

            if ( $license_saved ) {

                Pronamic_WP_ExtensionsPlugin_License::set_product_id( $license->ID, $licensed_product->ID );
                Pronamic_WP_ExtensionsPlugin_License::set_start_date( $license->ID, date( 'Y-m-d h:i:s' ) );
                Pronamic_WP_ExtensionsPlugin_License::set_end_date( $license->ID, date( 'Y-m-d h:i:s', strtotime( '+ 1 year' ) ) );

                $this->generated_licenses[] = $license;

                $license_ids[] = $license->ID;
            }
        }

        return $license_ids;
    }

    /**
     * Extends a license with the passed number of years.
     *
     * Adds every license it extends to the $this->extended_licenses array.
     *
     * @param WP_Post $license
     * @param int     $years
     *
     * @return int $license_id
     */
    public function extend_license( WP_Post $license, $years ) {

        if ( ! is_numeric( $years ) && $years > 0 ) {
            return $license->ID;
        }

        $end_date = Pronamic_WP_ExtensionsPlugin_License::get_end_date( $license->ID );

        // When the end date is in the past (expired), add the purchased license time to the current date
        if ( strtotime( $end_date ) < time() ) {

            $new_end_date = date( 'Y-m-d h:i:s', strtotime( '+ ' . $years . ' year' ) );

        // Otherwise add the purchased license time to the end date
        } else {

            $new_end_date = date( 'Y-m-d h:i:s', strtotime( $end_date . ' + ' . $years . ' year' ) );

        }

        Pronamic_WP_ExtensionsPlugin_License::set_end_date( $license->ID, $new_end_date );

        $this->extended_licenses[] = $license;

        return $license->ID;
    }

    //////////////////////////////////////////////////
    // WooCommerce add license key information
    //////////////////////////////////////////////////

    /**
     * Add a table with the generated license keys to the WooCommerce order email.
     *
     * @param WC_Order $order
     */
    public function woocommerce_email_order_meta_add_license_keys( $order ) {

        $this->plugin->display( 'public/emails/license-keys.php', array( 'licenses' => self::get_woocommerce_order_licenses( $order->id ) ) );
    }

    /**
     * Add a table with the order's generated license keys to the WooCommerce 'thank you' page.
     *
     * @param WC_order $order
     */
    public function woocommerce_order_details_after_order_table_add_license_keys( $order ) {

        $this->plugin->display( 'public/license-keys.php', array( 'licenses' => self::get_woocommerce_order_licenses( $order->id ) ) );
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
     * Add custom table heads to this post type's overview page.
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

        switch ( $column_name ) {

            case 'extension':

                $parent_post_id = wp_get_post_parent_id( $post_id );

                if ( $parent_post_id > 0 ) {
                    echo '<a href="' . get_edit_post_link( $parent_post_id ) . '">' . get_the_title( $parent_post_id ) . '</a>';
                } else {
                    _e( 'Warning: No extension has been assigned to this license yet', 'pronamic_wp_extensions' );
                }

                break;
        }
    }

    //////////////////////////////////////////////////
    // Singleton
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