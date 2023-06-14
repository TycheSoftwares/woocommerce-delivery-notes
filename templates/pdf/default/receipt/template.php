<?php
/**
 * Create default receipt PDF.
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
				font: inherit;
				margin: 0;
				padding: 0;
				vertical-align: baseline;
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
				line-height: 1;
				background: #fff;
				color: #000;
				font-family: "HelveticaNeue", Helvetica, Arial, sans-serif;
				font-size: 100%;
				line-height: 1.25em;
			}
			h1, h2, h3, h4 {
				font-weight: bold;
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
			address {
				font-style: normal;
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
				vertical-align: top;
			}
			td img, th img {
				vertical-align: top;
			}
			th {
				color: black;
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
				text-align: left;
				font-size: 0.875em;
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
					<h1>Receipt</h1>
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
					<h3><?php esc_attr_e( 'Shipping Address', 'woocommerce-delivery-notes' ); ?></h3>
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
				<h2><?php wcdn_document_title(); ?></h2>

				<ul class="info-list">
					<?php
					$fields = apply_filters( 'wcdn_order_info_fields', wcdn_get_order_info( $order ), $order );
					?>
					<?php foreach ( $fields as $field ) : ?>
						<li>
							<strong><?php echo wp_kses_post( apply_filters( 'wcdn_order_info_name', $field['label'], $field ) ); ?></strong>
							<strong><?php echo wp_kses_post( apply_filters( 'wcdn_order_info_content', $field['value'], $field ) ); ?></strong>
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

			<div class="order-notes">
				<?php if ( wcdn_has_customer_notes( $order ) ) : ?>
					<h4><?php esc_attr_e( 'Customer Note', 'woocommerce-delivery-notes' ); ?></h4>
					<?php wcdn_customer_notes( $order ); ?>
				<?php endif; ?>

				<?php do_action( 'wcdn_after_notes', $order ); ?>
			</div><!-- .order-notes -->

			<div class="order-thanks">
				<?php wcdn_personal_notes(); ?>

				<?php do_action( 'wcdn_after_thanks', $order ); ?>
			</div><!-- .order-thanks -->

			<div class="order-colophon">
				<div class="colophon-policies">
					<?php wcdn_policies_conditions(); ?>
				</div>

				<div class="colophon-imprint">
					<?php wcdn_imprint(); ?>
				</div>	

				<?php do_action( 'wcdn_after_colophon', $order ); ?>
			</div><!-- .order-colophon -->
		</div>
	</body>
</html>
