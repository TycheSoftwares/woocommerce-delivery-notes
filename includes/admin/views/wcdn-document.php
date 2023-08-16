<?php
/**
 * All document tab setting field.
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

$wcdn_document_settings = get_option( 'wcdn_document_settings' );
if ( ! isset( $_GET['wdcn_setting'] ) ) { // phpcs:ignore
	?>
	<div class="row">
		<div class="col-lg-3">
			<div class="card card-body rounded-0">
				<a href="<?php echo esc_url( get_admin_url() . 'admin.php?page=wc-settings&tab=wcdn-settings&setting=wcdn_document&wdcn_setting=wcdn_invoice' ); ?>">
					<div class="d-flex align-items-center justify-content-between card-padding">
						<div class="card-icon d-flex align-items-center">
							<img src="<?php echo esc_url( WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/invoice-icon.svg' ); ?>">
							<div><?php esc_html_e( 'Invoice', 'woocommerce-delivery-notes' ); ?></div>
						</div>
						<p class="card-text">
							<label class="switch">
								<input type="checkbox" name="wcdn_document[]" value="invoice" <?php echo esc_attr( ( get_option('wcdn_template_type_invoice') == 'yes' ) ? 'checked' : '' ); ?>>
								<span class="slider round"></span>
							</label>
						</p>
					</div>
				</a>
			</div>
		</div>
		<div class="col-lg-3">
			<div class="card card-body rounded-0">
				<a href="<?php echo esc_url( get_admin_url() . 'admin.php?page=wc-settings&tab=wcdn-settings&setting=wcdn_document&wdcn_setting=wcdn_receipt' ); ?>">
					<div class="d-flex align-items-center justify-content-between card-padding">
						<div class="card-icon d-flex align-items-center">
							<img src="<?php echo esc_url( WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/invoice-receipt-icon.svg' ); ?>">
							<div><?php esc_html_e( 'Receipt', 'woocommerce-delivery-notes' ); ?></div>
						</div>
						<p class="card-text">
							<label class="switch">
								<input type="checkbox" name="wcdn_document[]" value="receipt" <?php echo esc_attr( ( get_option('wcdn_template_type_receipt') == 'yes' ) ? 'checked' : '' ); ?>>
								<span class="slider round"></span>
							</label>
						</p>
					</div>
				</a>
			</div>
		</div>
		<div class="col-lg-3">
			<div class="card card-body rounded-0">
				<a href="<?php echo esc_url( get_admin_url() . 'admin.php?page=wc-settings&tab=wcdn-settings&setting=wcdn_document&wdcn_setting=wcdn_deliverynote' ); ?>">
					<div class="d-flex align-items-center justify-content-between card-padding">
						<div class="card-icon d-flex align-items-center">
							<img src="<?php echo esc_url( WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/notes-icon.svg' ); ?>">
							<div><?php esc_html_e( 'Delivery Notes', 'woocommerce-delivery-notes' ); ?></div>
						</div>
						<p class="card-text">
							<label class="switch">
								<input type="checkbox"  name="wcdn_document[]" value="delivery_note" <?php echo esc_attr( ( get_option('wcdn_template_type_delivery-note') == 'yes' ) ? 'checked' : '' ); ?>>
								<span class="slider round"></span>
							</label>
						</p>
					</div>
				</a>
			</div>
		</div>
	</div>
	<?php
}
if ( isset( $_GET['wdcn_setting'] ) ) { // phpcs:ignore
	include_once 'wcdn-customization.php';
}
?>
