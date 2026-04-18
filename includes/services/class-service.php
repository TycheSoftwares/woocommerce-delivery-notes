<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Service Locator Class.
 *
 * @author      Tyche Softwares
 * @package     WCDN
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN;

use Tyche\WCDN\Services\Pdf;

defined( 'ABSPATH' ) || exit;

/**
 * Service Locator Class.
 *
 * Provides access to registered plugin services.
 *
 * @since 7.0
 */
class Service {

	/**
	 * Retrieve a service instance.
	 *
	 * @param string $service Service identifier.
	 * @return object|null
	 * @since 7.0
	 */
	public static function get( $service ) {
		$instance = WCDN()->service( $service );

		if ( ! $instance ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf( 'Service "%s" not found.', esc_html( $service ) ),
				'7.0'
			);
		}

		return $instance;
	}

	/**
	 * Frontend service.
	 *
	 * @return object|null
	 * @since 7.0
	 */
	public static function frontend() {
		return self::get( 'frontend' );
	}

	/**
	 * Retrieve backend service.
	 *
	 * @return object|null
	 * @since 7.0
	 */
	public static function backend() {
		return self::get( 'backend' );
	}

	/**
	 * PDF service.
	 *
	 * @return object|null
	 * @since 7.0
	 */
	public static function pdf() {
		return self::get( 'pdf' );
	}

	/**
	 * Template Renderer service.
	 *
	 * @return object|null
	 * @since 7.0
	 */
	public static function renderer() {
		return self::get( 'renderer' );
	}
}
