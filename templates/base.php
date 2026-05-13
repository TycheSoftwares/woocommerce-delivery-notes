<?php
/**
 * WCDN Document Template.
 *
 * ============================================================================
 * OVERRIDING THIS TEMPLATE
 * ============================================================================
 * Copy this file to your active theme and the plugin will use your copy
 * instead of the bundled one:
 *
 *   your-theme/woocommerce-delivery-notes/base.php
 *
 * Keep the @version tag in your copy. The plugin uses it to detect when the
 * bundled template has been updated so it can warn you that your override may
 * need to be updated too.
 *
 * ============================================================================
 * OVERRIDING CSS
 * ============================================================================
 * Three CSS files control the document appearance. Copy any of them to your
 * theme at the corresponding path to override styles without editing the plugin:
 *
 *   Shared styles (HTML + PDF):
 *   your-theme/woocommerce-delivery-notes/css/style.css
 *
 *   HTML print-preview only:
 *   your-theme/woocommerce-delivery-notes/css/html/style.css
 *
 *   PDF generation only:
 *   your-theme/woocommerce-delivery-notes/css/pdf/style.css
 *
 * Theme CSS is loaded first; the plugin's dynamic settings CSS is applied on
 * top, so inline styles set via the settings page will still take effect.
 *
 * ============================================================================
 * USING HOOKS INSTEAD OF FULL OVERRIDES
 * ============================================================================
 * For smaller changes, hooks are safer than copying the whole file because
 * they are unaffected by plugin updates:
 *
 *   wcdn_before_document( $order, $template )   — before all document content
 *   wcdn_after_logo( $order, $template )        — after the shop logo
 *   wcdn_after_title( $order, $template )       — after the document title
 *   wcdn_after_branding( $order, $template )    — after shop name/address block
 *   wcdn_after_addresses( $order, $template )   — after billing/shipping/meta
 *   wcdn_before_items( $order, $template )      — before the line-items table
 *   wcdn_order_item_before( $item, $order, $template ) — before each line item
 *   wcdn_order_item_after( $item, $order, $template )  — after each line item
 *   wcdn_after_items( $order, $template )       — after the line-items table
 *   wcdn_after_totals( $order, $template )      — after the order totals table
 *   wcdn_after_pay_button( $order, $template )  — after the pay-now button
 *   wcdn_after_notes( $order, $template )       — after the customer note
 *   wcdn_after_document( $order, $template )    — after all document content
 *
 * @package WCDN/Templates
 * @version 7.1.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * All variables below are prepared by Template_Renderer::prepare_template_data()
 * and extracted into scope before this file is included. Raw data is also
 * available via $data['order'], $data['settings'], $data['shop'], $data['document'].
 *
 * @var array  $shop                Shop info.
 * @var array  $order               Order data.
 * @var array  $document            Document data.
 * @var array  $settings            Template settings.
 * @var string $template            Template key (invoice, receipt, …).
 * @var string $type                Render context ('pdf' or 'html').
 * @var array  $items               Line items for the current document.
 * @var array  $totals              Order totals.
 * @var bool   $is_rtl              Whether the document locale is RTL.
 * @var bool   $show_billing        Whether to render the billing address column.
 * @var bool   $show_shipping       Whether to render the shipping address column.
 * @var string $order_meta_position 'columns' or 'below'.
 * @var array  $order_meta_fields   Prepared meta rows ready for rendering.
 * @var bool   $has_order_meta      Whether any meta rows have a visible value.
 * @var int    $col_width           Width percentage for each address column.
 * @var int    $angle               Watermark rotation angle.
 * @var bool   $show_pay_now_button Whether to render the pay-now button.
 */

$meta_style_for = function ( $field ) {
	if ( ! isset( $field['fontSize'] ) ) {
		return '';
	}
	return 'font-size:' . $field['fontSize'] . 'px;'
		. 'font-weight:' . $field['fontWeight'] . ';'
		. 'text-align:' . $field['textAlign'] . ';'
		. 'color:' . $field['color'] . ';';
};

$columns_data = array();

if ( $show_billing ) {
	$columns_data['billing'] = function () use ( $order, $settings, $col_width, $order_meta_position ) {
		?>
<td style="width: <?php echo esc_attr( $col_width ); ?>%; vertical-align: top;" class="wcdn-billing-address">
	<strong><?php echo esc_html( $settings['billingAddressText'] ); ?></strong>
	<p>
		<?php echo esc_html( $order['billing']['name'] ); ?><br />

		<?php if ( ! empty( $order['billing']['address'] ) ) : ?>
			<?php foreach ( $order['billing']['address'] as $line ) : ?>
				<?php echo esc_html( $line ); ?><br />
		<?php endforeach; ?>
		<?php endif; ?>
	</p>

		<?php if ( 'columns' === $order_meta_position ) : ?>
			<?php if ( ! empty( $settings['showBillingPhone'] ) && ! empty( $order['billing']['phone'] ) ) : ?>
	<p class="wcdn-columns-billingPhone">
				<?php echo esc_html( ( $settings['billingPhoneText'] ?? __( 'Phone', 'woocommerce-delivery-notes' ) ) . ': ' . wcdn_format_phone_number( $order['billing']['phone'], $order['billing']['country'] ?? '' ) ); ?>
	</p>
	<?php endif; ?>

			<?php if ( ! empty( $settings['showBillingEmail'] ) && ! empty( $order['billing']['email'] ) ) : ?>
	<p class="wcdn-columns-billingEmail">
				<?php echo esc_html( ( $settings['billingEmailText'] ?? __( 'Email', 'woocommerce-delivery-notes' ) ) . ': ' . $order['billing']['email'] ); ?>
	</p>
	<?php endif; ?>
	<?php endif; ?>
</td>
		<?php
	};
}

if ( $show_shipping ) {
	$columns_data['shipping'] = function () use ( $order, $settings, $col_width ) {
		?>
<td style="width: <?php echo esc_attr( $col_width ); ?>%; vertical-align: top;" class="wcdn-shipping-address">
	<strong><?php echo esc_html( $settings['shippingAddressText'] ); ?></strong>
	<p>
		<?php echo esc_html( $order['shipping']['name'] ); ?><br />

		<?php if ( ! empty( $order['shipping']['address'] ) ) : ?>
			<?php foreach ( $order['shipping']['address'] as $line ) : ?>
				<?php echo esc_html( $line ); ?><br />
		<?php endforeach; ?>
		<?php endif; ?>

		<?php if ( ! empty( $order['shipping']['email'] ) ) : ?>
			<?php echo esc_html( __( 'Email', 'woocommerce-delivery-notes' ) . ': ' . $order['shipping']['email'] ); ?><br />
		<?php endif; ?>
	</p>
</td>
		<?php
	};
}

if ( $has_order_meta && 'columns' === $order_meta_position ) {
	$columns_data['meta'] = function () use ( $order_meta_fields, $settings, $col_width, $meta_style_for ) {
		?>
<td style="width: <?php echo esc_attr( $col_width ); ?>%; vertical-align: top;" class="wcdn-order-meta">
	<table>
		<?php foreach ( $order_meta_fields as $field ) : ?>
			<?php if ( $field['show'] && ! empty( $field['value'] ) ) : ?>
				<?php $meta_style = $meta_style_for( $field ); ?>
		<tr class="wcdn-meta-<?php echo esc_attr( $field['key'] ); ?>">
			<td class="label" <?php echo $meta_style ? ' style="' . esc_attr( $meta_style ) . '"' : ''; ?>>
				<?php echo esc_html( $field['label'] ); ?>:</td>
			<td class="value" <?php echo $meta_style ? ' style="' . esc_attr( $meta_style ) . '"' : ''; ?>>
				<?php echo esc_html( $field['value'] ); ?></td>
		</tr>
		<?php endif; ?>
		<?php endforeach; ?>
	</table>
</td>
		<?php
	};
}

if ( $is_rtl ) {
	$columns_data = array_reverse( $columns_data );
}
?>

<div class="wcdn-document <?php echo $is_rtl ? 'is-rtl' : ''; ?>">

	<?php do_action( 'wcdn_before_document', $order, $template ); ?>

	<?php
	if ( ! empty( $settings['showWatermark'] ) && ! empty( $settings['watermarkText'] ) ) :
		if ( isset( $settings['watermarkLayout'] ) && 'repeat' === $settings['watermarkLayout'] ) :
			?>
	<div class="wcdn-watermark-repeat">
			<?php for ( $i = 0; $i < 12; $i++ ) : ?>
		<span style="transform: rotate(<?php echo esc_attr( $angle ); ?>deg);">
				<?php echo esc_html( $settings['watermarkText'] ); ?>
		</span>
		<?php endfor; ?>
	</div>

	<?php else : ?>
	<div class="wcdn-watermark" style="transform: translate(-50%, -50%) rotate(<?php echo esc_attr( $angle ); ?>deg);">
		<?php echo esc_html( $settings['watermarkText'] ); ?>
	</div>

	<?php endif; ?>
	<?php endif; ?>

	<?php if ( ! empty( $settings['showLogo'] ) ) : ?>
	<div
		class="wcdn-logo align-<?php echo esc_attr( isset( $settings['logoAlignment'] ) ? $settings['logoAlignment'] : 'center' ); ?>">
		<?php if ( ! empty( $shop['logo'] ) ) : ?>
		<img class="wcdn-logo-image"
			src="<?php echo esc_attr( ( 'pdf' === $type && ! empty( $shop['logo_path'] ) ) ? $shop['logo_path'] : $shop['logo'] ); ?>"
			alt="<?php echo esc_attr( $shop['name'] ?? '' ); ?>" />
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php do_action( 'wcdn_after_logo', $order, $template ); ?>

	<?php if ( ! isset( $settings['showDocumentTitle'] ) || ! empty( $settings['showDocumentTitle'] ) ) : ?>
	<h1 class="wcdn-title">
		<?php echo esc_html( isset( $settings['documentTitle'] ) ? $settings['documentTitle'] : __( 'Document', 'woocommerce-delivery-notes' ) ); ?>
	</h1>
	<?php endif; ?>

	<?php do_action( 'wcdn_after_title', $order, $template ); ?>

	<?php
	if (
		! empty( $settings['showShopName'] ) ||
		! empty( $settings['showShopAddress'] ) ||
		! empty( $settings['showShopPhone'] ) ||
		! empty( $settings['showShopEmail'] )
	) :
		?>

	<hr />

	<div class="wcdn-shop">
		<?php if ( ! empty( $settings['showShopName'] ) && ! empty( $shop['name'] ) ) : ?>
		<div class="wcdn-shop-name"><?php echo esc_html( $shop['name'] ); ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $settings['showShopAddress'] ) && ! empty( $shop['address'] ) ) : ?>
		<div class="wcdn-shop-address"><?php echo nl2br( wp_kses_post( $shop['address'] ) ); ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $settings['showShopPhone'] ) && ! empty( $shop['phone'] ) ) : ?>
		<div class="wcdn-shop-phone">
			<?php echo esc_html( ( ! empty( $settings['shopPhoneText'] ) ? $settings['shopPhoneText'] . ': ' : '' ) . $shop['phone'] ); ?>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $settings['showShopEmail'] ) && ! empty( $shop['email'] ) ) : ?>
		<div class="wcdn-shop-email">
			<?php echo esc_html( ( ! empty( $settings['shopEmailText'] ) ? $settings['shopEmailText'] . ': ' : '' ) . $shop['email'] ); ?>
		</div>
		<?php endif; ?>
	</div>

		<?php do_action( 'wcdn_after_branding', $order, $template ); ?>

	<?php endif; ?>

	<?php
	$show_address_grid = ! empty( $columns_data );
	$show_meta_below   = 'below' === $order_meta_position && $has_order_meta;
	?>
	<?php if ( $show_address_grid || $show_meta_below ) : ?>

	<hr />

		<?php if ( $show_address_grid ) : ?>
	<table class="wcdn-address-grid">
		<tr>
			<?php
			foreach ( $columns_data as $column ) {
				$column();
			}
			?>
		</tr>
	</table>
	<?php endif; ?>

		<?php if ( $show_meta_below ) : ?>
			<?php if ( ! empty( $settings['showOrderDataHeader'] ) && ! empty( $settings['orderDataHeaderText'] ) ) : ?>
	<p class="wcdn-order-data-header">
				<?php echo esc_html( $settings['orderDataHeaderText'] ); ?>
	</p>
				<?php if ( ! empty( $settings['showOrderDataHeaderBorder'] ) ) : ?>
	<hr class="wcdn-order-data-header-border" style="margin: 0" />
	<?php endif; ?>
	<?php endif; ?>
	<table class="wcdn-order-meta wcdn-order-meta-below" style="width: 100%;">
			<?php foreach ( $order_meta_fields as $field ) : ?>
				<?php if ( $field['show'] && ! empty( $field['value'] ) ) : ?>
					<?php $meta_style = $meta_style_for( $field ); ?>
		<tr class="wcdn-meta-<?php echo esc_attr( $field['key'] ); ?>">
			<td class="label" <?php echo $meta_style ? ' style="' . esc_attr( $meta_style ) . '"' : ''; ?>>
					<?php echo esc_html( $field['label'] ); ?>:</td>
			<td class="value" <?php echo $meta_style ? ' style="' . esc_attr( $meta_style ) . '"' : ''; ?>>
					<?php echo esc_html( $field['value'] ); ?></td>
		</tr>
		<?php endif; ?>
		<?php endforeach; ?>
	</table>
	<?php endif; ?>

		<?php do_action( 'wcdn_after_addresses', $order, $template ); ?>

	<?php endif; ?>

	<?php if ( ! empty( $items ) ) : ?>
		<?php
		$show_price_cols = ! empty( $settings['displayPriceInProductDetailsTable'] ) ||
		( 'creditnote' === $template && ! empty( $settings['displayRefundItemsInTable'] ) );
		?>

		<?php do_action( 'wcdn_before_items', $order, $template ); ?>

	<hr style="margin: 0 0 10px 0;" />
	<table class="wcdn-items">
		<?php if ( $show_price_cols ) : ?>
		<colgroup>
			<col class="wcdn-col-product" style="width:50%;">
			<col class="wcdn-col-price" style="width:15%;">
			<col class="wcdn-col-qty" style="width:15%;">
			<col class="wcdn-col-total" style="width:20%;">
		</colgroup>
		<?php endif; ?>
		<thead>
			<tr>
				<th style="width:<?php echo $show_price_cols ? '50%' : '80%'; ?>;">
					<?php echo esc_html( ( 'creditnote' === $template ) ? __( 'Refunded Item', 'woocommerce-delivery-notes' ) : __( 'Product', 'woocommerce-delivery-notes' ) ); ?>
				</th>

				<?php if ( $show_price_cols ) : ?>
				<th style="width:15%;"><?php esc_html_e( 'Price', 'woocommerce-delivery-notes' ); ?></th>
				<?php endif; ?>

				<th style="width:<?php echo $show_price_cols ? '15%' : '20%'; ?>;">
					<?php esc_html_e( 'Quantity', 'woocommerce-delivery-notes' ); ?></th>

				<?php if ( $show_price_cols ) : ?>
				<th style="width:20%;">
					<?php echo esc_html( ( 'creditnote' === $template ) ? __( 'Total Refunded', 'woocommerce-delivery-notes' ) : __( 'Total', 'woocommerce-delivery-notes' ) ); ?>
				</th>
				<?php endif; ?>
			</tr>
		</thead>

		<tbody>
			<?php
			$_wcdn_wc_order = ! empty( $order['id'] ) ? wc_get_order( $order['id'] ) : null;
			?>
			<?php foreach ( $items as $item ) : ?>
				<?php
				$_wcdn_wc_product    = ! empty( $item['product_id'] ) ? wc_get_product( $item['product_id'] ) : null;
				$_wcdn_wc_order_item = ( $_wcdn_wc_order && ! empty( $item['order_item_id'] ) )
					? $_wcdn_wc_order->get_item( $item['order_item_id'] )
					: null;
				?>
			<tr>
				<td class="wcdn-product-cell">
					<?php
					do_action_deprecated(
						'wcdn_order_item_before',
						array( $_wcdn_wc_product, $_wcdn_wc_order, $_wcdn_wc_order_item ),
						'7.0.0',
						'wcdn_order_item_before',
						esc_html__( 'The wcdn_order_item_before hook argument order changed in v7.0. Update your callback to accept ( $item, $order, $template ) instead of ( $product, $order, $item ).', 'woocommerce-delivery-notes' )
					);
					do_action( 'wcdn_order_item_before', $item, $order, $template );
					?>
					<?php if ( ! empty( $item['addon'] ) ) : ?>
					<div class="wcdn-item-addon-name"><?php echo esc_html( $item['addon']['name'] ); ?></div>
					<div class="wcdn-item-addon-value"><?php echo esc_html( $item['addon']['value'] ); ?></div>
					<?php else : ?>
						<?php
						$img_src  = '';
						$img_size = (int) ( $settings['productImageSize'] ?? 40 );
						if ( ! empty( $settings['showProductImages'] ) ) {
							$img_src = ( 'pdf' === $type ) ? ( $item['image_path'] ?? '' ) : ( $item['image_url'] ?? '' );
						}
						$has_image = ! empty( $img_src );
						?>
						<?php if ( $has_image ) : ?>
					<table class="wcdn-item-layout" style="border-collapse:collapse;width:100%;">
						<tr>
							<td
								style="width:<?php echo esc_attr( $img_size ); ?>px;padding-right:6px;vertical-align:top;">
								<img class="wcdn-item-image" src="<?php echo esc_attr( $img_src ); ?>"
									width="<?php echo esc_attr( $img_size ); ?>"
									height="<?php echo esc_attr( $img_size ); ?>" alt="" />
							</td>
							<td style="vertical-align:top;">
								<?php endif; ?>
								<span class="wcdn-item-name">
									<?php echo wp_kses_post( $item['name'] ); ?>
									<?php if ( ! empty( $item['sku'] ) ) : ?>
									<span
										class="wcdn-item-sku"><?php echo wp_kses_post( '(' . __( 'SKU', 'woocommerce-delivery-notes' ) . ': ' . $item['sku'] . ')' ); ?></span>
									<?php endif; ?>
								</span>
								<?php if ( ! empty( $item['meta'] ) ) : ?>
								<dl class="wcdn-item-meta">
									<?php foreach ( $item['meta'] as $row ) : ?>
									<dt><?php echo wp_kses_post( $row['label'] ); ?></dt>
									<dd><?php echo wp_kses_post( $row['value'] ); ?></dd>
									<?php endforeach; ?>
								</dl>
								<?php endif; ?>
								<?php if ( $has_image ) : ?>
							</td>
						</tr>
					</table>
					<?php endif; ?>
					<?php endif; ?>
					<?php
					do_action_deprecated(
						'wcdn_order_item_after',
						array( $_wcdn_wc_product, $_wcdn_wc_order, $_wcdn_wc_order_item ),
						'7.0.0',
						'wcdn_order_item_after',
						esc_html__( 'The wcdn_order_item_after hook argument order changed in v7.0. Update your callback to accept ( $item, $order, $template ) instead of ( $product, $order, $item ).', 'woocommerce-delivery-notes' )
					);
					do_action( 'wcdn_order_item_after', $item, $order, $template );
					?>
				</td>

				<?php if ( $show_price_cols ) : ?>
				<td><?php echo wp_kses_post( $item['price'] ); ?></td>
				<?php endif; ?>

				<td><?php echo esc_html( $item['quantity'] ); ?></td>

				<?php if ( $show_price_cols ) : ?>
				<td><?php echo wp_kses_post( $item['total'] ); ?></td>
				<?php endif; ?>

			</tr>
			<?php endforeach; ?>
		</tbody>

		<?php
		$total_quantity  = 0;
		$non_addon_count = 0;
		foreach ( $items as $item ) {
			if ( empty( $item['addon'] ) ) {
				$total_quantity += (float) $item['quantity'];
				++$non_addon_count;
			}
		}
		$total_quantity = ( floor( $total_quantity ) === $total_quantity ) ? (int) $total_quantity : $total_quantity;
		?>
		<?php if ( $non_addon_count > 1 ) : ?>
		<tfoot>
			<tr class="wcdn-total-quantity">
				<?php if ( $show_price_cols ) : ?>
				<td colspan="2" class="wcdn-totals-label">
					<?php esc_html_e( 'Total Qty:', 'woocommerce-delivery-notes' ); ?></td>
				<td><?php echo esc_html( $total_quantity ); ?></td>
				<td></td>
				<?php else : ?>
				<td class="wcdn-totals-label"><?php esc_html_e( 'Total Qty:', 'woocommerce-delivery-notes' ); ?></td>
				<td><?php echo esc_html( $total_quantity ); ?></td>
				<?php endif; ?>
			</tr>
		</tfoot>
		<?php endif; ?>

	</table>
		<?php do_action( 'wcdn_after_items', $order, $template ); ?>
	<?php endif; ?>

	<?php
	if ( isset( $totals['total'] ) && 'creditnote' !== $template && ! empty( $settings['displayPriceInProductDetailsTable'] ) ) :
		$render_totals_row = function ( $value, $label, $bold = false, $row_class = '' ) use ( $order ) {
			$formatted  = esc_html( apply_filters( 'wcdn_invoice_order_total_label', $label, $order ) );
			$cell_label = $bold ? '<strong>' . $formatted . '</strong>' : $formatted;
			?>
	<tr<?php echo $row_class ? ' class="' . esc_attr( $row_class ) . '"' : ''; ?>>
		<td colspan="3" class="wcdn-totals-label"><?php echo wp_kses( $cell_label, array( 'strong' => array() ) ); ?></td>
		<td class="wcdn-totals-value"><?php echo wp_kses_post( $value ); ?></td>
	</tr>
			<?php
		};
		?>

		<table class="wcdn-totals" width="100%">
			<colgroup>
				<col class="wcdn-col-product" style="width:50%;">
				<col class="wcdn-col-price" style="width:15%;">
				<col class="wcdn-col-qty" style="width:15%;">
				<col class="wcdn-col-total" style="width:20%;">
			</colgroup>
			<tr style="line-height:0;font-size:0;">
				<td style="width:50%;height:0;padding:0;border:none;"></td>
				<td style="width:15%;height:0;padding:0;border:none;"></td>
				<td style="width:15%;height:0;padding:0;border:none;"></td>
				<td style="width:20%;height:0;padding:0;border:none;"></td>
			</tr>

			<?php if ( isset( $totals['subtotal'] ) && ! empty( $settings['showProductCharges'] ) && ! empty( $settings['showSubtotal'] ) ) : ?>
				<?php $render_totals_row( $totals['subtotal'], __( 'Subtotal:', 'woocommerce-delivery-notes' ) ); ?>
			<?php endif; ?>

			<?php if ( isset( $totals['discount'] ) ) : ?>
				<?php $render_totals_row( $totals['discount'], __( 'Discount:', 'woocommerce-delivery-notes' ) ); ?>
			<?php endif; ?>

			<?php if ( ( isset( $totals['tax'] ) || ! empty( $totals['tax_lines'] ) ) && ! empty( $settings['showProductCharges'] ) && ! empty( $settings['showTax'] ) ) : ?>
				<?php if ( ! empty( $totals['tax_lines'] ) ) : ?>
					<?php foreach ( $totals['tax_lines'] as $tax_line ) : ?>
						<?php $render_totals_row( $tax_line['value'], $tax_line['label'] . ':' ); ?>
			<?php endforeach; ?>
			<?php elseif ( isset( $totals['tax'] ) ) : ?>
				<?php $render_totals_row( $totals['tax'], __( 'Tax:', 'woocommerce-delivery-notes' ) ); ?>
			<?php endif; ?>
			<?php endif; ?>

			<?php if ( isset( $totals['shipping'] ) && ! empty( $settings['showProductCharges'] ) && ! empty( $settings['showShipping'] ) ) : ?>
				<?php $render_totals_row( $totals['shipping'], __( 'Shipping:', 'woocommerce-delivery-notes' ) ); ?>
			<?php endif; ?>

			<?php if ( ! empty( $totals['fee_lines'] ) ) : ?>
				<?php foreach ( $totals['fee_lines'] as $fee_line ) : ?>
					<?php $render_totals_row( $fee_line['value'], $fee_line['label'] . ':' ); ?>
			<?php endforeach; ?>
			<?php endif; ?>

			<?php if ( ! empty( $totals['has_refund'] ) ) : ?>
				<?php $render_totals_row( $totals['total'], __( 'Order Total:', 'woocommerce-delivery-notes' ), true ); ?>
				<?php $render_totals_row( $totals['refunded'], __( 'Refund:', 'woocommerce-delivery-notes' ) ); ?>
				<?php $render_totals_row( $totals['net_total'] . ( ! empty( $totals['tax_label'] ) ? ' ' . $totals['tax_label'] : '' ), __( 'Total:', 'woocommerce-delivery-notes' ), true, 'wcdn-total' ); ?>
			<?php else : ?>
				<?php $render_totals_row( $totals['total'], __( 'Total:', 'woocommerce-delivery-notes' ), true, 'wcdn-total' ); ?>
			<?php endif; ?>

			<?php if ( isset( $totals['awcdp_deposit'] ) ) : ?>
				<?php $render_totals_row( $totals['awcdp_deposit'], __( 'Deposit:', 'woocommerce-delivery-notes' ) ); ?>
			<?php endif; ?>

			<?php if ( isset( $totals['awcdp_future_payments'] ) ) : ?>
				<?php $render_totals_row( $totals['awcdp_future_payments'], __( 'Future Payments:', 'woocommerce-delivery-notes' ) ); ?>
			<?php endif; ?>

			<?php if ( isset( $totals['dfw_deposit'] ) ) : ?>
				<?php $render_totals_row( $totals['dfw_deposit'], __( 'Deposit:', 'woocommerce-delivery-notes' ) ); ?>
			<?php endif; ?>

			<?php if ( isset( $totals['dfw_future_payment'] ) ) : ?>
				<?php $render_totals_row( $totals['dfw_future_payment'], __( 'Future Payment:', 'woocommerce-delivery-notes' ) ); ?>
			<?php endif; ?>

			<?php if ( isset( $totals['dfw_total_cart_amount'] ) ) : ?>
				<?php $render_totals_row( $totals['dfw_total_cart_amount'], __( 'Total Cart Amount:', 'woocommerce-delivery-notes' ) ); ?>
			<?php endif; ?>
		</table>

		<?php endif; ?>

		<?php do_action( 'wcdn_after_totals', $order, $template ); ?>

		<?php if ( $show_pay_now_button ) : ?>
		<div class="wcdn-pay">
			<table align="center">
				<tr>
					<td>
						<a class="wcdn-payment-button" href="<?php echo esc_url( $order['payment_url'] ); ?>">
							<?php echo esc_html( $settings['payNowLabel'] ); ?> &mdash;
							<?php echo wp_kses_post( $totals['total'] ); ?>
						</a>
					</td>
				</tr>
			</table>
		</div>

		<?php endif; ?>

		<?php do_action( 'wcdn_after_pay_button', $order, $template ); ?>

		<?php if ( ! empty( $settings['showCustomerNote'] ) && ! empty( $order['customer_note'] ) ) : ?>
		<hr style="margin: 5px 0;" />
		<div class="wcdn-customer-note">
			<?php echo esc_html( $settings['customerNoteTitle'] ); ?>:
			<?php echo esc_html( $order['customer_note'] ); ?>
		</div>

			<?php do_action( 'wcdn_after_notes', $order, $template ); ?>

		<?php endif; ?>

		<?php if ( ! empty( $settings['showPolicies'] ) && ! empty( $document['policies'] ) ) : ?>
		<hr style="margin: 5px 0;" />
		<div class="wcdn-policies">
			<?php echo wp_kses_post( $document['policies'] ); ?>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $settings['showComplimentaryClose'] ) && ! empty( $document['complimentaryClose'] ) ) : ?>
		<hr style="margin: 5px 0;" />
		<div class="wcdn-complimentary-close">
			<?php echo wp_kses_post( $document['complimentaryClose'] ); ?>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $settings['showFooter'] ) && ! empty( $document['footer'] ) ) : ?>
		<hr style="margin: 5px 0;" />
		<div class="wcdn-footer">
			<?php echo wp_kses_post( $document['footer'] ); ?>
		</div>
		<?php endif; ?>

		<?php do_action( 'wcdn_after_document', $order, $template ); ?>

</div>