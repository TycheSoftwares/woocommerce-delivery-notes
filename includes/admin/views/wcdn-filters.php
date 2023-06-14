<?php
/**
 * Filters file.
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

$filters = array(
	array(
		'wcdn_template_styles',
		'To Assign new template for print-order template. Passed array.',
		"array(
			'name' => __( 'Default', 'woocommerce-delivery-notes' ),
			'type' => 'default',
			'path' => plugin_path . 'templates/print-order/',
			'url'  => plugin_url . 'templates/print-order/',
		)",
	),
	array(
		'wcdn_order_invoice_number',
		'This filter is used to alter the invoice number in all the document templates.',
		'apply_filters( "wcdn_order_invoice_number", get_post_meta( $order_id, $meta_key, true ) )',
	),
	array(
		'wcdn_order_invoice_date',
		'Use this filters for change the date formate for invoice date for all document.',
		'apply_filters( "wcdn_order_invoice_date", $formatted_date, $meta_date )',
	),
	array(
		'wcdn_print_button_name_on_my_account_page',
		'These filter hooks allows to change the label of the Print button on my account page',
		'apply_filters( "wcdn_print_button_name_on_my_account_page", __( "print", "woocommerce-delivery-notes" ), order )',
	),
	array(
		'wcdn_print_button_name_order_page',
		'These filter hooks allows to change the label of the Print button on order page',
		'apply_filters( "wcdn_print_button_name_order_page", __( "print", "woocommerce-delivery-notes" ), $order ) )',
	),
	array(
		'wcdn_theme_print_button_template_type_complete_status',
		'To change url name in place of "Invoice". when order status is completed.',
		'apply_filters( "wcdn_theme_print_button_template_type_complete_status", "invoice" )',
	),
	array(
		'wcdn_theme_print_button_template_type',
		'To change url name in place of "Order". For all order status.',
		'apply_filters( "wcdn_theme_print_button_template_type", "order" )',
	),
	array(
		'wcdn_theme_print_button_template_type_arbitrary',
		'this filter hook allows to change template type based on order status',
		'apply_filters( "wcdn_theme_print_button_template_type_arbitrary", $type, $order )',
	),
	array(
		'wcdn_change_text_of_print_invoice_in_bulk_option',
		'This filter is used to change invoice print button text in order edit page on admin side.',
		'apply_filters( "wcdn_change_text_of_print_invoice_in_bulk_option", __( "print Invoice", "woocommerce-delivery-notes" ) )',
	),
	array(
		'wcdn_change_text_of_print_delivery_note_in_bulk_option',
		'This filter is used to change delivery note print button text in order edit page on admin side.',
		'apply_filters( "wcdn_change_text_of_print_delivery_note_in_bulk_option", __( "print Delivery Note", "woocommerce-delivery-notes" ) )',
	),
	array(
		'wcdn_change_text_of_print_receipt_in_bulk_option',
		'This filter is used to change receipt print button text in order edit page on admin side.',
		'apply_filters( "wcdn_change_text_of_print_receipt_in_bulk_option", __( "print Receipt","woocommerce-delivery-notes" )',
	),
	array(
		'wcdn_address_billing',
		'To change the customer order Billing Address',
		'apply_filters( "wcdn_address_billing", $order->get_formatted_billing_address(), $order )',
	),
	array(
		'wcdn_address_shipping',
		'To change the customer order Shipping Address',
		'apply_filters( "wcdn_address_shipping", $order->get_formatted_shipping_address(), $order )',
	),
	array(
		'wcdn_order_info_fields',
		'Add or remove order information filed to the all document.',
		'apply_filters( "wcdn_order_info_fields", $wcdn_get_order_info( $order ), $order )',
	),
	array(
		'wcdn_document_title',
		'To change the heading of order inforamation listfor all the document',
		'apply_filters( "wcdn_document_title", wcdn_get_template_title() )',
	),
	array(
		'wcdn_formatted_item_price',
		'These filter hooks allows to change the item price in order table for all the document.',
		'apply_filters( "wcdn_formatted_item_price", $subtotal, $item, $order )',
	),
);
?>
<div class="accordion accordion-flush" id="wcdn_filters">
	<?php
	$i = 1;
	foreach ( $filters as $key => $singlefilter ) {
		?>
		<div class="accordion-item">
			<h2 class="accordion-header" id="<?php echo esc_attr( 'wcdn_singlefilter_' . $i ); ?>">
				<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo esc_attr( 'wcdn_singlefilter_content_' . $i ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( 'wcdn_singlefilter_content_' . $i ); ?>">
					<?php echo esc_html( $singlefilter[0] ); ?>
				</button>
			</h2>
			<div id="<?php echo esc_attr( 'wcdn_singlefilter_content_' . $i ); ?>" class="accordion-collapse collapse" aria-labelledby="<?php echo esc_attr( 'wcdn_singlefilter_' . $i ); ?>" data-bs-parent="#wcdn_filters">
				<?php echo esc_html( $singlefilter[1] ); ?><br><br>
				<code><?php echo esc_html( $singlefilter[2] ); ?></code>
			</div>
		</div>
		<?php
		$i++;
	}
	?>
</div>
