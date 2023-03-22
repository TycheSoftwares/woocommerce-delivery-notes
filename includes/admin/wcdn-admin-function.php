<?php
/**
 * Admin Functions
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wcdn_customization_label() {
	$customization_label = array(
		'document_setting'    => 'Document Title',
		'template_setting'    => 'Template Type',
		'company_setting'     => 'Company Logo / Name',
		'company_address'     => 'Company Address',
		'invoice_number'      => 'Invoice Number',
		'order_number'        => 'Order Number',
		'invoice_date'        => 'Invoice Date',
		'order_date'          => 'Order Date',
		'billing_address'     => 'Billing Address',
		'shipping_address'    => 'Shipping Address',
		'email_address'       => 'Email Address',
		'phone_number'        => 'Phone Number',
		'customer_note'       => 'Customer Note',
		'complimentary_close' => 'Complimentary Close',
		'footer'			  => 'Footer',
		'payment_received_stamp'      => 'Payment Received Stamp',
		'display_price_product_table' => 'Display price in product details table'
	);
	return $customization_label;
}

function wcdn_customization() {
	$customization = array(
		'invoice' => array(
			'document_setting'   => array('Title', 'Font Size', 'Text Align', 'Text Colour'),
			'template_setting'   => array('Template'),
			'company_setting'    => array('Display'),
			'company_address'    => array('Title', 'Text Align', 'Text Colour'),
			'invoice_number'     => array('Text', 'Font Size', 'Style', 'Text Colour'),
			'order_number'       => array('Text', 'Font Size', 'Style', 'Text Colour'),
			'invoice_date'       => array('Text', 'Format', 'Font Size', 'Style', 'Text Colour'),
			'order_date'         => array('Text', 'Format', 'Font Size', 'Style', 'Text Colour'),
			'billing_address'    => array('Title', 'Text Align', 'Text Colour'),
			'shipping_address'   => array('Title', 'Text Align', 'Text Colour'),
			'email_address'      => array('Title', 'Font Size', 'Text Colour'),
			'phone_number'       => array('Title', 'Font Size', 'Text Colour'),
			'customer_note'      => array('Title', 'Font Size', 'Text Colour'),
			'footer'             => array('Font Size', 'Text Colour'),
		),
		'receipt' => array(
			'document_setting'   => array('Title', 'Font Size', 'Text Align', 'Text Colour'),
			'template_setting'   => array('Template'),
			'company_setting'    => array('Display'),
			'company_address'    => array('Title', 'Text Align', 'Text Colour'),
			'order_number'       => array('Text', 'Font Size', 'Style', 'Text Colour'),
			'payment_received_stamp' => array('Width', 'Height', 'Text', 'Font Size', 'Border Width', 'Line Height', 'Opacity', 'Border radius', 'Form left', 'From Top', 'Angle', 'Colour' ),
			'order_date'         => array('Text', 'Format', 'Font Size', 'Style', 'Text Colour'),
			'billing_address'    => array('Title', 'Text Align', 'Text Colour'),
			'shipping_address'   => array('Title', 'Text Align', 'Text Colour'),
			'email_address'      => array('Title', 'Font Size', 'Text Colour'),
			'phone_number'       => array('Title', 'Font Size', 'Text Colour'),
			'customer_note'      => array('Title', 'Font Size', 'Text Colour'),
			'footer'             => array('Font Size', 'Text Colour'),
		),
		'deliverynote' => array(
			'document_setting'   => array('Title', 'Font Size', 'Text Align', 'Text Colour'),
			'template_setting'   => array('Template'),
			'company_setting'    => array('Display'),
			'company_address'    => array('Title', 'Text Align', 'Text Colour'),
			'order_number'       => array('Text', 'Font Size', 'Style', 'Text Colour'),
			'order_date'         => array('Text', 'Format', 'Font Size', 'Style', 'Text Colour'),
			'billing_address'    => array('Title', 'Text Align', 'Text Colour'),
			'shipping_address'   => array('Title', 'Text Align', 'Text Colour'),
			'email_address'      => array('Title', 'Font Size', 'Text Colour'),
			'phone_number'       => array('Title', 'Font Size', 'Text Colour'),
			'customer_note'      => array('Title', 'Font Size', 'Text Colour'),
			'complimentary_close'=> array('Title', 'Font Size', 'Text Colour'),
			'footer'             => array('Font Size', 'Text Colour'),
		)
	);
	return $customization;
}

function wcdn_customization_textfield( $tab, $id, $field, $key, $customization_data ) {
	?>
	<div class="form-group row">
        <label for="<?php echo $id; ?>" class="col-sm-12 col-form-label"><?php echo $field; ?></label>
        <div class="col-sm-12">
            <input type="text" class="form-control" name="<?php echo $tab.'['.$key.']['.$id.']'; ?>" id="<?php echo $id; ?>" value="<?php if(isset($customization_data[$key][$id])) { echo $customization_data[$key][$id]; } ?>">
        </div>
    </div>
	<?php
}

function wcdn_customization_numberfield( $tab, $id, $field, $key, $customization_data ) {
	?>
	<div class="form-group row">
		<label for="<?php echo $id; ?>" class="col-sm-12 col-form-label"><?php echo $field; ?></label>
        <div class="col-sm-12">
            <input type="number" class="form-control" name="<?php echo $tab.'['.$key.']['.$id.']'; ?>" id="<?php echo $id; ?>" value="<?php if(isset($customization_data[$key][$id])) { echo $customization_data[$key][$id]; } ?>">
        </div>
	</div>
	<?php
}

function wcdn_customization_selectbox( $tab, $id, $field, $key, $customization_data, $option = array() ) {
	?>
	<div class="form-group row">
		<label for="<?php echo $id; ?>" class="col-sm-12 col-form-label"><?php echo $field; ?></label>
	    <div class="col-sm-12">
	        <select name="<?php echo $tab.'['.$key.']['.$id.']'; ?>">
	        	<?php 
	        	foreach ($option as $value) {
	        		$formate_value = strtolower(str_replace(' ', '_', $value));
	        		echo $customization_data[$key][$id];
	        		if(isset($customization_data[$key][$id]) && $formate_value == $customization_data[$key][$id] ) {
	        			$select = "selected";
	        		} else {
	        			$select = "";
	        		}
	        		echo '<option value='.$formate_value.' '.$select.'>'. ucfirst($value) .'</option>';
	        	}
	        	?>
	        </select>
	    </div>
	</div>
	<?php
}

function wcdn_customization_colorfield( $tab, $id, $field, $key, $customization_data ) {
	?>
	<div class="form-group row">
		<label for="<?php echo $id; ?>" class="col-sm-12 col-form-label"><?php echo $field; ?></label>
        <div class="col-sm-12">
            <input type="color" class="form-control" name="<?php echo $tab.'['.$key.']['.$id.']'; ?>" id="<?php echo $id; ?>" value="<?php if(isset($customization_data[$key][$id])) { echo $customization_data[$key][$id]; } ?>">
        </div>
	</div>
	<?php
}

function wcdn_get_data() {
	$wcdn_general_settings           = get_option( 'wcdn_general_settings' );
    $wcdn_document_settings          = get_option( 'wcdn_document_settings' );
    $wcdn_invoice_settings           = get_option( 'wcdn_invoice_settings' );
    $wcdn_receipt_settings           = get_option( 'wcdn_receipt_settings' );
    $wcdn_deliverynote_settings      = get_option( 'wcdn_deliverynote_settings' );
    $wcdn_invoice_customization      = get_option( 'wcdn_invoice_customization' );
    $wcdn_recepit_customization      = get_option( 'wcdn_recepit_customization' );
    $wcdn_deliverynote_customization = get_option( 'wcdn_deliverynote_customization' );
    
}

?>
