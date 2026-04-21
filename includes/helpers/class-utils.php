<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Utility Helper Class.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Helpers
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Helpers;

use Tyche\WCDN\Helpers\Settings;
use Tyche\WCDN\Helpers\Templates;

defined( 'ABSPATH' ) || exit;

/**
 * Utility Helper Class.
 *
 * Provides helper methods for print URLs
 * and invoice-related order data.
 *
 * @since 7.0
 */
class Utils {

	/**
	 * Get print page url.
	 *
	 * @param array|string $order_ids     Order ids.
	 * @param string       $template_type Template type.
	 * @param string|null  $order_email   Order email.
	 * @param boolean      $permalink     Permalink mode.
	 * @return string
	 * @since 7.0
	 */
	public static function get_print_page_url( $order_ids, $template_type = 'invoice', $order_email = null, $permalink = false ) {

		$endpoint = Settings::get( 'printEndpoint' );

		// Explode the ids when needed.
		if ( ! is_array( $order_ids ) ) {
			$order_ids = array_filter( explode( '-', $order_ids ) );
		}

		// Build the args.
		$args = array( 'print-order-type' => $template_type );

		// Set the email arg.
		if ( ! empty( $order_email ) ) {
			$args = wp_parse_args( array( 'print-order-email' => rawurlencode( $order_email ) ), $args );
		}

		// Generate the url.
		$order_ids_slug = implode( '-', $order_ids );

		// Check for guest access token in the order meta.
		$guest_token       = '';
		$current_user_id   = get_current_user_id();
		$current_user_type = $current_user_id ? ( current_user_can( 'manage_woocommerce' ) ? 'admin' : 'customer' ) : 'guest'; // phpcs:ignore WordPress.WP.Capabilities.Unknown

		$should_check_guest_token = apply_filters(
			'wcdn_allow_check_for_guest_token',
			! is_user_logged_in(),
			$current_user_type,
			$current_user_id
		);

		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );

			if ( $order && $should_check_guest_token ) {
				$guest_token = $order->get_meta( '_guest_access_token' );
				if ( $guest_token ) {
					break; // If we found a token, we can stop searching.
				}
			}
		}

		// Create another url depending on where the user prints. This prevents some issues with ssl when the my-account page is secured with ssl but the admin isn't.
		if ( is_admin() && current_user_can( 'edit_shop_orders' ) && false === $permalink ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown

			// For the admin we use the ajax.php for better security.
			$args     = wp_parse_args( array( 'action' => 'print_order' ), $args );
			$base_url = admin_url( 'admin-ajax.php' );

			// Add the order ids and create the url.
			$url = add_query_arg( $endpoint, $order_ids_slug, $base_url );
		} else {
			// For the theme.
			$base_url = wc_get_page_permalink( 'myaccount' );

			// Add the order ids and create the url.
			$url = get_option( 'permalink_structure' ) ? trailingslashit( trailingslashit( $base_url ) . $endpoint . '/' . $order_ids_slug ) : add_query_arg( $endpoint, $order_ids_slug, $base_url );
		}

		// Add all other args.
		$url = add_query_arg( $args, $url );

		// If a guest token exists, add it as a query parameter AFTER the email.
		if ( $guest_token ) {
			$url = add_query_arg( 'guest_token', $guest_token, $url );
		}

		return $url;
	}

	/**
	 * Get the order.
	 *
	 * @param int $order_id Order id.
	 * @return \WC_Order|false
	 * @since 7.0
	 */
	public static function get_order( $order_id ) {
		if ( isset( WCDN()->print->orders[ $order_id ] ) ) {
			return WCDN()->print->orders[ $order_id ];
		}

		return false;
	}

	/**
	 * Replace placeholders in a given text string.
	 *
	 * Supported placeholders:
	 * {order_number}, {order_date}, {customer_name}
	 *
	 * @param string        $text     Text containing placeholders.
	 * @param int|\WC_Order $order    Order ID or WC_Order instance.
	 * @param bool          $do_strip Whether to sanitize values for filenames.
	 * @return string
	 * @since 7.0
	 */
	public static function replace_placeholders( $text, $order, $do_strip = true ) {

		if ( empty( $text ) || empty( $order ) ) {
			return $text;
		}

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( $order instanceof \WC_Order_Refund ) {
			$order = wc_get_order( $order->get_parent_id() );
		}

		if ( ! $order instanceof \WC_Order ) {
			return $text;
		}

		$order_id      = wcdn_order_id( $order );
		$date          = $order->get_date_created();
		$month         = $date ? $date->date( 'm' ) : '';
		$day           = $date ? $date->date( 'd' ) : '';
		$order_date    = wc_format_datetime( $date, 'Ymd' );
		$customer_name = $order->get_formatted_billing_full_name();
		$customer_id   = $order->get_customer_id();

		if ( $do_strip ) {
			$customer_name = strtolower( sanitize_file_name( $customer_name ) );
			$customer_name = str_replace( ' ', '-', $customer_name );
		}

		// Site name.
		$site_name = get_bloginfo( 'name' );

		if ( $do_strip ) {
			$site_name = strtolower( sanitize_file_name( $site_name ) );
			$site_name = str_replace( ' ', '-', $site_name );
		}

		$needs_counter = strpos( $text, '{next_number}' ) !== false;
		$counter       = $order->get_meta( '_wcdn_invoice_number_counter', true );

		if ( ! $counter && $needs_counter ) {
			$counter = self::maybe_generate_counter( $order_id );
		}

		if ( $counter ) {
			$length  = (int) get_option( 'wcdn_invoice_number_counter_length', 4 );
			$counter = str_pad( (int) $counter, $length, '0', STR_PAD_LEFT );
		}

		$year = $order->get_meta( '_wcdn_invoice_year', true );

		if ( ! $year ) {
			$year = $date ? $date->date( 'Y' ) : '';
		}

		$replacements = array(
			'{order_number}'  => $order->get_order_number(),
			'{order_date}'    => $order_date,
			'{customer_name}' => $customer_name,
			'{year}'          => $year,
			'{month}'         => $month,
			'{day}'           => $day,
			'{site_name}'     => $site_name,
			'{customer_id}'   => $customer_id ? $customer_id : '',
			'{next_number}'   => $counter ? $counter : '',
		);

		$replacements = apply_filters(
			'wcdn_placeholder_replacements',
			$replacements,
			$order
		);

		return str_replace(
			array_keys( $replacements ),
			array_values( $replacements ),
			$text
		);
	}

	/**
	 * Generate and assign invoice counter for an order.
	 *
	 * Creates a sequential, year-based counter if it does not already exist.
	 * Ensures uniqueness using a transient lock mechanism and stores both
	 * the counter and year in order meta.
	 *
	 * @param int $order_id Order ID.
	 * @return int|null Generated counter or null on failure.
	 * @since 7.0
	 */
	public static function maybe_generate_counter( $order_id ) {

		if ( 'sample' === $order_id ) {
			return;
		}

		$meta_key = '_wcdn_invoice_number_counter';

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return null;
		}

		// Already exists → return it.
		$counter = $order->get_meta( $meta_key, true );

		if ( $counter ) {
			return (int) $counter;
		}

		$use_yearly = Settings::get( 'resetInvoiceNumberYearly' );
		$year       = gmdate( 'Y' );

		$lock_key = 'wcdn_invoice_number_counter_lock';

		if ( ! add_option( $lock_key, microtime( true ), '', false ) ) {

			$lock = get_option( $lock_key );

			if ( $lock && ( microtime( true ) - (float) $lock ) < 10 ) {
				return (int) $order->get_meta( $meta_key, true );
			}

			update_option( $lock_key, microtime( true ), false );
		}

		// Generate counter.
		if ( $use_yearly ) {

			$counters = get_option( 'wcdn_invoice_number_year_counters', array() );
			$current  = isset( $counters[ $year ] ) ? (int) $counters[ $year ] : 0;

			// First invoice of the year → apply starting number.
			if ( 0 === $current ) {
				$start   = (int) Settings::get( 'startingNumberForEachYear' );
				$current = max( 1, $start - 1 );
			}

			$counter           = $current + 1;
			$counters[ $year ] = $counter;

			update_option( 'wcdn_invoice_number_year_counters', $counters );
			$order->update_meta_data( '_wcdn_invoice_year', $year );
		} else {
			$current = (int) get_option( 'wcdn_invoice_number_counter', 0 );
			$counter = $current + 1;
			update_option( 'wcdn_invoice_number_counter', $counter );
		}

		$order->update_meta_data( $meta_key, $counter );
		$order->save();
		delete_option( $lock_key );

		return $counter;
	}

	/**
	 * Get the order invoice number.
	 *
	 * @param int $order_id Order id.
	 * @return string
	 * @since 7.0
	 */
	public static function get_order_invoice_number( $order_id ) {
		$invoice_number_format = Settings::get( 'invoiceNumberFormat' );
		$order                 = wc_get_order( $order_id );
		$meta_key              = '_wcdn_invoice_number';

		if ( ! $order ) {
			return '';
		}

		$invoice_number = $order->get_meta( $meta_key, true );

		if ( '' === $invoice_number ) {

			self::maybe_generate_counter( $order_id );

			$invoice_number = self::replace_placeholders( $invoice_number_format, $order );

			$order->add_meta_data( $meta_key, $invoice_number, true );
			$order->save();
		}

		return apply_filters( 'wcdn_order_invoice_number', $invoice_number );
	}

	/**
	 * Get the order invoice date.
	 *
	 * @param int $order_id Order id.
	 * @return string
	 * @since 7.0
	 */
	public static function get_order_invoice_date( $order_id ) {
		return self::get_order_document_date( $order_id, 'invoice' );
	}

	/**
	 * Get (or generate and persist) the document date for a given template type.
	 *
	 * The date is stored as a Unix timestamp in `_wcdn_{template}_date` order meta
	 * the first time the document is generated. Subsequent calls return the same value
	 * so the date never changes after initial issuance.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $template Template key ('invoice', 'receipt', 'deliverynote', 'packingslip', 'creditnote').
	 * @return string Formatted date string.
	 * @since 7.0
	 */
	public static function get_order_document_date( $order_id, $template ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return '';
		}

		$meta_key  = '_wcdn_' . sanitize_key( $template ) . '_date';
		$meta_date = $order->get_meta( $meta_key, true );

		if ( '' === $meta_date ) {
			$meta_date = time();
			$order->add_meta_data( $meta_key, $meta_date, true );
			$order->save();
		}

		$formatted_date = date_i18n( get_option( 'date_format' ), (int) $meta_date );

		return apply_filters( 'wcdn_order_invoice_date', $formatted_date, (int) $meta_date );
	}

	/**
	 * Get WP_Filesystem instance.
	 *
	 * @return \WP_Filesystem_Base|null
	 * @since 7.0
	 */
	public static function get_filesystem() {

		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem;
	}

	/**
	 * Get template label configuration.
	 *
	 * @param string $template Template key.
	 * @return array|null
	 * @since 7.0
	 */
	public static function template_label( $template ) {

		$templates = array(

			'invoice'      => apply_filters(
				'wcdn_template_registration_invoice',
				array(
					'labels' => array(
						'name'           => __( 'Invoice', 'woocommerce-delivery-notes' ),
						'name_plural'    => __( 'Invoices', 'woocommerce-delivery-notes' ),
						'print'          => __( 'Print Invoice', 'woocommerce-delivery-notes' ),
						'print_plural'   => __( 'Print Invoices', 'woocommerce-delivery-notes' ),
						'message'        => __( 'Invoice created.', 'woocommerce-delivery-notes' ),
						'message_plural' => __( 'Invoices created.', 'woocommerce-delivery-notes' ),
						'setting'        => __( 'Show "Print Invoice" button', 'woocommerce-delivery-notes' ),
					),
				)
			),

			'deliverynote' => apply_filters(
				'wcdn_template_registration_delivery_note',
				array(
					'labels' => array(
						'name'           => __( 'Delivery Note', 'woocommerce-delivery-notes' ),
						'name_plural'    => __( 'Delivery Notes', 'woocommerce-delivery-notes' ),
						'print'          => __( 'Print Delivery Note', 'woocommerce-delivery-notes' ),
						'print_plural'   => __( 'Print Delivery Notes', 'woocommerce-delivery-notes' ),
						'message'        => __( 'Delivery Note created.', 'woocommerce-delivery-notes' ),
						'message_plural' => __( 'Delivery Notes created.', 'woocommerce-delivery-notes' ),
						'setting'        => __( 'Show "Print Delivery Note" button', 'woocommerce-delivery-notes' ),
					),
				)
			),

			'receipt'      => apply_filters(
				'wcdn_template_registration_receipt',
				array(
					'labels' => array(
						'name'           => __( 'Receipt', 'woocommerce-delivery-notes' ),
						'name_plural'    => __( 'Receipts', 'woocommerce-delivery-notes' ),
						'print'          => __( 'Print Receipt', 'woocommerce-delivery-notes' ),
						'print_plural'   => __( 'Print Receipts', 'woocommerce-delivery-notes' ),
						'message'        => __( 'Receipt created.', 'woocommerce-delivery-notes' ),
						'message_plural' => __( 'Receipts created.', 'woocommerce-delivery-notes' ),
						'setting'        => __( 'Show "Print Receipt" button', 'woocommerce-delivery-notes' ),
					),
				)
			),

			'creditnote'   => apply_filters(
				'wcdn_template_registration_creditnote',
				array(
					'labels' => array(
						'name'           => __( 'Credit Note', 'woocommerce-delivery-notes' ),
						'name_plural'    => __( 'Credit Notes', 'woocommerce-delivery-notes' ),
						'print'          => __( 'Print Credit Note', 'woocommerce-delivery-notes' ),
						'print_plural'   => __( 'Print Credit Notes', 'woocommerce-delivery-notes' ),
						'message'        => __( 'Credit Note created.', 'woocommerce-delivery-notes' ),
						'message_plural' => __( 'Credit Notes created.', 'woocommerce-delivery-notes' ),
						'setting'        => __( 'Show "Credit Note" button', 'woocommerce-delivery-notes' ),
					),
				)
			),

			'packingslip'  => apply_filters(
				'wcdn_template_registration_packingslip',
				array(
					'labels' => array(
						'name'           => __( 'Packing Slip', 'woocommerce-delivery-notes' ),
						'name_plural'    => __( 'Packing Slips', 'woocommerce-delivery-notes' ),
						'print'          => __( 'Print Packing Slip', 'woocommerce-delivery-notes' ),
						'print_plural'   => __( 'Print Packing Slips', 'woocommerce-delivery-notes' ),
						'message'        => __( 'Packing Slip created.', 'woocommerce-delivery-notes' ),
						'message_plural' => __( 'Packing Slips created.', 'woocommerce-delivery-notes' ),
						'setting'        => __( 'Show "Print Packing Slip" button', 'woocommerce-delivery-notes' ),
					),
				)
			),

		);

		return isset( $templates[ $template ] ) ? $templates[ $template ] : null;
	}

	/**
	 * Get template types based on order.
	 *
	 * @param \WC_Order $order Order object.
	 * @return string
	 * @since 7.0
	 */
	public static function get_template_types( $order ) {

		if ( $order instanceof \WC_Order_Refund ) {
			$order = wc_get_order( $order->get_parent_id() );
			if ( ! $order ) {
				return array();
			}
		}

		$types  = array();
		$status = $order->get_status();

		// Check if order has shippable items.
		$has_physical_items = false;

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();

			if ( $product && ! $product->is_virtual() ) {
				$has_physical_items = true;
				break;
			}
		}

		// Invoice.
		if ( in_array( $status, array( 'pending', 'on-hold', 'processing', 'completed' ), true ) ) {
			$types[] = 'invoice';
		}

		// Receipt (only after payment).
		if ( $order->is_paid() ) {
			$types[] = 'receipt';
		}

		// Shipping documents (only for physical products).
		if ( $has_physical_items ) {

			$types[] = 'packingslip';

			// Optional: stricter delivery note logic.
			if ( in_array( $status, array( 'processing', 'completed' ), true ) ) {
				$types[] = 'deliverynote';
			}
		}

		// Credit note.
		$refunded_total = (float) $order->get_total_refunded();

		if ( $refunded_total > 0 ) {

			// Partial refund vs full refund.
			if ( $refunded_total < (float) $order->get_total() ) {
				// Partial refund → still show credit note.
				$types[] = 'creditnote';
			} else {
				// Full refund → definitely show credit note.
				$types[] = 'creditnote';
			}
		}

		return apply_filters( 'wcdn_template_types_from_order', $types, $order );
	}

	/**
	 * Get the display label for a given template type.
	 *
	 * Returns the configured button label for a specific document/template type
	 * (e.g. invoice, receipt, packing slip). Falls back to a default "Print"
	 * label if no mapping exists.
	 *
	 * @param string $template_type Template type slug.
	 *
	 * @return string Translated label for the given template type.
	 * @since 7.0
	 */
	public static function get_label_for_template_type( $template_type ) {

		$map = array(
			'invoice'      => 'invoiceButtonLabel',
			'deliverynote' => 'deliveryNoteButtonLabel',
			'receipt'      => 'receiptButtonLabel',
			'creditnote'   => 'creditNoteButtonLabel',
			'packingslip'  => 'packingSlipButtonLabel',
		);

		if ( isset( $map[ $template_type ] ) ) {
			$override = Settings::get( $map[ $template_type ] );
			if ( ! empty( $override ) ) {
				return $override;
			}
		}

		$label_data = self::template_label( $template_type );
		return $label_data['labels']['print'] ?? __( 'Print', 'woocommerce-delivery-notes' );
	}

	/**
	 * Get the document type name for a given template type.
	 *
	 * Returns a plain document name (e.g. "Invoice", "Receipt") suitable for use in email subjects and body text.
	 *
	 * @param string $template_type Template type slug.
	 *
	 * @return string Document type name.
	 * @since 7.0
	 */
	public static function get_document_name_for_template_type( $template_type ) {

		$map = array(
			'invoice'      => __( 'Invoice', 'woocommerce-delivery-notes' ),
			'deliverynote' => __( 'Delivery Note', 'woocommerce-delivery-notes' ),
			'receipt'      => __( 'Receipt', 'woocommerce-delivery-notes' ),
			'creditnote'   => __( 'Credit Note', 'woocommerce-delivery-notes' ),
			'packingslip'  => __( 'Packing Slip', 'woocommerce-delivery-notes' ),
		);

		$name = isset( $map[ $template_type ] ) ? $map[ $template_type ] : Settings::get( 'defaultDocumentLabel' );

		return apply_filters( 'wcdn_document_title', $name, $template_type );
	}
}
