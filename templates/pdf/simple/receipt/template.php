<?php
/**
 * Create Receipt PDF.
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
	do_action( 'wcdn_head_pdf', wcdn_get_template_type() );
	wcdn_rtl();
	?>
	</head>
	<?php
		$data = get_option( 'wcdn_receipt_customization' );
	?>
	<body>
		<div class="content">
			<div class="page-header">
			<?php
				if ( isset( $data['company_logo']['active'] ) ) {
					?>
					<div class="company-logo">
						<?php
						if ( wcdn_get_company_logo_id() ) :
							?>
							<?php wcdn_pdf_company_logo( $ttype = 'simple' ); ?>
						<?php endif; ?>
					</div>
				<?php } ?>
				<?php
				if ( isset( $data['document_setting']['active'] ) ) {
					$style = 'font-size:' . $data['document_setting']['document_setting_font_size'] . 'px; text-align:' . $data['document_setting']['document_setting_text_align'] . '; color:' . $data['document_setting']['document_setting_text_colour'] . ';';
					?>
					<div class="document-name cap">						
						<h1 style="<?php echo $style; // phpcs:ignore ?>">
							<?php echo esc_html( $data['document_setting']['document_setting_title'] ); ?>
						</h1>
					</div>
				<?php } ?>
			</div><!-- .page-header -->

			<div class="order-branding">
				<?php
				$com_setting = $data['company_name'];
				if ( isset( $com_setting['active'] ) ) {
					$style = 'text-align:' . $data['company_name']['company_name_text_align'] . ';color:' . $data['company_name']['company_name_text_colour'] . ';font-size:' . $data['company_name']['company_name_font_size'] . 'px;';
					?>
					<div class="company-info">
						<h3 class="company-name" style="<?php echo $style; // phpcs:ignore ?>"><?php wcdn_company_name(); ?></h3>
					</div>
				<?php } ?>
				<?php
				if ( isset( $data['company_address']['active'] ) ) {
					$style = 'text-align:' . $data['company_address']['company_address_text_align'] . ';color:' . $data['company_address']['company_address_text_colour'] . ';font-size:' . $data['company_address']['company_address_font_size'] . 'px;';
					?>
					<div class="company-address" style="<?php echo $style; // phpcs:ignore ?>">
						<?php wcdn_company_info(); ?>
					</div>
				<?php } ?>
				<?php do_action( 'wcdn_after_branding', $order ); ?>
			</div><!-- .order-branding -->

			<div class="order-addresses">
				<?php
				if ( isset( $data['billing_address']['active'] ) ) :
					$style = 'text-align:' . $data['billing_address']['billing_address_text_align'] . ';color:' . $data['billing_address']['billing_address_text_colour'] . ';';
					if ( ! empty( $data['billing_address']['billing_address_title'] ) ) {
						$blabel = $data['billing_address']['billing_address_title'];
					} else {
						$blabel = 'Billing Address';
					}
					?>
					<div class="billing-address" style="<?php echo $style; // phpcs:ignore ?>">
						<h3 class="cap">
							<?php esc_attr_e( $blabel, 'woocommerce-delivery-notes' ); // phpcs:ignore ?>
						</h3>
						<address>
							<?php
							if ( ! $order->get_formatted_billing_address() ) {
								esc_attr_e( 'N/A', 'woocommerce-delivery-notes' );
							} else {
								echo wp_kses_post( apply_filters( 'wcdn_address_billing', $order->get_formatted_billing_address(), $order ) );
								$wdn_order_billing_id    = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) ? $order->get_billing_email() : $order->billing_email;
								$wdn_order_billing_phone = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) ? $order->get_billing_phone() : $order->billing_phone;
								if ( $wdn_order_billing_phone ) {
									if ( isset( $data['phone_number']['active'] ) ) {
										echo '<br>';
										echo $wdn_order_billing_phone; // phpcs:ignore
									}
								}
								if ( $wdn_order_billing_id ) {
									if ( isset( $data['email_address']['active'] ) ) {
										echo '<br>';
										echo $wdn_order_billing_id; // phpcs:ignore
									}
								}
							}
							?>
						</address>
					</div>
				<?php endif; ?>

				<?php
				if ( isset( $data['shipping_address']['active'] ) ) :
					$style = 'text-align:' . $data['shipping_address']['shipping_address_text_align'] . ';color:' . $data['shipping_address']['shipping_address_text_colour'];
					if ( ! empty( $data['shipping_address']['shipping_address_title'] ) ) {
						$slabel = $data['shipping_address']['shipping_address_title'];
					} else {
						$slabel = 'Shipping Address';
					}
					?>
					<div class="shipping-address" style="<?php echo $style; // phpcs:ignore ?>">	
						<h3 class="cap">
							<?php esc_attr_e( $slabel, 'woocommerce-delivery-notes' );  // phpcs:ignore ?>
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
									if ( isset( $data['phone_number']['active'] ) ) {
										echo '<br>';
										echo $wdn_order_billing_phone; // phpcs:ignore
									}
								}
								if ( $wdn_order_billing_id ) {
									if ( isset( $data['email_address']['active'] ) ) {
										echo '<br>';
										echo $wdn_order_billing_id; // phpcs:ignore
									}
								}
							}
							?>
						</address>
					</div>
				<?php endif; ?>	

				<?php do_action( 'wcdn_after_addresses', $order ); ?>
			</div><!-- .order-addresses -->

			<div class="order-info">
				<?php
				if ( 'receipt' === $type && isset( $data['payment_received_stamp']['active'] ) ) {

					// Specify watermark text.
					$text = $data['payment_received_stamp']['payment_received_stamp_text'];
			
					?>
					<div class="order-stamp-container">
						<?php echo $text; ?>
					</div>
					<?php
				} 
				?>
				<ul class="info-list">
					<?php
					$fields = apply_filters( 'wcdn_order_info_fields', wcdn_get_order_info( $order, 'receipt' ), $order );
					?>
					<?php
					foreach ( $fields as $field ) :
						if ( isset( $field['active'] ) && 'yes' === $field['active'] ) {
							if ( isset( $field['font-size'] ) ) {
								$labelstyle = 'font-size:' . $field['font-size'] . 'px;';
							}
							if ( isset( $field['color'] ) ) {
								$labelstyle .= 'color:' . $field['color'] . ';';
							}
							if ( isset( $field['font-weight'] ) ) {
								$labelstyle .= 'font-weight:' . $field['font-weight'] . ';';
							}
							?>
							<li>
								<strong style="<?php echo $labelstyle; // phpcs:ignore ?>"><?php echo wp_kses_post( apply_filters( 'wcdn_order_info_name', $field['label'], $field ) ); ?></strong>
								<strong style="<?php echo $labelstyle; // phpcs:ignore ?>"><?php echo wp_kses_post( apply_filters( 'wcdn_order_info_content', $field['value'], $field ) ); ?></strong>
							</li>
						<?php } ?>
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
							<?php endforeach; ?>
						<?php endif; ?>
					</tfoot>
				</table>

				<?php do_action( 'wcdn_after_items', $order ); ?>
			</div><!-- .order-items -->

			<?php
			if ( isset( $data['customer_note']['active'] ) ) :
				$style = 'font-size:' . $data['customer_note']['customer_note_font_size'] . 'px;color:' . $data['customer_note']['customer_note_text_colour'] . ';';
				if ( ! empty( $data['customer_note']['customer_note_title'] ) ) {
					$clabel = $data['customer_note']['customer_note_title'];
				} else {
					$clabel = 'Customer Note';
				}
				?>
				<div class="order-notes" style="<?php echo $style; // phpcs:ignore ?>">
					<?php if ( wcdn_has_customer_notes( $order ) ) : ?>
						<h4><?php esc_attr_e( $clabel, 'woocommerce-delivery-notes' ); // phpcs:ignore ?></h4>
						<?php wcdn_customer_notes( $order ); ?>
					<?php endif; ?>

					<?php do_action( 'wcdn_after_notes', $order ); ?>
				</div><!-- .order-notes -->
			<?php endif; ?>

			<div class="order-thanks">
				<?php
				if ( isset( $data['complimentary_close']['active'] ) ) {
					?>
					<style>
						.order-thanks p {
							color: <?php echo $data['complimentary_close']['complimentary_close_text_colour']; ?>;
							font-size: <?php echo $data['complimentary_close']['complimentary_close_font_size']; ?>;
						}
					</style>
					<div class="personal_note">
						<?php wcdn_personal_notes(); ?>
						<?php do_action( 'wcdn_after_thanks', $order ); ?>
					</div><!-- .order-thanks -->
					<?php
				}
				?>

				<?php
				if ( isset( $data['policies']['active'] ) ) {
					?>
					<style>
						.colophon-policies p {
							color: <?php echo $data['policies']['policies_text_colour']; ?>;
							font-size: <?php echo $data['policies']['policies_font_size']; ?>;
						}
					</style>
					<div class="colophon-policies">
						<?php wcdn_policies_conditions(); ?>
					</div>
					<?php
				}
				?>
			</div><!-- .order-thanks -->

			<div class="order-colophon">
				<?php
				if ( isset( $data['footer']['active'] ) ) {
					$style = 'font-size:' . $data['footer']['footer_font_size'] . 'px;color:' . $data['footer']['footer_text_colour'] . ';';
					?>
					<div class="colophon-imprint" style="<?php echo $style; // phpcs:ignore ?>">
						<?php wcdn_imprint(); ?>
					</div>
				<?php } ?>	

				<?php do_action( 'wcdn_after_colophon', $order ); ?>
			</div><!-- .order-colophon -->
		</div>
	</body>
</html>