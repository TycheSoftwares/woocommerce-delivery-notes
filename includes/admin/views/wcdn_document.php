<?php 
    $wcdn_document_settings = get_option( 'wcdn_document_settings' );
    if( !isset( $_GET['wdcn_setting'] ) ) { 
        ?>
        <div class="row">
            <div class="col-lg-3">
                <div class="card card-body rounded-0">
                    <a href="<?php echo get_admin_url().'admin.php?page=wc-settings&tab=wcdn-settings&setting=wcdn_document&wdcn_setting=wcdn_invoice' ?>">
                        <div class="d-flex align-items-center justify-content-between card-padding">
                            <div class="card-icon d-flex align-items-center">
                                <img src="<?php echo WooCommerce_Delivery_Notes::$plugin_url.'assets/images/invoice-icon.svg'; ?>">
                                <div>Invoice</div>
                            </div>
                            <p class="card-text">
                                <label class="switch">
                                    <input type="checkbox" name="wcdn_document[]" value="invoice" <?php if(!empty($wcdn_document_settings) && in_array('invoice', $wcdn_document_settings)) { echo "checked"; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card card-body rounded-0">
                    <a href="<?php echo get_admin_url().'admin.php?page=wc-settings&tab=wcdn-settings&setting=wcdn_document&wdcn_setting=wcdn_receipt' ?>">
                        <div class="d-flex align-items-center justify-content-between card-padding">
                            <div class="card-icon d-flex align-items-center">
                                <img src="<?php echo WooCommerce_Delivery_Notes::$plugin_url.'assets/images/invoice-receipt-icon.svg'; ?>">
                                <div>Receipt</div>
                            </div>
                            <p class="card-text">
                                <label class="switch">
                                    <input type="checkbox" name="wcdn_document[]" value="receipt" <?php if(!empty($wcdn_document_settings) && in_array('receipt', $wcdn_document_settings)) { echo "checked"; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card card-body rounded-0">
                    <a href="<?php echo get_admin_url().'admin.php?page=wc-settings&tab=wcdn-settings&setting=wcdn_document&wdcn_setting=wcdn_deliverynote' ?>">
                        <div class="d-flex align-items-center justify-content-between card-padding">
                            <div class="card-icon d-flex align-items-center">
                                <img src="<?php echo WooCommerce_Delivery_Notes::$plugin_url.'assets/images/notes-icon.svg'; ?>">
                                <div>Delivery Notes</div>
                            </div>
                            <p class="card-text">
                                <label class="switch">
                                    <input type="checkbox"  name="wcdn_document[]" value="delivery_note" <?php if(!empty($wcdn_document_settings) && in_array('delivery_note', $wcdn_document_settings)) { echo "checked"; } ?>>
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
    if( isset( $_GET['wdcn_setting'] ) ) { 
        include_once 'wcdn_customization.php'; 
    } 
?>