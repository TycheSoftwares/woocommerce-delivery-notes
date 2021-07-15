<?php
/**
 * Print order content. Copy this file to your themes
 * directory /woocommerce/print-order to customize it.
 *
 * @package WooCommerce Print Invoice & Delivery Note/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class = 'order_info'>
	<thead></thead>
	<tbody>
		<tr>
			<td class='order-id' ><?php esc_attr_e( '#', 'woocommerce-delivery-notes' ); ?><?php esc_attr_e( $order->get_id(), 'woocommerce-delivery-notes' ); ?></td>
			<td class="order-billing-name"><span><?php esc_attr_e( $order->get_formatted_billing_full_name(), 'woocommerce-delivery-notes' ); ?></span></td>
		</tr>
		<tr>
			<td class="order-time">
				<?php
					$order_post           = get_post( $order->get_id() );
					$wdn_order_order_date = $order_post->post_date;
					$order_time           = strtotime( $wdn_order_order_date );
					$order_time           = date( 'H:i', $order_time );
					?> &nbsp;
					<span><?php esc_attr_e( $order_time, 'woocommerce-delivery-notes' ); ?></span>
			</td>
			<td class='order-phone'>
				<span><?php esc_attr_e( 'Phone number', 'woocommerce-delivery-notes' ); ?></span>
				<span><?php esc_attr_e( $order->get_billing_phone(), 'woocommerce-delivery-notes' ); ?></span>
			</td>
		</tr>
	</tbody>
</table>
<br>
<hr>
<h2>Item Details</h2>
<div>
<table class="product-table">
			<thead>
				<tr style="border:1px solid black;" >
					<th></th>
					<th style = 'max-width: 0.01em;' ><?php esc_attr_e( 'Quantity', 'woocommerce-delivery-notes' ); ?></th>
					<th><span><?php esc_attr_e( 'Items', 'woocommerce-delivery-notes' ); ?></span></th>
				</tr>
			</thead>
			<tbody>
				<?php

				if ( count( $order->get_items() ) > 0 ) :
					?>
					<?php foreach ( $order->get_items() as $item ) : ?>

						<?php

						$product = apply_filters( 'wcdn_order_item_product', $item->get_product(), $item );
						if ( ! $product ) {
							continue;
						}
						if ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) {
							$item_meta = new WC_Order_Item_Product( $item['item_meta'], $product );
						} else {
							$item_meta = new WC_Order_Item_Meta( $item['item_meta'], $product );
						}
						?>
						<tr style="border:1px solid black;">
							<td style = 'max-width: 1px;'><input type ='checkbox'></td>
							<td style = 'max-width: 6px;' >
								<span><?php echo esc_attr( apply_filters( 'wcdn_order_item_quantity', $item['qty'], $item ) ); ?></span>
							<span> <b>&#215;</b> <span>
							</td>
							<td class="product-name">
								<?php do_action( 'wcdn_order_item_before', $product, $order, $item ); ?>
								<span class="name">
								<?php

								$addon_name  = $item->get_meta( '_wc_pao_addon_name', true );
								$addon_value = $item->get_meta( '_wc_pao_addon_value', true );
								$is_addon    = ! empty( $addon_value );

								if ( $is_addon ) { // Displaying options of product addon.
									$addon_html = '<div class="wc-pao-order-item-name">' . esc_html( $addon_name ) . '</div><div class="wc-pao-order-item-value">' . esc_html( $addon_value ) . '</div></div>';

									echo wp_kses_post( $addon_html );
								} else {

									$product_id   = $item['product_id'];
									$prod_name    = get_post( $product_id );
									$product_name = $prod_name->post_title;

									echo wp_kses_post( apply_filters( 'wcdn_order_item_name', $product_name, $item ) );
									?>
									</span>

									<?php

									if ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) {
										if ( isset( $item['variation_id'] ) && 0 !== $item['variation_id'] ) {
											$variation = wc_get_product( $item['product_id'] );
											foreach ( $item['item_meta'] as $key => $value ) {
												if ( ! ( 0 === strpos( $key, '_' ) ) ) {
													if ( is_array( $value ) ) {
														continue;
													}
													$term_wp        = get_term_by( 'slug', $value, $key );
													$attribute_name = wc_attribute_label( $key, $variation );
													if ( isset( $term_wp->name ) ) {
														echo '<br>' . wp_kses_post( $attribute_name . ':' . $term_wp->name );
													} else {
														echo '<br>' . wp_kses_post( $attribute_name . ':' . $value );
													}
												}
											}
										} else {
											foreach ( $item['item_meta'] as $key => $value ) {
												if ( ! ( 0 === strpos( $key, '_' ) ) ) {
													if ( is_array( $value ) ) {
														continue;
													}
													echo '<br>' . wp_kses_post( $key . ':' . $value );
												}
											}
										}
									} else {
										$item_meta_new = new WC_Order_Item_Meta( $item['item_meta'], $product );
										$item_meta_new->display();

									}
									?>
									<br>
									<dl class="extras">
										<?php if ( $product && $product->exists() && $product->is_downloadable() && $order->is_download_permitted() ) : ?>

											<dt><?php esc_attr_e( 'Download:', 'woocommerce-delivery-notes' ); ?></dt>
											<dd>
											<?php
											// translators: files count.
											printf( esc_attr_e( '%s Files', 'woocommerce-delivery-notes' ), count( $item->get_item_downloads() ) );
											?>
											</dd>

										<?php endif; ?>

										<?php
										wcdn_print_extra_fields( $item );
										$fields = apply_filters( 'wcdn_order_item_fields', array(), $product, $order, $item );

										foreach ( $fields as $field ) :
											?>

											<dt><?php echo esc_html( $field['label'] ); ?></dt>
											<dd><?php echo esc_html( $field['value'] ); ?></dd>

										<?php endforeach; ?>
									</dl>
								<?php } ?>
								<?php do_action( 'wcdn_order_item_after', $product, $order, $item ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
</table>
</div>
<div class="pickup-info">
	<span><?php esc_attr_e( 'Pickup Information:', 'woocommerce-delivery-notes' ); ?></span>
</div>
<div  class ="delivery-info">
	<span><?php esc_attr_e( 'Packed By: ______', 'woocommerce-delivery-notes' ); ?></span>
	&nbsp;&nbsp;
	<span><?php esc_attr_e( 'Delivered By: ______', 'woocommerce-delivery-notes' ); ?></span>
</div>
<br>
<br>
<center>
<div class="order-branding">
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
</div>
</center>