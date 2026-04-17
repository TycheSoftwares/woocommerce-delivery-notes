<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Migration Class for older versions of the plugin to 7.0 and above.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Core
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN;

use Tyche\WCDN\Api\Settings;
use Tyche\WCDN\Api\Templates;
use Tyche\WCDN\Services\Template_Engine;

defined( 'ABSPATH' ) || exit;

/**
 * Migration Class.
 *
 * @since 7.0
 */
class Migration {

	/**
	 * Cache for loaded options.
	 *
	 * @var array
	 * @since 7.0
	 */
	protected static $source_cache = array();

	/**
	 * Run migrations.
	 *
	 * @param string $from_version Previous plugin version.
	 * @return bool
	 * @since 7.0
	 */
	public static function run( $from_version ) {

		$success = true;

		// Migrate to 7.0.
		if ( version_compare( (string) $from_version, '7.0', '<' ) ) {
			$success = self::migrate_to_v7();
		}

		return $success;
	}

	/**
	 * Run migration to v7.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return bool
	 * @since 7.0
	 */
	public static function migrate_to_v7() {

		global $wpdb;

		if ( get_option( 'wcdn_migration_7_completed' ) ) {
			return true;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$options = $wpdb->get_results(
			"SELECT option_name, option_value 
            FROM {$wpdb->options} 
            WHERE option_name LIKE 'wcdn_%'",
			ARRAY_A
		);

		self::backup_old_data( $options );
		self::migrate_invoice_counter();

		$settings  = self::build_settings();
		$templates = self::build_templates();

		// Validate before saving.
		if ( ! is_array( $settings ) || ! is_array( $templates ) ) {
			return false;
		}

		update_option( Settings::OPTION_KEY, $settings );
		update_option( Templates::OPTION_KEY, $templates );
		self::cleanup_old_options( $options );
		update_option( 'wcdn_migration_7_completed', true );

		return true;
	}

	/**
	 * Backup old data.
	 *
	 * @param array $options Options from database.
	 * @return void
	 * @since 7.0
	 */
	protected static function backup_old_data( $options ) {

		$backup = array();

		foreach ( $options as $row ) {
			$backup[ $row['option_name'] ] = maybe_unserialize( $row['option_value'] );
		}

		update_option( 'wcdn_legacy_backup', $backup );
	}

	/**
	 * Build settings from legacy options.
	 *
	 * @return array
	 * @since 7.0
	 */
	protected static function build_settings() {

		$map = array(
			'storeLogo'                    => array(
				'source'    => 'wcdn_company_logo_image_id',
				'transform' => function ( $value ) {

					if ( empty( $value ) ) {
						return null;
					}

					$url = wp_get_attachment_url( (int) $value );

					return $url ? $url : null;
				},
			),
			'storeName'                    => array(
				'source' => 'wcdn_general_settings',
				'path'   => 'shop_name',
			),
			'storeAddress'                 => array(
				'source' => 'wcdn_company_address',
			),
			'footerText'                   => array(
				'source' => 'wcdn_footer_imprint',
			),
			'complimentaryClose'           => array(
				'source' => 'wcdn_personal_notes',
			),
			'policies'                     => array(
				'source' => 'wcdn_policies_conditions',
			),
			'printEndpoint'                => array(
				'source' => 'wcdn_general_settings',
				'path'   => 'page_endpoint',
			),
			'textDirection'                => array(
				'source'    => 'wcdn_rtl_invoice',
				'transform' => 'bool',
			),
			'enablePDFStorage'             => array(
				'source'    => 'wcdn_general_settings',
				'path'      => 'store_pdf',
				'transform' => 'bool',
			),
			'showCustomerEmailLink'        => array(
				'source'    => 'wcdn_email_print_link',
				'transform' => 'bool',
			),
			'showAdminEmailLink'           => array(
				'source'    => 'wcdn_admin_email_print_link',
				'transform' => 'bool',
			),
			'showPrintButtonMyAccountPage' => array(
				'source'    => 'wcdn_print_button_on_my_account_page',
				'transform' => 'bool',
			),
			'showViewOrderButton'          => array(
				'source'    => 'wcdn_print_button_on_view_order_page',
				'transform' => 'bool',
			),
			'invoiceNumberFormat'          => array(
				'source'    => 'wcdn_invoice_number_prefix',
				'transform' => function ( $prefix ) {

					$suffix  = get_option( 'wcdn_invoice_number_suffix' );
					$counter = (int) get_option( 'wcdn_invoice_number_counter', 0 );

					if ( empty( $prefix ) && empty( $suffix ) && 0 === $counter ) {
						return null;
					}

					$prefix = ! empty( $prefix ) ? sanitize_text_field( $prefix ) : 'INV';
					$suffix = ! empty( $suffix ) ? sanitize_text_field( $suffix ) : '';
					$format = $prefix . ( 0 === $counter ? '-{order_number}' : '{next_number}' );

					if ( $suffix ) {
						$format .= ( 0 === $counter ? '-' . $suffix : $suffix );
					}

					return $format;
				},
			),

		);

		$settings = array();

		foreach ( $map as $key => $config ) {
			$value = self::resolve_value( $config );

			if ( null !== $value && '' !== $value ) {
				$settings[ $key ] = $value;
			}
		}

		return wp_parse_args(
			$settings,
			Settings::default_settings()
		);
	}

	/**
	 * Build templates from legacy options.
	 *
	 * @return array
	 * @since 7.0
	 */
	protected static function build_templates() {

		$map = array(
			'invoice'      => array(
				'enabled'                     => array(
					'source'    => 'wcdn_template_type_invoice',
					'transform' => 'bool',
				),

				'attachToWoocommerceEmails'   => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'email_attach_to',
					'transform' => 'active_flag',
				),
				'woocommerceEmailsToAttachTo' => array(
					'source' => 'wcdn_invoice_settings',
					'path'   => 'status',
				),

				'showLogo'                    => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'company_logo',
					'transform' => 'active_flag',
				),

				// Shop Name.
				'showShopName'                => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'company_name',
					'transform' => 'active_flag',
				),
				'shopNameFontSize'            => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'company_name.company_name_font_size',
					'transform' => 'int',
				),
				'shopNameAlign'               => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'company_name.company_name_text_align',
					'transform' => 'string',
				),
				'shopNameTextColor'           => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'company_name.company_name_text_colour',
					'transform' => 'string',
				),

				// Document Title.
				'documentTitle'               => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'document_setting.document_setting_title',
					'transform' => 'string',
				),
				'documentTitleFontSize'       => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'document_setting.document_setting_font_size',
					'transform' => 'int',
				),
				'documentTitleAlign'          => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'document_setting.document_setting_text_align',
					'transform' => 'string',
				),
				'documentTitleTextColor'      => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'document_setting.document_setting_text_colour',
					'transform' => 'string',
				),

				// Shop Address.
				'showShopAddress'             => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'company_address',
					'transform' => 'active_flag',
				),
				'addressFontSize'             => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'company_address.company_address_font_size',
					'transform' => 'int',
				),
				'addressAlign'                => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'company_address.company_address_text_align',
					'transform' => 'string',
				),
				'addressTextColor'            => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'company_address.company_address_text_colour',
					'transform' => 'string',
				),

				// Invoice Number.
				'showInvoiceNumber'           => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'invoice_number',
					'transform' => 'active_flag',
				),
				'invoiceNumberText'           => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'invoice_number.invoice_number_text',
					'transform' => 'string',
				),
				'invoiceNumberFontSize'       => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'invoice_number.invoice_number_font_size',
					'transform' => 'int',
				),
				'invoiceNumberFontStyle'      => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'invoice_number.invoice_number_style',
					'transform' => function ( $value ) {
						return 'bolder' === $value ? 'bold' : 'normal';
					},
				),
				'invoiceNumberTextColor'      => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'invoice_number.invoice_number_text_colour',
					'transform' => 'string',
				),

				// Order Number.
				'showOrderNumber'             => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'order_number',
					'transform' => 'active_flag',
				),
				'orderNumberText'             => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'order_number.order_number_text',
					'transform' => 'string',
				),
				'orderNumberFontSize'         => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'order_number.order_number_font_size',
					'transform' => 'int',
				),
				'orderNumberFontStyle'        => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'order_number.order_number_style',
					'transform' => function ( $value ) {
						return 'bolder' === $value ? 'bold' : 'normal';
					},
				),
				'orderNumberTextColor'        => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'order_number.order_number_text_colour',
					'transform' => 'string',
				),

				// Order Date.
				'showOrderDate'               => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'order_date',
					'transform' => 'active_flag',
				),
				'orderDateText'               => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'order_date.order_date_text',
					'transform' => 'string',
				),
				'orderDateFontSize'           => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'order_date.order_date_font_size',
					'transform' => 'int',
				),
				'orderDateFontStyle'          => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'order_date.order_date_style',
					'transform' => function ( $value ) {
						return 'bolder' === $value ? 'bold' : 'normal';
					},
				),
				'orderDateTextColor'          => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'order_date.order_date_text_colour',
					'transform' => 'string',
				),

				// Payment Method.
				'showPaymentMethod'           => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'payment_method',
					'transform' => 'active_flag',
				),
				'paymentMethodText'           => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'payment_method.payment_method_text',
					'transform' => 'string',
				),
				'paymentMethodFontSize'       => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'payment_method.payment_method_font_size',
					'transform' => 'int',
				),
				'paymentMethodFontStyle'      => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'payment_method.payment_method_style',
					'transform' => function ( $value ) {
						return 'bolder' === $value ? 'bold' : 'normal';
					},
				),
				'paymentMethodTextColor'      => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'payment_method.payment_method_text_colour',
					'transform' => 'string',
				),

				// Billing Address.
				'showBillingAddress'          => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'billing_address',
					'transform' => 'active_flag',
				),
				'billingAddressText'          => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'billing_address.billing_address_title',
					'transform' => 'string',
				),
				'billingAddressAlign'         => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'billing_address.billing_address_text_align',
					'transform' => 'string',
				),
				'billingAddressTextColor'     => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'billing_address.billing_address_text_colour',
					'transform' => 'string',
				),

				// Shipping Address.
				'showShippingAddress'         => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'shipping_address',
					'transform' => 'active_flag',
				),
				'shippingAddressText'         => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'shipping_address.shipping_address_title',
					'transform' => 'string',
				),
				'shippingAddressAlign'        => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'shipping_address.shipping_address_text_align',
					'transform' => 'string',
				),
				'shippingAddressTextColor'    => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'shipping_address.shipping_address_text_colour',
					'transform' => 'string',
				),

				// Email.
				'showShopEmail'               => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'email_address',
					'transform' => 'active_flag',
				),

				// Phone.
				'showShopPhone'               => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'phone_number',
					'transform' => 'active_flag',
				),

				// Customer Note.
				'showCustomerNote'            => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'customer_note',
					'transform' => 'active_flag',
				),
				'customerNoteTitle'           => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'customer_note.customer_note_title',
					'transform' => 'string',
				),
				'customerNoteFontSize'        => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'customer_note.customer_note_font_size',
					'transform' => 'int',
				),
				'customerNoteTextColor'       => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'customer_note.customer_note_text_colour',
					'transform' => 'string',
				),

				// Complimentary Close.
				'showComplimentaryClose'      => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'complimentary_close',
					'transform' => 'active_flag',
				),
				'complimentaryCloseFontSize'  => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'complimentary_close.complimentary_close_font_size',
					'transform' => 'int',
				),
				'complimentaryCloseTextColor' => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'complimentary_close.complimentary_close_text_colour',
					'transform' => 'string',
				),

				// Policies.
				'showPolicies'                => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'policies',
					'transform' => 'active_flag',
				),
				'policiesFontSize'            => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'policies.policies_font_size',
					'transform' => 'int',
				),
				'policiesTextColor'           => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'policies.policies_text_colour',
					'transform' => 'string',
				),

				// Footer.
				'showFooter'                  => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'footer',
					'transform' => 'active_flag',
				),
				'footerFontSize'              => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'footer.footer_font_size',
					'transform' => 'int',
				),
				'footerTextColor'             => array(
					'source'    => 'wcdn_invoice_customization',
					'path'      => 'footer.footer_text_colour',
					'transform' => 'string',
				),
			),

			'receipt'      => array(
				'enabled'                     => array(
					'source'    => 'wcdn_template_type_receipt',
					'transform' => 'bool',
				),

				'attachToWoocommerceEmails'   => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'email_attach_to',
					'transform' => 'active_flag',
				),
				'woocommerceEmailsToAttachTo' => array(
					'source' => 'wcdn_receipt_settings',
					'path'   => 'status',
				),

				'showLogo'                    => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'company_logo',
					'transform' => 'active_flag',
				),

				// Shop Name.
				'showShopName'                => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'company_name',
					'transform' => 'active_flag',
				),
				'shopNameFontSize'            => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'company_name.company_name_font_size',
					'transform' => 'int',
				),
				'shopNameAlign'               => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'company_name.company_name_text_align',
					'transform' => 'string',
				),
				'shopNameTextColor'           => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'company_name.company_name_text_colour',
					'transform' => 'string',
				),

				// Document Title.
				'documentTitle'               => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'document_setting.document_setting_title',
					'transform' => 'string',
				),
				'documentTitleFontSize'       => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'document_setting.document_setting_font_size',
					'transform' => 'int',
				),
				'documentTitleAlign'          => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'document_setting.document_setting_text_align',
					'transform' => 'string',
				),
				'documentTitleTextColor'      => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'document_setting.document_setting_text_colour',
					'transform' => 'string',
				),

				// Shop Address.
				'showShopAddress'             => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'company_address',
					'transform' => 'active_flag',
				),
				'addressFontSize'             => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'company_address.company_address_font_size',
					'transform' => 'int',
				),
				'addressAlign'                => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'company_address.company_address_text_align',
					'transform' => 'string',
				),
				'addressTextColor'            => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'company_address.company_address_text_colour',
					'transform' => 'string',
				),

				// Invoice Number.
				'showInvoiceNumber'           => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'invoice_number',
					'transform' => 'active_flag',
				),
				'invoiceNumberText'           => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'invoice_number.invoice_number_text',
					'transform' => 'string',
				),
				'invoiceNumberFontSize'       => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'invoice_number.invoice_number_font_size',
					'transform' => 'int',
				),
				'invoiceNumberFontStyle'      => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'invoice_number.invoice_number_style',
					'transform' => function ( $value ) {
						return 'bolder' === $value ? 'bold' : 'normal';
					},
				),
				'invoiceNumberTextColor'      => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'invoice_number.invoice_number_text_colour',
					'transform' => 'string',
				),

				// Order Number.
				'showOrderNumber'             => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'order_number',
					'transform' => 'active_flag',
				),
				'orderNumberText'             => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'order_number.order_number_text',
					'transform' => 'string',
				),
				'orderNumberFontSize'         => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'order_number.order_number_font_size',
					'transform' => 'int',
				),
				'orderNumberFontStyle'        => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'order_number.order_number_style',
					'transform' => function ( $value ) {
						return 'bolder' === $value ? 'bold' : 'normal';
					},
				),
				'orderNumberTextColor'        => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'order_number.order_number_text_colour',
					'transform' => 'string',
				),

				// Order Date.
				'showOrderDate'               => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'order_date',
					'transform' => 'active_flag',
				),
				'orderDateText'               => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'order_date.order_date_text',
					'transform' => 'string',
				),
				'orderDateFontSize'           => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'order_date.order_date_font_size',
					'transform' => 'int',
				),
				'orderDateFontStyle'          => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'order_date.order_date_style',
					'transform' => function ( $value ) {
						return 'bolder' === $value ? 'bold' : 'normal';
					},
				),
				'orderDateTextColor'          => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'order_date.order_date_text_colour',
					'transform' => 'string',
				),

				// Payment Method.
				'showPaymentMethod'           => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'payment_method',
					'transform' => 'active_flag',
				),
				'paymentMethodText'           => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'payment_method.payment_method_text',
					'transform' => 'string',
				),
				'paymentMethodFontSize'       => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'payment_method.payment_method_font_size',
					'transform' => 'int',
				),
				'paymentMethodFontStyle'      => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'payment_method.payment_method_style',
					'transform' => function ( $value ) {
						return 'bolder' === $value ? 'bold' : 'normal';
					},
				),
				'paymentMethodTextColor'      => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'payment_method.payment_method_text_colour',
					'transform' => 'string',
				),

				// Payment Date.
				'showPaymentDate'             => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'payment_date',
					'transform' => 'active_flag',
				),
				'paymentDateText'             => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'payment_date.payment_date_text',
					'transform' => 'string',
				),
				'paymentDateFontSize'         => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'payment_date.payment_date_font_size',
					'transform' => 'int',
				),
				'paymentDateFontStyle'        => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'payment_date.payment_date_style',
					'transform' => function ( $value ) {
						return 'bolder' === $value ? 'bold' : 'normal';
					},
				),
				'paymentDateTextColor'        => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'payment_date.payment_date_text_colour',
					'transform' => 'string',
				),

				// Billing Address.
				'showBillingAddress'          => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'billing_address',
					'transform' => 'active_flag',
				),
				'billingAddressText'          => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'billing_address.billing_address_title',
					'transform' => 'string',
				),
				'billingAddressAlign'         => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'billing_address.billing_address_text_align',
					'transform' => 'string',
				),
				'billingAddressTextColor'     => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'billing_address.billing_address_text_colour',
					'transform' => 'string',
				),

				// Shipping Address.
				'showShippingAddress'         => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'shipping_address',
					'transform' => 'active_flag',
				),
				'shippingAddressText'         => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'shipping_address.shipping_address_title',
					'transform' => 'string',
				),
				'shippingAddressAlign'        => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'shipping_address.shipping_address_text_align',
					'transform' => 'string',
				),
				'shippingAddressTextColor'    => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'shipping_address.shipping_address_text_colour',
					'transform' => 'string',
				),

				// Email.
				'showShopEmail'               => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'email_address',
					'transform' => 'active_flag',
				),

				// Phone.
				'showShopPhone'               => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'phone_number',
					'transform' => 'active_flag',
				),

				// Customer Note.
				'showCustomerNote'            => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'customer_note',
					'transform' => 'active_flag',
				),
				'customerNoteTitle'           => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'customer_note.customer_note_title',
					'transform' => 'string',
				),
				'customerNoteFontSize'        => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'customer_note.customer_note_font_size',
					'transform' => 'int',
				),
				'customerNoteTextColor'       => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'customer_note.customer_note_text_colour',
					'transform' => 'string',
				),

				// Complimentary Close.
				'showComplimentaryClose'      => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'complimentary_close',
					'transform' => 'active_flag',
				),
				'complimentaryCloseFontSize'  => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'complimentary_close.complimentary_close_font_size',
					'transform' => 'int',
				),
				'complimentaryCloseTextColor' => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'complimentary_close.complimentary_close_text_colour',
					'transform' => 'string',
				),

				// Policies.
				'showPolicies'                => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'policies',
					'transform' => 'active_flag',
				),
				'policiesFontSize'            => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'policies.policies_font_size',
					'transform' => 'int',
				),
				'policiesTextColor'           => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'policies.policies_text_colour',
					'transform' => 'string',
				),

				// Footer.
				'showFooter'                  => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'footer',
					'transform' => 'active_flag',
				),
				'footerFontSize'              => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'footer.footer_font_size',
					'transform' => 'int',
				),
				'footerTextColor'             => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'footer.footer_text_colour',
					'transform' => 'string',
				),

				// Payment Received Timestamp.
				'showWatermark'               => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'payment_received_stamp',
					'transform' => 'active_flag',
				),
				'watermarkText'               => array(
					'source'    => 'wcdn_receipt_customization',
					'path'      => 'payment_received_stamp.payment_received_stamp_text',
					'transform' => 'string',
				),
			),

			'deliverynote' => array(
				'enabled'                           => array(
					'source'    => 'wcdn_template_type_delivery-note',
					'transform' => 'bool',
				),

				'attachToWoocommerceEmails'         => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'email_attach_to',
					'transform' => 'active_flag',
				),
				'woocommerceEmailsToAttachTo'       => array(
					'source' => 'wcdn_deliverynote_settings',
					'path'   => 'status',
				),

				'showLogo'                          => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'company_logo',
					'transform' => 'active_flag',
				),

				// Shop Name.
				'showShopName'                      => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'company_name',
					'transform' => 'active_flag',
				),
				'shopNameFontSize'                  => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'company_name.company_name_font_size',
					'transform' => 'int',
				),
				'shopNameAlign'                     => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'company_name.company_name_text_align',
					'transform' => 'string',
				),
				'shopNameTextColor'                 => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'company_name.company_name_text_colour',
					'transform' => 'string',
				),

				// Document Title.
				'documentTitle'                     => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'document_setting.document_setting_title',
					'transform' => 'string',
				),
				'documentTitleFontSize'             => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'document_setting.document_setting_font_size',
					'transform' => 'int',
				),
				'documentTitleAlign'                => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'document_setting.document_setting_text_align',
					'transform' => 'string',
				),
				'documentTitleTextColor'            => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'document_setting.document_setting_text_colour',
					'transform' => 'string',
				),

				// Shop Address.
				'showShopAddress'                   => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'company_address',
					'transform' => 'active_flag',
				),
				'addressFontSize'                   => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'company_address.company_address_font_size',
					'transform' => 'int',
				),
				'addressAlign'                      => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'company_address.company_address_text_align',
					'transform' => 'string',
				),
				'addressTextColor'                  => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'company_address.company_address_text_colour',
					'transform' => 'string',
				),

				// Invoice Number.
				'showInvoiceNumber'                 => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'invoice_number',
					'transform' => 'active_flag',
				),
				'invoiceNumberText'                 => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'invoice_number.invoice_number_text',
					'transform' => 'string',
				),
				'invoiceNumberFontSize'             => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'invoice_number.invoice_number_font_size',
					'transform' => 'int',
				),
				'invoiceNumberFontStyle'            => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'invoice_number.invoice_number_style',
					'transform' => function ( $value ) {
						return 'bolder' === $value ? 'bold' : 'normal';
					},
				),
				'invoiceNumberTextColor'            => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'invoice_number.invoice_number_text_colour',
					'transform' => 'string',
				),

				// Order Number.
				'showOrderNumber'                   => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'order_number',
					'transform' => 'active_flag',
				),
				'orderNumberText'                   => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'order_number.order_number_text',
					'transform' => 'string',
				),
				'orderNumberFontSize'               => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'order_number.order_number_font_size',
					'transform' => 'int',
				),
				'orderNumberFontStyle'              => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'order_number.order_number_style',
					'transform' => function ( $value ) {
						return 'bolder' === $value ? 'bold' : 'normal';
					},
				),
				'orderNumberTextColor'              => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'order_number.order_number_text_colour',
					'transform' => 'string',
				),

				// Order Date.
				'showOrderDate'                     => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'order_date',
					'transform' => 'active_flag',
				),
				'orderDateText'                     => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'order_date.order_date_text',
					'transform' => 'string',
				),
				'orderDateFontSize'                 => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'order_date.order_date_font_size',
					'transform' => 'int',
				),
				'orderDateFontStyle'                => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'order_date.order_date_style',
					'transform' => function ( $value ) {
						return 'bolder' === $value ? 'bold' : 'normal';
					},
				),

				// Billing Address.
				'showBillingAddress'                => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'billing_address',
					'transform' => 'active_flag',
				),
				'billingAddressText'                => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'billing_address.billing_address_title',
					'transform' => 'string',
				),
				'billingAddressAlign'               => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'billing_address.billing_address_text_align',
					'transform' => 'string',
				),
				'billingAddressTextColor'           => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'billing_address.billing_address_text_colour',
					'transform' => 'string',
				),

				// Shipping Address.
				'showShippingAddress'               => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'shipping_address',
					'transform' => 'active_flag',
				),
				'shippingAddressText'               => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'shipping_address.shipping_address_title',
					'transform' => 'string',
				),
				'shippingAddressAlign'              => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'shipping_address.shipping_address_text_align',
					'transform' => 'string',
				),
				'shippingAddressTextColor'          => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'shipping_address.shipping_address_text_colour',
					'transform' => 'string',
				),

				// Email.
				'showShopEmail'                     => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'email_address',
					'transform' => 'active_flag',
				),

				// Phone.
				'showShopPhone'                     => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'phone_number',
					'transform' => 'active_flag',
				),

				// Display Price in Product Table.
				'displayPriceInProductDetailsTable' => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'display_price_product_table',
					'transform' => 'active_flag',
				),

				// Customer Note.
				'showCustomerNote'                  => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'customer_note',
					'transform' => 'active_flag',
				),
				'customerNoteTitle'                 => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'customer_note.customer_note_title',
					'transform' => 'string',
				),
				'customerNoteFontSize'              => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'customer_note.customer_note_font_size',
					'transform' => 'int',
				),
				'customerNoteTextColor'             => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'customer_note.customer_note_text_colour',
					'transform' => 'string',
				),

				// Complimentary Close.
				'showComplimentaryClose'            => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'complimentary_close',
					'transform' => 'active_flag',
				),
				'complimentaryCloseFontSize'        => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'complimentary_close.complimentary_close_font_size',
					'transform' => 'int',
				),
				'complimentaryCloseTextColor'       => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'complimentary_close.complimentary_close_text_colour',
					'transform' => 'string',
				),

				// Policies.
				'showPolicies'                      => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'policies',
					'transform' => 'active_flag',
				),
				'policiesFontSize'                  => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'policies.policies_font_size',
					'transform' => 'int',
				),
				'policiesTextColor'                 => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'policies.policies_text_colour',
					'transform' => 'string',
				),

				// Footer.
				'showFooter'                        => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'footer',
					'transform' => 'active_flag',
				),
				'footerFontSize'                    => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'footer.footer_font_size',
					'transform' => 'int',
				),
				'footerTextColor'                   => array(
					'source'    => 'wcdn_deliverynote_customization',
					'path'      => 'footer.footer_text_colour',
					'transform' => 'string',
				),
			),
		);

		$templates = array();

		foreach ( $map as $template => $fields ) {
			foreach ( $fields as $key => $config ) {

				$value = self::resolve_value( $config );

				if ( null !== $value && '' !== $value ) {
					$templates[ $template ][ $key ] = $value;
				}
			}

			$structure = Template_Engine::get_structure( $template );

			if ( ! empty( $structure ) ) {

				$defaults               = Template_Engine::build_defaults( $template, $structure );
				$templates[ $template ] = wp_parse_args(
					$templates[ $template ],
					$defaults
				);
			}
		}

		return $templates;
	}

	/**
	 * Resolve mapping configuration.
	 *
	 * @param array $config Mapping configuration.
	 * @return mixed
	 * @since 7.0
	 */
	protected static function resolve_value( $config ) {

		$source    = $config['source'] ?? null;
		$path      = $config['path'] ?? null;
		$transform = $config['transform'] ?? null;

		if ( ! $source ) {
			return null;
		}

		$data = self::get_source( $source );

		$value = $path
			? self::get_from_path( $data, $path )
			: $data;

		// For active_flag transforms, a missing section means the feature was
		// disabled in the old plugin — pass null through so the transform can
		// explicitly return false rather than falling back to the new default.
		if ( 'active_flag' !== $transform && ( null === $value || '' === $value ) ) {
			return null;
		}

		if ( $transform ) {
			$value = self::apply_transform( $value, $transform );
		}

		if ( 'wcdn_rtl_invoice' === $source ) {
			$value = $value ? 'rtl' : 'ltr';
		}

		return $value;
	}

	/**
	 * Get option value with caching.
	 *
	 * @param string $source Option name.
	 * @return mixed
	 * @since 7.0
	 */
	protected static function get_source( $source ) {

		if ( isset( self::$source_cache[ $source ] ) ) {
			return self::$source_cache[ $source ];
		}

		$sentinel = '__wcdn_not_set__';
		$raw      = get_option( $source, $sentinel );

		if ( $sentinel === $raw ) {
			self::$source_cache[ $source ] = null;
			return null;
		}

		$value = maybe_unserialize( $raw );

		if ( null === $value ) {
			$value = array();
		}

		self::$source_cache[ $source ] = $value;

		return $value;
	}

	/**
	 * Get value from dot notation path.
	 *
	 * @param mixed  $data    Source data.
	 * @param string $path    Dot notation path.
	 * @return mixed|null
	 * @since 7.0
	 */
	protected static function get_from_path( $data, $path ) {

		if ( ! $path ) {
			return $data;
		}

		$segments = explode( '.', $path );

		foreach ( $segments as $segment ) {
			if ( is_array( $data ) && isset( $data[ $segment ] ) ) {
				$data = $data[ $segment ];
			} else {
				return null;
			}
		}

		return $data;
	}

	/**
	 * Apply value transformation.
	 *
	 * @param mixed        $value     Value to transform.
	 * @param string|mixed $transform Transform type or callable.
	 * @return mixed
	 * @since 7.0
	 */
	protected static function apply_transform( $value, $transform ) {

		switch ( $transform ) {

			case 'bool':
				return self::to_bool( $value );

			case 'active_flag':
				// Value is the whole section array. A null/non-array value means the
				// section did not exist in the old plugin — treat as disabled (false)
				// so the new plugin default is not applied when the old plugin had it off.
				if ( ! is_array( $value ) ) {
					return false;
				}
				return isset( $value['active'] ) && 'on' === $value['active'];

			case 'int':
				return (int) $value;

			case 'string':
				return (string) $value;

			default:
				if ( is_callable( $transform ) ) {
					return call_user_func( $transform, $value );
				}
		}

		return $value;
	}

	/**
	 * Normalize boolean values.
	 *
	 * @param mixed $value Value to normalize.
	 * @return bool
	 * @since 7.0
	 */
	protected static function to_bool( $value ) {

		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_numeric( $value ) ) {
			return 1 === (int) $value;
		}

		if ( is_string( $value ) ) {

			$value = strtolower( trim( $value ) );

			if ( in_array( $value, array( 'yes', 'on', 'true', '1' ), true ) ) {
				return true;
			}

			if ( in_array( $value, array( 'no', 'off', 'false', '0', '' ), true ) ) {
				return false;
			}
		}

		return (bool) $value;
	}

	/**
	 * Cleanup old options.
	 *
	 * @param array $options Options from database.
	 * @return void
	 * @since 7.0
	 */
	protected static function cleanup_old_options( $options ) {

		foreach ( $options as $row ) {
			$option_name = $row['option_name'];

			if (
			! in_array(
				$option_name,
				array(
					'wcdn_allow_tracking',
					'wcdn_version',
					'wcdn_migration_lock',
					'wcdn_migration_7_completed',
				),
				true
			)
			&& 0 !== strpos( $option_name, 'wcdn_invoice_number_counter' )
			&& 0 !== strpos( $option_name, 'wcdn_invoice_number_counter_lock' )
			) {
				delete_option( $option_name );
			}
		}
	}

	/**
	 * Rollback migration.
	 *
	 * @return bool
	 * @since 7.0
	 */
	public static function rollback() {

		$backup = get_option( 'wcdn_legacy_backup' );

		if ( empty( $backup ) || ! is_array( $backup ) ) {
			return false;
		}

		foreach ( $backup as $option => $value ) {
			update_option( $option, $value );
		}

		// Remove new data.
		delete_option( Settings::OPTION_KEY );
		delete_option( Templates::OPTION_KEY );

		// Reset flags.
		delete_option( 'wcdn_migration_7_completed' );

		return true;
	}

	/**
	 * Migrate old invoice counter to new year-based counter.
	 *
	 * @since 7.0
	 */
	protected static function migrate_invoice_counter() {

		$old_counter = get_option( 'wcdn_invoice_number_count', null );

		if ( null === $old_counter ) {
			return;
		}

		$old_counter = (int) $old_counter;

		if ( $old_counter <= 0 ) {
			delete_option( 'wcdn_invoice_number_count' );
			return;
		}

		$new_option = 'wcdn_invoice_number_counter';

		// The old plugin stored the *next* number to assign (default 1).
		// The new plugin stores the *last assigned* number (default 0).
		// Subtract 1 so the first new invoice generated keeps the same sequence.
		$migrated_counter = max( 0, $old_counter - 1 );

		// Only set if new option doesn't already exist.
		if ( false === get_option( $new_option, false ) ) {
			update_option( $new_option, $migrated_counter );
		}

		// Clean up old option.
		delete_option( 'wcdn_invoice_number_count' );
	}
}
