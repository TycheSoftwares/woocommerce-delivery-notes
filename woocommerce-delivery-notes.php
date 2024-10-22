<?php
/**
 * Print invoices & delivery notes for WooCommerce orders.
 *
 * Plugin Name: Print Invoice & Delivery Notes for WooCommerce
 * Plugin URI: https://www.tychesoftwares.com/
 * Description: Print Invoices & Delivery Notes for WooCommerce Orders.
 * Version: 5.2.0
 * Author: Tyche Softwares
 * Author URI: https://www.tychesoftwares.com/
 * License: GPLv3 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: woocommerce-delivery-notes
 * Domain Path: /languages
 * Requires PHP: 7.4
 * WC requires at least: 5.0.0
 * WC tested up to: 9.3.3
 * Tested up to: 6.6.2
 * Requires Plugins: woocommerce
 *
 * Copyright 2019 Tyche Softwares
 *
 *     This file is part of Print Invoice & Delivery Notes for WooCommerce,
 *     a plugin for WordPress.
 *
 *     Print Invoice & Delivery Notes for WooCommerce is free software:
 *     You can redistribute it and/or modify it under the terms of the
 *     GNU General Public License as published by the Free Software
 *     Foundation, either version 2 of the License, or (at your option)
 *     any later version.
 *
 *     Print Invoice & Delivery Notes for WooCommerce is distributed in the hope that
 *     it will be useful, but WITHOUT ANY WARRANTY; without even the
 *     implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 *     PURPOSE. See the GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with WordPress. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce_Delivery_Notes' ) ) {
	include_once 'includes/class-woocommerce-delivery-notes.php';

	/**
	 * Global for backwards compatibility.
	 */
	$GLOBALS['wcdn'] = WooCommerce_Delivery_Notes::instance();

	/**
	 * Sets the compatibility with Woocommerce HPOS.
	 */
	add_action(
		'before_woocommerce_init',
		function() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', 'woocommerce-delivery-notes/woocommerce-delivery-notes.php', true );
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'orders_cache', 'woocommerce-delivery-notes/woocommerce-delivery-notes.php', true );
			}
		}
	);

}
