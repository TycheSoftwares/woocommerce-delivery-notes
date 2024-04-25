<?php
/**
 * All customization General field.
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

if ( isset( $_GET['wdcn_setting'] ) ) {
	$setting = htmlspecialchars( $_GET['wdcn_setting'] ); // phpcs:ignore
	?>
	<select class="card-body" name="document_type" id="document_type" onchange="location = 'admin.php?page=wc-settings&tab=wcdn-settings&setting=wcdn_document&wdcn_setting=' + this.value;" >
		<option value="wcdn_invoice"  >Invoice</option>
		<option value="wcdn_receipt" >Receipt</option>
		<option value="wcdn_deliverynote" >Delivery Notes</option>
	</select>
	<div style="margin-top:35px">
		<div class="form-group row">
			<label for="enable_invoice" class="col-sm-2 col-form-label"><?php esc_html_e( 'Enable Invoice', 'woocommerce-delivery-notes' ); ?></label>
				<div class="col-sm-6 icon-flex">
					<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Enable button that will display on admin side order page.', 'woocommerce-delivery-notes' ); ?>"></i>
					<label class="switch">	
					<input type="checkbox" name="wcdn_document[]" id="invoice_checkbox" value="invoice" <?php echo esc_attr( ( get_option( 'wcdn_template_type_invoice' ) === 'yes' ) ? 'checked' : '' ); ?> >
					<span class="slider round"></span>
					</label>
				</div>
			</div>
			<div class="form-group row">
				<label for="enable_receipt" class="col-sm-2 col-form-label"><?php esc_html_e( 'Enable Receipt', 'woocommerce-delivery-notes' ); ?></label>
				<div class="col-sm-6 icon-flex">
					<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Enable button that will display on admin side order page.', 'woocommerce-delivery-notes' ); ?>"></i>
					<label class="switch">
						<input type="checkbox" name="wcdn_document[]" id='receipt' value="receipt" <?php echo esc_attr( ( get_option( 'wcdn_template_type_receipt' ) === 'yes' ) ? 'checked' : '' ); ?>>
						<span class="slider round"></span>
					</label>
				</div>
			</div>
			<div class="form-group row">
				<label for="enable_deliverynotes" class="col-sm-2 col-form-label"><?php esc_html_e( 'Enable Delivery Notes', 'woocommerce-delivery-notes' ); ?></label>
				<div class="col-sm-6 icon-flex">
					<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Enable button that will display on admin side order page.', 'woocommerce-delivery-notes' ); ?>"></i>
					<label class="switch">
					<input type="checkbox"  name="wcdn_document[]" id='delivery_note' value="delivery-note" <?php echo esc_attr( ( get_option( 'wcdn_template_type_delivery-note' ) === 'yes' ) ? 'checked' : '' ); ?>>
						<span class="slider round"></span>
					</label>
				</div>
			</div>
		<?php
		if ( 'wcdn_invoice' === $setting ) {
			$c_tab = 'invoice';
		} elseif ( 'wcdn_receipt' === $setting ) {
			$c_tab = 'receipt';
		} elseif ( 'wcdn_deliverynote' === $setting ) {
			$c_tab = 'deliverynote';
		}
		?>
	</div>
	<div class="col-sm-4">
	<div class="accordion" id="wdcn_customize">
		<?php
		$customization_data = get_option( 'wcdn_' . $c_tab . '_customization' );
		$settings           = wcdn_customization();
		$label              = wcdn_customization_label();
		$i                  = 1;
		$hidden             = 'pointer-events:none;';
		foreach ( $settings[ $c_tab ] as $key => $eachsetting ) {
			$customization_data['template_setting']['template_setting_template'] = get_option( 'wcdn_template_type' );
			if ( isset( $customization_data['template_setting']['template_setting_template'] ) && 'simple' === $customization_data['template_setting']['template_setting_template'] && 1 === $i ) {
				$hidden = '';
			}
			?>
			<div class="accordion-item">
				<h2 class="accordion-header" id="<?php echo esc_attr( 'ct_acc_' . $i ); ?>">
					<button <?php echo esc_html( $hidden ); ?> class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ct_acc_<?php echo esc_attr( $i ); ?>_content" aria-expanded="true" aria-controls="ct_acc_<?php echo esc_attr( $i ); ?>_content">
					<span class="accordion-icon"></span>
						<?php echo esc_html( $label[ $key ] ); ?>
					</button>
						<label class="switch">
							<input type="checkbox" <?php echo esc_html( $hidden ); ?> name="<?php echo esc_attr( $c_tab . '[' . $key . '][active]' ); ?>" 
								<?php
								if ( isset( $customization_data[ $key ]['active'] ) && 'on' === $customization_data[ $key ]['active'] ) {
									echo 'checked';
								}
								?>
								>
								<span class="slider round"></span>
						</label>
					</h2>
					<?php
					if ( ! empty( $eachsetting ) ) {
						?>
						<div id="<?php echo esc_attr( 'ct_acc_' . $i . '_content' ); ?>" class="accordion-collapse collapse <?php echo esc_attr( $class ); ?>" aria-labelledby="<?php echo esc_attr( 'ct_acc_' . $i ); ?>">
							<div class="accordion-body">
							<?php
							foreach ( $eachsetting as $fieldkey => $field ) {
								$field_id = $key . '_' . strtolower( str_replace( ' ', '_', $field ) );
								if ( 'Title' === $field || 'Text' === $field ) {
									wcdn_customization_textfield( $c_tab, $field_id, $field, $key, $customization_data );
								} elseif ( 'Font Size' === $field ) {
									wcdn_customization_numberfield( $c_tab, $field_id, $field, $key, $customization_data );
								} elseif ( 'Text Align' === $field ) {
									$option = array( 'left', 'right', 'center' );
									wcdn_customization_selectbox( $c_tab, $field_id, $field, $key, $customization_data, $option );
								} elseif ( 'Text Colour' === $field ) {
									wcdn_customization_colorfield( $c_tab, $field_id, $field, $key, $customization_data );
								} elseif ( 'Style' === $field ) {
									$option = array( 'bolder', '800', 'bold', '600', '500', 'normal', '300', '200', 'lighter' );
									wcdn_customization_selectbox( $c_tab, $field_id, $field, $key, $customization_data, $option );
								} elseif ( 'Formate' === $field ) {
									$option = array( 'm-d-Y', 'd-m-Y', 'Y-m-d', 'd/m/Y', 'd/m/y', 'd/M/y', 'd/M/Y', 'm/d/Y', 'm/d/y', 'M/d/y', 'M/d/Y' );
									wcdn_customization_selectbox( $c_tab, $field_id, $field, $key, $customization_data, $option );
								} elseif ( 'Next Number' === $field ) {
									wcdn_customization_numbering();
								} elseif ( 'Email Attach To' === $field ) {
									wcdn_customization_emailattachto();
								}
							}
							?>
							</div>
						</div>
					<?php } ?>
				</div>
				<?php
				++$i;
		}
		?>
		</div>
	</div>
	<?php
		$imgarray = array(
			'receipt'      => array(
				'default' => WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/Default.png',
				'simple'  => WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/Receipt.png',
			),
			'deliverynote' => array(
				'default' => WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/Default.png',
				'simple'  => WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/Delivery_Note.png',
			),
			'invoice'      => array(
				'default' => WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/Default.png',
				'simple'  => WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/Invoice.png',
			),
		);
		if ( is_array( $customization_data ) && isset( $customization_data['template_setting'] ) && isset( $customization_data['template_setting']['template_setting_template'] ) && 'simple' === $customization_data['template_setting']['template_setting_template'] ) {
			$imgurls = $imgarray[ $c_tab ]['simple'];
			$imgurld = $imgarray[ $c_tab ]['default'];
			$styles  = 'style=display:block;';
			$styled  = 'style=display:none;';
		} else {
			$imgurls = $imgarray[ $c_tab ]['simple'];
			$imgurld = $imgarray[ $c_tab ]['default'];
			$styles  = 'style=display:none;';
			$styled  = 'style=display:block;';
		}
		?>
		<div class="col-sm-6 offset-sm-2">
			<div class="wcdn_preview_img">
				<div class="wcdn_for_default"<?php echo $styled; ?>>
					<img src="<?php echo esc_url( $imgurld ); ?>">
				</div>
				<div class="wcdn_for_simple"<?php echo $styles; ?>>
					<img src="<?php echo esc_url( $imgurls ); ?>">
				</div>
				<h6>Live preview is in the Wishlist and will be available in the future.</h6>
			</div>
		</div>
	<?php
}
?>