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

// Build order meta.
$order_meta_fields = array(
	array(
		'show'  => ! empty( $settings['showInvoiceNumber'] ),
		'label' => $settings['invoiceNumberText'] ?? 'Invoice Number',
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
		'label' => $settings['orderNumberText'] ?? 'Order Number',
		'value' => $order['orderNumber'] ?? '',
		'key'   => 'orderNumber',
	),
	array(
		'show'  => ! empty( $settings['showOrderDate'] ),
		'label' => $settings['orderDateText'] ?? 'Order Date',
		'value' => wcdn_format_date( $order['date'] ?? '', $settings['dateFormat'] ?? '' ),
		'key'   => 'orderDate',
	),
	array(
		'show'  => ! empty( $settings['showPaymentMethod'] ),
		'label' => $settings['paymentMethodText'] ?? 'Payment Method',
		'value' => $order['paymentMethod'] ?? '',
		'key'   => 'paymentMethod',
	),
	array(
		'show'  => ! empty( $settings['showPaymentDate'] ),
		'label' => $settings['paymentDateText'] ?? 'Payment Date',
		'value' => wcdn_format_date( $order['paymentDate'] ?? '', $settings['dateFormat'] ?? '' ),
		'key'   => 'paymentDate',
	),
	array(
		'show'  => ! empty( $settings['showShippingMethod'] ),
		'label' => $settings['shippingMethodText'] ?? 'Shipping Method',
		'value' => $order['shippingMethod'] ?? '',
		'key'   => 'shippingMethod',
	),
	array(
		'show'  => ! empty( $settings['showRefundDate'] ),
		'label' => $settings['refundDateText'] ?? 'Refund Date',
		'value' => wcdn_format_date( $order['refund']['date'] ?? '', $settings['dateFormat'] ?? '' ),
		'key'   => 'refundDate',
	),
	array(
		'show'  => ! empty( $settings['showRefundReason'] ),
		'label' => $settings['refundReasonText'] ?? 'Refund Reason',
		'value' => $order['refund']['reason'] ?? '',
		'key'   => 'refundReason',
	),
);

// Check if any meta exists.
$has_order_meta = false;
foreach ( $order_meta_fields as $field ) {
	if ( $field['show'] && ! empty( $field['value'] ) ) {
		$has_order_meta = true;
		break;
	}
}

// Count columns.
$columns = 0;
if ( $show_billing ) {
	++$columns;
}

if ( $show_shipping ) {
	++$columns;
}

if ( $has_order_meta ) {
	++$columns;
}

$col_width = $columns ? floor( 100 / $columns ) : 100;

$angle = $settings['watermarkAngle'] ?? -25;

$columns_data = array();

$show_pay_now_button = ! empty( $settings['showPayNowButton'] ) && ! empty( $totals['total'] ) && ! empty( $order['payment_url'] );
$show_pay_now_button = $show_pay_now_button && in_array( $order['status'], array( 'pending', 'failed' ), true );

// Billing.
if ( $show_billing ) {
	$columns_data['billing'] = function () use ( $order, $settings, $col_width ) {
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

		<?php if ( ! empty( $order['billing']['phone'] ) ) : ?>
			<?php echo esc_html( $settings['shopPhoneText'] . ': ' . $order['billing']['phone'] ); ?><br />
		<?php endif; ?>

		<?php if ( ! empty( $order['billing']['email'] ) ) : ?>
			<?php echo esc_html( $settings['shopEmailText'] . ': ' . $order['billing']['email'] ); ?><br />
		<?php endif; ?>
	</p>
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

// Meta.
if ( $has_order_meta ) {
	$columns_data['meta'] = function () use ( $order_meta_fields, $settings, $col_width ) {
		?>
<td style="width: <?php echo esc_attr( $col_width ); ?>%; vertical-align: top;" class="wcdn-order-meta">
	<table>
		<?php foreach ( $order_meta_fields as $field ) : ?>
			<?php if ( $field['show'] && ! empty( $field['value'] ) ) : ?>
		<tr class="wcdn-meta-<?php echo esc_attr( $field['key'] ); ?>">
			<td class="label"><?php echo esc_html( $field['label'] ); ?>:</td>
			<td class="value"><?php echo esc_html( $field['value'] ); ?></td>
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
			src="<?php echo esc_attr( ! empty( $shop['logo_path'] ) ? $shop['logo_path'] : $shop['logo'] ); ?>"
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
		<div class="wcdn-shop-address"><?php echo esc_html( $shop['address'] ); ?></div>
		<?php endif; ?>

		<div class="wcdn-shop-contact">
			<?php
			echo wcdn_separate( // phpcs:ignore
				array(
					! empty( $settings['showShopPhone'] ) && ! empty( $shop['phone'] )
						? '<span class="wcdn-shop-phone">' . esc_html( $settings['shopPhoneText'] ) . ': ' . esc_html( $shop['phone'] ) . '</span>'
						: null,

					! empty( $settings['showShopEmail'] ) && ! empty( $shop['email'] )
						? '<span class="wcdn-shop-email">' . esc_html( $settings['shopEmailText'] ) . ': ' . esc_html( $shop['email'] ) . '</span>'
						: null,
				)
			);
			?>
		</div>
	</div>

		<?php do_action( 'wcdn_after_branding', $order, $template ); ?>

	<?php endif; ?>

	<!-- ADDRESSES -->
	<?php if ( ! empty( $settings['showBillingAddress'] ) || ! empty( $settings['showShippingAddress'] ) || $has_order_meta ) : ?>

	<hr />

	<table class="wcdn-address-grid">
		<tr>
			<?php
			foreach ( $columns_data as $column ) {
				$column();
			}
			?>
		</tr>
	</table>

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
			<col class="wcdn-col-product">
			<col class="wcdn-col-price">
			<col class="wcdn-col-qty">
			<col class="wcdn-col-total">
		</colgroup>
		<?php endif; ?>
		<thead>
			<tr>
				<th><?php echo esc_html( ( 'creditnote' === $template ) ? __( 'Refunded Item', 'woocommerce-delivery-notes' ) : __( 'Product', 'woocommerce-delivery-notes' ) ); ?>
				</th>

				<?php
				if ( ! empty( $settings['displayPriceInProductDetailsTable'] ) ||
				( 'creditnote' === $template && ! empty( $settings['displayRefundItemsInTable'] ) ) ) :
					?>
				<th><?php esc_html_e( 'Price', 'woocommerce-delivery-notes' ); ?></th>
				<?php endif; ?>

				<th><?php esc_html_e( 'Quantity', 'woocommerce-delivery-notes' ); ?></th>

				<?php
				if ( ! empty( $settings['displayPriceInProductDetailsTable'] ) ||
				( 'creditnote' === $template && ! empty( $settings['displayRefundItemsInTable'] ) ) ) :
					?>
				<th><?php echo esc_html( ( 'creditnote' === $template ) ? __( 'Total Refunded', 'woocommerce-delivery-notes' ) : __( 'Total', 'woocommerce-delivery-notes' ) ); ?>
				</th>
				<?php endif; ?>
			</tr>
		</thead>

		<tbody>
			<?php foreach ( $items as $item ) : ?>
			<tr>
				<td>
					<?php do_action( 'wcdn_order_item_before', $item, $order, $template ); ?>
					<?php if ( ! empty( $item['addon'] ) ) : ?>
					<div class="wcdn-item-addon-name"><?php echo esc_html( $item['addon']['name'] ); ?></div>
					<div class="wcdn-item-addon-value"><?php echo esc_html( $item['addon']['value'] ); ?></div>
					<?php else : ?>
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
					<?php endif; ?>
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

				<?php do_action( 'wcdn_order_item_after', $item, $order, $template ); ?>
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
			<col class="wcdn-col-product">
			<col class="wcdn-col-price">
			<col class="wcdn-col-qty">
			<col class="wcdn-col-total">
		</colgroup>
		<?php if ( isset( $totals['subtotal'] ) ) : ?>
		<tr>
			<td colspan="3" class="wcdn-totals-label"><?php echo esc_html( apply_filters( 'wcdn_invoice_order_total_label', __( 'Subtotal:', 'woocommerce-delivery-notes' ), $order ) ); ?></td>
			<td class="wcdn-totals-value"><?php echo wp_kses_post( $totals['subtotal'] ); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( isset( $totals['tax'] ) ) : ?>
		<tr>
			<td colspan="3" class="wcdn-totals-label"><?php echo esc_html( apply_filters( 'wcdn_invoice_order_total_label', __( 'Tax:', 'woocommerce-delivery-notes' ), $order ) ); ?></td>
			<td class="wcdn-totals-value"><?php echo wp_kses_post( $totals['tax'] ); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( isset( $totals['shipping'] ) ) : ?>
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