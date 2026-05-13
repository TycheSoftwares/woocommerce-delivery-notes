<?php
/**
 * WCDN Document Template.
 *
 * This is the shared layout file rendered for all five document types:
 * invoice, receipt, delivery note, packing slip, and credit note.
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
 *   Actions (fire unconditionally unless noted):
 *   wcdn_before_document( $order, $template )              — before all content
 *   wcdn_before_logo( $order, $template )                  — before shop logo
 *   wcdn_after_logo( $order, $template )                   — after shop logo
 *   wcdn_before_title( $order, $template )                 — before document title
 *   wcdn_after_title( $order, $template )                  — after document title
 *   wcdn_before_branding( $order, $template )              — before shop name/address block
 *   wcdn_after_branding( $order, $template )               — after shop name/address block *
 *   wcdn_before_addresses( $order, $template )             — before billing/shipping/meta
 *   wcdn_after_addresses( $order, $template )              — after billing/shipping/meta *
 *   wcdn_before_items( $order, $template )                 — before the line-items table *
 *   wcdn_order_item_before( $item, $order, $template )     — before each line item
 *   wcdn_order_item_after( $item, $order, $template )      — after each line item
 *   wcdn_after_items( $order, $template )                  — after the line-items table *
 *   wcdn_before_totals( $order, $template )                — before the totals table
 *   wcdn_after_totals( $order, $template )                 — after the totals table
 *   wcdn_before_pay_button( $order, $template )            — before the pay-now button
 *   wcdn_after_pay_button( $order, $template )             — after the pay-now button
 *   wcdn_before_notes( $order, $template )                 — before the customer note
 *   wcdn_after_notes( $order, $template )                  — after the customer note *
 *   wcdn_before_policies( $order, $template )              — before the policies section
 *   wcdn_after_policies( $order, $template )               — after the policies section
 *   wcdn_before_complimentary_close( $order, $template )   — before the complimentary close
 *   wcdn_after_complimentary_close( $order, $template )    — after the complimentary close
 *   wcdn_before_footer( $order, $template )                — before the footer
 *   wcdn_after_footer( $order, $template )                 — after the footer
 *   wcdn_after_document( $order, $template )               — after all content
 *
 *   Filters:
 *   wcdn_watermark_text( $text, $order, $template )        — override watermark text
 *
 *   * = fires only when the section is visible (conditional context)
 *
 * @package WCDN/Templates
 * @version 7.1.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * ============================================================================
 * AVAILABLE VARIABLES
 * ============================================================================
 * All variables below are prepared by Template_Renderer::prepare_template_data()
 * and extracted into scope before this file is included. The full raw data is
 * also accessible via the $data array (e.g. $data['order'], $data['settings']).
 *
 * --- SHOP ---
 *
 * @var array $shop {
 *   Shop/store information pulled from plugin settings.
 *
 *   @type string $name       Store name.
 *   @type string $address    Store address (may contain HTML line breaks).
 *   @type string $phone      Store phone number.
 *   @type string $email      Store email address.
 *   @type string $logo       Logo URL (used in HTML context).
 *   @type string $logo_path  Logo absolute file path (used in PDF context).
 * }
 *
 * --- ORDER ---
 * @var array $order {
 *   Formatted order data for the current document.
 *
 *   @type int    $id              WooCommerce order ID.
 *   @type string $invoiceNumber   Formatted invoice number (e.g. "INV-00042").
 *   @type string $documentDate    Formatted document date string.
 *   @type string $orderNumber     WooCommerce order number (may differ from ID).
 *   @type string $date            Order creation date (ISO 8601).
 *   @type string $paymentDate     Order payment date (ISO 8601), empty if unpaid.
 *   @type string $paymentMethod   Payment gateway title (e.g. "PayPal").
 *   @type string $shippingMethod  Shipping method name (e.g. "Flat rate").
 *   @type string $currency        Currency code (e.g. "USD").
 *   @type string $payment_url     Checkout payment URL; empty when already paid.
 *   @type string $customer_note   Customer-provided order note.
 *   @type string $status          Order status slug (e.g. "processing").
 *   @type array  $billing {
 *     @type string $name     Full billing name.
 *     @type array  $address  Address lines as a flat array of strings.
 *     @type string $phone    Billing phone number.
 *     @type string $email    Billing email address.
 *   }
 *   @type array  $shipping {
 *     @type string $name     Full shipping name.
 *     @type array  $address  Address lines as a flat array of strings.
 *   }
 *   @type array  $items    See $items below.
 *   @type array  $totals   See $totals below.
 *   @type array  $refund   Refund data; used by credit note template only.
 * }
 *
 * --- DOCUMENT ---
 * @var array $document {
 *   Document-level content pulled from template settings.
 *
 *   @type string $policies          Policies text (HTML allowed).
 *   @type string $complimentaryClose Complimentary close text (HTML allowed).
 *   @type string $footer            Footer text (HTML allowed).
 *   @type bool   $isRTL             Whether the site locale is right-to-left.
 * }
 *
 * --- SETTINGS ---
 * @var array $settings {
 *   Per-template settings configured in the admin Template Editor.
 *   All values below are examples; the full set is defined in Template_Engine.
 *
 *   Visibility toggles (bool):
 *   @type bool $showLogo                Show the store logo.
 *   @type bool $showDocumentTitle       Show the document title heading.
 *   @type bool $showShopName            Show the store name.
 *   @type bool $showShopAddress         Show the store address.
 *   @type bool $showShopPhone           Show the store phone number.
 *   @type bool $showShopEmail           Show the store email address.
 *   @type bool $showBillingAddress      Show the billing address column.
 *   @type bool $showShippingAddress     Show the shipping address column.
 *   @type bool $showBillingPhone        Show billing phone in the address block.
 *   @type bool $showBillingEmail        Show billing email in the address block.
 *   @type bool $showProductImages       Show product thumbnail images in the items table.
 *   @type bool $showWatermark           Show the watermark overlay.
 *   @type bool $showCustomerNote        Show the customer order note.
 *   @type bool $showPolicies            Show the policies section.
 *   @type bool $showComplimentaryClose  Show the complimentary close section.
 *   @type bool $showFooter              Show the footer section.
 *   @type bool $showSubtotal            Show the subtotal row in the totals table.
 *   @type bool $showTax                 Show the tax row in the totals table.
 *   @type bool $showShipping            Show the shipping row in the totals table.
 *   @type bool $showProductCharges      Master toggle for subtotal/tax/shipping rows.
 *   @type bool $displayPriceInProductDetailsTable Show price/total columns in the items table.
 *
 *   Labels (string):
 *   @type string $documentTitle         The document heading text (e.g. "Invoice").
 *   @type string $billingAddressText    Label above the billing address block.
 *   @type string $shippingAddressText   Label above the shipping address block.
 *   @type string $billingPhoneText      Label for the billing phone field.
 *   @type string $billingEmailText      Label for the billing email field.
 *   @type string $shopPhoneText         Label prefix for the store phone number.
 *   @type string $shopEmailText         Label prefix for the store email address.
 *   @type string $customerNoteTitle     Label shown before the customer note.
 *   @type string $payNowLabel           Text on the pay-now button.
 *   @type string $logoAlignment         Logo alignment: 'left', 'center', or 'right'.
 *   @type string $watermarkText         Watermark overlay text.
 *   @type string $watermarkLayout       Watermark layout: 'single' or 'repeat'.
 *   @type string $orderDataHeaderText   Optional heading above the order meta table.
 *
 *   Numeric:
 *   @type int $productImageSize         Product image size in pixels (default: 40).
 *   @type int $documentZoom             Document zoom level 70–130 (default: 100).
 * }
 *
 * --- ITEMS ---
 * @var array $items {
 *   Formatted line items. Each element is an associative array:
 *
 *   @type string $name          Product name (may contain HTML).
 *   @type string $sku           Product SKU; empty string when not set.
 *   @type string $price         Formatted unit price HTML (e.g. "<span>$10.00</span>").
 *   @type int    $quantity      Line item quantity.
 *   @type string $total         Formatted line total HTML.
 *   @type int    $product_id    WooCommerce product ID (0 if product deleted).
 *   @type int    $order_item_id WooCommerce order item ID.
 *   @type array  $meta          Extra meta rows: [ [ 'label' => '', 'value' => '' ], … ]
 *   @type array|null $addon     WC Product Addons data: [ 'name' => '', 'value' => '' ] or null.
 *   @type string $image_url     Product image URL (HTML context).
 *   @type string $image_path    Product image absolute path (PDF context).
 * }
 *
 * --- TOTALS ---
 * @var array $totals {
 *   Formatted order totals. Keys are conditionally present based on the order.
 *
 *   @type string $subtotal              Formatted subtotal HTML.
 *   @type string $discount             Formatted discount HTML (present when > 0).
 *   @type string $tax                  Formatted tax HTML (present when no tax lines).
 *   @type array  $tax_lines            Itemised tax lines: [ [ 'label' => '', 'value' => '' ], … ]
 *   @type string $shipping             Formatted shipping total HTML.
 *   @type array  $fee_lines            Fee lines: [ [ 'label' => '', 'value' => '' ], … ]
 *   @type string $total                Formatted order total HTML.
 *   @type bool   $has_refund          True when the order has a non-zero refund.
 *   @type string $refunded            Formatted refunded amount HTML (present when has_refund).
 *   @type string $net_total           Formatted net total HTML (present when has_refund).
 *   @type string $tax_label           Inclusive tax note for net total (may be empty).
 *   @type string $awcdp_deposit       Deposit amount (AWCDP plugin; present when applicable).
 *   @type string $awcdp_future_payments Future payments amount (AWCDP plugin; present when applicable).
 *   @type string $dfw_deposit         Deposit amount (Deposits for WooCommerce plugin).
 *   @type string $dfw_future_payment  Future payment amount (Deposits for WooCommerce plugin).
 *   @type string $dfw_total_cart_amount Total cart amount (Deposits for WooCommerce plugin).
 * }
 *
 * --- OTHER ---
 * @var string $template            Template key: 'invoice', 'receipt', 'deliverynote', 'packingslip', 'creditnote'.
 * @var string $type                Render context: 'html' (print preview) or 'pdf' (Dompdf).
 * @var bool   $is_rtl              True when the document locale is right-to-left.
 * @var bool   $show_billing        True when the billing address column should be rendered.
 * @var bool   $show_shipping       True when the shipping address column should be rendered.
 * @var string $order_meta_position 'columns' (beside addresses) or 'below' (separate table).
 * @var array  $order_meta_fields   Prepared meta rows ready for rendering.
 * @var bool   $has_order_meta      True when at least one meta row has a non-empty value.
 * @var int    $col_width           Width percentage assigned to each address/meta column.
 * @var int    $angle               Watermark rotation angle in degrees.
 * @var bool   $show_pay_now_button True when the pay-now button should be shown.
 */

/*
 * Helper: build an inline style string from a meta field's font settings.
 * $field keys: fontSize, fontWeight, textAlign, color.
 * Returns an empty string when no fontSize is set (no inline style needed).
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

/*
 * Build address column closures.
 * Each visible column (billing, shipping, meta) is stored as a callable so
 * they can be reversed for RTL layouts without duplicating markup logic.
 */
$columns_data = array();

/*
 * Billing address column.
 * Controlled by: Settings > Show Billing Address
 * CSS classes: .wcdn-billing-address
 */
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

		<?php
		/*
		 * Phone and email are only shown here when order data is in 'columns' mode.
		 * In 'below' mode they appear inside the order meta table instead.
		 */
		?>
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

/*
 * Shipping address column.
 * Controlled by: Settings > Show Shipping Address
 * CSS classes: .wcdn-shipping-address
 */
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

/*
 * Order meta column (columns mode only).
 * When order data position is set to 'columns', the meta table appears as a
 * third column beside billing/shipping. When set to 'below', it is rendered
 * as a full-width table below the address grid instead (see further below).
 * CSS classes: .wcdn-order-meta
 */
if ( $has_order_meta && 'columns' === $order_meta_position ) {
	$columns_data['meta'] = function () use ( $order_meta_fields, $settings, $col_width, $meta_style_for ) {
		?>
<td style="width: <?php echo esc_attr( $col_width ); ?>%; vertical-align: top;" class="wcdn-order-meta">
	<table>
		<?php foreach ( $order_meta_fields as $field ) : ?>
			<?php
			/*
			 * Each $field: [ 'key' => string, 'label' => string, 'value' => string,
			 *   'show' => bool, 'fontSize' => int, 'fontWeight' => string,
			 *   'textAlign' => string, 'color' => string ]
			 * Only rows with show=true AND a non-empty value are rendered.
			 */
			?>
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

/* RTL support: reverse column order so billing appears on the right. */
if ( $is_rtl ) {
	$columns_data = array_reverse( $columns_data );
}
?>

<?php
/*
 * DOCUMENT WRAPPER
 * CSS class: .wcdn-document
 * Add .is-rtl when the locale is right-to-left.
 */
?>
<div class="wcdn-document <?php echo $is_rtl ? 'is-rtl' : ''; ?>">

	<?php
	/**
	 * Hook: wcdn_before_document
	 *
	 * Fires at the very start of the document, inside .wcdn-document but before
	 * any content. Use this to prepend custom HTML to every document.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key (e.g. 'invoice').
	 */
	do_action( 'wcdn_before_document', $order, $template );
	?>

	<?php
	/*
	 * WATERMARK
	 * Controlled by: Settings > Watermark
	 * Two layouts available: 'single' (centred overlay) or 'repeat' (tiled grid of 12 spans).
	 * CSS classes: .wcdn-watermark  |  .wcdn-watermark-repeat
	 */
	?>
	<?php
	if (
		! empty( $settings['showWatermark'] ) && ! empty( $settings['watermarkText'] ) ) :
		/**
		 * Filter the watermark text displayed on the document.
		 *
		 * Use this filter to replace the watermark text programmatically
		 * (e.g. based on order status, customer, or document type) without
		 * changing the setting stored in the database.
		 *
		 * @param string $watermark_text The configured watermark text.
		 * @param array  $order          Formatted order data array.
		 * @param string $template       Template key.
		 * @since 7.1.2
		 */
		$watermark_text = apply_filters( 'wcdn_watermark_text', $settings['watermarkText'], $order, $template );
		if ( isset( $settings['watermarkLayout'] ) && 'repeat' === $settings['watermarkLayout'] ) :
			?>
	<div class="wcdn-watermark-repeat">
			<?php for ( $i = 0; $i < 12; $i++ ) : ?>
		<span style="transform: rotate(<?php echo esc_attr( $angle ); ?>deg);">
				<?php echo esc_html( $watermark_text ); ?>
		</span>
		<?php endfor; ?>
	</div>

	<?php else : ?>
	<div class="wcdn-watermark" style="transform: translate(-50%, -50%) rotate(<?php echo esc_attr( $angle ); ?>deg);">
		<?php echo esc_html( $watermark_text ); ?>
	</div>

	<?php endif; ?>
	<?php endif; ?>

	<?php
	/**
	 * Hook: wcdn_before_logo
	 *
	 * Fires before the shop logo block (before the logo visibility check).
	 * Use this to prepend a banner, header image, or custom HTML above the logo.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_before_logo', $order, $template );
	?>

	<?php
	/*
	 * LOGO
	 * Controlled by: Settings > Logo
	 * In PDF context, $shop['logo_path'] (absolute path) is used instead of $shop['logo'] (URL)
	 * so Dompdf can read the file directly.
	 * CSS classes: .wcdn-logo  .wcdn-logo-image
	 * Alignment modifier: .align-left | .align-center | .align-right
	 */
	?>
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

	<?php
	/**
	 * Hook: wcdn_after_logo
	 *
	 * Fires after the logo block (whether the logo is shown or not).
	 * Use this to insert content between the logo and the document title.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_after_logo', $order, $template );
	?>

	<?php
	/**
	 * Hook: wcdn_before_title
	 *
	 * Fires before the document title heading (before the visibility check).
	 * Use this to insert a tagline, reference number, or custom element between
	 * the logo and the document title.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_before_title', $order, $template );
	?>

	<?php
	/*
	 * DOCUMENT TITLE
	 * Controlled by: Settings > Document Title
	 * $settings['documentTitle'] holds the configured heading text.
	 * CSS classes: .wcdn-title
	 */
	?>
	<?php if ( ! isset( $settings['showDocumentTitle'] ) || ! empty( $settings['showDocumentTitle'] ) ) : ?>
	<h1 class="wcdn-title">
		<?php echo esc_html( isset( $settings['documentTitle'] ) ? $settings['documentTitle'] : __( 'Document', 'woocommerce-delivery-notes' ) ); ?>
	</h1>
	<?php endif; ?>

	<?php
	/**
	 * Hook: wcdn_after_title
	 *
	 * Fires after the document title heading.
	 * Use this to insert a subtitle, tagline, or custom metadata line.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_after_title', $order, $template );
	?>

	<?php
	/**
	 * Hook: wcdn_before_branding
	 *
	 * Fires before the shop name/address/phone/email block (before the visibility
	 * check). Use this to insert content between the document title and the
	 * store details regardless of whether the branding block is enabled.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_before_branding', $order, $template );
	?>

	<?php
	/*
	 * SHOP / BRANDING BLOCK
	 * Controlled by: Settings > Shop Name / Address / Phone / Email
	 * The entire block (including its hr) is hidden when all four toggles are off.
	 * CSS classes: .wcdn-shop  .wcdn-shop-name  .wcdn-shop-address
	 *              .wcdn-shop-phone  .wcdn-shop-email
	 */
	?>
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

		<?php
		/*
		 * $shop['address'] may contain <br> tags from the settings textarea.
		 * nl2br + wp_kses_post preserves intentional line breaks safely.
		 */
		?>
		<?php if ( ! empty( $settings['showShopAddress'] ) && ! empty( $shop['address'] ) ) : ?>
		<div class="wcdn-shop-address"><?php echo nl2br( wp_kses_post( $shop['address'] ) ); ?></div>
		<?php endif; ?>

		<?php
		/*
		 * $settings['shopPhoneText'] is the label prefix (e.g. "Phone").
		 * When set, it is prepended as "Phone: 555-1234".
		 */
		?>
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

		<?php
		/**
		 * Hook: wcdn_after_branding
		 *
		 * Fires after the shop name/address/phone/email block.
		 * Only fires when at least one branding field is enabled.
		 * Use this to append additional store information (e.g. VAT number,
		 * registration number) directly below the shop details.
		 *
		 * @param array  $order    Formatted order data array.
		 * @param string $template Template key.
		 */
		do_action( 'wcdn_after_branding', $order, $template );
		?>

	<?php endif; ?>

	<?php
	/**
	 * Hook: wcdn_before_addresses
	 *
	 * Fires before the billing/shipping address grid and order meta block (before
	 * the visibility check). Use this to add content between the branding and
	 * address sections regardless of whether any address is enabled.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_before_addresses', $order, $template );
	?>

	<?php
	/*
	 * ADDRESS GRID + ORDER META
	 * $show_address_grid — true when billing and/or shipping is enabled.
	 * $show_meta_below   — true when order data position is 'below'.
	 * The hr and this entire section are hidden when both are false.
	 * CSS classes: .wcdn-address-grid  .wcdn-order-meta
	 *              .wcdn-order-meta-below  .wcdn-order-data-header
	 */
	?>
	<?php
	$show_address_grid = ! empty( $columns_data );
	$show_meta_below   = 'below' === $order_meta_position && $has_order_meta;
	?>
	<?php if ( $show_address_grid || $show_meta_below ) : ?>

	<hr />

		<?php
		/*
		 * Address grid: a single-row table with one <td> per column.
		 * Column width ($col_width %) is divided equally across visible columns.
		 * Column order is reversed automatically for RTL locales (see above).
		 */
		?>
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
			<?php
			/*
			 * Optional section header above the meta table.
			 * Controlled by: Settings > Order Data Header / Order Data Header Border.
			 */
			?>
			<?php if ( ! empty( $settings['showOrderDataHeader'] ) && ! empty( $settings['orderDataHeaderText'] ) ) : ?>
	<p class="wcdn-order-data-header">
				<?php echo esc_html( $settings['orderDataHeaderText'] ); ?>
	</p>
				<?php if ( ! empty( $settings['showOrderDataHeaderBorder'] ) ) : ?>
	<hr class="wcdn-order-data-header-border" style="margin: 0" />
	<?php endif; ?>
	<?php endif; ?>

			<?php
			/*
			 * Full-width order meta table (shown when position = 'below').
			 * Each $field in $order_meta_fields: [ 'key', 'label', 'value', 'show',
			 *   'fontSize', 'fontWeight', 'textAlign', 'color' ]
			 * CSS row class: .wcdn-meta-{key} (e.g. .wcdn-meta-orderNumber)
			 */
			?>
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

		<?php
		/**
		 * Hook: wcdn_after_addresses
		 *
		 * Fires after the billing/shipping address grid and order meta block.
		 * Only fires when at least one address or meta section is visible.
		 * Use this to add content between the address block and the items table
		 * (e.g. a delivery instructions field or a custom reference number).
		 *
		 * @param array  $order    Formatted order data array.
		 * @param string $template Template key.
		 */
		do_action( 'wcdn_after_addresses', $order, $template );
		?>

	<?php endif; ?>

	<?php
	/*
	 * LINE ITEMS TABLE
	 * Hidden entirely when $items is empty (e.g. manual-amount refunds).
	 * $show_price_cols — true when price/total columns should be shown.
	 *   For normal templates: controlled by displayPriceInProductDetailsTable setting.
	 *   For credit notes: controlled by displayRefundItemsInTable.
	 * Column widths (with prices):    Product 50% | Price 15% | Qty 15% | Total 20%
	 * Column widths (without prices): Product 80% | Qty 20%
	 * CSS classes: .wcdn-items  .wcdn-col-product  .wcdn-col-price
	 *              .wcdn-col-qty  .wcdn-col-total  .wcdn-product-cell
	 *              .wcdn-item-name  .wcdn-item-sku  .wcdn-item-meta
	 *              .wcdn-item-image  .wcdn-item-layout
	 *              .wcdn-item-addon-name  .wcdn-item-addon-value
	 */
	?>
	<?php if ( ! empty( $items ) ) : ?>
		<?php
		$show_price_cols = ! empty( $settings['displayPriceInProductDetailsTable'] ) ||
		( 'creditnote' === $template && ! empty( $settings['displayRefundItemsInTable'] ) );
		?>

		<?php
		/**
		 * Hook: wcdn_before_items
		 *
		 * Fires immediately before the line-items table.
		 * Use this to add a custom section header or summary row above the table.
		 *
		 * @param array  $order    Formatted order data array.
		 * @param string $template Template key.
		 */
		do_action( 'wcdn_before_items', $order, $template );
		?>

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
				<?php
				/* Header label switches to "Refunded Item" for credit notes. */
				?>
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
					<?php
					/* "Total Refunded" for credit notes; "Total" for everything else. */
					echo esc_html( ( 'creditnote' === $template ) ? __( 'Total Refunded', 'woocommerce-delivery-notes' ) : __( 'Total', 'woocommerce-delivery-notes' ) );
					?>
				</th>
				<?php endif; ?>
			</tr>
		</thead>

		<tbody>
			<?php
			/*
			 * Resolve the native WC_Order and WC_Order_Item objects for this order.
			 * These are passed to the deprecated pre-v7 wcdn_order_item_before/after hooks
			 * for backwards compatibility. New code should use $item, $order, $template.
			 */
			$_wcdn_wc_order = ! empty( $order['id'] ) ? wc_get_order( $order['id'] ) : null;
			?>
			<?php foreach ( $items as $item ) : ?>
				<?php
				/*
				 * Resolve product and order item objects for the deprecated hooks.
				 * $item is the formatted array; $_wcdn_wc_order_item is the raw WC object.
				 */
				$_wcdn_wc_product    = ! empty( $item['product_id'] ) ? wc_get_product( $item['product_id'] ) : null;
				$_wcdn_wc_order_item = ( $_wcdn_wc_order && ! empty( $item['order_item_id'] ) )
					? $_wcdn_wc_order->get_item( $item['order_item_id'] )
					: null;
				?>
			<tr>
				<td class="wcdn-product-cell">
					<?php
					/**
					 * Hook: wcdn_order_item_before
					 *
					 * Fires before each line item's product cell content.
					 * Use this to prepend custom HTML to a specific item row.
					 *
					 * Note: the deprecated form passed ( $product, $order, $item ) as
					 * WC objects. The current signature is:
					 *
					 * @param array  $item     Formatted item array (name, sku, price, etc.).
					 * @param array  $order    Formatted order data array.
					 * @param string $template Template key.
					 */
					do_action_deprecated(
						'wcdn_order_item_before',
						array( $_wcdn_wc_product, $_wcdn_wc_order, $_wcdn_wc_order_item ),
						'7.0.0',
						'wcdn_order_item_before',
						esc_html__( 'The wcdn_order_item_before hook argument order changed in v7.0. Update your callback to accept ( $item, $order, $template ) instead of ( $product, $order, $item ).', 'woocommerce-delivery-notes' )
					);
					do_action( 'wcdn_order_item_before', $item, $order, $template );
					?>

					<?php
					/*
					 * WC Product Addons (PAO): when an item is a PAO add-on, it carries
					 * 'addon' data instead of a standard name+meta layout.
					 */
					?>
					<?php if ( ! empty( $item['addon'] ) ) : ?>
					<div class="wcdn-item-addon-name"><?php echo esc_html( $item['addon']['name'] ); ?></div>
					<div class="wcdn-item-addon-value"><?php echo esc_html( $item['addon']['value'] ); ?></div>
					<?php else : ?>
						<?php
						/*
						 * Product image: shown when Settings > Show Product Images is enabled.
						 * In PDF context, $item['image_path'] (absolute path) is used so
						 * Dompdf can load the image from disk.
						 * Image size is configurable via Settings > Product Image Size (px).
						 */
						$img_src  = '';
						$img_size = (int) ( $settings['productImageSize'] ?? 40 );
						if ( ! empty( $settings['showProductImages'] ) ) {
							$img_src = ( 'pdf' === $type ) ? ( $item['image_path'] ?? '' ) : ( $item['image_url'] ?? '' );
						}
						$has_image = ! empty( $img_src );
						?>

						<?php
						/*
						 * When an image is present, a nested table is used to align the image
						 * and text side by side. This avoids float/flex which Dompdf does not
						 * fully support.
						 */
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
							<td style="vertical-align:middle;">
								<?php endif; ?>

								<?php
								/*
								 * Product name: filtered via wcdn_order_item_name.
								 * SKU: shown inline when non-empty; toggle in CSS via .wcdn-item-sku.
								 */
								?>
								<span class="wcdn-item-name">
									<?php echo wp_kses_post( $item['name'] ); ?>
									<?php if ( ! empty( $item['sku'] ) ) : ?>
									<span
										class="wcdn-item-sku"><?php echo wp_kses_post( '(' . __( 'SKU', 'woocommerce-delivery-notes' ) . ': ' . $item['sku'] . ')' ); ?></span>
									<?php endif; ?>
								</span>

								<?php
								/*
								 * Item meta: variation attributes, custom fields, download count, etc.
								 * Each row: [ 'label' => string, 'value' => string ]
								 * CSS classes: .wcdn-item-meta  dt  dd
								 */
								?>
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
					/**
					 * Hook: wcdn_order_item_after
					 *
					 * Fires after each line item's product cell content.
					 * Use this to append custom HTML below a specific item (e.g. a
					 * delivery date, gift message, or fulfilment note).
					 *
					 * @param array  $item     Formatted item array.
					 * @param array  $order    Formatted order data array.
					 * @param string $template Template key.
					 */
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
		/*
		 * Total Quantity footer row: shown only when the table has more than one
		 * non-addon item. Add-on rows are excluded from the count because they do
		 * not represent standalone quantities.
		 * CSS class: .wcdn-total-quantity
		 */
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

		<?php
		/**
		 * Hook: wcdn_after_items
		 *
		 * Fires immediately after the line-items table closing tag.
		 * Use this to add a custom summary block, totals override, or notes
		 * section directly below the items.
		 *
		 * @param array  $order    Formatted order data array.
		 * @param string $template Template key.
		 */
		do_action( 'wcdn_after_items', $order, $template );
		?>
	<?php endif; ?>

	<?php
	/**
	 * Hook: wcdn_before_totals
	 *
	 * Fires before the order totals table (before the visibility check).
	 * Use this to insert a custom summary row, a sub-total note, or a separator
	 * between the items table and the totals regardless of whether totals are shown.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_before_totals', $order, $template );
	?>

	<?php
	/*
	 * ORDER TOTALS TABLE
	 * ==================
	 * Shown only when:
	 *   - $totals['total'] is set, AND
	 *   - template is not 'creditnote', AND
	 *   - displayPriceInProductDetailsTable is enabled.
	 *
	 * Rows rendered conditionally based on $totals keys and settings:
	 *   subtotal     — shown when showProductCharges + showSubtotal on
	 *   discount     — shown when $totals['discount'] exists
	 *   tax/tax_lines— shown when showProductCharges + showTax on
	 *   shipping     — shown when showProductCharges + showShipping on
	 *   fee_lines    — always shown when present
	 *   total        — always shown (bold)
	 *   refunded/net — shown when $totals['has_refund'] is true
	 *   awcdp_*      — Deposits & Partial Payments for WC Pro rows
	 *   dfw_*        — Deposits for WooCommerce rows
	 *
	 * CSS classes: .wcdn-totals  .wcdn-totals-label  .wcdn-totals-value
	 *              .wcdn-total (on the grand total row)
	 *
	 * To customise row labels use the filter:
	 *   wcdn_invoice_order_total_label( $label, $order )
	 */
	?>
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
			<?php
			/*
			 * Zero-height spacer row keeps the totals columns aligned with the items table
			 * columns above. Dompdf requires explicit width declarations on each cell.
			 */
			?>
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

			<?php
			/*
			 * Tax: renders itemised lines when available, falls back to a single row.
			 * Both are gated behind showProductCharges + showTax toggles.
			 */
			?>
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

			<?php
			/*
			 * Fee lines: injected by WooCommerce or third-party plugins (e.g. payment
			 * gateway surcharges). Rendered unconditionally when present.
			 */
			?>
			<?php if ( ! empty( $totals['fee_lines'] ) ) : ?>
				<?php foreach ( $totals['fee_lines'] as $fee_line ) : ?>
					<?php $render_totals_row( $fee_line['value'], $fee_line['label'] . ':' ); ?>
			<?php endforeach; ?>
			<?php endif; ?>

			<?php
			/*
			 * When has_refund is true, the order has been partially or fully refunded.
			 * Three rows are shown: Order Total, Refund, and a bold Net Total.
			 * Otherwise a single bold Total row is shown.
			 */
			?>
			<?php if ( ! empty( $totals['has_refund'] ) ) : ?>
				<?php $render_totals_row( $totals['total'], __( 'Order Total:', 'woocommerce-delivery-notes' ), true ); ?>
				<?php $render_totals_row( $totals['refunded'], __( 'Refund:', 'woocommerce-delivery-notes' ) ); ?>
				<?php $render_totals_row( $totals['net_total'] . ( ! empty( $totals['tax_label'] ) ? ' ' . $totals['tax_label'] : '' ), __( 'Total:', 'woocommerce-delivery-notes' ), true, 'wcdn-total' ); ?>
			<?php else : ?>
				<?php $render_totals_row( $totals['total'], __( 'Total:', 'woocommerce-delivery-notes' ), true, 'wcdn-total' ); ?>
			<?php endif; ?>

			<?php
			/*
			 * Deposits & Partial Payments for WooCommerce Pro (AWCDP) rows.
			 * Added by the AWCDP integration via the wcdn_order_totals filter.
			 */
			?>
			<?php if ( isset( $totals['awcdp_deposit'] ) ) : ?>
				<?php $render_totals_row( $totals['awcdp_deposit'], __( 'Deposit:', 'woocommerce-delivery-notes' ) ); ?>
			<?php endif; ?>

			<?php if ( isset( $totals['awcdp_future_payments'] ) ) : ?>
				<?php $render_totals_row( $totals['awcdp_future_payments'], __( 'Future Payments:', 'woocommerce-delivery-notes' ) ); ?>
			<?php endif; ?>

			<?php
			/*
			 * Deposits for WooCommerce (DFW) rows.
			 * Added via the wcdn_order_totals filter when DFW is active.
			 */
			?>
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

		<?php
		/**
		 * Hook: wcdn_after_totals
		 *
		 * Fires after the order totals table (or after the items table when
		 * price columns are disabled).
		 * Use this to add a custom totals row, a payment summary, or a
		 * tax breakdown that the core plugin does not render.
		 *
		 * @param array  $order    Formatted order data array.
		 * @param string $template Template key.
		 */
		do_action( 'wcdn_after_totals', $order, $template );
		?>

	<?php
	/**
	 * Hook: wcdn_before_pay_button
	 *
	 * Fires before the pay-now button block (before the visibility check).
	 * Use this to add a payment instructions notice or custom CTA above the button.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_before_pay_button', $order, $template );
	?>

	<?php
	/*
	 * PAY NOW BUTTON
	 * Shown when $show_pay_now_button is true, which requires:
	 *   - Settings > Pay Now enabled, AND
	 *   - the order has a payment URL (i.e. it has not yet been paid).
	 * CSS classes: .wcdn-pay  .wcdn-payment-button
	 */
	?>
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

	<?php
	/**
	 * Hook: wcdn_after_pay_button
	 *
	 * Fires after the pay-now button block (whether or not the button is shown).
	 * Use this to add payment instructions or a QR code below the button.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_after_pay_button', $order, $template );
	?>

	<?php
	/**
	 * Hook: wcdn_before_notes
	 *
	 * Fires before the customer note block (before the visibility check).
	 * Use this to add a separator or intro text above the customer note section.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_before_notes', $order, $template );
	?>

	<?php
	/*
	 * CUSTOMER NOTE
	 * Controlled by: Settings > Customer Note
	 * $order['customer_note'] is the note the customer entered at checkout.
	 * CSS classes: .wcdn-customer-note
	 */
	?>
	<?php if ( ! empty( $settings['showCustomerNote'] ) && ! empty( $order['customer_note'] ) ) : ?>
		<hr style="margin: 5px 0;" />
		<div class="wcdn-customer-note">
			<?php echo esc_html( $settings['customerNoteTitle'] ); ?>:
			<?php echo esc_html( $order['customer_note'] ); ?>
		</div>

		<?php
		/**
		 * Hook: wcdn_after_notes
		 *
		 * Fires after the customer note block.
		 * Only fires when the note is visible (setting on + note non-empty).
		 * Use this to add a custom notice or internal memo below the note.
		 *
		 * @param array  $order    Formatted order data array.
		 * @param string $template Template key.
		 */
		do_action( 'wcdn_after_notes', $order, $template );
		?>

	<?php endif; ?>

	<?php
	/**
	 * Hook: wcdn_before_policies
	 *
	 * Fires before the policies block (before the visibility check).
	 * Use this to insert content between the customer note and the policies section.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_before_policies', $order, $template );
	?>

	<?php
	/*
	 * POLICIES
	 * Controlled by: Settings > Policies
	 * $document['policies'] comes from the template's Policies textarea.
	 * HTML is allowed (wp_kses_post).
	 * CSS classes: .wcdn-policies
	 */
	?>
	<?php if ( ! empty( $settings['showPolicies'] ) && ! empty( $document['policies'] ) ) : ?>
		<hr style="margin: 5px 0;" />
		<div class="wcdn-policies">
			<?php echo wp_kses_post( $document['policies'] ); ?>
		</div>
	<?php endif; ?>

	<?php
	/**
	 * Hook: wcdn_after_policies
	 *
	 * Fires after the policies block (after the visibility check).
	 * Use this to append content below the policies section.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_after_policies', $order, $template );
	?>

	<?php
	/**
	 * Hook: wcdn_before_complimentary_close
	 *
	 * Fires before the complimentary close block (before the visibility check).
	 * Use this to insert a signature line or custom message above the closing.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_before_complimentary_close', $order, $template );
	?>

	<?php
	/*
	 * COMPLIMENTARY CLOSE
	 * Controlled by: Settings > Complimentary Close
	 * $document['complimentaryClose'] comes from the template textarea.
	 * HTML is allowed (wp_kses_post).
	 * CSS classes: .wcdn-complimentary-close
	 */
	?>
	<?php if ( ! empty( $settings['showComplimentaryClose'] ) && ! empty( $document['complimentaryClose'] ) ) : ?>
		<hr style="margin: 5px 0;" />
		<div class="wcdn-complimentary-close">
			<?php echo wp_kses_post( $document['complimentaryClose'] ); ?>
		</div>
	<?php endif; ?>

	<?php
	/**
	 * Hook: wcdn_after_complimentary_close
	 *
	 * Fires after the complimentary close block (after the visibility check).
	 * Use this to add a digital signature image or custom sign-off below the close.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_after_complimentary_close', $order, $template );
	?>

	<?php
	/**
	 * Hook: wcdn_before_footer
	 *
	 * Fires before the footer block (before the visibility check).
	 * Use this to add a separator or custom content just above the footer.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_before_footer', $order, $template );
	?>

	<?php
	/*
	 * FOOTER.
	 * Controlled by: Settings > Footer.
	 * $document['footer'] comes from the template's Footer textarea.
	 * HTML is allowed (wp_kses_post).
	 * CSS classes: .wcdn-footer.
	 */
	?>
	<?php if ( ! empty( $settings['showFooter'] ) && ! empty( $document['footer'] ) ) : ?>
		<hr style="margin: 5px 0;" />
		<div class="wcdn-footer">
			<?php echo wp_kses_post( $document['footer'] ); ?>
		</div>
	<?php endif; ?>

	<?php
	/**
	 * Hook: wcdn_after_footer
	 *
	 * Fires after the footer block (after the visibility check).
	 * Use this to append content at the very bottom of the document, below the
	 * footer, but before wcdn_after_document.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_after_footer', $order, $template );
	?>

	<?php
	/**
	 * Hook: wcdn_after_document
	 *
	 * Fires at the very end of the document, before the closing .wcdn-document
	 * div. Use this to append any custom HTML that should appear on every
	 * document regardless of which sections are enabled.
	 *
	 * @param array  $order    Formatted order data array.
	 * @param string $template Template key.
	 */
	do_action( 'wcdn_after_document', $order, $template );
	?>

</div>
