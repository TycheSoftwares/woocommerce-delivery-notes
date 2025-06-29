<?php
/**
 * Create live preview for Default template.
 *
 * @package WooCommerce Print Invoice & Delivery Note/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php
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

	<div class="order-brandings">
		<div class="company-logo">
			<?php
			if ( wcdn_get_company_logo_id() ) :
				?>
				<?php wcdn_company_logo(); ?><?php endif; ?>
		</div>

		<div class="company-info">
			<?php
			if ( ! wcdn_get_company_logo_id() ) :
				?>
				<h1 class="company-name"><?php wcdn_company_name(); ?></h1><?php endif; ?>
			<div class="company-address"><?php wcdn_company_info(); ?></div>
		</div>

		<?php do_action( 'wcdn_after_branding', $order ); ?>
	</div><!-- .order-branding -->

	<div class="order-addresses">
		<div class="billing-addresss">
			<h3><?php esc_attr_e( 'Billing Address', 'woocommerce-delivery-notes' ); ?></h3>
			<address>

				<?php
				if ( ! $order->get_formatted_billing_address() ) {
					esc_attr_e( 'N/A', 'woocommerce-delivery-notes' );
				} else {
					echo wp_kses_post( apply_filters( 'wcdn_address_billing', $order->get_formatted_billing_address(), $order ) );
				}
				?>

			</address>
		</div>

		<div class="shipping-addresss">						
		<h3><?php echo esc_html( apply_filters( 'wcdn_address_shipping_title', esc_attr__( 'Shipping Address', 'woocommerce-delivery-notes' ), $order ) ); ?></h3>
			<address>

				<?php
				if ( ! $order->get_formatted_shipping_address() ) {
					esc_attr_e( 'N/A', 'woocommerce-delivery-notes' );
				} else {
					echo wp_kses_post( apply_filters( 'wcdn_address_shipping', $order->get_formatted_shipping_address(), $order ) );
				}
				?>

			</address>
		</div>

		<?php do_action( 'wcdn_after_addresses', $order ); ?>
	</div><!-- .order-addresses -->


	<div class="order-infoo">
		<?php
		$template_save = get_option( 'wcdn_template_type' );
		$setting       = htmlspecialchars( $_GET['wdcn_setting'] ); // phpcs:ignore

		if ( 'default' === $template_save ) {
			if ( 'wcdn_invoice' === $setting ) {
				?>
				<h2>Invoice</h2>
				<?php
			} elseif ( 'wcdn_receipt' === $setting ) {
				?>
				<h2>Receipt</h2>
				<?php
			} elseif ( 'wcdn_deliverynote' === $setting ) {
				?>
				<h2>Delivery Note</h2>
				<?php
			}
		}
		?>
			<ul class="info-list">
				<?php
				$fields = apply_filters( 'wcdn_order_info_fields', wcdn_get_order_info( $order ), $order );
				if ( 'default' === $template_save ) {
					if ( 'wcdn_receipt' === $setting || 'wcdn_deliverynote' === $setting ) {
						unset( $fields['invoice_number'] );
					}
				}
				?>
				<?php foreach ( $fields as $field ) : ?>
					<li>
						<strong><?php echo wp_kses_post( apply_filters( 'wcdn_order_info_name', $field['label'], $field ) ); ?></strong>
						<span><?php echo wp_kses_post( apply_filters( 'wcdn_order_info_content', $field['value'], $field ) ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php do_action( 'wcdn_after_info', $order ); ?>
	</div><!-- .order-info -->


	<div class="order-items">
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
						$adjusted_qty = wcdn_get_adjusted_quantity( $order, $item_id );
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
								<?php wcdn_get_product_name( $product, $order, $item ); ?>
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

					foreach ( $totals_arr as $total ) :
						$modified_label = apply_filters( 'wcdn_invoice_order_total_label', $total['label'], $order );
						?>
						<tr>
							<td class="total-name"><span><?php echo wp_kses_post( $modified_label ); ?></span></td>
							<td class="total-item-price"></td>
							<?php if ( 'Total' === $total['label'] ) { ?>
							<td class="total-quantity"><?php echo wp_kses_post( $total_adjusted_quantity ); ?></td>
							<?php } else {  ?>
							<td class="total-quantity"></td>
							<?php } ?>
							<td class="total-price"><span><?php echo wp_kses_post( $total['value'] ); ?></span></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tfoot>
		</table>

		<?php do_action( 'wcdn_after_items', $order ); ?>
	</div><!-- .order-items -->

	<div class="order-notess">
		<?php if ( wcdn_has_customer_notes( $order ) ) : ?>
			<h4><?php esc_attr_e( 'Customer Note', 'woocommerce-delivery-notes' ); ?></h4>
			<h5><?php wcdn_customer_notes( $order ); ?></h5>
		<?php endif; ?>

		<?php do_action( 'wcdn_after_notes', $order ); ?>
	</div><!-- .order-notes -->

	<div class="order-thankss">
		<?php wcdn_personal_notes(); ?>

		<?php do_action( 'wcdn_after_thanks', $order ); ?>
	</div><!-- .order-thanks -->

	<div class="order-colophonn">
		<div class="colophon-policiess">
			<?php wcdn_policies_conditions(); ?>
		</div>

		<div class="colophon-imprintt">
			<?php wcdn_imprint(); ?>
		</div>	

		<?php do_action( 'wcdn_after_colophon', $order ); ?>
	</div><!-- .order-colophon -->

