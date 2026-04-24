<?php
/**
 * WCDN Document Template.
 *
 * @package WCDN/Templates
 * @since   7.0
 */

defined( 'ABSPATH' ) || exit;

$shop     = isset( $data['shop'] ) ? $data['shop'] : array();
$order    = isset( $data['order'] ) ? $data['order'] : array(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$document = isset( $data['document'] ) ? $data['document'] : array();
$settings = isset( $data['settings'] ) ? $data['settings'] : array();
$template = isset( $data['template'] ) ? $data['template'] : 'invoice'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

$items = ( 'creditnote' === $template )
	? ( isset( $order['refund']['items'] ) ? $order['refund']['items'] : array() )
	: ( isset( $order['items'] ) ? $order['items'] : array() );

$totals = ( 'creditnote' === $template ) // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	? array( 'total' => isset( $order['refund']['total'] ) ? $order['refund']['total'] : 0 )
	: ( isset( $order['totals'] ) ? $order['totals'] : array() );

$is_rtl = isset( $document['isRTL'] ) ? $document['isRTL'] : false;

/**
 * Order meta fields.
 */
$order_meta_keys = array(
	'invoiceNumber',
	'documentDate',
	'orderNumber',
	'orderDate',
	'paymentMethod',
	'paymentDate',
	'shippingMethod',
	'refundDate',
	'refundReason',
);

// Determine which columns exist.
$show_billing  = ! empty( $settings['showBillingAddress'] ) && ! empty( $order['billing'] );
$show_shipping = ! empty( $settings['showShippingAddress'] ) && ! empty( $order['shipping'] );

$order_meta_position = $settings['orderMetaPosition'] ?? 'columns';

// Build order meta.
$order_meta_fields = array(
	array(
		'show'  => ! empty( $settings['showInvoiceNumber'] ),
		'label' => $settings['invoiceNumberText'] ?? __( 'Invoice No', 'woocommerce-delivery-notes' ),
		'value' => $order['invoiceNumber'] ?? '',
		'key'   => 'invoiceNumber',
	),
	array(
		'show'  => ! empty( $settings['showDocumentDate'] ),
		'label' => $settings['documentDateText'] ?? '',
		'value' => $order['documentDate'] ?? '',
		'key'   => 'documentDate',
	),
	array(
		'show'  => ! empty( $settings['showOrderNumber'] ),
		'label' => $settings['orderNumberText'] ?? __( 'Order No', 'woocommerce-delivery-notes' ),
		'value' => $order['orderNumber'] ?? '',
		'key'   => 'orderNumber',
	),
	array(
		'show'  => ! empty( $settings['showOrderDate'] ),
		'label' => $settings['orderDateText'] ?? __( 'Date', 'woocommerce-delivery-notes' ),
		'value' => wcdn_format_date( $order['date'] ?? '', $settings['dateFormat'] ?? '' ),
		'key'   => 'orderDate',
	),
	array(
		'show'  => ! empty( $settings['showPaymentMethod'] ),
		'label' => $settings['paymentMethodText'] ?? __( 'Payment Method', 'woocommerce-delivery-notes' ),
		'value' => $order['paymentMethod'] ?? '',
		'key'   => 'paymentMethod',
	),
	array(
		'show'  => ! empty( $settings['showPaymentDate'] ),
		'label' => $settings['paymentDateText'] ?? __( 'Payment Date', 'woocommerce-delivery-notes' ),
		'value' => wcdn_format_date( $order['paymentDate'] ?? '', $settings['dateFormat'] ?? '' ),
		'key'   => 'paymentDate',
	),
	array(
		'show'  => ! empty( $settings['showShippingMethod'] ),
		'label' => $settings['shippingMethodText'] ?? __( 'Shipping Method', 'woocommerce-delivery-notes' ),
		'value' => $order['shippingMethod'] ?? '',
		'key'   => 'shippingMethod',
	),
	array(
		'show'  => ! empty( $settings['showRefundDate'] ),
		'label' => $settings['refundDateText'] ?? __( 'Refund Date', 'woocommerce-delivery-notes' ),
		'value' => wcdn_format_date( $order['refund']['date'] ?? '', $settings['dateFormat'] ?? '' ),
		'key'   => 'refundDate',
	),
	array(
		'show'  => ! empty( $settings['showRefundReason'] ),
		'label' => $settings['refundReasonText'] ?? __( 'Refund Reason', 'woocommerce-delivery-notes' ),
		'value' => $order['refund']['reason'] ?? '',
		'key'   => 'refundReason',
	),
);

// In 'below' mode only: append phone/email as meta table rows.
if ( 'below' === $order_meta_position ) {
	if ( ! empty( $settings['showBillingPhone'] ) && ! empty( $order['billing']['phone'] ) ) {
		$order_meta_fields[] = array(
			'show'  => true,
			'label' => $settings['billingPhoneText'] ?? __( 'Phone', 'woocommerce-delivery-notes' ),
			'value' => wcdn_format_phone_number( $order['billing']['phone'], $order['billing']['country'] ?? '' ),
			'key'   => 'billingPhone',
		);
	}

	if ( ! empty( $settings['showBillingEmail'] ) && ! empty( $order['billing']['email'] ) ) {
		$order_meta_fields[] = array(
			'show'  => true,
			'label' => $settings['billingEmailText'] ?? __( 'Email', 'woocommerce-delivery-notes' ),
			'value' => $order['billing']['email'],
			'key'   => 'billingEmail',
		);
	}
}

// Check if any meta exists.
$has_order_meta = false;
foreach ( $order_meta_fields as $field ) {
	if ( $field['show'] && ! empty( $field['value'] ) ) {
		$has_order_meta = true;
		break;
	}
}

// Count columns. Meta is only a column when position is 'columns'.
$columns = 0;
if ( $show_billing ) {
	++$columns;
}

if ( $show_shipping ) {
	++$columns;
}

if ( $has_order_meta && 'columns' === $order_meta_position ) {
	++$columns;
}

$col_width = $columns ? floor( 100 / $columns ) : 100;

$angle = $settings['watermarkAngle'] ?? -25;

$columns_data = array();

$show_pay_now_button = ! empty( $settings['showPayNowButton'] ) && ! empty( $totals['total'] ) && ! empty( $order['payment_url'] );
$show_pay_now_button = $show_pay_now_button && in_array( $order['status'], array( 'pending', 'failed' ), true );

// Billing.
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
				<p class="wcdn-columns-billingPhone"><?php echo esc_html( ( $settings['billingPhoneText'] ?? __( 'Phone', 'woocommerce-delivery-notes' ) ) . ': ' . wcdn_format_phone_number( $order['billing']['phone'], $order['billing']['country'] ?? '' ) ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $settings['showBillingEmail'] ) && ! empty( $order['billing']['email'] ) ) : ?>
				<p class="wcdn-columns-billingEmail"><?php echo esc_html( ( $settings['billingEmailText'] ?? __( 'Email', 'woocommerce-delivery-notes' ) ) . ': ' . $order['billing']['email'] ); ?></p>
			<?php endif; ?>
		<?php endif; ?>
</td>
		<?php
	};
}

// Shipping.
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
			<?php echo esc_html( 'Email: ' . $order['shipping']['email'] ); ?><br />
		<?php endif; ?>
	</p>
</td>
		<?php
	};
}

// Meta as a column (only when position is 'columns').
if ( $has_order_meta && 'columns' === $order_meta_position ) {
	$columns_data['meta'] = function () use ( $order_meta_fields, $settings, $col_width ) {
		?>
<td style="width: <?php echo esc_attr( $col_width ); ?>%; vertical-align: top;" class="wcdn-order-meta">
	<table>
		<?php foreach ( $order_meta_fields as $field ) : ?>
			<?php if ( $field['show'] && ! empty( $field['value'] ) ) : ?>
				<?php
				$meta_style = '';
				if ( isset( $field['fontSize'] ) ) {
					$meta_style = 'font-size:' . $field['fontSize'] . 'px;'
						. 'font-weight:' . $field['fontWeight'] . ';'
						. 'text-align:' . $field['textAlign'] . ';'
						. 'color:' . $field['color'] . ';';
				}
				?>
		<tr class="wcdn-meta-<?php echo esc_attr( $field['key'] ); ?>">
			<td class="label"<?php echo $meta_style ? ' style="' . esc_attr( $meta_style ) . '"' : ''; ?>><?php echo esc_html( $field['label'] ); ?>:</td>
			<td class="value"<?php echo $meta_style ? ' style="' . esc_attr( $meta_style ) . '"' : ''; ?>><?php echo esc_html( $field['value'] ); ?></td>
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

	<!-- WATERMARK -->
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

	<!-- LOGO -->
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

	<!-- TITLE -->
	<h1 class="wcdn-title">
		<?php echo esc_html( isset( $settings['documentTitle'] ) ? $settings['documentTitle'] : __( 'Document', 'woocommerce-delivery-notes' ) ); ?>
	</h1>

	<!-- SHOP DETAILS -->
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
		<div class="wcdn-shop-address"><?php echo nl2br( esc_html( $shop['address'] ) ); ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $settings['showShopPhone'] ) && ! empty( $shop['phone'] ) ) : ?>
		<div class="wcdn-shop-phone"><?php echo esc_html( ( ! empty( $settings['shopPhoneText'] ) ? $settings['shopPhoneText'] . ': ' : '' ) . $shop['phone'] ); ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $settings['showShopEmail'] ) && ! empty( $shop['email'] ) ) : ?>
		<div class="wcdn-shop-email"><?php echo esc_html( ( ! empty( $settings['shopEmailText'] ) ? $settings['shopEmailText'] . ': ' : '' ) . $shop['email'] ); ?></div>
		<?php endif; ?>
	</div>

		<?php do_action( 'wcdn_after_branding', $order, $template ); ?>

	<?php endif; ?>

	<!-- ADDRESSES & ORDER META -->
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
				<hr class="wcdn-order-data-header-border" style="margin: 0 0 8px;" />
				<?php endif; ?>
			<?php endif; ?>
		<table class="wcdn-order-meta wcdn-order-meta-below" style="width: 100%;">
			<?php foreach ( $order_meta_fields as $field ) : ?>
				<?php if ( $field['show'] && ! empty( $field['value'] ) ) : ?>
					<?php
					$meta_style = '';
					if ( isset( $field['fontSize'] ) ) {
						$meta_style = 'font-size:' . $field['fontSize'] . 'px;'
							. 'font-weight:' . $field['fontWeight'] . ';'
							. 'text-align:' . $field['textAlign'] . ';'
							. 'color:' . $field['color'] . ';';
					}
					?>
			<tr class="wcdn-meta-<?php echo esc_attr( $field['key'] ); ?>">
				<td class="label"<?php echo $meta_style ? ' style="' . esc_attr( $meta_style ) . '"' : ''; ?>><?php echo esc_html( $field['label'] ); ?>:</td>
				<td class="value"<?php echo $meta_style ? ' style="' . esc_attr( $meta_style ) . '"' : ''; ?>><?php echo esc_html( $field['value'] ); ?></td>
			</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</table>
		<?php endif; ?>

		<?php do_action( 'wcdn_after_addresses', $order, $template ); ?>

	<?php endif; ?>

	<!-- ITEMS -->
	<?php if ( ! empty( $items ) ) : ?>
		<?php
		$show_price_cols = ! empty( $settings['displayPriceInProductDetailsTable'] ) ||
		( 'creditnote' === $template && ! empty( $settings['displayRefundItemsInTable'] ) );
		?>
	<hr />
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
				<?php
				$has_price_cols = ! empty( $settings['displayPriceInProductDetailsTable'] ) ||
					( 'creditnote' === $template && ! empty( $settings['displayRefundItemsInTable'] ) );
				?>
				<th style="width:<?php echo $has_price_cols ? '50%' : '80%'; ?>;"><?php echo esc_html( ( 'creditnote' === $template ) ? __( 'Refunded Item', 'woocommerce-delivery-notes' ) : __( 'Product', 'woocommerce-delivery-notes' ) ); ?></th>

				<?php if ( $has_price_cols ) : ?>
				<th style="width:15%;"><?php esc_html_e( 'Price', 'woocommerce-delivery-notes' ); ?></th>
				<?php endif; ?>

				<th style="width:<?php echo $has_price_cols ? '15%' : '20%'; ?>;"><?php esc_html_e( 'Quantity', 'woocommerce-delivery-notes' ); ?></th>

				<?php if ( $has_price_cols ) : ?>
				<th style="width:20%;"><?php echo esc_html( ( 'creditnote' === $template ) ? __( 'Total Refunded', 'woocommerce-delivery-notes' ) : __( 'Total', 'woocommerce-delivery-notes' ) ); ?></th>
				<?php endif; ?>
			</tr>
		</thead>

		<tbody>
			<?php
			// Load the WC_Order once so the deprecated backward-compat hooks below can
			// pass the original WC objects that v6 callbacks expected.
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
				<td>
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
						<table class="wcdn-item-layout" style="border-collapse:collapse;width:100%;"><tr>
						<td style="width:<?php echo esc_attr( $img_size ); ?>px;padding-right:6px;vertical-align:top;">
							<img class="wcdn-item-image" src="<?php echo esc_attr( $img_src ); ?>" width="<?php echo esc_attr( $img_size ); ?>" height="<?php echo esc_attr( $img_size ); ?>" alt="" />
						</td>
						<td style="vertical-align:top;">
						<?php endif; ?>
						<?php echo wp_kses_post( $item['name'] ); ?>
						<?php if ( ! empty( $item['sku'] ) ) : ?>
					<span class="wcdn-item-sku"><?php echo wp_kses_post( '(SKU: ' . $item['sku'] . ')' ); ?></span>
					<?php endif; ?>
						<?php if ( ! empty( $item['meta'] ) ) : ?>
					<dl class="wcdn-item-meta">
							<?php foreach ( $item['meta'] as $row ) : ?>
						<dt><?php echo wp_kses_post( $row['label'] ); ?></dt>
						<dd><?php echo wp_kses_post( $row['value'] ); ?></dd>
						<?php endforeach; ?>
					</dl>
					<?php endif; ?>
						<?php if ( $has_image ) : ?>
						</td></tr></table>
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

				<?php
				if ( ! empty( $settings['displayPriceInProductDetailsTable'] ) ||
				( 'creditnote' === $template && ! empty( $settings['displayRefundItemsInTable'] ) ) ) :
					?>
				<td><?php echo wp_kses_post( $item['price'] ); ?></td>
				<?php endif; ?>

				<td><?php echo esc_html( $item['quantity'] ); ?></td>

				<?php
				if ( ! empty( $settings['displayPriceInProductDetailsTable'] ) ||
				( 'creditnote' === $template && ! empty( $settings['displayRefundItemsInTable'] ) ) ) :
					?>
				<td><?php echo wp_kses_post( $item['total'] ); ?></td>
				<?php endif; ?>

			</tr>
			<?php endforeach; ?>
		</tbody>

	</table>
		<?php do_action( 'wcdn_after_items', $order, $template ); ?>
	<?php endif; ?>

	<!-- TOTALS -->
	<?php
	if ( isset( $totals['total'] ) && 'creditnote' !== $template && ! empty( $settings['displayPriceInProductDetailsTable'] ) ) :
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
		<tr>
			<td colspan="3" class="wcdn-totals-label"><?php echo esc_html( apply_filters( 'wcdn_invoice_order_total_label', __( 'Subtotal:', 'woocommerce-delivery-notes' ), $order ) ); ?></td>
			<td class="wcdn-totals-value"><?php echo wp_kses_post( $totals['subtotal'] ); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( isset( $totals['discount'] ) ) : ?>
		<tr>
			<td colspan="3" class="wcdn-totals-label"><?php echo esc_html( apply_filters( 'wcdn_invoice_order_total_label', __( 'Discount:', 'woocommerce-delivery-notes' ), $order ) ); ?></td>
			<td class="wcdn-totals-value"><?php echo wp_kses_post( $totals['discount'] ); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( ( isset( $totals['tax'] ) || ! empty( $totals['tax_lines'] ) ) && ! empty( $settings['showProductCharges'] ) && ! empty( $settings['showTax'] ) ) : ?>
			<?php if ( ! empty( $totals['tax_lines'] ) ) : ?>
				<?php foreach ( $totals['tax_lines'] as $tax_line ) : ?>
				<tr>
					<td colspan="3" class="wcdn-totals-label"><?php echo esc_html( apply_filters( 'wcdn_invoice_order_total_label', $tax_line['label'] . ':', $order ) ); ?></td>
					<td class="wcdn-totals-value"><?php echo wp_kses_post( $tax_line['value'] ); ?></td>
				</tr>
				<?php endforeach; ?>
			<?php elseif ( isset( $totals['tax'] ) ) : ?>
			<tr>
				<td colspan="3" class="wcdn-totals-label"><?php echo esc_html( apply_filters( 'wcdn_invoice_order_total_label', __( 'Tax:', 'woocommerce-delivery-notes' ), $order ) ); ?></td>
				<td class="wcdn-totals-value"><?php echo wp_kses_post( $totals['tax'] ); ?></td>
			</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( isset( $totals['shipping'] ) && ! empty( $settings['showProductCharges'] ) && ! empty( $settings['showShipping'] ) ) : ?>
		<tr>
			<td colspan="3" class="wcdn-totals-label"><?php echo esc_html( apply_filters( 'wcdn_invoice_order_total_label', __( 'Shipping:', 'woocommerce-delivery-notes' ), $order ) ); ?></td>
			<td class="wcdn-totals-value"><?php echo wp_kses_post( $totals['shipping'] ); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( ! empty( $totals['has_refund'] ) ) : ?>
		<tr>
			<td colspan="3" class="wcdn-totals-label"><strong><?php echo esc_html( apply_filters( 'wcdn_invoice_order_total_label', __( 'Order Total:', 'woocommerce-delivery-notes' ), $order ) ); ?></strong></td>
			<td class="wcdn-totals-value"><?php echo wp_kses_post( $totals['total'] ); ?></td>
		</tr>
		<tr>
			<td colspan="3" class="wcdn-totals-label"><?php echo esc_html( apply_filters( 'wcdn_invoice_order_total_label', __( 'Refund:', 'woocommerce-delivery-notes' ), $order ) ); ?></td>
			<td class="wcdn-totals-value"><?php echo wp_kses_post( $totals['refunded'] ); ?></td>
		</tr>
		<tr class="wcdn-total">
			<td colspan="3" class="wcdn-totals-label"><strong><?php echo esc_html( apply_filters( 'wcdn_invoice_order_total_label', __( 'Total:', 'woocommerce-delivery-notes' ), $order ) ); ?></strong></td>
			<td class="wcdn-totals-value">
				<?php echo wp_kses_post( $totals['net_total'] ); ?>
				<?php if ( ! empty( $totals['tax_label'] ) ) : ?>
					<?php echo wp_kses_post( $totals['tax_label'] ); ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php else : ?>
		<tr class="wcdn-total">
			<td colspan="3" class="wcdn-totals-label"><strong><?php echo esc_html( apply_filters( 'wcdn_invoice_order_total_label', __( 'Total:', 'woocommerce-delivery-notes' ), $order ) ); ?></strong></td>
			<td class="wcdn-totals-value"><?php echo wp_kses_post( $totals['total'] ); ?></td>
		</tr>
		<?php endif; ?>
	</table>

	<?php endif; ?>

	<!-- PAY NOW -->
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

	<!-- CUSTOMER NOTE -->
	<?php if ( ! empty( $settings['showCustomerNote'] ) && ! empty( $order['customer_note'] ) ) : ?>
	<hr />
	<div class="wcdn-customer-note">
		<?php echo esc_html( $settings['customerNoteTitle'] ); ?>: <?php echo esc_html( $order['customer_note'] ); ?>
	</div>

		<?php do_action( 'wcdn_after_notes', $order, $template ); ?>

	<?php endif; ?>

	<!-- POLICIES -->
	<?php if ( ! empty( $settings['showPolicies'] ) && ! empty( $document['policies'] ) ) : ?>
	<hr />
	<div class="wcdn-policies">
		<?php echo wp_kses_post( $document['policies'] ); ?>
	</div>
	<?php endif; ?>

	<!-- COMPLIMENTARY CLOSE -->
	<?php if ( ! empty( $settings['showComplimentaryClose'] ) && ! empty( $document['complimentaryClose'] ) ) : ?>
	<hr />
	<div class="wcdn-complimentary-close">
		<?php echo wp_kses_post( $document['complimentaryClose'] ); ?>
	</div>
	<?php endif; ?>

	<!-- FOOTER -->
	<?php if ( ! empty( $settings['showFooter'] ) && ! empty( $document['footer'] ) ) : ?>
	<hr />
	<div class="wcdn-footer">
		<?php echo wp_kses_post( $document['footer'] ); ?>
	</div>
	<?php endif; ?>

	<?php do_action( 'wcdn_after_document', $order, $template ); ?>

</div>