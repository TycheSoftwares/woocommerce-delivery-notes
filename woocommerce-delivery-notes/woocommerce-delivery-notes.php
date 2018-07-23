<?php
/**
 * Print invoices & delivery notes for WooCommerce orders.
 *
 * Plugin Name: WooCommerce Print Invoice & Delivery Note
 * Plugin URI: https://www.tychesoftwares.com/
 * Description: Print Invoices & Delivery Notes for WooCommerce Orders. 
 * Version: 4.4.3
 * Author: Tyche Softwares
 * Author URI: https://www.tychesoftwares.com/
 * License: GPLv3 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: woocommerce-delivery-notes
 * Domain Path: /languages
 * Requires PHP: 5.6
 * WC requires at least: 3.0.0
 * WC tested up to: 3.3.0
 *
 * Copyright 2015 Tyche Softwares
 *		
 *     This file is part of WooCommerce Print Invoices & Delivery Notes,
 *     a plugin for WordPress.
 *
 *     WooCommerce Print Invoice & Delivery Note is free software:
 *     You can redistribute it and/or modify it under the terms of the
 *     GNU General Public License as published by the Free Software
 *     Foundation, either version 2 of the License, or (at your option)
 *     any later version.
 *
 *     WooCommerce Print Invoice & Delivery Note is distributed in the hope that
 *     it will be useful, but WITHOUT ANY WARRANTY; without even the
 *     implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 *     PURPOSE. See the GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with WordPress. If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * Exit if accessed directly
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; 
}

/**
 * Base class
 */
if ( !class_exists( 'WooCommerce_Delivery_Notes' ) ) {
	/**
	 * WooCommerce Delivery Notes
	 * 
	 * @author Tyche Softwares
	 * @package WooCommerce-Delivery-Notes
	 */
	final class WooCommerce_Delivery_Notes {
 
		/**
		 * The single instance of the class
		 */
		protected static $_instance = null;
	
		/**
		 * Default properties
		 */
		public static $plugin_version = '4.4.3';
		public static $plugin_url;
		public static $plugin_path;
		public static $plugin_basefile;
		public static $plugin_basefile_path;
		public static $plugin_text_domain;
		
		/**
		 * Sub class instances
		 */
		public $writepanel;
		public $settings;
		public $print;
		public $theme;

		/**
		 * Main Instance
		 */
		public static function instance() {
			if( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
	
		/**
		 * Cloning is forbidden
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-delivery-notes' ), '4.1' );
		}
	
		/**
		 * Unserializing instances of this class is forbidden
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-delivery-notes' ), '4.1' );
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->define_constants();
			$this->init_hooks();
			
			// Send out the load action
			do_action( 'wcdn_load');
		}

		/**
		 * Hook into actions and filters
		 */
		public function init_hooks() {
			add_action( 'init', array( $this, 'localise' ) );
			add_action( 'woocommerce_init', array( $this, 'load' ) );
			if ( true === is_admin() ) {
			    include_once( 'includes/wcdn-all-component.php' );
			}
		}

		/**
		 * Define WC Constants
		 */
		private function define_constants() {
			self::$plugin_basefile_path = __FILE__;
			self::$plugin_basefile = plugin_basename( self::$plugin_basefile_path );
			self::$plugin_url = plugin_dir_url( self::$plugin_basefile );
			self::$plugin_path = trailingslashit( dirname( self::$plugin_basefile_path ) );	
			self::$plugin_text_domain = trim( dirname( self::$plugin_basefile ) );		
		}
		
		/**
		 * Define constant if not already set
		 */
		private function define( $name, $value ) {

			if( !defined( $name ) ) {
				define( $name, $value );
			}
		}
		
		/**
		 * Include the main plugin classes and functions
		 */
		public function include_classes() {
			include_once( 'includes/class-wcdn-print.php' );
			include_once( 'includes/class-wcdn-settings.php' );
			include_once( 'includes/class-wcdn-writepanel.php' );
			include_once( 'includes/class-wcdn-theme.php' );
			
		}

		/**
		 * Function used to init Template Functions.
		 * This makes them pluggable by plugins and themes.
		 */
		public function include_template_functions() {
			include_once( 'includes/wcdn-template-functions.php' );
			include_once( 'includes/wcdn-template-hooks.php' );
		}
		
		/**
		 * Load the localisation 
		 */
		public function localise() {							
			// Load language files from the wp-content/languages/plugins folder
			$mo_file = WP_LANG_DIR . '/plugins/' . self::$plugin_text_domain . '-' . get_locale() . '.mo';
			if( is_readable( $mo_file ) ) {
				load_textdomain( self::$plugin_text_domain, $mo_file );
			}

			// Otherwise load them from the plugin folder
			load_plugin_textdomain( self::$plugin_text_domain, false, dirname( self::$plugin_basefile ) . '/languages/' );
		}
		
		/**
		 * Load the main plugin classes and functions
		 */
		public function load() {
			// WooCommerce activation required
			if ( $this->is_woocommerce_activated() ) {	
				// Include the classes	
				$this->include_classes();
							
				// Create the instances
				$this->print = new WooCommerce_Delivery_Notes_Print();
				$this->settings = new WooCommerce_Delivery_Notes_Settings();
				$this->writepanel = new WooCommerce_Delivery_Notes_Writepanel();
				$this->theme = new WooCommerce_Delivery_Notes_Theme();

				// Load the hooks for the template after the objetcs.
				// Like this the template has full access to all objects.
				add_filter( 'plugin_action_links_' . self::$plugin_basefile, array( $this, 'add_settings_link') );
				add_action( 'admin_init', array( $this, 'update' ) );
				add_action( 'init', array( $this, 'include_template_functions' ) );

				add_filter( 'ts_deativate_plugin_questions', array( &$this, 'wcdn_deactivate_add_questions' ), 10, 1 );
                add_filter( 'ts_tracker_data',               array( &$this, 'wcdn_ts_add_plugin_tracking_data' ), 10, 1 );
				add_filter( 'ts_tracker_opt_out_data',       array( &$this, 'wcdn_get_data_for_opt_out' ), 10, 1 );
                
				
				// Send out the init action
				do_action( 'wcdn_init');
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
         * 
         */

        public static function wcdn_ts_add_plugin_tracking_data ( $data ) {
            if ( isset( $_GET[ 'wcdn_tracker_optin' ] ) && isset( $_GET[ 'wcdn_tracker_nonce' ] ) && wp_verify_nonce( $_GET[ 'wcdn_tracker_nonce' ], 'wcdn_tracker_optin' ) ) {

                $plugin_data[ 'ts_meta_data_table_name' ] = 'ts_tracking_wcdn_meta_data';
                $plugin_data[ 'ts_plugin_name' ]		  = 'WooCommerce Print Invoice & Delivery Note';
                
                // Get all plugin options info
                $plugin_data[ 'invoice_in_admin' ]        = get_option( 'wcdn_template_type_invoice' );
                $plugin_data[ 'delivery_in_admin' ]       = get_option( 'wcdn_template_type_delivery-note' );
				$plugin_data[ 'receipt_in_admin' ]        = get_option('wcdn_template_type_receipt');
				$plugin_data[ 'print_in_myaccount' ]      = get_option('wcdn_print_button_on_my_account_page');
				$plugin_data[ 'print_in_vieworder' ]      = get_option('wcdn_print_button_on_my_account_page');
				$plugin_data[ 'print_in_email' ]          = get_option('wcdn_print_button_on_my_account_page');
                $plugin_data[ 'wcdn_plugin_version' ]     = self::$plugin_version;
                $plugin_data[ 'wcdn_allow_tracking' ]     = get_option ( 'wcdn_allow_tracking' );
                $data[ 'plugin_data' ]                    = $plugin_data;
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
         * 
         */
        public static function wcdn_get_data_for_opt_out ( $params ) {
            $plugin_data[ 'ts_meta_data_table_name']   = 'ts_tracking_wcdn_meta_data';
            $plugin_data[ 'ts_plugin_name' ]		   = 'WooCommerce Print Invoice & Delivery Note';
            
            // Store count info
            $params[ 'plugin_data' ]  				   = $plugin_data;
            
            return $params;
        }
		
		/**
		 * It will add the question for the deactivate popup modal
         * @return array $dfw_deactivate_questions All questions.
		 */
		public static function wcdn_deactivate_add_questions ( $dfw_deactivate_questions ) {

			$dfw_deactivate_questions = array(
                0 => array(
                    'id'                => 4,
                    'text'              => __( "I can't differentiate between Invoice, Delivery Notes & Receipt. The templates are the same. ", "woocommerce-delivery-notes" ),
                    'input_type'        => '',
                    'input_placeholder' => ''
                    ), 
                1 =>  array(
                    'id'                => 5,
                    'text'              => __( "The invoice sent through mail can't be downloaded as PDF directly.", "woocommerce-delivery-notes" ),
                    'input_type'        => '',
                    'input_placeholder' => ''
                ),
                2 => array(
                    'id'                => 6,
                    'text'              => __( "The plugin is not compatible with another plugin.", "woocommerce-delivery-notes" ),
                    'input_type'        => 'textfield',
                    'input_placeholder' => 'Which plugin?'
                ),
                3 => array(
                    'id'                => 7,
                    'text'              => __( "This plugin is not useful to me.", "woocommerce-delivery-notes" ),
                    'input_type'        => '',
                    'input_placeholder' => ''
                )

            );
			return $dfw_deactivate_questions;
		}
		/**
		 * Install or update the default settings
		 */
		public function update() {
			$option_version = get_option( 'wcdn_version', '1' );

			// Update the settings
			if( version_compare( $option_version, self::$plugin_version, '<' ) ) {
				// Legacy updates
				if( version_compare( $option_version, '4.2.0', '<' ) ) {
					// Group invoice numbering
					$invoice_start = intval( get_option( 'wcdn_invoice_number_start', 1 ) );
					$invoice_counter = intval( get_option( 'wcdn_invoice_number_counter', 0 ) );
					update_option( 'wcdn_invoice_number_count', $invoice_start + $invoice_counter );	
					
					// Translate checkbox values
					foreach( $this->settings->get_settings() as $value ) {
						if( isset( $value['id'] ) && isset( $value['type'] ) && $value['type'] == 'checkbox' ) {
							$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
							$option = get_option( $value['id'] );
							if( (bool)$option ) {
								update_option( $value['id'], 'yes' );	
							} else {
								update_option( $value['id'], 'no' );	
							}
						}
					}				
				}
				
				// Set all options that have default values
				foreach( $this->settings->get_settings() as $value ) {
					if( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
				
				// Flush the transients in case the endpoint changed
				set_transient( 'wcdn_flush_rewrite_rules', true );

				// Update the settings to the latest version
				update_option( 'wcdn_version', self::$plugin_version );
			}
		}
		
		/**
		 * Add settings link to plugin page
		 */
		public function add_settings_link( $links ) {
			$url = esc_url( admin_url( add_query_arg( array( 'page' => 'wc-settings', 'tab' => $this->settings->id ), 'admin.php' ) ) );
			$settings = sprintf( '<a href="%s" title="%s">%s</a>' , $url, __( 'Go to the settings page', 'woocommerce-delivery-notes' ) , __( 'Settings', 'woocommerce-delivery-notes' ) );
			array_unshift( $links, $settings );
			return $links;	
		}
				
		/**
		 * Check if woocommerce is activated
		 */
		public function is_woocommerce_activated() {
			$blog_plugins = get_option( 'active_plugins', array() );
			$site_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$woocommerce_basename = plugin_basename( WC_PLUGIN_FILE );
					
			if( ( in_array( $woocommerce_basename, $blog_plugins ) || isset( $site_plugins[$woocommerce_basename] ) ) && version_compare( WC_VERSION, '2.2', '>=' ) ) {
				return true;
			} else {
				return false;
			}
		}
		
	}
}

/**
 * Returns the main instance of the plugin to prevent the need to use globals
 */
function WCDN() {
	return WooCommerce_Delivery_Notes::instance();
}

/**
 * Global for backwards compatibility
 */
$GLOBALS['wcdn'] = WCDN();

?>