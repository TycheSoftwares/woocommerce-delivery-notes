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
	 * Per-request cache for resolved locale font info.
	 *
	 * @var array|null { name: string, path: string|false }
	 */
	private static $locale_font_cache = null;

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

		include $template_file; // nosemgrep: audit.php.lang.security.file.inclusion-arg -- path resolved by locate(), which calls sanitize_file_name() and file_exists() before returning.

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
			$locale_font   = self::get_locale_font_face_css();
			$cjk_font_name = $locale_font['name'];
			// Inter and locale fonts are registered with dompdf via registerFont() in
			// Pdf::generate() — no @font-face CSS needed here. Embedding fonts as base64
			// data URIs bloats the HTML to 2+ MB and causes dompdf's CSS/regex parser to
			// run out of memory before processing the font-family declarations.
		}

		$base_css = self::import_css( get_stylesheet_directory() . '/' . self::TEMPLATE_OVERRIDE_PATH . 'css/style.css', self::TEMPLATE_PATH . 'css/style.css' );

		$settings     = $data['settings'] ?? array();
		$settings_css = Template_Style::generate( $settings, $context );

		// Context stylesheet loaded last; corrects px → pt for PDF via dompdf.
		$context_css = self::import_css( get_stylesheet_directory() . '/' . self::TEMPLATE_OVERRIDE_PATH . $subdir . 'style.css', self::TEMPLATE_PATH . $subdir . 'style.css' );

		/*
		 * When a locale font is active, patch every font-family declaration in the
		 * imported stylesheets directly. CSS cascade overrides fail inside dompdf
		 * table cells because dompdf does not reliably apply class+element selectors
		 * to <th>/<td> inside <thead>/<tbody>. Replacing the font stack at the source
		 * is the only approach guaranteed to work for all elements including tables.
		 *
		 * dompdf picks the first available font for an entire element — no per-glyph
		 * fallback. Inter must follow the locale font so non-Latin glyphs are not
		 * rendered as ???. All Noto fonts include full Basic Latin coverage.
		 */
		if ( $cjk_font_name ) {
			$escaped      = esc_attr( $cjk_font_name );
			$replacement  = "'{$escaped}', Inter, DejaVuSans, sans-serif";
			$base_css     = str_replace( 'Inter, DejaVuSans, sans-serif', $replacement, $base_css );
			$settings_css = str_replace( 'Inter, DejaVuSans, sans-serif', $replacement, $settings_css );
			$context_css  = str_replace( 'Inter, DejaVuSans, sans-serif', $replacement, $context_css );
		}

		$css .= $base_css . $settings_css . $context_css;

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
	 * Return font status for the admin UI without triggering a download.
	 *
	 * @return array {
	 *   needed: bool, locale: string, font_name: string, display_name: string,
	 *   google_url: string, regular: array|null, bold: array|null
	 * }
	 * @since 7.0
	 */
	public static function get_font_admin_status() {

		$locale = get_locale();
		$config = self::get_locale_config( $locale );

		if ( ! $config ) {
			return array(
				'needed' => false,
				'locale' => $locale,
			);
		}

		$font_name = $config['name'];
		$font_dir  = trailingslashit( wp_upload_dir()['basedir'] ) . 'wcdn/fonts/';

		$valid_signatures = array( "\x00\x01\x00\x00", 'true', 'OTTO', 'typ1' );

		$find_file = function ( array $suffixes ) use ( $font_dir, $font_name, $valid_signatures ) {
			foreach ( $suffixes as $suffix ) {
				$path = $font_dir . sanitize_file_name( $font_name ) . $suffix;
				if ( file_exists( $path ) && filesize( $path ) > 40960 ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					$magic = (string) file_get_contents( $path, false, null, 0, 4 );
					if ( in_array( $magic, $valid_signatures, true ) ) {
						return array(
							'name' => basename( $path ),
							'size' => filesize( $path ),
						);
					}
				}
			}
			return null;
		};

		$regular  = $find_file( array( '_v5.ttf', '_v5.otf', '-Regular.ttf', '-Regular.otf' ) );
		$bold     = $find_file( array( '_v5_bold.ttf', '_v5_bold.otf', '-Bold.ttf', '-Bold.otf' ) );
		$language = function_exists( 'locale_get_display_language' )
			? locale_get_display_language( $locale, 'en' )
			: $locale;

		return array(
			'needed'       => true,
			'locale'       => $locale,
			'language'     => $language,
			'font_name'    => $font_name,
			'display_name' => $config['google'],
			'google_url'   => 'https://fonts.google.com/specimen/' . rawurlencode( str_replace( ' ', '+', $config['google'] ) ),
			'regular'      => $regular,
			'bold'         => $bold,
		);
	}

	/**
	 * Return the locale font config for a given locale without triggering a download.
	 *
	 * @param string|null $locale Locale string; defaults to current site locale.
	 * @return array|null Font config array or null if the locale uses Latin script.
	 * @since 7.0
	 */
	public static function get_locale_config( $locale = null ) {

		if ( null === $locale ) {
			$locale = get_locale();
		}

		$map    = self::locale_font_map();
		$config = null;

		foreach ( $map as $prefix => $data ) {
			if ( 0 === strpos( $locale, $prefix ) ) {
				$config = $data;
				break;
			}
		}

		return apply_filters( 'wcdn_pdf_locale_font_config', $config, $locale );
	}

	/**
	 * Return the full locale → font config map.
	 *
	 * @return array
	 * @since 7.0
	 */
	protected static function locale_font_map() {
		return array(

			// Chinese — Simplified.
			'zh_CN' => array(
				'name'   => 'NotoSansSC',
				'google' => 'Noto Sans SC',
			),
			'zh_SG' => array(
				'name'   => 'NotoSansSC',
				'google' => 'Noto Sans SC',
			),

			// Chinese — Traditional.
			'zh_TW' => array(
				'name'   => 'NotoSansTC',
				'google' => 'Noto Sans TC',
			),
			'zh_HK' => array(
				'name'   => 'NotoSansHK',
				'google' => 'Noto Sans HK',
			),

			// Japanese.
			'ja'    => array(
				'name'   => 'NotoSansJP',
				'google' => 'Noto Sans JP',
			),

			// Korean.
			'ko_KR' => array(
				'name'   => 'NotoSansKR',
				'google' => 'Noto Sans KR',
			),
			'ko'    => array(
				'name'   => 'NotoSansKR',
				'google' => 'Noto Sans KR',
			),

			// Arabic script (Arabic, Persian/Farsi, Urdu, Pashto, Kurdish Sorani).
			'ar'    => array(
				'name'   => 'NotoNaskhArabic',
				'google' => 'Noto Naskh Arabic',
			),
			'fa'    => array(
				'name'   => 'NotoNaskhArabic',
				'google' => 'Noto Naskh Arabic',
			),
			'ur'    => array(
				'name'   => 'NotoNaskhArabic',
				'google' => 'Noto Naskh Arabic',
			),
			'ps'    => array(
				'name'   => 'NotoNaskhArabic',
				'google' => 'Noto Naskh Arabic',
			),
			'ckb'   => array(
				'name'   => 'NotoNaskhArabic',
				'google' => 'Noto Naskh Arabic',
			),

			// Hebrew.
			'he_IL' => array(
				'name'   => 'NotoSansHebrew',
				'google' => 'Noto Sans Hebrew',
			),
			'he'    => array(
				'name'   => 'NotoSansHebrew',
				'google' => 'Noto Sans Hebrew',
			),

			// Devanagari (Hindi, Marathi, Nepali, Sanskrit, Bhojpuri).
			'hi_IN' => array(
				'name'   => 'NotoSansDevanagari',
				'google' => 'Noto Sans Devanagari',
			),
			'hi'    => array(
				'name'   => 'NotoSansDevanagari',
				'google' => 'Noto Sans Devanagari',
			),
			'mr'    => array(
				'name'   => 'NotoSansDevanagari',
				'google' => 'Noto Sans Devanagari',
			),
			'ne_NP' => array(
				'name'   => 'NotoSansDevanagari',
				'google' => 'Noto Sans Devanagari',
			),
			'ne'    => array(
				'name'   => 'NotoSansDevanagari',
				'google' => 'Noto Sans Devanagari',
			),
			'sa_IN' => array(
				'name'   => 'NotoSansDevanagari',
				'google' => 'Noto Sans Devanagari',
			),
			'bho'   => array(
				'name'   => 'NotoSansDevanagari',
				'google' => 'Noto Sans Devanagari',
			),

			// Bengali.
			'bn_BD' => array(
				'name'   => 'NotoSansBengali',
				'google' => 'Noto Sans Bengali',
			),
			'bn_IN' => array(
				'name'   => 'NotoSansBengali',
				'google' => 'Noto Sans Bengali',
			),
			'bn'    => array(
				'name'   => 'NotoSansBengali',
				'google' => 'Noto Sans Bengali',
			),

			// Tamil.
			'ta_IN' => array(
				'name'   => 'NotoSansTamil',
				'google' => 'Noto Sans Tamil',
			),
			'ta_LK' => array(
				'name'   => 'NotoSansTamil',
				'google' => 'Noto Sans Tamil',
			),
			'ta'    => array(
				'name'   => 'NotoSansTamil',
				'google' => 'Noto Sans Tamil',
			),

			// Telugu.
			'te'    => array(
				'name'   => 'NotoSansTelugu',
				'google' => 'Noto Sans Telugu',
			),

			// Kannada.
			'kn'    => array(
				'name'   => 'NotoSansKannada',
				'google' => 'Noto Sans Kannada',
			),

			// Malayalam.
			'ml_IN' => array(
				'name'   => 'NotoSansMalayalam',
				'google' => 'Noto Sans Malayalam',
			),
			'ml'    => array(
				'name'   => 'NotoSansMalayalam',
				'google' => 'Noto Sans Malayalam',
			),

			// Gujarati.
			'gu'    => array(
				'name'   => 'NotoSansGujarati',
				'google' => 'Noto Sans Gujarati',
			),

			// Punjabi / Gurmukhi.
			'pa_IN' => array(
				'name'   => 'NotoSansGurmukhi',
				'google' => 'Noto Sans Gurmukhi',
			),

			// Sinhala.
			'si_LK' => array(
				'name'   => 'NotoSansSinhala',
				'google' => 'Noto Sans Sinhala',
			),
			'si'    => array(
				'name'   => 'NotoSansSinhala',
				'google' => 'Noto Sans Sinhala',
			),

			// Thai.
			'th'    => array(
				'name'   => 'NotoSansThai',
				'google' => 'Noto Sans Thai',
			),

			// Khmer.
			'km'    => array(
				'name'   => 'NotoSansKhmer',
				'google' => 'Noto Sans Khmer',
			),

			// Myanmar / Burmese.
			'my_MM' => array(
				'name'   => 'NotoSansMyanmar',
				'google' => 'Noto Sans Myanmar',
			),
			'my'    => array(
				'name'   => 'NotoSansMyanmar',
				'google' => 'Noto Sans Myanmar',
			),

			// Lao.
			'lo'    => array(
				'name'   => 'NotoSansLao',
				'google' => 'Noto Sans Lao',
			),

			// Tibetan.
			'bo'    => array(
				'name'   => 'NotoSerifTibetan',
				'google' => 'Noto Serif Tibetan',
			),

			// Georgian.
			'ka_GE' => array(
				'name'   => 'NotoSansGeorgian',
				'google' => 'Noto Sans Georgian',
			),
			'ka'    => array(
				'name'   => 'NotoSansGeorgian',
				'google' => 'Noto Sans Georgian',
			),

			// Armenian.
			'hy'    => array(
				'name'   => 'NotoSansArmenian',
				'google' => 'Noto Sans Armenian',
			),

			// Amharic / Ethiopic.
			'am'    => array(
				'name'   => 'NotoSansEthiopic',
				'google' => 'Noto Sans Ethiopic',
			),
		);
	}

	/**
	 * Resolve the locale font info, downloading if necessary and caching the result.
	 *
	 * @return array { name: string, path: string|false, bold_path: string|false }
	 * @since 7.0
	 */
	protected static function get_locale_font_info() {

		if ( null !== self::$locale_font_cache ) {
			return self::$locale_font_cache;
		}

		$empty  = array(
			'name'      => '',
			'path'      => false,
			'bold_path' => false,
		);
		$locale = get_locale();
		$config = self::get_locale_config( $locale );

		if ( ! $config ) {
			self::$locale_font_cache = $empty;
			return $empty;
		}

		$font_name = $config['name'];

		// 1. Local file path provided via filter.
		$local_path = apply_filters( 'wcdn_pdf_locale_font_path', '', $locale );
		if ( $local_path && file_exists( $local_path ) ) {
			$result                  = array(
				'name'      => $font_name,
				'path'      => $local_path,
				'bold_path' => false,
			);
			self::$locale_font_cache = $result;
			return $result;
		}

		// 2. Remote TTF/OTF URL provided via filter — registerFont() will download it.
		$remote_url = apply_filters( 'wcdn_pdf_locale_font_url', '', $locale );
		if ( $remote_url ) {
			$result                  = array(
				'name'      => $font_name,
				'path'      => esc_url_raw( $remote_url ),
				'bold_path' => false,
			);
			self::$locale_font_cache = $result;
			return $result;
		}

		/*
		 * 3. Download from Google Fonts and cache in uploads/wcdn/fonts/.
		 *
		 *    The font is registered with dompdf via FontMetrics::registerFont() using the
		 *    cached local file path. This avoids embedding the font as a base64 data URI,
		 *    which can exhaust PHP memory for large CJK fonts (5–20 MB → 7–27 MB base64).
		 *
		 *    Disable with: add_filter( 'wcdn_pdf_use_google_fonts_for_locale', '__return_false' );
		 */
		if ( apply_filters( 'wcdn_pdf_use_google_fonts_for_locale', true ) ) {
			$cached      = self::download_locale_font( $font_name, $config['google'] );
			$cached_bold = self::download_locale_font_bold( $font_name, $config['google'] );

			if ( $cached ) {
				$result                  = array(
					'name'      => $font_name,
					'path'      => $cached,
					'bold_path' => $cached_bold ? $cached_bold : false,
				);
				self::$locale_font_cache = $result;
				return $result;
			}
		}

		self::$locale_font_cache = $empty;
		return $empty;
	}

	/**
	 * Return locale font name for use in the CSS font-family stack.
	 *
	 * The actual font registration is handled by register_locale_font() in Pdf::generate().
	 * This method only returns the CSS font name so get_css() can inject it into the
	 * font-family declaration — no @font-face block is needed here.
	 *
	 * @return array { css: string, name: string }
	 * @since 7.0
	 */
	protected static function get_locale_font_face_css() {

		$info = self::get_locale_font_info();

		return array(
			'css'  => '',
			'name' => $info['name'],
		);
	}

	/**
	 * Pre-download the locale font for the current WordPress locale.
	 *
	 * Intended to be called from a background Action Scheduler job so the font
	 * is cached before the first PDF is generated. Safe to call multiple times —
	 * subsequent calls return immediately once the file is already on disk.
	 *
	 * @return bool True if the font is available after this call, false otherwise.
	 * @since 7.0
	 */
	public static function prefetch_locale_font() {
		self::$locale_font_cache = null;
		$info = self::get_locale_font_info();
		return ! empty( $info['path'] ) && file_exists( $info['path'] );
	}

	/**
	 * Clear the in-memory locale font cache.
	 *
	 * Call after uploading or deleting a font file so the next PDF generation
	 * picks up the new file without a stale cached path.
	 *
	 * @return void
	 * @since 7.0
	 */
	public static function clear_locale_font_cache() {
		self::$locale_font_cache = null;
	}

	/**
	 * Register the locale font with dompdf's FontMetrics engine.
	 *
	 * Must be called after new Dompdf() and before Dompdf::loadHtml().
	 * Registers all four weight/style variants pointing to the same file so that
	 * dompdf never falls back to a built-in font (e.g. for bold <th> elements)
	 * when a locale font only ships a single Regular weight file.
	 *
	 * @param \Dompdf\Dompdf $dompdf Dompdf instance.
	 * @return void
	 * @since 7.0
	 */
	public static function register_locale_font( $dompdf ) {

		$info = self::get_locale_font_info();

		if ( ! $info['name'] || ! $info['path'] ) {
			return;
		}

		$bold_path = ! empty( $info['bold_path'] ) ? $info['bold_path'] : $info['path'];

		foreach ( array( 'normal', 'italic' ) as $style ) {
			$dompdf->getFontMetrics()->registerFont(
				array(
					'family' => $info['name'],
					'weight' => 'normal',
					'style'  => $style,
				),
				$info['path']
			);
			$dompdf->getFontMetrics()->registerFont(
				array(
					'family' => $info['name'],
					'weight' => 'bold',
					'style'  => $style,
				),
				$bold_path
			);
		}
	}

	/**
	 * Download a locale font and cache it in uploads/wcdn/fonts/.
	 *
	 * Tries two strategies in order:
	 *
	 * 1. Google Fonts Download ZIP API — downloads the full family ZIP and extracts
	 *    the Regular weight. This is the only reliable path for CJK scripts (Japanese,
	 *    Chinese, Korean) because the CSS API only serves Latin-only subsets for those
	 *    families regardless of User-Agent. Requires PHP ZipArchive extension.
	 *
	 * 2. Google Fonts CSS API fallback — parses the legacy CSS response and downloads
	 *    the first candidate URL that passes TTF/OTF magic-byte validation. Works for
	 *    smaller non-CJK scripts (Arabic, Hebrew, Thai, Devanagari, etc.) where Google
	 *    serves a single-file complete font to old browser User-Agents.
	 *
	 * The cache filename uses a _v4 suffix so stale _v3 files (which may be Latin-only
	 * TTF subsets downloaded by the old CSS-API-only strategy) are never reused.
	 *
	 * @param string $font_name     CSS font-family identifier used as the cache filename.
	 * @param string $google_family Google Fonts family name (spaces, not URL-encoded).
	 * @return string|false Absolute path to the cached font file, or false on failure.
	 * @since 7.0
	 */
	protected static function download_locale_font( $font_name, $google_family ) {

		$font_dir  = trailingslashit( wp_upload_dir()['basedir'] ) . 'wcdn/fonts/';
		$font_file = $font_dir . sanitize_file_name( $font_name ) . '_v5.ttf';

		// 40 KB minimum rejects Latin-only subset files (~10–34 KB) that the CSS API
		// fallback may have saved in earlier versions of the plugin.
		if ( file_exists( $font_file ) && filesize( $font_file ) > 40960 ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$magic            = (string) file_get_contents( $font_file, false, null, 0, 4 );
			$valid_signatures = array( "\x00\x01\x00\x00", 'true', 'OTTO', 'typ1' );
			if ( in_array( $magic, $valid_signatures, true ) ) {
				return $font_file;
			}
			wp_delete_file( $font_file );
		}

		if ( ! wp_mkdir_p( $font_dir ) ) {
			return false;
		}

		// Strategy 0: User-provided file — conventional name {FontName}-Regular.ttf/otf.
		// Allows admins to manually drop a font into uploads/wcdn/fonts/ without filters.
		foreach ( array( '.ttf', '.otf' ) as $ext ) {
			$manual = $font_dir . sanitize_file_name( $font_name ) . '-Regular' . $ext;
			if ( file_exists( $manual ) && filesize( $manual ) > 40960 ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$magic            = (string) file_get_contents( $manual, false, null, 0, 4 );
				$valid_signatures = array( "\x00\x01\x00\x00", 'true', 'OTTO', 'typ1' );
				if ( in_array( $magic, $valid_signatures, true ) ) {
					return $manual;
				}
			}
		}

		// Strategy 1: Google Fonts download ZIP with browser headers to pass bot detection.
		if ( class_exists( 'ZipArchive' ) ) {
			$result = self::try_download_google_zip( $google_family, $font_dir, $font_file );
			if ( $result ) {
				return $result;
			}
		}

		// Strategy 2: Direct download from Google Fonts GitHub mirror (no bot detection).
		$result = self::try_download_font_direct( $google_family, $font_file );
		if ( $result ) {
			return $result;
		}

		// Strategy 3: CSS API fallback — works for smaller non-CJK scripts only.
		return self::try_download_font_css( $google_family, $font_file );
	}

	/**
	 * Download the Bold weight of the locale font, caching it separately.
	 *
	 * @param string $font_name     CSS font-family identifier.
	 * @param string $google_family Google Fonts family name.
	 * @return string|false Absolute path to the cached bold font file, or false on failure.
	 * @since 7.0
	 */
	protected static function download_locale_font_bold( $font_name, $google_family ) {

		$font_dir  = trailingslashit( wp_upload_dir()['basedir'] ) . 'wcdn/fonts/';
		$bold_file = $font_dir . sanitize_file_name( $font_name ) . '_v5_bold.ttf';

		if ( file_exists( $bold_file ) && filesize( $bold_file ) > 40960 ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$magic            = (string) file_get_contents( $bold_file, false, null, 0, 4 );
			$valid_signatures = array( "\x00\x01\x00\x00", 'true', 'OTTO', 'typ1' );
			if ( in_array( $magic, $valid_signatures, true ) ) {
				return $bold_file;
			}
			wp_delete_file( $bold_file );
		}

		if ( ! wp_mkdir_p( $font_dir ) ) {
			return false;
		}

		// User-provided bold file: {FontName}-Bold.ttf/otf.
		foreach ( array( '.ttf', '.otf' ) as $ext ) {
			$manual = $font_dir . sanitize_file_name( $font_name ) . '-Bold' . $ext;
			if ( file_exists( $manual ) && filesize( $manual ) > 40960 ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$magic            = (string) file_get_contents( $manual, false, null, 0, 4 );
				$valid_signatures = array( "\x00\x01\x00\x00", 'true', 'OTTO', 'typ1' );
				if ( in_array( $magic, $valid_signatures, true ) ) {
					return $manual;
				}
			}
		}

		if ( class_exists( 'ZipArchive' ) ) {
			$result = self::try_download_google_zip( $google_family, $font_dir, $bold_file, true );
			if ( $result ) {
				return $result;
			}
		}

		return self::try_download_font_direct( $google_family, $bold_file, true );
	}

	/**
	 * Download the Google Fonts ZIP using browser-like headers to bypass bot detection.
	 *
	 * The fonts.google.com/download endpoint blocks plain server-side requests but passes
	 * through requests that look like a real browser (User-Agent + Referer + Accept headers).
	 *
	 * @param string $google_family Google Fonts family name.
	 * @param string $font_dir      Absolute path to the fonts cache directory (trailing slash).
	 * @param string $font_file     Absolute path for the output font file.
	 * @param bool   $bold          True to extract Bold weight; false for Regular.
	 * @return string|false
	 * @since 7.0
	 */
	protected static function try_download_google_zip( $google_family, $font_dir, $font_file, $bold = false ) {

		$temp_zip = $font_dir . md5( $google_family ) . '_tmp.zip';

		$response = wp_remote_get(
			'https://fonts.google.com/download?family=' . rawurlencode( $google_family ),
			array(
				'timeout' => 120,
				'headers' => array(
					'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
					'Referer'    => 'https://fonts.google.com/',
					'Accept'     => 'application/zip,application/octet-stream,*/*',
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$zip_data = wp_remote_retrieve_body( $response );

		if ( strlen( $zip_data ) < 1024 || 'PK' !== substr( $zip_data, 0, 2 ) ) {
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $temp_zip, $zip_data );

		if ( ! file_exists( $temp_zip ) || filesize( $temp_zip ) < 1024 ) {
			wp_delete_file( $temp_zip );
			return false;
		}

		$zip = new \ZipArchive();

		if ( true !== $zip->open( $temp_zip ) ) {
			wp_delete_file( $temp_zip );
			return false;
		}

		$weight_pattern   = $bold ? '/\bBold\b[^\/]*\.(ttf|otf)$/i' : '/\bRegular\b[^\/]*\.(ttf|otf)$/i';
		$valid_signatures = array( "\x00\x01\x00\x00", 'true', 'OTTO', 'typ1' );
		$font_data        = false;

		for ( $i = 0; $i < $zip->numFiles; $i++ ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$entry = $zip->getNameIndex( $i );

			if ( ! preg_match( $weight_pattern, $entry ) ) {
				continue;
			}

			$data = $zip->getFromIndex( $i );

			if ( ! $data || strlen( $data ) < 40960 ) {
				continue;
			}

			if ( ! in_array( substr( $data, 0, 4 ), $valid_signatures, true ) ) {
				continue;
			}

			$font_data = $data;
			break;
		}

		$zip->close();
		wp_delete_file( $temp_zip );

		if ( ! $font_data ) {
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $font_file, $font_data );

		return file_exists( $font_file ) ? $font_file : false;
	}

	/**
	 * Download a locale font directly from the Google Fonts GitHub mirror.
	 *
	 * Uses static per-weight TTF files rather than variable fonts so dompdf renders
	 * text at the correct weight. raw.githubusercontent.com has no bot detection.
	 *
	 * @param string $google_family Google Fonts family name.
	 * @param string $font_file     Absolute path for the output font file.
	 * @param bool   $bold          True to download the Bold weight; false for Regular.
	 * @return string|false
	 * @since 7.0
	 */
	protected static function try_download_font_direct( $google_family, $font_file, $bold = false ) {

		$base = 'https://raw.githubusercontent.com/google/fonts/main/ofl/';

		// CJK fonts: full Unicode-mapped OTF per weight from googlefonts/noto-cjk Sans/OTF/.
		// SubsetOTF files are CID-keyed (no Unicode cmap) and not usable with dompdf.
		// Non-CJK fonts come from google/fonts static subdirectory (TTF per weight).
		$cjk = 'https://raw.githubusercontent.com/googlefonts/noto-cjk/main/Sans/OTF/';

		$family_map = array(
			'Noto Sans JP'         => array(
				'regular' => $cjk . 'Japanese/NotoSansCJKjp-Regular.otf',
				'bold'    => $cjk . 'Japanese/NotoSansCJKjp-Bold.otf',
			),
			'Noto Sans SC'         => array(
				'regular' => $cjk . 'SimplifiedChinese/NotoSansCJKsc-Regular.otf',
				'bold'    => $cjk . 'SimplifiedChinese/NotoSansCJKsc-Bold.otf',
			),
			'Noto Sans TC'         => array(
				'regular' => $cjk . 'TraditionalChinese/NotoSansCJKtc-Regular.otf',
				'bold'    => $cjk . 'TraditionalChinese/NotoSansCJKtc-Bold.otf',
			),
			'Noto Sans HK'         => array(
				'regular' => $cjk . 'TraditionalChineseHK/NotoSansCJKhk-Regular.otf',
				'bold'    => $cjk . 'TraditionalChineseHK/NotoSansCJKhk-Bold.otf',
			),
			'Noto Sans KR'         => array(
				'regular' => $cjk . 'Korean/NotoSansCJKkr-Regular.otf',
				'bold'    => $cjk . 'Korean/NotoSansCJKkr-Bold.otf',
			),
			'Noto Naskh Arabic'    => array(
				'regular' => $base . 'notonaskharabic/static/NotoNaskhArabic-Regular.ttf',
				'bold'    => $base . 'notonaskharabic/static/NotoNaskhArabic-Bold.ttf',
			),
			'Noto Sans Hebrew'     => array(
				'regular' => $base . 'notosanshebrew/static/NotoSansHebrew-Regular.ttf',
				'bold'    => $base . 'notosanshebrew/static/NotoSansHebrew-Bold.ttf',
			),
			'Noto Sans Devanagari' => array(
				'regular' => $base . 'notosansdevanagari/static/NotoSansDevanagari-Regular.ttf',
				'bold'    => $base . 'notosansdevanagari/static/NotoSansDevanagari-Bold.ttf',
			),
			'Noto Sans Bengali'    => array(
				'regular' => $base . 'notosansbengali/static/NotoSansBengali-Regular.ttf',
				'bold'    => $base . 'notosansbengali/static/NotoSansBengali-Bold.ttf',
			),
			'Noto Sans Tamil'      => array(
				'regular' => $base . 'notosanstamil/static/NotoSansTamil-Regular.ttf',
				'bold'    => $base . 'notosanstamil/static/NotoSansTamil-Bold.ttf',
			),
			'Noto Sans Telugu'     => array(
				'regular' => $base . 'notosanstelugu/static/NotoSansTelugu-Regular.ttf',
				'bold'    => $base . 'notosanstelugu/static/NotoSansTelugu-Bold.ttf',
			),
			'Noto Sans Kannada'    => array(
				'regular' => $base . 'notosanskannada/static/NotoSansKannada-Regular.ttf',
				'bold'    => $base . 'notosanskannada/static/NotoSansKannada-Bold.ttf',
			),
			'Noto Sans Malayalam'  => array(
				'regular' => $base . 'notosansmalayalam/static/NotoSansMalayalam-Regular.ttf',
				'bold'    => $base . 'notosansmalayalam/static/NotoSansMalayalam-Bold.ttf',
			),
			'Noto Sans Gujarati'   => array(
				'regular' => $base . 'notosansgujarati/static/NotoSansGujarati-Regular.ttf',
				'bold'    => $base . 'notosansgujarati/static/NotoSansGujarati-Bold.ttf',
			),
			'Noto Sans Gurmukhi'   => array(
				'regular' => $base . 'notosansgurmukhi/static/NotoSansGurmukhi-Regular.ttf',
				'bold'    => $base . 'notosansgurmukhi/static/NotoSansGurmukhi-Bold.ttf',
			),
			'Noto Sans Sinhala'    => array(
				'regular' => $base . 'notosanssinhala/static/NotoSansSinhala-Regular.ttf',
				'bold'    => $base . 'notosanssinhala/static/NotoSansSinhala-Bold.ttf',
			),
			'Noto Sans Thai'       => array(
				'regular' => $base . 'notosansthai/static/NotoSansThai-Regular.ttf',
				'bold'    => $base . 'notosansthai/static/NotoSansThai-Bold.ttf',
			),
			'Noto Sans Khmer'      => array(
				'regular' => $base . 'notosanskhmer/static/NotoSansKhmer-Regular.ttf',
				'bold'    => $base . 'notosanskhmer/static/NotoSansKhmer-Bold.ttf',
			),
			'Noto Sans Myanmar'    => array(
				'regular' => $base . 'notosansmyanmar/static/NotoSansMyanmar-Regular.ttf',
				'bold'    => $base . 'notosansmyanmar/static/NotoSansMyanmar-Bold.ttf',
			),
			'Noto Sans Lao'        => array(
				'regular' => $base . 'notosanslao/static/NotoSansLao-Regular.ttf',
				'bold'    => $base . 'notosanslao/static/NotoSansLao-Bold.ttf',
			),
			'Noto Serif Tibetan'   => array(
				'regular' => $base . 'notoseriftibetan/static/NotoSerifTibetan-Regular.ttf',
				'bold'    => $base . 'notoseriftibetan/static/NotoSerifTibetan-Bold.ttf',
			),
			'Noto Sans Georgian'   => array(
				'regular' => $base . 'notosansgeorgian/static/NotoSansGeorgian-Regular.ttf',
				'bold'    => $base . 'notosansgeorgian/static/NotoSansGeorgian-Bold.ttf',
			),
			'Noto Sans Armenian'   => array(
				'regular' => $base . 'notosansarmenian/static/NotoSansArmenian-Regular.ttf',
				'bold'    => $base . 'notosansarmenian/static/NotoSansArmenian-Bold.ttf',
			),
			'Noto Sans Ethiopic'   => array(
				'regular' => $base . 'notosansethiopic/static/NotoSansEthiopic-Regular.ttf',
				'bold'    => $base . 'notosansethiopic/static/NotoSansEthiopic-Bold.ttf',
			),
		);

		if ( ! isset( $family_map[ $google_family ] ) ) {
			return false;
		}

		$weight_key = $bold ? 'bold' : 'regular';

		/**
		 * Filter the direct-download URL for a locale font weight.
		 *
		 * @param string $url           Default GitHub raw URL.
		 * @param string $google_family Google Fonts family name.
		 * @param bool   $bold          True for the bold variant.
		 * @since 7.0
		 */
		$url = apply_filters(
			'wcdn_locale_font_direct_url',
			$family_map[ $google_family ][ $weight_key ],
			$google_family,
			$bold
		);

		if ( empty( $url ) ) {
			return false;
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 120,
			)
		);

		$status = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );

		if ( is_wp_error( $response ) || 200 !== $status ) {
			return false;
		}

		$font_data = wp_remote_retrieve_body( $response );

		if ( strlen( $font_data ) < 40960 ) {
			return false;
		}

		$valid_signatures = array( "\x00\x01\x00\x00", 'true', 'OTTO', 'typ1' );

		if ( ! in_array( substr( $font_data, 0, 4 ), $valid_signatures, true ) ) {
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $font_file, $font_data );

		return file_exists( $font_file ) ? $font_file : false;
	}

	/**
	 * Download a locale font via the Google Fonts legacy CSS API.
	 *
	 * Uses an Android 2.2 User-Agent to request TTF/OTF instead of WOFF2.
	 * For CJK scripts Google only serves Latin-only subsets (~34 KB) via this path;
	 * those are filtered out by the 40 KB size threshold.  For smaller scripts
	 * (Arabic, Hebrew, Thai, Devanagari, etc.) a single complete font is usually served.
	 *
	 * @param string $google_family Google Fonts family name.
	 * @param string $font_file     Absolute path for the output font file.
	 * @return string|false
	 * @since 7.0
	 */
	protected static function try_download_font_css( $google_family, $font_file ) {

		// Android 2.2 UA: Google Fonts serves TTF instead of WOFF2.
		$css_response = wp_remote_get(
			'https://fonts.googleapis.com/css?family=' . rawurlencode( $google_family ),
			array(
				'user-agent' => 'Mozilla/5.0 (Linux; U; Android 2.2; en-us; Nexus One Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
				'timeout'    => 15,
			)
		);

		if ( is_wp_error( $css_response ) || 200 !== (int) wp_remote_retrieve_response_code( $css_response ) ) {
			return false;
		}

		$css_body = wp_remote_retrieve_body( $css_response );

		if ( ! preg_match_all( '#\burl\(\s*[\'"]?(https://fonts\.gstatic\.com/[^\'")\s]+)[\'"]?\s*\)#i', $css_body, $url_matches ) ) {
			return false;
		}

		$font_urls        = array_unique( $url_matches[1] );
		$valid_signatures = array( "\x00\x01\x00\x00", 'true', 'OTTO', 'typ1' );
		$font_data        = false;

		foreach ( $font_urls as $candidate_url ) {

			$font_response = wp_remote_get( $candidate_url, array( 'timeout' => 60 ) );

			if ( is_wp_error( $font_response ) || 200 !== (int) wp_remote_retrieve_response_code( $font_response ) ) {
				continue;
			}

			$data = wp_remote_retrieve_body( $font_response );

			if ( strlen( $data ) < 1024 ) {
				continue;
			}

			if ( ! in_array( substr( $data, 0, 4 ), $valid_signatures, true ) ) {
				continue;
			}

			// Reject Latin-only subset files. Complete fonts for any non-Latin script
			// are always larger than 40 KB; subsets served by the CSS API are 10–34 KB.
			if ( strlen( $data ) < 40960 ) {
				continue;
			}

			$font_data = $data;
			break;
		}

		if ( ! $font_data ) {
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $font_file, $font_data );

		return file_exists( $font_file ) ? $font_file : false;
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
