<?php 
if( isset( $_GET['wdcn_setting'] ) ) {
	$setting = $_GET['wdcn_setting'];
	?>
	<div class="wcdn_top_bar">
		<h3 class="wcdn_heading">
			<?php 
				if( $setting == 'wcdn_invoice' ) {
					echo "Invoice Settings";
					$tab = 'invoice';
					$settings_db_data = get_option( 'wcdn_invoice_settings' );				
				}elseif ( $setting == 'wcdn_receipt' ) {
					echo "Receipt Settings";
					$tab = 'receipt';
					$settings_db_data = get_option( 'wcdn_receipt_settings' );
				}elseif ( $setting == 'wcdn_deliverynote' ) {
					echo "Delivery Notes Settings";
					$tab = 'deliverynote';
					$settings_db_data = get_option( 'wcdn_deliverynote_settings' );
				}
			?>
		</h3>
		<img src="<?php echo WooCommerce_Delivery_Notes::$plugin_url.'assets/images/wcdn_back_arrow.png'; ?>" class="wcdn_back_arrow">
	</div>
	<ul class="nav nav-tabs non-bg" id="wcdn_tab" role="tablist">
	    <li class="nav-item" role="presentation">
	        <button class="nav-link active" id="<?php echo $setting; ?>_tab" data-bs-toggle="tab" data-bs-target="#<?php echo $setting; ?>" type="button" role="tab" aria-controls="<?php echo $setting; ?>" aria-selected="true">Settings</button>
	    </li>
	    <li class="nav-item" role="presentation">
	        <button class="nav-link" id="<?php echo $setting; ?>customize_tab" data-bs-toggle="tab" data-bs-target="#<?php echo $setting; ?>_customize" type="button" role="tab" aria-controls="<?php echo $setting?>_customize" aria-selected="false">Customize</button>
	    </li>
	</ul>
	<div class="tab-content" id="wcdn_tabContent">
	    <div class="tab-pane fade show active" id="<?php echo $setting; ?>" role="tabpanel" aria-labelledby="wcdn-general-tab">
	        <div class="tab_container">
	        	<?php if( $setting == 'wcdn_invoice' ) { ?>
			    <div class="form-group row">
			        <div class="col-sm-8">
			            <h5 class="wcdn_title">Invoice number</h5>
			        </div>
			    </div>
			    <div class="form-group row">
			        <label for="invoice_suffix" class="col-sm-2 col-form-label">Suffix</label>
			        <div class="col-sm-6">
			            <input type="text" class="form-control" name="wcdn_invoice[invoice_suffix]" id="invoice_suffix" value="<?php if(isset($settings_db_data['invoice_suffix'])) { echo $settings_db_data['invoice_suffix']; } ?>">
			        </div>
			    </div>
			    <div class="form-group row">
			        <label for="invoice_preffix" class="col-sm-2 col-form-label">Preffix</label>
			        <div class="col-sm-6">
			            <input type="text" class="form-control" name="wcdn_invoice[invoice_preffix]" id="invoice_preffix" value="<?php if(isset($settings_db_data['invoice_preffix'])) { echo $settings_db_data['invoice_preffix']; } ?>">
			        </div>
			    </div>
			    <div class="form-group row">
			        <label for="invoice_number" class="col-sm-2 col-form-label">Numbering</label>
			        <div class="col-sm-6">
			            <input type="radio" class="form-control" name="wcdn_invoice[invoice_number]" value="order_number" <?php if(isset($settings_db_data['invoice_number']) && $settings_db_data['invoice_number'] = 'order_number' ) { echo "checked"; } ?>>Order Number
			            <input type="radio" class="form-control" name="wcdn_invoice[invoice_number]" value="custom_number" <?php if(isset($settings_db_data['invoice_number']) && $settings_db_data['invoice_number'] = 'custom_number' ) { echo "checked"; } ?>>Custom Number
			        </div>
			    </div>
			    <div class="form-group row">
			        <label for="invoice_nlength" class="col-sm-2 col-form-label">Length</label>
			        <div class="col-sm-6">
			            <input type="number" class="form-control" name="wcdn_invoice[invoice_nlength]" id="invoice_nlength" value="<?php if(isset($settings_db_data['invoice_nlength'])) { echo $settings_db_data['invoice_nlength']; } ?>">
			        </div>
			    </div>
				<?php } ?>
			    <div class="form-group row">
			        <label for="attch_mail" class="col-sm-2 col-form-label">Attch Email To</label>
			        <div class="col-sm-6">
			            <select class="wcdn_email form-control" name="<?php echo $setting; ?>[status][]" multiple="multiple" style="width: 100%;">
						  	<?php
						    $email_classes = WC()->mailer()->get_emails();
						    foreach ( $email_classes as $email_class ) {
						    	if(isset($settings_db_data['status']) && in_array($email_class->id, $settings_db_data['status'])) {
						    		$select = "selected";
						    	}else {
						    		$select = "";
						    	}
						        echo '<option value="'.$email_class->id.'" '.$select.'>'.$email_class->title.'</option>';
						    }   
						  	?>
						</select>
			        </div>
			    </div>
	        </div>
	    </div>
	    <div class="tab-pane fade" id="<?php echo $setting; ?>_customize" role="tabpanel" aria-labelledby="wcdn-document-tab">
	        <div class="tab_container">
	        	<div class="row">
	        		<?php include_once 'wcdn_comman_field.php'; ?>
				</div>
	        </div>
	    </div>
	</div>
	<?php 
} 
?>