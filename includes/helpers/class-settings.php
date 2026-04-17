<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Settings Helper Class.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Helpers
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Settings Helper Class.
 *
 * @since 7.0
 */
class Settings extends Helper {

	/**
	 * Get the option key used to retrieve settings.
	 *
	 * @return string
	 * @since 7.0
	 */
	protected static function option_key() {
		return \Tyche\WCDN\Api\Settings::OPTION_KEY;
	}

	/**
	 * Get default configuration values.
	 *
	 * @return array
	 * @since 7.0
	 */
	protected static function defaults() {
		return \Tyche\WCDN\Api\Settings::default_settings();
	}

	/**
	 * Retrieve a configuration value with legacy filter support.
	 *
	 * Applies backward compatibility filters for settings
	 * when a value has not been explicitly saved.
	 *
	 * @param string|null $key      Configuration key.
	 * @param mixed       $_default Default value if key is not found.
	 * @return mixed
	 * @since 7.0
	 */
	public static function get( $key = null, $_default = null ) {

		$value = parent::_get( $key, $_default, null, false );

		return static::resolve_legacy_filter(
			$value,
			$key,
			array( 'type' => 'settings' )
		);
	}
}
