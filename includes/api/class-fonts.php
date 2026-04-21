<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * REST API for Font Management.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Admin/API/Fonts
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Api;

use WP_REST_Request;
use Tyche\WCDN\Services\Template_Renderer;

defined( 'ABSPATH' ) || exit;

/**
 * Fonts REST API.
 *
 * @since 7.0
 */
class Fonts extends \Tyche\WCDN\Api\Api {

	/**
	 * Constructor.
	 *
	 * @since 7.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 *
	 * @since 7.0
	 */
	public static function register_routes() {

		register_rest_route(
			'wcdn/v1',
			'/fonts',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_status' ),
					'permission_callback' => array( __CLASS__, 'permissions' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'upload_font' ),
					'permission_callback' => array( __CLASS__, 'permissions' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_font' ),
					'permission_callback' => array( __CLASS__, 'permissions' ),
				),
			)
		);
	}

	/**
	 * Return font status for the current locale.
	 *
	 * @return \WP_REST_Response
	 * @since 7.0
	 */
	public static function get_status() {
		return self::response( 'success', Template_Renderer::get_font_admin_status() );
	}

	/**
	 * Upload a font file for the current locale.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 * @since 7.0
	 */
	public static function upload_font( WP_REST_Request $request ) {

		$files  = $request->get_file_params();
		$weight = sanitize_key( $request->get_param( 'weight' ) ?: 'regular' );

		if ( 'bold' !== $weight ) {
			$weight = 'regular';
		}

		if ( empty( $files['font_file'] ) || UPLOAD_ERR_OK !== (int) $files['font_file']['error'] ) {
			return new \WP_Error( 'no_file', __( 'No valid font file provided.', 'woocommerce-delivery-notes' ), array( 'status' => 400 ) );
		}

		$file     = $files['font_file'];
		$tmp_path = $file['tmp_name'];

		if ( filesize( $tmp_path ) < 40960 ) {
			return new \WP_Error( 'font_too_small', __( 'Font file is too small. Please upload a complete font file.', 'woocommerce-delivery-notes' ), array( 'status' => 400 ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$magic            = (string) file_get_contents( $tmp_path, false, null, 0, 4 );
		$valid_signatures = array( "\x00\x01\x00\x00", 'true', 'OTTO', 'typ1' );

		if ( ! in_array( $magic, $valid_signatures, true ) ) {
			return new \WP_Error( 'invalid_font', __( 'Invalid font file. Please upload a TTF or OTF file.', 'woocommerce-delivery-notes' ), array( 'status' => 400 ) );
		}

		$config = Template_Renderer::get_locale_config();

		if ( ! $config ) {
			return new \WP_Error( 'no_locale_font', __( 'No font is required for the current site language.', 'woocommerce-delivery-notes' ), array( 'status' => 400 ) );
		}

		$font_name = $config['name'];
		$ext       = 'otf' === strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) ) ? '.otf' : '.ttf';
		$suffix    = 'bold' === $weight ? '-Bold' : '-Regular';
		$filename  = sanitize_file_name( $font_name ) . $suffix . $ext;

		$font_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'wcdn/fonts/';
		wp_mkdir_p( $font_dir );

		$target = $font_dir . $filename;

		foreach ( array( '_v5.ttf', '_v5.otf', '_v5_bold.ttf', '_v5_bold.otf' ) as $stale_suffix ) {
			$stale = $font_dir . sanitize_file_name( $font_name ) . $stale_suffix;
			if ( file_exists( $stale ) ) {
				wp_delete_file( $stale );
			}
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
		if ( ! move_uploaded_file( $tmp_path, $target ) ) {
			return new \WP_Error( 'upload_failed', __( 'Failed to save the font file.', 'woocommerce-delivery-notes' ), array( 'status' => 500 ) );
		}

		Template_Renderer::clear_locale_font_cache();

		return self::response(
			'success',
			array(
				'message'   => __( 'Font uploaded successfully.', 'woocommerce-delivery-notes' ),
				'file_name' => $filename,
				'file_size' => filesize( $target ),
				'status'    => Template_Renderer::get_font_admin_status(),
			)
		);
	}

	/**
	 * Delete a font file for the current locale.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 * @since 7.0
	 */
	public static function delete_font( WP_REST_Request $request ) {

		$weight = sanitize_key( $request->get_param( 'weight' ) ?: 'regular' );

		if ( 'bold' !== $weight ) {
			$weight = 'regular';
		}

		$config = Template_Renderer::get_locale_config();

		if ( ! $config ) {
			return new \WP_Error( 'no_locale_font', __( 'No font is configured for the current site language.', 'woocommerce-delivery-notes' ), array( 'status' => 400 ) );
		}

		$font_name = $config['name'];
		$font_dir  = trailingslashit( wp_upload_dir()['basedir'] ) . 'wcdn/fonts/';

		if ( 'bold' === $weight ) {
			$candidates = array( '-Bold.ttf', '-Bold.otf', '_v5_bold.ttf', '_v5_bold.otf' );
		} else {
			$candidates = array( '-Regular.ttf', '-Regular.otf', '_v5.ttf', '_v5.otf' );
		}

		$deleted = false;

		foreach ( $candidates as $suffix ) {
			$path = $font_dir . sanitize_file_name( $font_name ) . $suffix;
			if ( file_exists( $path ) ) {
				wp_delete_file( $path );
				$deleted = true;
			}
		}

		if ( ! $deleted ) {
			return new \WP_Error( 'no_font_file', __( 'No font file found to delete.', 'woocommerce-delivery-notes' ), array( 'status' => 404 ) );
		}

		Template_Renderer::clear_locale_font_cache();

		return self::response(
			'success',
			array(
				'message' => __( 'Font deleted successfully.', 'woocommerce-delivery-notes' ),
				'status'  => Template_Renderer::get_font_admin_status(),
			)
		);
	}
}
