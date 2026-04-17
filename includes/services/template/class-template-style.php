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
					'font-size' => 'shopPhoneFontSize',
					'color'     => 'shopPhoneTextColor',
				),

				'.wcdn-shop-email'            => array(
					'font-size' => 'shopEmailFontSize',
					'color'     => 'shopEmailTextColor',
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
					'font-size' => 'policiesFontSize',
					'color'     => 'policiesTextColor',
				),

				'.wcdn-complimentary-close'   => array(
					'font-size' => 'complimentaryCloseFontSize',
					'color'     => 'complimentaryCloseTextColor',
				),

				'.wcdn-footer'                => array(
					'font-size' => 'footerFontSize',
					'color'     => 'footerTextColor',
				),

				'.wcdn-payment-button'        => array(
					'background' => 'payNowColor',
				),

				'.wcdn-customer-note'         => array(
					'font-size'   => 'customerNoteFontSize',
					'color'       => 'customerNoteTextColor',
					'font-weight' => array( 'customerNoteFontStyle', 'bold' ),
				),
			),
			$settings,
			$context
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
				)
			);
		}

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
				$context
			);
		}

		return apply_filters( 'wcdn_dynamic_css', $css, $settings );
	}

	/**
	 * Map multiple selectors to settings.
	 *
	 * @param array  $map      Selector-to-setting mapping.
	 * @param array  $settings Template settings.
	 * @param string $context  Rendering context ('pdf', 'html', etc.).
	 * @return string
	 * @since 7.0
	 */
	protected static function map( $map, $settings, $context = 'html' ) {

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

			$css .= self::rule( $selector, $styles, $context );
		}

		return $css;
	}

	/**
	 * Build CSS rule.
	 *
	 * @param string $selector CSS selector.
	 * @param array  $styles   CSS properties.
	 * @param string $context  Rendering context ('pdf', 'html', etc.).
	 * @return string
	 * @since 7.0
	 */
	protected static function rule( $selector, $styles, $context = 'html' ) {

		if ( empty( $styles ) ) {
			return '';
		}

		$css = $selector . '{';

		foreach ( $styles as $prop => $value ) {

			if ( null === $value || '' === $value ) {
				continue;
			}

			if ( 'font-size' === $prop && is_numeric( $value ) ) {
				if ( 'pdf' === $context ) {
					// Convert px (at 96 DPI) to pt for dompdf, which uses 72 pt/inch.
					$value = round( $value * 72 / 96, 2 ) . 'pt';
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
