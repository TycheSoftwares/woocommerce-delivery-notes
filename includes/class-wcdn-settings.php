<?php
/**
 * Settings class
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings class
 */
if ( ! class_exists( 'WCDN_Settings' ) ) {

	/**
	 * WooCommerce Print Delivery Notes
	 *
	 * @author Tyche Softwares
	 * @package WooCommerce-Delivery-Notes/Settings
	 */
	class WCDN_Settings {

		/**
		 * Id variable
		 *
		 * @var int $id ID variable
		 */
		public $id;

		/**
		 * Constructor
		 */
		public function __construct() {
			// Define default variables.
			$this->id = 'wcdn-settings';

			// Load the hooks.
			add_action( 'admin_menu', array( $this, 'menu' ), 999 ); // Add menu.
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 200 );
			add_action( 'woocommerce_settings_start', array( $this, 'add_assets' ) );
			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'wp_ajax_wcdn_remove_shoplogo', array( $this, 'wcdn_remove_shoplogo' ) );
			add_action( 'woocommerce_admin_field_link', array( &$this, 'wcdn_add_admin_field_reset_button' ) );
		}

		/**
		 * It will add a reset tracking data button on the settting page.
		 *
		 * @hook woocommerce_admin_field_link
		 *
		 * @param string $value Check if tracking is reset.
		 */
		public static function wcdn_add_admin_field_reset_button( $value ) {
			if ( 'ts_reset_tracking' === $value ['id'] ) {
				do_action( 'wcdn_add_new_settings', $value );
			}
		}

		/**
		 * Add the scripts
		 */
		public function add_assets() {
			// Styles.
			wp_enqueue_style( 'woocommerce-delivery-notes-admin', WooCommerce_Delivery_Notes::$plugin_url . 'assets/css/admin.css', '', WooCommerce_Delivery_Notes::$plugin_version );

			if ( isset( $_GET['tab'] ) && 'wcdn-settings' === $_GET['tab'] ) { // phpcs:ignore
				wp_enqueue_style( 'woocommerce-delivery-notes-bootstrap-style', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css', '', WooCommerce_Delivery_Notes::$plugin_version );
				wp_enqueue_style( 'woocommerce-delivery-notes-select2-style', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css', '', WooCommerce_Delivery_Notes::$plugin_version );
				wp_enqueue_script( 'woocommerce-delivery-notes-bootstrap', WooCommerce_Delivery_Notes::$plugin_url . 'assets/js/bootstrap.min.js', array(), WooCommerce_Delivery_Notes::$plugin_version, false );
			}

			// Scripts.
			wp_enqueue_media();
			wp_enqueue_script( 'woocommerce-delivery-notes-print-link', WooCommerce_Delivery_Notes::$plugin_url . 'assets/js/jquery.print-link.js', array( 'jquery' ), WooCommerce_Delivery_Notes::$plugin_version, false );
			wp_enqueue_script( 'woocommerce-delivery-notes-admin', WooCommerce_Delivery_Notes::$plugin_url . 'assets/js/admin.js', array( 'jquery', 'custom-header', 'woocommerce-delivery-notes-print-link' ), WooCommerce_Delivery_Notes::$plugin_version, false );
			wp_enqueue_script( 'woocommerce-delivery-notes-vue', WooCommerce_Delivery_Notes::$plugin_url . 'assets/js/vue.js', array(), WooCommerce_Delivery_Notes::$plugin_version, false );
			if ( isset( $_GET['wdcn_setting'] ) && 'wcdn_invoice' === $_GET['wdcn_setting'] ) { // phpcs:ignore
				wp_enqueue_script( 'woocommerce-delivery-notes-edit-invoice', WooCommerce_Delivery_Notes::$plugin_url . 'assets/js/wdne-invoice-add-edit.js', array(), WooCommerce_Delivery_Notes::$plugin_version, false );
				wp_enqueue_style( 'woocommerce-delivery-notes-adminstyle', WooCommerce_Delivery_Notes::$plugin_url . 'assets/css/adminstyle.css', '', WooCommerce_Delivery_Notes::$plugin_version );
			}
			if ( isset( $_GET['wdcn_setting'] ) && 'wcdn_receipt' === $_GET['wdcn_setting'] ) { // phpcs:ignore
				wp_enqueue_script( 'woocommerce-delivery-notes-edit-receipt', WooCommerce_Delivery_Notes::$plugin_url . 'assets/js/wdne-receipt-add-edit.js', array(), WooCommerce_Delivery_Notes::$plugin_version, false );
				wp_enqueue_style( 'woocommerce-delivery-notes-adminstyle', WooCommerce_Delivery_Notes::$plugin_url . 'assets/css/adminstyle.css', '', WooCommerce_Delivery_Notes::$plugin_version );
			}
			if ( isset( $_GET['wdcn_setting'] ) && 'wcdn_deliverynote' === $_GET['wdcn_setting'] ) { // phpcs:ignore
				wp_enqueue_script( 'woocommerce-delivery-notes-edit-deliverynote', WooCommerce_Delivery_Notes::$plugin_url . 'assets/js/wdne-deliverynote-add-edit.js', array(), WooCommerce_Delivery_Notes::$plugin_version, false );
				wp_enqueue_style( 'woocommerce-delivery-notes-adminstyle', WooCommerce_Delivery_Notes::$plugin_url . 'assets/css/adminstyle.css', '', WooCommerce_Delivery_Notes::$plugin_version );
			}
			wp_enqueue_script( 'woocommerce-delivery-notes-admin', WooCommerce_Delivery_Notes::$plugin_url . 'assets/js/admin.js', array( 'jquery', 'custom-header', 'woocommerce-delivery-notes-print-link' ), WooCommerce_Delivery_Notes::$plugin_version, false );
			$template_save = get_option( 'wcdn_template_type' );
			wp_localize_script(
				'woocommerce-delivery-notes-admin',
				'admin_object',
				array(
					'ajax_url'      => admin_url( 'admin-ajax.php' ),
					'admin_url'     => admin_url(),
					'template_save' => $template_save,
				)
			);
			// Preview data for invoice.
			$invoice_data = get_option( 'wcdn_invoice_customization' );
			if ( false === $invoice_data ) {
				$invoice_data = array();
			}
			$invoice_defaults = array(
				'document_setting'    => array(
					'active'                       => '',
					'document_setting_title'       => 'Invoice',
					'document_setting_font_size'   => 25,
					'document_setting_text_align'  => 'right',
					'document_setting_text_colour' => '#000000',
				),
				'company_logo'        => array(
					'active' => '',
				),
				'email_address'       => array(
					'active' => '',
				),
				'phone_number'        => array(
					'active' => '',
				),
				'company_name'        => array(
					'active'                   => '',
					'company_name_font_size'   => 15,
					'company_name_text_align'  => 'left',
					'company_name_text_colour' => '#000000',
				),
				'company_address'     => array(
					'active'                      => '',
					'company_address_text_align'  => 'left',
					'company_address_font_size'   => 20,
					'company_address_text_colour' => '#000000',
				),
				'billing_address'     => array(
					'active'                      => '',
					'billing_address_title'       => 'Billing Address',
					'billing_address_text_align'  => 'left',
					'billing_address_text_colour' => '#000000',
				),
				'shipping_address'    => array(
					'active'                       => '',
					'shipping_address_title'       => 'Shipping Address',
					'shipping_address_text_align'  => 'left',
					'shipping_address_text_colour' => '#000000',
				),
				'invoice_number'      => array(
					'active'                     => '',
					'invoice_number_text'        => 'Invoice Number',
					'invoice_number_font_size'   => 15,
					'invoice_number_style'       => 'bold',
					'invoice_number_text_colour' => '#000000',
				),
				'order_number'        => array(
					'active'                   => '',
					'order_number_text'        => 'Order Number',
					'order_number_font_size'   => 15,
					'order_number_style'       => 'bold',
					'order_number_text_colour' => '#000000',
				),
				'order_date'          => array(
					'active'                 => '',
					'order_date_text'        => 'Order Date',
					'order_date_font_size'   => 15,
					'order_date_style'       => 'bold',
					'order_date_text_colour' => '#000000',
				),
				'payment_method'      => array(
					'active'                     => '',
					'payment_method_text'        => 'Payment Method',
					'payment_method_font_size'   => 15,
					'payment_method_style'       => 'bold',
					'payment_method_text_colour' => '#000000',
				),
				'customer_note'       => array(
					'active'                    => '',
					'customer_note_title'       => 'Customer Notes',
					'customer_note_font_size'   => 13,
					'customer_note_text_colour' => '#000000',
				),
				'complimentary_close' => array(
					'active'                          => '',
					'complimentary_close_font_size'   => 15,
					'complimentary_close_text_colour' => '#000000',
				),
				'policies'            => array(
					'active'               => '',
					'policies_font_size'   => 15,
					'policies_text_colour' => '#000000',
				),
				'footer'              => array(
					'active'             => '',
					'footer_font_size'   => 15,
					'footer_text_colour' => '#000000',
				),
			);

			foreach ( $invoice_defaults as $parent_key => $invoice_default_values ) {
				foreach ( $invoice_default_values as $key => $invoice_default_value ) {
					if ( ! isset( $invoice_data[ $parent_key ][ $key ] ) || empty( $invoice_data[ $parent_key ][ $key ] ) ) {
						$invoice_data[ $parent_key ][ $key ] = $invoice_default_value;
					}
				}
			}

			wp_localize_script(
				'woocommerce-delivery-notes-edit-invoice',
				'settings_object',
				$invoice_data
			);
			// Preview data for receipt.
			$receipt_data = get_option( 'wcdn_receipt_customization' );
			if ( false === $receipt_data ) {
				$receipt_data = array();
			}
			$receipt_defaults = array(
				'document_setting'       => array(
					'active'                       => '',
					'document_setting_title'       => 'Receipt',
					'document_setting_font_size'   => 25,
					'document_setting_text_align'  => 'right',
					'document_setting_text_colour' => '#000000',
				),
				'company_logo'           => array(
					'active' => '',
				),
				'email_address'          => array(
					'active' => '',
				),
				'phone_number'           => array(
					'active' => '',
				),
				'company_name'           => array(
					'active'                   => '',
					'company_name_font_size'   => 15,
					'company_name_text_align'  => 'left',
					'company_name_text_colour' => '#000000',
				),
				'company_address'        => array(
					'active'                      => '',
					'company_address_text_align'  => 'left',
					'company_address_font_size'   => 20,
					'company_address_text_colour' => '#000000',
				),
				'billing_address'        => array(
					'active'                      => '',
					'billing_address_title'       => 'Billing Address',
					'billing_address_text_align'  => 'left',
					'billing_address_text_colour' => '#000000',
				),
				'shipping_address'       => array(
					'active'                       => '',
					'shipping_address_title'       => 'Shipping Address',
					'shipping_address_text_align'  => 'left',
					'shipping_address_text_colour' => '#000000',
				),
				'invoice_number'         => array(
					'active'                     => '',
					'invoice_number_text'        => 'Invoice Number',
					'invoice_number_font_size'   => 15,
					'invoice_number_style'       => 'bold',
					'invoice_number_text_colour' => '#000000',
				),
				'order_number'           => array(
					'active'                   => '',
					'order_number_text'        => 'Order Number',
					'order_number_font_size'   => 15,
					'order_number_style'       => 'bold',
					'order_number_text_colour' => '#000000',
				),
				'order_date'             => array(
					'active'                 => '',
					'order_date_text'        => 'Order Date',
					'order_date_font_size'   => 15,
					'order_date_style'       => 'bold',
					'order_date_text_colour' => '#000000',
				),
				'payment_method'         => array(
					'active'                     => '',
					'payment_method_text'        => 'Payment Method',
					'payment_method_font_size'   => 15,
					'payment_method_style'       => 'bold',
					'payment_method_text_colour' => '#000000',
				),
				'payment_date'           => array(
					'active'                   => '',
					'payment_date_text'        => 'Payment Date',
					'payment_date_font_size'   => 15,
					'payment_date_style'       => 'bold',
					'payment_date_text_colour' => '#000000',
				),
				'customer_note'          => array(
					'active'                    => '',
					'customer_note_title'       => 'Customer Notes',
					'customer_note_font_size'   => 13,
					'customer_note_text_colour' => '#000000',
				),
				'complimentary_close'    => array(
					'active'                          => '',
					'complimentary_close_font_size'   => 15,
					'complimentary_close_text_colour' => '#000000',
				),
				'policies'               => array(
					'active'               => '',
					'policies_font_size'   => 15,
					'policies_text_colour' => '#000000',
				),
				'footer'                 => array(
					'active'             => '',
					'footer_font_size'   => 15,
					'footer_text_colour' => '#000000',
				),
				'payment_received_stamp' => array(
					'active'                      => '',
					'payment_received_stamp_text' => 'Payment Stamp',
				),
			);

			foreach ( $receipt_defaults as $parent_key => $receipt_default_values ) {
				foreach ( $receipt_default_values as $key => $receipt_default_value ) {
					if ( ! isset( $receipt_data[ $parent_key ][ $key ] ) || empty( $receipt_data[ $parent_key ][ $key ] ) ) {
						$receipt_data[ $parent_key ][ $key ] = $receipt_default_value;
					}
				}
			}
			wp_localize_script(
				'woocommerce-delivery-notes-edit-receipt',
				'settings_object_receipt',
				$receipt_data
			);

			// Preview data for deliverynotes.
			$deliverynote_data = get_option( 'wcdn_deliverynote_customization' );
			if ( false === $deliverynote_data ) {
				$deliverynote_data = array();
			}
			$deliverynote_defaults = array(
				'document_setting'            => array(
					'active'                       => '',
					'document_setting_title'       => 'Delivery Notes',
					'document_setting_font_size'   => 25,
					'document_setting_text_align'  => 'right',
					'document_setting_text_colour' => '#000000',
				),
				'company_logo'                => array(
					'active' => '',
				),
				'email_address'               => array(
					'active' => '',
				),
				'phone_number'                => array(
					'active' => '',
				),
				'company_name'                => array(
					'active'                   => '',
					'company_name_font_size'   => 15,
					'company_name_text_align'  => 'left',
					'company_name_text_colour' => '#000000',
				),
				'company_address'             => array(
					'active'                      => '',
					'company_address_text_align'  => 'left',
					'company_address_font_size'   => 20,
					'company_address_text_colour' => '#000000',
				),
				'billing_address'             => array(
					'active'                      => '',
					'billing_address_title'       => 'Billing Address',
					'billing_address_text_align'  => 'left',
					'billing_address_text_colour' => '#000000',
				),
				'shipping_address'            => array(
					'active'                       => '',
					'shipping_address_title'       => 'Shipping Address',
					'shipping_address_text_align'  => 'left',
					'shipping_address_text_colour' => '#000000',
				),
				'invoice_number'              => array(
					'active'                     => '',
					'invoice_number_text'        => 'Invoice Number',
					'invoice_number_font_size'   => 15,
					'invoice_number_style'       => 'bold',
					'invoice_number_text_colour' => '#000000',
				),
				'order_number'                => array(
					'active'                   => '',
					'order_number_text'        => 'Order Number',
					'order_number_font_size'   => 15,
					'order_number_style'       => 'bold',
					'order_number_text_colour' => '#000000',
				),
				'order_date'                  => array(
					'active'                 => '',
					'order_date_text'        => 'Order Date',
					'order_date_font_size'   => 15,
					'order_date_style'       => 'bold',
					'order_date_text_colour' => '#000000',
				),
				'payment_method'              => array(
					'active'                     => '',
					'payment_method_text'        => 'Payment Method',
					'payment_method_font_size'   => 15,
					'payment_method_style'       => 'bold',
					'payment_method_text_colour' => '#000000',
				),
				'display_price_product_table' => array(
					'active' => '',
				),
				'customer_note'               => array(
					'active'                    => '',
					'customer_note_title'       => 'Customer Notes',
					'customer_note_font_size'   => 13,
					'customer_note_text_colour' => '#000000',
				),
				'complimentary_close'         => array(
					'active'                          => '',
					'complimentary_close_font_size'   => 15,
					'complimentary_close_text_colour' => '#000000',
				),
				'policies'                    => array(
					'active'               => '',
					'policies_font_size'   => 15,
					'policies_text_colour' => '#000000',
				),
				'footer'                      => array(
					'active'             => '',
					'footer_font_size'   => 15,
					'footer_text_colour' => '#000000',
				),
			);

			foreach ( $deliverynote_defaults as $parent_key => $deliverynote_default_values ) {
				foreach ( $deliverynote_default_values as $key => $deliverynote_default_value ) {
					if ( ! isset( $deliverynote_data[ $parent_key ][ $key ] ) || empty( $deliverynote_data[ $parent_key ][ $key ] ) ) {
						$deliverynote_data[ $parent_key ][ $key ] = $deliverynote_default_value;
					}
				}
			}
			wp_localize_script(
				'woocommerce-delivery-notes-edit-deliverynote',
				'settings_object_deliverynotes',
				$deliverynote_data
			);

			if ( isset( $_GET['tab'] ) && 'wcdn-settings' == $_GET['tab'] ) { // phpcs:ignore
				wp_enqueue_script( 'woocommerce-delivery-notes-bootstrap-script', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), WooCommerce_Delivery_Notes::$plugin_version, false );
				wp_enqueue_script( 'woocommerce-delivery-notes-select2-script', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js', array( 'jquery' ), WooCommerce_Delivery_Notes::$plugin_version, false );
			}

			// Localize the script strings.
			$translation = array( 'resetCounter' => __( 'Do you really want to reset the counter to zero? This process can\'t be undone.', 'woocommerce-delivery-notes' ) );
			wp_localize_script( 'woocommerce-delivery-notes-admin', 'WCDNText', $translation );
		}

		/**
		 * Create a new settings tab
		 *
		 * @param array $settings_tabs Add settings tab in WooCommerce->Settings page.
		 */
		public function add_settings_page( $settings_tabs ) {
			$settings_tabs[ $this->id ] = __( 'Invoice', 'woocommerce-delivery-notes' );
			return $settings_tabs;
		}

		/**
		 * Set Invoice menu in woocomerece setting.
		 */
		public function menu() {
			$parent_slug = 'woocommerce';
			add_submenu_page(
				$parent_slug,
				esc_html__( 'Invoice', 'woocommerce-delivery-notes' ),
				esc_html__( 'Invoice', 'woocommerce-delivery-notes' ),
				'manage_options',
				'wcdn-settings',
				array( $this, 'redirect_to_wcdn_settings' )
			);
		}

		/**
		 * Set genral setting page.
		 */
		public function redirect_to_wcdn_settings() {
			wp_redirect( admin_url( 'admin.php?page=wc-settings&tab=wcdn-settings' ) ); // phpcs:ignore
			exit;
		}

		/**
		 * Output the settings fields into the tab.
		 */
		public function output() {
			include_once __DIR__ . '/admin/views/wcdn-header.php';
		}

		/**
		 * Ajax Call For remove shop logo.
		 */
		public function wcdn_remove_shoplogo() {
			if ( ! empty( $_POST['shop_logoid'] ) ) { // phpcs:ignore
				update_option( 'wcdn_company_logo_image_id', '' );
				wp_delete_attachment( $_POST['shop_logoid'] ); // phpcs:ignore
			}
		}

		/**
		 * Generate the template type setting fields
		 *
		 * @param array  $settings Settings fields.
		 * @param string $section Section name.
		 */
		public function generate_template_type_fields( $settings, $section = '' ) {
			$position = $this->get_setting_position( 'wcdn_email_print_link', $settings );
			if ( false !== $position ) {
				$new_settings = array();

				// Go through all registrations but remove the default 'order' type.
				$template_registrations = WCDN_Print::$template_registrations;
				array_splice( $template_registrations, 0, 1 );
				$end = count( $template_registrations ) - 1;
				foreach ( $template_registrations as $index => $template_registration ) {
					$title         = '';
					$desc_tip      = '';
					$checkboxgroup = '';

					// Define the group settings.
					if ( 0 === $index ) {
						$title         = __( 'Admin', 'woocommerce-delivery-notes' );
						$checkboxgroup = 'start';
					} elseif ( $index === $end ) {
						$desc_tip      = __( 'The print buttons are available on the order listing and on the order detail screen.', 'woocommerce-delivery-notes' );
						$checkboxgroup = 'end';
					}

					// Create the setting.
					$new_settings[] = array(
						'title'         => $title,
						'desc'          => $template_registration['labels']['setting'],
						'id'            => 'wcdn_template_type_' . $template_registration['type'],
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => $checkboxgroup,
						'desc_tip'      => $desc_tip,
					);
				}

				// Add the settings.
				$settings = $this->array_merge_at( $settings, $new_settings, $position );
			}

			return $settings;
		}

		/**
		 * Generate the description for the template settings.
		 */
		public function get_template_description() {
			$description = '';
			$args        = array(
				'post_status'    => array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed' ),
				'posts_per_page' => 1,
			);
			$query       = new WC_Order_Query( $args );
			$results     = $query->get_orders();

			// show template preview links when an order is available.
			if ( is_array( $results ) && count( $results ) > 0 ) {
				$test_id           = $results[0]->ID;
				$invoice_url       = wcdn_get_print_link( $test_id, 'invoice' );
				$delivery_note_url = wcdn_get_print_link( $test_id, 'delivery-note' );
				$receipt_url       = wcdn_get_print_link( $test_id, 'receipt' );
				/* translators: %s: invoice url, delivery note url, receipt url */
				$description = sprintf( __( 'This section lets you customise the content. You can preview the <a href="%1$s" target="%4$s" class="%5$s">invoice</a>, <a href="%2$s" target="%4$s" class="%5$s">delivery note</a> or <a href="%3$s" target="%4$s" class="%5$s">receipt</a> template.', 'woocommerce-delivery-notes' ), $invoice_url, $delivery_note_url, $receipt_url, '_blank', '' );
			}

			return $description;
		}

		/**
		 * Generate the options for the template styles field.
		 */
		public function get_options_styles() {
			$options = array();

			foreach ( WCDN_Print::$template_styles as $template_style ) {
				if ( is_array( $template_style ) && isset( $template_style['type'] ) && isset( $template_style['name'] ) ) {
					$options[ $template_style['type'] ] = $template_style['name'];
				}
			}

			return $options;
		}

		/**
		 * Load image with ajax.
		 */
		public function load_image_ajax() {
			// Verify the nonce.
			if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'woocommerce-settings' ) ) {
				die();
			}

			// Verify the id.
			if ( empty( $_POST['attachment_id'] ) && is_numeric( $_POST['attachment_id'] ) ) {
				die();
			}

			$wdn_image_attachmnet_id = sanitize_text_field( wp_unslash( $_POST['attachment_id'] ) );
			// create the image.
			$this->create_image( $wdn_image_attachmnet_id );

			exit;
		}

		/**
		 * Create image.
		 *
		 * @param int $attachment_id Attachment ID.
		 */
		public function create_image( $attachment_id ) {
			$attachment_src = wp_get_attachment_image_src( $attachment_id, 'medium', false );
			$orientation    = 'landscape';
			if ( ( $attachment_src[1] / $attachment_src[2] ) < 1 ) {
				$orientation = 'portrait';
			}

			?>
			<img src="<?php echo esc_url( $attachment_src[0] ); ?>" class="<?php echo esc_attr( $orientation ); ?>" alt="" />
			<?php
		}

		/**
		 * Output image select field.
		 *
		 * @param array $value Title.
		 */
		public function output_image_select( $value ) {
			// Define the defaults.
			if ( ! isset( $value['title_select'] ) ) {
				$value['title_select'] = __( 'Select', 'woocommerce-delivery-notes' );
			}

			if ( ! isset( $value['title_remove'] ) ) {
				$value['title_remove'] = __( 'Remove', 'woocommerce-delivery-notes' );
			}

			// Get additional data fields.
			$field        = WC_Admin_Settings::get_field_description( $value );
			$description  = $field['description'];
			$tooltip_html = $field['tooltip_html'];
			$option_value = WC_Admin_Settings::get_option( $value['id'], $value['default'] );
			$class_name   = 'wcdn-image-select';

			?>
			<tr valign="top">


				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $tooltip_html ); ?></label>
				</th>
				<td class="forminp image_width_settings">
					<input name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" type="hidden" value="<?php echo esc_attr( $option_value ); ?>" class="<?php echo esc_attr( $class_name ); ?>-image-id <?php echo esc_attr( $value['class'] ); ?>" />

					<div id="<?php echo esc_attr( $value['id'] ); ?>_field" class="<?php echo esc_attr( $class_name ); ?>-field <?php echo esc_attr( $value['class'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>">
						<span id="<?php echo esc_attr( $value['id'] ); ?>_spinner" class="<?php echo esc_attr( $class_name ); ?>-spinner spinner"></span>
						<div id="<?php echo esc_attr( $value['id'] ); ?>_attachment" class="<?php echo esc_attr( $class_name ); ?>-attachment <?php echo esc_attr( $value['class'] ); ?> ">
							<div class="thumbnail">
								<div class="centered">
								<?php if ( ! empty( $option_value ) ) : ?>
									<?php $this->create_image( $option_value ); ?>
								<?php endif; ?>
								</div>
							</div>
						</div>

						<div id="<?php echo esc_attr( $value['id'] ); ?>_buttons" class="<?php echo esc_attr( $class_name ); ?>-buttons <?php echo esc_attr( $value['class'] ); ?>">
							<a href="#" id="<?php echo esc_attr( $value['id'] ); ?>_remove_button" class="<?php echo esc_attr( $class_name ); ?>-remove-button 
												<?php
												if ( empty( $option_value ) ) :
													?>
								hidden<?php endif; ?> button">
								<?php echo esc_html( $value['title_remove'] ); ?>
							</a>
							<a href="#" id="<?php echo esc_attr( $value['id'] ); ?>_add_button" class="<?php echo esc_attr( $class_name ); ?>-add-button 
													<?php
													if ( ! empty( $option_value ) ) :
														?>
								hidden<?php endif; ?> button" data-uploader-title="<?php echo esc_attr( $value['title'] ); ?>" data-uploader-button-title="<?php echo esc_attr( $value['title_select'] ); ?>">
								<?php echo esc_html( $value['title_select'] ); ?>
							</a>
						</div>
					</div>

					<?php echo wp_kses_post( $description ); ?>
				</td>
			</tr><?php
		}

		/**
		 * Merge array at given position.
		 *
		 * @param array $array Parent array.
		 * @param array $insert New array.
		 * @param int   $position Position to merge at.
		 */
		public function array_merge_at( $array, $insert, $position ) {
			$new_array = array();
			// if pos is start, just merge them.
			if ( 0 === $position ) {
				$new_array = array_merge( $insert, $array );
			} else {
				// if pos is end just merge them.
				if ( $position >= ( count( $array ) - 1 ) ) {
					$new_array = array_merge( $array, $insert );
				} else {
					// split into head and tail, then merge head+inserted bit+tail.
					$head      = array_slice( $array, 0, $position );
					$tail      = array_slice( $array, $position );
					$new_array = array_merge( $head, $insert, $tail );
				}
			}
			return $new_array;
		}
	}

}
?>
