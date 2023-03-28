<?php
/**
 * All general tab setting field.
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

$data           = get_option( 'wcdn_general_settings' );
$shop_logoid    = get_option( 'wcdn_company_logo_image_id' );
$shop_logourl   = wp_get_attachment_image_url( $shop_logoid, '' );
$shop_logotitle = get_the_title( $shop_logoid );
?>
<div class="form-group row">
	<div class="col-sm-8">
		<h5 class="wcdn_title"><?php esc_html_e( 'Shop Details', 'woocommerce-delivery-notes' ); ?></h5>
	</div>
</div>
<div class="form-group row">
	<label for="shop_logo" class="col-sm-2 col-form-label" name="wcdn_general[shop_logo]"><?php esc_html_e( 'Logo', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6">
		<input type="hidden" name="shop_logoid" value="
		<?php
		if ( isset( $shop_logoid ) && ! empty( $shop_logoid ) ) {
			echo esc_html( $shop_logoid );
		}
		?>
		">
		<div class="image-upload-wrap" 
		<?php
		if ( isset( $shop_logoid ) && ! empty( $shop_logoid ) ) {
			echo 'style="display:none"'; }
		?>
		>
			<input type="file" class="form-control" name="shop_logo" id="shop_logo" onchange="readURL(this);">
			<div class="drag-text">
				<img src="<?php echo esc_url( WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/no_img.png' ); ?>">
			</div>
		</div>
		<div class="file-upload-content" 
		<?php
		if ( isset( $shop_logoid ) && ! empty( $shop_logoid ) ) {
			echo 'style="display:block"'; }
		?>
		>
			<span onclick="removeUpload()" class="remove-image" ><img src="<?php echo esc_url( WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/remove.png' ); ?>"></span>
			<img class="file-upload-image" src="
			<?php
			if ( isset( $shop_logoid ) && ! empty( $shop_logoid ) ) {
				echo esc_url( $shop_logourl );
			}
			?>
			" alt="your image" />
			<div class="image-title-wrap">
				<span class="image-title">
				<?php
				if ( isset( $shop_logoid ) && ! empty( $shop_logoid ) ) {
					echo esc_html( $shop_logotitle );
				}
				?>
				</span>
			</div>
		</div>
	</div>
</div>
<div class="form-group row">
	<label for="logo_size" class="col-sm-2 col-form-label"><?php esc_html_e( 'Logo Size', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6 logo_size_container">
		<?php
		if ( isset( $data['logo_size']['sizeh'] ) ) {
			$logo_size_hval = $data['logo_size']['sizeh'];
		} else {
			$logo_size_hval = '125';
		}
		if ( isset( $data['logo_size']['sizew'] ) ) {
			$logo_size_wval = $data['logo_size']['sizew'];
		} else {
			$logo_size_wval = '125';
		}
		?>
		<input type="number" class="form-control" name="wcdn_general[logo_size][sizeh]" value="<?php echo esc_html( $logo_size_hval ); ?>" style="width: 60px;">
		<span> X </span>
		<input type="number" class="form-control" name="wcdn_general[logo_size][sizew]" value="<?php echo esc_html( $logo_size_wval ); ?>" style="width: 60px;">
	</div>
</div>
<div class="form-group row">
	<label for="shop_name" class="col-sm-2 col-form-label"><?php esc_html_e( 'Name', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6">
		<input type="text" class="form-control" name="wcdn_general[shop_name]" id="shop_name" value="<?php echo esc_attr( isset( $data['shop_name'] ) ? $data['shop_name'] : '' ); ?>">
	</div>
</div>
<div class="form-group row">
	<label for="shop_address" class="col-sm-2 col-form-label"><?php esc_html_e( 'Address', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6">
		<textarea name="wcdn_general[shop_address]" id="shop_address" class="form-control"><?php echo esc_html( isset( $data['shop_address'] ) ? $data['shop_address'] : '' ); ?></textarea>
	</div>
</div>
<div class="form-group row">
	<label for="shop_complimentry_close" class="col-sm-2 col-form-label"><?php esc_html_e( 'Complimentary Close', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6">
		<input type="text" class="form-control" name="wcdn_general[shop_complimentry_close]" id="shop_complimentry_close" value="<?php echo esc_attr( isset( $data['shop_complimentry_close'] ) ? $data['shop_complimentry_close'] : '' ); ?>">
	</div>
</div>
<div class="form-group row">
	<label for="shop_policy" class="col-sm-2 col-form-label"><?php esc_html_e( 'Policies', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6">
		<input type="text" class="form-control" name="wcdn_general[shop_policy]" id="shop_policy" value="<?php echo esc_attr( isset( $data['shop_policy'] ) ? $data['shop_policy'] : '' ); ?>">
	</div>
</div>
<div class="form-group row">
	<label for="shop_footer" class="col-sm-2 col-form-label"><?php esc_html_e( 'Footer', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6">
		<textarea name="wcdn_general[shop_footer]" id="shop_footer" class="form-control" placeholder=""><?php echo esc_attr( isset( $data['shop_footer'] ) ? $data['shop_footer'] : '' ); ?></textarea>
	</div>
</div>
<div class="form-group row">
	<label for="shop_copyright" class="col-sm-2 col-form-label"><?php esc_html_e( 'Copyright Text', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6">
		<input type="text" class="form-control" name="wcdn_general[shop_copyright]" id="shop_copyright" value="<?php echo esc_attr( isset( $data['shop_copyright'] ) ? $data['shop_copyright'] : '' ); ?>">
	</div>
</div>
<div class="form-group row">
	<div class="col-sm-8">
		<h5 class="wcdn_title"><?php esc_html_e( 'Pages & Buttons', 'woocommerce-delivery-notes' ); ?></h5>
	</div>
</div>
<div class="form-group row">
	<label for="page_endpoint" class="col-sm-2 col-form-label"><?php esc_html_e( 'Print Page Endpoint', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6">
		<input type="text" class="form-control" name="wcdn_general[page_endpoint]" id="page_endpoint" value="<?php echo esc_attr( isset( $data['page_endpoint'] ) ? $data['page_endpoint'] : '' ); ?>">
	</div>
</div>
<div class="form-group row">
	<label for="print_link" class="col-sm-2 col-form-label"><?php esc_html_e( 'Email', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6">
		<label class="switch">
		<input type="checkbox" class="form-control" name="wcdn_general[print_customer]" id="print_customer" value="" <?php echo esc_attr( isset( $data['print_customer'] ) ? 'checked' : '' ); ?> >
		<span class="slider round"></span>
		</label>
		<label><?php esc_html_e( 'Show print link in customer emails', 'woocommerce-delivery-notes' ); ?></label>
	</div>
	<div class="col-sm-4"></div>
	<div class="col-sm-2"></div>
	<div class="col-sm-6">
		<label class="switch">
			<input type="checkbox" class="form-control" name="wcdn_general[print_admin]" id="print_admin" value="" <?php echo esc_attr( isset( $data['print_admin'] ) ? 'checked' : '' ); ?> >
			<span class="slider round"></span>
		</label>
		<label><?php esc_html_e( 'Show print link in admin emails', 'woocommerce-delivery-notes' ); ?></label>
	</div>
</div>
<div class="form-group row">
	<label for="show_myaccount" class="col-sm-2 col-form-label"><?php esc_html_e( 'My Account', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6">
		<label class="switch">
		<input type="checkbox" class="form-control" name="wcdn_general[view_order]" id="view_order" value="" <?php echo esc_attr( isset( $data['view_order'] ) ? 'checked' : '' ); ?> >
		<span class="slider round"></span>
		</label>
		<label><?php esc_html_e( 'Show print button on View Order page', 'woocommerce-delivery-notes' ); ?></label>
	</div>
	<div class="col-sm-4"></div>
	<div class="col-sm-2"></div>
	<div class="col-sm-6">
		<label class="switch">
		<input type="checkbox" class="form-control" name="wcdn_general[view_account]" id="view_account" value="" <?php echo esc_attr( isset( $data['view_account'] ) ? 'checked' : '' ); ?> >
		<span class="slider round"></span>
		</label>
		<label><?php esc_html_e( 'Show print button on My Account page', 'woocommerce-delivery-notes' ); ?></label>
	</div>
</div>
<div class="form-group row">
	<label for="store_pdf" class="col-sm-2 col-form-label">Store PDF files for X days </label>
	<div class="col-sm-6">
		<?php
		if ( isset( $data['store_pdf'] ) ) {
			$store_pdf = $data['store_pdf'];
		} else {
			$store_pdf = 7;
		}
		?>
		<input type="number" class="form-control" min="1" name="wcdn_general[store_pdf]" id="store_pdf" value="<?php echo esc_html( $store_pdf ); ?>">
	</div>
</div>
