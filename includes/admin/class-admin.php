<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Admin Base Class.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Admin
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Base Class.
 *
 * @since 7.0
 */
class Admin {

	/**
	 * Construct
	 *
	 * @since 7.0
	 */
	public function __construct() {
	}

	/**
	 * Checks if the user is on the Admin Section of the Plugin.
	 *
	 * @since 7.0
	 */
	public static function is_on_wcdn_page() {
		global $pagenow;
		return 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'wcdn_page' === $_GET['page']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading admin URL param for context detection only
	}

	/**
	 * Checks if the user is on theWP Plugin Page.
	 *
	 * @since 7.0
	 */
	public static function is_on_wp_plugin_page() {
		global $pagenow;
		return 'plugins.php' === $pagenow;
	}
}
