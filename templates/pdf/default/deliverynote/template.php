<?php
/**
 * Create default Delivery note PDF.
 *
 * @package WooCommerce Print Invoice & Delivery Note/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<html>
	<head>
	<?php
	wcdn_rtl();
	?>
	</head>
	<body>
		<div class="content">
			<div class="page-header">
				<div class="company-logo">
					<?php
					if ( wcdn_get_company_logo_id() ) :
						?>
						<?php wcdn_pdf_company_logo(); ?><?php endif; ?>
				</div>
				<div class="document-name cap">						
					<h1>Delivery Note</h1>
				</div>
			</div>

			<div class="order-branding">
				<div class="company-info">
					<h3 class="company-name"><?php wcdn_company_name(); ?></h3>
					<div class="company-address"><?php wcdn_company_info(); ?></div>
				</div>

				<?php do_action( 'wcdn_after_branding', $order ); ?>
			</div><!-- .order-branding -->

			<div class="order-addresses">
				<div class="billing-address">
					<h3 class="cap"><?php esc_attr_e( 'Billing Address', 'woocommerce-delivery-notes' ); ?></h3>
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

				<div class="shipping-address">						
					<h3 class="cap"><?php esc_attr_e( 'Shipping Address', 'woocommerce-delivery-notes' ); ?></h3>
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
			
			<div class="order-info">
				<ul class="info-list">
					<?php
					$fields = apply_filters( 'wcdn_order_info_fields', wcdn_get_order_info( $order ), $order );
					?>
					<?php
					foreach ( $fields as $field ) :
						$nodisplay = array( 'Payment Method', 'Email', 'Telephone' );
						if ( ! in_array( $field['label'], $nodisplay, true ) ) {
							?>
							<li>
								<strong><?php echo wp_kses_post( apply_filters( 'wcdn_order_info_name', $field['label'], $field ) ); ?></strong>
								<strong><?php echo wp_kses_post( apply_filters( 'wcdn_order_info_content', $field['value'], $field ) ); ?></strong>
							</li>
							<?php
						}
					endforeach;
					?>
				</ul>

				<?php do_action( 'wcdn_after_info', $order ); ?>
			</div><!-- .order-info -->
			
			<div class="order-items">
				<table>
					<thead>
						<tr>
							<th class="head-name"><span><?php esc_attr_e( 'Product', 'woocommerce-delivery-notes' ); ?></span></th>
							<th class="head-quantity"><span><?php esc_attr_e( 'Quantity', 'woocommerce-delivery-notes' ); ?></span></th>
						</tr>
					</thead>

					<tbody>
						<?php
						if ( count( $order->get_items() ) > 0 ) :
							?>
							<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
								<tr>
									<td class="product-name">
										<?php echo $item->get_name(); ?>
									</td>
									<td class="product-quantity">
										<span><?php echo esc_attr( apply_filters( 'wcdn_order_item_quantity', $item['qty'], $item ) ); ?></span>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

				<?php do_action( 'wcdn_after_items', $order ); ?>
			</div><!-- .order-items -->

			<div class="order-notes">
				<?php if ( wcdn_has_customer_notes( $order ) ) : ?>
					<h4><?php esc_attr_e( 'Customer Note', 'woocommerce-delivery-notes' ); ?></h4>
					<?php wcdn_customer_notes( $order ); ?>
				<?php endif; ?>

				<?php do_action( 'wcdn_after_notes', $order ); ?>
			</div><!-- .order-notes -->

			<div class="order-thanks">
				<!-- Complimentary Close -->
				<?php wcdn_personal_notes(); ?>

				<?php do_action( 'wcdn_after_thanks', $order ); ?>
			</div><!-- .order-thanks -->

			<div class="order-colophon">
				<div class="colophon-policies">
					<!-- Shop Policy -->
					<?php wcdn_policies_conditions(); ?>
				</div>

				<div class="colophon-imprint">
					<!-- Shop footer -->
					<?php wcdn_imprint(); ?>
				</div>	

				<?php do_action( 'wcdn_after_colophon', $order ); ?>
			</div><!-- .order-colophon -->
		</div>
	</body>
</html>
