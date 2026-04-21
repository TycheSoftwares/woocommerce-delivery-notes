<?php
/**
 * Uninstallation functions and actions for Print Invoice & Delivery Notes for WooCommerce
 *
 * @author      Tyche Softwares
 * @package     WCDN/Uninstall
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN;

defined( 'ABSPATH' ) || exit;

/**
 * WCDN Uninstall Class.
 *
 * @since 7.0
 */
class Uninstall {

	/**
	 * Init.
	 *
	 * @since 7.0
	 */
	public static function init() {
		self::delete_plugin_data();
		self::remove_cron_jobs();
		wp_cache_flush();
	}

	/**
	 * Deletes plugin options, transients and events.
	 *
	 * @since 7.0
	 */
	public static function delete_plugin_data() {
		delete_option( WCDN_SLUG . '_version' );
		delete_option( WCDN_SLUG . '_admin_installation_timestamp' );
		delete_option( WCDN_SLUG . '_db_version' );
		delete_option( WCDN_SLUG . '_notices' );
		delete_option( WCDN_SLUG . '_allow_tracking' );
		delete_option( 'ts_tracker_last_send' );
	}

	/**
	 * Removes Cron Jobs.
	 *
	 * @since 7.0
	 */
	public static function remove_cron_jobs() {
		wp_clear_scheduled_hook( 'wcdn_ts_tracker_send_event' );
	}

	/**
	 * Plugin Deactivation.
	 *
	 * @since 7.0
	 */
	public static function deactivate_plugin() {
		self::remove_cron_jobs(); // Remove scheduled cron jobs on plugin deactivation.
	}
}
