<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * REST API for Dashboard.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Admin/API/Dashboard
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Api;

use WP_REST_Request;
use Tyche\WCDN\Helpers\Settings;
use Tyche\WCDN\Services\Template_Engine;
use Tyche\WCDN\Helpers\Templates;
use Tyche\WCDN\Helpers\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * REST API.
 *
 * @since 7.0
 */
class Dashboard extends \Tyche\WCDN\Api\Api {

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
			'/dashboard',
			array(

				array(
					'methods'             => 'GET',
					'callback'            => array(
						__CLASS__,
						'fetch_data',
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
	 * Get Dashboard Data.
	 *
	 * @since 7.0
	 */
	public static function fetch_data() {

		$settings = get_option(
			WCDN_SLUG . '_settings',
			array()
		);

		$footer_text         = $settings['footerText'] ?? '';
		$complimentary_close = $settings['complimentaryClose'] ?? '';
		$policies            = $settings['policies'] ?? '';
		$store_name          = $settings['storeName'] ?? '';
		$store_logo          = $settings['storeLogo'] ?? '';
		$store_address       = $settings['storeAddress'] ?? '';

		$store_details_configured      = '' !== $footer_text && '' !== $complimentary_close && '' !== $policies && $store_name && '' !== $store_name && '' !== $store_address;
		$at_least_one_template_enabled = false;
		$pdf_generation_enabled        = Settings::get( 'enablePDF' );
		$invoice_numbering_configured  = '' !== Settings::get( 'invoiceNumberFormat' );

		foreach ( Template_Engine::get_template_keys() as $template ) {
			if ( Templates::get( $template, 'enabled' ) ) {
				$at_least_one_template_enabled = true;
			}
		}

		// Latest Order Invoice.

		return self::response(
			'success',
			array(
				'store_details_configured'      => $store_details_configured,
				'at_least_one_template_enabled' => $at_least_one_template_enabled,
				'pdf_generation_enabled'        => $pdf_generation_enabled,
				'invoice_numbering_configured'  => $invoice_numbering_configured,
				'url'                           => array(
					'preview_sample_invoice'    => self::get_preview_sample_url(),
					'view_latest_order_invoice' => self::get_latest_order_url(),
					'edit_templates'            => 'admin.php?page=wcdn_page#/templates',
				),
			)
		);
	}

	/**
	 * Get frontend URL for previewing a sample document.
	 *
	 * @param string $template Template key.
	 * @return string
	 * @since 7.0
	 */
	public static function get_preview_sample_url( $template = 'invoice' ) {

		$endpoint = Settings::get( 'printEndpoint' );
		$base     = wc_get_page_permalink( 'myaccount' );

		// Build endpoint URL.
		$url = get_option( 'permalink_structure' ) ? trailingslashit( $base ) . $endpoint . '/sample' : add_query_arg( $endpoint, 'sample', $base );

		return add_query_arg(
			array(
				'print-order-type' => $template,
			),
			$url
		);
	}

	/**
	 * Get URL for viewing the latest order invoice.
	 *
	 * Generates a frontend URL pointing to the most recent order's
	 * printable document (e.g., invoice) for quick access from the dashboard.
	 *
	 * Falls back to an empty string if no valid order is found.
	 *
	 * @return string URL to the latest order document.
	 * @since 7.0
	 */
	public static function get_latest_order_url() {

		$orders = wc_get_orders(
			array(
				'limit'      => 1,
				'orderby'    => 'date',
				'order'      => 'DESC',
				'status'     => array( 'processing', 'completed' ),
				'meta_query' => array(),
			)
		);

		if ( empty( $orders ) ) {
			return null;
		}

		$order = $orders[0];

		return Utils::get_print_page_url(
			$order->get_id(),
			'invoice'
		);
	}
}
