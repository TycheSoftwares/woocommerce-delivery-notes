<?php
/**
 * Welcome page on activate or updation of the plugin
 */
?>
<style>
    .feature-section .feature-section-item {
        float:left;
        width:48%;
    }
</style>
<div class="wrap about-wrap">

    <?php echo $get_welcome_header ?>

    <div style="float:left;width: 80%;">
    <p class="about-text" style="margin-right:20px;"><?php
        printf(
            __( "Thank you for activating or updating to the latest version of WooCommerce Print Invoice & Delivery Note! If you're a first time user, welcome! You're well on your way to explore the print functionality for your WooCommerce orders." )
        );
        ?></p>
    </div>
    <div class="wcdn-badge"><img src="<?php echo $badge_url; ?>" style="width:150px;"/></div>

    <p>&nbsp;</p>

    <div class="feature-section clearfix introduction">

        <h3><?php esc_html_e( "Get Started with WooCommerce Print Invoice & Delivery Note", 'woocommerce-delivery-notes' ); ?></h3>

        <div class="video feature-section-item" style="float:left;padding-right:10px;">
            <img src="<?php echo $ts_dir_image_path . 'wcdn-settings.png' ?>"
                    alt="<?php esc_attr_e( 'WooCommerce Print Invoice & Delivery Note', 'woocommerce-delivery-notes' ); ?>" style="width:600px;">
        </div>

        <div class="content feature-section last-feature">
            <h3><?php esc_html_e( 'Add settings', 'woocommerce-delivery-notes' ); ?></h3>

            <p><?php esc_html_e( 'To enable the print functionality for your invoices, delivery notes & receipts, you just need to set it up under WooCommerce -> Settings -> Print page. Here you can also setup the Company Logo that will appear on the printed items, Company Address & other information.', 'woocommerce-delivery-notes' ); ?></p>
            <a href="admin.php?page=wc-settings&tab=wcdn-settings" target="_blank" class="button-secondary">
                <?php esc_html_e( 'Click Here to go to Print page', 'woocommerce-delivery-notes' ); ?>
                <span class="dashicons dashicons-external"></span>
            </a>
        </div>
    </div>

    <div class="content">

        <div class="feature-section clearfix">
            <div class="content feature-section-item">

                <h3><?php esc_html_e( 'Enable Print button for Customers.', 'woocommerce-delivery-notes' ); ?></h3>

                    <p><?php esc_html_e( 'Allow customers to print the WooCommerce order invoice from the customer notification email, from the My Account page or from the View Order page under My Account.', 'woocommerce-delivery-notes' ); ?></p>
                    <a href="admin.php?page=wc-settings&tab=wcdn-settings" target="_blank" class="button-secondary">
                        <?php esc_html_e( 'Click Here to Enable Print button for Customers', 'woocommerce-delivery-notes' ); ?>
                        <span class="dashicons dashicons-external"></span>
                    </a>
            </div>

            <div class="content feature-section-item last-feature">
                <img src="<?php echo $ts_dir_image_path . 'wcdn-email-myaccount.png'; ?>" alt="<?php esc_attr_e( 'WooCommerce Print Invoice & Delivery Note', 'woocommerce-delivery-notes' ); ?>" style="width:500px;">
            </div>
        </div>

        <div class="feature-section clearfix introduction">
            <div class="video feature-section-item" style="float:left;padding-right:10px;">
                <img src="<?php echo $ts_dir_image_path . 'wcdn-invoice-numbering.png'; ?>" alt="<?php esc_attr_e( 'WooCommerce Print Invoice & Delivery Note', 'woocommerce-delivery-notes' ); ?>" style="width:500px;">
            </div>

            <div class="content feature-section-item last-feature">
                <h3><?php esc_html_e( 'Enable Invoice Numbering', 'woocommerce-delivery-notes' ); ?></h3>

                <p><?php esc_html_e( 'If you want to change the default invoice numbers & set some numbering scheme of your own, then you can set it here with a starting invoice number, a prefix & suffix. For example, you could set it as: TS/001/17-18.', 'woocommerce-delivery-notes' ); ?></p>
                <a href="admin.php?page=wc-settings&tab=wcdn-settings" target="_blank" class="button-secondary">
                    <?php esc_html_e( 'Click Here to Enable Invoice Numbering', 'woocommerce-delivery-notes' ); ?>
                    <span class="dashicons dashicons-external"></span>
                </a>
            </div>
        </div>
    </div>

    <div class="feature-section clearfix">

        <div class="content feature-section-item">

            <h3><?php esc_html_e( 'Getting to Know Tyche Softwares', 'woocommerce-delivery-notes' ); ?></h3>

            <ul class="ul-disc">
                <li><a href="https://tychesoftwares.com/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank"><?php esc_html_e( 'Visit the Tyche Softwares Website', 'woocommerce-delivery-notes' ); ?></a></li>
                <li><a href="https://tychesoftwares.com/premium-woocommerce-plugins/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank"><?php esc_html_e( 'View all Premium Plugins', 'woocommerce-delivery-notes' ); ?></a>
                <ul class="ul-disc">
                    <li><a href="https://www.tychesoftwares.com/store/premium-plugins/woocommerce-abandoned-cart-pro/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank">Abandoned Cart Pro Plugin for WooCommerce</a></li>
                    <li><a href="https://www.tychesoftwares.com/store/premium-plugins/woocommerce-booking-plugin/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank">Booking & Appointment Plugin for WooCommerce</a></li>
                    <li><a href="https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank">Order Delivery Date for WooCommerce</a></li>
                    <li><a href="https://www.tychesoftwares.com/store/premium-plugins/product-delivery-date-pro-for-woocommerce/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank">Product Delivery Date for WooCommerce</a></li>
                    <li><a href="https://www.tychesoftwares.com/store/premium-plugins/deposits-for-woocommerce/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank">Deposits for WooCommerce</a></li>
                </ul>
                </li>
                <li><a href="https://tychesoftwares.com/about/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank"><?php esc_html_e( 'Meet the team', 'woocommerce-delivery-notes' ); ?></a></li>
            </ul>
        </div>
    </div>            
    <!-- /.feature-section -->
</div>
