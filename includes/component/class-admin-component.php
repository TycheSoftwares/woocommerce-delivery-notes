<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Boilerplate components.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Component
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN;

defined( 'ABSPATH' ) || exit;

/**
 * It will Add all the Boilerplate component when we activate the plugin.
 */
class Admin_Component {

	/**
	 * It will Add all the Boilerplate component when we activate the plugin.
	 */
	public function __construct() {

		if ( ! is_admin() ) {
			return;
		}

		$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$post_action  = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( strpos( $request_uri, 'plugins.php' ) !== false || strpos( $request_uri, 'action=deactivate' ) !== false || ( strpos( $request_uri, 'admin-ajax.php' ) !== false && 'tyche_plugin_deactivation_submit_action' === $post_action ) ) {
			WCDN()::include_file( 'component/plugin-deactivation/class-tyche-plugin-deactivation.php' );
			new Tyche_Plugin_Deactivation(
				array(
					'plugin_name'       => WCDN_PLUGIN_NAME,
					'plugin_base'       => 'woocommerce-delivery-notes/woocommerce-delivery-notes.php',
					'script_file'       => WCDN()::get_asset_url( '/assets/js/plugin-deactivation.js', WCDN_FILE ),
					'plugin_short_name' => WCDN_SLUG,
					'version'           => WCDN_PLUGIN_VERSION,
					'plugin_locale'     => 'woocommerce-delivery-notes',
				)
			);
		}

		WCDN()::include_file( 'component/plugin-tracking/class-tyche-plugin-tracking.php' );
		new Tyche_Plugin_Tracking(
			array(
				'plugin_name'       => WCDN_PLUGIN_NAME,
				'plugin_locale'     => 'woocommerce-delivery-notes',
				'plugin_short_name' => WCDN_SLUG,
				'version'           => WCDN_PLUGIN_VERSION,
				'blog_link'         => 'https://www.tychesoftwares.com/woocommerce-delivery-notes-plugin-usage-tracking/',
			)
		);
	}
}
