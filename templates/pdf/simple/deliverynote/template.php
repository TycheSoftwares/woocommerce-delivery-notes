<?php
/**
 * Create Delivery Note PDF.
 *
 * @package WooCommerce Print Invoice & Delivery Note/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<html>
	<head>
		<style>
			/* Pdf file style CSS */
			html, body, div, span, h1, h2, h3, h4, h5, h6, p, a, table, ol, ul, dl, li, dt, dd {
				border: 0 none;
				margin: 0;
				padding: 0;
				vertical-align: middle;
			}
			ol, ul {
				list-style: none;
			}
			table {
				border-collapse: collapse;
				border-spacing: 0;
			}
			/* Main Body */
			body {
				background: #fff;
				font-family: "HelveticaNeue", Helvetica, Arial, sans-serif;
			}
			h1, h2, h3, h4 {
				margin-bottom: 10px;
			}
			ul {
				margin-bottom: 0.1em;
			}
			li, dt, dd  {
				padding: 5px 0;
			}
			dt {
				font-weight: bold;
			}
			p + p {
				margin-top: 1.25em;
			}

			/* Basic Table Styling */
			table {
				width: 100%;
			}
			tr {
				/* page-break-inside: avoid;
				page-break-after: auto;	 */
				border-bottom: 0.12em solid #bbb;
			}
			td, th {
				padding: 0.375em 0.75em 0.375em 0;
				vertical-align: middle;
			}
			td img, th img {
				vertical-align: middle;
			}
			th {
				font-weight: bold;
				text-align: left;
				padding-bottom: 1.25em;
			}
			tfoot {
				display: table-row-group;
			}
			/* Page Margins & Basic Stylings */
			#page {
				margin-left: auto;
				margin-right: auto;
			}
			.content {
				padding-left: 10%;
				padding-right: 10%;
				padding-top: 5%;
				padding-bottom: 5%;
			}
			/* .content + .content {
				page-break-before: always;
			} */
			h1, h2 {
				font-size: 1.572em;
			}
			.order-branding,
			.order-addresses,
			.order-info,
			.order-items,
			.order-notes,
			.order-thanks,
			.order-colophon {
				margin-bottom: 2em;
				margin-top: 2em;
			}

			/* .order-items {
				page-break-before: auto;
				page-break-after: auto;
			} */

			.cap {
				text-transform: uppercase;
			}

			/* Header */
			.page-header {
				margin-bottom: 2em;
				padding-bottom: 3em; 
				border-bottom: 0.24em solid black;
				width: 100%;
				vertical-align: middle;
			}
			.company-logo {
				width: 50%;
				float: left;
			}
			.document-name {
				width: 50%;
				float: left;
				text-align: right;
			}

			/* Order Branding */
			.order-branding {
				margin-bottom: 3em;
			}

			/* Order Addresses */
			.order-addresses {
				margin-bottom: 3em;
			}
			.order-addresses:after {
				content: ".";
				display: block;
				height: 0;
				clear: both;
				visibility: hidden;
			}
			.billing-address {
				width: 50%;
				float: left;
			}
			.shipping-address {
				width: 50%;
				float: left;
			}
			/* Switch the addresses for invoices */

			/* Order Info */
			.order-info ul {
				border-top: 0.24em solid black;
			}
			.order-info li {
				border-bottom: 0.12em solid #bbb;
				width: 100%;
			}
			.order-info li strong {
				min-width: 30%;
				display: inline-block;
			}

			/* Order Items */
			.order-items {
				margin-top: 2em;
				margin-bottom: 1em;
			}

			.order-items .head-name,
			.order-items .product-name,
			.order-items .total-name {
				width: 50%;
			}

			.order-items .head-quantity,
			.order-items .product-quantity,
			.order-items .total-quantity,
			.order-items .head-item-price,
			.order-items .product-item-price,
			.order-items .total-item-price {
				width: 15%;
			}

			.order-items .head-price,
			.order-items .product-price,
			.order-items .total-price {
				width: 20%;
			}

			.order-items p {
				display: inline;
			}

			.order-items small,
			.order-items dt,
			.order-items dd {
				font-size: 0.785em;
				font-weight: normal;
				line-height: 150%;
				padding: 0;
				margin: 0;
			}

			.order-items dt,
			.order-items dd {
				display: block;
				float: left;
			}

			.order-items dt {
				clear: left;
				padding-right: 0.2em;
			}

			.order-items .product-name .attachment {
				display: block;
				float: left; 
				margin-right: 0.5em;
				width: 36px;
			}

			.order-items .product-name .attachment img {
				max-width: 100%;
				height: auto;
			}

			.order-items tfoot tr:first-child,
			.order-items tfoot tr:last-child {
				font-weight: bold;
			}

			.order-items tfoot tr:last-child .total-price .amount:first-child {
				font-weight: bold;
			}

			.order-items tfoot tr:last-child {
				border-bottom: 0.24em solid black;
			}

			/* Order Notes */
			.order-notes {
				margin-top: 3em;
				margin-bottom: 6em;
			}

			.order-notes h4 {
				margin-bottom: 0;
			}

			/* Order Thanks */
			.order-thanks {
				margin-left: 50%;
			}

			/* Order Colophon */
			.order-colophon {
				font-size: 0.785em;
				line-height: 150%;
				margin-bottom: 0;
			}

			.colophon-policies {
				margin-bottom: 1.25em;
			}


			/* CSS Media Queries for Print
			------------------------------------------*/
			@media print {
				body {
					font-size: 8pt;
				}
				.content {
					/* Remove padding to not generate empty follow up pages */
					padding-bottom: 0;
				}
			}
		</style>
	</head>
	<?php $data = get_option( 'wcdn_deliverynote_customization' ); ?>
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
						if ( 'yes' === $field['active'] && isset( $field['active'] ) ) {
							$labelstyle = 'font-size:' . $field['font-size'] . 'px;font-weight:' . $field['font-weight'] . ';color' . $field['color'];
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
				<?php
				if ( isset( $data['complimentary_close']['active'] ) ) :
					$style = 'font-size:' . $data['complimentary_close']['complimentary_close_font_size'] . 'px;color:' . $data['complimentary_close']['complimentary_close_text_colour'];
					?>
					<?php wcdn_personal_notes(); ?>
				<?php } ?>

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
