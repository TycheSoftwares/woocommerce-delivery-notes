<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * General plugin helper functions.
 *
 * @package WCDN
 * @since   7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if WooCommerce version is 3.0.0 or higher.
 *
 * @return bool True if WooCommerce version >= 3.0.0, false otherwise.
 *
 * @since 7.0
 */
function wcdn_woocommerce_version_3_0_0() {

	$version = get_option( 'woocommerce_version' );

	if ( empty( $version ) && defined( 'WC_VERSION' ) ) {
		$version = WC_VERSION;
	}

	return version_compare( $version, '3.0.0', '>=' );
}

/**
 * Get email addresses of all administrators and shop managers.
 *
 * @return array List of unique email addresses.
 *
 * @since 7.0
 */
function wcdn_get_all_administrator_emails() {

	$users = get_users(
		array(
			'role__in' => array( 'administrator', 'shop_manager' ),
			'fields'   => array( 'user_email' ),
		)
	);

	if ( empty( $users ) ) {
		return array();
	}

	$emails = wp_list_pluck( $users, 'user_email' );

	// Remove duplicates and empty values.
	$emails = array_filter( array_unique( $emails ) );

	/**
	 * Filter administrator email recipients.
	 *
	 * @param array $emails List of email addresses.
	 * @since 7.0
	 */
	return apply_filters( 'wcdn_administrator_emails', $emails );
}

/**
 * Retrieve the Order ID from an Order.
 *
 * @param WC_Order $order Order object.
 *
 * @since 7.0
 */
function wcdn_order_id( $order ) {
	return wcdn_woocommerce_version_3_0_0() ? $order->get_id() : $order->id;
}

/**
 * Format a phone number for display on documents.
 *
 * Passes the raw phone and billing country through the
 * `wcdn_format_phone_number` filter so store owners can
 * apply locale-specific formatting without modifying the plugin.
 *
 * @param string $phone   Raw phone number.
 * @param string $country ISO 3166-1 alpha-2 billing country code.
 * @return string
 *
 * @since 7.0
 */
function wcdn_format_phone_number( $phone, $country = '' ) {

	/**
	 * Filter the phone number displayed on documents.
	 *
	 * @param string $phone   Raw phone number.
	 * @param string $country Billing country code (e.g. 'US', 'GB').
	 * @since 7.0
	 */
	return apply_filters( 'wcdn_format_phone_number', $phone, $country );
}
