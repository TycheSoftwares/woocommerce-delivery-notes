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
                require_once( "component/faq-support/ts-faq-support.php" );
                
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

                require_once 'component/plugin-deactivation/class-tyche-plugin-deactivation.php';
                new Tyche_Plugin_Deactivation(
                    array(
                        'plugin_name'       => 'Print Invoices & Delivery Notes for WooCommerce',
                        'plugin_base'       => 'woocommerce-delivery-notes/woocommerce-delivery-notes.php',
                        'script_file'       => $wcdn_plugin_url . '/assets/js/plugin-deactivation.js',
                        'plugin_short_name' => 'wcdn',
                        'version'           => $wcdn_get_previous_version,
                        'plugin_locale'     => 'woocommerce-delivery-notes',
                    )
                );

                require_once 'component/plugin-tracking/class-tyche-plugin-tracking.php';
                new Tyche_Plugin_Tracking(
                    array(
                        'plugin_name'       => 'Print Invoices & Delivery Notes for WooCommerce',
                        'plugin_locale'     => 'woocommerce-delivery-notes',
                        'plugin_short_name' => 'wcdn',
                        'version'           => $wcdn_get_previous_version,
                        'blog_link'         => '',
                    )
                );

                new WCDN_TS_Woo_Active ( $wcdn_plugin_name, $wcdn_file_name, $wcdn_locale );

                $ts_pro_faq = self::wcdn_get_faq ();
                new WCDN_TS_Faq_Support( $wcdn_plugin_name, $wcdn_plugin_prefix, $wcdn_plugins_page, $wcdn_locale, $wcdn_plugin_folder_name, $wcdn_plugin_slug, $ts_pro_faq );

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
						'answer'   => 'Use the `wcdn_order_info_fields` filter hook. It returns all the fields as array. Read the WooCommerce documentation to learn how you get custom checkout and order fields. Tip: To get custom meta field values you will most probably need the `get_post_meta( $order->get_id(), \'your_meta_field_name\', true);` function and of course the `your_meta_field_name`. 
                        <br/><br/>
                        An example that adds a \'VAT\' and \'Customer Number\' field to the end of the list. Paste the code in the `functions.php` file of your theme:
                        <pre>
function example_custom_order_fields( $fields, $order ) {
    $new_fields = array();
        
    if( get_post_meta( $order->get_id(), \'your_meta_field_name\', true ) ) {
        $new_fields[\'your_meta_field_name\'] = array( 
            \'label\' => \'VAT\',
            \'value\' => get_post_meta( $order->get_id(), \'your_meta_field_name\', true )
        );
    }
    
    if( get_post_meta( $order->get_id(), \'your_meta_field_name\', true ) ) {
        $new_fields[\'your_meta_field_name\'] = array( 
            \'label\' => \'Customer Number\',
            \'value\' => get_post_meta( $order->get_id(), \'your_meta_field_name\', true )
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
    if( isset( $product->get_id() ) && ( \'\' !== $product->get_id() ) && has_post_thumbnail( $product->get_id ) ) {
        echo get_the_post_thumbnail(
            $product->get_id(),
            array( 40, 40 ),
            array( \'loading\' => false )
        );
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