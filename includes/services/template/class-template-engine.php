<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Template Engine.
 *
 * Responsible for:
 * - Building template structure
 * - Generating schema
 * - Generating defaults
 * - Generating UI config
 *
 * @package     WCDN
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Class Template_Engine
 *
 * Core template builder engine.
 *
 * Provides a centralized registry for template layouts
 * and reusable UI components.
 *
 * @since 7.0
 */
class Template_Engine {

	/**
	 * Cached template layouts.
	 *
	 * @var array<string,array>|null
	 * @since 7.0
	 */
	private static $layouts_cache = null;

	/**
	 * Cached component registry.
	 *
	 * @var array<string,array>|null
	 * @since 7.0
	 */
	private static $components_cache = null;

	/**
	 * Cached built structures.
	 *
	 * @var array<string,array>
	 * @since 7.0
	 */
	private static $structure_cache = array();

	/**
	 * Get full template structure.
	 *
	 * Returns a structured array used for:
	 * - Schema generation
	 * - Default generation
	 * - UI configuration building
	 *
	 * @param string $template Template key.
	 *
	 * @return array Structured template definition.
	 *
	 * @since 7.0
	 */
	public static function get_structure( $template ) {

		if ( isset( self::$structure_cache[ $template ] ) ) {
			return self::$structure_cache[ $template ];
		}

		$layouts = self::template_layouts();

		if ( ! isset( $layouts[ $template ] ) ) {
			return array();
		}

		switch ( $template ) {

			case 'invoice':
				$structure = array(
					'sections' => array(
						array(
							'type'  => 'section',
							'id'    => 'invoiceSettings',
							'title' => __( 'Invoice Settings', 'woocommerce-delivery-notes' ),
							'items' => self::build_groups( array( 'enabled', 'dateFormat', 'pdfFilename', 'attachCustomerEmail', 'attachAdminEmail', 'attachCustomEmails', 'customEmailAddresses', 'attachToOrderStatus', 'orderStatusToAttachTo', 'attachToWoocommerceEmails', 'woocommerceEmailsToAttachTo' ) ),
						),
						array(
							'type'  => 'section',
							'id'    => 'invoiceTemplateSettings',
							'title' => __( 'Template Settings', 'woocommerce-delivery-notes' ),
							'items' => self::build_groups( $layouts[ $template ] ),
						),
					),
				);
				break;

			case 'receipt':
				$structure = array(
					'sections' => array(
						array(
							'type'  => 'section',
							'id'    => 'receiptSettings',
							'title' => __( 'Receipt Settings', 'woocommerce-delivery-notes' ),
							'items' => self::build_groups( array( 'enabled', 'dateFormat', 'pdfFilename', 'attachCustomerEmail', 'attachAdminEmail', 'attachCustomEmails', 'customEmailAddresses', 'attachToOrderStatus', 'orderStatusToAttachTo', 'attachToWoocommerceEmails', 'woocommerceEmailsToAttachTo' ) ),
						),
						array(
							'type'  => 'section',
							'id'    => 'receiptTemplateSettings',
							'title' => __( 'Template Settings', 'woocommerce-delivery-notes' ),
							'items' => self::build_groups( $layouts[ $template ] ),
						),
					),
				);
				break;

			case 'deliverynote':
				$structure = array(
					'sections' => array(
						array(
							'type'  => 'section',
							'id'    => 'deliveryNoteSettings',
							'title' => __( 'Delivery Note Settings', 'woocommerce-delivery-notes' ),
							'items' => self::build_groups( array( 'enabled', 'dateFormat', 'pdfFilename', 'attachCustomerEmail', 'attachAdminEmail', 'attachCustomEmails', 'customEmailAddresses', 'attachToOrderStatus', 'orderStatusToAttachTo', 'attachToWoocommerceEmails', 'woocommerceEmailsToAttachTo' ) ),
						),
						array(
							'type'  => 'section',
							'id'    => 'deliveryNoteTemplateSettings',
							'title' => __( 'Template Settings', 'woocommerce-delivery-notes' ),
							'items' => self::build_groups( $layouts[ $template ] ),
						),
					),
				);
				break;

			case 'packingslip':
				$structure = array(
					'sections' => array(
						array(
							'type'  => 'section',
							'id'    => 'packingSlipSettings',
							'title' => __( 'Packing Slip Settings', 'woocommerce-delivery-notes' ),
							'items' => self::build_groups( array( 'enabled', 'dateFormat', 'pdfFilename', 'attachCustomerEmail', 'attachAdminEmail', 'attachCustomEmails', 'customEmailAddresses', 'attachToOrderStatus', 'orderStatusToAttachTo', 'attachToWoocommerceEmails', 'woocommerceEmailsToAttachTo' ) ),
						),
						array(
							'type'  => 'section',
							'id'    => 'packingSlipTemplateSettings',
							'title' => __( 'Template Settings', 'woocommerce-delivery-notes' ),
							'items' => self::build_groups( $layouts[ $template ] ),
						),
					),
				);
				break;

			case 'creditnote':
				$structure = array(
					'sections' => array(
						array(
							'type'  => 'section',
							'id'    => 'creditNoteSettings',
							'title' => __( 'Credit Note Settings', 'woocommerce-delivery-notes' ),
							'items' => self::build_groups( array( 'enabled', 'dateFormat', 'pdfFilename', 'attachCustomerEmail', 'attachAdminEmail', 'attachCustomEmails', 'customEmailAddresses', 'attachToOrderStatus', 'orderStatusToAttachTo', 'attachToWoocommerceEmails', 'woocommerceEmailsToAttachTo' ) ),
						),
						array(
							'type'  => 'section',
							'id'    => 'creditNoteTemplateSettings',
							'title' => __( 'Template Settings', 'woocommerce-delivery-notes' ),
							'items' => self::build_groups( $layouts[ $template ] ),
						),
					),
				);
				break;
		}

		self::$structure_cache[ $template ] = $structure;

		return $structure;
	}

	/**
	 * Build schema array from template structure.
	 *
	 * @param array $structure Template structure.
	 *
	 * @return array<string,string> Field schema definitions.
	 *
	 * @since 7.0
	 */
	public static function build_schema( $structure ) {

		$schema = array();

		if ( empty( $structure['sections'] ) ) {
			return $schema;
		}

		foreach ( $structure['sections'] as $section ) {
			foreach ( $section['items'] as $group ) {

				if ( isset( $group['items'] ) ) {
					foreach ( $group['items'] as $field ) {
						$schema[ $field['field'] ] = $field['schema'] ?? null;
					}
				} else {
					$schema[ $group['field'] ] = $group['schema'] ?? null;
				}
			}
		}

		return $schema;
	}

	/**
	 * Build default values from template structure.
	 *
	 * @param string $template Template.
	 * @param array  $structure Template structure.
	 *
	 * @return array<string,mixed> Default values keyed by field name.
	 *
	 * @since 7.0
	 */
	public static function build_defaults( $template, $structure ) {

		$defaults = array();

		if ( empty( $structure['sections'] ) ) {
			return $defaults;
		}

		foreach ( $structure['sections'] as $section ) {
			foreach ( $section['items'] as $group ) {

				if ( isset( $group['items'] ) ) {
					foreach ( $group['items'] as $field ) {
						$defaults[ $field['field'] ] = self::extract_template_config( $template, $field['default'] ?? null );
					}
				} else {
					$defaults[ $group['field'] ] = self::extract_template_config( $template, $group['default'] ?? null );
				}
			}
		}

		// Template Specific Defaults.
		if ( in_array( $template, array( 'invoice', 'receipt' ), true ) ) {
			$defaults['displayPriceInProductDetailsTable'] = true;
		}

		return $defaults;
	}

	/**
	 * Build UI configuration.
	 *
	 * Removes internal keys such as schema and default.
	 * Also removes toggle field from group items.
	 *
	 * @param string $template Template.
	 * @param array  $structure Template structure.
	 *
	 * @return array
	 *
	 * @since 7.0
	 */
	public static function build_config( $template, $structure ) {

		$sections = $structure['sections'] ?? array();

		foreach ( $sections as &$section ) {

			foreach ( $section['items'] as &$group ) {

				if ( isset( $group['items'] ) ) {

					if ( isset( $group['label'] ) ) {
						$group['label'] = self::extract_template_config( $template, $group['label'] );
					}

					$toggle_field = $group['toggle'] ?? null;

					foreach ( $group['items'] as $index => &$field ) {
						/*
						 * Remove toggle field from visible items
						 */
						if ( $toggle_field && $field['field'] === $toggle_field ) {
							unset( $group['items'][ $index ] );
							continue;
						}

						if ( isset( $field['label'] ) ) {
							$field['label'] = self::extract_template_config( $template, $field['label'] );
						}

						if ( isset( $field['bottomLabel'] ) ) {
							$field['bottomLabel'] = self::extract_template_config( $template, $field['bottomLabel'] );
						}

						if ( isset( $field['options'] ) ) {
							$field['options'] = self::extract_template_config( $template, $field['options'] );
						}

						/*
						 * Remove internal keys
						 */
						unset( $field['schema'], $field['default'] );
					}

					/*
					* Reindex array to avoid sparse numeric keys
					*/
					$group['items'] = array_values( $group['items'] );
				} else {

					if ( isset( $group['label'] ) ) {
						$group['label'] = self::extract_template_config( $template, $group['label'] );
					}

					if ( isset( $group['bottomLabel'] ) ) {
						$group['bottomLabel'] = self::extract_template_config( $template, $group['bottomLabel'] );
					}

					if ( isset( $group['options'] ) ) {
						$group['options'] = self::extract_template_config( $template, $group['options'] );
					}

					/*
					* Remove internal keys
					*/
					unset( $group['schema'], $group['default'] );
				}
			}
		}

		return $sections;
	}

	/**
	 * Extracts field values for cases where array of values are passed.
	 *
	 * @param string $template Template.
	 * @param array  $field Config Field.
	 *
	 * @since 7.0
	 */
	private static function extract_template_config( $template, $field ) {
		return is_array( $field ) ? ( $field[ $template ] ?? $field ) : $field;
	}

	/**
	 * Build groups from layout definition.
	 *
	 * @param array $layout Layout array.
	 * @return array
	 * @since 7.0
	 */
	private static function build_groups( $layout ) {

		$components = self::component_registry();
		$groups     = array();

		foreach ( $layout as $component_key ) {

			if ( isset( $components[ $component_key ] ) ) {
				$groups[] = $components[ $component_key ];
			}
		}

		return $groups;
	}

	/**
	 * Template layouts definition.
	 *
	 * @return array
	 * @since 7.0
	 */
	private static function template_layouts() {

		if ( null !== self::$layouts_cache ) {
			return self::$layouts_cache;
		}

		self::$layouts_cache = array(
			'invoice'      => array(
				'logo',
				'documentTitle',
				'shopName',
				'shopAddress',
				'shopPhone',
				'shopEmail',
				'billingAddress',
				'shippingAddress',
				'invoiceNumber',
				'documentDate',
				'orderNumber',
				'orderDate',
				'paymentMethod',
				'payNow',
				'customerNote',
				'complimentaryClose',
				'policies',
				'footer',
			),
			'receipt'      => array(
				'logo',
				'documentTitle',
				'shopName',
				'shopAddress',
				'shopPhone',
				'shopEmail',
				'billingAddress',
				'shippingAddress',
				'invoiceNumber',
				'documentDate',
				'orderNumber',
				'orderDate',
				'paymentMethod',
				'paymentDate',
				'watermark',
				'customerNote',
				'complimentaryClose',
				'policies',
				'footer',
			),
			'deliverynote' => array(
				'logo',
				'documentTitle',
				'shopName',
				'shopAddress',
				'shopPhone',
				'shopEmail',
				'billingAddress',
				'shippingAddress',
				'invoiceNumber',
				'documentDate',
				'orderNumber',
				'orderDate',
				'displayPriceInProductDetailsTable',
				'customerNote',
				'complimentaryClose',
				'policies',
				'footer',
			),
			'packingslip'  => array(
				'logo',
				'documentTitle',
				'shopName',
				'shopAddress',
				'shopPhone',
				'shopEmail',
				'billingAddress',
				'shippingAddress',
				'documentDate',
				'orderNumber',
				'orderDate',
				'shippingMethod',
				'customerNote',
				'complimentaryClose',
				'policies',
				'footer',
			),
			'creditnote'   => array(
				'logo',
				'documentTitle',
				'shopName',
				'shopAddress',
				'shopPhone',
				'shopEmail',
				'billingAddress',
				'shippingAddress',
				'invoiceNumber',
				'documentDate',
				'orderNumber',
				'orderDate',
				'paymentMethod',
				'refundDate',
				'refundReason',
				'refundTotal',
				'displayRefundItemsInTable',
				'watermark',
				'customerNote',
				'complimentaryClose',
				'policies',
				'footer',
			),
		);

		return self::$layouts_cache;
	}

	/**
	 * Get all registered template keys.
	 *
	 * @return array<string> Template keys.
	 *
	 * @since 7.0
	 */
	public static function get_template_keys() {
		return array_keys( self::template_layouts() );
	}

	/**
	 * Component registry.
	 *
	 * Defines reusable template UI components.
	 *
	 * @return array
	 * @since 7.0
	 */
	private static function component_registry() {

		if ( null !== self::$components_cache ) {
			return self::$components_cache;
		}

		self::$components_cache = array(

			/* Enable */
			'enabled'                           => array(
				'type'        => 'field',
				'fieldType'   => 'checkbox',
				'field'       => 'enabled',
				'label'       => array(
					'invoice'      => __( 'Enable Invoice', 'woocommerce-delivery-notes' ),
					'receipt'      => __( 'Enable Receipt', 'woocommerce-delivery-notes' ),
					'deliverynote' => __( 'Enable Delivery Note', 'woocommerce-delivery-notes' ),
					'packingslip'  => __( 'Enable Packing Slip', 'woocommerce-delivery-notes' ),
					'creditnote'   => __( 'Enable Credit Note', 'woocommerce-delivery-notes' ),
				),
				'schema'      => 'bool',
				'default'     => true,
				'bottomLabel' => array(
					'invoice'      => __(
						'An invoice ia a detailed bill showing items purchased, taxes, shipping charges, and the total amount due.',
						'woocommerce-delivery-notes'
					),
					'receipt'      => __(
						'A receipt is used to issue a payment confirmation document showing items purchased and the amount paid.',
						'woocommerce-delivery-notes'
					),
					'deliverynote' => __(
						'The delivery note template generates a document listing delivered items.',
						'woocommerce-delivery-notes'
					),
					'packingslip'  => __(
						'The packing slip includes a document inside the package detailing the shipment contents for verification.',
						'woocommerce-delivery-notes'
					),
					'creditnote'   => __(
						'The credit note is a document created to refund or adjust a previously issued invoice.',
						'woocommerce-delivery-notes'
					),
				),
			),

			/* Date Format */
			'dateFormat'                        => array(
				'type'      => 'field',
				'fieldType' => 'select',
				'field'     => 'dateFormat',
				'label'     => __( 'Date Format', 'woocommerce-delivery-notes' ),
				'options'   => array_values(
					array_reduce(
						array(
							array(
								'label' => date_i18n( get_option( 'date_format' ) ),
								'value' => get_option( 'date_format' ),
							),
							array(
								'label' => date_i18n( 'F j, Y' ),
								'value' => 'F j, Y',
							),
							array(
								'label' => date_i18n( 'Y-m-d' ),
								'value' => 'Y-m-d',
							),
							array(
								'label' => date_i18n( 'd/m/Y' ),
								'value' => 'd/m/Y',
							),
							array(
								'label' => date_i18n( 'm/d/Y' ),
								'value' => 'm/d/Y',
							),
						),
						fn( $c, $i ) => $c + array( $i['value'] => $i ),
						array()
					)
				),
				'schema'    => 'text',
				'default'   => get_option( 'date_format' ),
			),

			/* PDF Filename */
			'pdfFilename'                       => array(
				'type'        => 'field',
				'fieldType'   => 'text',
				'field'       => 'pdfFilename',
				'label'       => __( 'PDF Filename', 'woocommerce-delivery-notes' ),
				'bottomLabel' => __(
					'Available placeholders: {order_number}, {order_date}, {customer_name}',
					'woocommerce-delivery-notes'
				),
				'schema'      => 'text',
				'default'     => array(
					'invoice'      => 'invoice-{order_number}.pdf',
					'receipt'      => 'receipt-{order_number}.pdf',
					'deliverynote' => 'delivery-note-{order_number}.pdf',
					'packingslip'  => 'packing-slip-{order_number}.pdf',
					'creditnote'   => 'credit-note-{order_number}.pdf',
				),
			),

			/* Attach Customer Email */
			'attachCustomerEmail'               => array(
				'type'      => 'field',
				'fieldType' => 'checkbox',
				'field'     => 'attachCustomerEmail',
				'label'     => __( 'Attach PDF to customer emails', 'woocommerce-delivery-notes' ),
				'schema'    => 'bool',
				'default'   => true,
			),

			/* Attach Admin Email */
			'attachAdminEmail'                  => array(
				'type'      => 'field',
				'fieldType' => 'checkbox',
				'field'     => 'attachAdminEmail',
				'label'     => __( 'Send the PDF as an attachment to all administrator email addresses.', 'woocommerce-delivery-notes' ),
				'schema'    => 'bool',
				'default'   => false,
			),

			/* Attach Custom Email */
			'attachCustomEmails'                => array(
				'type'      => 'field',
				'fieldType' => 'checkbox',
				'field'     => 'attachCustomEmails',
				'label'     => __( 'Attach PDF to custom email addresses', 'woocommerce-delivery-notes' ),
				'schema'    => 'bool',
				'default'   => false,
			),
			'customEmailAddresses'              => array(
				'type'        => 'field',
				'fieldType'   => 'textarea',
				'field'       => 'customEmailAddresses',
				'label'       => '',
				'condition'   => 'attachCustomEmails',
				'bottomLabel' => __(
					'Enter multiple email addresses separated by commas.',
					'woocommerce-delivery-notes'
				),
				'schema'      => 'text',
				'default'     => '',
			),

			/* Attach PDF to Custom Order Status */
			'attachToOrderStatus'               => array(
				'type'      => 'field',
				'fieldType' => 'checkbox',
				'field'     => 'attachToOrderStatus',
				'label'     => __( 'Attach PDF to selected order status(es)', 'woocommerce-delivery-notes' ),
				'schema'    => 'bool',
				'default'   => false,
			),
			'orderStatusToAttachTo'             => array(
				'type'        => 'field',
				'fieldType'   => 'multiselect',
				'field'       => 'orderStatusToAttachTo',
				'label'       => '',
				'condition'   => 'attachToOrderStatus',
				'schema'      => 'array',
				'default'     => array(),
				'options'     => array(
					'invoice'      => self::get_template_order_statuses( 'invoice' ),
					'receipt'      => self::get_template_order_statuses( 'receipt' ),
					'deliverynote' => self::get_template_order_statuses( 'deliverynote' ),
					'packingslip'  => self::get_template_order_statuses( 'packingslip' ),
					'creditnote'   => self::get_template_order_statuses( 'creditnote' ),
				),
				'bottomLabel' => __(
					'Start typing to select order statuses.',
					'woocommerce-delivery-notes'
				),
			),

			/* Attach PDF to selected WooCommerce Emails */
			'attachToWoocommerceEmails'         => array(
				'type'      => 'field',
				'fieldType' => 'checkbox',
				'field'     => 'attachToWoocommerceEmails',
				'label'     => __( 'Attach PDF to selected WooCommerce Email(s)', 'woocommerce-delivery-notes' ),
				'schema'    => 'bool',
				'default'   => false,
			),
			'woocommerceEmailsToAttachTo'       => array(
				'type'        => 'field',
				'fieldType'   => 'multiselect',
				'field'       => 'woocommerceEmailsToAttachTo',
				'label'       => '',
				'condition'   => 'attachToWoocommerceEmails',
				'schema'      => 'array',
				'default'     => array(),
				'options'     => self::woocommerce_email_types(),
				'bottomLabel' => __(
					'Start typing to select WooCommerce email types.',
					'woocommerce-delivery-notes'
				),
			),

			/* Logo */
			'logo'                              => array(
				'type'   => 'group',
				'id'     => 'logo',
				'label'  => __( 'Show Shop Logo', 'woocommerce-delivery-notes' ),
				'toggle' => 'showLogo',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showLogo',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'logoScale',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Scale', 'woocommerce-delivery-notes' ),
						'default'   => 100,
						'min'       => 20,
						'max'       => 120,
					),
					array(
						'type'      => 'field',
						'field'     => 'logoAlignment',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Alignment', 'woocommerce-delivery-notes' ),
						'default'   => 'center',
						'options'   => self::field_options( 'alignOptions' ),
					),
				),
			),

			/* Document Title */
			'documentTitle'                     => array(
				'type'  => 'group',
				'id'    => 'documentTitle',
				'label' => __( 'Document Title', 'woocommerce-delivery-notes' ),
				'items' => array(
					array(
						'type'      => 'field',
						'field'     => 'documentTitle',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Title', 'woocommerce-delivery-notes' ),
						'default'   => array(
							'invoice'      => __( 'INVOICE', 'woocommerce-delivery-notes' ),
							'receipt'      => __( 'RECEIPT', 'woocommerce-delivery-notes' ),
							'deliverynote' => __( 'DELIVERY NOTE', 'woocommerce-delivery-notes' ),
							'packingslip'  => __( 'PACKING SLIP', 'woocommerce-delivery-notes' ),
							'creditnote'   => __( 'CREDIT NOTE', 'woocommerce-delivery-notes' ),
						),
					),
					array(
						'type'      => 'field',
						'field'     => 'documentTitleFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 40,
						'min'       => 14,
						'max'       => 40,
					),
					array(
						'type'      => 'field',
						'field'     => 'documentTitleFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'bold',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'documentTitleAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'center',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'documentTitleTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Shop Name */
			'shopName'                          => array(
				'type'   => 'group',
				'id'     => 'shopName',
				'label'  => __( 'Show Shop Name', 'woocommerce-delivery-notes' ),
				'toggle' => 'showShopName',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showShopName',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'shopNameFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 18,
						'min'       => 10,
						'max'       => 30,
					),
					array(
						'type'      => 'field',
						'field'     => 'shopNameFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'bold',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'shopNameAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'center',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'shopNameTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Shop Address */
			'shopAddress'                       => array(
				'type'   => 'group',
				'id'     => 'shopAddress',
				'label'  => __( 'Show Shop Address', 'woocommerce-delivery-notes' ),
				'toggle' => 'showShopAddress',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showShopAddress',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'addressFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 14,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'addressFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'normal',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'addressAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'center',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'addressTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Shop Phone */
			'shopPhone'                         => array(
				'type'   => 'group',
				'id'     => 'shopPhone',
				'label'  => __( 'Show Shop Phone Number', 'woocommerce-delivery-notes' ),
				'toggle' => 'showShopPhone',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showShopPhone',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'shopPhoneText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => 'Phone',
					),
					array(
						'type'      => 'field',
						'field'     => 'shopPhoneFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 14,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'shopPhoneTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Shop Email */
			'shopEmail'                         => array(
				'type'   => 'group',
				'id'     => 'showShopEmail',
				'label'  => __( 'Show Shop Email Address', 'woocommerce-delivery-notes' ),
				'toggle' => 'showShopEmail',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showShopEmail',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'shopEmailText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => 'Email',
					),
					array(
						'type'      => 'field',
						'field'     => 'shopEmailFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 14,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'shopEmailTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Billing Address */
			'billingAddress'                    => array(
				'type'   => 'group',
				'id'     => 'billingAddress',
				'label'  => __( 'Show Billing Address', 'woocommerce-delivery-notes' ),
				'toggle' => 'showBillingAddress',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showBillingAddress',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'billingAddressText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => 'Billing Address',
					),
					array(
						'type'      => 'field',
						'field'     => 'billingAddressFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 14,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'billingAddressFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'normal',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'billingAddressAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'left',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'billingAddressTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Shipping Address */
			'shippingAddress'                   => array(
				'type'   => 'group',
				'id'     => 'shippingAddress',
				'label'  => __( 'Show Shipping Address', 'woocommerce-delivery-notes' ),
				'toggle' => 'showShippingAddress',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showShippingAddress',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'shippingAddressText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => 'Shipping Address',
					),
					array(
						'type'      => 'field',
						'field'     => 'shippingAddressFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 14,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'shippingAddressFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'normal',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'shippingAddressAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'left',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'shippingAddressTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Invoice Number */
			'invoiceNumber'                     => array(
				'type'   => 'group',
				'id'     => 'invoiceNumber',
				'label'  => __( 'Show Invoice Number', 'woocommerce-delivery-notes' ),
				'toggle' => 'showInvoiceNumber',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showInvoiceNumber',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'invoiceNumberText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => 'Invoice No',
					),
					array(
						'type'      => 'field',
						'field'     => 'invoiceNumberFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 14,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'invoiceNumberFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'normal',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'invoiceNumberAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'left',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'invoiceNumberTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Document Date */
			'documentDate'                      => array(
				'type'   => 'group',
				'id'     => 'documentDate',
				'label'  => array(
					'invoice'      => __( 'Show Invoice Date', 'woocommerce-delivery-notes' ),
					'receipt'      => __( 'Show Receipt Date', 'woocommerce-delivery-notes' ),
					'deliverynote' => __( 'Show Delivery Note Date', 'woocommerce-delivery-notes' ),
					'packingslip'  => __( 'Show Packing Slip Date', 'woocommerce-delivery-notes' ),
					'creditnote'   => __( 'Show Credit Note Date', 'woocommerce-delivery-notes' ),
				),
				'toggle' => 'showDocumentDate',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showDocumentDate',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => false,
					),
					array(
						'type'      => 'field',
						'field'     => 'documentDateText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => array(
							'invoice'      => __( 'Invoice Date', 'woocommerce-delivery-notes' ),
							'receipt'      => __( 'Receipt Date', 'woocommerce-delivery-notes' ),
							'deliverynote' => __( 'Delivery Note Date', 'woocommerce-delivery-notes' ),
							'packingslip'  => __( 'Packing Slip Date', 'woocommerce-delivery-notes' ),
							'creditnote'   => __( 'Credit Note Date', 'woocommerce-delivery-notes' ),
						),
					),
					array(
						'type'      => 'field',
						'field'     => 'documentDateFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 14,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'documentDateFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'normal',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'documentDateAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'left',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'documentDateTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Order Number */
			'orderNumber'                       => array(
				'type'   => 'group',
				'id'     => 'orderNumber',
				'label'  => __( 'Show Order Number', 'woocommerce-delivery-notes' ),
				'toggle' => 'showOrderNumber',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showOrderNumber',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'orderNumberText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => 'Order No',
					),
					array(
						'type'      => 'field',
						'field'     => 'orderNumberFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 14,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'orderNumberFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'normal',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'orderNumberAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'left',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'orderNumberTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Order Date */
			'orderDate'                         => array(
				'type'   => 'group',
				'id'     => 'orderDate',
				'label'  => __( 'Show Order Date', 'woocommerce-delivery-notes' ),
				'toggle' => 'showOrderDate',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showOrderDate',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'orderDateText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => 'Date',
					),
					array(
						'type'      => 'field',
						'field'     => 'orderDateFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 14,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'orderDateFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'normal',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'orderDateAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'left',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'orderDateTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Payment Method */
			'paymentMethod'                     => array(
				'type'   => 'group',
				'id'     => 'paymentMethod',
				'label'  => __( 'Show Payment Method', 'woocommerce-delivery-notes' ),
				'toggle' => 'showPaymentMethod',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showPaymentMethod',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'paymentMethodText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => __( 'Payment Method', 'woocommerce-delivery-notes' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'paymentMethodFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 14,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'paymentMethodFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'normal',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'paymentMethodAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'left',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'paymentMethodTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Payment Date */
			'paymentDate'                       => array(
				'type'   => 'group',
				'id'     => 'paymentDate',
				'label'  => __( 'Show Payment Date', 'woocommerce-delivery-notes' ),
				'toggle' => 'showPaymentDate',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showPaymentDate',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'paymentDateText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => __( 'Payment Method', 'woocommerce-delivery-notes' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'paymentDateFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 14,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'paymentDateFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'normal',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'paymentDateAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'left',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'paymentDateTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Shipping Method */
			'shippingMethod'                    => array(
				'type'   => 'group',
				'id'     => 'shippingMethod',
				'label'  => __( 'Show Shipping Method', 'woocommerce-delivery-notes' ),
				'toggle' => 'showShippingMethod',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showShippingMethod',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'shippingMethodText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => __( 'Shipping Method', 'woocommerce-delivery-notes' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'shippingMethodFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'normal',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'shippingMethodAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'left',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'shippingMethodTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Refund Date */
			'refundDate'                        => array(
				'type'   => 'group',
				'id'     => 'refundDate',
				'label'  => __( 'Show Refund Date', 'woocommerce-delivery-notes' ),
				'toggle' => 'showRefundDate',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showRefundDate',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'refundDateText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => __( 'Refund Date', 'woocommerce-delivery-notes' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'refundDateFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 14,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'refundDateFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'normal',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'refundDateAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'left',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'refundDateTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Refund Reason */
			'refundReason'                      => array(
				'type'   => 'group',
				'id'     => 'refundReason',
				'label'  => __( 'Show Refund Reason', 'woocommerce-delivery-notes' ),
				'toggle' => 'showRefundReason',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showRefundReason',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'refundReasonText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => __( 'Refund Reason', 'woocommerce-delivery-notes' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'refundReasonFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 14,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'refundReasonFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'normal',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'refundReasonAlign',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Text Align', 'woocommerce-delivery-notes' ),
						'default'   => 'left',
						'options'   => self::field_options( 'alignOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'refundReasonTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Pay Now */
			'payNow'                            => array(
				'type'   => 'group',
				'id'     => 'payNow',
				'label'  => __( 'Show Pay Now Button', 'woocommerce-delivery-notes' ),
				'toggle' => 'showPayNowButton',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showPayNowButton',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => false,
					),
					array(
						'type'      => 'field',
						'field'     => 'payNowLabel',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Button Label', 'woocommerce-delivery-notes' ),
						'default'   => __( 'Pay Now', 'woocommerce-delivery-notes' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'payNowColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Button Color', 'woocommerce-delivery-notes' ),
						'default'   => '#2271b1',
					),
				),
			),

			/* Customer Note */
			'customerNote'                      => array(
				'type'   => 'group',
				'id'     => 'customerNote',
				'label'  => __( 'Show Customer Note', 'woocommerce-delivery-notes' ),
				'toggle' => 'showCustomerNote',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showCustomerNote',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => false,
					),
					array(
						'type'      => 'field',
						'field'     => 'customerNoteTitle',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Title', 'woocommerce-delivery-notes' ),
						'default'   => 'Customer Note',
					),
					array(
						'type'      => 'field',
						'field'     => 'customerNoteFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 13,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'customerNoteFontStyle',
						'schema'    => 'text',
						'fieldType' => 'select',
						'label'     => __( 'Font Style', 'woocommerce-delivery-notes' ),
						'default'   => 'normal',
						'options'   => self::field_options( 'fontStyleOptions' ),
					),
					array(
						'type'      => 'field',
						'field'     => 'customerNoteTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Complimentary Close */
			'complimentaryClose'                => array(
				'type'   => 'group',
				'id'     => 'complimentaryClose',
				'label'  => __( 'Show Complimentary Close', 'woocommerce-delivery-notes' ),
				'toggle' => 'showComplimentaryClose',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showComplimentaryClose',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => false,
					),
					array(
						'type'      => 'field',
						'field'     => 'complimentaryCloseFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 13,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'complimentaryCloseTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Policies */
			'policies'                          => array(
				'type'   => 'group',
				'id'     => 'policies',
				'label'  => __( 'Show Policies', 'woocommerce-delivery-notes' ),
				'toggle' => 'showPolicies',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showPolicies',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'policiesFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 13,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'policiesTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#444444',
					),
				),
			),

			/* Footer */
			'footer'                            => array(
				'type'   => 'group',
				'id'     => 'footer',
				'label'  => __( 'Show Footer', 'woocommerce-delivery-notes' ),
				'toggle' => 'showFooter',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showFooter',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'footerFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 13,
						'min'       => 10,
						'max'       => 20,
					),
					array(
						'type'      => 'field',
						'field'     => 'footerTextColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Text Color', 'woocommerce-delivery-notes' ),
						'default'   => '#666666',
					),
				),
			),

			/* Watermark */
			'watermark'                         => array(
				'type'   => 'group',
				'id'     => 'watermark',
				'label'  => __( 'Show Watermark', 'woocommerce-delivery-notes' ),
				'toggle' => 'showWatermark',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'showWatermark',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
					array(
						'type'      => 'field',
						'field'     => 'watermarkText',
						'schema'    => 'text',
						'fieldType' => 'text',
						'label'     => __( 'Text', 'woocommerce-delivery-notes' ),
						'default'   => array(
							'receipt'    => __( 'PAID', 'woocommerce-delivery-notes' ),
							'creditnote' => __( 'REFUNDED', 'woocommerce-delivery-notes' ),
						),
					),
					array(
						'type'      => 'field',
						'field'     => 'watermarkFontSize',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Font Size', 'woocommerce-delivery-notes' ),
						'default'   => 120,
						'min'       => 50,
						'max'       => 200,
					),
					array(
						'type'      => 'field',
						'field'     => 'watermarkOpacity',
						'schema'    => 'float',
						'fieldType' => 'slider',
						'label'     => __( 'Opacity', 'woocommerce-delivery-notes' ),
						'default'   => 0.08,
						'min'       => 0.02,
						'max'       => 0.5,
						'step'      => 0.01,
					),
					array(
						'type'      => 'field',
						'field'     => 'watermarkAngle',
						'schema'    => 'number',
						'fieldType' => 'slider',
						'label'     => __( 'Angle', 'woocommerce-delivery-notes' ),
						'default'   => -25,
						'min'       => -90,
						'max'       => 90,
						'step'      => 11,
					),
					array(
						'type'      => 'field',
						'field'     => 'watermarkLayout',
						'schema'    => 'text',
						'fieldType' => 'radio',
						'label'     => __( 'Layout', 'woocommerce-delivery-notes' ),
						'default'   => 'single',
						'options'   => array(
							array(
								'label' => 'Single',
								'value' => 'single',
							),
							array(
								'label' => 'Repeat',
								'value' => 'repeat',
							),
						),
					),
					array(
						'type'      => 'field',
						'field'     => 'watermarkColor',
						'schema'    => 'text',
						'fieldType' => 'color',
						'label'     => __( 'Color', 'woocommerce-delivery-notes' ),
						'default'   => '#000000',
					),
				),
			),

			/* Display price in product details table */
			'displayPriceInProductDetailsTable' => array(
				'type'   => 'group',
				'id'     => 'displayPriceInProductDetailsTable',
				'label'  => __( 'Display Price in Product Details Table', 'woocommerce-delivery-notes' ),
				'toggle' => 'displayPriceInProductDetailsTable',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'displayPriceInProductDetailsTable',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
				),
			),

			/* Show Refund Items */
			'displayRefundItemsInTable'         => array(
				'type'   => 'group',
				'id'     => 'displayRefundItemsInTable',
				'label'  => __( 'Display Refund Items in Product Table', 'woocommerce-delivery-notes' ),
				'toggle' => 'displayRefundItemsInTable',
				'items'  => array(
					array(
						'type'      => 'field',
						'field'     => 'displayRefundItemsInTable',
						'schema'    => 'bool',
						'fieldType' => 'checkbox',
						'default'   => true,
					),
				),
			),
		);

		return self::$components_cache;
	}

	/**
	 * Get field option sets.
	 *
	 * @param string $option Option key.
	 *
	 * @return array<int,array<string,string>>
	 *
	 * @since 7.0
	 */
	private static function field_options( $option ) {

		$options = array(
			'alignOptions'     => array(
				array(
					'label' => __( 'Left', 'woocommerce-delivery-notes' ),
					'value' => 'left',
				),
				array(
					'label' => __( 'Center', 'woocommerce-delivery-notes' ),
					'value' => 'center',
				),
				array(
					'label' => __( 'Right', 'woocommerce-delivery-notes' ),
					'value' => 'right',
				),
			),
			'fontStyleOptions' => array(
				array(
					'label' => __( 'Normal', 'woocommerce-delivery-notes' ),
					'value' => 'normal',
				),
				array(
					'label' => __( 'Bold', 'woocommerce-delivery-notes' ),
					'value' => 'bold',
				),
			),
		);

		return $options[ $option ] ?? array();
	}

	/**
	 * Returns a normalized list of WooCommerce order statuses
	 * formatted for UI consumption.
	 *
	 * Each status contains a label and a value without the `wc-` prefix.
	 *
	 * @return array List of order statuses with label and value.
	 * @since 7.0
	 */
	private static function order_statuses() {

		$order_statuses = wc_get_order_statuses();
		$statuses       = array();

		foreach ( $order_statuses as $key => $label ) {
			$statuses[] = array(
				'label' => $label,
				'value' => str_replace( 'wc-', '', $key ),
			);
		}

		return $statuses;
	}

	/**
	 *
	 * Defines the allowed WooCommerce order statuses for each document template.
	 *
	 * This ensures that only relevant statuses are selectable when attaching PDFs
	 * to specific document types.
	 *
	 * @param string $template Template key (e.g. invoice, receipt).
	 * @return array List of order statuses with label, value, and disabled state.
	 *
	 * @since 7.0
	 */
	public static function get_template_order_statuses( $template ) {

		$all_statuses      = self::order_statuses();
		$template_statuses = array(
			'invoice'      => array(
				'pending',
				'on-hold',
				'processing',
				'completed',
			),

			'receipt'      => array(
				'processing',
				'completed',
			),

			'deliverynote' => array(
				'processing',
				'completed',
			),

			'packingslip'  => array(
				'processing',
			),

			'creditnote'   => array(
				'refunded',
				'completed',
			),
		);

		$allowed = $template_statuses[ $template ] ?? array();

		// Allow extension via filter.
		$allowed = apply_filters( 'wcdn_allowed_statuses_' . $template, $allowed );

		$filtered = array();

		foreach ( $all_statuses as $status ) {

			if ( in_array( $status['value'], $allowed, true ) ) {
				$filtered[] = $status;
			}
		}

		return $filtered;
	}

	/**
	 * WooCommerce Email Types.
	 *
	 * @since 7.0
	 */
	private static function woocommerce_email_types() {

		$email_types = WC()->mailer()->get_emails();
		$emails      = array();

		foreach ( $email_types as $email ) {

			if ( ! in_array( $email->id, array( 'customer_reset_password', 'customer_new_account' ), true ) ) {
				$emails[] = array(
					'label' => $email->title,
					'value' => $email->id,
				);
			}
		}

		return $emails;
	}
}
