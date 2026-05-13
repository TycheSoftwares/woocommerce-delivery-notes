<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Deposits & Partial Payments for WooCommerce Pro Integration.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Integrations
 * @category    Classes
 * @since       7.1.3
 */

namespace Tyche\WCDN\Integrations;

defined( 'ABSPATH' ) || exit;

/**
 * Deposits integration class.
 *
 * Adds Deposit and Future Payments rows to WCDN document totals
 * when the Deposits & Partial Payments for WooCommerce Pro plugin is active.
 *
 * @since 7.1.2
 */
class Deposits_Partial_Payments {

	/**
	 * Constructor.
	 *
	 * @since 7.1.2
	 */
	public function __construct() {
		add_filter( 'wcdn_order_totals', array( $this, 'append_deposit_totals' ), 10, 3 );
	}

	/**
	 * Whether a WC_Order has deposit data from the awcdp plugin.
	 *
	 * @param \WC_Order $wc_order Order object.
	 * @return bool
	 */
	private function has_deposit( $wc_order ) {
		return 'yes' === $wc_order->get_meta( '_awcdp_deposits_order_has_deposit', true );
	}

	/**
	 * Append Deposit and Future Payments rows to the totals array,
	 * mirroring the values shown on the Edit Order page.
	 *
	 * @param array          $totals    Existing totals array.
	 * @param \WC_Order|null $wc_order  Order object.
	 * @param string         $_template Template key — required by hook signature, not used here.
	 * @return array
	 */
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- required by wcdn_order_totals filter signature.
	public function append_deposit_totals( $totals, $wc_order, $_template ) {
		if ( ! $wc_order instanceof \WC_Order ) {
			return $totals;
		}

		if ( ! $this->has_deposit( $wc_order ) ) {
			return $totals;
		}

		$currency = $wc_order->get_currency();
		$deposit  = floatval( $wc_order->get_meta( '_awcdp_deposits_deposit_amount', true ) );

		// _awcdp_deposits_second_payment is the scheduled future-payment total written at checkout
		// and is always persisted. awcdp_deposits_balance_amount is updated post-save via a hook
		// without a follow-up save(), so it can hold a stale full-order-total value.
		$balance = floatval( $wc_order->get_meta( '_awcdp_deposits_second_payment', true ) );

		if ( $deposit > 0 ) {
			$totals['awcdp_deposit'] = wc_price( $deposit, array( 'currency' => $currency ) );
		}

		if ( $balance > 0 ) {
			$totals['awcdp_future_payments'] = wc_price( $balance, array( 'currency' => $currency ) );
		}

		// Remove any "Partial Payment for order X" fee lines AWCDP added to the main order —
		// they duplicate the deposit/balance rows we just added above.
		if ( ! empty( $totals['fee_lines'] ) ) {
			$totals['fee_lines'] = array_values(
				array_filter(
					$totals['fee_lines'],
					function ( $fee ) {
						return 0 !== strpos( $fee['label'], 'Partial Payment for order' );
					}
				)
			);
		}

		return $totals;
	}

}
