<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Template Settings Helper Class.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Helpers
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Helpers;

use Tyche\WCDN\Services\Template_Engine;

defined( 'ABSPATH' ) || exit;

/**
 * Template Settings Helper Class.
 *
 * @since 7.0
 */
class Templates extends Helper {

	/**
	 * Cached Template settings.
	 *
	 * @var array|null
	 * @since 7.0
	 */
	protected static $template_settings = null;

	/**
	 * Get the option key used to retrieve template settings.
	 *
	 * @return string
	 * @since 7.0
	 */
	protected static function option_key() {
		return \Tyche\WCDN\Api\Templates::OPTION_KEY;
	}

	/**
	 * Return merged template settings with defaults.
	 *
	 * @return array
	 * @since 7.0
	 */
	public static function all() {

		if ( null !== static::$template_settings ) {
			return static::$template_settings;
		}

		$saved = get_option( static::option_key(), array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$template_keys = Template_Engine::get_template_keys();

		$settings = array();

		foreach ( $template_keys as $template_key ) {

			$structure = Template_Engine::get_structure( $template_key );

			if ( empty( $structure ) ) {
				continue;
			}

			$defaults = Template_Engine::build_defaults(
				$template_key,
				$structure
			);

			// Merge saved template settings over defaults.
			$settings[ $template_key ] = isset( $saved[ $template_key ] )
				? wp_parse_args( $saved[ $template_key ], $defaults )
				: $defaults;
		}

		static::$template_settings = $settings;

		return static::$template_settings;
	}

	/**
	 * Get full configuration for a specific template.
	 *
	 * @param string $template Template key.
	 * @return array
	 * @since 7.0
	 */
	public static function template( $template ) {
		$settings = static::all();
		return $settings[ $template ] ?? array();
	}

	/**
	 * Get a specific template setting using dot notation.
	 *
	 * @param string      $template Template key.
	 * @param string|null $key      Setting key using dot notation.
	 * @param mixed       $_default  Default value if key is not found.
	 * @return mixed
	 * @since 7.0
	 */
	public static function get( $template, $key, $_default = null ) {
		$data  = static::template( $template );
		$value = parent::_get( $key, $_default, $data, false );

		return static::resolve_legacy_filter(
			$value,
			$key,
			array(
				'type'     => 'template',
				'template' => $template,
			)
		);
	}
}
