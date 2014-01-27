<?php
/**
 * @author Stefan Boonstra
 */
class Pronamic_WP_ExtensionsPlugin_LicenseReminder {

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @var Pronamic_WP_ExtensionsPlugin_LicenseReminder
	 */
	protected static $instance = null;

	//////////////////////////////////////////////////

	/**
	 * Meta key that stores whether or not the license expired message was sent.
	 *
	 * @const string
	 */
	const LICENSE_EXPIRED_REMINDER_SENT_META_KEY = '_pronamic_wp_extensions_license_expiration_reminder_sent';

	/**
	 * Meta key that stores the most recent date a license expiration reminder mail was sent.
	 *
	 * @const string
	 */
	const DATE_LAST_LICENSE_EXPIRATION_REMINDER_META_KEY = '_pronamic_wp_extensions_date_last_license_expiration_reminder';

	//////////////////////////////////////////////////

	/**
	 * Option key that stores whether or not to notify a license's user when his license has expired.
	 *
	 * @const string
	 */
	const SEND_LICENSE_EXPIRED_REMINDER_OPTION = 'pronamic_wp_extensions_send_license_expired_reminder';

	/**
	 * Option key that stores whether or not to notify a license's user in advance when his license is about to expire.
	 *
	 * @const string
	 */
	const SEND_LICENSE_REMINDERS_IN_ADVANCE_OPTION = 'pronamic_wp_extensions_send_license_reminders_in_advance';

	/**
	 * Option key that stores how much time in advance a license's user should be notified that his license is about to
	 * expire.
	 *
	 * This can be one of the following values:
	 *
	 * ''
	 * '1 day'
	 * '2 days'
	 * '3 days'
	 * '4 days'
	 * '5 days'
	 * '6 days'
	 * '1 week'
	 * '2 weeks'
	 *
	 * @const string
	 */
	const SEND_LICENSE_REMINDERS_TIME_IN_ADVANCE_OPTION = 'pronamic_wp_extensions_send_license_reminders_time_in_advance';

	/**
	 * Option key that stores the subject for the license expiration reminder.
	 *
	 * @const string
	 */
	const LICENSE_REMINDER_SUBJECT_OPTION = 'pronamic_wp_extensions_license_reminder_subject';

	/**
	 * Option key that stores the subject for the license expiration reminder taht's sent in advance.
	 *
	 * @const string
	 */
	const LICENSE_REMINDER_IN_ADVANCE_SUBJECT_OPTION = 'pronamic_wp_extensions_license_reminder_in_advance_subject';

	//////////////////////////////////////////////////

	/**
	 * Extensions plugin.
	 *
	 * @var Pronamic_WP_ExtensionsPlugin_Plugin
	 */
	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param Pronamic_WP_ExtensionsPlugin_Plugin $plugin
	 */
	private function __construct( Pronamic_WP_ExtensionsPlugin_Plugin $plugin ) {

		$this->plugin = $plugin;

		add_action( 'pronamic_wp_extensions_register_settings', array( $this, 'register_settings' ) );

		add_action( 'admin_init', array( $this, 'periodically_check_for_expired_licenses' ) );

		add_action( 'pronamic_wp_extensions_license_created' , array( $this, 'init_license_meta_data' ) );
		add_action( 'pronamic_wp_extensions_license_extended', array( $this, 'init_license_meta_data' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Registers settings specific to the license reminder
	 */
	public function register_settings() {

		add_settings_section(
			'pronamic_wp_extensions_license_reminder', // id
			__( 'License Reminder Emails', 'pronamic_wp_extensions' ), // title
			'__return_false', // callback
			'pronamic_wp_extensions' // page
		);

		add_settings_field(
			self::SEND_LICENSE_EXPIRED_REMINDER_OPTION, // id
			__( 'Send license expired reminder', 'pronamic_wp_extensions' ), // title
			array( $this->plugin->admin, 'input_checkbox' ), // callback
			'pronamic_wp_extensions', // page
			'pronamic_wp_extensions_license_reminder', // section
			array(
				'label_for' => self::SEND_LICENSE_EXPIRED_REMINDER_OPTION,
			) // args
		);

		add_settings_field(
			self::SEND_LICENSE_REMINDERS_IN_ADVANCE_OPTION, // id
			__( 'Send reminders in advance', 'pronamic_wp_extensions' ), // title
			array( $this->plugin->admin, 'input_checkbox' ), // callback
			'pronamic_wp_extensions', // page
			'pronamic_wp_extensions_license_reminder', // section
			array(
				'label_for' => self::SEND_LICENSE_REMINDERS_IN_ADVANCE_OPTION,
			) // args
		);

		add_settings_field(
			self::SEND_LICENSE_REMINDERS_TIME_IN_ADVANCE_OPTION, // id
			__( 'Send reminders in advance when license expires in', 'pronamic_wp_extensions' ), // title
			array( $this->plugin->admin, 'input_select' ), // callback
			'pronamic_wp_extensions', // page
			'pronamic_wp_extensions_license_reminder', // section
			array(
				'label_for' => self::SEND_LICENSE_REMINDERS_TIME_IN_ADVANCE_OPTION,
				'options'   => self::get_send_license_reminders_time_in_advance_options(),
			) // args
		);

		add_settings_field(
			self::LICENSE_REMINDER_SUBJECT_OPTION, // id
			__( 'Reminder subject', 'pronamic_wp_extensions' ), // title
			array( $this->plugin->admin, 'input_text' ), // callback
			'pronamic_wp_extensions', // page
			'pronamic_wp_extensions_license_reminder', // section
			array(
				'label_for' => self::LICENSE_REMINDER_SUBJECT_OPTION,
				'classes'   => array( 'regular-text' ),
			) // args
		);

		add_settings_field(
			self::LICENSE_REMINDER_IN_ADVANCE_SUBJECT_OPTION, // id
			__( 'Reminder in advance subject', 'pronamic_wp_extensions' ), // title
			array( $this->plugin->admin, 'input_text' ), // callback
			'pronamic_wp_extensions', // page
			'pronamic_wp_extensions_license_reminder', // section
			array(
				'label_for' => self::LICENSE_REMINDER_IN_ADVANCE_SUBJECT_OPTION,
				'classes'   => array( 'regular-text' ),
			) // args
		);

		register_setting( 'pronamic_wp_extensions', self::SEND_LICENSE_EXPIRED_REMINDER_OPTION );
		register_setting( 'pronamic_wp_extensions', self::SEND_LICENSE_REMINDERS_IN_ADVANCE_OPTION );
		register_setting( 'pronamic_wp_extensions', self::SEND_LICENSE_REMINDERS_TIME_IN_ADVANCE_OPTION );
		register_setting( 'pronamic_wp_extensions', self::LICENSE_REMINDER_SUBJECT_OPTION );
		register_setting( 'pronamic_wp_extensions', self::LICENSE_REMINDER_IN_ADVANCE_SUBJECT_OPTION );
	}

	//////////////////////////////////////////////////

	/**
	 * Called upon license creation. Makes sure the license's reminder data is correctly set so the reminder data can
	 * be queried upon.
	 *
	 * @param int $license_id
	 */
	public function init_license_meta_data( $license_id ) {

		Pronamic_WP_ExtensionsPlugin_License::set_license_expired_reminder_sent( $license_id, false );
		Pronamic_WP_ExtensionsPlugin_License::set_license_expired_reminder_sent( $license_id, '' );
	}

	//////////////////////////////////////////////////

	/**
	 * Checks on a daily basis whether there are any expiring licenses within the next period of time. Sends an email
	 * to the license's author when the license is about to expire.
	 */
	public function periodically_check_for_expired_licenses() {

		// Should be 40 characters or less
		$transient = 'pronamic_extensions_periodic_license_check';

		if ( get_site_transient( $transient ) !== false ) {
			return;
		}

		if ( ! self::get_send_license_expired_reminder() &&
			 ! self::get_send_license_reminders_in_advance() ) {
			return;
		}

		// Get all licenses that are about to expire within the next two weeks
		$expiring_licenses_query = new WP_Query( array(
			'post_type'      => Pronamic_WP_ExtensionsPlugin_LicensePostType::POST_TYPE,
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => Pronamic_WP_ExtensionsPlugin_License::END_DATE_META_KEY,
					'value'   => date( 'Y-m-d h:i:s', strtotime( '+ ' . self::get_send_license_reminders_time_in_advance() ) ),
					'compare' => '<=',
					'type'    => 'date',
				),
				array(
					'key'     => self::LICENSE_EXPIRED_REMINDER_SENT_META_KEY,
					'value'   => 1,
					'compare' => '!=',
					'type'    => 'numeric'
				),
			),
		) );

		// Loop through licenses that are about to expire
		while ( $expiring_licenses_query->have_posts() ) {

			$expiring_license = $expiring_licenses_query->next_post();

			$user = get_userdata( $expiring_license->post_author );

			if ( ! $user || ! is_email( $user->user_email ) ) {

				continue;
			}

			$expire_timestamp = strtotime( Pronamic_WP_ExtensionsPlugin_License::get_end_date( $expiring_license->ID ) );

			$expire_day = date( 'Y-m-d', $expire_timestamp );
			$today      = date( 'Y-m-d' );

			var_dump( $expire_day, $today );

			// Mail about the license expiring today
			if ( $expire_day === $today ) {

				$template = 'public/emails/license-expiration-today-reminder.php';

				$mail_subject = self::get_license_reminder_subject( $expiring_license->ID );

				$log_success_message = __( 'License expires today reminder sent', 'pronamic_wp_extensions' );
				$log_error_message   = __( 'License expires today reminder could not be sent', 'pronamic_wp_extensions' );

//                Pronamic_WP_ExtensionsPlugin_License::set_license_expired_reminder_sent( $expiring_license->ID, true );

			// Mail about the license expiring within the next period of time
			} else if ( $expire_timestamp > time() && strlen( Pronamic_WP_ExtensionsPlugin_License::get_date_last_expiration_reminder( $expiring_license->ID ) ) <= 0 ) {

				$template = 'public/emails/license-expiration-reminder.php';

				$mail_subject = self::get_license_reminder_in_advance_subject( $expiring_license->ID );

				$log_success_message = __( 'License expiration reminder sent', 'pronamic_wp_extensions' );
				$log_error_message   = __( 'License expiration reminder could not be sent', 'pronamic_wp_extensions' );

//                Pronamic_WP_ExtensionsPlugin_License::set_date_last_expiration_reminder( $expiring_license->ID, date( 'Y-m-d h:i:s' ) );

			} else {

				continue;
			}

			ob_start();

			$this->plugin->display( $template, array( 'license' => $expiring_license, 'user' => $user ), true );

			$mail_body = ob_get_clean();
			$mail_to   = $user->user_email;

			$mail_headers = array(
				'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>',
				'Content-Type: text/html',
			);

			// Send reminder mail
			$success = wp_mail( $mail_to, $mail_subject, $mail_body, $mail_headers );

			if ( $success ) {
				Pronamic_WP_ExtensionsPlugin_License::log( $expiring_license->ID, $log_success_message );
			} else {
				Pronamic_WP_ExtensionsPlugin_License::log( $expiring_license->ID, $log_error_message );
			}
		}

		// Check licenses on a daily basis
		set_site_transient( $transient, true, 60 * 60 * 24 );
	}

	//////////////////////////////////////////////////

	/**
	 * Check if license expired reminders should be sent.
	 *
	 * @return bool $send_license_expired_reminder
	 */
	public static function get_send_license_expired_reminder() {

		if ( get_option( self::SEND_LICENSE_EXPIRED_REMINDER_OPTION ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if license expiration reminders should be sent in advance.
	 *
	 * @return bool $send_license_reminders_in_advance
	 */
	public static function get_send_license_reminders_in_advance() {

		if ( get_option( self::SEND_LICENSE_REMINDERS_IN_ADVANCE_OPTION ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the amount of time a license expiration reminder should be sent in advance.
	 *
	 * @return string $send_license_reminders_time_in_advance
	 */
	public static function get_send_license_reminders_time_in_advance() {

		$send_license_reminders_time_in_advance         = get_option( self::SEND_LICENSE_REMINDERS_TIME_IN_ADVANCE_OPTION );
		$send_license_reminders_time_in_advance_options = self::get_send_license_reminders_time_in_advance_options();

		if ( strlen( $send_license_reminders_time_in_advance ) > 0 &&
			 array_key_exists( $send_license_reminders_time_in_advance, $send_license_reminders_time_in_advance_options ) ) {

			return $send_license_reminders_time_in_advance;
		}

		return end( $send_license_reminders_time_in_advance_options );
	}

	/**
	 * Get the different times of sending an expiration reminder.
	 *
	 * @return array $send_license_reminders_time_in_advance_options
	 */
	public static function get_send_license_reminders_time_in_advance_options() {

		return array(
			''        => '',
			'1 day'   => __( 'One day'   , 'pronamic_wp_extensions' ),
			'2 days'  => __( 'Two days'  , 'pronamic_wp_extensions' ),
			'3 days'  => __( 'Three days', 'pronamic_wp_extensions' ),
			'4 days'  => __( 'Four days' , 'pronamic_wp_extensions' ),
			'5 days'  => __( 'Five days' , 'pronamic_wp_extensions' ),
			'6 days'  => __( 'Six days'  , 'pronamic_wp_extensions' ),
			'1 week'  => __( 'One week'  , 'pronamic_wp_extensions' ),
			'2 weeks' => __( 'Two weeks' , 'pronamic_wp_extensions' ),
		);
	}

	/**
	 * Get the subject of a reminder email
	 *
	 * @param int $license_id
	 *
	 * @return string $subject
	 */
	public static function get_license_reminder_subject( $license_id ) {

		$license_reminder_subject = get_option( self::LICENSE_REMINDER_SUBJECT_OPTION );

		if ( strlen( $license_reminder_subject ) > 0 ) {

			return $license_reminder_subject;
		}

		return sprintf( __( 'Your %s license expires today', 'pronamic_wp_extensions' ), get_the_title( wp_get_post_parent_id( $license_id ) ) );
	}

	/**
	 * Get the subject of a reminder email that's being sent in advance.
	 *
	 * @param int $license_id
	 *
	 * @return string $subject
	 */
	public static function get_license_reminder_in_advance_subject( $license_id ) {

		$license_reminder_in_advance_subject = get_option( self::LICENSE_REMINDER_IN_ADVANCE_SUBJECT_OPTION );

		if ( strlen( $license_reminder_in_advance_subject ) > 0 ) {

			return $license_reminder_in_advance_subject;
		}

		return sprintf( __( 'Your %s license is about to expire', 'pronamic_wp_extensions' ), get_the_title( wp_get_post_parent_id( $license_id ) ) );
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
	 * @return Pronamic_WP_ExtensionsPlugin_LicenseReminder A single instance of this class.
	 */
	public static function get_instance( Pronamic_WP_ExtensionsPlugin_Plugin $plugin ) {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self( $plugin );
		}

		return self::$instance;
	}
}