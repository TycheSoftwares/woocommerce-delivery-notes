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
	<div class="col-sm-6 icon-flex">
		<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'A shop logo representing your business. When the image is printed, its pixel density will automatically be eight times higher than the original. This means, 1 printed inch will correspond to about 288 pixels on the screen.', 'woocommerce-delivery-notes' ); ?>"></i>
		<?php wp_nonce_field( 'wcdn_remove_shoplogo_action', 'wcdn_remove_shoplogo_nonce' ); ?>
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
	<div class="col-sm-6 icon-flex logo_size_container">
		<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( ' Shop Logo height x width value. Default value is 125 x 125.', 'woocommerce-delivery-notes' ); ?>"></i>
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
	<div class="col-sm-6 icon-flex">
		<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'The shop name. Leave blank to use the default Website or Blog title defined in WordPress settings. The name will be ignored when a Logo is set.', 'woocommerce-delivery-notes' ); ?>"></i>
		<input type="text" class="form-control" name="wcdn_general[shop_name]" id="shop_name" value="<?php echo esc_html( stripcslashes( get_option( 'wcdn_custom_company_name' ) ) ); ?>">
	</div>
</div>
<div class="form-group row">
	<label for="shop_address" class="col-sm-2 col-form-label"><?php esc_html_e( 'Address', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6 icon-flex">
		<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'The postal address of the shop or even e-mail or telephone.', 'woocommerce-delivery-notes' ); ?>"></i>
		<textarea name="wcdn_general[shop_address]" id="shop_address" class="form-control"><?php echo esc_attr( stripcslashes( get_option( 'wcdn_company_address' ) ) ); ?></textarea>
	</div>
</div>
<div class="form-group row">
	<label for="shop_complimentry_close" class="col-sm-2 col-form-label"><?php esc_html_e( 'Complimentary Close', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6 icon-flex">
		<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Add a personal close, notes or season greetings.', 'woocommerce-delivery-notes' ); ?>"></i>
		<input type="text" class="form-control" name="wcdn_general[shop_complimentry_close]" id="shop_complimentry_close" value="<?php echo esc_attr( stripcslashes( get_option( 'wcdn_personal_notes' ) ) ); ?>">
	</div>
</div>
<div class="form-group row">
	<label for="shop_policy" class="col-sm-2 col-form-label"><?php esc_html_e( 'Policies', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6 icon-flex">
		<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Add the shop policies, conditions, etc.', 'woocommerce-delivery-notes' ); ?>"></i>
		<textarea name="wcdn_general[shop_policy]" id="shop_policy" class="form-control" placeholder=""><?php echo esc_attr( stripcslashes( get_option( 'wcdn_policies_conditions' ) ) ); ?></textarea>
	</div>
</div>
<div class="form-group row">
	<label for="shop_footer" class="col-sm-2 col-form-label"><?php esc_html_e( 'Footer', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6 icon-flex">
		<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Add a footer imprint, instructions, copyright notes, e-mail, telephone, etc.', 'woocommerce-delivery-notes' ); ?>"></i>
		<textarea name="wcdn_general[shop_footer]" id="shop_footer" class="form-control" placeholder=""><?php echo esc_attr( stripcslashes( get_option( 'wcdn_footer_imprint' ) ) ); ?></textarea>
	</div>
</div>
<div class="form-group row">
	<div class="col-sm-8">
		<h5 class="wcdn_title"><?php esc_html_e( 'Pages & Buttons', 'woocommerce-delivery-notes' ); ?></h5>
	</div>
</div>
<div class="form-group row">
	<label for="page_endpoint" class="col-sm-2 col-form-label"><?php esc_html_e( 'Print Page Endpoint', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6 icon-flex">
		<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'The endpoint is appended to the accounts page URL to print the order. It should be unique.', 'woocommerce-delivery-notes' ); ?>"></i>
		<input type="text" class="form-control" name="wcdn_general[page_endpoint]" id="page_endpoint" value="<?php echo esc_attr( get_option( 'wcdn_print_order_page_endpoint' ) ); ?>">
	</div>
</div>
<div class="form-group row">
	<label for="print_link" class="col-sm-2 col-form-label"><?php esc_html_e( 'Email', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6 icon-flex">
	<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'This includes the emails for a new, processing and completed order. On top of that the customer and admin invoice emails will also include the link.', 'woocommerce-delivery-notes' ); ?>"></i>
		<label class="switch">
		<input type="checkbox" class="form-control" name="wcdn_general[print_customer]" id="print_customer" value="" <?php echo esc_attr( ( get_option('wcdn_email_print_link', 'yes' ) == 'yes' ) ? 'checked' : '' ); ?> >
		<span class="slider round"></span>
		</label>
		<label><?php esc_html_e( 'Show print link in customer emails', 'woocommerce-delivery-notes' ); ?></label>
	</div>
	<div class="col-sm-4"></div>
	<div class="col-sm-2"></div>
	<div class="col-sm-6 icon-flex">
		<label class="switch">
			<input type="checkbox" class="form-control" name="wcdn_general[print_admin]" id="print_admin" value="" <?php echo esc_attr( ( get_option('wcdn_admin_email_print_link', 'yes') == 'yes' ) ? 'checked' : '' ); ?> >
			<span class="slider round"></span>
		</label>
		<label><?php esc_html_e( 'Show print link in admin emails', 'woocommerce-delivery-notes' ); ?></label>
	</div>
</div>
<div class="form-group row">
	<label for="show_myaccount" class="col-sm-2 col-form-label"><?php esc_html_e( 'My Account', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6 icon-flex">
		<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'This includes print button on View Order page and My Account page.', 'woocommerce-delivery-notes' ); ?>"></i>
		<label class="switch">
		<input type="checkbox" class="form-control" name="wcdn_general[view_order]" id="view_order" value="" <?php echo esc_attr( ( get_option('wcdn_print_button_on_view_order_page', 'yes' ) == 'yes' ) ? 'checked' : '' ); ?> >
		<span class="slider round"></span>
		</label>
		<label><?php esc_html_e( 'Show print button on View Order page', 'woocommerce-delivery-notes' ); ?></label>
	</div>
	<div class="col-sm-4"></div>
	<div class="col-sm-2"></div>
	<div class="col-sm-6 icon-flex">
		<label class="switch">
		<input type="checkbox" class="form-control" name="wcdn_general[view_account]" id="view_account" value="" <?php echo esc_attr( ( get_option('wcdn_print_button_on_my_account_page', 'yes' ) == 'yes' ) ? 'checked' : '' ); ?> >
		<span class="slider round"></span>
		</label>
		<label><?php esc_html_e( 'Show print button on My Account page', 'woocommerce-delivery-notes' ); ?></label>
	</div>
</div>
<div class="form-group row">
	<label for="store_pdf" class="col-sm-2 col-form-label">Store PDF files for X days </label>
	<div class="col-sm-6 icon-flex">
		<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'To Store PDF files in the \wp-content\uploads\wcdn\ folder for a specific duration, like X days, with a default value of 7 days', 'woocommerce-delivery-notes' ); ?>"></i>
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
<div class="form-group row">
	<div class="col-sm-8">
		<h5 class="wcdn_title"><?php esc_html_e( 'Template Type', 'woocommerce-delivery-notes' ); ?></h5>
	</div>
</div>
<div class="form-group row">
	<label for="template" class="col-sm-2 col-form-label">Template</label>
	<div class="col-sm-6 icon-flex">
		<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'You have the option to customize the Simple template to your liking, or you can stick with the default display.	', 'woocommerce-delivery-notes' ); ?>"></i>
		<select name="wcdn_general[template]" id="template">
			<?php $template_save = get_option( 'wcdn_template_type' ); ?>
			<option value="default" <?php selected( $template_save, 'default' ); ?>><?php esc_html_e( 'Default', 'woocommerce-delivery-notes' ); ?></option>
			<option value="simple" <?php selected( $template_save, 'simple' ); ?>><?php esc_html_e( 'Simple', 'woocommerce-delivery-notes' ); ?></option>
		</select>
	</div>
</div>

<div class="form-group row">
	<label for="page_textdirection" class="col-sm-2 col-form-label"><?php esc_html_e( 'Text Direction', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6 icon-flex">
		<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Show text in right to left direction in Invoice, Print Receipt & Delivery note if you are using languages such as Hebrew, Arabic, etc.', 'woocommerce-delivery-notes' ); ?>"></i>
		<label class="switch">
			<input type="checkbox" class="form-control" name="wcdn_general[page_textdirection]" id="page_textdirection" value="" <?php echo esc_attr( ( get_option('wcdn_rtl_invoice', 'yes' ) == 'yes' ) ? 'checked' : '' ); ?> >
			<span class="slider round"></span>
		</label>
		<label><?php esc_html_e( 'Print Text from Right to left', 'woocommerce-delivery-notes' ); ?></label>
	</div>
</div>
<div class="form-group row">
	<label for="reset_tracking" class="col-sm-2 col-form-label"><?php esc_html_e( 'Reset usage tracking', 'woocommerce-delivery-notes' ); ?></label>
	<div class="col-sm-6 icon-flex">
		<i class="dashicons dashicons-info" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'This will reset your usage tracking settings, causing it to show the opt-in banner again and not sending any data', 'woocommerce-delivery-notes' ); ?>"></i>
		<input class="trietary-btn reverse reset button-secondary reset_tracking" type="button" name="" value="Reset" >
	</div>
</div>