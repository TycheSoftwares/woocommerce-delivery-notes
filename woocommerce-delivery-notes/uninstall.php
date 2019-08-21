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

// Generatl information.
delete_option( 'wcdn_company_address ' );
delete_option( 'wcdn_company_logo_image_id' );
delete_option( 'wcdn_custom_company_name' );
delete_option( 'wcdn_personal_notes' );
delete_option( 'wcdn_policies_conditions' );

// Behaviour.
delete_option( 'wcdn_email_print_link' );
delete_option( 'wcdn_footer_imprint' );

// Invoice number generation.
delete_option( 'wcdn_create_invoice_number' );
delete_option( 'wcdn_invoice_number_count' );
delete_option( 'wcdn_invoice_number_prefix' );
delete_option( 'wcdn_invoice_number_suffix' );

// Customer view options.
delete_option( 'wcdn_print_button_on_my_account_page' );
delete_option( 'wcdn_print_button_on_view_order_page' );
delete_option( 'wcdn_print_order_page_endpoint' );

// Template options.
delete_option( 'wcdn_template_style' );
delete_option( 'wcdn_template_type_delivery-note' );
delete_option( 'wcdn_template_type_invoice' );
delete_option( 'wcdn_template_type_receipt' );

// Tracking options.
delete_option( 'wcdn_allow_tracking' );
delete_option( 'wcdn_ts_tracker_last_send' );
delete_option( 'wcdn_version' );

// Delete transient added for new endpoint.
if ( '1' === get_transient( 'wcdn_flush_rewrite_rules' ) ) {
	delete_transient( 'wcdn_flush_rewrite_rules' );
	flush_rewrite_rules();
}
