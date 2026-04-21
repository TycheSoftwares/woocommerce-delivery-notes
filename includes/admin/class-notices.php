<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Admin Notices
 *
 * @author      Tyche Softwares
 * @package     WCDN/Notices
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN;

defined( 'ABSPATH' ) || exit;

/**
 * WCDN Notice Class.
 *
 * @since 7.0
 */
class Notices {

	/**
	 * Notices.
	 *
	 * @var array $notices
	 * @access private
	 */
	private static $notices = array();

	/**
	 * Hooks.
	 *
	 * @since 7.0
	 */
	public static function init() {
		self::$notices = get_option( WCDN_SLUG . '_notices', array() );

		add_action( WCDN_SLUG . '_installed', array( __CLASS__, 'reset_notices' ) );
		add_action( 'admin_init', array( __CLASS__, 'hide_notice' ), 20 );
		add_action( 'admin_notices', array( __CLASS__, 'show_notices' ) );
	}

	/**
	 * Store notices.
	 */
	public static function save_notices() {
		update_option( WCDN_SLUG . '_notices', self::$notices );
	}

	/**
	 * Removes a single notice.
	 *
	 * @param string $name Name of Notice.
	 * @param string $is_custom Whether the notice is a custom notice.
	 */
	public static function remove_notice( $name, $is_custom = false ) {

		if ( $is_custom ) {
			delete_option( WCDN_SLUG . '_notice_' . $name );
			return;
		}

		$notices = is_array( self::$notices ) ? self::$notices : array();

		self::$notices = array_diff( $notices, array( $name ) );
		self::save_notices();
	}

	/**
	 * Reset notices.
	 */
	public static function reset_notices() {
		self::$notices = array();
		self::save_notices();
	}

	/**
	 * Adds a notice.
	 *
	 * @param string $name Notice name.
	 * @param string $content Notice.
	 * @param string $is_custom Whether the notice is a custom notice.
	 * @param string $type Type of Notice - success, error, warning.
	 */
	public static function add_notice( $name, $content, $is_custom = false, $type = 'success' ) {

		if ( $is_custom ) {
			update_option( WCDN_SLUG . '_notice_' . $name, $content );
			return;
		}

		$notices          = is_array( self::$notices ) ? self::$notices : array();
		$notices[ $name ] = array(
			'type'    => $type,
			'content' => $content,
		);
		self::$notices    = $notices;

		self::save_notices();
	}

	/**
	 * Hide a notice.
	 */
	public static function hide_notice() {
		if ( isset( $_GET['wcdn-hide-notice'] ) && isset( $_GET['_wcdn_notice_nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified on next line
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wcdn_notice_nonce'] ) ), 'woocommerce_wcdn_hide_notice_nonce' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- this IS the nonce verification
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce-delivery-notes' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( "You don't have the permission to hide the selected notice.", 'woocommerce-delivery-notes' ) );
			}

			self::hide_notice( sanitize_text_field( wp_unslash( $_GET['wcdn-hide-notice'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce already verified above
		}
	}

	/**
	 * Display the notices.
	 */
	public static function show_notices() {

		global $pagenow;

		self::$notices = is_array( self::$notices ) ? self::$notices : array();

		delete_transient( WCDN_SLUG . '_show_welcome_installation_notice' );

		if ( empty( self::$notices ) ) {
			return;
		}

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$show_on_screens = array(
			'dashboard',
			'toplevel_page_wcdn_page',
			'toplevel_page_woocommerce',
			'woocommerce_page_wc-admin',
			'edit-shop_order',
		);

		// Notices should only show on WooCommerce screens, the main dashboard, and on the plugins screen.
		if ( ! in_array( $screen_id, wc_get_screen_ids(), true ) && ! in_array( $screen_id, $show_on_screens, true ) ) {
			return;
		}

		if ( count( self::$notices ) > 0 ) {

			foreach ( self::$notices as $key => $notice ) {

				if ( isset( $notice['type'] ) && isset( $notice['content'] ) ) {

					$type    = $notice['type'];
					$content = $notice['content'];

					// Don't display empty content or empty array data.
					if ( '' === $content || is_array( $content ) ) {
						return;
					}

					/* translators: 1. %1$s notice type, 2. %2$s notice content */
					printf( '<div class="notice notice-%1$s"><p>%2$s</p></div>', esc_attr( $type ), wp_kses_post( $content ) );

					// Display Notices 5 times before deleting.
					$remove_notice = false;

					if ( isset( $notice['counter'] ) ) {
						$counter = (int) $notice['counter'];

						if ( $counter >= 3 ) {
							$remove_notice = true;
						} else {
							self::$notices[ $key ]['counter'] = ++$counter;
						}
					} else {
						self::$notices[ $key ]['counter'] = 1;
					}

					if ( $remove_notice ) {
						unset( self::$notices[ $key ] );
					}
				}
			}

			self::save_notices();
		}
	}
}

Notices::init();
