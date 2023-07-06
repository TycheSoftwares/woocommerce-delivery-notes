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
	<?php
		$data = get_option( 'wcdn_receipt_customization' );
	?>
	<body>
		<div class="content">
			<div class="page-header">
				<?php 
					if ( isset( $data['company_setting']['active'] ) && $data['company_setting']['company_setting_display'] === 'company_logo' ) { ?>
						<div class="company-logo">
							<?php
							if ( wcdn_get_company_logo_id() ) : ?>
								<?php wcdn_pdf_company_logo(); ?>
							<?php endif; ?>
						</div>
				<?php } ?>
				<?php
					if ( isset( $data['document_setting']['active'] ) ) {
						$style = 'font-size:' . $data['document_setting']['document_setting_font_size'] . 'px;text-align:' . $data['document_setting']['document_setting_text_align']. ';color:' . $data['document_setting']['document_setting_text_colour'];
						?>
						<div class="document-name cap">						
							<h1 style="<?php echo $style; ?>"><?php echo esc_html( $data['document_setting']['document_setting_title'] ); ?></h1>
						</div>
				<?php } ?>
			</div><!-- .page-header -->

			<?php 
				$com_setting = $data['company_setting'];
				if ( isset( $com_setting['active'] ) && $com_setting['company_setting_display'] === 'company_name' ) { ?>
					<div class="order-branding">
						<div class="company-info">
							<h3 class="company-name"><?php wcdn_company_name(); ?></h3>
							<?php if ( isset( $data['company_address']['active'] ) ) { 
								$style = 'text-align:' . $data['company_address']['company_address_text_align']. ';color:' . $data['company_address']['company_address_text_colour'];
								?>
								<div class="company-address"><?php wcdn_company_info(); ?></div>
							<?php } ?>
						</div>

						<?php do_action( 'wcdn_after_branding', $order ); ?>
					</div><!-- .order-branding -->
			<?php } ?>

			<div class="order-addresses">
				<?php
					if ( isset( $data['billing_address']['active'] ) ) :
						$style = 'text-align:' . $data['billing_address']['billing_address_text_align']. ';color:' . $data['billing_address']['billing_address_text_colour'];
						if ( ! empty( $data['billing_address']['billing_address_title'] ) ) {
							$blabel = $data['billing_address']['billing_address_title'];
						} else {
							$blabel = 'Billing Address';
						}
						?>
						<div class="billing-address" style="<?php echo $style; ?>">
							<h3 class="cap">
								<?php esc_attr_e( $blabel, 'woocommerce-delivery-notes' ); // phpcs:ignore ?>
							</h3>
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
				<?php endif; ?>

				<?php
					if ( isset( $data['shipping_address']['active'] ) ) :
						$style = 'text-align:' . $data['shipping_address']['shipping_address_text_align']. ';color:' . $data['shipping_address']['shipping_address_text_colour'];
						if ( ! empty( $data['shipping_address']['shipping_address_title'] ) ) {
							$slabel = $data['shipping_address']['shipping_address_title'];
						} else {
							$slabel = 'Shipping Address';
						}
						?>
						<div class="shipping-address" style="<?php echo $style; ?>">						
							<h3 class="cap"><?php esc_attr_e( $slabel, 'woocommerce-delivery-notes' ); ?></h3>
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
				<?php endif; ?>	

				<?php do_action( 'wcdn_after_addresses', $order ); ?>
			</div><!-- .order-addresses -->

			<div class="order-info">
				<h2><?php wcdn_document_title(); ?></h2>

				<ul class="info-list">
					<?php
					$fields = apply_filters( 'wcdn_order_info_fields', wcdn_get_order_info( $order, 'invoice' ), $order );
					?>
					<?php
					foreach ( $fields as $field ) :
						if ( 'yes' === $field['active']  && isset( $field['active'] ) ) {
							$labelstyle = 'font-size:' . $field['font-size'] . 'px;color' . $field['color'];
							if( isset( $field['font-weight'] ) ) {
								$labelstyle .= 'font-weight:' . $field['font-weight'] . ';';
							}
							?>
							<li>
								<strong style="<?php echo $labelstyle; ?>"><?php echo wp_kses_post( apply_filters( 'wcdn_order_info_name', $field['label'], $field ) ); ?></strong>
								<strong style="<?php echo $labelstyle; ?>"><?php echo wp_kses_post( apply_filters( 'wcdn_order_info_content', $field['value'], $field ) ); ?></strong>
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
							?>
							<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
								<tr>
									<td class="product-name">
										<?php echo $item->get_name(); ?>
									</td>
									<td class="product-item-price">
										<span><?php echo wp_kses_post( wcdn_get_formatted_item_price( $order, $item ) ); ?></span>
									</td>
									<td class="product-quantity">
										<span><?php echo esc_attr( apply_filters( 'wcdn_order_item_quantity', $item['qty'], $item ) ); ?></span>
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
									<td class="total-quantity"><?php echo wp_kses_post( $order->get_item_count() ); ?></td>
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
					$style = 'font-size:' . $data['customer_note']['customer_note_font_size'] . 'px;color:' . $data['customer_note']['customer_note_text_colour'];
					if ( ! empty( $data['customer_note']['customer_note_title'] ) ) {
						$clabel = $data['customer_note']['customer_note_title'];
					} else {
						$clabel = 'Customer Note';
					}
					?>
					<div class="order-notes" style="<?php echo $style; ?>">
						<?php if ( wcdn_has_customer_notes( $order ) ) : ?>
							<h4><?php esc_attr_e( $clabel, 'woocommerce-delivery-notes' ); ?></h4>
							<?php wcdn_customer_notes( $order ); ?>
						<?php endif; ?>

						<?php do_action( 'wcdn_after_notes', $order ); ?>
					</div><!-- .order-notes -->
			<?php endif; ?>
			
			<div class="order-thanks">
				<?php wcdn_personal_notes(); ?>

				<?php do_action( 'wcdn_after_thanks', $order ); ?>
			</div><!-- .order-thanks -->

			<div class="order-colophon">
				<div class="colophon-policies">
					<?php wcdn_policies_conditions(); ?>
				</div>

				<?php
				if ( isset( $data['footer']['active'] ) ) {
					$style = 'font-size:' . $data['footer']['footer_font_size'] . 'px;color:' . $data['footer']['footer_text_colour'];
					?>
					<div class="colophon-imprint" style="<?php echo $style; ?>">
						<?php wcdn_imprint(); ?>
					</div>
				<?php } ?>	

				<?php do_action( 'wcdn_after_colophon', $order ); ?>
			</div><!-- .order-colophon -->
		</div>
	</body>
</html>