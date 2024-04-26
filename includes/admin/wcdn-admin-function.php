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

/**
 * Function for all labels.
 */
function wcdn_customization_label() {
	$customization_label = array(
		'template_setting'            => __( 'Template Type', 'woocommerce-delivery-notes' ),
		'document_setting'            => __( 'Document Title', 'woocommerce-delivery-notes' ),
		'company_logo'                => __( 'Company Logo', 'woocommerce-delivery-notes' ),
		'company_name'                => __( 'Company Name', 'woocommerce-delivery-notes' ),
		'company_address'             => __( 'Company Address', 'woocommerce-delivery-notes' ),
		'invoice_number'              => __( 'Invoice Number', 'woocommerce-delivery-notes' ),
		'order_number'                => __( 'Order Number', 'woocommerce-delivery-notes' ),
		'order_date'                  => __( 'Order Date', 'woocommerce-delivery-notes' ),
		'payment_method'              => __( 'Payment Method', 'woocommerce-delivery-notes' ),
		'payment_date'  	      => __( 'Payment Date', 'woocommerce-delivery-notes' ), 
		'billing_address'             => __( 'Billing Address', 'woocommerce-delivery-notes' ),
		'shipping_address'            => __( 'Shipping Address', 'woocommerce-delivery-notes' ),
		'email_address'               => __( 'Email Address', 'woocommerce-delivery-notes' ),
		'phone_number'                => __( 'Phone Number', 'woocommerce-delivery-notes' ),
		'customer_note'               => __( 'Customer Note', 'woocommerce-delivery-notes' ),
		'complimentary_close'         => __( 'Complimentary Close', 'woocommerce-delivery-notes' ),
		'policies'                    => __( 'Policies', 'woocommerce-delivery-notes' ),
		'footer'                      => __( 'Footer', 'woocommerce-delivery-notes' ),
		'payment_received_stamp'      => __( 'Payment Received Stamp', 'woocommerce-delivery-notes' ),
		'display_price_product_table' => __( 'Display price in product details table', 'woocommerce-delivery-notes' ),
	);
	return $customization_label;
}

/**
 * Each setting field for customization.
 */
function wcdn_customization() {
	$customization = array(
		'invoice'      => array(
			'template_setting'    => array( 'Template' ),
			'document_setting'    => array( 'Title', 'Font Size', 'Text Align', 'Text Colour' ),
			'company_logo'        => '',
			'company_name'        => array( 'Text Align', 'Font Size', 'Text Colour' ),
			'company_address'     => array( 'Text Align', 'Font Size', 'Text Colour' ),
			'invoice_number'      => array( 'Text', 'Font Size', 'Style', 'Text Colour' ),
			'order_number'        => array( 'Text', 'Font Size', 'Style', 'Text Colour' ),
			'order_date'          => array( 'Text', 'Format', 'Font Size', 'Style', 'Text Colour' ),
			'payment_method'      => array( 'Text', 'Font Size', 'Style', 'Text Colour' ),
			'billing_address'     => array( 'Title', 'Text Align', 'Text Colour' ),
			'shipping_address'    => array( 'Title', 'Text Align', 'Text Colour' ),
			'email_address'       => '',
			'phone_number'        => '',
			'customer_note'       => array( 'Title', 'Font Size', 'Text Colour' ),
			'complimentary_close' => array( 'Font Size', 'Text Colour' ),
			'policies'            => array( 'Font Size', 'Text Colour' ),
			'footer'              => array( 'Font Size', 'Text Colour' ),
		),
		'receipt'      => array(
			'template_setting'       => array( 'Template' ),
			'document_setting'       => array( 'Title', 'Font Size', 'Text Align', 'Text Colour' ),
			'company_logo'     		 => '',
			'company_name'     		 => array( 'Text Align', 'Font Size', 'Text Colour' ),
			'company_address'  		 => array( 'Text Align', 'Font Size', 'Text Colour' ),
			'invoice_number'         => array( 'Text', 'Font Size', 'Style', 'Text Colour' ),
			'order_number'           => array( 'Text', 'Font Size', 'Style', 'Text Colour' ),
			'order_date'             => array( 'Text', 'Format', 'Font Size', 'Style', 'Text Colour' ),
			'payment_method'         => array( 'Text', 'Font Size', 'Style', 'Text Colour' ),
			'payment_date'           => array( 'Text', 'Font Size', 'Style', 'Text Colour' ),
			'billing_address'        => array( 'Title', 'Text Align', 'Text Colour' ),
			'shipping_address'       => array( 'Title', 'Text Align', 'Text Colour' ),
			'email_address'          => '',
			'phone_number'           => '',
			'customer_note'          => array( 'Title', 'Font Size', 'Text Colour' ),
			'complimentary_close'    => array( 'Font Size', 'Text Colour' ),
			'policies'               => array( 'Font Size', 'Text Colour' ),
			'footer'                 => array( 'Font Size', 'Text Colour' ),
			'payment_received_stamp' => array( 'Text' ),
		),
		'deliverynote' => array(
			'template_setting'    		  => array( 'Template' ),
			'document_setting'    		  => array( 'Title', 'Font Size', 'Text Align', 'Text Colour' ),
			'company_logo'     	          => '',
			'company_name'     	          => array( 'Text Align', 'Font Size', 'Text Colour' ),
			'company_address'  	          => array( 'Text Align', 'Font Size', 'Text Colour' ),
			'invoice_number'      		  => array( 'Text', 'Font Size', 'Style', 'Text Colour' ),
			'order_number'        		  => array( 'Text', 'Font Size', 'Style', 'Text Colour' ),
			'order_date'          		  => array( 'Text', 'Format', 'Font Size', 'Style', 'Text Colour' ),
			'billing_address'     		  => array( 'Title', 'Text Align', 'Text Colour' ),
			'shipping_address'    		  => array( 'Title', 'Text Align', 'Text Colour' ),
			'email_address'       		  => '',
			'phone_number'        		  => '',
			'display_price_product_table'     => '',
			'customer_note'       		  => array( 'Title', 'Font Size', 'Text Colour' ),
			'complimentary_close' 		  => array( 'Font Size', 'Text Colour' ),
			'policies'            		  => array( 'Font Size', 'Text Colour' ),
			'footer'              		  => array( 'Font Size', 'Text Colour' ),
		),
	);
	return $customization;
}

/**
 * This function returns the Text field.
 *
 * @param array  $tab Setting field array for each document.
 * @param int    $id Field Id name.
 * @param string $field Label for the field.
 * @param string $key different setting keys for all the settings.
 * @param array  $customization_data Data with all save value in database.
 *
 * @since 5.0
 */
function wcdn_customization_textfield( $tab, $id, $field, $key, $customization_data ) {
	?>
	<div class="form-group row">
		<label for="<?php echo esc_attr( $id ); ?>" class="col-sm-12 col-form-label">
			<?php echo __( $field, 'woocommerce-delivery-notes' ); // phpcs:ignore ?>
		</label>
		<div class="col-sm-12">
		<?php $vmodel =  $vmodel = $tab . '.' . $id; // phpcs:ignore ?>
			<input type="text" class="form-control" name="<?php echo esc_attr( $tab . '[' . $key . '][' . $id . ']' ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( isset( $customization_data[ $key ][ $id ] ) ? $customization_data[ $key ][ $id ] : '' ); ?>" v-model= "<?php echo $vmodel; // phpcs:ignore ?>">
		</div>
	</div>
	<?php
}

/**
 * This function returns the Number field.
 *
 * @param array  $tab Setting field array for each document.
 * @param int    $id Field Id name.
 * @param string $field Label for the field.
 * @param string $key different setting keys for all the settings.
 * @param array  $customization_data Data with all save value in database.
 *
 * @since 5.0
 */
function wcdn_customization_numberfield( $tab, $id, $field, $key, $customization_data ) {
	?>
	<div class="form-group row">
		<label for="<?php echo esc_attr( $id ); ?>" class="col-sm-12 col-form-label">
			<?php echo __( $field, 'woocommerce-delivery-notes' ); // phpcs:ignore ?>
		</label>
		<div class="col-sm-12">
		<?php $vmodel =  $vmodel = $tab . '.' . $id; // phpcs:ignore ?>
			<input type="number" class="form-control" name="<?php echo esc_attr( $tab . '[' . $key . '][' . $id . ']' ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="<?php echo esc_attr( isset( $customization_data[ $key ][ $id ] ) ? $customization_data[ $key ][ $id ] : '' ); ?>" v-model= "<?php echo $vmodel; // phpcs:ignore ?>">
		</div>
	</div>
	<?php
}

/**
 * This function returns the select box field.
 *
 * @param array  $tab Setting field array for each document.
 * @param int    $id Field Id name.
 * @param string $field Label for the field.
 * @param string $key different setting keys for all the settings.
 * @param array  $customization_data Data with all save value in database.
 * @param array  $option Option value array.
 *
 * @since 5.0
 */
function wcdn_customization_selectbox( $tab, $id, $field, $key, $customization_data, $option = array() ) {
	?>
	<div class="form-group row">
		<label for="<?php echo esc_attr( $id ); ?>" class="col-sm-12 col-form-label">
			<?php echo __( $field, 'woocommerce-delivery-notes' ); // phpcs:ignore ?>
		</label>
		<div class="col-sm-12">
		<?php $vmodel =  $vmodel = $tab . '.' . $id; // phpcs:ignore ?>
			<select name="<?php echo esc_attr( $tab . '[' . $key . '][' . $id . ']' ); ?>" v-model= "<?php echo $vmodel; // phpcs:ignore ?>">
			<?php
			foreach ( $option as $value ) {
				$formate_value = strtolower( str_replace( ' ', '_', $value ) );
				if ( isset( $customization_data[ $key ][ $id ] ) && $formate_value === $customization_data[ $key ][ $id ] ) {
					$select = 'selected';
				} else {
					$select = '';
				}
				echo '<option value=' . esc_attr( $formate_value ) . ' ' . esc_attr( $select ) . '>';
				echo __( ucfirst( $value ), 'woocommerce-delivery-notes' ); // phpcs:ignore
				echo '</option>';
			}
			?>
			</select>
		</div>
	</div>
	<?php
}

/**
 * This function returns the Color field.
 *
 * @param array  $tab Setting field array for each document.
 * @param int    $id Field Id name.
 * @param string $field Label for the field.
 * @param string $key different setting keys for all the settings.
 * @param array  $customization_data Data with all save value in database.
 *
 * @since 5.0
 */
function wcdn_customization_colorfield( $tab, $id, $field, $key, $customization_data ) {
	?>
	<div class="form-group row">
		<label for="<?php echo esc_attr( $id ); ?>" class="col-sm-12 col-form-label">
			<?php echo __( $field, 'woocommerce-delivery-notes' ); // phpcs:ignore ?>
		</label>
		<div class="col-sm-12">
		<?php $vmodel =  $vmodel = $tab . '.' . $id; // phpcs:ignore ?>
			<input type="color" class="form-control" name="<?php echo esc_attr( $tab . '[' . $key . '][' . $id . ']' ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( isset( $customization_data[ $key ][ $id ] ) ? $customization_data[ $key ][ $id ] : '' ); ?>" v-model= "<?php echo $vmodel; // phpcs:ignore ?>">
		</div>
	</div>
	<?php
}
?>
