<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Configuration Helper Base Class.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Helpers
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Configuration Helper Base Class.
 *
 * @since 7.0
 */
abstract class Helper {

	/**
	 * Cached settings.
	 *
	 * @var array|null
	 * @since 7.0
	 */
	protected static $settings = null;

	/**
	 * Raw settings.
	 *
	 * @var array|null
	 * @since 7.0
	 */
	protected static $raw_settings = null;

	/**
	 * Get the option key used to retrieve settings.
	 *
	 * @return string
	 * @since 7.0
	 */
	abstract protected static function option_key();

	/**
	 * Get default configuration values.
	 *
	 * @return array
	 * @since 7.0
	 */
	protected static function defaults() {
		return array();
	}

	/**
	 * Retrieve all settings merged with defaults.
	 *
	 * @return array
	 * @since 7.0
	 */
	public static function all() {

		if ( null !== static::$settings ) {
			return static::$settings;
		}

		$saved = get_option( static::option_key(), array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		static::$raw_settings = $saved;

		$defaults         = static::defaults();
		static::$settings = wp_parse_args( $saved, $defaults );

		return static::$settings;
	}

	/**
	 * Retrieve configuration value.
	 *
	 * Optionally applies legacy filter mapping.
	 *
	 * @param string|null $key               Configuration key.
	 * @param mixed       $_default          Default value if key is not found.
	 * @param array|null  $data              Data source array.
	 * @param bool        $do_filter_mapping Whether to apply legacy filter mapping.
	 * @return mixed
	 *
	 * @since 7.0
	 */
	public static function _get( $key = null, $_default = null, $data = null, $do_filter_mapping = true ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore

		if ( ! $data ) {
			$data = static::all();
		}

		if ( ! $key ) {
			return $data;
		}

		if ( ! isset( $data[ $key ] ) ) {
			return $_default;
		}

		$value = $data[ $key ];

		if ( $do_filter_mapping ) {
			// Apply legacy filter mapping.
			$value = static::resolve_legacy_filter( $value, $key );
		}

		return $value;
	}


	/**
	 * Resolve legacy filter mappings for a setting or template value.
	 *
	 * Applies backward compatibility filters when a value
	 * has not been explicitly saved in the database.
	 *
	 * @param mixed  $value   Current value.
	 * @param string $key     Configuration key.
	 * @param array  $context Optional context data.
	 *                        Supported keys:
	 *                        - type (string)     Context type (settings|template).
	 *                        - template (string) Template identifier.
	 * @return mixed
	 * @since 7.0
	 */
	protected static function resolve_legacy_filter( $value, $key, $context = array() ) {

		$map = static::legacy_filter_map();

		$type     = $context['type'] ?? 'settings';
		$template = $context['template'] ?? null;

		/*
		* 1. TEMPLATE: Scoped mapping ONLY.
		*/
		if ( 'template' === $type && $template ) {

			if (
			isset( $map[ $template ] ) &&
			is_array( $map[ $template ] ) &&
			isset( $map[ $template ][ $key ] )
			) {

				$filter = $map[ $template ][ $key ];

				if ( has_filter( $filter ) ) {
					return apply_filters( $filter, $value );
				}
			}

			// 🚫 No global fallback for templates.
			return $value;
		}

		/*
		* 2. SETTINGS: Global mapping — always apply if hooked.
		*/
		if ( isset( $map[ $key ] ) && is_string( $map[ $key ] ) ) {

			$filter = $map[ $key ];

			if ( has_filter( $filter ) ) {
				return apply_filters( $filter, $value );
			}
		}

		return $value;
	}

	/**
	 * Determine whether a configuration key is explicitly saved.
	 *
	 * Checks raw stored settings before defaults or
	 * legacy filters are applied.
	 *
	 * @param string $key     Configuration key.
	 * @param array  $context Optional context data.
	 *                        Supported keys:
	 *                        - type (string)     Context type (settings|template).
	 *                        - template (string) Template identifier.
	 * @return bool
	 * @since 7.0
	 */
	protected static function is_key_saved( $key, $context = array() ) {

		if ( ! is_array( static::$raw_settings ) ) {
			return false;
		}

		$type     = $context['type'] ?? 'settings';
		$template = $context['template'] ?? null;

		if ( 'template' === $type && $template ) {
			return isset( static::$raw_settings[ $template ][ $key ] );
		}

		return array_key_exists( $key, static::$raw_settings );
	}

	/**
	 * Legacy filters.
	 *
	 * This maps the filter hooks to the settings key.
	 *
	 * @return array
	 * @since 7.0
	 */
	public static function legacy_filter_map() {

		return array(
			'invoiceButtonLabel'       => 'wcdn_change_text_of_print_invoice_in_bulk_option',
			'deliveryNoteButtonLabel'  => 'wcdn_change_text_of_print_delivery_note_in_bulk_option',
			'receiptButtonLabel'       => 'wcdn_change_text_of_print_receipt_in_bulk_option',
			'myAccountPageButtonLabel' => 'wcdn_print_button_name_on_my_account_page',
			'viewOrderButtonLabel'     => 'wcdn_print_button_name_order_page',
			'defaultDocumentLabel'     => 'wcdn_document_title',
		);
	}
}
