<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * WooCommerce Local Pickup Plus Integration.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Integrations
 * @category    Classes
 * @since       7.1.3
 */

namespace Tyche\WCDN\Integrations;

defined( 'ABSPATH' ) || exit;

/**
 * Local Pickup Plus integration class.
 *
 * Adds pickup location details (name, address, phone, appointment)
 * to the WCDN order info fields section for orders using the
 * WooCommerce Local Pickup Plus shipping method (v2.0+).
 *
 * @since 7.1.2
 */
class Local_Pickup_Plus {

	/**
	 * Constructor.
	 *
	 * @since 7.1.2
	 */
	public function __construct() {
		add_filter( 'wcdn_order_info_fields', array( $this, 'append_pickup_fields' ), 10, 2 );
	}

	/**
	 * Append pickup location details to the order info fields.
	 *
	 * Uses the plugin's own Orders handler to retrieve pre-formatted
	 * pickup data and maps each label/value pair into the WCDN fields array.
	 * Supports multiple pickup packages via key suffixes.
	 *
	 * @param array     $fields Order info fields.
	 * @param \WC_Order $order  Order object.
	 * @return array
	 * @since 7.1.2
	 */
	public function append_pickup_fields( $fields, $order ) {

		if ( ! $order instanceof \WC_Order || ! class_exists( 'WC_Local_Pickup_Plus_Orders' ) ) {
			return $fields;
		}

		$pickup_handler   = new \WC_Local_Pickup_Plus_Orders();
		$pickup_locations = $pickup_handler->get_order_pickup_data( $order );

		if ( empty( $pickup_locations ) ) {
			return $fields;
		}

		$package_index = 0;

		foreach ( $pickup_locations as $pickup_meta ) {
			$suffix = $package_index > 0 ? '_' . $package_index : '';
			++$package_index;

			foreach ( $pickup_meta as $label => $value ) {
				$key            = 'pickup_' . sanitize_key( $label ) . $suffix;
				$fields[ $key ] = array(
					'label' => $label,
					'value' => wp_strip_all_tags( $value ),
				);
			}
		}

		return $fields;
	}
}
