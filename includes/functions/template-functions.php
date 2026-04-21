<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Deprecated template functions kept for backwards compatibility.
 *
 * @package WCDN
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output the template part
 *
 * @param string $name Template name.
 * @param array  $args Arguments array.
 */
function wcdn_get_template_content( $name, $args = array() ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Return Type of the print template
 */
function wcdn_get_template_type() {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Return Title of the print template
 */
function wcdn_get_template_title() {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Return print page link
 *
 * @param array   $order_ids Order IDs.
 * @param string  $template_type Template Type.
 * @param string  $order_email Order email.
 * @param boolean $permalink Permalinks.
 */
function wcdn_get_print_link( $order_ids, $template_type = 'order', $order_email = null, $permalink = false ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Output the document title depending on type
 */
function wcdn_document_title() {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Output the print navigation style
 */
function wcdn_navigation_style() {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Create print navigation
 */
function wcdn_navigation() {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Output template stylesheet
 *
 * @param string $template_type Template type.
 */
function wcdn_template_stylesheet( $template_type ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Output the template print content
 *
 * @param object $order Order object.
 * @param string $template_type Template type.
 */
function wcdn_content( $order, $template_type ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Return logo id
 */
function wcdn_get_company_logo_id() {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Show logo html
 */
function wcdn_company_logo() {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Show pdf logo html
 *
 * @param string $ttype pdf type.
 */
function wcdn_pdf_company_logo( $ttype ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Apply css if RTL is active.
 */
function wcdn_rtl() {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Return default title name of Delivery Note
 */
function wcdn_company_name() {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Return shop/company info if provided
 */
function wcdn_company_info() {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Get orders as array. Every order is a normal WC_Order instance.
 */
function wcdn_get_orders() {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Get an order
 *
 * @param int $order_id Order ID.
 */
function wcdn_get_order( $order_id ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Get the order info fields
 *
 * @param object $order Order object.
 * @param string $type  Document type.
 */
function wcdn_get_order_info( $order, $type = '' ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Get the invoice number of an order
 *
 * @param int $order_id Order ID.
 */
function wcdn_get_order_invoice_number( $order_id ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Get the invoice date of an order
 *
 * @param int $order_id Order ID.
 */
function wcdn_get_order_invoice_date( $order_id ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Additional fields for the product
 *
 * @param array  $fields Fields array.
 * @param object $product Product Object.
 * @param object $order Order object.
 */
function wcdn_additional_product_fields( $fields, $product, $order ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- deprecated stub, parameters kept for backwards-compatible signature
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Check if a shipping address is enabled
 * Note: In v4.6.3, we have removed this function but it throws the fatal error on printing the invoice if someone have customized the invoice and copied print-content.php file in thier theme so from v4.6.4 we need to keep this function as blank and returning true value to avoid errors when function is called.
 *
 * @param object $order Order object.
 */
function wcdn_has_shipping_address( $order ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- deprecated stub, parameter kept for backwards-compatible signature
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Check if an order contains a refund
 *
 * @param object $order Order object.
 */
function wcdn_has_refund( $order ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Gets formatted item subtotal for display.
 *
 * @param object $order Order object.
 * @param array  $item Item array.
 * @param string $tax_display Display excluding tax or including.
 */
function wcdn_get_formatted_item_price( $order, $item, $tax_display = '' ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Add refund totals
 *
 * @param array  $total_rows Rows array.
 * @param object $order Order object.
 */
function wcdn_add_refunded_order_totals( $total_rows, $order ) {
	if ( wcdn_has_refund( $order ) ) {
		$wdn_order_currency = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) ? $order->get_currency() : $order->get_order_currency();

		if ( version_compare( WC_VERSION, '2.3.0', '>=' ) ) {
			$refunded_tax_del = '';
			$refunded_tax_ins = '';

			// Tax for inclusive prices.
			if ( wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_cart' ) ) {
				$tax_del_array = array();
				$tax_ins_array = array();

				if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {

					foreach ( $order->get_tax_totals() as $code => $tax ) {
						$tax_del_array[] = sprintf( '%s %s', $tax->formatted_amount, $tax->label );
						$tax_ins_array[] = sprintf( '%s %s', wc_price( $tax->amount - $order->get_total_tax_refunded_by_rate_id( $tax->rate_id ), array( 'currency' => $wdn_order_currency ) ), $tax->label );
					}
				} else {
					$tax_del_array[] = sprintf( '%s %s', wc_price( $order->get_total_tax(), array( 'currency' => $wdn_order_currency ) ), WC()->countries->tax_or_vat() );
					$tax_ins_array[] = sprintf( '%s %s', wc_price( $order->get_total_tax() - $order->get_total_tax_refunded(), array( 'currency' => $wdn_order_currency ) ), WC()->countries->tax_or_vat() );
				}

				if ( ! empty( $tax_del_array ) ) {
					/* translators: %s: Taxes to delete */
					$refunded_tax_del .= ' ' . sprintf( __( '(Includes %s)', 'woocommerce' ), implode( ', ', $tax_del_array ) );
				}

				if ( ! empty( $tax_ins_array ) ) {
					/* translators: %s: Taxes to insert */
					$refunded_tax_ins .= ' ' . sprintf( __( '(Includes %s)', 'woocommerce' ), implode( ', ', $tax_ins_array ) );
				}
			}
			// use only the number for new wc versions.
			$order_subtotal = wc_price( $order->get_total(), array( 'currency' => $wdn_order_currency ) );
		} else {
			$refunded_tax_del = '';
			$refunded_tax_ins = '';

			// use the normal total for older wc versions.
			$order_subtotal = $total_rows['order_total']['value'];
		}

		// Add refunded totals row.
		$total_rows['wcdn_refunded_total'] = array(
			'label' => __( 'Refund', 'woocommerce-delivery-notes' ),
			'value' => wc_price( -$order->get_total_refunded(), array( 'currency' => $wdn_order_currency ) ),
		);

		// Add new order totals row.
		$total_rows['wcdn_order_total'] = array(
			'label' => $total_rows['order_total']['label'],
			'value' => wc_price( $order->get_total() - $order->get_total_refunded(), array( 'currency' => $wdn_order_currency ) ) . $refunded_tax_ins,
		);

		// Edit the original order total row.
		$total_rows['order_total'] = array(
			'label' => __( 'Order Subtotal', 'woocommerce-delivery-notes' ),
			'value' => $order_subtotal,
		);
	}

	return $total_rows;
}

/**
 * Remove the semicolon from the totals
 *
 * @param array  $total_rows Rows array.
 * @param object $order Order object.
 */
function wcdn_remove_semicolon_from_totals( $total_rows, $order ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- $order required by WooCommerce filter signature
	foreach ( $total_rows as $key => $row ) {
		$label = $row['label'];
		$colon = strrpos( $label, ':' );
		if ( false !== $colon ) {
			$label = substr_replace( $label, '', $colon, 1 );
		}
		$total_rows[ $key ]['label'] = $label;
	}
	return $total_rows;
}

/**
 * Remove the payment method text from the totals
 *
 * @param array  $total_rows Rows array.
 * @param object $order Order object.
 */
function wcdn_remove_payment_method_from_totals( $total_rows, $order ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- $order required by WooCommerce filter signature
	unset( $total_rows['payment_method'] );
	unset( $total_rows['refund_0'] );
	return $total_rows;
}

/**
 * Return customer notes
 *
 * @param object $order Order object.
 */
function wcdn_get_customer_notes( $order ) {
	_deprecated_function( __METHOD__, '7.0' );}

/**
 * Show customer notes
 *
 * @param object $order Order object.
 */
function wcdn_customer_notes( $order ) {
	_deprecated_function( __METHOD__, '7.0' );}

/**
 * Return has customer notes
 *
 * @param object $order Order object.
 */
function wcdn_has_customer_notes( $order ) {
	_deprecated_function( __METHOD__, '7.0' );}

/**
 * Return personal notes, season greetings etc.
 */
function wcdn_get_personal_notes() {
	_deprecated_function( __METHOD__, '7.0' );}

/**
 * Show personal notes, season greetings etc.
 */
function wcdn_personal_notes() {
	_deprecated_function( __METHOD__, '7.0' );}

/**
 * Return policy for returns
 */
function wcdn_get_policies_conditions() {
	_deprecated_function( __METHOD__, '7.0' );}

/**
 * Show policy for returns
 */
function wcdn_policies_conditions() {
	_deprecated_function( __METHOD__, '7.0' );}

/**
 * Return shop/company footer imprint, copyright etc.
 */
function wcdn_get_imprint() {
	_deprecated_function( __METHOD__, '7.0' );}

/**
 * Show shop/company footer imprint, copyright etc.
 */
function wcdn_imprint() {
	_deprecated_function( __METHOD__, '7.0' );}

/**
 * Show PIF Fileds in the invoice.
 *
 * @param array $item Cart item array.
 */
function wcdn_print_extra_fields( $item ) {
	_deprecated_function( __METHOD__, '7.0' );}

/**
 * Function to pass product name in PDFs.
 *
 * @param object   $product product array.
 * @param WC_Order $order Order object.
 * @param object   $item Item Type.
 */
function wcdn_get_product_name( $product, $order, $item ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Function to adjust the item quantity.
 *
 * @param WC_Order      $order   The WooCommerce order object.
 * @param WC_Order_Item $item_id  The order item object containing product details.
 */
function wcdn_get_adjusted_quantity( $order, $item_id ) {
	_deprecated_function( __FUNCTION__, '7.0' );
}

/**
 * Adds a guest access token for normal checkout.
 *
 * @param WC_Order $order The WooCommerce order object.
 * @param array    $data  The checkout data.
 */
function wcdn_add_guest_access_token_to_order( $order, $data ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- deprecated stub, parameters kept for backwards-compatible signature
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Adds a guest access token for block-based checkout.
 *
 * @param WC_Order $order The WooCommerce order object.
 */
function wcdn_add_guest_access_token_to_order_blocks( $order ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Generates and saves a guest access token to the order meta.
 *
 * @param WC_Order $order The WooCommerce order object.
 */
function wcdn_add_guest_access_token( $order ) {
	_deprecated_function( __METHOD__, '7.0' );
}

/**
 * Format date helper.
 *
 * @param string $date       Date string.
 * @param string $date_format Date format.
 * @return string
 */
function wcdn_format_date( $date, $date_format ) {
	if ( empty( $date ) ) {
		return '';
	}

	if ( empty( $date_format ) ) {
		$date_format = get_option( 'date_format' );
	}

	$timestamp = is_numeric( $date ) ? (int) $date : strtotime( $date );

	if ( false === $timestamp ) {
		return '';
	}

	return date_i18n( $date_format, $timestamp );
}

/**
 * Build inline style string for a settings-driven element.
 *
 * @param array  $settings Settings array.
 * @param string $prefix   Settings key prefix.
 * @return string
 */
function wcdn_meta_style( $settings, $prefix ) {
	return sprintf(
		'text-align:%s;color:%s;font-weight:%s;',
		esc_attr( isset( $settings[ "{$prefix}Align" ] ) ? $settings[ "{$prefix}Align" ] : 'left' ),
		esc_attr( isset( $settings[ "{$prefix}TextColor" ] ) ? $settings[ "{$prefix}TextColor" ] : '#000' ),
		( isset( $settings[ "{$prefix}FontStyle" ] ) && 'bold' === $settings[ "{$prefix}FontStyle" ] ) ? '600' : '400'
	);
}

/**
 * Join non-empty items with a separator.
 *
 * @param array  $items     Items to join.
 * @param string $separator Separator string.
 * @return string
 */
function wcdn_separate( $items = array(), $separator = ' · ' ) {
	$filtered = array_values( array_filter( $items ) );
	return implode( $separator, $filtered );
}
