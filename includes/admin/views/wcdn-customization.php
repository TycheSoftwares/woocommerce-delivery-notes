<?php
/**
 * All customization General field.
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

if ( isset( $_GET['wdcn_setting'] ) ) { 
	$setting = htmlspecialchars( $_GET['wdcn_setting'] ); // phpcs:ignore
	?>
	<div class="wcdn_top_bar">
		<h3 class="wcdn_heading">
			<?php
			if ( 'wcdn_invoice' === $setting ) {
				esc_html_e( 'Invoice Settings', 'woocommerce-delivery-notes' );
				$c_tab            = 'invoice';
				$settings_db_data = get_option( 'wcdn_invoice_settings' );
			} elseif ( 'wcdn_receipt' === $setting ) {
				esc_html_e( 'Receipt Settings', 'woocommerce-delivery-notes' );
				$c_tab            = 'receipt';
				$settings_db_data = get_option( 'wcdn_receipt_settings' );
			} elseif ( 'wcdn_deliverynote' === $setting ) {
				esc_html_e( 'Delivery Notes Settings', 'woocommerce-delivery-notes' );
				$c_tab            = 'deliverynote';
				$settings_db_data = get_option( 'wcdn_deliverynote_settings' );
			}
			?>
		</h3>
		<img src="<?php echo esc_url( WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/wcdn_back_arrow.png' ); ?>" class="wcdn_back_arrow" title="Back to document page">
	</div>
	<ul class="nav nav-tabs non-bg" id="wcdn_tab" role="tablist">
		<li class="nav-item" role="presentation">
			<button class="nav-link active" id="<?php echo esc_attr( $setting ); ?>_tab" data-bs-toggle="tab" data-bs-target="#<?php echo esc_attr( $setting ); ?>" type="button" role="tab" aria-controls="<?php echo esc_attr( $setting ); ?>" aria-selected="true">
				<?php esc_html_e( 'Settings', 'woocommerce-delivery-notes' ); ?>
			</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" id="<?php echo esc_attr( $setting ); ?>customize_tab" data-bs-toggle="tab" data-bs-target="#<?php echo esc_attr( $setting ); ?>_customize" type="button" role="tab" aria-controls="<?php echo esc_attr( $setting ); ?>_customize" aria-selected="false">
				<?php esc_html_e( 'Customize', 'woocommerce-delivery-notes' ); ?>
			</button>
		</li>
	</ul>
	<div class="tab-content" id="wcdn_tabContent">
		<div class="tab-pane fade show active" id="<?php echo esc_attr( $setting ); ?>" role="tabpanel" aria-labelledby="wcdn-general-tab">
			<div class="tab_container">
				<?php if ( 'wcdn_invoice' === $setting ) { ?>
				<div class="form-group row">
					<div class="col-sm-8">
						<h5 class="wcdn_title"><?php esc_html_e( 'Invoice number', 'woocommerce-delivery-notes' ); ?></h5>
					</div>
				</div>
				<div class="form-group row">
					<label for="invoice_number" class="col-sm-2 col-form-label"><?php esc_html_e( 'Numbering', 'woocommerce-delivery-notes' ); ?></label>
					<div class="col-sm-6 icon-flex">
						<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Create invoice numbers.', 'woocommerce-delivery-notes' ); ?>"></i>
						<label class="switch">
							<input type="checkbox" name="wcdn_invoice[numbering]" value="" <?php echo esc_attr( ( get_option('wcdn_create_invoice_number') == 'yes' ) ? 'checked' : '' ); ?>>
							<span class="slider round"></span>
						</label>
					</div>
				</div>
				<?php 
					$wcdn_depend_row_style = ( get_option('wcdn_create_invoice_number') == 'yes' ) ? 'display:flex' : 'display:none';
				?>
				<div class="form-group row wcdn_depend_row" style="<?php echo $wcdn_depend_row_style; ?>;">
					<label for="invoice_nextnumber" class="col-sm-2 col-form-label"><?php esc_html_e( 'Next Number', 'woocommerce-delivery-notes' ); ?></label>
					<div class="col-sm-6 icon-flex">
						<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'The next invoice number.', 'woocommerce-delivery-notes' ); ?>"></i>
						<input type="number" class="form-control" name="wcdn_invoice[invoice_nextnumber]" id="invoice_nextnumber" value="<?php echo esc_attr( get_option('wcdn_invoice_number_count') ); ?>">
					</div>
				</div>
				<div class="form-group row wcdn_depend_row" style="<?php echo $wcdn_depend_row_style; ?>;">
					<label for="invoice_suffix" class="col-sm-2 col-form-label"><?php esc_html_e( 'Suffix', 'woocommerce-delivery-notes' ); ?></label>
					<div class="col-sm-6 icon-flex">
						<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'This text will be appended to the invoice number.', 'woocommerce-delivery-notes' ); ?>"></i>
						<input type="text" class="form-control" name="wcdn_invoice[invoice_suffix]" id="invoice_suffix" value="<?php echo esc_attr( get_option('wcdn_invoice_number_suffix') ); ?>">
					</div>
				</div>
				<div class="form-group row wcdn_depend_row" style="<?php echo $wcdn_depend_row_style; ?>;">
					<label for="invoice_preffix" class="col-sm-2 col-form-label"><?php esc_html_e( 'Preffix', 'woocommerce-delivery-notes' ); ?></label>
					<div class="col-sm-6 icon-flex">
						<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'This text will be prepended to the invoice number.', 'woocommerce-delivery-notes' ); ?>"></i>
						<input type="text" class="form-control" name="wcdn_invoice[invoice_preffix]" id="invoice_preffix" value="<?php echo esc_attr( get_option('wcdn_invoice_number_prefix') ); ?>">
					</div>
				</div>
				<?php } ?>
				<div class="form-group row">
					<label for="attch_mail" class="col-sm-2 col-form-label"><?php esc_html_e( 'Attach Email To', 'woocommerce-delivery-notes' ); ?></label>
					<div class="col-sm-6 icon-flex">
						<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Different mail status in which you want to send document.', 'woocommerce-delivery-notes' ); ?>"></i>
						<select class="wcdn_email form-control" name="<?php echo esc_attr( $setting ); ?>[status][]" multiple="multiple" style="width: 100%;">
							<?php
							$email_classes = WC()->mailer()->get_emails();
							foreach ( $email_classes as $email_class ) {
								$select = ( isset( $settings_db_data['status'] ) && in_array( $email_class->id, $settings_db_data['status'], true ) ) ? 'selected' : '';
								echo '<option value="' . esc_attr( $email_class->id ) . '" ' . esc_attr( $select ) . '>' . esc_html( $email_class->title ) . '</option>';
							}
							?>
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-pane fade" id="<?php echo esc_attr( $setting ); ?>_customize" role="tabpanel" aria-labelledby="wcdn-document-tab">
			<div class="tab_container">
				<div class="row">
					<?php include_once 'wcdn-comman-field.php'; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}
?>
