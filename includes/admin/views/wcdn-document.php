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
			<label for="enable_invoice" class="custom-label"><?php esc_html_e( 'Enable Invoice', 'woocommerce-delivery-notes' ); ?></label>
				<div class="col-sm-6 icon-flex">
					<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Enable Print Invoice option on the WooCommerce Orders page.', 'woocommerce-delivery-notes' ); ?>"></i>
					<label class="switch">
					<input type="checkbox" name="wcdn_document[]" id="invoice_checkbox" value="invoice" <?php echo esc_attr( ( get_option( 'wcdn_template_type_invoice', 'yes' ) === 'yes' ) ? 'checked' : '' ); ?> >
					<span class="slider round"></span>
					</label>
				</div>
			</div>
			<div class="form-group row">
				<label for="enable_receipt" class="custom-label"><?php esc_html_e( 'Enable Receipt', 'woocommerce-delivery-notes' ); ?></label>
				<div class="col-sm-6 icon-flex">
					<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Enable Print Receipt option on the WooCommerce Orders page.', 'woocommerce-delivery-notes' ); ?>"></i>
					<label class="switch">
						<input type="checkbox" name="wcdn_document[]" id='receipt' value="receipt" <?php echo esc_attr( ( get_option( 'wcdn_template_type_receipt', 'yes' ) === 'yes' ) ? 'checked' : '' ); ?>>
						<span class="slider round"></span>
					</label>
				</div>
			</div>
			<div class="form-group row">
				<label for="enable_deliverynotes" class="col-sm-2 col-form-label"><?php esc_html_e( 'Enable Delivery Notes', 'woocommerce-delivery-notes' ); ?></label>
				<div class="col-sm-6 icon-flex">
					<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Enable Print Delivery Notes option on the WooCommerce Orders page.', 'woocommerce-delivery-notes' ); ?>"></i>
					<label class="switch">
					<input type="checkbox"  name="wcdn_document[]" id='delivery_note' value="delivery-note" <?php echo esc_attr( ( get_option( 'wcdn_template_type_delivery-note', 'yes' ) === 'yes' ) ? 'checked' : '' ); ?>>
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
	<div class="tab_container" id= "<?php echo $c_tab . '_template'; // phpcs:ignore ?>">
		<div class="row">
			<div class="col-sm-4">
				<div class="accordion" id="wdcn_customize">
					<?php
					$customization_data = get_option( 'wcdn_' . $c_tab . '_customization' );
					$settings           = wcdn_customization();
					$label              = wcdn_customization_label();
					$i                  = 1;
					$hidden             = 'pointer-events:none;';
					foreach ( $settings[ $c_tab ] as $key => $eachsetting ) {
						// Ensure $customization_data is an array before accessing or modifying it.
						if ( ! is_array( $customization_data ) ) {
							$customization_data = array();
						}
						if ( ! isset( $customization_data['template_setting'] ) ) {
							$customization_data['template_setting'] = array();
						}
						$customization_data['template_setting']['template_setting_template'] = get_option( 'wcdn_template_type' );
						if ( isset( $customization_data['template_setting']['template_setting_template'] ) && 'simple' === $customization_data['template_setting']['template_setting_template'] && 1 === $i ) {
							$hidden = '';
						}
						?>
						<div class="accordion-item">
							<h2 class="accordion-header" id="<?php echo esc_attr( 'ct_acc_' . $i ); ?>">
								<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ct_acc_<?php echo esc_attr( $i ); ?>_content" aria-expanded="true" aria-controls="ct_acc_<?php echo esc_attr( $i ); ?>_content">
								<?php if ( ! in_array( $key, ['email_address', 'phone_number', 'display_price_product_table', 'company_logo'] ) ) : // phpcs:ignore ?>
								<span class="accordion-icon"></span>
								<?php endif; ?>
									<?php echo esc_html( $label[ $key ] ); ?>
								</button>
									<label class="switch">
										<?php
										$vmodel = $c_tab . '.' . $key;
										?>
										<input type="checkbox" class="custom-checkbox" name="<?php echo esc_attr( $c_tab . '[' . $key . '][active]' ); ?>" v-model= "<?php echo $vmodel; // phpcs:ignore ?>"
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
			<?php $template_save = get_option( 'wcdn_template_type' ); ?>
			<div class="col-sm-8">
				<div class="col-sm-10 offset-sm-2" style="border: 2px solid black;">
					<div class="wcdn_preview_template" style="margin: 60px;">
						<?php if ( 'wcdn_invoice' === $setting && 'simple' === $template_save ) : ?>
							<div class="wcdn_for_invoice">
								<?php include_once plugin_dir_path( __FILE__ ) . 'Preview_template/invoice-preview-template.php'; ?>
							</div>
						<?php elseif ( 'wcdn_receipt' === $setting && 'simple' === $template_save ) : ?>
							<div class="wcdn_for_receipt">
								<?php include_once plugin_dir_path( __FILE__ ) . 'Preview_template/receipt-preview-template.php'; ?>
							</div>
						<?php elseif ( 'wcdn_deliverynote' === $setting && 'simple' === $template_save ) : ?>
							<div class="wcdn_for_deliverynote">
								<?php include_once plugin_dir_path( __FILE__ ) . 'Preview_template/deliverynote-preview-template.php'; ?>
							</div>
						<?php else : ?>
							<div class="wcdn_for_default">
							<?php include_once plugin_dir_path( __FILE__ ) . 'Preview_template/default-preview-template.php'; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
?>