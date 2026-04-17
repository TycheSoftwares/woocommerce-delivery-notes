<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Admin Menu.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Admin/Menu
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN;

defined( 'ABSPATH' ) || exit;

/**
 * Class for adding the Menu.
 */
class Menu {

	/**
	 * Constructor.
	 *
	 * @since 7.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
	}

	/**
	 * Admin Menu.
	 *
	 * @since 7.0
	 */
	public function admin_menu() {

		add_submenu_page(
			'woocommerce',
			__( 'Print Invoices & Delivery Notes', 'woocommerce-delivery-notes' ),
			__( 'Print Invoices & Delivery Notes', 'woocommerce-delivery-notes' ),
			'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown
			'wcdn_page',
			array( __CLASS__, 'admin_page' )
		);
	}

	/**
	 * Displays the Settings menu item.
	 *
	 * @since 7.0
	 */
	public static function admin_page() {
		?>
		<div id="woocommerce-delivery-notes"></div>
		<?php
	}
}
