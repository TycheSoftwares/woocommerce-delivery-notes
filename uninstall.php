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

require_once __DIR__ . '/woocommerce-delivery-notes.php';
require_once __DIR__ . '/includes/woocommerce-delivery-notes.php';
WCDN_Uninstall::init();
