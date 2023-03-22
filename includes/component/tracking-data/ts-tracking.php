<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once( 'class-ts-tracker.php' );

/** Adds the Tracking non-senstive data notice
 *
 * @since 6.8
 */
class WCDN_TS_tracking {

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

	/**
	 * @var string Settings page
	 * @access public
	 */
	public static $ts_settings_page = '';

	/**
	 * @var string On which setting page need to add setting
	 * @access public
	 */
	public static $ts_add_setting_on_page = '';
	/**
	 * @var string On which section setting need to add
	 * @access public
	 */
	public static $ts_add_setting_on_section = '';
	/**
	 * @var string Register setting
	 * @access public
	 */
	public static $ts_register_setting = '';
	/** 
	 * Default Constructor 
	 *
	 */
	public function __construct( $ts_plugin_prefix = '', $ts_plugin_name = '', $ts_blog_post_link = '', $ts_plugin_context = '', $ts_plugin_url = '', $setting_page = '', $add_setting = '', $setting_section = '', $setting_register = '' ) {

		self::$plugin_prefix  = $ts_plugin_prefix;
		self::$plugin_name    = $ts_plugin_name;
		self::$blog_post_link = $ts_blog_post_link;
		self::$plugin_url     = $ts_plugin_url;
		self::$ts_plugin_locale = $ts_plugin_context;
		self::$ts_settings_page  = $setting_page;
		self::$ts_add_setting_on_page = $add_setting;
		self::$ts_add_setting_on_section = $setting_section;
		self::$ts_register_setting = $setting_register;

		self::$ts_file_path   = untrailingslashit( plugins_url( '/', __FILE__) ) ;
		//Tracking Data
		add_action( 'admin_notices', array( 'WCDN_TS_tracking', 'ts_track_usage_data' ) );
		add_action( 'admin_footer',  array( 'WCDN_TS_tracking', 'ts_admin_notices_scripts' ) );
		add_action( 'wp_ajax_'.self::$plugin_prefix.'_admin_notices', array( 'WCDN_TS_tracking', 'ts_admin_notices' ) );

		add_filter( 'cron_schedules', array( 'WCDN_TS_tracking', 'ts_add_cron_schedule' ) );

		add_action( self::$plugin_prefix . '_add_new_settings', array( 'WCDN_TS_tracking', 'ts_add_reset_tracking_setting' ) );

		add_action ( 'admin_init', array( 'WCDN_TS_tracking', 'ts_reset_tracking_setting' ) ) ;

		self::ts_schedule_cron_job();

		add_filter( self::$plugin_prefix . '_add_settings_field', array( 'WCDN_TS_tracking', 'ts_add_new_settings_field') );
	}

	/**
	 * It will add the New setting for the WooCommerce settings.
	 * @hook self::$plugin_prefix . '_add_settings_field'
	 */
	public static function ts_add_new_settings_field ( $ts_settings ) {

		$ts_settings = array (
			'name'          => __( 'Reset usage tracking', 'deposits-for-woocommerce'),
			'type'          => 'link',
			'desc'          => __( 'This will reset your usage tracking settings, causing it to show the opt-in banner again and not sending any data','ts-tracking'),
			'button_text'   => 'Reset',
			'desc_tip'      => true,
			'class'         => 'button-secondary reset_tracking',
			'id'            => 'ts_reset_tracking',
		);

		return $ts_settings;
	}



	/**
	 * It will delete the tracking option from the database.
	 */
	public static function ts_reset_tracking_setting () {

		if ( isset ( $_GET [ 'ts_action' ] ) && 'wcdn_reset_tracking' == $_GET [ 'ts_action' ] ) {
			delete_option( self::$plugin_prefix . '_allow_tracking' );
			delete_option( 'wcdn_ts_tracker_last_send' );
			$ts_url = remove_query_arg( 'ts_action' );
			wp_safe_redirect( $ts_url );
		}
	}

	/**
	 * It will add the settinig, which will allow store owner to reset the tracking data. Which will result into stop trakcing the data.
	 * @hook self::$plugin_prefix . '_add_new_settings'
	 * 
	 */
	public static function ts_add_reset_tracking_setting ( $value ) {
		
		if ( '' == self::$ts_add_setting_on_page && '' == self::$ts_add_setting_on_section && '' == self::$ts_register_setting ) {
			if ( $value['id'] == 'ts_reset_tracking' ) {
			$description = WC_Admin_Settings::get_field_description( $value );
			$ts_action = self::$ts_settings_page . "&amp;ts_action=" . self::$plugin_prefix . "_reset_tracking";
		?>
			
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                <?php echo  $description['tooltip_html'];?>
            </th>
            
            <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                
				<a  href = "<?php echo $ts_action; ?>"
					name ="ts_reset_tracking"
					id   ="ts_reset_tracking"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
				> <?php echo $value['button_text']; ?> </a> <?php echo $description['description']; ?>
            </td>
        </tr><?php
			}
		} else {
			add_settings_field(
				'ts_reset_tracking',
				__( 'Reset usage tracking', self::$ts_plugin_locale  ),
				array( 'WCDN_TS_tracking', 'ts_rereset_tracking_callback' ),
				self::$ts_add_setting_on_page,
				self::$ts_add_setting_on_section,
				array( 'This will reset your usage tracking settings, causing it to show the opt-in banner again and not sending any data.', self::$ts_plugin_locale )
			);

			register_setting(
				self::$ts_register_setting,
				'ts_reset_tracking'
			);
		}
	}

	public static function ts_reset_tracking_setting_section_callback ( ) {

	}

	/**
	 * It will add the Reset button on the settings page.
	 * @param array $args
	 */
	public static function ts_rereset_tracking_callback ( $args ) {
		$wcap_restrict_domain_address = get_option( 'wcap_restrict_domain_address' );
		$domain_value                 = isset( $wcap_restrict_domain_address ) ? esc_attr( $wcap_restrict_domain_address ) : '';
		// Next, we update the name attribute to access this element's ID in the context of the display options array
		// We also access the show_header element of the options collection in the call to the checked() helper function
		$ts_action = self::$ts_settings_page . "&amp;ts_action=" . self::$plugin_prefix . "_reset_tracking"; 
		printf( '<a href="'.$ts_action.'" class="button button-large reset_tracking">Reset</a>' );
		
		// Here, we'll take the first argument of the array and add it to a label next to the checkbox
		$html = '<label for="wcap_restrict_domain_address_label"> '  . $args[0] . '</label>';
		echo $html;
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
			wp_schedule_event( time() + 604800, 'once_in_week', self::$plugin_prefix . '_ts_tracker_send_event' );
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
            self::$plugin_prefix . 'ts_dismiss_notice',
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
        WCDN_TS_Tracker::ts_send_tracking_data( false );
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
			WCDN_TS_Tracker::ts_send_tracking_data( true );
			header( 'Location: ' . $_SERVER[ 'HTTP_REFERER' ] );
		} elseif ( isset( $_GET[ self::$plugin_prefix . '_tracker_optout' ] ) && isset( $_GET[ self::$plugin_prefix . '_tracker_nonce' ] ) && wp_verify_nonce( $_GET[ self::$plugin_prefix . '_tracker_nonce' ], self::$plugin_prefix . '_tracker_optout' ) ) {
			update_option( self::$plugin_prefix . '_allow_tracking', 'no' );
			WCDN_TS_Tracker::ts_send_tracking_data( false );
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
					<?php print( __( 'Want to help make ' . self::$plugin_name . ' even more awesome? Allow ' . self::$plugin_name . ' to collect non-sensitive diagnostic data and usage information and get 20% off on your next purchase. <a href="' . self::$blog_post_link . '" target="_blank">Find out more</a>.', self::$plugin_context ) ); ?></p>
				<p class="submit">
					<a class="button-primary button button-large" href="<?php echo esc_url( wp_nonce_url( add_query_arg( self::$plugin_prefix . '_tracker_optin', 'true' ), self::$plugin_prefix . '_tracker_optin', self::$plugin_prefix . '_tracker_nonce' ) ); ?>"><?php esc_html_e( 'Allow', self::$plugin_context ); ?></a>
					<a class="button-secondary button button-large skip"  href="<?php echo esc_url( wp_nonce_url( add_query_arg( self::$plugin_prefix . '_tracker_optout', 'true' ), self::$plugin_prefix . '_tracker_optout', self::$plugin_prefix . '_tracker_nonce' ) ); ?>"><?php esc_html_e( 'No thanks', self::$plugin_context ); ?></a>
				</p>
			</div>
		<?php endif;
	}
}