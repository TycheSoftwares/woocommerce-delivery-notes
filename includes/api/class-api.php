<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * REST API for Admin.
 *
 * Will be used to fetch data that will be passed to the admin interface.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Admin/API
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Api;

defined( 'ABSPATH' ) || exit;

/**
 * REST API.
 *
 * @since 7.0
 */
class Api extends \Tyche\WCDN\Admin {

	/**
	 * REST Base Endpoint.
	 *
	 * @var string
	 */
	public static $base_endpoint = 'wcdn/admin/v1';

	/**
	 * Returns the REST API Endpoint.
	 *
	 * @since 7.0
	 */
	public static function endpoint() {
		return self::$base_endpoint;
	}

	/**
	 * Returns the REST API response.
	 *
	 * @param string $status Status of response.
	 * @param string $data Data.
	 *
	 * @since 7.0
	 */
	public static function response( $status, $data ) {
		return self::return_response(
			array(
				'status' => $status,
				'data'   => $data,
			)
		);
	}

	/**
	 * Returns the REST API response.
	 *
	 * @param string|array $response Response data.
	 * @return WP_REST_Response
	 * @since 7.0
	 */
	public static function return_response( $response ) {
		return rest_ensure_response( $response );
	}

	/**
	 * Returns a success message.
	 *
	 * @since 7.0
	 */
	public static function success() {
		return self::return_response( 'success' );
	}

	/**
	 * Returns an error message.
	 *
	 * @since 7.0
	 */
	public static function error() {
		return self::return_response( 'error' );
	}

	/**
	 * Verify nonce.
	 *
	 * @param WP_REST_Request $request Request.
	 * @param bool            $stop_execution TRUE - stops execution, FALSE - return status of nonce verification.
	 *
	 * @since 7.0
	 */
	public static function verify_nonce( $request, $stop_execution = true ) {
		if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {

			if ( $stop_execution ) {
				wp_die( self::error() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON REST error response
			}

			return false;
		}

		return true;
	}

	/**
	 * Returns a value if the target value is empty.
	 *
	 * @param string $value Target Value.
	 * @param string $return_value_if_empty Value to be returned if target value is empty.
	 *
	 * @since 7.0
	 */
	public static function return_value_if_empty( $value, $return_value_if_empty ) {
		return '' === $value ? $return_value_if_empty : $value;
	}

	/**
	 * Sanitize data.
	 *
	 * @param array $data Data.
	 * @param array $schema Schema of data to be sanitized.
	 * @param array $defaults Default data.
	 *
	 * @since 7.0
	 */
	public static function sanitize( $data, $schema, $defaults ) {

		$sanitized = array();

		foreach ( $schema as $field => $type ) {

			if ( 'meta' === $field ) {
				continue;
			}

			$value = $data[ $field ] ??
				$defaults[ $field ] ??
				null;

			$sanitized[ $field ] =
			self::sanitize_field(
				$value,
				$type
			);
		}

		return $sanitized;
	}

	/**
	 * Permissions
	 *
	 * @since 7.0
	 */
	public static function permissions() {
		return current_user_can( 'manage_woocommerce' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown
	}

	/**
	 * Sanitize Individual Field.
	 *
	 * @param mixed  $value Field value.
	 * @param string $type  Field type.
	 *
	 * @since 7.0
	 */
	public static function sanitize_field( $value, $type ) {

		switch ( $type ) {

			case 'text':
				return sanitize_text_field( $value );

			case 'textarea':
				return sanitize_textarea_field( $value );

			case 'email':
				return sanitize_email( $value );

			case 'slug':
				return sanitize_title( $value );

			case 'url':
				return esc_url_raw( $value );

			case 'bool':
				return ! empty( $value );

			case 'number':
				return intval( $value );

			case 'float':
				return floatval( $value );

			case 'array':
				return is_array( $value )
				? $value
				: array();

			default:
				return sanitize_text_field( $value );

		}
	}
}
