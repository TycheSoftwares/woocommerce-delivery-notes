<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Template Renderer Service Class.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Services
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Services;

use Tyche\WCDN\Helpers\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Template Renderer Service Class.
 *
 * Handles template location and rendering
 * with support for theme overrides.
 *
 * @since 7.0
 */
class Template_Renderer {

	/**
	 * Base template path inside plugin.
	 *
	 * @var string
	 * @since 7.0
	 */
	const TEMPLATE_PATH = WCDN_PLUGIN_PATH . '/templates/';

	/**
	 * Theme override base path.
	 *
	 * @var string
	 * @since 7.0
	 */
	const TEMPLATE_OVERRIDE_PATH = 'woocommerce-delivery-notes/';

	/**
	 * Render template and return output as string.
	 *
	 * @param string $template Template name (without extension).
	 * @param array  $data     Template data.
	 * @param string $type     Template type (pdf, html, etc.).
	 * @return string
	 * @since 7.0
	 */
	public static function render( $template, $data = array(), $type = 'pdf' ) {

		add_filter( 'woocommerce_get_order_item_totals', 'wcdn_remove_semicolon_from_totals', 10, 2 );
		add_filter( 'woocommerce_get_order_item_totals', 'wcdn_remove_payment_method_from_totals', 20, 2 );
		add_filter( 'woocommerce_get_order_item_totals', 'wcdn_add_refunded_order_totals', 30, 2 );

		$template_file = self::locate( $template, $type );

		if ( ! $template_file ) {
			return '';
		}

		/**
		 * Filter template data.
		 */
		$data = apply_filters( 'wcdn_template_data', $data, $template, $type );

		ob_start();

		self::extract_data( $data );

		include $template_file;

		$html = ob_get_clean();
		$css  = self::get_css( $type, $data );

		return '<html><head>' . $css . '</head><body>' . $html . '</body></html>';
	}

	/**
	 * Get template CSS.
	 *
	 * @param string $context Rendering context (pdf, html, etc.).
	 * @param array  $data    Template data containing settings.
	 * @return string
	 * @since 7.0
	 */
	protected static function get_css( $context = 'pdf', $data = array() ) {

		$css    = '';
		$subdir = trailingslashit( "css/{$context}" );

		if ( 'pdf' === $context ) {
			$css .= self::get_font_face_css();
		}

		$css .= self::import_css( get_stylesheet_directory() . '/' . self::TEMPLATE_OVERRIDE_PATH . 'css/style.css', self::TEMPLATE_PATH . 'css/style.css' );

		$settings = $data['settings'] ?? array();
		$css     .= Template_Style::generate( $settings, $context );

		// Context stylesheet loaded last; corrects px → pt for PDF via dompdf.
		$css .= self::import_css( get_stylesheet_directory() . '/' . self::TEMPLATE_OVERRIDE_PATH . $subdir . 'style.css', self::TEMPLATE_PATH . $subdir . 'style.css' );

		$css = apply_filters( 'wcdn_template_css', $css, $context, $data );

		return '<style>' . $css . '</style>';
	}

	/**
	 * Locate template file.
	 *
	 * Checks for theme override before falling back to plugin template.
	 *
	 * @param string $template Template name (without extension).
	 * @param string $type     Template type (pdf, html, etc.).
	 * @return string|false
	 * @since 7.0
	 */
	public static function locate( $template, $type = 'pdf' ) {

		$template = sanitize_file_name( $template );
		$filename = "{$template}.php";

		$theme_path = get_stylesheet_directory() . '/' . self::TEMPLATE_OVERRIDE_PATH . $filename;

		if ( file_exists( $theme_path ) ) {
			return apply_filters( 'wcdn_locate_template', $theme_path, $template, $type, 'theme' );
		}

		$plugin_path = self::TEMPLATE_PATH . $filename;

		if ( file_exists( $plugin_path ) ) {
			return apply_filters( 'wcdn_locate_template', $plugin_path, $template, $type, 'plugin' );
		}

		return apply_filters( 'wcdn_locate_template', false, $template, $type, 'not_found' );
	}

	/**
	 * Extract data into local scope for template usage.
	 *
	 * @param array $data Template data.
	 * @return void
	 * @since 7.0
	 */
	protected static function extract_data( $data ) {

		if ( ! is_array( $data ) ) {
			return;
		}

		foreach ( $data as $key => $value ) {
			${$key} = $value;
		}
	}

	/**
	 * Build @font-face declarations for the PDF context. Loads Inter from plugin-bundled TTF files when present.
	 *
	 * @return string
	 * @since 7.0
	 */
	protected static function get_font_face_css() {

		$font_dir = WCDN_PLUGIN_PATH . '/assets/fonts/inter/';

		$weights = array(
			400 => 'Inter-Regular.ttf',
			500 => 'Inter-Medium.ttf',
			600 => 'Inter-SemiBold.ttf',
			700 => 'Inter-Bold.ttf',
		);

		$css = '';

		foreach ( $weights as $weight => $filename ) {
			$path = $font_dir . $filename;

			if ( ! file_exists( $path ) ) {
				continue;
			}

			$data = base64_encode( file_get_contents( $path ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			$css .= '@font-face {'
				. "font-family:'Inter';"
				. "src:url('data:font/truetype;base64,{$data}') format('truetype');"
				. "font-weight:{$weight};"
				. 'font-style:normal;'
				. '}';
		}

		return $css;
	}

	/**
	 * Read CSS file contents, falling back to a secondary path if the primary doesn't exist.
	 *
	 * @param string $css_url          Absolute path to the primary CSS file.
	 * @param string $fallback_css_url Absolute path to the fallback CSS file.
	 * @return string
	 * @since 7.0
	 */
	protected static function import_css( $css_url, $fallback_css_url ) {

		$filesystem = Utils::get_filesystem();

		if ( ! $filesystem ) {
			return '';
		}

		if ( $filesystem->exists( $css_url ) ) {
			return $filesystem->get_contents( $css_url );
		}

		if ( $filesystem->exists( $fallback_css_url ) ) {
			return $filesystem->get_contents( $fallback_css_url );
		}

		return '';
	}
}
