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
		if ( $success && version_compare( (string) $from_version, '7.0', '<' ) ) {
			$success = self::migrate_to_v7();
		}

		// 7.0.2 had no data transform — mark it complete so the flag is consistent.
		if ( $success && version_compare( (string) $from_version, '7.0.2', '<' ) ) {
			update_option( 'wcdn_migration_7_0_2_completed', true );
		}

		// Recover settings wiped by the v702 migration bug.
		if ( $success && version_compare( (string) $from_version, '7.1.0', '<' ) ) {
			$success = self::migrate_to_v71();
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

		// Re-activation after deactivation — restore from the v7 snapshot instead
		// of re-running the full legacy migration, preserving any v7 customisations.
		$snapshot_settings  = get_option( WCDN_SLUG . '_v7_settings_snapshot' );
		$snapshot_templates = get_option( WCDN_SLUG . '_v7_templates_snapshot' );

		if ( $snapshot_settings && $snapshot_templates ) {
			update_option( Settings::OPTION_KEY, $snapshot_settings );
			update_option( Templates::OPTION_KEY, $snapshot_templates );
			delete_option( WCDN_SLUG . '_v7_settings_snapshot' );
			delete_option( WCDN_SLUG . '_v7_templates_snapshot' );
			update_option( 'wcdn_migration_7_completed', true );
			return true;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$options = $wpdb->get_results(
			"SELECT option_name, option_value, autoload
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
	 * Run migration to v7.1.0.
	 *
	 * Recovers global settings (storeName, storeLogo, policies, etc.) and
	 * per-template settings (alignments, font sizes, show/hide toggles) that
	 * were wiped to defaults by the v7.0.2 migration bug. The bug caused
	 * migrate_to_v702() to call build_settings() in a fresh request after
	 * cleanup_old_options() had already deleted the legacy options, producing
	 * an all-defaults settings array that silently overwrote correctly-migrated
	 * v7.0 data.
	 *
	 * Recovery reads from the wcdn_legacy_backup saved during the original v7
	 * migration and restores any key whose current value still matches the
	 * default, leaving any settings the user re-entered manually untouched.
	 *
	 * @return bool
	 * @since 7.1.0
	 */
	public static function migrate_to_v71() {

		if ( get_option( 'wcdn_migration_7_1_0_completed' ) ) {
			return true;
		}

		$backup = get_option( 'wcdn_legacy_backup' );

		if ( is_array( $backup ) && ! empty( $backup ) ) {

			// Seed the source cache from the backup so build_settings() and
			// build_templates() can resolve values without the original options
			// being in the database.
			foreach ( $backup as $option_name => $value ) {
				self::$source_cache[ $option_name ] = $value;
			}

			$recovered_settings  = self::build_settings();
			$recovered_templates = self::build_templates();
			self::$source_cache  = array();

			// Recover global settings.
			if ( is_array( $recovered_settings ) ) {

				$current  = get_option( Settings::OPTION_KEY, array() );
				$defaults = Settings::default_settings();

				// Only apply recovered values where the current value still matches
				// the default, indicating the setting was wiped rather than changed.
				foreach ( $recovered_settings as $key => $recovered_value ) {
					$default_value = $defaults[ $key ] ?? null;
					$current_value = $current[ $key ] ?? null;

					if ( $current_value === $default_value && $recovered_value !== $default_value ) {
						$current[ $key ] = $recovered_value;
					}
				}

				update_option( Settings::OPTION_KEY, $current );
			}

			// Recover per-template settings (alignments, font sizes, show/hide toggles, etc.).
			if ( is_array( $recovered_templates ) ) {

				$current_templates = get_option( Templates::OPTION_KEY, array() );

				foreach ( $recovered_templates as $template => $recovered_fields ) {

					$structure = Template_Engine::get_structure( $template );
					$defaults  = $structure ? Template_Engine::build_defaults( $template, $structure ) : array();
					$current   = $current_templates[ $template ] ?? array();

					foreach ( $recovered_fields as $key => $recovered_value ) {
						$default_value = $defaults[ $key ] ?? null;
						$current_value = $current[ $key ] ?? null;

						if ( $current_value === $default_value && $recovered_value !== $default_value ) {
							// Standard: value matches template default → was wiped → restore.
							$current_templates[ $template ][ $key ] = $recovered_value;
						} elseif ( false === $current_value && true === $recovered_value ) {
							// Boolean show toggles set to false by the old v7.0 migration
							// (active_flag returned false for missing source options) when v6
							// defaults had them on. Restore to true so sections are visible.
							$current_templates[ $template ][ $key ] = $recovered_value;
						}
					}
				}

				// If the site used the default template type in v6, the user never
				// customised alignments — update any field still at the old 'center'
				// default to the new 'left' default introduced in v7.1.0.
				if ( 'default' === ( $backup['wcdn_template_type'] ?? null ) ) {

					$left_aligned_fields = array(
						'logoAlignment',
						'documentTitleAlign',
						'shopNameAlign',
						'addressAlign',
						'shopPhoneAlign',
						'shopEmailAlign',
					);

					foreach ( array_keys( $current_templates ) as $tmpl ) {
						foreach ( $left_aligned_fields as $field ) {
							if ( 'center' === ( $current_templates[ $tmpl ][ $field ] ?? null ) ) {
								$current_templates[ $tmpl ][ $field ] = 'left';
							}
						}
					}
				}

				update_option( Templates::OPTION_KEY, $current_templates );
			}
		}

		// Normalize documentTitleFontSize: the default changed from 40 → 25 in v7.1.0.
		// Update any template still holding the old default value.
		$ct         = get_option( Templates::OPTION_KEY, array() );
		$ct_updated = false;

		foreach ( array_keys( $ct ) as $tmpl ) {
			if ( 40 === ( $ct[ $tmpl ]['documentTitleFontSize'] ?? null ) ) {
				$ct[ $tmpl ]['documentTitleFontSize'] = 25;
				$ct_updated                           = true;
			}
		}

		if ( $ct_updated ) {
			update_option( Templates::OPTION_KEY, $ct );
		}

		update_option( 'wcdn_migration_7_1_0_completed', true );
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

		$backup   = array();
		$autoload = array();

		foreach ( $options as $row ) {
			$backup[ $row['option_name'] ]   = maybe_unserialize( $row['option_value'] );
			$autoload[ $row['option_name'] ] = $row['autoload'] ?? 'yes';
		}

		update_option( 'wcdn_legacy_backup', $backup, false );
		update_option( 'wcdn_legacy_backup_autoload', $autoload, false );
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
				'source'    => 'wcdn_custom_company_name',
				'transform' => function ( $value ) {

					// Primary: standalone wcdn_custom_company_name option.
					if ( ! empty( $value ) ) {
						return sanitize_text_field( $value );
					}

					// Fallback: nested shop_name inside wcdn_general_settings.
					$general = self::get_source( 'wcdn_general_settings' );
					return ! empty( $general['shop_name'] )
						? sanitize_text_field( $general['shop_name'] )
						: null;
				},
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

					$suffix  = self::get_source( 'wcdn_invoice_number_suffix' );
					$counter = (int) ( self::get_source( 'wcdn_invoice_number_counter' ) ?? 0 );

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
					'transform' => function ( $value ) {
						// Email attachment was opt-in in v6. Absent section = off.
						if ( ! is_array( $value ) ) {
							return false;
						}
						return isset( $value['active'] ) && 'on' === $value['active'];
					},
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
					'transform' => function () {
						return 25;
					},
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
					'transform' => function ( $value ) {
						if ( 'simple' === self::get_source( 'wcdn_template_type' ) ) {
							return null;
						}
						return is_numeric( $value ) ? (int) $value : null;
					},
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
					'transform' => function ( $value ) {
						// Email attachment was opt-in in v6. Absent section = off.
						if ( ! is_array( $value ) ) {
							return false;
						}
						return isset( $value['active'] ) && 'on' === $value['active'];
					},
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
					'transform' => function () {
						return 25;
					},
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
					'transform' => function ( $value ) {
						if ( 'simple' === self::get_source( 'wcdn_template_type' ) ) {
							return null;
						}
						return is_numeric( $value ) ? (int) $value : null;
					},
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
					'transform' => function ( $value ) {
						// Email attachment was opt-in in v6. Absent section = off.
						if ( ! is_array( $value ) ) {
							return false;
						}
						return isset( $value['active'] ) && 'on' === $value['active'];
					},
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
					'transform' => function () {
						return 25;
					},
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
					'transform' => function ( $value ) {
						if ( 'simple' === self::get_source( 'wcdn_template_type' ) ) {
							return null;
						}
						return is_numeric( $value ) ? (int) $value : null;
					},
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

		// Callable transforms receive null so they can implement their own
		// fallback logic (e.g. checking a secondary option key).
		// active_flag also receives null so the transform can return true for
		// absent source options (v6 default was all-on).
		if ( 'active_flag' !== $transform && ! is_callable( $transform ) && ( null === $value || '' === $value ) ) {
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
				// Value is the whole section array.
				// null  → source option absent entirely; v6 default was all-on → true.
				// non-array, non-null → malformed value → treat as disabled.
				if ( null === $value ) {
					return true;
				}
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
	 * Restores legacy options to the exact state captured by backup_old_data():
	 * same values, same autoload settings. Keys created by the migration that
	 * did not exist pre-migration are removed so the DB is left in a clean
	 * pre-v7 state. The backup itself is deleted so it is regenerated fresh on
	 * next activation rather than accumulating stale data.
	 *
	 * @return bool
	 * @since 7.0
	 */
	public static function rollback() {

		$backup   = get_option( 'wcdn_legacy_backup' );
		$autoload = get_option( 'wcdn_legacy_backup_autoload', array() );

		if ( empty( $backup ) || ! is_array( $backup ) ) {
			return false;
		}

		foreach ( $backup as $option => $value ) {
			// Restore with the original autoload setting when available (new backups).
			// Fall back to null for old backups that pre-date autoload tracking: null
			// preserves the existing autoload for options still in the DB, and lets
			// WordPress default to 'yes' only for options being freshly recreated.
			$original_autoload = isset( $autoload[ $option ] )
				? ( 'no' !== $autoload[ $option ] )
				: null;
			update_option( $option, $value, $original_autoload );
		}

		// Remove keys created by the migration that were not in the pre-v7 snapshot.
		// migrate_invoice_counter() creates wcdn_invoice_number_counter after the SQL
		// snapshot runs, so it is absent from $backup if it did not already exist.
		if ( ! isset( $backup['wcdn_invoice_number_counter'] ) ) {
			delete_option( 'wcdn_invoice_number_counter' );
		}
		if ( ! isset( $backup['wcdn_invoice_number_counter_lock'] ) ) {
			delete_option( 'wcdn_invoice_number_counter_lock' );
		}

		// Delete backup artifacts — they will be regenerated on next activation.
		delete_option( 'wcdn_legacy_backup' );
		delete_option( 'wcdn_legacy_backup_autoload' );

		// Remove new data.
		delete_option( Settings::OPTION_KEY );
		delete_option( Templates::OPTION_KEY );

		// Reset all migration flags so run() can re-execute cleanly.
		delete_option( 'wcdn_migration_7_completed' );
		delete_option( 'wcdn_migration_7_0_2_completed' );
		delete_option( 'wcdn_migration_7_1_0_completed' );

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
