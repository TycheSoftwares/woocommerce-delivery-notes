<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce
 *
 * @package      woocommerce-print-invoice-delivery-notes
 * @copyright    Copyright (C) 2026, Tyche Softwares - support@tychesoftwares.com
 * @link         https://www.tychesoftwares.com
 * @since        6.0
 *
 * @wordpress-plugin
 * Plugin Name:  Print Invoice & Delivery Notes for WooCommerce
 * Plugin URI:   https://www.tychesoftwares.com
 * Description:  This plugin lets you generate, customize, print, and email professional order documents directly from your WooCommerce store.
 * Version:      7.0.0
 * Author:       Tyche Softwares
 * Author URI:   https://www.tychesoftwares.com
 * Text Domain:  woocommerce-delivery-notes
 * Domain Path: /languages
 * Requires PHP: 7.4
 * WC requires at least: 5.0.0
 * WC tested up to: 10.7.0
 * Requires Plugins: woocommerce
 * License: GPLv3
 *
 * Copyright 2026 Tyche Softwares
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
 */

namespace Tyche\WCDN;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WCDN_FILE' ) ) {
	define( 'WCDN_FILE', __FILE__ );
}

// Include the WCDN class.
if ( ! class_exists( 'WooCommerce_Delivery_Notes', false ) ) {
	include_once dirname( WCDN_FILE ) . '/includes/class-woocommerce-delivery-notes.php';
}

/**
 * Returns the instance of WCDN.
 *
 * @since  7.0
 * @return WCDN
 */
function WCDN() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WooCommerce_Delivery_Notes::instance();
}

WCDN();
