<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Delivery & Pickup Date Time for WooCommerce (Coderockz) Integration.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Integrations
 * @category    Classes
 * @since       7.1.2
 */

namespace Tyche\WCDN\Integrations;

defined( 'ABSPATH' ) || exit;

/**
 * Coderockz Woo Delivery integration class.
 *
 * Appends delivery/pickup date and time fields to the WCDN order info
 * fields section for orders placed with the Delivery & Pickup Date Time
 * for WooCommerce plugin (Coderockz). Respects the plugin's own label
 * and date/time format settings.
 *
 * @since 7.1.2
 */
class Coderockz_Woo_Delivery {

	/**
	 * Constructor.
	 *
	 * @since 7.1.2
	 */
	public function __construct() {
		add_filter( 'wcdn_order_info_fields', array( $this, 'append_delivery_fields' ), 10, 2 );
	}

	/**
	 * Append delivery/pickup date and time to the order info fields.
	 *
	 * @param array     $fields Order info fields.
	 * @param \WC_Order $order  Order object.
	 * @return array
	 * @since 7.1.2
	 */
	public function append_delivery_fields( $fields, $order ) {

		if ( ! $order instanceof \WC_Order ) {
			return $fields;
		}

		$delivery_date_settings = get_option( 'coderockz_woo_delivery_date_settings', array() );
		$pickup_date_settings   = get_option( 'coderockz_woo_delivery_pickup_date_settings', array() );
		$delivery_time_settings = get_option( 'coderockz_woo_delivery_time_settings', array() );
		$pickup_time_settings   = get_option( 'coderockz_woo_delivery_pickup_settings', array() );

		// Labels — fall back to translatable defaults.
		$delivery_date_label = ! empty( $delivery_date_settings['field_label'] )
			? stripslashes( $delivery_date_settings['field_label'] )
			: __( 'Delivery Date', 'woocommerce-delivery-notes' );

		$delivery_time_label = ! empty( $delivery_time_settings['field_label'] )
			? stripslashes( $delivery_time_settings['field_label'] )
			: __( 'Delivery Time', 'woocommerce-delivery-notes' );

		$pickup_date_label = ! empty( $pickup_date_settings['pickup_field_label'] )
			? stripslashes( $pickup_date_settings['pickup_field_label'] )
			: __( 'Pickup Date', 'woocommerce-delivery-notes' );

		$pickup_time_label = ! empty( $pickup_time_settings['field_label'] )
			? stripslashes( $pickup_time_settings['field_label'] )
			: __( 'Pickup Time', 'woocommerce-delivery-notes' );

		// Date formats.
		$delivery_date_format = ! empty( $delivery_date_settings['date_format'] ) ? $delivery_date_settings['date_format'] : 'F j, Y';
		if ( ! empty( $delivery_date_settings['add_weekday_name'] ) ) {
			$delivery_date_format = 'l ' . $delivery_date_format;
		}

		$pickup_date_format = ! empty( $pickup_date_settings['date_format'] ) ? $pickup_date_settings['date_format'] : 'F j, Y';
		if ( ! empty( $pickup_date_settings['add_weekday_name'] ) ) {
			$pickup_date_format = 'l ' . $pickup_date_format;
		}

		// Time formats.
		$delivery_time_format = ( '24' === ( $delivery_time_settings['time_format'] ?? '12' ) ) ? 'H:i' : 'h:i A';
		$pickup_time_format   = ( '24' === ( $pickup_time_settings['time_format'] ?? '12' ) ) ? 'H:i' : 'h:i A';

		// Delivery date.
		$delivery_date_raw = $order->get_meta( 'delivery_date', true );
		if ( ! empty( $delivery_date_raw ) ) {
			$fields['coderockz_delivery_date'] = array(
				'label' => $delivery_date_label,
				'value' => date_i18n( $delivery_date_format, strtotime( $delivery_date_raw ) ),
			);
		}

		// Delivery time.
		$delivery_time_raw = $order->get_meta( 'delivery_time', true );
		if ( ! empty( $delivery_time_raw ) ) {
			$fields['coderockz_delivery_time'] = array(
				'label' => $delivery_time_label,
				'value' => self::format_time_slot( $delivery_time_raw, $delivery_time_format ),
			);
		}

		// Pickup date.
		$pickup_date_raw = $order->get_meta( 'pickup_date', true );
		if ( ! empty( $pickup_date_raw ) ) {
			$fields['coderockz_pickup_date'] = array(
				'label' => $pickup_date_label,
				'value' => date_i18n( $pickup_date_format, strtotime( $pickup_date_raw ) ),
			);
		}

		// Pickup time.
		$pickup_time_raw = $order->get_meta( 'pickup_time', true );
		if ( ! empty( $pickup_time_raw ) ) {
			$fields['coderockz_pickup_time'] = array(
				'label' => $pickup_time_label,
				'value' => self::format_time_slot( $pickup_time_raw, $pickup_time_format ),
			);
		}

		return $fields;
	}

	/**
	 * Format a time slot string (e.g. "08:00 - 09:00") using the given PHP time format.
	 *
	 * @param string $raw    Raw time value from order meta.
	 * @param string $format PHP date format string.
	 * @return string
	 * @since 7.1.2
	 */
	private static function format_time_slot( $raw, $format ) {
		$parts = explode( ' - ', $raw );
		if ( ! isset( $parts[1] ) ) {
			return date_i18n( $format, strtotime( $parts[0] ) );
		}
		return date_i18n( $format, strtotime( $parts[0] ) ) . ' - ' . date_i18n( $format, strtotime( $parts[1] ) );
	}
}
