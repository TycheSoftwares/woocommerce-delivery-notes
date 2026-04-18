<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Class for loading asset files for the Admin.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Admin/Files
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Scripts.
 *
 * @since 7.0
 */
class Scripts extends Admin {

	/**
	 * Construct
	 *
	 * @since 7.0
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_css' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_js' ) );
		add_filter( WCDN_SLUG . '_ts_tracker_display_notice', array( &$this, 'display_tracking_notice' ) );
	}

	/**
	 * CSS.
	 *
	 * @since 7.0
	 */
	public static function enqueue_css() {

		if ( ! self::is_on_wcdn_page() ) {
			return;
		}

		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'wp-edit-blocks' );

		wp_enqueue_style(
			'wcdn-main',
			WCDN()::get_asset_url( '/build/admin.css', WCDN_FILE ),
			array(),
			WCDN_PLUGIN_VERSION,
			false
		);
	}

	/**
	 * JS.
	 *
	 * @since 7.0
	 */
	public static function enqueue_js() {

		if ( ! self::is_on_wcdn_page() ) {
			return;
		}

		// Load WordPress Media Uploader.
		wp_enqueue_media();

		if ( file_exists( WCDN_PLUGIN_PATH . '/build/admin.asset.php' ) ) {

			$asset = include WCDN_PLUGIN_PATH . '/build/admin.asset.php';

			if ( $asset ) {
				wp_enqueue_script(
					'wcdn-main',
					WCDN()::get_asset_url( '/build/admin.js', WCDN_FILE ),
					$asset['dependencies'],
					$asset['version'],
					true
				);

				wp_set_script_translations( 'wcdn-main', 'wcdn-main', WCDN_PLUGIN_PATH . '/languages/' );
			}
		}
	}

	/**
	 * Display Tracking Notice.
	 *
	 * @param bool $display Whether to display.
	 * @since 7.0
	 */
	public static function display_tracking_notice( $display ) {
		return self::is_on_wcdn_page();
	}
}
