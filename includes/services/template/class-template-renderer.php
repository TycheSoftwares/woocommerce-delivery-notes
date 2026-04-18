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

		return '<html><head><meta charset="UTF-8">' . $css . '</head><body>' . $html . '</body></html>';
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

		$cjk_font_name = '';

		if ( 'pdf' === $context ) {
			// Locale font CSS must come first: @import rules are only valid before @font-face.
			$locale_font   = self::get_locale_font_face_css();
			$css          .= $locale_font['css'];
			$css          .= self::get_inter_font_face_css();
			$cjk_font_name = $locale_font['name'];
		}

		$css .= self::import_css( get_stylesheet_directory() . '/' . self::TEMPLATE_OVERRIDE_PATH . 'css/style.css', self::TEMPLATE_PATH . 'css/style.css' );

		$settings = $data['settings'] ?? array();
		$css     .= Template_Style::generate( $settings, $context );

		// Context stylesheet loaded last; corrects px → pt for PDF via dompdf.
		$css .= self::import_css( get_stylesheet_directory() . '/' . self::TEMPLATE_OVERRIDE_PATH . $subdir . 'style.css', self::TEMPLATE_PATH . $subdir . 'style.css' );

		// Inject CJK font as fallback in the font stack so dompdf picks it up for every element.
		if ( $cjk_font_name ) {
			$escaped = esc_attr( $cjk_font_name );
			$css    .= 'body,td,th,.wcdn-title,.wcdn-shop-name,.wcdn-shop-address,.wcdn-shop-phone,.wcdn-shop-email,'
				. '.wcdn-billing-address,.wcdn-shipping-address,.wcdn-policies,.wcdn-complimentary-close,'
				. '.wcdn-customer-note,.wcdn-footer{'
				. "font-family:Inter,'{$escaped}',DejaVuSans,sans-serif;}";
		}

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
	 * Build @font-face declarations for Inter from plugin-bundled TTF files.
	 *
	 * @return string
	 * @since 7.0
	 */
	protected static function get_inter_font_face_css() {

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
	 * Build @font-face CSS for non-Latin locales and return the registered font name.
	 *
	 * Covers scripts not included in Inter or DejaVu: CJK, Arabic, Hebrew,
	 * Devanagari, Thai, Bengali, Tamil, and many more.
	 *
	 * Cyrillic, Greek, and Latin-extended scripts (most European languages) are
	 * already covered by the DejaVu fallback and need no extra font.
	 *
	 * Resolution order:
	 *   1. Local file path via wcdn_pdf_locale_font_path filter.
	 *   2. Remote TTF/OTF URL via wcdn_pdf_locale_font_url filter.
	 *   3. Google Fonts @import (requires isRemoteEnabled; disable via wcdn_pdf_use_google_fonts_for_locale).
	 *
	 * @return array { css: string, name: string }
	 * @since 7.0
	 */
	protected static function get_locale_font_face_css() {

		$empty = array(
			'css'  => '',
			'name' => '',
		);

		$locale = get_locale();

		/*
		 * Locale prefix → font config map.
		 * More-specific prefixes must come before shorter ones (e.g. zh_CN before zh).
		 * Values: 'name' = CSS font-family identifier; 'google' = Google Fonts family param.
		 *
		 * Omitted scripts (covered by DejaVu Sans bundled with dompdf):
		 *   Latin Extended, Cyrillic, Greek, Armenian-basic.
		 */
		$locale_font_map = array(

			// Chinese — Simplified.
			'zh_CN' => array( 'name' => 'NotoSansSC', 'google' => 'Noto Sans SC' ),
			'zh_SG' => array( 'name' => 'NotoSansSC', 'google' => 'Noto Sans SC' ),

			// Chinese — Traditional.
			'zh_TW' => array( 'name' => 'NotoSansTC', 'google' => 'Noto Sans TC' ),
			'zh_HK' => array( 'name' => 'NotoSansHK', 'google' => 'Noto Sans HK' ),

			// Japanese.
			'ja'    => array( 'name' => 'NotoSansJP', 'google' => 'Noto Sans JP' ),

			// Korean.
			'ko_KR' => array( 'name' => 'NotoSansKR', 'google' => 'Noto Sans KR' ),
			'ko'    => array( 'name' => 'NotoSansKR', 'google' => 'Noto Sans KR' ),

			// Arabic script (Arabic, Persian/Farsi, Urdu, Pashto, Kurdish Sorani).
			'ar'    => array( 'name' => 'NotoNaskhArabic', 'google' => 'Noto Naskh Arabic' ),
			'fa'    => array( 'name' => 'NotoNaskhArabic', 'google' => 'Noto Naskh Arabic' ),
			'ur'    => array( 'name' => 'NotoNaskhArabic', 'google' => 'Noto Naskh Arabic' ),
			'ps'    => array( 'name' => 'NotoNaskhArabic', 'google' => 'Noto Naskh Arabic' ),
			'ckb'   => array( 'name' => 'NotoNaskhArabic', 'google' => 'Noto Naskh Arabic' ),

			// Hebrew.
			'he_IL' => array( 'name' => 'NotoSansHebrew', 'google' => 'Noto Sans Hebrew' ),
			'he'    => array( 'name' => 'NotoSansHebrew', 'google' => 'Noto Sans Hebrew' ),

			// Devanagari (Hindi, Marathi, Nepali, Sanskrit, Bhojpuri).
			'hi_IN' => array( 'name' => 'NotoSansDevanagari', 'google' => 'Noto Sans Devanagari' ),
			'hi'    => array( 'name' => 'NotoSansDevanagari', 'google' => 'Noto Sans Devanagari' ),
			'mr'    => array( 'name' => 'NotoSansDevanagari', 'google' => 'Noto Sans Devanagari' ),
			'ne_NP' => array( 'name' => 'NotoSansDevanagari', 'google' => 'Noto Sans Devanagari' ),
			'ne'    => array( 'name' => 'NotoSansDevanagari', 'google' => 'Noto Sans Devanagari' ),
			'sa_IN' => array( 'name' => 'NotoSansDevanagari', 'google' => 'Noto Sans Devanagari' ),
			'bho'   => array( 'name' => 'NotoSansDevanagari', 'google' => 'Noto Sans Devanagari' ),

			// Bengali.
			'bn_BD' => array( 'name' => 'NotoSansBengali', 'google' => 'Noto Sans Bengali' ),
			'bn_IN' => array( 'name' => 'NotoSansBengali', 'google' => 'Noto Sans Bengali' ),
			'bn'    => array( 'name' => 'NotoSansBengali', 'google' => 'Noto Sans Bengali' ),

			// Tamil.
			'ta_IN' => array( 'name' => 'NotoSansTamil', 'google' => 'Noto Sans Tamil' ),
			'ta_LK' => array( 'name' => 'NotoSansTamil', 'google' => 'Noto Sans Tamil' ),
			'ta'    => array( 'name' => 'NotoSansTamil', 'google' => 'Noto Sans Tamil' ),

			// Telugu.
			'te'    => array( 'name' => 'NotoSansTelugu', 'google' => 'Noto Sans Telugu' ),

			// Kannada.
			'kn'    => array( 'name' => 'NotoSansKannada', 'google' => 'Noto Sans Kannada' ),

			// Malayalam.
			'ml_IN' => array( 'name' => 'NotoSansMalayalam', 'google' => 'Noto Sans Malayalam' ),
			'ml'    => array( 'name' => 'NotoSansMalayalam', 'google' => 'Noto Sans Malayalam' ),

			// Gujarati.
			'gu'    => array( 'name' => 'NotoSansGujarati', 'google' => 'Noto Sans Gujarati' ),

			// Punjabi / Gurmukhi.
			'pa_IN' => array( 'name' => 'NotoSansGurmukhi', 'google' => 'Noto Sans Gurmukhi' ),

			// Sinhala.
			'si_LK' => array( 'name' => 'NotoSansSinhala', 'google' => 'Noto Sans Sinhala' ),
			'si'    => array( 'name' => 'NotoSansSinhala', 'google' => 'Noto Sans Sinhala' ),

			// Thai.
			'th'    => array( 'name' => 'NotoSansThai', 'google' => 'Noto Sans Thai' ),

			// Khmer.
			'km'    => array( 'name' => 'NotoSansKhmer', 'google' => 'Noto Sans Khmer' ),

			// Myanmar / Burmese.
			'my_MM' => array( 'name' => 'NotoSansMyanmar', 'google' => 'Noto Sans Myanmar' ),
			'my'    => array( 'name' => 'NotoSansMyanmar', 'google' => 'Noto Sans Myanmar' ),

			// Lao.
			'lo'    => array( 'name' => 'NotoSansLao', 'google' => 'Noto Sans Lao' ),

			// Tibetan.
			'bo'    => array( 'name' => 'NotoSerifTibetan', 'google' => 'Noto Serif Tibetan' ),

			// Georgian.
			'ka_GE' => array( 'name' => 'NotoSansGeorgian', 'google' => 'Noto Sans Georgian' ),
			'ka'    => array( 'name' => 'NotoSansGeorgian', 'google' => 'Noto Sans Georgian' ),

			// Armenian.
			'hy'    => array( 'name' => 'NotoSansArmenian', 'google' => 'Noto Sans Armenian' ),

			// Amharic / Ethiopic.
			'am'    => array( 'name' => 'NotoSansEthiopic', 'google' => 'Noto Sans Ethiopic' ),
		);

		$config = null;
		foreach ( $locale_font_map as $prefix => $data ) {
			if ( 0 === strpos( $locale, $prefix ) ) {
				$config = $data;
				break;
			}
		}

		/**
		 * Filter the locale font config for the current locale.
		 * Return null to disable non-Latin font loading entirely.
		 *
		 * @param array|null $config { name: string, google: string }|null
		 * @param string     $locale Current WordPress locale.
		 * @since 7.0
		 */
		$config = apply_filters( 'wcdn_pdf_locale_font_config', $config, $locale );

		if ( ! $config ) {
			return $empty;
		}

		$font_name = $config['name'];

		// 1. Local font file (fastest — base64-embedded into the PDF CSS).
		$local_path = apply_filters( 'wcdn_pdf_locale_font_path', '', $locale );
		if ( $local_path && file_exists( $local_path ) ) {
			$ext  = strtolower( pathinfo( $local_path, PATHINFO_EXTENSION ) );
			$mime = 'otf' === $ext ? 'opentype' : 'truetype';
			$b64  = base64_encode( file_get_contents( $local_path ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$css  = '@font-face{'
				. "font-family:'{$font_name}';"
				. "src:url('data:font/{$mime};base64,{$b64}') format('{$mime}');"
				. 'font-weight:400;font-style:normal;}';
			return array(
				'css'  => $css,
				'name' => $font_name,
			);
		}

		// 2. Remote TTF/OTF URL (served directly — dompdf fetches and caches the file).
		$remote_url = apply_filters( 'wcdn_pdf_locale_font_url', '', $locale );
		if ( $remote_url ) {
			$css = '@font-face{'
				. "font-family:'{$font_name}';"
				. "src:url('" . esc_url_raw( $remote_url ) . "') format('truetype');"
				. 'font-weight:400;font-style:normal;}';
			return array(
				'css'  => $css,
				'name' => $font_name,
			);
		}

		/*
		 * 3. Google Fonts @import — dompdf fetches the CSS and then the font file.
		 *    Requires isRemoteEnabled=true (already set) and outbound HTTP from the server.
		 *    Disable with: add_filter( 'wcdn_pdf_use_google_fonts_for_locale', '__return_false' );
		 */
		if ( apply_filters( 'wcdn_pdf_use_google_fonts_for_locale', true ) ) {
			$google_url = 'https://fonts.googleapis.com/css?family=' . rawurlencode( $config['google'] );
			$css        = "@import url('{$google_url}');";
			return array(
				'css'  => $css,
				'name' => $font_name,
			);
		}

		return $empty;
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
