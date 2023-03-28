<?php
/**
 * All customization General field.
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

if ( isset( $_GET['wdcn_setting'] ) ) { // phpcs:ignore
	$setting = $_GET['wdcn_setting']; // phpcs:ignore
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
		<img src="<?php echo esc_url( WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/wcdn_back_arrow.png' ); ?>" class="wcdn_back_arrow">
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
					<label for="invoice_suffix" class="col-sm-2 col-form-label"><?php esc_html_e( 'Suffix', 'woocommerce-delivery-notes' ); ?></label>
					<div class="col-sm-6">
						<input type="text" class="form-control" name="wcdn_invoice[invoice_suffix]" id="invoice_suffix" value="<?php echo esc_attr( isset( $settings_db_data['invoice_suffix'] ) ? $settings_db_data['invoice_suffix'] : '' ); ?>">
					</div>
				</div>
				<div class="form-group row">
					<label for="invoice_preffix" class="col-sm-2 col-form-label"><?php esc_html_e( 'Preffix', 'woocommerce-delivery-notes' ); ?></label>
					<div class="col-sm-6">
						<input type="text" class="form-control" name="wcdn_invoice[invoice_preffix]" id="invoice_preffix" value="<?php echo esc_attr( isset( $settings_db_data['invoice_preffix'] ) ? $settings_db_data['invoice_preffix'] : '' ); ?>">
					</div>
				</div>
				<div class="form-group row">
					<label for="invoice_number" class="col-sm-2 col-form-label"><?php esc_html_e( 'Numbering', 'woocommerce-delivery-notes' ); ?></label>
					<div class="col-sm-6">
						<input type="radio" class="form-control" name="wcdn_invoice[invoice_number]" value="order_number" <?php echo esc_attr( isset( $settings_db_data['invoice_number'] ) && 'order_number' === $settings_db_data['invoice_number'] ? 'checked' : '' ); ?> ><?php esc_html_e( 'Order Number', 'woocommerce-delivery-notes' ); ?>
						<input type="radio" class="form-control" name="wcdn_invoice[invoice_number]" value="custom_number" <?php echo esc_attr( isset( $settings_db_data['invoice_number'] ) && 'custom_number' === $settings_db_data['invoice_number'] ? 'checked' : '' ); ?> ><?php esc_html_e( 'Custom Number', 'woocommerce-delivery-notes' ); ?>
					</div>
				</div>
				<div class="form-group row">
					<label for="invoice_nlength" class="col-sm-2 col-form-label"><?php esc_html_e( 'Length', 'woocommerce-delivery-notes' ); ?></label>
					<div class="col-sm-6">
						<input type="number" class="form-control" name="wcdn_invoice[invoice_nlength]" id="invoice_nlength" value="<?php echo esc_attr( isset( $settings_db_data['invoice_nlength'] ) ? $settings_db_data['invoice_nlength'] : '' ); ?>">
					</div>
				</div>
				<?php } ?>
				<div class="form-group row">
					<label for="attch_mail" class="col-sm-2 col-form-label"><?php esc_html_e( 'Attch Email To', 'woocommerce-delivery-notes' ); ?></label>
					<div class="col-sm-6">
						<select class="wcdn_email form-control" name="<?php echo esc_attr( $setting ); ?>[status][]" multiple="multiple" style="width: 100%;">
							<?php
							$email_classes = WC()->mailer()->get_emails();
							foreach ( $email_classes as $email_class ) {
								if ( isset( $settings_db_data['status'] ) && in_array( $email_class->id, $settings_db_data['status'], true ) ) {
									$select = 'selected';
								} else {
									$select = '';
								}
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
