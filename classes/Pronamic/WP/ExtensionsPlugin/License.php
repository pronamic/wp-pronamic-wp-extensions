<?php
/**
 * @author Stefan Boonstra
 */
class Pronamic_WP_ExtensionsPlugin_License {

	/**
	 * All fields possible for saving a post.
	 */
	public $ID;
	public $post_content;
	public $post_name;
	public $post_title;
	public $post_status;
	public $post_type;
	public $post_author;
	public $ping_status;
	public $post_parent;
	public $menu_order;
	public $to_ping;
	public $pinged;
	public $post_password;
	public $guid;
	public $post_content_filtered;
	public $post_excerpt;
	public $post_date;
	public $post_date_gmt;
	public $comment_status;
	public $post_category;
	public $tags_input;
	public $tax_input;
	public $page_template;

	//////////////////////////////////////////////////

	/**
	 * Active sites meta key.
	 *
	 * @const string
	 */
	const ACTIVE_SITES_META_KEY = '_pronamic_extensions_license_active_sites';

	/**
	 * Start date meta key.
	 *
	 * @const string
	 */
	const START_DATE_META_KEY = '_pronamic_extensions_license_start_date';

	/**
	 * End date meta key.
	 *
	 * @const string
	 */
	const END_DATE_META_KEY = '_pronamic_extensions_license_end_date';

	/**
	 * License IDs user meta key.
	 *
	 * @const string
	 */
	const WOOCOMMERCE_PRODUCT_ID_META_KEY = '_pronamic_extensions_license_woocommerce_product_id';

	/**
	 * Log meta key.
	 *
	 * @const string
	 */
	const LOG_META_KEY = '_pronamic_extensions_license_log';

	//////////////////////////////////////////////////

	/**
	 * When passed an ID, a matching database record will be retrieved with which to fill the returned object.
	 *
	 * @param int $ID (optional, defaults to null)
	 */
	public function __construct( $ID = null ) {

		if ( is_numeric( $ID ) ) {

			$license = get_post( $ID );

			if ( $license instanceof WP_Post ) {

				$this->ID                    = $license->ID;
				$this->post_content          = $license->post_content;
				$this->post_name             = $license->post_name;
				$this->post_title            = $license->post_title;
				$this->post_status           = $license->post_status;
				$this->post_type             = $license->post_type;
				$this->post_author           = $license->post_author;
				$this->ping_status           = $license->ping_status;
				$this->post_parent           = $license->post_parent;
				$this->menu_order            = $license->menu_order;
				$this->to_ping               = $license->to_ping;
				$this->pinged                = $license->pinged;
				$this->post_password         = $license->post_password;
				$this->guid                  = $license->guid;
				$this->post_content_filtered = $license->post_content_filtered;
				$this->post_excerpt          = $license->post_excerpt;
				$this->post_date             = $license->post_date;
				$this->post_date_gmt         = $license->post_date_gmt;
				$this->comment_status        = $license->comment_status;
			}
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Saves the current state of the object to the database.
	 *
	 * @return bool $success
	 */
	public function save() {

		$is_license_new = false;

		if ( ! isset( $this->ID ) || ! is_numeric( $this->ID ) || $this->ID <= 0 ) {

			$is_license_new = true;
		}

		$this->ID = wp_insert_post( array(
			'ID'                    => $this->ID,
			'post_content'          => $this->post_content,
			'post_name'             => $this->post_name,
			'post_title'            => $this->post_title,
			'post_status'           => $this->post_status,
			'post_type'             => $this->post_type,
			'post_author'           => $this->post_author,
			'ping_status'           => $this->ping_status,
			'post_parent'           => $this->post_parent,
			'menu_order'            => $this->menu_order,
			'to_ping'               => $this->to_ping,
			'pinged'                => $this->pinged,
			'post_password'         => $this->post_password,
			'guid'                  => $this->guid,
			'post_content_filtered' => $this->post_content_filtered,
			'post_excerpt'          => $this->post_excerpt,
			'post_date'             => $this->post_date,
			'post_date_gmt'         => $this->post_date_gmt,
			'comment_status'        => $this->comment_status,
			'post_category'         => $this->post_category,
			'tags_input'            => $this->tags_input,
			'tax_input'             => $this->tax_input,
			'page_template'         => $this->page_template,
		) );

		if ( ! is_wp_error( $this->ID ) ) {

			if ( $is_license_new ) {

				do_action( 'pronamic_wp_extensions_license_created', $this->ID );

			} else {

				do_action( 'pronamic_wp_extensions_license_updated', $this->ID );

			}

			return true;
		}

		return false;
	}

	//////////////////////////////////////////////////

	/**
	 * Get an array of sites that are currently using this license key.
	 *
	 * @param int $license_id
	 *
	 * @return array $active_sites
	 */
	public static function get_active_sites( $license_id ) {

		$active_sites = get_post_meta( $license_id, self::ACTIVE_SITES_META_KEY, true );

		if ( is_array( $active_sites ) ) {
			return $active_sites;
		}

		return array();
	}

	/**
	 * Add a site to the list of active sites.
	 *
	 * @param int    $license_id
	 * @param string $site
	 * @param string $activation_date (optional, defaults to the current date)
	 * @param mixed  $active_sites    (optional, defaults to getting the currently active sites from the database)
	 *
	 * @return bool $success
	 */
	public static function add_active_site( $license_id, $site, $activation_date = null, $active_sites = null ) {

		if ( ! is_array( $active_sites ) ) {
			$active_sites = self::get_active_sites( $license_id );
		}

		if ( ! isset( $activation_date ) || strlen( $activation_date ) <= 0 ) {
			$activation_date = date( 'Y-m-d h:i:s' );
		}

		$active_sites[ $site ] = array( 'activation_date' => $activation_date );

		return update_post_meta( $license_id, self::ACTIVE_SITES_META_KEY, $active_sites );
	}

	/**
	 * Remove a site to the list of active sites.
	 *
	 * @param int    $license_id
	 * @param string $site
	 * @param mixed  $active_sites (optional, defaults to getting the currently active sites from the database)
	 *
	 * @return bool $success
	 */
	public static function remove_active_site( $license_id, $site, $active_sites = null ) {

		if ( ! is_array( $active_sites ) ) {
			$active_sites = self::get_active_sites( $license_id );
		}

		unset( $active_sites[ $site ] );

		return update_post_meta( $license_id, self::ACTIVE_SITES_META_KEY, $active_sites );
	}

	//////////////////////////////////////////////////

	/**
	 * Get the start date of the license.
	 *
	 * @param int $license_id
	 *
	 * @return string $start_date
	 */
	public static function get_start_date( $license_id ) {

		$start_date = get_post_meta( $license_id, self::START_DATE_META_KEY, true );

		if ( strlen( $start_date ) > 0 ) {
			return $start_date;
		}

		return date( 'Y-m-d h:i:s' );
	}

	/**
	 * Set the start date of the license.
	 *
	 * @param int    $license_id
	 * @param string $start_date
	 *
	 * @return bool $success
	 */
	public static function set_start_date( $license_id, $start_date ) {

		return update_post_meta( $license_id, self::START_DATE_META_KEY, $start_date );
	}

	//////////////////////////////////////////////////

	/**
	 * Get the end date of the license.
	 *
	 * @param int $license_id
	 *
	 * @return string $end_date
	 */
	public static function get_end_date( $license_id ) {

		$end_date = get_post_meta( $license_id, self::END_DATE_META_KEY, true );

		if ( strlen( $end_date ) > 0 ) {
			return $end_date;
		}

		return date( 'Y-m-d h:i:s' );
	}

	/**
	 * Set the end date of the license.
	 *
	 * @param int    $license_id
	 * @param string $end_date
	 *
	 * @return bool $success
	 */
	public static function set_end_date( $license_id, $end_date ) {

		return update_post_meta( $license_id, self::END_DATE_META_KEY, $end_date );
	}

	//////////////////////////////////////////////////

	/**
	 * Get the WooCommerce product ID of the license.
	 *
	 * @param int $license_id
	 *
	 * @return int $product_id
	 */
	public static function get_product_id( $license_id ) {

		$product_id = get_post_meta( $license_id, self::WOOCOMMERCE_PRODUCT_ID_META_KEY, true );

		if ( ! is_numeric( $product_id ) ) {

			return 0;
		}

		return $product_id;
	}

	/**
	 * Sets the WooCommerce product ID of the license.
	 *
	 * @param int $license_id
	 * @param int $product_id
	 *
	 * @return bool $success
	 */
	public static function set_product_id( $license_id, $product_id ) {

		if ( ! is_numeric( $product_id ) ) {

			return false;
		}

		return update_post_meta( $license_id, self::WOOCOMMERCE_PRODUCT_ID_META_KEY, $product_id );
	}

	//////////////////////////////////////////////////

	/**
	 * Gets a license's log.
	 *
	 * @param int $license_id
	 *
	 * @return array $log
	 */
	public static function get_log( $license_id ) {

		$log = get_post_meta( $license_id, self::LOG_META_KEY, true );

		if ( ! is_array( $log ) ) {

			$log = array();
		}

		return $log;
	}

	/**
	 * Add a log entry.
	 *
	 * @param int    $license_id
	 * @param string $message
	 *
	 * @return bool $success
	 */
	public static function log( $license_id, $message ) {

		if ( is_string( $message ) && strlen( $message ) > 0 ) {

			$log = self::get_log( $license_id );

			array_unshift( $log, array( 'message' => $message, 'timestamp' => time() ) );

			return update_post_meta( $license_id, self::LOG_META_KEY, $log );
		}

		return false;
	}

	//////////////////////////////////////////////////

	/**
	 * Get the date the last expiration reminder was sent.
	 *
	 * @param int $license_id
	 *
	 * @return string $date_last_reminder
	 */
	public static function get_date_last_expiration_reminder( $license_id ) {

		$date_last_reminder = get_post_meta( $license_id, Pronamic_WP_ExtensionsPlugin_LicenseReminder::DATE_LAST_LICENSE_EXPIRATION_REMINDER_META_KEY, true );

		if ( ! $date_last_reminder ) {
			return '';
		}

		return $date_last_reminder;
	}

	/**
	 * Set the date the last expiration reminder was sent.
	 *
	 * @param int    $license_id
	 * @param string $date_last_expiration_reminder
	 *
	 * @return bool $success
	 */
	public static function set_date_last_expiration_reminder( $license_id, $date_last_expiration_reminder ) {

		return update_post_meta( $license_id, Pronamic_WP_ExtensionsPlugin_LicenseReminder::DATE_LAST_LICENSE_EXPIRATION_REMINDER_META_KEY, $date_last_expiration_reminder );
	}

	//////////////////////////////////////////////////

	/**
	 * Get whether or not the license expired reminder was sent.
	 *
	 * @param int $license_id
	 *
	 * @return bool $license_expired_reminder_sent
	 */
	public static function get_license_expired_reminder_sent( $license_id ) {

		$license_expired_reminder_sent = get_post_meta( $license_id, Pronamic_WP_ExtensionsPlugin_LicenseReminder::LICENSE_EXPIRED_REMINDER_SENT_META_KEY, true );

		if ( $license_expired_reminder_sent ) {
			return true;
		}

		return false;
	}

	/**
	 * Set whether or not the license expired reminder was sent.
	 *
	 * @param int  $license_id
	 * @param bool $license_expired_reminder_sent
	 *
	 * @return bool $success
	 */
	public static function set_license_expired_reminder_sent( $license_id, $license_expired_reminder_sent ) {

		return update_post_meta( $license_id, Pronamic_WP_ExtensionsPlugin_LicenseReminder::LICENSE_EXPIRED_REMINDER_SENT_META_KEY, $license_expired_reminder_sent );
	}

	//////////////////////////////////////////////////

	/**
	 * Generate a v4 UUID.
	 *
	 * @see https://gist.github.com/dahnielson/508447
	 *
	 * @return string $license_key
	 */
	public static function generate_license_key() {

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

	//////////////////////////////////////////////////

	/**
	 * Generates the URL that adds the license extending product to the cart.
	 *
	 * @param int    $license_id
	 * @param string $return_url (optional, defaults to the default return URL)
	 *
	 * @return string $extend_url
	 */
	public static function generate_extend_url( $license_id, $return_url = '' ) {

		return add_query_arg( array(
			'pronamic_extensions_add_product_to_cart_to_be_extended' => true,
			'pronamic_extensions_woocommerce_product_id'             => self::get_product_id( $license_id ),
			'pronamic_extensions_license_id'                         => $license_id,
			'return_url'                                             => urlencode( $return_url ),
		), home_url() );
	}
}