<?php
/**
 * The tracker class adds functionality to track usage of the plugin based on if the customer opted in.
 * No personal information is tracked, only general settings, order and user counts and admin email for 
 * discount code.
 *
 * @class 		WCDN_TS_Tracker
 * @version		6.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCDN_TS_Tracker {

	/**
	 * URL to the  Tracker API endpoint.
	 * @var string
	 */

	private static $api_url = 'http://tracking.tychesoftwares.com/v1/';

	/**
	* @var string Plugin prefix
	* @access public 
	*/

	public static $plugin_prefix = '';

	/**
	* @var string Plugin name
	* @access public 
	*/

	public static $plugin_name = '';

	/**
	 * Hook into cron event.
	 */
	public function __construct( $ts_plugin_prefix = '', $ts_plugin_name = '' ) {

		self::$plugin_prefix = $ts_plugin_prefix;
		self::$plugin_name   = $ts_plugin_name;

		add_action( self::$plugin_prefix . '_ts_tracker_send_event',   array( __CLASS__, 'ts_send_tracking_data' ) );
	}

	/**
	 * Decide whether to send tracking data or not.
	 *
	 * @param boolean $override
	 */
	public static function ts_send_tracking_data( $override = false ) {
		if ( ! apply_filters( 'ts_tracker_send_override', $override ) ) {
			// Send a maximum of once per week by default.
			$last_send = self::ts_get_last_send_time();
			if ( $last_send && $last_send > apply_filters( 'ts_tracker_last_send_interval', strtotime( '-1 week' ) ) ) {
				return;
			}
		} else {
			// Make sure there is at least a 1 hour delay between override sends, we don't want duplicate calls due to double clicking links.
			$last_send = self::ts_get_last_send_time();
			if ( $last_send && $last_send > strtotime( '-1 hours' ) ) {
				return;
			}
		}
        
		$allow_tracking =  get_option( self::$plugin_prefix . '_allow_tracking' );
		if ( 'yes' == $allow_tracking ) {
		    $override = true;
		}
		
		// Update time first before sending to ensure it is set
		update_option( 'wcdn_ts_tracker_last_send', time() );

		if( $override == false ) {
			$params   = array();
			$params[ 'tracking_usage' ] = 'no';
			$params[ 'url' ]            = home_url();
			$params[ 'email' ]          = '';
			
			$params 					= apply_filters( 'ts_tracker_opt_out_data', $params );
		} else {
			$params   = self::ts_get_tracking_data();
		}
		
		wp_safe_remote_post( self::$api_url, array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => false,
				'headers'     => array( 'user-agent' => 'TSTracker/' . md5( esc_url( home_url( '/' ) ) ) . ';' ),
				'body'        => json_encode( $params ),
				'cookies'     => array(),
			)
		);	
	}

	/**
	 * Get the last time tracking data was sent.
	 * @return int|bool
	 */
	private static function ts_get_last_send_time() {
		return apply_filters( 'ts_tracker_last_send_time', get_option( 'wcdn_ts_tracker_last_send', false ) );
	}

	/**
	 * Get all the tracking data.
	 * @return array
	 */
	private static function ts_get_tracking_data() {
		$data                        = array();

		// General site info
		$data[ 'url' ]               = home_url();
		$data[ 'email' ]             = apply_filters( 'ts_tracker_admin_email', get_option( 'admin_email' ) );

		// WordPress Info
		$data[ 'wp' ]                = self::ts_get_wordpress_info();

		$data[ 'theme_info' ]        = self::ts_get_theme_info();

		// Server Info
		$data[ 'server' ]            = self::ts_get_server_info();

		// Plugin info
		$all_plugins                 = self::ts_get_all_plugins();
		$data[ 'active_plugins' ]    = $all_plugins[ 'active_plugins' ];
		$data[ 'inactive_plugins' ]  = $all_plugins[ 'inactive_plugins' ];

		//WooCommerce version 
		$data[ 'wc_plugin_version' ] = self::ts_get_wc_plugin_version();


				
		return apply_filters( 'ts_tracker_data', $data );
	}

	/**
	 * Get Selected city of the WooCommerce store.
	 * @return string $ts_city Name of the city
	 */
	private static function ts_get_wc_city () {
		$ts_city = get_option ( 'woocommerce_store_city' ); 
		return $ts_city;
	}

	/**
	 * Get Selected country of the WooCommerce store.
	 * @return string $ts_country Name of the city
	 */
	private static function ts_get_wc_country () {
		$ts_country = get_option ( 'woocommerce_default_country' ); 
		return $ts_country;
	}
    
	/**
	 * Get WordPress related data.
	 * @return array
	 */
	private static function ts_get_wordpress_info() {
		$wp_data = array();

		$memory = wc_let_to_num( WP_MEMORY_LIMIT );

		if ( function_exists( 'memory_get_usage' ) ) {
			$system_memory = wc_let_to_num( @ini_get( 'memory_limit' ) );
			$memory        = max( $memory, $system_memory );
		}

		$wp_data[ 'memory_limit' ] = size_format( $memory );
		$wp_data[ 'debug_mode' ]   = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No';
		$wp_data[ 'locale' ]       = get_locale();
		$wp_data[ 'wp_version' ]   = get_bloginfo( 'version' );
		$wp_data[ 'multisite' ]    = is_multisite() ? 'Yes' : 'No';
		$wp_data[ 'blogdescription' ] = get_option ( 'blogdescription' );
		$wp_data[ 'blogname' ] = get_option ( 'blogname' );
		$wp_data[ 'wc_city' ] 	 = self::ts_get_wc_city();
		$wp_data[ 'wc_country' ] = self::ts_get_wc_country();

		return $wp_data;
	}

	/**
	 * Get the current theme info, theme name and version.
	 * @return array
	 */
	public static function ts_get_theme_info() {
		$theme_data        = wp_get_theme();
		$theme_child_theme = is_child_theme() ? 'Yes' : 'No';

		return array( 'theme_name' => $theme_data->Name, 
					'theme_version' => $theme_data->Version, 
					'child_theme' => $theme_child_theme );
	}

	/**
	 * Get server related info.
	 * @return array
	 */
	private static function ts_get_server_info() {
		global $wpdb;
		$server_data = array();

		if ( isset( $_SERVER[ 'SERVER_SOFTWARE' ] ) && ! empty( $_SERVER[ 'SERVER_SOFTWARE' ] ) ) {
			$server_data[ 'software' ] = $_SERVER[ 'SERVER_SOFTWARE' ];
		}

		if ( function_exists( 'phpversion' ) ) {
			$server_data[ 'php_version' ] = phpversion();
		}

		if ( function_exists( 'ini_get' ) ) {
			$server_data[ 'php_post_max_size' ] = size_format( wc_let_to_num( ini_get( 'post_max_size' ) ) );
			$server_data[ 'php_time_limt' ] = ini_get( 'max_execution_time' );
			$server_data[ 'php_max_input_vars' ] = ini_get( 'max_input_vars' );
			$server_data[ 'php_suhosin' ] = extension_loaded( 'suhosin' ) ? 'Yes' : 'No';
		}

		$server_data[ 'mysql_version' ] = $wpdb->db_version();

		$server_data[ 'php_max_upload_size' ] = size_format( wp_max_upload_size() );
		$server_data[ 'php_default_timezone' ] = date_default_timezone_get();
		$server_data[ 'php_soap' ] = class_exists( 'SoapClient' ) ? 'Yes' : 'No';
		$server_data[ 'php_fsockopen' ] = function_exists( 'fsockopen' ) ? 'Yes' : 'No';
		$server_data[ 'php_curl' ] = function_exists( 'curl_init' ) ? 'Yes' : 'No';

		return $server_data;
	}

	/**
	 * Get all plugins grouped into activated or not.
	 * @return array
	 */
	private static function ts_get_all_plugins() {
		// Ensure get_plugins function is loaded
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins        	 = get_plugins();
		$active_plugins_keys = get_option( 'active_plugins', array() );
		$active_plugins 	 = array();

		foreach ( $plugins as $k => $v ) {
			// Take care of formatting the data how we want it.
			$formatted = array();
			$formatted[ 'name' ] = strip_tags( $v[ 'Name' ] );
			if ( isset( $v[ 'Version' ] ) ) {
				$formatted[ 'version' ] = strip_tags( $v[ 'Version' ] );
			}
			if ( isset( $v[ 'Author' ] ) ) {
				$formatted[ 'author' ] = strip_tags( $v[ 'Author' ] );
			}
			if ( isset( $v[ 'Network' ] ) ) {
				$formatted[ 'network' ] = strip_tags( $v[ 'Network' ] );
			}
			if ( isset( $v[ 'PluginURI' ] ) ) {
				$formatted[ 'plugin_uri' ] = strip_tags( $v[ 'PluginURI' ] );
			}
			if ( in_array( $k, $active_plugins_keys ) ) {
				// Remove active plugins from list so we can show active and inactive separately
				unset( $plugins[ $k ] );
				$active_plugins[ $k ] = $formatted;
			} else {
				$plugins[ $k ] = $formatted;
			}
		}

		return array( 'active_plugins' => $active_plugins, 'inactive_plugins' => $plugins );
	}
	
	/**
	 * Sends current WooCommerce version
	 * @return string
	 */
	private static function ts_get_wc_plugin_version() {
		return WC()->version;
	}
}