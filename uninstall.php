<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Uninstall functions.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Uninstall
 * @category    Classes
 * @since       7.0
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'WCDN_FILE' ) ) {
	define( 'WCDN_FILE', __FILE__ );
}

require_once __DIR__ . '/includes/class-woocommerce-delivery-notes.php';
\Tyche\WCDN\WooCommerce_Delivery_Notes::define_constants();

require_once __DIR__ . '/includes/core/class-uninstall.php';
\Tyche\WCDN\Uninstall::init();
