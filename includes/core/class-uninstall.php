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
	 * Snapshots current v7 settings before restoring legacy options, so that:
	 * - A downgrade to v6 finds its original settings intact.
	 * - Re-activating v7 restores the snapshot instead of re-running migration.
	 *
	 * @since 7.0
	 */
	public static function deactivate_plugin() {
		self::remove_cron_jobs();
		self::snapshot_v7_settings();

		$rolled_back = Migration::rollback();

		if ( $rolled_back ) {
			// rollback() deleted the v7 settings and reset the migration flags.
			// Reset db_version so maybe_update() calls Migration::run() on
			// re-activation, which will find and restore the snapshot.
			update_option( WCDN_SLUG . '_db_version', '0.0.0' );
		}
	}

	/**
	 * Saves current v7 settings to snapshot options before rollback.
	 *
	 * @since 7.0
	 */
	private static function snapshot_v7_settings() {
		$settings  = get_option( \Tyche\WCDN\Api\Settings::OPTION_KEY );
		$templates = get_option( \Tyche\WCDN\Api\Templates::OPTION_KEY );

		if ( $settings ) {
			update_option( WCDN_SLUG . '_v7_settings_snapshot', $settings, false );
		}

		if ( $templates ) {
			update_option( WCDN_SLUG . '_v7_templates_snapshot', $templates, false );
		}
	}
}
