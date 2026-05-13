<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Template Style Engine Class.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Services
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Template Style Engine Class.
 *
 * Generates dynamic CSS rules based on
 * template configuration settings.
 *
 * @since 7.0
 */
class Template_Style {

	/**
	 * Generate CSS from settings.
	 *
	 * @param array  $settings Template style settings.
	 * @param string $context  Rendering context ('pdf', 'html', etc.).
	 * @return string
	 * @since 7.0
	 */
	public static function generate( $settings = array(), $context = 'html' ) {

		if ( empty( $settings ) ) {
			return '';
		}

		$css = '';

		/**
		 * DOCUMENT ZOOM
		 * HTML: CSS zoom property.
		 * PDF: pt-value scaling via zoom_factor + padding-right override in Template_Renderer.
		 */
		$zoom        = (int) ( $settings['documentZoom'] ?? 100 );
		$zoom_factor = $zoom / 100;

		if ( 'html' === $context && 100 !== $zoom ) {
			$css .= '.wcdn-document{zoom:' . $zoom . '%;}';
		}

		// PDF: apply a 0.9 base factor so text/spacing match the HTML viewport rendering.
		// context_css pt-scaling + padding-right override handled in Template_Renderer::get_css().
		$zoom_mode = 'pdf' === $context
			? apply_filters( 'wcdn_pdf_zoom_mode', $settings['documentZoomMode'] ?? 'layout' )
			: 'layout';

		if ( 'pdf' === $context ) {
			$zoom_factor *= 0.9;
		}

		/**
		 * BASIC SECTIONS
		 */
		$css .= self::map(
			array(
				'.wcdn-watermark-repeat span' => array(
					'font-size' => 'watermarkFontSize',
					'color'     => 'watermarkColor',
					'opacity'   => 'watermarkOpacity',
				),

				'.wcdn-watermark-container'   => array(
					'font-size' => 'watermarkFontSize',
					'color'     => 'watermarkColor',
					'opacity'   => 'watermarkOpacity',
				),

				'.wcdn-logo-image'            => array(
					'max-height' => function ( $settings ) {
						$scale = ( $settings['logoScale'] ?? 100 ) / 100;
						return round( 80 * $scale ) . 'px';
					},
				),

				'.wcdn-title'                 => array(
					'font-size'   => 'documentTitleFontSize',
					'color'       => 'documentTitleTextColor',
					'text-align'  => 'documentTitleAlign',
					'font-weight' => array( 'documentTitleFontStyle', 'bold' ),
				),

				'.wcdn-shop-name'             => array(
					'font-size'   => 'shopNameFontSize',
					'color'       => 'shopNameTextColor',
					'text-align'  => 'shopNameAlign',
					'font-weight' => array( 'shopNameFontStyle', 'bold' ),
				),

				'.wcdn-shop-address'          => array(
					'font-size'   => 'addressFontSize',
					'color'       => 'addressTextColor',
					'text-align'  => 'addressAlign',
					'font-weight' => array( 'addressFontStyle', 'bold' ),
				),

				'.wcdn-shop-phone'            => array(
					'font-size'     => 'shopPhoneFontSize',
					'color'         => 'shopPhoneTextColor',
					'font-weight'   => array( 'shopPhoneFontStyle', 'bold' ),
					'text-align'    => 'shopPhoneAlign',
					'margin-top'    => 'shopPhoneMarginTop',
					'margin-bottom' => 'shopPhoneMarginBottom',
				),

				'.wcdn-shop-email'            => array(
					'font-size'     => 'shopEmailFontSize',
					'color'         => 'shopEmailTextColor',
					'font-weight'   => array( 'shopEmailFontStyle', 'bold' ),
					'text-align'    => 'shopEmailAlign',
					'margin-top'    => 'shopEmailMarginTop',
					'margin-bottom' => 'shopEmailMarginBottom',
				),

				'.wcdn-billing-address'       => array(
					'font-size'   => 'billingAddressFontSize',
					'text-align'  => 'billingAddressAlign',
					'color'       => 'billingAddressTextColor',
					'font-weight' => array( 'billingAddressFontStyle', 'bold' ),
				),

				'.wcdn-billing-address p'     => array(
					'font-size' => 'billingAddressFontSize',
					'color'     => 'billingAddressTextColor',
				),

				'.wcdn-shipping-address'      => array(
					'font-size'   => 'shippingAddressFontSize',
					'text-align'  => 'shippingAddressAlign',
					'color'       => 'shippingAddressTextColor',
					'font-weight' => array( 'shippingAddressFontStyle', 'bold' ),
				),

				'.wcdn-shipping-address p'    => array(
					'font-size' => 'shippingAddressFontSize',
					'color'     => 'shippingAddressTextColor',
				),

				'.wcdn-totals'                => array(
					'font-size' => 'totalsFontSize',
				),

				'.wcdn-policies'              => array(
					'font-size'   => 'policiesFontSize',
					'color'       => 'policiesTextColor',
					'font-weight' => array( 'policiesFontStyle', 'bold' ),
					'text-align'  => 'policiesAlign',
				),

				'.wcdn-complimentary-close'   => array(
					'font-size'   => 'complimentaryCloseFontSize',
					'color'       => 'complimentaryCloseTextColor',
					'font-weight' => array( 'complimentaryCloseFontStyle', 'bold' ),
					'text-align'  => 'complimentaryCloseAlign',
				),

				'.wcdn-footer'                => array(
					'font-size'   => 'footerFontSize',
					'color'       => 'footerTextColor',
					'font-weight' => array( 'footerFontStyle', 'bold' ),
					'text-align'  => 'footerAlign',
				),

				'.wcdn-payment-button'        => array(
					'background' => 'payNowColor',
				),

				'.wcdn-customer-note'         => array(
					'font-size'   => 'customerNoteFontSize',
					'color'       => 'customerNoteTextColor',
					'font-weight' => array( 'customerNoteFontStyle', 'bold' ),
				),

				'.wcdn-item-name'             => array(
					'font-size'      => 'productNameFontSize',
					'font-weight'    => array( 'productNameFontStyle', 'bold' ),
					'color'          => 'productNameTextColor',
					'padding-top'    => 'productNamePadding',
					'padding-bottom' => 'productNamePadding',
				),
			),
			$settings,
			$context,
			$zoom_factor,
			$zoom_mode
		);

		/**
		 * WATERMARK
		 */
		if ( ! empty( $settings['showWatermark'] ) ) {

			$angle = $settings['watermarkAngle'] ?? -25;

			$css .= self::rule(
				'.wcdn-watermark',
				array(
					'color'     => $settings['watermarkColor'] ?? '#000',
					'opacity'   => $settings['watermarkOpacity'] ?? 0.08,
					'font-size' => $settings['watermarkFontSize'] ?? '80px',
					'transform' => "rotate({$angle}deg)",
				),
				$context,
				$zoom_factor,
				$zoom_mode
			);
		}

		/**
		 * ORDER DATA HEADER
		 */
		$css .= self::rule(
			'.wcdn-order-data-header',
			array(
				'font-size'     => $settings['orderDataHeaderFontSize'] ?? null,
				'font-weight'   => 'bold' === ( $settings['orderDataHeaderFontStyle'] ?? 'bold' ) ? 'bold' : 'normal',
				'text-align'    => $settings['orderDataHeaderAlign'] ?? null,
				'color'         => $settings['orderDataHeaderTextColor'] ?? null,
				'margin-top'    => $settings['orderDataHeaderSpacingTop'] ?? null,
				'margin-bottom' => $settings['orderDataHeaderSpacingBottom'] ?? null,
			),
			$context,
			$zoom_factor,
			$zoom_mode
		);

		/**
		 * ORDER META (DYNAMIC LOOP 🔥)
		 */
		$meta_keys = array(
			'invoiceNumber',
			'documentDate',
			'orderNumber',
			'orderDate',
			'paymentMethod',
			'paymentDate',
			'shippingMethod',
			'refundDate',
			'refundReason',
			'billingPhone',
			'billingEmail',
		);

		foreach ( $meta_keys as $key ) {

			$css .= self::rule(
				".wcdn-meta-{$key} td",
				array(
					'text-align'  => $settings[ "{$key}Align" ] ?? null,
					'font-size'   => $settings[ "{$key}FontSize" ] ?? null,
					'color'       => $settings[ "{$key}TextColor" ] ?? null,
					'font-weight' => self::font_weight( $settings[ "{$key}FontStyle" ] ?? null ),
				),
				$context,
				$zoom_factor,
				$zoom_mode
			);
		}

		// Columns-mode billing phone/email (rendered as <p> siblings, not table rows).
		foreach ( array( 'billingPhone', 'billingEmail' ) as $key ) {
			$css .= self::rule(
				".wcdn-billing-address .wcdn-columns-{$key}",
				array(
					'text-align'  => $settings[ "{$key}Align" ] ?? null,
					'font-size'   => $settings[ "{$key}FontSize" ] ?? null,
					'color'       => $settings[ "{$key}TextColor" ] ?? null,
					'font-weight' => self::font_weight( $settings[ "{$key}FontStyle" ] ?? null ),
				),
				$context,
				$zoom_factor,
				$zoom_mode
			);
		}

		return apply_filters( 'wcdn_dynamic_css', $css, $settings );
	}

	/**
	 * Map multiple selectors to settings.
	 *
	 * @param array  $map         Selector-to-setting mapping.
	 * @param array  $settings    Template settings.
	 * @param string $context     Rendering context ('pdf', 'html', etc.).
	 * @param float  $zoom_factor Multiplier applied to numeric size values in PDF context.
	 * @param string $zoom_mode   'layout' scales all dimensional props; 'text' scales font-size only.
	 * @return string
	 * @since 7.0
	 */
	protected static function map( $map, $settings, $context = 'html', $zoom_factor = 1.0, $zoom_mode = 'layout' ) {

		$css = '';

		foreach ( $map as $selector => $rules ) {

			$styles = array();

			foreach ( $rules as $prop => $setting_key ) {

				if ( is_callable( $setting_key ) ) {
					$value = call_user_func( $setting_key, $settings, $prop );

					if ( null !== $value && '' !== $value ) {
						$styles[ $prop ] = $value;
					}

					continue;
				}

				/**
				 * Handle font weight mapping
				 */
				if ( is_array( $setting_key ) ) {
					[ $key, $type ] = $setting_key;

					if ( 'bold' === $type ) {
						$styles[ $prop ] = self::font_weight( $settings[ $key ] ?? null );
					}

					continue;
				}

				if ( ! empty( $settings[ $setting_key ] ) ) {
					$styles[ $prop ] = $settings[ $setting_key ];
				}
			}

			$css .= self::rule( $selector, $styles, $context, $zoom_factor, $zoom_mode );
		}

		return $css;
	}

	/**
	 * Build CSS rule.
	 *
	 * @param string $selector    CSS selector.
	 * @param array  $styles      CSS properties.
	 * @param string $context     Rendering context ('pdf', 'html', etc.).
	 * @param float  $zoom_factor Multiplier applied to numeric size values in PDF context.
	 * @param string $zoom_mode   'layout' scales all dimensional props; 'text' scales font-size only.
	 * @return string
	 * @since 7.0
	 */
	protected static function rule( $selector, $styles, $context = 'html', $zoom_factor = 1.0, $zoom_mode = 'layout' ) {

		if ( empty( $styles ) ) {
			return '';
		}

		$css = $selector . '{';

		foreach ( $styles as $prop => $value ) {

			if ( null === $value || '' === $value ) {
				continue;
			}

			$px_props = array( 'font-size', 'margin-top', 'margin-bottom', 'padding-top', 'padding-bottom', 'padding' );
			if ( in_array( $prop, $px_props, true ) && is_numeric( $value ) ) {
				if ( 'pdf' === $context ) {
					// In text mode only font-size is zoomed; other dimensional props keep full scale.
					$factor = ( 'text' === $zoom_mode && 'font-size' !== $prop ) ? 1.0 : $zoom_factor;
					$value  = round( $value * 72 / 96 * $factor, 2 ) . 'pt';
				} else {
					$value .= 'px';
				}
			}

			$css .= $prop . ':' . $value . ';';
		}

		$css .= '}';

		return $css;
	}

	/**
	 * Normalize font weight value.
	 *
	 * @param string|null $value Font style value.
	 * @return string
	 * @since 7.0
	 */
	protected static function font_weight( $value ) {
		return 'bold' === $value ? '600' : '400';
	}
}
