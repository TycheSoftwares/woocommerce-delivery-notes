<?php
/**
 * Create live preview for deliverynote..
 *
 * @package WooCommerce Print Invoice & Delivery Note/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php
$data            = get_option( 'wcdn_receipt_customization' );
$orders_to_check = 10;
$orders_checked  = 0;
$parent_order    = null;
while ( $orders_checked < $orders_to_check && is_null( $parent_order ) ) {
		$orders = wc_get_orders(
			array(
				'limit'   => 1,
				'orderby' => 'date',
				'order'   => 'DESC',
				'offset'  => $orders_checked,
			)
		);
	if ( ! empty( $orders ) ) {
		$order = reset($orders); // phpcs:ignore
		if ( $order->get_parent_id() === 0 ) {
			$parent_order = $order;
		}
	}
		$orders_checked++;
}
if ( is_null( $parent_order ) ) {
	echo '<div class="notices">No WooCommerce orders found! Please consider adding your first order to see this preview.</div>';
	return;
}
?>
	<div class="page-header">
		<div class="company-logo" v-show="receipt.company_logo" >
			<?php
			if ( wcdn_get_company_logo_id() ) :
				?>
			<div>
				<?php wcdn_pdf_company_logo( $ttype = 'simple' ); // phpcs:ignore ?>
			</div>
			<?php endif; ?>
		</div>
		<div class="document-name cap" v-show="receipt.document_setting" >
			<h1 
				:style="{ fontSize: receipt.document_setting_font_size + 'px', textAlign: receipt.document_setting_text_align, color: receipt.document_setting_text_colour }">
				{{ receipt.document_setting_title }}
			</h1>
		</div>
	</div>

	<div class="order-branding">
		<div class="company-info" >
			<h3 class="company-name" v-show="receipt.company_name" :style="{ textAlign: receipt.company_name_text_align, fontSize: receipt.company_name_font_size + 'px', color: receipt.company_name_text_colour }"><?php wcdn_company_name(); ?></h3>
		</div>
		<div class="company-address footer-content" v-show="receipt.company_address" :style="{ textAlign: receipt.company_address_text_align, fontSize: receipt.company_address_font_size + 'px', color: receipt.company_address_text_colour }" >
			<?php wcdn_company_info(); ?>
		</div>
	</div><!-- .order-branding -->

	<div class="order-addresses">
		<div class="billing-address" v-show="receipt.billing_address" :style="{ textAlign: receipt.billing_address_text_align, color: receipt.billing_address_text_colour }">
			<h3 class="cap" :style="{ text: receipt.billing_address_title_style }">
				{{ receipt.billing_address_title }}
			</h3>
			<address >
				<?php
				if ( ! $order->get_formatted_billing_address() ) {
					esc_attr_e( 'N/A', 'woocommerce-delivery-notes' );
				} else {
					echo wp_kses_post( apply_filters( 'wcdn_address_billing', $order->get_formatted_billing_address(), $order ) );
					$wdn_order_billing_id    = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) ? $order->get_billing_email() : $order->billing_email;
					$wdn_order_billing_phone = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) ? $order->get_billing_phone() : $order->billing_phone;
					if ( $wdn_order_billing_phone ) {
						echo '<br>';
						?>
						<span v-show="receipt.phone_number">
							<?php echo $wdn_order_billing_phone; // phpcs:ignore ?>
						</span>
						<?php
					}
					if ( $wdn_order_billing_id ) {
						echo '<br>';
						?>
						<span v-show="receipt.email_address">
							<?php echo $wdn_order_billing_id; // phpcs:ignore ?>
						</span>
						<?php
					}
				}
				?>
			</address>
		</div>

		<div class="shipping-address" v-show="receipt.shipping_address" :style="{ textAlign: receipt.shipping_address_text_align, color: receipt.shipping_address_text_colour }">
			<h3 class="cap" :style="{ text: receipt.billing_address_title_style }">
				{{ receipt.shipping_address_title }}
			</h3>
			<address>
				<?php
				if ( ! $order->get_formatted_shipping_address() ) {
					esc_attr_e( 'N/A', 'woocommerce-delivery-notes' );
				} else {
					echo wp_kses_post( apply_filters( 'wcdn_address_shipping', $order->get_formatted_shipping_address(), $order ) );
					$wdn_order_billing_id    = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) ? $order->get_billing_email() : $order->billing_email;
					$wdn_order_billing_phone = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) ? $order->get_billing_phone() : $order->billing_phone;
					if ( $wdn_order_billing_phone ) {
						echo '<br>';
						?>
						<span v-show="receipt.phone_number">
							<?php echo $wdn_order_billing_phone; // phpcs:ignore ?>
						</span>
						<?php
					}
					if ( $wdn_order_billing_id ) {
						echo '<br>';
						?>
						<span v-show="receipt.email_address">
							<?php echo $wdn_order_billing_id; // phpcs:ignore ?>
						</span>
						<?php
					}
				}
				?>
			</address>
		</div>
	</div><!-- .order-addresses -->

	<div class="order-info">
		<ul class="info-list">
			<?php
			$invoice_number = $order->get_meta( '_wcdn_invoice_number' );
			if ( empty( $invoice_number ) ) {
				$invoice_number = get_option( 'wcdn_invoice_number_count' );
			}
			$order_number           = $order->get_order_number();
			$order_date             = $order->get_date_created()->format( 'F j, Y' );
			$payment_method         = $order->get_payment_method();
			$date_formate           = get_option( 'date_format' );
			$wdn_order_payment_date = $order->get_date_paid();
			if ( $wdn_order_payment_date ) {
				$payment_date_formatted = $wdn_order_payment_date->date( $date_formate );
				$payment_date           = __( $payment_date_formatted, 'woocommerce' ); // phpcs:ignore
			} else {
				$payment_date = $order->get_date_created()->format( 'F j, Y' );
			}
			?>
			<div class="invoice-number" v-show="receipt.invoice_number" :style="{ text: receipt.invoice_number_text, fontWeight: receipt.invoice_number_style, color: receipt.invoice_number_text_colour, fontSize: receipt.invoice_number_font_size + 'px' }">
				<li>
					<span>{{ receipt.invoice_number_text }}</span>
					<span><?php echo wp_kses_post( $invoice_number ); ?></span>
				</li>
			</div>

			<div class="order-number" v-show="receipt.order_number" :style="{ text: receipt.order_number_text, fontWeight: receipt.order_number_style, color: receipt.order_number_text_colour, fontSize: receipt.order_number_font_size + 'px' }">
				<li>
					<span> {{receipt.order_number_text}} </span>
					<span><?php echo wp_kses_post( $order_number ); ?></span>
				</li>
			</div>

			<div class="order-date" v-show="receipt.order_date" :style="{ text: receipt.order_date_text, fontWeight: receipt.order_date_style, color: receipt.order_date_text_colour, fontSize: receipt.order_date_font_size + 'px' }">
				<li>
					<span>{{ receipt.order_date_text }}</span>
					<span><?php echo wp_kses_post( $order_date ); ?></span>
				</li>
			</div>

			<div class="payment-method" v-show="receipt.payment_method" :style="{ text: receipt.payment_method_text, fontWeight: receipt.payment_method_style, color: receipt.payment_method_text_colour, fontSize: receipt.payment_method_font_size + 'px' }">
				<li>
					<span>{{ receipt.payment_method_text }}</span>
					<span><?php echo wp_kses_post( $payment_method ); ?></span>
				</li>
			</div>
			<div class="payment-date" v-show="receipt.payment_date" :style="{ text: receipt.payment_date_text, fontWeight: receipt.payment_date_style, color: receipt.payment_date_text_colour, fontSize: receipt.payment_date_font_size + 'px' }">
				<li>
					<span>{{ receipt.payment_date_text }}</span>
					<span><?php echo wp_kses_post( $payment_date ); ?></span>
				</li>
			</div>
		</ul>
	</div><!-- .order-info -->

	<div class="order-items">
		<div class="order-stamp-container" v-show="receipt.payment_received_stamp">
			{{ receipt.payment_received_stamp_text }}
		</div>
		<table>
			<thead>
				<tr>
					<th class="head-name"><span><?php esc_attr_e( 'Product', 'woocommerce-delivery-notes' ); ?></span></th>
					<th class="head-item-price"><span><?php esc_attr_e( 'Price', 'woocommerce-delivery-notes' ); ?></span></th>
					<th class="head-quantity"><span><?php esc_attr_e( 'Quantity', 'woocommerce-delivery-notes' ); ?></span></th>
					<th class="head-price"><span><?php esc_attr_e( 'Total', 'woocommerce-delivery-notes' ); ?></span></th>
				</tr>
			</thead>

			<tbody>
				<?php

				if ( count( $order->get_items() ) > 0 ) :
					$total_adjusted_quantity = 0;
					?>
					<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
						<?php
						$product = apply_filters( 'wcdn_order_item_product', $item->get_product(), $item );
						if ( ! $product ) {
							continue;
						}
						// Call the function to get the adjusted quantity.
						$adjusted_qty = get_adjusted_quantity( $order, $item_id );
						if ( $adjusted_qty > 0 ) {
							$total_adjusted_quantity += $adjusted_qty;
						} else {
							continue;
						}

						if ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) {
							$item_meta = new WC_Order_Item_Product( $item['item_meta'], $product );
						} else {
							$item_meta = new WC_Order_Item_Meta( $item['item_meta'], $product );
						}
						?>
						<tr>
							<td class="product-name">
								<?php do_action( 'wcdn_order_item_before', $product, $order, $item ); ?>
								<?php get_product_name( $product, $order, $item ); ?>
								<?php do_action( 'wcdn_order_item_after', $product, $order, $item ); ?>
							</td>
							<td class="product-item-price">
								<span><?php echo wp_kses_post( wcdn_get_formatted_item_price( $order, $item ) ); ?></span>
							</td>
							<td class="product-quantity">
								<span><?php echo esc_attr( apply_filters( 'wcdn_order_item_quantity', $adjusted_qty, $item ) ); ?></span>
							</td>
							<td class="product-price">
								<span><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></span>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>

			<tfoot>
				<?php
				$totals_arr = $order->get_order_item_totals();
				if ( $totals_arr ) :

					foreach ( $totals_arr as $key => $total ) :
						if ( 'payment_method' !== $key ) :
							?>
						<tr>
							<td class="total-name"><span><?php echo wp_kses_post( $total['label'] ); ?></span></td>
							<td class="total-item-price"></td>
							<?php if ( 'Total' === $total['label'] ) { ?>
							<td class="total-quantity"><?php echo wp_kses_post( $total_adjusted_quantity ); ?></td>
							<?php } else { ?>
							<td class="total-quantity"></td>
							<?php } ?>
							<td class="total-price"><span><?php echo wp_kses_post( $total['value'] ); ?></span></td>
						</tr>
							<?php
						endif;
					endforeach;
				endif;
				?>
			</tfoot>
		</table>
		<?php do_action( 'wcdn_after_items', $order ); ?>
	</div><!-- .order-items -->

		<div class="order-notes footer-content" v-show="receipt.customer_note" :style="{ fontSize: receipt.customer_note_font_size + 'px', color: receipt.customer_note_text_colour }">
			<?php if ( wcdn_has_customer_notes( $order ) ) : ?>	
				<h4 class="cap" :style="{ text: receipt.customer_note_title, fontSize: receipt.customer_note_font_size + 'px', color: receipt.customer_note_text_colour }">
				{{ receipt.customer_note_title }}
			</h4>
				<?php wcdn_customer_notes( $order ); ?>
			<?php endif; ?>

			<?php do_action( 'wcdn_after_notes', $order ); ?>
		</div><!-- .order-notes -->

		<div class="order-thanks">
				<div class="personal_note footer-content" v-show="receipt.complimentary_close" :style="{ fontSize: receipt.complimentary_close_font_size + 'px', color: receipt.complimentary_close_text_colour }"> 
					<?php wcdn_personal_notes(); ?>
					<?php do_action( 'wcdn_after_thanks', $order ); ?>
				</div><!-- .order-thanks -->

				<div class="colophon-policies footer-content" v-show="receipt.policies" :style="{ fontSize: receipt.policies_font_size + 'px', color: receipt.policies_text_colour }">
					<?php wcdn_policies_conditions(); ?>
				</div>
		</div><!-- .order-thanks -->

		<div class="order-colophon">
				<div class="colophon-imprint footer-content" v-show="receipt.footer" :style="{ fontSize: receipt.footer_font_size + 'px', color: receipt.footer_text_colour }">
					<?php wcdn_imprint(); ?>
				</div>
			<?php do_action( 'wcdn_after_colophon', $order ); ?>
		</div><!-- .order-colophon -->