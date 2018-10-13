<?php
/**
 * It will Add all the Boilerplate component when we activate the plugin.
 * @author  Tyche Softwares
 * 
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
if ( ! class_exists( 'WCDN_Component' ) ) {
	/**
	 * It will Add all the Boilerplate component when we activate the plugin.
	 * 
	 */
	class WCDN_Component {
	    
		/**
		 * It will Add all the Boilerplate component when we activate the plugin.
		 */
		public function __construct() {
            
			$is_admin = is_admin();

			if ( true === $is_admin ) {

                
                require_once( "component/woocommerce-check/ts-woo-active.php" );

                require_once( "component/tracking-data/ts-tracking.php" );
                require_once( "component/deactivate-survey-popup/class-ts-deactivation.php" );

                require_once( "component/welcome-page/ts-welcome.php" );
                require_once( "component/faq-support/ts-faq-support.php" );
                require_once( "component/pro-notices-in-lite/ts-pro-notices.php" );
                
                $wcdn_plugin_name          = self::ts_get_plugin_name();;
                $wcdn_locale               = self::ts_get_plugin_locale();

                $wcdn_file_name            = 'woocommerce-delivery-notes/woocommerce-delivery-notes.php';
                $wcdn_plugin_prefix        = 'wcdn';
                $wcdn_lite_plugin_prefix   = 'wcdn';
                $wcdn_plugin_folder_name   = 'woocommerce-delivery-notes/';
                $wcdn_plugin_dir_name      = dirname ( untrailingslashit( plugin_dir_path ( __FILE__ ) ) ) . '/woocommerce-delivery-notes.php' ;
                $wcdn_plugin_url           = dirname ( untrailingslashit( plugins_url( '/', __FILE__ ) ) );

                $wcdn_get_previous_version = get_option( 'wcdn_version', '1' );

                $wcdn_blog_post_link       = 'https://www.tychesoftwares.com/docs/docs/woocommerce-print-invoice-delivery-note/usage-tracking/';

                $wcdn_plugins_page         = '';
                $wcdn_plugin_slug          = '';
                $wcdn_pro_file_name        = '';

                $wcdn_settings_page        = 'admin.php?page=wc-settings&tab=wcdn-settings';

                new WCDN_TS_Woo_Active ( $wcdn_plugin_name, $wcdn_file_name, $wcdn_locale );

                new WCDN_TS_tracking ( $wcdn_plugin_prefix, $wcdn_plugin_name, $wcdn_blog_post_link, $wcdn_locale, $wcdn_plugin_url, $wcdn_settings_page );

                new WCDN_TS_Tracker ( $wcdn_plugin_prefix, $wcdn_plugin_name );

                $wcdn_deativate = new WCDN_TS_deactivate;
                $wcdn_deativate->init ( $wcdn_file_name, $wcdn_plugin_name );

                //$user = wp_get_current_user();
                
                /*if ( in_array( 'administrator', (array) $user->roles ) ) {
                    new WCDN_TS_Welcome ( $wcdn_plugin_name, $wcdn_plugin_prefix, $wcdn_locale, $wcdn_plugin_folder_name, $wcdn_plugin_dir_name, $wcdn_get_previous_version );
                }*/
                $ts_pro_faq = self::wcdn_get_faq ();
                new WCDN_TS_Faq_Support( $wcdn_plugin_name, $wcdn_plugin_prefix, $wcdn_plugins_page, $wcdn_locale, $wcdn_plugin_folder_name, $wcdn_plugin_slug, $ts_pro_faq );
                
                //$ts_pro_notices = self::wcdn_get_notice_text ();
				//new WCDN_ts_pro_notices( $wcdn_plugin_name, $wcdn_lite_plugin_prefix, $wcdn_plugin_prefix, $ts_pro_notices, $wcdn_file_name, $wcdn_pro_file_name );

            }
        }

         /**
         * It will retrun the plguin name.
         * @return string $ts_plugin_name Name of the plugin
         */
		public static function ts_get_plugin_name () {
            $ordd_plugin_dir =  dirname ( dirname ( __FILE__ ) );
            $ordd_plugin_dir .= '/woocommerce-delivery-notes.php';
           
            $ts_plugin_name = '';
            $plugin_data = get_file_data( $ordd_plugin_dir, array( 'name' => 'Plugin Name' ) );
            if ( ! empty( $plugin_data['name'] ) ) {
                $ts_plugin_name = $plugin_data[ 'name' ];
            }
            return $ts_plugin_name;
        }

        /**
         * It will retrun the Plugin text Domain
         * @return string $ts_plugin_domain Name of the Plugin domain
         */
        public static function ts_get_plugin_locale () {
            $ordd_plugin_dir =  dirname ( dirname ( __FILE__ ) );
            $ordd_plugin_dir .= '/woocommerce-delivery-notes.php';

            $ts_plugin_domain = '';
            $plugin_data = get_file_data( $ordd_plugin_dir, array( 'domain' => 'Text Domain' ) );
            if ( ! empty( $plugin_data['domain'] ) ) {
                $ts_plugin_domain = $plugin_data[ 'domain' ];
            }
            return $ts_plugin_domain;
        }
        
        /**
         * It will Display the notices in the admin dashboard for the pro vesion of the plugin.
         * @return array $ts_pro_notices All text of the notices
         */
        public static function wcdn_get_notice_text () {
            $ts_pro_notices = array();

            $wcdn_locale               = self::ts_get_plugin_locale();

            $message_first = wp_kses_post ( __( 'Thank you for using WooCommerce Print Invoice & Delivery Note plugin! Now make your deliveries more accurate by allowing customers to select their preferred delivery date & time from Product Delivery Date Pro for WooCommerce. <strong><a target="_blank" href= "https://www.tychesoftwares.com/store/premium-plugins/product-delivery-date-pro-for-woocommerce/?utm_source=wpnotice&utm_medium=first&utm_campaign=PrintInvoicePlugin">Get it now!</a></strong>', $wcdn_locale ) );  

            $message_two = wp_kses_post ( __( 'Never login to your admin to check your deliveries by syncing the delivery dates to the Google Calendar from Product Delivery Date Pro for WooCommerce. <strong><a target="_blank" href= "https://www.tychesoftwares.com/store/premium-plugins/product-delivery-date-pro-for-woocommerce/checkout?edd_action=add_to_cart&download_id=16&utm_source=wpnotice&utm_medium=first&utm_campaign=PrintInvoicePlugin">Get it now!</a></strong>', $wcdn_locale ) );

            $message_three = wp_kses_post ( __( 'You can now view all your deliveries in list view or in calendar view from Product Delivery Date Pro for WooCommerce. <strong><a target="_blank" href= "https://www.tychesoftwares.com/store/premium-plugins/product-delivery-date-pro-for-woocommerce/checkout?edd_action=add_to_cart&download_id=16&utm_source=wpnotice&utm_medium=first&utm_campaign=PrintInvoicePlugin">Get it now!</a></strong>.', $wcdn_locale ) );

            $message_four = wp_kses_post ( __( 'Allow your customers to pay extra for delivery for certain Weekdays/Dates from Product Delivery Date Pro for WooCommerce. <strong><a target="_blank" href= "https://www.tychesoftwares.com/store/premium-plugins/product-delivery-date-pro-for-woocommerce/checkout?edd_action=add_to_cart&download_id=16&utm_source=wpnotice&utm_medium=first&utm_campaign=PrintInvoicePlugin">Have it now!</a></strong>.', $wcdn_locale ) );

            $message_five = wp_kses_post ( __( 'Customers can now edit the Delivery date & time on cart and checkout page or they can reschedule the deliveries for the already placed orders from Product Delivery Date Pro for WooCommerce. <strong><a target="_blank" href= "https://www.tychesoftwares.com/store/premium-plugins/product-delivery-date-pro-for-woocommerce/checkout?edd_action=add_to_cart&download_id=16&utm_source=wpnotice&utm_medium=first&utm_campaign=PrintInvoicePlugin">Have it now!</a></strong>.', $wcdn_locale ) );

		// message six
            $_link = 'https://www.tychesoftwares.com/store/premium-plugins/woocommerce-abandoned-cart-pro/?utm_source=wpnotice&utm_medium=sixth&utm_campaign=PrintInvoicePlugin';
            $message_six = wp_kses_post ( __( 'Boost your sales by recovering up to 60% of the abandoned carts with our Abandoned Cart Pro for WooCommerce plugin. You can capture customer email addresses right when they click the Add To Cart button. <strong><a target="_blank" href= "'.$_link.'">Grab your copy of Abandon Cart Pro plugin now</a></strong>.', $wcdn_locale ) );
            
            $wcdn_message_six = array ( 'message' => $message_six, 'plugin_link' => 'woocommerce-abandon-cart-pro/woocommerce-ac.php' );
		// message seven
            $_link = 'https://www.tychesoftwares.com/store/premium-plugins/woocommerce-abandoned-cart-pro/?utm_source=wpnotice&utm_medium=seventh&utm_campaign=PrintInvoicePlugin';
            $message_seven = wp_kses_post ( __( 'Don\'t loose your sales to abandoned carts. Use our Abandon Cart Pro plugin & start recovering your lost sales in less then 60 seconds.<br> 
            <strong><a target="_blank" href= "'.$_link.'">Get it now!</a></strong>', $wcdn_locale ) );
            $wcdn_message_seven = array ( 'message' => $message_seven, 'plugin_link' => 'woocommerce-abandon-cart-pro/woocommerce-ac.php' );
        
        // message eight
            $_link = 'https://www.tychesoftwares.com/store/premium-plugins/woocommerce-abandoned-cart-pro/?utm_source=wpnotice&utm_medium=eight&utm_campaign=PrintInvoicePlugin';
            $message_eight = wp_kses_post ( __( 'Send Abandoned Cart reminders that actually convert. Take advantage of our fully responsive email templates designed specially with an intent to trigger conversion. <br><strong><a target="_blank" href= "'.$_link.'">Grab your copy now!</a></strong>', $wcdn_locale ) );
            $wcdn_message_eight = array ( 'message' => $message_eight, 'plugin_link' => 'woocommerce-abandon-cart-pro/woocommerce-ac.php' );

		// message nine
            $_link = 'https://www.tychesoftwares.com/store/premium-plugins/woocommerce-abandoned-cart-pro/?utm_source=wpnotice&utm_medium=ninth&utm_campaign=PrintInvoicePlugin';
            $message_nine = wp_kses_post ( __( 'Increase your store sales by recovering your abandoned carts for just $119. No profit sharing, no monthly fees. Our Abandoned Cart Pro plugin comes with a 30 day money back guarantee as well. :). Use coupon code ACPRO20 & save $24!<br>
            <strong><a target="_blank" href= "'.$_link.'">Purchase now</a></strong>', $wcdn_locale ) );
            $wcdn_message_nine = array ( 'message' => $message_nine, 'plugin_link' => 'woocommerce-abandon-cart-pro/woocommerce-ac.php' );
            
		// message ten  
	        $_link = 'https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/?utm_source=wpnotice&utm_medium=tenth&utm_campaign=PrintInvoicePlugin';
            $message_ten = wp_kses_post ( __( 'Allow your customers to select the Delivery Date & Time on the Checkout Page using our Order Delivery Date Pro for WooCommerce Plugin. <br> 
            <strong><a target="_blank" href= "'.$_link.'">Shop now</a></strong> & be one of the 20 customers to get 20% discount on the plugin price. Use the code "ORDPRO20". Hurry!!', $wcdn_locale ) );
            $wcdn_message_ten = array ( 'message' => $message_ten, 'plugin_link' => 'order-delivery-date/order_delivery_date.php' );

		// message eleven
            $_link = 'https://www.tychesoftwares.com/store/premium-plugins/woocommerce-booking-plugin/?utm_source=wpnotice&utm_medium=eleven&utm_campaign=PrintInvoicePlugin';
            $message_eleven = wp_kses_post ( __( ' Allow your customers to book an appointment or rent an apartment with our Booking and Appointment for WooCommerce plugin. You can also sell your product as a resource or integrate with a few Vendor plugins. <br>Shop now & Save 20% on the plugin with the code "BKAP20". Only for first 20 customers. <strong><a target="_blank" href= "'.$_link.'">Have it now!</a></strong>', $wcdn_locale ) );
            $wcdn_message_eleven = array ( 'message' => $message_eleven, 'plugin_link' => 'woocommerce-booking/woocommerce-booking.php' );

		// message 12
            $_link = 'https://www.tychesoftwares.com/store/premium-plugins/deposits-for-woocommerce/?utm_source=wpnotice&utm_medium=twelve&utm_campaign=PrintInvoicePlugin';
            $message_twelve = wp_kses_post ( __( ' Allow your customers to pay deposits on products using our Deposits for WooCommerce plugin.<br>
            <strong><a target="_blank" href= "'.$_link.'">Purchase now</a></strong> & Grab 20% discount with the code "DFWP20". The discount code is valid only for the first 20 customers.', $wcdn_locale ) );
            $wcdn_message_twelve = array ( 'message' => $message_twelve, 'plugin_link' => 'woocommerce-deposits/deposits-for-woocommerce.php' );

		// message 13 
	        $_link = 'https://www.tychesoftwares.com/store/premium-plugins/product-delivery-date-pro-for-woocommerce/?utm_source=wpnotice&utm_medium=thirteen&utm_campaign=PrintInvoicePlugin';
            $message_thirteen = wp_kses_post ( __( 'Allow your customers to select the Delivery Date & Time for your WooCommerce products using our Product Delivery Date Pro for WooCommerce Plugin. <br> 
            <strong><a target="_blank" href= "'.$_link.'">Shop now</a></strong>', $wcdn_locale ) );
            $wcdn_message_thirteen = array ( 'message' => $message_thirteen, 'plugin_link' => 'product-delivery-date/product-delivery-date.php' );

            $ts_pro_notices = array (
                1 => $message_first,
                2 => $message_two,
                3 => $message_three,
                4 => $message_four,
                5 => $message_five,
                6 => $wcdn_message_six,
                7 => $wcdn_message_seven,
                8 => $wcdn_message_eight,
                9 => $wcdn_message_nine,
                10 => $wcdn_message_ten,
                11 => $wcdn_message_eleven,
                12 => $wcdn_message_twelve,
                13 => $wcdn_message_thirteen
            );

            return $ts_pro_notices;
        }
		
		/**
         * It will contain all the FAQ which need to be display on the FAQ page.
         * @return array $ts_faq All questions and answers.
         * 
         */
        public static function wcdn_get_faq () {

            $ts_faq = array ();

            $ts_faq = array(
                1 => array (
                        'question' => 'It prints the 404 page instead of the order, how to correct that?',
                        'answer'   => 'This is most probably due to the permalink settings. Go either to the WordPress Permalink or the WooCommerce Print Settings and save them again. If that didn\'t help, go to the WooCommerce \'Accounts\' settings tab and make sure that for \'My Account Page\' a page is selected.'
                    ), 
                2 => array (
                        'question' => 'How do I quickly change the font of the invoice and delivery note?',
                        'answer'   => 'You can change the font with CSS. Use the `wcdn_head` hook and then write your own CSS code. It\'s best to place the code in the `functions.php` file of your theme. 
                        An example that changes the font and makes the addresses very large. Paste the code in the `functions.php` file of your theme:
                        <br/> <br/>
                        <pre>
function example_serif_font_and_large_address() {
    ?&gt;
    &lt;style&gt;	
            #page {
                font-size: 1em;
                font-family: Georgia, serif;
            }
            
            .order-addresses address {
                font-size: 2.5em;
                line-height: 125%;
            }
    &lt;/style&gt;
    &lt;?php
}
add_action( \'wcdn_head\', \'example_serif_font_and_large_address\', 20 ); </pre>'
                    ),
                3 => array (
						'question' => 'Can I hide the prices on the delivery note?',
						'answer'   => 'Sure, the easiest way is to hide them with some CSS that is hooked in with `wcdn_head`.
                        <br/><br/>
                        An example that hides the whole price column and the totals. Paste the code in the `functions.php` file of your theme:
                        <pre>
function example_price_free_delivery_note() {
    ?&gt;
    &lt;style&gt;
            .delivery-note .head-item-price,
            .delivery-note .head-price, 
            .delivery-note .product-item-price,
            .delivery-note .product-price,
            .delivery-note .order-items tfoot {
                display: none;
            }
            .delivery-note .head-name,
            .delivery-note .product-name {
                width: 50%;
            }
            .delivery-note .head-quantity,
            .delivery-note .product-quantity {
                width: 50%;
            }
            .delivery-note .order-items tbody tr:last-child {
                border-bottom: 0.24em solid black;
            }
        &lt;/style&gt;
    &lt;?php
}
add_action( \'wcdn_head\', \'example_price_free_delivery_note\', 20 );</pre>'
                ),
                4 => array (
						'question' => 'I use the receipt in my POS, can I style it?',
						'answer'   => 'Sure, you can style with CSS, very much the same way as the delivery note or invoice. 
<br/><br/>
                        An example that hides the addresses. Paste the code in the `functions.php` file of your theme:
                        <pre>
function example_address_free_receipt() {
    ?&gt;
    &lt;style&gt;
            .content {
                padding: 4% 6%;
            }
            .company-address,
            .order-addresses {
                display: none;
            }
            .order-info li span {
                display: inline-block;
                float: right;
            }
            .order-thanks {
                margin-left: inherit;
            }
            &lt;/style&gt;
    &lt;?php
}
add_action( \'wcdn_head\', \'example_address_free_receipt\', 20 );</pre>'
                ),
                5 => array (
						'question' => 'Is it possible to remove a field from the order info section?',
						'answer'   => 'Yes, use the `wcdn_order_info_fields` filter hook. It returns all the fields as array. Unset or rearrange the values as you like.
                    <br/><br/>
                        An example that removes the \'Payment Method\' field. Paste the code in the `functions.php` file of your theme:
                        <pre>
function example_removed_payment_method( $fields ) {
    unset( $fields[\'payment_method\'] );
    return $fields;
}
add_filter( \'wcdn_order_info_fields\', \'example_removed_payment_method\' );<pre>'
                ),
                6 => array (
						'question' => 'How can I add some more fields to the order info section? ',
						'answer'   => 'Use the `wcdn_order_info_fields` filter hook. It returns all the fields as array. Read the WooCommerce documentation to learn how you get custom checkout and order fields. Tip: To get custom meta field values you will most probably need the `get_post_meta( $order->id, \'your_meta_field_name\', true);` function and of course the `your_meta_field_name`. 
                        <br/><br/>
                        An example that adds a \'VAT\' and \'Customer Number\' field to the end of the list. Paste the code in the `functions.php` file of your theme:
                        <pre>
function example_custom_order_fields( $fields, $order ) {
    $new_fields = array();
        
    if( get_post_meta( $order->id, \'your_meta_field_name\', true ) ) {
        $new_fields[\'your_meta_field_name\'] = array( 
            \'label\' => \'VAT\',
            \'value\' => get_post_meta( $order->id, \'your_meta_field_name\', true )
        );
    }
    
    if( get_post_meta( $order->id, \'your_meta_field_name\', true ) ) {
        $new_fields[\'your_meta_field_name\'] = array( 
            \'label\' => \'Customer Number\',
            \'value\' => get_post_meta( $order->id, \'your_meta_field_name\', true )
        );
    }
    
    return array_merge( $fields, $new_fields );
}
add_filter( \'wcdn_order_info_fields\', \'example_custom_order_fields\', 10, 2 );</pre>'
                ),
                7 => array (
						'question' => 'What about the product image, can I add it to the invoice and delivery note? ',
						'answer'   => 'Yes, use the `wcdn_order_item_before` action hook. It allows you to add html content before the item name.
<br/><br/>
                        An example that adds a 40px large product image. Paste the code in the `functions.php` file of your theme:
                        
                        <pre>
function example_product_image( $product ) {	
    if( isset( $product->id ) && has_post_thumbnail( $product->id ) ) {
        echo get_the_post_thumbnail( $product->id, array( 40, 40 ) );
    }
}
add_action( \'wcdn_order_item_before\', \'example_product_image\' );</pre>'
                ),
                8 => array (
						'question' => 'How can I differentiate between invoice and delivery note through CSS?',
						'answer'   => 'The `body` tag contains a class that specifies the template type. The class can be `invoice` or `delivery-note`. You can prefix your style rules to only target one template. For example you could rise the font size for the addresses on the right side:
<pre>
.invoice .billing-address {
    font-size: 2em;
}

.delivery-note .shipping-address {
    font-size: 2em;
} </pre>'
                ),
                9 => array (
						'question' => 'How do I customize the look of the invoice and delivery note? ',
						'answer'   => 'You can use the techniques from the questions above. Or you consider the `wcdn_head` hook to enqueue your own stylesheet. Or for full control, copy the file `style.css` from `woocommerce-delivery-notes/templates/print-order` to `yourtheme/woocommerce/print-order` and start editing it. 
                        <br/><br/>
                       <strong>Note</strong>: Create the `woocommerce` and `print-order` folders if they do not exist. This way your changes won\'t be overridden on plugin updates.
                       '
                ),
                10 => array (
						'question' => 'How can I translate the plugin?',
						'answer'   => 'Upload your language file to `/wp-content/languages/plugins/` (create this folder if it doesn\'t exist). WordPress will then load the language. Make sure you use the same locale as in your configuration and the correct plugin locale i.e. `woocommerce-delivery-notes-it_IT.mo/.po`.'
                )    
            );

            return $ts_faq;
        }
	}
	$WCDN_Component = new WCDN_Component();
}