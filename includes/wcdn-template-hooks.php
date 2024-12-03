<?php
/**
 * Template Hooks
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Header
 */
add_action( 'wcdn_head', 'wcdn_navigation_style' );
add_action( 'wcdn_head', 'wcdn_template_stylesheet' );

/**
 * Before page
 */
add_action( 'wcdn_before_page', 'wcdn_navigation' );

/**
 * Content
 */
add_action( 'wcdn_loop_content', 'wcdn_content', 10, 2 );
add_filter( 'wcdn_order_item_fields', 'wcdn_additional_product_fields', 10, 3 );

/**
 * Add a guest access token to the order.
 */
add_action( 'woocommerce_checkout_create_order', 'add_guest_access_token_to_order', 10, 2 );
add_action( 'woocommerce_store_api_checkout_update_order_meta', 'add_guest_access_token_to_order_blocks', 10, 1 );
