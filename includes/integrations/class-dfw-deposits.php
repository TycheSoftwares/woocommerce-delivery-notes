<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Deposits For WooCommerce Integration.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Integrations
 * @category    Classes
 * @since       7.1.3
 */

namespace Tyche\WCDN\Integrations;

defined( 'ABSPATH' ) || exit;

/**
 * DFW Deposits integration class.
 *
 * Adds Deposit and Future Payment rows to WCDN document totals
 * when the Deposits For WooCommerce plugin is active.
 *
 * @since 7.1.2
 */
class DFW_Deposits {

	/**
	 * Constructor.
	 *
	 * @since 7.1.2
	 */
	public function __construct() {
		add_filter( 'wcdn_order_totals', array( $this, 'append_deposit_totals' ), 10, 3 );
	}

	/**
	 * Append Deposit, Future Payment, and Total Cart Amount rows to the totals array,
	 * mirroring the values shown on the Edit Order page.
	 *
	 * Handles two DFW deposit flows:
	 *  - Order-level (cart deposit): reads _deposit and _future_payments order meta.
	 *  - Item-level (per-product deposit): sums full_amount − line_subtotal per deposit item
	 *    and computes the total cart amount (full_amount + non-deposit subtotals + shipping − discounts).
	 *
	 * @param array          $totals    Existing totals array.
	 * @param \WC_Order|null $wc_order  Order object.
	 * @param string         $_template Template key — required by hook signature, not used here.
	 * @return array
	 */
	public function append_deposit_totals( $totals, $wc_order, $_template ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- required by wcdn_order_totals filter signature.
		if ( ! $wc_order instanceof \WC_Order ) {
			return $totals;
		}

		$currency = $wc_order->get_currency();

		// Order-level deposit (cart deposit flow saves _has_order_deposit meta).
		if ( \DFW_Manage_Orders::dfw_has_order_deposit( $wc_order ) ) {
			$deposit = $wc_order->get_meta( '_deposit' );
			$future  = $wc_order->get_meta( '_future_payments' );

			if ( is_numeric( $deposit ) && $deposit > 0 ) {
				$totals['dfw_deposit'] = wc_price( $deposit, array( 'currency' => $currency ) );
			}

			if ( is_numeric( $future ) && $future > 0 ) {
				$totals['dfw_future_payment'] = wc_price( $future, array( 'currency' => $currency ) );
			}

			return $totals;
		}

		// Item-level deposit (per-product deposit flow stores has_deposit on each item).
		if ( \DFW_Manage_Orders::has_deposit( $wc_order ) ) {
			$remaining  = 0.0;
			$total_cart = 0.0;

			foreach ( $wc_order->get_items() as $item ) {
				if ( ! empty( $item['has_deposit'] ) && isset( $item['full_amount'] ) ) {
					$full        = floatval( $item['full_amount'] );
					$remaining  += $full - $wc_order->get_line_subtotal( $item, false );
					$total_cart += $full;
				} else {
					$total_cart += $wc_order->get_line_subtotal( $item, false );
				}
			}

			$total_cart += $wc_order->get_shipping_total();
			$total_cart -= $wc_order->get_discount_total();

			if ( $remaining > 0 ) {
				$totals['dfw_future_payment']    = wc_price( $remaining, array( 'currency' => $currency ) );
				$totals['dfw_total_cart_amount'] = wc_price( $total_cart, array( 'currency' => $currency ) );
			}
		}

		return $totals;
	}
}
