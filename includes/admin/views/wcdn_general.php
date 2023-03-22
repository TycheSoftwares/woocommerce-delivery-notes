<?php 
    $data           = get_option( 'wcdn_general_settings' ); 
    $shop_logoid    = get_option( 'wcdn_company_logo_image_id' );
    $shop_logourl   = wp_get_attachment_image_url( $shop_logoid, '' );
    $shop_logotitle = get_the_title($shop_logoid)
?>
<div class="form-group row">
    <div class="col-sm-8">
        <h5 class="wcdn_title">Shop Details</h5>
    </div>
</div>
<div class="form-group row">
    <label for="shop_logo" class="col-sm-2 col-form-label" name="wcdn_general[shop_logo]">Logo</label>
    <div class="col-sm-6">
        <input type="hidden" name="shop_logoid" value="<?php if(isset($shop_logoid) && !empty($shop_logoid)) { echo $shop_logoid; } ?>">
        <div class="image-upload-wrap" <?php if(isset($shop_logoid) && !empty($shop_logoid)) { echo 'style="display:none"'; } ?>>
            <input type="file" class="form-control" name="shop_logo" id="shop_logo" onchange="readURL(this);">
            <div class="drag-text">
                <img src="<?php echo WooCommerce_Delivery_Notes::$plugin_url.'assets/images/no_img.png'; ?>">
            </div>
        </div>
        <div class="file-upload-content" <?php if(isset($shop_logoid) && !empty($shop_logoid)) { echo 'style="display:block"'; } ?>>
            <span onclick="removeUpload()" class="remove-image" ><img src="<?php echo WooCommerce_Delivery_Notes::$plugin_url.'assets/images/remove.png'; ?>"></span>
            <img class="file-upload-image" src="<?php if(isset($shop_logoid) && !empty($shop_logoid)) { echo $shop_logourl; } ?>" alt="your image" />
            <div class="image-title-wrap">
                <span class="image-title"><?php if(isset($shop_logoid) && !empty($shop_logoid)) { echo $shop_logotitle; } ?></span>
            </div>
        </div>
    </div>
</div>
<div class="form-group row">
    <label for="logo_size" class="col-sm-2 col-form-label">Logo Size</label>
    <div class="col-sm-6 logo_size_container">
        <input type="number" class="form-control" name="wcdn_general[logo_size][sizeh]" value="<?php if(isset($data['logo_size']['sizeh'])) { echo $data['logo_size']['sizeh']; } else { echo '125'; } ?>" style="width: 60px;">
        <span> X </span>
        <input type="number" class="form-control" name="wcdn_general[logo_size][sizew]" value="<?php if(isset($data['logo_size']['sizew'])) { echo $data['logo_size']['sizew']; } else { echo '125'; } ?>" style="width: 60px;">
    </div>
</div>
<div class="form-group row">
    <label for="shop_name" class="col-sm-2 col-form-label">Name</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="wcdn_general[shop_name]" id="shop_name" value="<?php if(isset($data['shop_name'])) { echo $data['shop_name']; } ?>">
    </div>
</div>
<div class="form-group row">
    <label for="shop_address" class="col-sm-2 col-form-label">Address</label>
    <div class="col-sm-6">
        <textarea name="wcdn_general[shop_address]" id="shop_address" class="form-control">
            <?php if(isset($data['shop_address'])) { echo $data['shop_address']; } ?>
        </textarea>
    </div>
</div>
<div class="form-group row">
    <label for="shop_complimentry_close" class="col-sm-2 col-form-label">Complimentary Close</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="wcdn_general[shop_complimentry_close]" id="shop_complimentry_close" value="<?php if(isset($data['shop_complimentry_close'])) { echo $data['shop_complimentry_close']; } ?>">
    </div>
</div>
<div class="form-group row">
    <label for="shop_policy" class="col-sm-2 col-form-label">Policies</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="wcdn_general[shop_policy]" id="shop_policy" value="<?php if(isset($data['shop_policy'])) { echo $data['shop_policy']; } ?>">
    </div>
</div>
<div class="form-group row">
    <label for="shop_footer" class="col-sm-2 col-form-label">Footer</label>
    <div class="col-sm-6">
        <textarea name="wcdn_general[shop_footer]" id="shop_footer" class="form-control" placeholder="">
            <?php if(isset($data['shop_footer'])) { echo trim($data['shop_footer']); } ?>
        </textarea>
    </div>
</div>
<div class="form-group row">
    <label for="shop_copyright" class="col-sm-2 col-form-label">Copyright Text</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="wcdn_general[shop_copyright]" id="shop_copyright" value="<?php if(isset($data['shop_copyright'])) { echo $data['shop_copyright']; } ?>">
    </div>
</div>
<div class="form-group row">
    <div class="col-sm-8">
        <h5 class="wcdn_title">Pages & Buttons</h5>
    </div>
</div>
<div class="form-group row">
    <label for="page_endpoint" class="col-sm-2 col-form-label">Print Page Endpoint</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="wcdn_general[page_endpoint]" id="page_endpoint" value="<?php if(isset($data['page_endpoint'])) { echo $data['page_endpoint']; } ?>">
    </div>
</div>
<div class="form-group row">
    <label for="print_link" class="col-sm-2 col-form-label">Email</label>
    <div class="col-sm-6">
        <label class="switch">
        <input type="checkbox" class="form-control" name="wcdn_general[print_customer]" id="print_customer" value="" <?php if(isset($data['print_customer'])) { echo "checked"; } ?>>
        <span class="slider round"></span>
        </label>
        <label>Show print link in customer emails</label>
    </div>
    <div class="col-sm-4"></div>
    <div class="col-sm-2"></div>
    <div class="col-sm-6">
        <label class="switch">
            <input type="checkbox" class="form-control" name="wcdn_general[print_admin]" id="print_admin" value="" <?php if(isset($data['print_admin'])) { echo "checked"; } ?>>
            <span class="slider round"></span>
        </label>
        <label>Show print link in admin emails</label>
    </div>
</div>
<div class="form-group row">
    <label for="show_myaccount" class="col-sm-2 col-form-label">My Account</label>
    <div class="col-sm-6">
        <label class="switch">
        <input type="checkbox" class="form-control" name="wcdn_general[view_order]" id="view_order" value="" <?php if(isset($data['view_order'])) { echo "checked"; } ?>>
        <span class="slider round"></span>
        </label>
        <label>Show print button on View Order page</label>
    </div>
    <div class="col-sm-4"></div>
    <div class="col-sm-2"></div>
    <div class="col-sm-6">
        <label class="switch">
        <input type="checkbox" class="form-control" name="wcdn_general[view_account]" id="view_account" value="" <?php if(isset($data['view_account'])) { echo "checked"; } ?>>
        <span class="slider round"></span>
        </label>
        <label>Show print button on My Account page</label>
    </div>
</div>
<div class="form-group row">
    <label for="store_pdf" class="col-sm-2 col-form-label">Store PDF files for X days </label>
    <div class="col-sm-6">
        <input type="number" class="form-control" min="1" name="wcdn_general[store_pdf]" id="store_pdf" value="<?php if(isset($data['store_pdf'])) { echo $data['store_pdf']; } ?>">
    </div>
</div>



    

