<?php
/**
 * WooCommerce Print Invoice & Delivery Note Uninstall
 *
 * Uninstalling WooCommerce Print Invoice & Delivery Note options.
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete the data for the WordPress Multisite.
 */
global $wpdb;

if ( is_multisite() ) {
	$sql_table_user_meta_cart = 'DELETE FROM `' . $wpdb->prefix . 'usermeta` WHERE meta_key LIKE "%wcdn_%"';
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$wpdb->get_results( $wpdb->prepare( $sql_table_user_meta_cart ) );
} else {
	$sql_table_user_meta_cart = 'DELETE FROM `' . $wpdb->prefix . 'usermeta` WHERE meta_key LIKE  "%wcdn_%"';
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$wpdb->get_results( $wpdb->prepare( $sql_table_user_meta_cart ) );
}

wp_cache_flush();
