<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Integration with Order Delivery Date (ORDD) plugin.
 *
 * ORDD stopped registering the wcdn_order_info_fields hook itself in its
 * newer versions, so WCDN now owns this bridge to keep delivery date,
 * time slot, and location fields showing on invoices and delivery notes.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Integrations
 * @category    Classes
 * @since       7.x
 */

namespace Tyche\WCDN\Integrations;

defined( 'ABSPATH' ) || exit;

/**
 * Orddd_Integration class.
 *
 * @since 7.x
 */
class Orddd_Integration {

	/**
	 * Constructor. Hooks into WCDN's order info fields filter.
	 *
	 * @since 7.x
	 */
	public function __construct() {
		add_filter( 'wcdn_order_info_fields', array( $this, 'add_delivery_fields' ), 10, 2 );
	}

	/**
	 * Add ORDD delivery date, time slot, and location fields to WCDN's
	 * order info block.
	 *
	 * @param array     $fields Existing extra fields.
	 * @param \WC_Order $order  Order object.
	 * @return array
	 * @since 7.x
	 */
	public function add_delivery_fields( $fields, $order ) {

		$order_id   = $order->get_id();
		$new_fields = array();

		// Delivery location, if ORDD's location field is configured.
		$locations_label = get_option( 'orddd_location_field_label' );

		if ( ! empty( $locations_label ) ) {
			$address = $order->get_meta( $locations_label, true );
			if ( '' !== $address ) {
				$new_fields[ sanitize_key( $locations_label ) ] = array(
					'label' => $locations_label,
					'value' => $address,
				);
			}
		}

		// Delivery date.
		if ( method_exists( 'orddd_common', 'orddd_get_order_delivery_date' ) ) {

			$delivery_date_formatted = \orddd_common::orddd_get_order_delivery_date( $order_id );

			$delivery_date_label = class_exists( 'orddd_custom_delivery_functions' )
				&& method_exists( 'orddd_custom_delivery_functions', 'orddd_fetch_delivery_date_field_label' )
				? \orddd_custom_delivery_functions::orddd_fetch_delivery_date_field_label( $order_id )
				: __( 'Delivery Date', 'woocommerce-delivery-notes' );

			if ( '' !== $delivery_date_formatted ) {
				$new_fields['orddd_delivery_date'] = array(
					'label' => $delivery_date_label,
					'value' => $delivery_date_formatted,
				);
			}
		}

		// Time slot.
		if ( method_exists( 'orddd_common', 'orddd_get_order_timeslot' )
			&& class_exists( 'orddd_custom_delivery_functions' )
			&& method_exists( 'orddd_custom_delivery_functions', 'orddd_fetch_time_slot_field_label' )
		) {

			$time_slot_label = \orddd_custom_delivery_functions::orddd_fetch_time_slot_field_label( $order_id );
			$time_slot        = \orddd_common::orddd_get_order_timeslot( $order_id );

			if ( '' !== $time_slot_label && '' !== $time_slot ) {
				$new_fields['orddd_time_slot'] = array(
					'label' => $time_slot_label,
					'value' => $time_slot,
				);
			}
		}

		/**
		 * Filter the ORDD delivery fields before merging into WCDN's order info block.
		 *
		 * @param array     $new_fields Fields sourced from ORDD.
		 * @param \WC_Order $order      Order object.
		 * @since 7.x
		 */
		$new_fields = apply_filters( 'wcdn_orddd_delivery_fields', $new_fields, $order );

		return array_merge( $fields, $new_fields );
	}
}
