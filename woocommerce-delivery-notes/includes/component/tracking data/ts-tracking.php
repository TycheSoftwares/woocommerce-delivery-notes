<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once( 'class-ts-tracker.php' );

/** Adds the Tracking non-senstive data notice
 *
 * @since 6.8
 */
class TS_tracking {

	/**
	 * @var string Plugin prefix
	 * @access public 
	 */

	public static $plugin_prefix = '';

	/** 
	 * @var string Plugin Name
	 * @access public
	 */

	public static $plugin_name = '';

	/**
	 * @var string Tracking data blog post link
	 * @access public
	 */

	public static $blog_post_link = '';

	/**
	 * @var string Plugin context
	 * @access public
	 */

	public static $plugin_context = '';

	/**
	 * @var string Plugin url
	 * @access public
	 */
	public static $plugin_url = '';

	/**
	 * @var string File path
	 * @access public
	 */
	public static $ts_file_path = '' ;
	/**
	 * @var string plugin locale
	 * @access public
	 */
	public static $ts_plugin_locale = '';
	/** Default Constructor 
	 *
	 * @since 6.8
	 */
	public function __construct( $ts_plugin_prefix = '', $ts_plugin_name = '', $ts_blog_post_link = '', $ts_plugin_context = '', $ts_plugin_url = '' ) {

		self::$plugin_prefix    = $ts_plugin_prefix;
		self::$plugin_name      = $ts_plugin_name;
		self::$blog_post_link   = $ts_blog_post_link;
		self::$plugin_url       = $ts_plugin_url;
		self::$ts_plugin_locale = $ts_plugin_context;
		self::$ts_file_path     = untrailingslashit( plugins_url( '/', __FILE__) ) ;
		//Tracking Data
		add_action( 'admin_notices', array( 'TS_tracking', 'ts_track_usage_data' ) );
		add_action( 'admin_footer',  array( 'TS_tracking', 'ts_admin_notices_scripts' ) );
		add_action( 'wp_ajax_'.self::$plugin_prefix.'_admin_notices', array( 'TS_tracking', 'ts_admin_notices' ) );

		//wp_clear_scheduled_hook( 'wcap_ts_tracker_send_event' );
		add_filter( 'cron_schedules', array( 'TS_tracking', 'ts_add_cron_schedule' ) );

		self::ts_schedule_cron_job();
	}

	/**
	 * It will add a cron job for sending the tarcking data.
	 * By default it will set once in a week interval.
	 * @hook cron_schedules	
	 * @param array $schedules
	 * @return array $schedules
	 */
	public static function ts_add_cron_schedule( $schedules ) {
		$schedules[ 'once_in_week' ] = array(
			'interval' => 604800,  // one week in seconds
			'display'  => __( 'Once in a Week', self::$ts_plugin_locale )
		);
		
		return $schedules;
	}

	/**
	 * To capture the data from the client site.
	 */
	public static function ts_schedule_cron_job () {
		if ( ! wp_next_scheduled( self::$plugin_prefix . '_ts_tracker_send_event' ) ) {
			wp_schedule_event( time(), 'once_in_week', self::$plugin_prefix . '_ts_tracker_send_event' );
		}
	}

	/**
	 * Load the js file in the admin
	 *
	 * @since 6.8
	 * @access public
	 */
	public static function ts_admin_notices_scripts() {
		
        wp_enqueue_script(
            'ts_dismiss_notice',
			self::$ts_file_path . '/assets/js/dismiss-notice.js',
            '',
            '',
            false
		);

		wp_localize_script( 'ts_dismiss_notice', 'ts_dismiss_notice', array (
			'ts_prefix_of_plugin' =>  self::$plugin_prefix,
			'ts_admin_url'        => admin_url( 'admin-ajax.php' )
		) );
	}

    /**
	 * Called when the dismiss icon is clicked on the notice. 
	 *
	 * @since 6.8
	 * @access public
	 */

    public static function ts_admin_notices() {
        update_option( self::$plugin_prefix . '_allow_tracking', 'dismissed' );
        TS_Tracker::ts_send_tracking_data( false );
        die();
    }

	/**
	 * Send the data tracking data to the server.
	 * 
	 * @access public
	 * 
	 */

	private static function ts_tracking_actions() {
		if ( isset( $_GET[ self::$plugin_prefix . '_tracker_optin' ] ) && isset( $_GET[ self::$plugin_prefix . '_tracker_nonce' ] ) && wp_verify_nonce( $_GET[ self::$plugin_prefix . '_tracker_nonce' ], self::$plugin_prefix . '_tracker_optin' ) ) {
			update_option( self::$plugin_prefix . '_allow_tracking', 'yes' );
			TS_Tracker::ts_send_tracking_data( true );
			header( 'Location: ' . $_SERVER[ 'HTTP_REFERER' ] );
		} elseif ( isset( $_GET[ self::$plugin_prefix . '_tracker_optout' ] ) && isset( $_GET[ self::$plugin_prefix . '_tracker_nonce' ] ) && wp_verify_nonce( $_GET[ self::$plugin_prefix . '_tracker_nonce' ], self::$plugin_prefix . '_tracker_optout' ) ) {
			update_option( self::$plugin_prefix . '_allow_tracking', 'no' );
			TS_Tracker::ts_send_tracking_data( false );
			header( 'Location: ' . $_SERVER[ 'HTTP_REFERER' ] );
		}
	}

	/**
	 * Adds a data usage tracking notice in the admin
	 * 
	 * @access public
	 * @since 6.8
	 */
	
	public static function ts_track_usage_data() {
		$admin_url = get_admin_url();
		echo '<input type="hidden" id="admin_url" value="' . $admin_url . '"/>';
		self::ts_tracking_actions();
		if ( 'unknown' === get_option( self::$plugin_prefix . '_allow_tracking', 'unknown' ) ) : ?>
			<div class="<?php echo self::$plugin_prefix; ?>-message <?php echo self::$plugin_prefix; ?>-tracker notice notice-info is-dismissible" style="position: relative;">
				<div style="position: absolute;"><img class="site-logo" src= " <?php echo self::$ts_file_path . '/assets/images/site-logo-new.jpg '; ?> "></div>
				<p style="margin: 10px 0 10px 130px; font-size: medium;">
					<?php print( __( 'Want to help make ' . self::$plugin_name . ' even more awesome? Allow ' . self::$plugin_name . ' to collect non-sensitive diagnostic data and usage information and get 20% off on your next purchase. <a href="' . self::$blog_post_link . '">Find out more</a>.', self::$plugin_context ) ); ?></p>
				<p class="submit">
					<a class="button-primary button button-large" href="<?php echo esc_url( wp_nonce_url( add_query_arg( self::$plugin_prefix . '_tracker_optin', 'true' ), self::$plugin_prefix . '_tracker_optin', self::$plugin_prefix . '_tracker_nonce' ) ); ?>"><?php esc_html_e( 'Allow', self::$plugin_context ); ?></a>
					<a class="button-secondary button button-large skip"  href="<?php echo esc_url( wp_nonce_url( add_query_arg( self::$plugin_prefix . '_tracker_optout', 'true' ), self::$plugin_prefix . '_tracker_optout', self::$plugin_prefix . '_tracker_nonce' ) ); ?>"><?php esc_html_e( 'No thanks', self::$plugin_context ); ?></a>
				</p>
			</div>
		<?php endif;
	}
}