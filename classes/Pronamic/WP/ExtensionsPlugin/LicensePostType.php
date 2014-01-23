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
    private $products_with_generated_licenses;

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

        add_action( 'add_meta_boxes', array( $this, 'woocommerce_order_add_license_key_meta_box' ) );

        add_action( 'edit_user_profile', array( $this, 'add_license_keys_to_user_profile' ) );
        add_action( 'show_user_profile', array( $this, 'add_license_keys_to_user_profile' ) );

        add_action( 'init', array( $this, 'woocommerce_maybe_add_license_to_cart_to_be_extended' ) );

        add_action( 'woocommerce_add_order_item_meta', array( $this, 'woocommerce_add_order_item_meta' ), 10, 2 );

        add_action( 'woocommerce_order_status_pending_to_processing', array( $this, 'woocommerce_order_status_pending_to_processing_generate_licenses' ) );
        add_action( 'woocommerce_order_status_pending_to_complete', array( $this, 'woocommerce_order_status_pending_to_processing_generate_licenses' ) );

        add_action( 'woocommerce_email_order_meta', array( $this, 'woocommerce_email_order_meta_add_license_keys' ) );

        add_action( 'woocommerce_order_details_after_order_table', array( $this, 'woocommerce_order_details_after_order_table_add_license_keys' ) );

        add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'manage_pronamic_license_posts_custom_column' ), 10, 2 );

        // Filters
        add_filter( 'default_title', array( $this, 'maybe_generate_license_key' ) );

        add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'manage_pronamic_license_posts_columns' ) );

        add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'woocommerce_get_cart_item_from_session_add_license_id' ), 10, 2 );

        add_filter( 'woocommerce_in_cart_product_title', array( $this, 'woocommerce_in_cart_product_title' ), 10, 2 );
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
     * Called when a WooCommerce order gets its status updated.
     *
     * @param int $order_id
     */
    public function woocommerce_order_status_pending_to_processing_generate_licenses( $order_id ) {

        if ( ! class_exists( 'WC_Order' ) ) {
            return;
        }

        // When $order_license_ids consists of an array of license IDs, the licenses have already been generated and the method should exit
        $order_license_ids = get_post_meta( $order_id, self::WOOCOMMERCE_ORDER_LICENSE_KEYS_META_KEY, true );

        if ( is_array( $order_license_ids ) ) {
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

        $this->products_with_generated_licenses = array();

        // Loop through licensed products, generating licenses
        while ( $licensed_products_query->have_posts() ) {

            $licensed_product = $licensed_products_query->next_post();

            $extension_id = get_post_meta( $licensed_product->ID, 'extension_id', true );

            // When the product's quantity is greater than zero, prepare the array of licenses
            if ( $product_quantities[ $licensed_product->ID ] > 0 ) {

                $this->products_with_generated_licenses[ $extension_id ] = array();
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

                    Pronamic_WP_ExtensionsPlugin_License::set_product_id( $license->ID, $licensed_product->ID );
                    Pronamic_WP_ExtensionsPlugin_License::set_start_date( $license->ID, date( 'Y-m-d h:i:s' ) );
                    Pronamic_WP_ExtensionsPlugin_License::set_end_date( $license->ID, date( 'Y-m-d h:i:s', strtotime( '+ 1 year' ) ) );

                    $this->products_with_generated_licenses[ $extension_id ][] = $license;

                    $license_ids[] = $license->ID;
                }
            }
        }

        // Add the license IDs to the order
        update_post_meta( $order_id, self::WOOCOMMERCE_ORDER_LICENSE_KEYS_META_KEY, $license_ids );
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

        // The 'products_with_generated_licenses' variable is set after licenses have been generated. If the mail is being resent, get the license keys first
        if ( ! is_array( $this->products_with_generated_licenses ) ) {

            $order_license_ids = get_post_meta( $order->id, self::WOOCOMMERCE_ORDER_LICENSE_KEYS_META_KEY, true );

            if ( ! is_array( $order_license_ids ) || count( $order_license_ids ) <= 0 ) {
                $order_license_ids = array( -1 );
            }

            $license_query = new WP_Query( array(
                'post_type'      => Pronamic_WP_ExtensionsPlugin_LicensePostType::POST_TYPE,
                'post__in'       => $order_license_ids,
                'posts_per_page' => -1,
            ) );

            $this->products_with_generated_licenses = array();

            while ( $license_query->have_posts() ) {

                $license = $license_query->next_post();

                $this->products_with_generated_licenses[ $license->post_parent ][] = $license;
            }
        }

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