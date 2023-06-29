<?php
/**
 * Main class
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
 * Base class
 */
if ( ! class_exists( 'WooCommerce_Delivery_Notes' ) ) {
	/**
	 * WooCommerce Delivery Notes
	 *
	 * @author Tyche Softwares
	 * @package WooCommerce-Delivery-Notes
	 */
	final class WooCommerce_Delivery_Notes {

		/**
		 * The single instance of the class
		 *
		 * @var object $wcdn_instance Instance object
		 */
		protected static $wcdn_instance = null;

		/**
		 * Default properties
		 *
		 * @var string $plugin_version Current plugin version number
		 */
		public static $plugin_version = '5.0.0';

		/**
		 * Plugin URL on current installation
		 *
		 * @var string
		 */
		public static $plugin_url;

		/**
		 * Plugin folder path on current installation
		 *
		 * @var string
		 */
		public static $plugin_path;

		/**
		 * Plugin's basefile name
		 *
		 * @var string
		 */
		public static $plugin_basefile;

		/**
		 * Plugin's basefile path
		 *
		 * @var string
		 */
		public static $plugin_basefile_path;

		/**
		 * Plugin's text domain
		 *
		 * @var string
		 */
		public static $plugin_text_domain;

		/**
		 * Sub class instances
		 *
		 * @var object $writepanel
		 */
		public $writepanel;

		/**
		 * Sub class instances
		 *
		 * @var object $settings
		 */
		public $settings;

		/**
		 * Sub class instances
		 *
		 * @var object $print
		 */
		public $print;

		/**
		 * Sub class instances
		 *
		 * @var object $theme
		 */
		public $theme;

		/**
		 * Main Instance.
		 */
		public static function instance() {
			if ( is_null( self::$wcdn_instance ) ) {
				self::$wcdn_instance = new self();
			}
			return self::$wcdn_instance;
		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html_e( 'Cheatin&#8217; huh?', 'woocommerce-delivery-notes' ), '4.1' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html_e( 'Cheatin&#8217; huh?', 'woocommerce-delivery-notes' ), '4.1' );
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->init_hooks();

			add_action( 'wcdn_delete_file', array( $this, 'wcdn_delete_file_callbak' ) );

			// Send out the load action.
			do_action( 'wcdn_load' );
		}

		/**
		 * Hook into actions and filters.
		 */
		public function init_hooks() {
			add_action( 'init', array( $this, 'localise' ) );
			add_action( 'init', array( $this, 'wcdn_create_dir' ) );
			add_action( 'init', array( $this, 'wcdn_remove_save_btn' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'wcdn_deactivation_enquaue_script' ) );
			add_action( 'woocommerce_init', array( $this, 'load' ) );
		}


		/**
		 * Define WC Constants.
		 */
		private function define_constants() {
			self::$plugin_basefile_path = dirname( dirname( __FILE__ ) ) . '/woocommerce-delivery-notes.php';
			self::$plugin_basefile      = plugin_basename( self::$plugin_basefile_path );
			self::$plugin_url           = plugin_dir_url( self::$plugin_basefile );
			self::$plugin_path          = trailingslashit( dirname( self::$plugin_basefile_path ) );
			self::$plugin_text_domain   = trim( dirname( self::$plugin_basefile ) );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param string $name Constant name.
		 * @param string $value Constant value.
		 */
		private function define( $name, $value ) {

			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include the main plugin classes and functions.
		 */
		public function include_classes() {
			include_once 'class-wcdn-print.php';
			include_once 'class-wcdn-settings.php';
			include_once 'class-wcdn-writepanel.php';
			include_once 'class-wcdn-theme.php';
			if ( true === is_admin() ) {
				include_once 'wcdn-all-component.php';
			}

			require_once 'class-tyche-plugin-deactivation.php';
			new Tyche_Plugin_Deactivation(
				array(
					'plugin_name'       => 'Print invoices & delivery notes for WooCommerce orders',
					'plugin_base'       => 'Print-Invoice-Delivery-Notes-for-WooCommerce/woocommerce-delivery-notes.php',
					'script_file'       => self::$plugin_url . 'assets/js/plugin-deactivation.js',
					'plugin_short_name' => 'wcdn',
					'version'           => self::$plugin_version,
				)
			);
		}

		/**
		 * Function used to init Template Functions.
		 * This makes them pluggable by plugins and themes.
		 */
		public function include_template_functions() {
			include_once 'admin/wcdn-admin-function.php';
			include_once 'front/wcdn-front-function.php';
			include_once 'wcdn-template-functions.php';
			include_once 'wcdn-template-hooks.php';
		}


		/**
		 * Remove Button form help & guide tab.
		 *
		 * @since 5.0
		 */
		public function wcdn_remove_save_btn() {
			if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'wcdn-settings' && isset( $_GET['setting'] ) && $_GET['setting'] === 'wcdn_helpguide' ) { // phpcs:ignore
				?>
				<style type="text/css">
					.wp-core-ui .button-primary.woocommerce-save-button {
						display: none;
					}
				</style>
				<?php
			}
		}

		/**
		 * Enquaue Admin Script.
		 *
		 * @since 5.0
		 */
		public function wcdn_deactivation_enquaue_script() {
			wp_register_script(
				'tyche',
				self::$plugin_url . 'assets/js/tyche.js',
				array( 'jquery' ),
				self::$plugin_version,
				true
			);
			wp_enqueue_script( 'tyche' );
		}

		/**
		 * Load the localisation.
		 */
		public function localise() {
			// Load language files from the wp-content/languages/plugins folder.
			$mo_file = WP_LANG_DIR . '/plugins/' . self::$plugin_text_domain . '-' . get_locale() . '.mo';
			if ( is_readable( $mo_file ) ) {
				load_textdomain( self::$plugin_text_domain, $mo_file );
			}
			// Otherwise load them from the plugin folder.
			load_plugin_textdomain( self::$plugin_text_domain, false, dirname( self::$plugin_basefile ) . '/languages/' );
		}

		/**
		 * Create folders to store all document.
		 *
		 * @since 5.0
		 */
		public function wcdn_create_dir() {
			$is_action_scheduled = as_next_scheduled_action( 'wcdn_delete_file' );
			if ( false === $is_action_scheduled ) {
				as_schedule_recurring_action( time(), 86400, 'wcdn_delete_file' );
			}
			$upload_path = wp_upload_dir()['basedir'];

			if ( ! file_exists( $upload_path . '/wcdn' ) ) {
				mkdir( $upload_path . '/wcdn', 0777, true );
			}
			if ( ! file_exists( $upload_path . '/wcdn/invoice' ) ) {
				mkdir( $upload_path . '/wcdn/invoice', 0777, true );
			}
			if ( ! file_exists( $upload_path . '/wcdn/receipt' ) ) {
				mkdir( $upload_path . '/wcdn/receipt', 0777, true );
			}
			if ( ! file_exists( $upload_path . '/wcdn/deliverynote' ) ) {
				mkdir( $upload_path . '/wcdn/deliverynote', 0777, true );
			}
		}

		/**
		 * Delete all pdf after certain time.
		 *
		 * @since 5.0
		 */
		public function wcdn_delete_file_callbak() {
			
			/** Define directory */
			$upload_path       = wp_upload_dir()['basedir'];
			$wcdn_invoice      = $upload_path . '/wcdn/invoice/';
			$wcdn_receipt      = $upload_path . '/wcdn/receipt/';
			$wcdn_deliverynote = $upload_path . '/wcdn/deliverynote/';
			if ( isset( get_option( 'wcdn_general_settings' )['store_pdf'] ) ) {
				$get_x_days = get_option( 'wcdn_general_settings' )['store_pdf'];
			} else {
				$get_x_days = 7;
			}
			$convert_second = (int) $get_x_days * 24 * 60;
			foreach ( glob( $wcdn_invoice . '*' ) as $file ) {
				/*** If file is 24 hours (86400 seconds) old then delete it */
				if ( time() - filectime( $file ) > $convert_second ) {
					unlink( $file );
				}
			}
			foreach ( glob( $wcdn_receipt . '*' ) as $file ) {
				if ( time() - filectime( $file ) > $convert_second ) {
					unlink( $file );
				}
			}
			foreach ( glob( $wcdn_deliverynote . '*' ) as $file ) {
				if ( time() - filectime( $file ) > $convert_second ) {
					unlink( $file );
				}
			}
		}

		/**
		 * Load the main plugin classes and functions.
		 */
		public function load() {
			// WooCommerce activation required.
			if ( $this->is_woocommerce_activated() ) {
				// Include the classes.
				$this->include_classes();

				// Create the instances.
				$this->print      = new WCDN_Print();
				$this->settings   = new WCDN_Settings();
				$this->writepanel = new WCDN_Writepanel();
				$this->theme      = new WCDN_Theme();

				// Load the hooks for the template after the objetcs.
				// Like this the template has full access to all objects.
				add_filter( 'plugin_action_links_' . self::$plugin_basefile, array( $this, 'add_settings_link' ) );
				add_action( 'admin_init', array( $this, 'update' ) );
				add_action( 'init', array( $this, 'include_template_functions' ) );

				add_filter( 'ts_deativate_plugin_questions', array( &$this, 'wcdn_deactivate_add_questions' ), 10, 1 );
				add_filter( 'ts_tracker_data', array( &$this, 'wcdn_ts_add_plugin_tracking_data' ), 10, 1 );
				add_filter( 'ts_tracker_opt_out_data', array( &$this, 'wcdn_get_data_for_opt_out' ), 10, 1 );

				// Send out the init action.
				do_action( 'wcdn_init' );
			}
		}

		/**
		 * Plugin's data to be tracked when Allow option is choosed.
		 *
		 * @hook ts_tracker_data
		 *
		 * @param array $data Contains the data to be tracked.
		 *
		 * @return array Plugin's data to track.
		 */
		public static function wcdn_ts_add_plugin_tracking_data( $data ) {

			$wcdn_tracker_nonce = isset( $_GET['wcdn_tracker_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['wcdn_tracker_nonce'] ) ) : '';

			if ( isset( $_GET['wcdn_tracker_optin'] ) && isset( $_GET['wcdn_tracker_nonce'] ) && wp_verify_nonce( $wcdn_tracker_nonce, 'wcdn_tracker_optin' ) ) {

				$plugin_data['ts_meta_data_table_name'] = 'ts_tracking_wcdn_meta_data';
				$plugin_data['ts_plugin_name']          = 'WooCommerce Print Invoice & Delivery Note';

				// Get all plugin options info.
				$plugin_data['invoice_in_admin']    = get_option( 'wcdn_template_type_invoice' );
				$plugin_data['delivery_in_admin']   = get_option( 'wcdn_template_type_delivery-note' );
				$plugin_data['receipt_in_admin']    = get_option( 'wcdn_template_type_receipt' );
				$plugin_data['print_in_myaccount']  = get_option( 'wcdn_print_button_on_my_account_page' );
				$plugin_data['print_in_vieworder']  = get_option( 'wcdn_print_button_on_my_account_page' );
				$plugin_data['print_in_email']      = get_option( 'wcdn_print_button_on_my_account_page' );
				$plugin_data['wcdn_plugin_version'] = self::$plugin_version;
				$plugin_data['wcdn_allow_tracking'] = get_option( 'wcdn_allow_tracking' );
				$data['plugin_data']                = $plugin_data;
			}
			return $data;
		}


		/**
		 * Tracking data to send when No, thanks. button is clicked.
		 *
		 * @hook ts_tracker_opt_out_data
		 *
		 * @param array $params Parameters to pass for tracking data.
		 *
		 * @return array Data to track when opted out.
		 */
		public static function wcdn_get_data_for_opt_out( $params ) {
			$plugin_data['ts_meta_data_table_name'] = 'ts_tracking_wcdn_meta_data';
			$plugin_data['ts_plugin_name']          = 'WooCommerce Print Invoice & Delivery Note';

			// Store count info.
			$params['plugin_data'] = $plugin_data;

			return $params;
		}

		/**
		 * It will add the question for the deactivate popup modal.
		 *
		 * @param array $dfw_deactivate_questions Array of all questions.
		 *
		 * @return array $dfw_deactivate_questions All questions.
		 */
		public static function wcdn_deactivate_add_questions( $dfw_deactivate_questions ) {

			$dfw_deactivate_questions = array(
				0 => array(
					'id'                => 4,
					'text'              => __( "I can't differentiate between Invoice, Delivery Notes & Receipt. The templates are the same. ", 'woocommerce-delivery-notes' ),
					'input_type'        => '',
					'input_placeholder' => '',
				),
				1 => array(
					'id'                => 5,
					'text'              => __( "The invoice sent through mail can't be downloaded as PDF directly.", 'woocommerce-delivery-notes' ),
					'input_type'        => '',
					'input_placeholder' => '',
				),
				2 => array(
					'id'                => 6,
					'text'              => __( 'The plugin is not compatible with another plugin.', 'woocommerce-delivery-notes' ),
					'input_type'        => 'textfield',
					'input_placeholder' => 'Which plugin?',
				),
				3 => array(
					'id'                => 7,
					'text'              => __( 'This plugin is not useful to me.', 'woocommerce-delivery-notes' ),
					'input_type'        => '',
					'input_placeholder' => '',
				),

			);
			return $dfw_deactivate_questions;
		}
		/**
		 * Install or update the default settings.
		 */
		public function update() {
			$option_version = get_option( 'wcdn_version', '1' );
			if ( isset( $_FILES['shop_logo'] ) && ! empty( $_FILES['shop_logo'] ) ) {
				$upload = wp_handle_upload(
					$_FILES['shop_logo'], // phpcs:ignore
					array( 'test_form' => false )
				);
				if ( ! isset( $upload['error'] ) ) {
					$attachment_id = wp_insert_attachment(
						array(
							'guid'           => $upload['url'],
							'post_mime_type' => $upload['type'],
							'post_title'     => basename( $upload['file'] ),
							'post_content'   => '',
							'post_status'    => 'inherit',
						),
						$upload['file']
					);

					wp_update_attachment_metadata(
						$attachment_id,
						wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
					);
					update_option( 'wcdn_company_logo_image_id', $attachment_id );
					$_POST['wcdn_general']['shop_logo'] = $attachment_id;
				}
			}
			if ( isset( $_POST['wcdn_general'] ) && ! empty( $_POST['wcdn_general'] ) ) {
				update_option( 'wcdn_print_order_page_endpoint', $_POST['wcdn_general']['page_endpoint'] );
				update_option( 'wcdn_footer_imprint', $_POST['wcdn_general']['shop_footer'] );
				update_option( 'wcdn_policies_conditions', $_POST['wcdn_general']['shop_policy'] );
				update_option( 'wcdn_personal_notes', $_POST['wcdn_general']['shop_complimentry_close'] );
				update_option( 'wcdn_company_address', $_POST['wcdn_general']['shop_address'] );
				update_option( 'wcdn_custom_company_name', $_POST['wcdn_general']['shop_name'] );
				update_option( 'wcdn_general_settings', $_POST['wcdn_general'] );
				if ( isset( $_POST['wcdn_general']['view_account'] ) ) {
					update_option( 'wcdn_print_button_on_my_account_page', 'yes' );
				} else {
					update_option( 'wcdn_print_button_on_my_account_page', 'no' );
				}
				if ( isset( $_POST['wcdn_general']['view_order'] ) ) {
					update_option( 'wcdn_print_button_on_view_order_page', 'yes' );
				} else {
					update_option( 'wcdn_print_button_on_view_order_page', 'no' );
				}
				if ( isset( $_POST['wcdn_general']['print_customer'] ) ) {
					update_option( 'wcdn_email_print_link', 'yes' );
				} else {
					update_option( 'wcdn_email_print_link', 'no' );
				}
				if ( isset( $_POST['wcdn_general']['print_admin'] ) ) {
					update_option( 'wcdn_admin_email_print_link', 'yes' );
				} else {
					update_option( 'wcdn_admin_email_print_link', 'no' );
				}

				$rtltext = ( isset( $_POST['wcdn_general']['page_textdirection'] ) ) ? 'yes' : 'no';
				update_option( 'wcdn_rtl_invoice', $rtltext );
			}
			if ( isset( $_POST['wcdn_document'] ) && ! empty( $_POST['wcdn_document'] ) ) {
				update_option( 'wcdn_document_settings', $_POST['wcdn_document'] );
				if ( in_array( 'invoice', $_POST['wcdn_document'] ) ) {
					update_option( 'wcdn_template_type_invoice', 'yes' );
				} else {
					update_option( 'wcdn_template_type_invoice', 'no' );
				}
				if ( in_array( 'receipt', $_POST['wcdn_document'] ) ) {
					update_option( 'wcdn_template_type_receipt', 'yes' );
				} else {
					update_option( 'wcdn_template_type_receipt', 'no' );
				}
				if ( in_array( 'delivery_note', $_POST['wcdn_document'] ) ) {
					update_option( 'wcdn_template_type_delivery-note', 'yes' );
				} else {
					update_option( 'wcdn_template_type_delivery-note', 'no' );
				}
			}
			if ( isset( $_POST['wcdn_invoice'] )  && ! empty( $_POST['wcdn_invoice'] ) ) {
				update_option( 'wcdn_invoice_settings', $_POST['wcdn_invoice'] );
				update_option( 'wcdn_invoice_number_suffix', $_POST['wcdn_invoice']['invoice_suffix'] );
				update_option( 'wcdn_invoice_number_prefix', $_POST['wcdn_invoice']['invoice_preffix'] );
				$number = ( isset( $_POST['wcdn_invoice']['numbering'] ) ) ? 'yes' : 'no';
				update_option( 'wcdn_create_invoice_number', $number );
				update_option( 'wcdn_invoice_number_count', $_POST['wcdn_invoice']['invoice_nextnumber'] );
			}
			if ( isset( $_POST['wcdn_receipt'] )  && ! empty( $_POST['wcdn_receipt'] ) ) {
				update_option( 'wcdn_receipt_settings', $_POST['wcdn_receipt'] );
			}
			if ( isset( $_POST['wcdn_deliverynote'] )  && ! empty( $_POST['wcdn_deliverynote'] ) ) {
				update_option( 'wcdn_deliverynote_settings', $_POST['wcdn_deliverynote'] );
			}

			if ( isset( $_POST['invoice'] )  && ! empty( $_POST['invoice'] ) ) {
				update_option( 'wcdn_invoice_customization', $_POST['invoice'] );
			}
			if ( isset( $_POST['receipt'] )  && ! empty( $_POST['receipt'] ) ) {
				update_option( 'wcdn_receipt_customization', $_POST['receipt'] );
			}
			if ( isset( $_POST['deliverynote'] )  && ! empty( $_POST['deliverynote'] ) ) {
				update_option( 'wcdn_deliverynote_customization', $_POST['deliverynote'] );
			}

			// Update the settings.
			if ( version_compare( $option_version, self::$plugin_version, '<' ) ) {
				// Legacy updates.
				if ( version_compare( $option_version, '5.0.0', '<' ) ) {
					// Group invoice numbering.
					$invoice_start   = intval( get_option( 'wcdn_invoice_number_start', 1 ) );
					$invoice_counter = intval( get_option( 'wcdn_invoice_number_counter', 0 ) );
					update_option( 'wcdn_invoice_number_count', $invoice_start + $invoice_counter );
					update_option( 'wcdn_invoice_template_type', 'default' );
					update_option( 'wcdn_receipt_template_type', 'default' );
					update_option( 'wcdn_delivery_note_template_type', 'default' );
				}

				// Flush the transients in case the endpoint changed.
				set_transient( 'wcdn_flush_rewrite_rules', true );

				// Update the settings to the latest version.
				update_option( 'wcdn_version', self::$plugin_version );
			}
		}

		/**
		 * Add settings link to plugin page.
		 *
		 * @param array $links Links on Plugins page.
		 */
		public function add_settings_link( $links ) {
			$url      = esc_url(
				admin_url(
					add_query_arg(
						array(
							'page' => 'wc-settings',
							'tab'  => $this->settings->id,
						),
						'admin.php'
					)
				)
			);
			$settings = sprintf( '<a href="%s" title="%s">%s</a>', $url, __( 'Go to the settings page', 'woocommerce-delivery-notes' ), __( 'Settings', 'woocommerce-delivery-notes' ) );
			array_unshift( $links, $settings );
			return $links;
		}

		/**
		 * Check if woocommerce is activated.
		 */
		public function is_woocommerce_activated() {
			$blog_plugins         = get_option( 'active_plugins', array() );
			$site_plugins         = get_site_option( 'active_sitewide_plugins', array() );
			$woocommerce_basename = plugin_basename( WC_PLUGIN_FILE );

			if ( ( in_array( $woocommerce_basename, $blog_plugins, true ) || isset( $site_plugins[ $woocommerce_basename ] ) ) && version_compare( WC_VERSION, '2.2', '>=' ) ) {
				return true;
			} else {
				return false;
			}
		}

	}
}

/**
 * Returns the main instance of the plugin to prevent the need to use globals.
 */
function wcdn_init() {
	return WC_Delivery_Notes::instance();
}

