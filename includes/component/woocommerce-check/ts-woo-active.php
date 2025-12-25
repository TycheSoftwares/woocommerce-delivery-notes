<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable
/**
 * WCDN_TS_Woo_Active Class
 *
 * @class WCDN_TS_Woo_Active
 */

class WCDN_TS_Woo_Active {

	/**
	 * @var string The name of the plugin
	 * @access public
	 */
	public $plugin_name = '';

	/**
	 * Store the plugin name.
	 * @var string Path of the plugin name.
	 * @access public
	 */
	public $plugin_file = '';

	/**
	 * Store the plguin locale.
	 * @var string Used Plugin locale.
	 * @access public
	 */
	public $ts_locale = '';

	public function __construct( $ts_plugin_name = '' , $ts_file_name = '', $ts_locale = '' ) {

		$this->plugin_name = $ts_plugin_name;
		$this->plugin_file = $ts_file_name;
		$this->ts_locale   = $ts_locale;
		
		//Check for WooCommerce plugin
		if ( '' != $this->plugin_file ) {
			add_action( 'admin_init', array( &$this, 'ts_check_if_woocommerce_active' ) );
		}
	}

	/**
	 * Checks if the WooCommerce plugin is active or not. If it is not active then it will display a notice.
	 */
	public function ts_check_if_woocommerce_active() {
		if ( ! $this->ts_check_woo_installed() ) {
		    if ( is_plugin_active(  $this->plugin_file ) ) {
		        deactivate_plugins(  $this->plugin_file );
		        add_action( 'admin_notices', array( &$this, 'ts_disabled_notice' ) );
		        if ( isset( $_GET[ 'activate' ] ) ) {
		            unset( $_GET[ 'activate' ] );
		        }
		    }
		}
	}

	/**
	 * Check if WooCommerce is active.
	 */
	public function ts_check_woo_installed() {
	    if ( class_exists( 'WooCommerce' ) ) {
	        return true;
	    } else {
	        return false;
	    }
	}

	/**
	 * Display a notice in the admin plugins page if the plugin is activated while WooCommerce is deactivated.
	 */
	public function ts_disabled_notice() {
		$class = 'notice notice-error';
		$message = __( $this->plugin_name . ' plugin requires WooCommerce installed and active.', $this->ts_locale );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}
}