<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * REST API for Templates.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Admin/API/Templates
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Api;

use WP_REST_Request;
use Tyche\WCDN\Services\Template_Engine;
use Tyche\WCDN\Helpers\Settings;
use Tyche\WCDN\Helpers\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Templates REST API Controller.
 *
 * Handles:
 * - Fetching template configuration
 * - Saving template settings
 * - Providing preview data
 *
 * @since 7.0
 */
class Templates extends \Tyche\WCDN\Api\Api {

	/**
	 * Option key.
	 */
	const OPTION_KEY = WCDN_SLUG . '_template_settings';

	/**
	 * Construct
	 *
	 * @since 7.0
	 */
	public function __construct() {

		add_action(
			'rest_api_init',
			array( __CLASS__, 'register_routes' )
		);
	}

	/**
	 * Function for registering the API routes.
	 *
	 * @since 7.0
	 */
	public static function register_routes() {

		register_rest_route(
			'wcdn/v1',
			'/templates',
			array(

				array(
					'methods'             => 'GET',
					'callback'            => array(
						__CLASS__,
						'fetch_templates',
					),
					'permission_callback' => array(
						__CLASS__,
						'permissions',
					),
				),

				array(
					'methods'             => 'POST',
					'callback'            => array(
						__CLASS__,
						'save_templates',
					),
					'permission_callback' => array(
						__CLASS__,
						'permissions',
					),
				),

			)
		);
	}

	/**
	 * Generate Preview Data
	 *
	 * Generates preview data used by the template preview
	 * renderer in the admin UI.
	 *
	 * The preview includes:
	 *
	 * - Store identity from plugin settings
	 * - Document footer content
	 * - A random WooCommerce order
	 *
	 * @since 7.0
	 */
	private static function get_preview_data() {

		$order      = self::get_random_order();
		$order_data = $order ? self::format_order_data( $order ) : null;

		// Fall back to fully-generated sample when no order exists or the order
		// produced no items (e.g. all its products were deleted).
		if ( empty( $order_data ) || empty( $order_data['items'] ) ) {
			$order_data = self::format_order_data( null, true );
		} else {
			// Merge each real item with sample values so missing properties
			// (deleted product, no SKU, etc.) always render something in the preview.
			$sample_item = array(
				'name'          => __( 'Sample Product', 'woocommerce-delivery-notes' ),
				'sku'           => 'SKU-001',
				'price'         => wc_price( 25 ),
				'quantity'      => 2,
				'total'         => wc_price( 50 ),
				'product_id'    => 0,
				'order_item_id' => 0,
				'meta'          => array(),
				'addon'         => null,
				'image_url'     => '',
				'image_path'    => '',
			);

			$order_data['items'] = array_map(
				function ( $item ) use ( $sample_item ) {
					// Structural keys: fill only when absent.
					$item = wp_parse_args( $item, $sample_item );

					// Renderable string keys: also substitute when empty so the
					// preview never shows a blank cell.
					foreach ( array( 'name', 'sku', 'price', 'quantity', 'total' ) as $key ) {
						if ( isset( $sample_item[ $key ] ) && ( ! isset( $item[ $key ] ) || '' === (string) $item[ $key ] ) ) {
							$item[ $key ] = $sample_item[ $key ];
						}
					}

					return $item;
				},
				$order_data['items']
			);
		}

		// When the preview order has no tax (taxes disabled or zero-tax order),
		// inject a sample amount so the tax row is always visible in the preview.
		if ( empty( $order_data['totals']['tax'] ) ) {
			$order_data['totals']['tax'] = wc_price( 5 );
		}

		return array(
			'shop'     => self::get_store_data(),
			'document' => self::get_document_data(),
			'order'    => $order_data,
		);
	}

	/**
	 * Get Store Data
	 *
	 * Retrieves store identity information from the
	 * plugin settings configuration.
	 *
	 * @since 7.0
	 */
	public static function get_store_data() {

		$settings = get_option(
			WCDN_SLUG . '_settings',
			array()
		);

		$logo      = $settings['storeLogo'] ?? '';
		$logo_path = '';

		if ( is_numeric( $logo ) ) {
			$attached  = get_attached_file( (int) $logo );
			$logo_path = $attached ? $attached : '';
			$logo      = wp_get_attachment_url( $logo );
		} elseif ( ! empty( $logo ) ) {
			// storeLogo stored as URL — legacy migration format. Resolve back to
			// a file path so dompdf can embed the image in PDFs.
			$attachment_id = attachment_url_to_postid( $logo );
			if ( $attachment_id ) {
				$attached  = get_attached_file( $attachment_id );
				$logo_path = $attached ? $attached : '';
			}
		}

		return array(
			'name'      => $settings['storeName'] ?? '',
			'logo'      => $logo,
			'logo_path' => $logo_path,
			'address'   => $settings['storeAddress'] ?? '',
			'phone'     => $settings['phone'] ?? '',
			'email'     => $settings['email'] ?? '',
		);
	}

	/**
	 * Get Document Data
	 *
	 * Retrieves document related content such as footer,
	 * policies and complimentary close from plugin settings.
	 *
	 * @since 7.0
	 */
	public static function get_document_data() {

		$settings = get_option(
			WCDN_SLUG . '_settings',
			array()
		);

		return array(
			'footer'             => $settings['footerText'] ?? '',
			'complimentaryClose' => $settings['complimentaryClose'] ?? '',
			'policies'           => $settings['policies'] ?? '',
			'isRTL'              => 'rtl' === ( $settings['textDirection'] ?? 'ltr' ),
		);
	}

	/**
	 * Get Random Order
	 *
	 * Retrieves a random WooCommerce order to populate
	 * preview data for invoice templates.
	 *
	 * @since 7.0
	 */
	private static function get_random_order() {

		$orders = wc_get_orders(
			array(
				'limit'   => 1,
				'orderby' => 'rand',
				'status'  => array( 'processing', 'completed' ),
			)
		);

		if ( empty( $orders ) ) {
			return null;
		}

		return $orders[0];
	}

	/**
	 * Filter: replace currency symbol with the ISO 4217 currency code.
	 * Used during PDF data generation to avoid rendering issues with fonts
	 * that lack certain currency glyphs.
	 *
	 * @param string $symbol   Currency symbol (unused).
	 * @param string $currency ISO 4217 currency code.
	 * @return string
	 */
	public static function normalize_currency_symbol( $symbol, $currency ) {
		return $currency . ' ';
	}

	/**
	 * Format Order Data.
	 *
	 * @param \WC_Order|string $order     Order object or 'sample'.
	 * @param bool             $is_sample Whether to return sample data.
	 * @param string           $template  Template key ('invoice', 'receipt', etc.).
	 * @return array
	 * @since 7.0
	 */
	public static function format_order_data( $order, $is_sample = false, $template = 'invoice' ) {

		add_filter( 'woocommerce_currency_symbol', array( __CLASS__, 'normalize_currency_symbol' ), 10, 2 );

		if ( $is_sample ) {
			$sample = array(
				'invoiceNumber'  => 'INV-2026-0001',
				'documentDate'   => gmdate( 'c' ),
				'paymentMethod'  => 'Credit Card (Stripe)',
				'shippingMethod' => 'Flat Rate',
				'payment_url'    => '#',
				'customer_note'  => 'Please deliver between 9am–5pm.',
				'billing'        => array(
					'name'    => 'John Doe',
					'address' => array_filter(
						explode(
							'<br/>',
							WC()->countries->get_formatted_address(
								array(
									'first_name' => 'John',
									'last_name'  => 'Doe',
									'company'    => '',
									'address_1'  => '1234 Elm Street',
									'address_2'  => 'Apt 5B',
									'city'       => 'Springfield',
									'state'      => 'IL',
									'postcode'   => '62704',
									'country'    => 'US',
								)
							)
						)
					),
					'phone'   => '(555) 123-4567',
					'email'   => 'john@example.com',
				),
				'shipping'       => array(
					'name'    => 'John Doe',
					'address' => array_filter(
						explode(
							'<br/>',
							WC()->countries->get_formatted_address(
								array(
									'first_name' => 'John',
									'last_name'  => 'Doe',
									'company'    => '',
									'address_1'  => '1234 Elm Street',
									'address_2'  => 'Apt 5B',
									'city'       => 'Springfield',
									'state'      => 'IL',
									'postcode'   => '62704',
									'country'    => 'US',
								)
							)
						)
					),
				),
				'orderNumber'    => '1234',
				'date'           => gmdate( 'c' ),
				'paymentDate'    => gmdate( 'c', strtotime( '-1 hour' ) ),
				'currency'       => get_woocommerce_currency(),
				'items'          => array(
					array(
						'name'       => 'Sample Product',
						'sku'        => 'SKU-001',
						'quantity'   => 2,
						'price'      => wc_price( 25 ),
						'total'      => wc_price( 50 ),
						'image_url'  => '',
						'image_path' => '',
					),
				),
				'totals'         => apply_filters(
					'wcdn_order_totals',
					array(
						'subtotal'   => wc_price( 50 ),
						'discount'   => wc_price( -5 ),
						'tax'        => wc_price( 5 ),
						'shipping'   => wc_price( 10 ),
						'total'      => wc_price( 65 ),
						'has_refund' => false,
					),
					null,
					$template
				),
				'refund'         => array(
					'date'   => gmdate( 'c' ),
					'reason' => __( 'Customer returned item', 'woocommerce-delivery-notes' ),
					'total'  => wc_price( 25 ),
					'items'  => array(
						array(
							'name'     => 'Sample Product',
							'sku'      => 'SKU-001',
							'quantity' => 1,
							'price'    => wc_price( 25 ),
							'total'    => wc_price( 25 ),
							'meta'     => array(),
							'addon'    => null,
						),
					),
				),
				'status'         => 'pending',
			);

			remove_filter( 'woocommerce_currency_symbol', array( __CLASS__, 'normalize_currency_symbol' ), 10 );

			return $sample;
		}

		// WC_Order_Refund objects lack address/payment methods; always work with the parent order.
		if ( $order instanceof \WC_Order_Refund ) {
			$parent = wc_get_order( $order->get_parent_id() );
			if ( ! $parent ) {
				return array();
			}
			$order = $parent;
		}

		$items = array();

		foreach ( $order->get_items() as $item_id => $item ) {

			$product = apply_filters( 'wcdn_order_item_product', $item->get_product(), $item );

			if ( ! $product ) {
				continue;
			}

			$qty_refunded = $order->get_qty_refunded_for_item( $item_id );
			$_qty         = max( 0, $item->get_quantity() + $qty_refunded );

			if ( $_qty <= 0 ) {
				continue;
			}

			$image_id   = $product->get_image_id();
			$image_file = $image_id ? get_attached_file( $image_id ) : false;

			$items[] = array(
				'name'          => self::format_order_item( 'name', $product, $order, $item ),
				'sku'           => self::format_order_item( 'sku', $product, $order, $item ),
				'price'         => self::format_order_item( 'price', $product, $order, $item ),
				'quantity'      => self::format_order_item( 'quantity', $product, $order, $item ),
				'total'         => self::format_order_item( 'total', $product, $order, $item ),
				'product_id'    => $product->get_id(),
				'order_item_id' => $item_id,
				'meta'          => self::format_order_item( 'meta', $product, $order, $item ),
				'addon'         => self::format_order_item( 'addon', $product, $order, $item ),
				'image_url'     => $image_id ? wp_get_attachment_url( $image_id ) : '',
				'image_path'    => $image_file ? $image_file : '',
			);
		}

		$billing  = $order->get_address( 'billing' );
		$shipping = $order->get_address( 'shipping' );

		unset( $billing['first_name'], $billing['last_name'] );
		unset( $shipping['first_name'], $shipping['last_name'] );

		$billing_address = WC()->countries->get_formatted_address( $billing );
		$billing_address = array_filter( explode( '<br/>', $billing_address ) );

		$shipping_address = WC()->countries->get_formatted_address( $shipping );
		$shipping_address = array_filter( explode( '<br/>', $shipping_address ) );

		$shipping_method_name = '';
		$methods              = $order->get_shipping_methods();

		if ( ! empty( $methods ) ) {
			$method               = reset( $methods );
			$shipping_method_name = $method->get_name();
		}

		$payment_method_title = $order->get_payment_method_title();

		if ( empty( $payment_method_title ) ) {

			// Fallback to gateway ID.
			$payment_method = $order->get_payment_method();

			if ( ! empty( $payment_method ) ) {

				// Convert slug to readable label (e.g. "cod" → "Cash on delivery").
				$payment_method_title = wc_get_payment_gateway_by_order( $order );

				if ( $payment_method_title && method_exists( $payment_method_title, 'get_title' ) ) {
					$payment_method_title = $payment_method_title->get_title();
				} else {
					$payment_method_title = ucwords( str_replace( '_', ' ', $payment_method ) );
				}
			} else {
				$payment_method_title = __( 'N/A', 'woocommerce-delivery-notes' );
			}
		}

		$data = array(
			'id'             => $order->get_id(),
			'invoiceNumber'  => self::format_invoice_number( $order ),
			'documentDate'   => Utils::get_order_document_date( $order->get_id(), $template ),
			'orderNumber'    => $order->get_order_number(),
			'date'           => $order->get_date_created() ? $order->get_date_created()->format( 'Y-m-d\TH:i:s' ) : '',
			'paymentDate'    => $order->get_date_paid() ? $order->get_date_paid()->format( 'Y-m-d\TH:i:s' ) : '',
			'paymentMethod'  => $payment_method_title,
			'shippingMethod' => $shipping_method_name,
			'currency'       => $order->get_currency(),
			'payment_url'    => $order->is_paid() ? '' : $order->get_checkout_payment_url(),
			'customer_note'  => $order->get_customer_note(),

			'billing'        => array(
				'name'    => $order->get_formatted_billing_full_name(),
				'address' => $billing_address,
				'phone'   => wcdn_format_phone_number( $order->get_billing_phone(), $order->get_billing_country() ),
				'email'   => $order->get_billing_email(),
			),

			'shipping'       => array(
				'name'    => $order->get_formatted_shipping_full_name(),
				'address' => $shipping_address,
			),
			'items'          => $items,
			'totals'         => self::format_order_totals( $order, $template ),
			'refund'         => self::generate_refund_preview( $order, $items ),
			'status'         => $order->get_status(),
		);

		remove_filter( 'woocommerce_currency_symbol', array( __CLASS__, 'normalize_currency_symbol' ), 10 );

		return $data;
	}

	/**
	 * Format order totals, including refund breakdown when applicable.
	 *
	 * Returns base totals for all orders. When the order has a non-zero
	 * refunded amount, three additional keys are added:
	 *  - 'refunded'  — amount refunded (positive float)
	 *  - 'net_total' — order total minus refunded amount
	 *  - 'tax_label' — inclusive-tax note for the net total (empty string when not applicable)
	 *
	 * @param \WC_Order $order    Order object.
	 * @param string    $template Template key ('invoice', 'receipt', etc.).
	 * @return array
	 * @since 7.0
	 */
	private static function format_order_totals( $order, $template = '' ) {

		$currency       = $order->get_currency();
		$total_refunded = (float) $order->get_total_refunded();

		$totals = array(
			'subtotal' => wc_price( $order->get_subtotal(), array( 'currency' => $currency ) ),
			'shipping' => wc_price( $order->get_shipping_total(), array( 'currency' => $currency ) ),
			'total'    => wc_price( $order->get_total(), array( 'currency' => $currency ) ),
		);

		if ( $order->get_discount_total() > 0 ) {
			$totals['discount'] = wc_price( -$order->get_discount_total(), array( 'currency' => $currency ) );
		}

		// Tax rows — only when WC taxes are enabled and the order has a non-zero tax amount.
		if ( wc_tax_enabled() && $order->get_total_tax() > 0 ) {

			$tax_totals = $order->get_tax_totals();

			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) && ! empty( $tax_totals ) ) {

				$tax_lines = array();

				foreach ( $tax_totals as $tax ) {
					$amount = $total_refunded > 0
						? $tax->amount - $order->get_total_tax_refunded_by_rate_id( $tax->rate_id )
						: $tax->amount;

					if ( $amount > 0 ) {
						$tax_lines[] = array(
							'label' => $tax->label,
							'value' => wc_price( $amount, array( 'currency' => $currency ) ),
						);
					}
				}

				if ( ! empty( $tax_lines ) ) {
					$totals['tax_lines'] = $tax_lines;
				}
			}

			// Always keep the aggregate 'tax' key for backward-compat with the wcdn_order_totals filter.
			$totals['tax'] = wc_price( $order->get_total_tax(), array( 'currency' => $currency ) );
		}

		if ( $total_refunded <= 0 ) {
			/**
			 * Filter the order totals array used in document templates.
			 *
			 * Unset any key to hide that row. Recognised keys: subtotal, tax, tax_lines, shipping,
			 * total, has_refund, refunded, net_total, tax_label.
			 *
			 * @param array         $totals   Totals array.
			 * @param WC_Order|null $order    WooCommerce order object (null in preview mode).
			 * @param string        $template Template key (invoice, receipt, deliverynote, etc.).
			 */
			return apply_filters( 'wcdn_order_totals', $totals, $order, $template );
		}

		$tax_label = '';

		if ( wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_cart' ) ) {

			$tax_parts = array();

			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {

				foreach ( $order->get_tax_totals() as $tax ) {
					$net_tax     = $tax->amount - $order->get_total_tax_refunded_by_rate_id( $tax->rate_id );
					$tax_parts[] = sprintf(
						'%s %s',
						wc_price( $net_tax, array( 'currency' => $currency ) ),
						$tax->label
					);
				}
			} else {

				$net_tax     = $order->get_total_tax() - $order->get_total_tax_refunded();
				$tax_parts[] = sprintf(
					'%s %s',
					wc_price( $net_tax, array( 'currency' => $currency ) ),
					WC()->countries->tax_or_vat()
				);
			}

			if ( ! empty( $tax_parts ) ) {
				/* translators: %s: Tax label(s) e.g. "$4.50 VAT" */
				$tax_label = sprintf( __( '(Includes %s)', 'woocommerce' ), implode( ', ', $tax_parts ) );
			}
		}

		$totals['has_refund'] = true;
		$totals['refunded']   = wc_price( -$total_refunded, array( 'currency' => $currency ) );
		$totals['net_total']  = wc_price( $order->get_total() - $total_refunded, array( 'currency' => $currency ) );
		$totals['tax_label']  = $tax_label;

		/** This filter is documented in includes/api/class-templates.php */
		return apply_filters( 'wcdn_order_totals', $totals, $order, $template );
	}

	/**
	 * Format a specific field for an order item.
	 *
	 * @param string         $field   Field to format.
	 * @param \WC_Product    $product Product object.
	 * @param \WC_Order      $order   Order object.
	 * @param \WC_Order_Item $item    Order item object.
	 * @return mixed Formatted field value.
	 * @since 7.0
	 */
	private static function format_order_item( $field, $product, $order, $item ) {

		if ( 'name' === $field ) {
			if ( ! empty( $item->get_meta( '_wc_pao_addon_value', true ) ) ) {
				return $item->get_meta( '_wc_pao_addon_name', true );
			}
			return apply_filters( 'wcdn_order_item_name', $item->get_name(), $item );
		}

		if ( 'sku' === $field ) {
			return $product ? $product->get_sku() : '';
		}

		if ( 'meta' === $field ) {
			if ( ! empty( $item->get_meta( '_wc_pao_addon_value', true ) ) ) {
				return array();
			}
			return $product ? self::format_item_meta( $product, $order, $item ) : array();
		}

		if ( 'addon' === $field ) {
			$addon_value = $item->get_meta( '_wc_pao_addon_value', true );
			if ( ! empty( $addon_value ) ) {
				return array(
					'name'  => $item->get_meta( '_wc_pao_addon_name', true ),
					'value' => $addon_value,
				);
			}
			return null;
		}

		if ( 'quantity' === $field ) {
			$item_id      = $item->get_id();
			$qty_refunded = $order->get_qty_refunded_for_item( $item_id );
			$adjusted_qty = max( 0, $item->get_quantity() + $qty_refunded );
			return apply_filters( 'wcdn_order_item_quantity', $adjusted_qty, $item );
		}

		if ( 'total' === $field ) {
			return $order->get_formatted_line_subtotal( $item );
		}

		if ( 'price' === $field ) {
			$tax_display = get_option( 'woocommerce_tax_display_cart' );

			if ( 'excl' === $tax_display ) {
				$ex_tax_label = wc_prices_include_tax() ? 1 : 0;
				$price        = wc_price(
					$order->get_item_subtotal( $item, false ),
					array(
						'ex_tax_label' => $ex_tax_label,
						'currency'     => $order->get_currency(),
					)
				);
			} else {
				$price = wc_price(
					$order->get_item_subtotal( $item, true ),
					array( 'currency' => $order->get_currency() )
				);
			}

			return apply_filters( 'wcdn_formatted_item_price', $price, $item, $order );
		}

		return null;
	}

	/**
	 * Build structured meta rows for an order item.
	 *
	 * @param \WC_Product    $product Product object.
	 * @param \WC_Order      $order   Order object.
	 * @param \WC_Order_Item $item   Order item object.
	 * @return array Array of ['label' => string, 'value' => string] pairs.
	 * @since 7.0
	 */
	private static function format_item_meta( $product, $order, $item ) {

		$meta           = array();
		$product_id     = $product ? $product->get_id() : 0;
		$yith_keys_used = array();

		/**
		 * YITH Product Add-ons.
		 */
		$yith_raw = $item->get_meta( '_ywapo_meta_data', true );

		if ( ! empty( $yith_raw ) && is_array( $yith_raw ) ) {

			foreach ( $yith_raw as $group ) {

				if ( ! is_array( $group ) ) {
					continue;
				}

				foreach ( (array) $group as $maybe ) {

					$entries = isset( $maybe['addon_id'] ) ? array( $maybe ) : (array) $maybe;

					foreach ( $entries as $sub ) {

						if ( ! isset( $sub['addon_id'] ) ) {
							continue;
						}

						$option_id        = $sub['option_id'] ?? 0;
						$meta_key         = 'ywapo-addon-' . $sub['addon_id'] . '-' . $option_id;
						$yith_keys_used[] = $meta_key;

						if ( isset( $sub['display_label'], $sub['display_value'] ) ) {
							$meta[] = array(
								'label' => $sub['display_label'],
								'value' => wp_strip_all_tags( $sub['display_value'] ),
							);
						}
					}
				}
			}
		}

		/**
		 * Extra Product Options (TM EPO / _tmcartepo_data).
		 */
		$epo_data = $item->get_meta( '_tmcartepo_data', true );

		if ( ! empty( $epo_data ) && is_array( $epo_data ) ) {
			foreach ( $epo_data as $epo ) {
				if ( ! empty( $epo['name'] ) && isset( $epo['value'] ) ) {
					$meta[] = array(
						'label' => $epo['name'],
						'value' => wp_strip_all_tags( $epo['value'] ),
					);
				}
			}
		}

		/**
		 * WooCommerce Product Addons.
		 */
		$product_addons = array();

		if ( class_exists( 'WC_Product_Addons_Helper' ) ) {
			$product_addons = \WC_Product_Addons_Helper::get_product_addons( $product_id );
		}

		/**
		 * Variation attributes and remaining visible meta.
		 */
		$item_meta = apply_filters( 'wcdn_product_meta_data', $item->get_formatted_meta_data( '_' ), $item );

		if ( $item_meta ) {
			foreach ( $item_meta as $meta_field ) {

				// Skip YITH keys already rendered above.
				if ( in_array( $meta_field->key, $yith_keys_used, true ) ) {
					continue;
				}

				$value = wp_strip_all_tags( $meta_field->display_value );

				foreach ( $product_addons as $addon ) {
					if ( 'file_upload' === $addon['type'] && $meta_field->key === $addon['name'] ) {
						$value = wp_basename( $value );
						break;
					}
				}

				$meta[] = array(
					'label' => wp_strip_all_tags( $meta_field->display_key ),
					'value' => $value,
				);
			}
		}

		/**
		 * Downloadable file count.
		 */
		if ( $product && $product->exists() && $product->is_downloadable() && $order->is_download_permitted() ) {
			$meta[] = array(
				'label' => __( 'Download', 'woocommerce-delivery-notes' ),
				/* translators: %s: number of files */
				'value' => sprintf( __( '%s Files', 'woocommerce-delivery-notes' ), count( $item->get_item_downloads() ) ),
			);
		}

		/**
		 * Product Input Fields for WooCommerce (Lite + Pro) by Algoritmika.
		 * Reads global and local input field values stored in item meta.
		 */
		$pif_plugins = array(
			'product-input-fields-for-woocommerce-pro/product-input-fields-for-woocommerce-pro.php',
			'product-input-fields-for-woocommerce/product-input-fields-for-woocommerce.php',
		);

		$pif_active = false;

		foreach ( $pif_plugins as $pif_plugin ) {
			if ( in_array( $pif_plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) ) {
				$pif_active = true;
				break;
			}

			if ( is_multisite() && array_key_exists( $pif_plugin, get_site_option( 'active_sitewide_plugins', array() ) ) ) {
				$pif_active = true;
				break;
			}
		}

		if ( $pif_active ) {

			foreach ( array( '_alg_wc_pif_global', '_alg_wc_pif_local' ) as $pif_meta_key ) {

				$pif_fields = $item->get_meta( $pif_meta_key, true );

				if ( ! empty( $pif_fields ) && is_array( $pif_fields ) ) {
					foreach ( $pif_fields as $pif_field ) {
						if ( isset( $pif_field['title'], $pif_field['_value'] ) ) {
							$meta[] = array(
								'label' => wp_strip_all_tags( $pif_field['title'] ),
								'value' => wp_strip_all_tags( $pif_field['_value'] ),
							);
						}
					}
				}
			}
		}

		/**
		 * Custom fields via filter.
		 */
		$extra_fields = apply_filters( 'wcdn_order_item_fields', array(), $product, $order, $item );

		foreach ( $extra_fields as $field ) {
			$meta[] = array(
				'label' => $field['label'],
				'value' => $field['value'],
			);
		}

		return $meta;
	}

	/**
	 * Format the invoice number using a given format and order data.
	 *
	 * Replaces placeholders in the format string with values from the order.
	 *
	 * @param WC_Order $order  The order object used for replacements.
	 * @return string The formatted invoice number.
	 *
	 * @since 7.0
	 */
	private static function format_invoice_number( $order ) {
		return Utils::get_order_invoice_number( $order->get_id() );
	}

	/**
	 * Fetch Templates.
	 *
	 * Returns saved template settings, generated defaults,
	 * UI configuration and preview data for all registered templates.
	 *
	 * @return \WP_REST_Response
	 *
	 * @since 7.0
	 */
	public static function fetch_templates() {

		$template_keys = Template_Engine::get_template_keys();

		$saved_templates = get_option(
			self::OPTION_KEY,
			array()
		);

		$templates = array();
		$config    = array();

		foreach ( $template_keys as $template_key ) {

			$structure = Template_Engine::get_structure( $template_key );

			if ( empty( $structure ) ) {
				continue;
			}

			$defaults = Template_Engine::build_defaults( $template_key, $structure );

			// Merge saved values over defaults.
			$templates[ $template_key ] = isset( $saved_templates[ $template_key ] )
			? wp_parse_args( $saved_templates[ $template_key ], $defaults )
			: $defaults;

			$config[ $template_key ] = Template_Engine::build_config( $template_key, $structure );
		}

		$preview = get_transient( 'wcdn_preview_data' );

		if ( false === $preview ) {
			$preview = self::get_preview_data();
			set_transient( 'wcdn_preview_data', $preview, 60 );
		}

		return self::response(
			'success',
			array(
				'templates' => $templates,
				'config'    => $config,
				'preview'   => $preview,
			)
		);
	}

	/**
	 * Save Template Settings.
	 *
	 * Saves settings for a specific template.
	 *
	 * @param WP_REST_Request $request REST request.
	 *
	 * @return \WP_REST_Response
	 *
	 * @since 7.0
	 */
	public static function save_templates( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {

			return self::response(
				'error',
				array(
					'error_description' =>
					__( 'Authentication has failed.', 'woocommerce-delivery-notes' ),
				)
			);
		}

		$params = $request->get_json_params();

		if ( empty( $params['template'] ) || empty( $params['data'] ) ) {

			return self::response(
				'error',
				array(
					'error_description' =>
					__( 'Invalid request payload.', 'woocommerce-delivery-notes' ),
				)
			);
		}

		$template_key  = sanitize_key( $params['template'] );
		$template_data = $params['data'];

		/*
		* Get template structure from engine
		*/
		$structure = Template_Engine::get_structure( $template_key );

		if ( empty( $structure ) ) {

			return self::response(
				'error',
				array(
					'error_description' =>
					__( 'Invalid template type.', 'woocommerce-delivery-notes' ),
				)
			);
		}

		/*
		* Build schema and defaults
		*/
		$schema   = Template_Engine::build_schema( $structure );
		$defaults = Template_Engine::build_defaults( $template_key, $structure );

		/*
		* Get existing templates
		*/
		$templates = get_option(
			self::OPTION_KEY,
			array()
		);

		/*
		* Sanitize and update
		*/
		$templates[ $template_key ] = self::sanitize(
			$template_data,
			$schema,
			$defaults
		);

		update_option( self::OPTION_KEY, $templates );

		return self::response(
			'success',
			array(
				'message'   =>
				__( 'Template saved successfully.', 'woocommerce-delivery-notes' ),
				'templates' =>
				$templates[ $template_key ],
			)
		);
	}

	/**
	 * Generate Refund Preview Data.
	 *
	 * Ensures the preview always has refund data even if the
	 * selected order does not contain real refunds.
	 *
	 * @param WC_Order $order Order object.
	 * @param array    $items Order items.
	 *
	 * @return array
	 *
	 * @since 7.0
	 */
	private static function generate_refund_preview( $order, $items ) {

		$refunds = $order->get_refunds();

		/*
		* If the order already has a refund, use it
		*/
		if ( ! empty( $refunds ) ) {

			$refund         = current( $refunds );
			$refunded_items = array();
			$currency       = $order->get_currency();

			foreach ( $refund->get_items() as $item ) {

				$product = $item->get_product();
				$qty     = abs( $item->get_quantity() );

				$refunded_items[] = array(
					'name'     => self::format_order_item( 'name', $product, $order, $item ),
					'sku'      => self::format_order_item( 'sku', $product, $order, $item ),
					'price'    => self::format_order_item( 'price', $product, $order, $item ),
					'quantity' => $qty,
					'total'    => wc_price( abs( $item->get_total() ), array( 'currency' => $currency ) ),
					'meta'     => self::format_order_item( 'meta', $product, $order, $item ),
					'addon'    => self::format_order_item( 'addon', $product, $order, $item ),
				);
			}

			// Manual amount refunds have no line items — fall back to original order items.
			if ( empty( $refunded_items ) && ! empty( $items ) ) {
				$refunded_items = $items;
			}

			return array(
				'date'   => wc_format_datetime( $refund->get_date_created() ),
				'reason' => $refund->get_reason(),
				'total'  => wc_price( abs( $refund->get_amount() ), array( 'currency' => $currency ) ),
				'items'  => $refunded_items,
			);
		}

		/*
		* Otherwise generate fake preview refund from the first real order item.
		* meta and addon are already formatted by format_order_item(), carry them through.
		*/
		if ( empty( $items ) ) {
			return array();
		}

		$sample = $items[0];

		return array(
			'date'   => wc_format_datetime( new \WC_DateTime() ),
			'reason' => __( 'Customer returned item', 'woocommerce-delivery-notes' ),
			'total'  => $sample['total'],
			'items'  => array(
				array(
					'name'     => $sample['name'],
					'sku'      => $sample['sku'],
					'quantity' => 1,
					'price'    => $sample['price'],
					'total'    => $sample['total'],
					'meta'     => $sample['meta'] ?? array(),
					'addon'    => $sample['addon'] ?? null,
				),
			),
		);
	}
}
