<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Class for including files for the Admin.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Admin/Files
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN;

defined( 'ABSPATH' ) || exit;

/**
 * WCDN Admin Files.
 *
 * @since 7.0
 */
class Files {

	/**
	 * Load a third-party integration once its detection class is available.
	 *
	 * Defers to plugins_loaded priority 20 so third-party plugins that
	 * register their classes at the default priority (10) are already loaded.
	 *
	 * @param string $detect_class Fully-qualified class name used to detect the plugin.
	 * @param string $file         Path relative to the includes directory.
	 * @param string $fqcn         Fully-qualified class name of the integration to instantiate.
	 * @since 7.1.2
	 */
	private static function load_integration( string $detect_class, string $file, string $fqcn ): void {
		$load = static function () use ( $detect_class, $file, $fqcn ) {
			if ( class_exists( $detect_class ) ) {
				WCDN()::include_file( $file );
				new $fqcn();
			}
		};

		if ( did_action( 'plugins_loaded' ) ) {
			$load();
		} else {
			add_action( 'plugins_loaded', $load, 20 );
		}
	}

	/**
	 * Include files.
	 *
	 * @since 7.0
	 */
	public static function include() {

		$autoload = WCDN_PLUGIN_PATH . '/vendor/autoload.php';

		if ( ! class_exists( \Dompdf\Dompdf::class ) && file_exists( $autoload ) ) {
			require_once $autoload;
		}

		// General non-plugin functions.
		WCDN()::include_file( 'functions/functions.php' );

		// Template related Functions.
		WCDN()::include_file( 'functions/template-functions.php' );

		// Notices.
		WCDN()::include_file( 'admin/class-notices.php' );

		// Uninstallation.
		WCDN()::include_file( 'core/class-uninstall.php' );

		// Tyche Admin Components.
		WCDN()::include_file( 'component/class-admin-component.php' );
		new Admin_Component();

		// Menu.
		WCDN()::include_file( 'admin/class-admin.php' );
		WCDN()::include_file( 'admin/class-menu.php' );
		new Menu();

		// API.
		WCDN()::include_file( 'api/class-api.php' );

		WCDN()::include_file( 'api/class-settings.php' );
		new \Tyche\WCDN\Api\Settings();

		WCDN()::include_file( 'api/class-fonts.php' );
		new \Tyche\WCDN\Api\Fonts();

		WCDN()::include_file( 'helpers/class-helper.php' );
		WCDN()::include_file( 'helpers/class-settings.php' );

		WCDN()::include_file( 'helpers/class-utils.php' );

		WCDN()::include_file( 'services/template/class-template-engine.php' );

		WCDN()::include_file( 'api/class-templates.php' );
		new \Tyche\WCDN\Api\Templates();

		WCDN()::include_file( 'api/class-dashboard.php' );
		new \Tyche\WCDN\Api\Dashboard();

		WCDN()::include_file( 'core/class-migration.php' );

		// Installation.
		WCDN()::include_file( 'core/class-install.php' );
		Install::run();

		WCDN()::include_file( 'services/template/class-template-renderer.php' );
		WCDN()::include_file( 'services/template/class-template-style.php' );

		WCDN()::include_file( 'helpers/class-templates.php' );

		WCDN()::include_file( 'services/class-pdf.php' );
		WCDN()::include_file( 'services/class-service.php' );

		WCDN()::include_file( 'integrations/class-emails.php' );
		new \Tyche\WCDN\Integrations\Emails();

		self::load_integration( 'AWCDP_Deposits', 'integrations/class-deposits-partial-payments.php', \Tyche\WCDN\Integrations\Deposits_Partial_Payments::class );
		self::load_integration( 'DFW_Deposits', 'integrations/class-dfw-deposits.php', \Tyche\WCDN\Integrations\DFW_Deposits::class );
		self::load_integration( 'WC_Local_Pickup_Plus_Orders', 'integrations/class-local-pickup-plus.php', \Tyche\WCDN\Integrations\Local_Pickup_Plus::class );
		self::load_integration( 'Coderockz_Woo_Delivery', 'integrations/class-coderockz-woo-delivery.php', \Tyche\WCDN\Integrations\Coderockz_Woo_Delivery::class );

		WCDN()::include_file( 'frontend/class-frontend.php' );
		WCDN()::include_file( 'admin/class-backend.php' );

		// Scripts.
		WCDN()::include_file( 'admin/class-scripts.php' );
		new Scripts();
	}
}
