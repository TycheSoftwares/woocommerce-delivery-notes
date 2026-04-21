<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * REST API for Settings.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Admin/API/Settings
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Api;

use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

/**
 * REST API.
 *
 * @since 7.0
 */
class Settings extends \Tyche\WCDN\Api\Api {

	/**
	 * Option key.
	 */
	const OPTION_KEY = WCDN_SLUG . '_settings';

	/**
	 * Construct
	 *
	 * @since 7.0
	 */
	public function __construct() {
		add_action(
			'rest_api_init',
			array( __CLASS__, 'register_routes' )
		);
	}

	/**
	 * Function for registering the API routes.
	 *
	 * @since 7.0
	 */
	public static function register_routes() {

		register_rest_route(
			'wcdn/v1',
			'/settings',
			array(

				array(
					'methods'             => 'GET',
					'callback'            => array(
						__CLASS__,
						'fetch_settings',
					),
					'permission_callback' => array(
						__CLASS__,
						'permissions',
					),
				),

				array(
					'methods'             => 'POST',
					'callback'            => array(
						__CLASS__,
						'save_settings',
					),
					'permission_callback' => array(
						__CLASS__,
						'permissions',
					),
				),
			)
		);
	}

	/**
	 * Settings Schema.
	 *
	 * Defines field types for sanitization.
	 *
	 * @since 7.0
	 */
	private static function schema() {

		return array(
			'storeName'                    => 'text',
			'storeLogo'                    => 'url',
			'storeAddress'                 => 'textarea',
			'email'                        => 'email',
			'phone'                        => 'text',
			'footerText'                   => 'textarea',
			'complimentaryClose'           => 'textarea',
			'policies'                     => 'textarea',
			'printEndpoint'                => 'slug',
			'defaultDocumentLabel'         => 'text',
			'textDirection'                => 'text',
			'enablePDF'                    => 'bool',
			'showCustomerEmailLink'        => 'bool',
			'customerEmailText'            => 'text',
			'showAdminEmailLink'           => 'bool',
			'adminEmailText'               => 'text',
			'showViewOrderButton'          => 'bool',
			'showPrintButtonMyAccountPage' => 'bool',
			'viewOrderButtonLabel'         => 'text',
			'invoiceButtonLabel'           => 'text',
			'deliveryNoteButtonLabel'      => 'text',
			'receiptButtonLabel'           => 'text',
			'creditNoteButtonLabel'        => 'text',
			'packingSlipButtonLabel'       => 'text',
			'processingTemplate'           => 'text',
			'processingAuto'               => 'bool',
			'processingAttach'             => 'bool',
			'completedTemplate'            => 'text',
			'completedAuto'                => 'bool',
			'completedAttach'              => 'bool',
			'refundedTemplate'             => 'text',
			'refundedAuto'                 => 'bool',
			'refundedAttach'               => 'bool',
			'enablePayNow'                 => 'bool',
			'enablePDFStorage'             => 'bool',
			'invoiceNumberFormat'          => 'text',
			'nextInvoiceNumber'            => 'number',
			'numberDaysPdfExpiration'      => 'number',
			'myAccountPageButtonLabel'     => 'text',
			'resetInvoiceNumberYearly'     => 'bool',
			'startingNumberForEachYear'    => 'number',
		);
	}

	/**
	 * Get Settings
	 *
	 * @since 7.0
	 */
	public static function fetch_settings() {

		$saved = get_option( self::OPTION_KEY, array() );

		$settings = wp_parse_args(
			$saved,
			self::default_settings()
		);

		$next_number = (int) get_option( 'wcdn_invoice_number_counter', 0 ) + 1;

		$settings = array_merge(
			$settings,
			array(
				'meta' => array(
					'maxInvoiceNumber' => $next_number,
				),
			)
		);

		$settings['nextInvoiceNumber'] = $next_number;

		return self::response(
			'success',
			$settings
		);
	}


	/**
	 * Save Settings
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 7.0
	 */
	public static function save_settings( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response(
				'error',
				array(
					'error_description' => __( 'Authentication has failed.', 'woocommerce-delivery-notes' ),
				)
			);
		}

		$params = $request->get_json_params();

		if ( ! $params ) {
			return self::response(
				'error',
				array(
					'error_description' => __( 'No data received.', 'woocommerce-delivery-notes' ),
				)
			);
		}

		if ( isset( $params['reset_plugin_usage_tracking'] ) ) {
			if ( get_option( WCDN_SLUG . '_allow_tracking', false ) ) {
				delete_option( WCDN_SLUG . '_allow_tracking' );

				return self::response(
					'success',
					array(
						'message' => __( 'Tracking data settings reset successfully.', 'woocommerce-delivery-notes' ),
					)
				);
			}

			return self::response(
				'success',
				array(
					'message' => __( 'Tracking data already reset.', 'woocommerce-delivery-notes' ),
				)
			);

		}

		$old_settings = get_option( self::OPTION_KEY, array() );

		$next_invoice_number = isset( $params['nextInvoiceNumber'] ) ? absint( $params['nextInvoiceNumber'] ) : null;
		$year_start_number   = isset( $params['startingNumberForEachYear'] ) ? absint( $params['startingNumberForEachYear'] ) : null;

		$settings = self::sanitize( $params, self::schema(), self::default_settings() );

		unset( $settings['nextInvoiceNumber'] );
		unset( $settings['startingNumberForEachYear'] );

		// $current_max is the last assigned number; the current "next" is $current_max + 1.
		$current_max  = (int) get_option( 'wcdn_invoice_number_counter', 0 );
		$current_next = $current_max + 1;

		if ( null !== $next_invoice_number && $next_invoice_number < $current_next ) {
			return self::response(
				'error',
				array(
					'error_description' => sprintf(
						// translators: %d: minimum allowed invoice number.
						__( 'Next invoice number must be greater than or equal to %d.', 'woocommerce-delivery-notes' ),
						$current_next
					),
				)
			);
		}

		$current_year_max = (int) get_option( 'wcdn_invoice_number_counter_year', 0 );

		if ( null !== $year_start_number && $year_start_number <= 0 ) {
			return self::response(
				'error',
				array(
					'error_description' => __(
						'Year starting number must be greater than 0.',
						'woocommerce-delivery-notes'
					),
				)
			);
		}

		if ( null !== $next_invoice_number ) {
			// Store last-used value so the next assignment increments to $next_invoice_number.
			update_option( 'wcdn_invoice_number_counter', $next_invoice_number - 1 );
		}

		if ( null !== $year_start_number ) {
			$current_year = (int) gmdate( 'Y' );
			$counters     = get_option( 'wcdn_invoice_number_counter_year', array() );

			if ( ! isset( $counters[ $current_year ] ) ) {
				$counters[ $current_year ] = $year_start_number;
				update_option( 'wcdn_invoice_number_counter_year', $counters );
			}
		}

		// Trigger rewrite flush if endpoint changed.
		if (
		! isset( $old_settings['printEndpoint'] ) ||
		$old_settings['printEndpoint'] !== $settings['printEndpoint']
		) {
			update_option( 'wcdn_flush_rewrite_rules', 1 );
		}

		update_option(
			self::OPTION_KEY,
			$settings
		);

		delete_transient( 'wcdn_preview_data' );

		return self::response(
			'success',
			array(
				'message'  =>
				__( 'Settings saved successfully.', 'woocommerce-delivery-notes' ),
				'settings' =>
				self::fetch_settings(),
			)
		);
	}

	/**
	 * Default Settings Data.
	 *
	 * @since 7.0
	 */
	public static function default_settings() {

		return array(
			'storeName'                    => '',
			'storeLogo'                    => '',
			'storeAddress'                 => '',
			'email'                        => get_option( 'admin_email' ),
			'phone'                        => '',
			'footerText'                   => '',
			'complimentaryClose'           => '',
			'policies'                     => '',
			'printEndpoint'                => 'print-order',
			'defaultDocumentLabel'         => __( 'Document', 'woocommerce-delivery-notes' ),
			'textDirection'                => 'ltr',
			'enablePDF'                    => true,
			'showCustomerEmailLink'        => true,
			'customerEmailText'            => __( 'View and print your invoice', 'woocommerce-delivery-notes' ),
			'showAdminEmailLink'           => true,
			'adminEmailText'               => __( 'Print order documents', 'woocommerce-delivery-notes' ),
			'showViewOrderButton'          => true,
			'showPrintButtonMyAccountPage' => true,
			'ordersListLabel'              => __( 'Print', 'woocommerce-delivery-notes' ),
			'viewOrderButtonLabel'         => __( 'Print', 'woocommerce-delivery-notes' ),
			'invoiceButtonLabel'           => '',
			'deliveryNoteButtonLabel'      => '',
			'receiptButtonLabel'           => '',
			'creditNoteButtonLabel'        => '',
			'packingSlipButtonLabel'       => '',
			'processingTemplate'           => __( 'invoice', 'woocommerce-delivery-notes' ),
			'processingAuto'               => false,
			'processingAttach'             => false,
			'completedTemplate'            => 'invoice',
			'completedAuto'                => true,
			'completedAttach'              => true,
			'refundedTemplate'             => 'invoice',
			'refundedAuto'                 => false,
			'refundedAttach'               => false,
			'enablePayNow'                 => true,
			'enablePDFStorage'             => false,
			'invoiceNumberFormat'          => 'INV-{order_number}',
			'numberDaysPdfExpiration'      => 7,
			'myAccountPageButtonLabel'     => __( 'Print', 'woocommerce-delivery-notes' ),
			'nextInvoiceNumber'            => (int) get_option( 'wcdn_invoice_number_counter', 0 ) + 1,
			'resetInvoiceNumberYearly'     => false,
			'startingNumberForEachYear'    => 1,
		);
	}
}
