<?php

/**
 * Contains the logic for deactivation popups
 * @since 1.0.0
 */
class WCDN_TS_deactivate {
	public static $ts_deactivation_str;

	public static $ts_generic_questions;

	public static $ts_plugin_specific_questions;

	/**
	 * URL to the  Tracker API endpoint.
	 * @var string
	 */

	private static $api_url = 'http://tracking.tychesoftwares.com/v1/';

	/**
	* @var string Plugin name
	* @access public 
	*/

	public static $plugin_name = '';

	/**
	 * @var string Plugin file name
	 * @access public
	 */
	public static $ts_plugin_file_name = '';

	/**
	 * @var string Plugin URL
	 * @access public
	 */
	public static $ts_plugin_url = '';

	/**
	 * Initialization of hooks where we prepare the functionality to ask use for survey
	 */
	public static function init( $ts_plugin_file_name = '', $ts_plugin_name = '' ) {
		self::$ts_plugin_file_name = $ts_plugin_file_name;
		self::$plugin_name         = $ts_plugin_name;
		self::$ts_plugin_url       = untrailingslashit( plugin_dir_path ( __FILE__ ) );
		
		self::ts_load_all_str();
		add_action( 'admin_footer', 					  array( __CLASS__, 'maybe_load_deactivate_options' ) );
		add_action( 'wp_ajax_ts_submit_uninstall_reason', array( __CLASS__, '_submit_uninstall_reason_action' ) );

		add_filter( 'plugin_action_links_' . self::$ts_plugin_file_name, array( __CLASS__, 'ts_plugin_settings_link' ) );
	}

	/**
	 * Settings link on Plugins page
	 * 
	 * @access public
	 * @param array $links 
	 * @return array
	 */
	public static function ts_plugin_settings_link( $links ) {
		
		if ( isset ( $links['deactivate'] ) ) {
			$links['deactivate'] .= '<i class="wcdn-ts-slug" data-slug="' . self::$ts_plugin_file_name  . '"></i>';
		}
		return $links;
	}

	/**
	 * Localizes all the string used
	 */
	public static function ts_load_all_str() {
		self::$ts_deactivation_str = array(
			"deactivation-share-reason"                => __( "If you have a moment, please let us know why you are deactivating", "ts-deactivation-survey" ),
			"deactivation-modal-button-submit"         => __( "Submit & Deactivate", "ts-deactivation-survey" ),
			"deactivation-modal-button-deactivate"     => __( "Deactivate", "ts-deactivation-survey" ),
			"deactivation-modal-button-cancel"         => __( "Cancel", "ts-deactivation-survey" ),
			"deactivation-modal-button-confirm"        => __( 'Yes - Deactivate', 'ts-deactivation-survey' ),
		);

		self::$ts_generic_questions = array(
			"reason-found-a-better-plugin"             => __( "I found a better plugin", "ts-deactivation-survey" ),
			"placeholder-plugin-name"                  => __( "What's the plugin's name?", "ts-deactivation-survey" ),
			"reason-needed-for-a-short-period"         => __( "I only needed the plugin for a short period", "ts-deactivation-survey" ),
			"reason-not-working"                       => __( "The plugin is not working", "ts-deactivation-survey" ),
			"placeholder-share-what-didnt-work"        => __( "Kindly share what didn't work so we can fix it for future users...", "ts-deactivation-survey" ),
			"reason-great-but-need-specific-feature"   => __( "The plugin is great, but I need specific feature that you don't support", "ts-deactivation-survey" ),
			"placeholder-feature"                      => __( "What feature?", "ts-deactivation-survey" ),
			"reason-dont-like-to-share-my-information" => __( "I don't like to share my information with you", "ts-deactivation-survey" ),
			"reason-other"                             => _x( "Other", "the text of the 'other' reason for deactivating the plugin that is shown in the modal box.", "ts-deactivation-survey" ),
		);
	}

	/**
	 * Checking current page and pushing html, js and css for this task
	 * @global string $pagenow current admin page
	 * @global array $VARS global vars to pass to view file
	 */
	public static function maybe_load_deactivate_options() {
		global $pagenow;
		if ( $pagenow == "plugins.php" ) {
			global $VARS;
			$VARS = array( 'slug' => "asvbsd", 'reasons' => self::deactivate_options() );
			include_once self::$ts_plugin_url . "/template/ts-deactivate-modal.php";
		}
	}

	/**
	 * deactivation reasons in array format
	 * @return array reasons array
	 * @since 1.0.0
	 */
	public static function deactivate_options() {

		self::$ts_plugin_specific_questions = apply_filters( 'ts_deativate_plugin_questions', array () );


		$reason_found_better_plugin = array(
			'id'                => 2,
			'text'              => self::$ts_generic_questions[ 'reason-found-a-better-plugin' ],
			'input_type'        => 'textfield',
			'input_placeholder' => self::$ts_generic_questions[ 'placeholder-plugin-name' ]
		);

		$reason_not_working = array(
			'id'                => 3,
			'text'              => self::$ts_generic_questions[ 'reason-not-working' ],
			'input_type'        => 'textfield',
			'input_placeholder' => self::$ts_generic_questions[ 'placeholder-share-what-didnt-work' ]
		);

		$reason_great_but_need_specific_feature = array(
			'id'                => 8,
			'text'              => self::$ts_generic_questions[ 'reason-great-but-need-specific-feature' ],
			'input_type'        => 'textfield',
			'input_placeholder' => self::$ts_generic_questions[ 'placeholder-feature' ]
		); 

		$reason_plugin_not_compatible =  isset ( self::$ts_plugin_specific_questions[ 3 ] ) ? self::$ts_plugin_specific_questions[ 3 ] : '' ; 

		$reason_other = array(
			'id'                => 10,
			'text'              => self::$ts_generic_questions[ 'reason-other' ],
			'input_type'        => 'textfield',
			'input_placeholder' => ''
		);

		$long_term_user_reasons = array(
			array(
				'id'                => 1,
				'text'              => self::$ts_generic_questions[ 'reason-needed-for-a-short-period' ],
				'input_type'        => '',
				'input_placeholder' => ''
			),
			$reason_found_better_plugin,
			$reason_not_working,
			isset ( self::$ts_plugin_specific_questions[ 0 ] ) ? self::$ts_plugin_specific_questions[ 0 ] : '',
			isset ( self::$ts_plugin_specific_questions[ 1 ] ) ? self::$ts_plugin_specific_questions[ 1 ] : '',
			isset ( self::$ts_plugin_specific_questions[ 2 ] ) ? self::$ts_plugin_specific_questions[ 2 ] : '',
			$reason_plugin_not_compatible,
			$reason_great_but_need_specific_feature,
			array(
				'id'                => 9,
				'text'              => self::$ts_generic_questions[ 'reason-dont-like-to-share-my-information' ],
				'input_type'        => '',
				'input_placeholder' => ''
			)
		);


		$uninstall_reasons[ 'default' ] = $long_term_user_reasons;

		$uninstall_reasons = apply_filters( 'ts_uninstall_reasons', $uninstall_reasons );
		array_push( $uninstall_reasons['default'], $reason_other );

		return $uninstall_reasons;
	}

	/**
	 * get exact str against the slug
	 *
	 * @param type $slug
	 *
	 * @return type
	 */
	public static function load_str( $slug ) {
		return self::$ts_deactivation_str[ $slug ];
	}

	/**
	 * Called after the user has submitted his reason for deactivating the plugin.
	 *
	 * @since  1.1.2
	 */
	public static function _submit_uninstall_reason_action() {
		if ( ! isset( $_POST[ 'reason_id' ] ) ) {
			exit;
		}

		$plugin_data = array();
		
		$plugin_data[ 'url' ]   = home_url();
		$plugin_data[ 'email' ] = apply_filters( 'ts_tracker_admin_email', get_option( 'admin_email' ) );

		$reason_info = isset( $_REQUEST[ 'reason_info' ] ) ? trim( stripslashes( $_REQUEST[ 'reason_info' ] ) ) : '';

		$plugin_data[ 'reason_id' ] = $_POST[ 'reason_id' ];
		$plugin_data[ 'reason_info' ] = substr( $reason_info, 0, 128 );
		$plugin_data[ 'reason_text' ] = $_POST[ 'reason_text' ];

		$plugin_data[ 'ts_meta_data_table_name' ] = 'ts_deactivation_survey';
        $plugin_data[ 'ts_plugin_name' ]		  = self::$plugin_name;

		wp_safe_remote_post( self::$api_url, array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => false,
				'headers'     => array( 'user-agent' => 'TSTracker/' . md5( esc_url( home_url( '/' ) ) ) . ';' ),
				'body'        => json_encode( $plugin_data ),
				'cookies'     => array(),
			)
		);
		// Print '1' for successful operation.
		echo 1;
		exit;
	}

}