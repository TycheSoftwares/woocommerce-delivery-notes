<?php

class WCDN_ts_pro_notices {

	/**
	* Plugin's Name
	* 
	* @access public
	* @since 3.5
	*/
	public static $plugin_name = "";

	/**
	* Plugin's unique prefix
	*
	* @access public
	* @since 3.5
	*/

	public static $plugin_prefix = '';

	/**
	* Pro plugin's unique prefix
	*
	* @access public
	* @since 3.5
	*/

	public static $pro_plugin_prefix = '';

	/**
	 * @var array Collection of all messages.
	 * @access public
	 */
	public static $ts_pro_notices = array ();

	/**
	 * @var string file name
	 * @access public
	 */
	public static $ts_file_name = '';

	/**
	 * @var string Pro version file name
	 * @access public
	 */
	public static $ts_pro_file_name = '';


	/** 
	* Default Constructor
	* 
	* @since 3.5
	*/

	public function __construct( $ts_plugin_name = '', $ts_plugin_prefix = '', $ts_pro_plugin_prefix = '', $ts_notices = array(), $ts_file = '', $ts_pro_file = '' ) {
		self::$plugin_name   	 = $ts_plugin_name;
		self::$plugin_prefix 	 = $ts_plugin_prefix;
		self::$pro_plugin_prefix = $ts_pro_plugin_prefix;
		self::$ts_pro_notices    = $ts_notices;
		self::$ts_file_name      = $ts_file;
		self::$ts_pro_file_name  = $ts_pro_file;

		//Initialize settings
		register_activation_hook( __FILE__,  array( &$this, 'ts_notices_activate' ) );

		//Add pro notices
        add_action( 'admin_notices', array( 'WCDN_ts_pro_notices', 'ts_notices_of_pro' ) );
		add_action( 'admin_init', array( 'WCDN_ts_pro_notices', 'ts_ignore_pro_notices' ) );
		
		add_action( self::$plugin_prefix . '_activate', array( 'WCDN_ts_pro_notices', 'ts_activate_time' ) );
	}

	public static function ts_activate_time () {

		if( !get_option( self::$plugin_prefix .'_activate_time' ) ) {
			add_option( self::$plugin_prefix .'_activate_time', current_time( 'timestamp' ) );
		}
	}

	/**
	* Add an option which stores the timestamp when the plugin is first activated
	*
	* @since 3.5
	*/
	public static function ts_notices_activate() {
		//Pro admin Notices
        if( !get_option( self::$plugin_prefix . '_activate_time' ) ) {
            add_option( self::$plugin_prefix . '_activate_time', current_time( 'timestamp' ) );
        }
	}

	public static function ts_notices_of_pro() {
		$activate_time = get_option ( self::$plugin_prefix . '_activate_time' );
        $sixty_days    = strtotime ( '+60 Days', $activate_time );
		$current_time  = current_time ( 'timestamp' );
		$add_query_arguments = '';
		$message             = '';

		if( '' != self::$ts_pro_file_name && !is_plugin_active( self::$ts_pro_file_name ) && 
            ( false === $activate_time || ( $activate_time > 0 && $current_time >= $sixty_days ) ) ) {
        	global $current_user ;
			$user_id = $current_user->ID;
			
			if( ! get_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_first_notice_ignore' ) ) {
				
				$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_first_notice_ignore', '0' );
				
				$class = 'updated notice-info point-notice one';
				$style = 'position:relative';
				$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
				printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, self::$ts_pro_notices[1], $cancel_button );
			}

			if ( get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_first_notice_ignore' ) && 
				! get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_second_notice_ignore' )
				 ) {

				$first_ignore_time = get_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_first_notice_ignore_time' );
				$fifteen_days = strtotime( '+15 Days', $first_ignore_time[0] );
				
				if ( $current_time > $fifteen_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_second_notice_ignore', '0' );
					
					$class = 'updated notice-info point-notice two';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, self::$ts_pro_notices[2], $cancel_button );
				}
			}
			
			if ( get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_first_notice_ignore' ) && 
				get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_second_notice_ignore' ) &&
				! get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_third_notice_ignore' )
			   ) {

				$second_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_second_notice_ignore_time' );
				$ts_fifteen_days = strtotime( '+15 Days', $second_ignore_time[0] );

				if ( $current_time > $ts_fifteen_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_third_notice_ignore', '0' );
					
					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, self::$ts_pro_notices[3], $cancel_button );
				}
			}

			if ( get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_first_notice_ignore' ) && 
				get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_second_notice_ignore' ) &&
				get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_third_notice_ignore' ) &&
				! get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_fourth_notice_ignore' )
			   ) {

				$third_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_third_notice_ignore_time' );
				$ts_fifteen_days = strtotime( '+15 Days', $third_ignore_time[0] );

				if ( $current_time > $ts_fifteen_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_fourth_notice_ignore', '0' );
					
					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, self::$ts_pro_notices[4], $cancel_button );
				}
			}

			if ( get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_first_notice_ignore' )  && 
				 get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_second_notice_ignore' ) &&
				 get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_third_notice_ignore' )  &&
				 get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_fourth_notice_ignore' ) &&
				! get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_fifth_notice_ignore' )
			   ) {

				$fourth_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_fourth_notice_ignore_time' );
				$ts_fifteen_days = strtotime( '+15 Days', $fourth_ignore_time[0] );
	
				if ( $current_time > $ts_fifteen_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_fifth_notice_ignore', '0' );
					
					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, self::$ts_pro_notices[5], $cancel_button );
				}
			}

			/**
			 * Display Other plugin notices.
			 */

			if ( get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_first_notice_ignore' )  && 
				 get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_second_notice_ignore' ) &&
				 get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_third_notice_ignore' )  &&
			   	 get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_fourth_notice_ignore' ) &&
			     get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_fifth_notice_ignore' )
			) {
				$fifth_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_fifth_notice_ignore_time' );

				self::ts_display_other_pro_plugin_notices( $current_time, $activate_time, $fifth_ignore_time [ 0 ] );
		  	}
		}

		/**
		 * What happen if the respective plugin is activated.
		 * AC - With latest version, Lite will be deactivated. With old version, it will be deactivated.
		 * Ordd, Prdd - Both version can be activated.
		 * 
		 */
		$seven_days    = strtotime ( '+7 Days', $activate_time );
		
		if( ( is_plugin_active( self::$ts_pro_file_name ) || '' == self::$ts_pro_file_name  ) && 
		( false === $activate_time || ( $activate_time > 0 && $current_time >= $seven_days ) ) ) {

			self::ts_display_other_pro_plugin_notices( $current_time, $activate_time );
		}

	}

	/**
	 * It will display the all othe pro plugin notices
	 * 
	 */
	public static function ts_display_other_pro_plugin_notices ( $current_time, $activate_time, $ts_consider_time = '' ) {

		$sixth_plugin_link = self::$ts_pro_notices[6] ['plugin_link'];
		
			if ( !is_plugin_active( $sixth_plugin_link  ) && 
				 ! get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_sixth_notice_ignore' )
				) {

				if ( '' != $ts_consider_time ) {
					/**
					 * This is fifth ignore notice time plus 7 days
					 */
					$ts_consider_time = strtotime( '+7 Days', $ts_consider_time );
				
				}

				$sixth_message = self::$ts_pro_notices[6] ['message'];
				
				if ( $current_time > $ts_consider_time ) { 
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_sixth_notice_ignore', '0' );
					
					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $sixth_message, $cancel_button );
				}
				
			}

			if ( !is_plugin_active( $sixth_plugin_link ) && 
				  get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_sixth_notice_ignore' )  &&
				! get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_seventh_notice_ignore' ) 
			) {

				$sixth_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_sixth_notice_ignore_time' );
				$ts_seven_days = strtotime( '+7 Days', $sixth_ignore_time[0] );

				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_seventh_notice_ignore', '0' );
					
					$seventh_message = self::$ts_pro_notices[7] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $seventh_message, $cancel_button );
				}
			}


			if ( !is_plugin_active( $sixth_plugin_link ) && 
				 
				 get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_seventh_notice_ignore' ) &&
				! get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_eigth_notice_ignore' ) 
			) {

				$seventh_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_seventh_notice_ignore_time' );
				$ts_seven_days = strtotime( '+7 Days', $seventh_ignore_time[0] );

				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_eigth_notice_ignore', '0' );
					
					$eight_message = self::$ts_pro_notices[8] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $eight_message, $cancel_button );
				}
			}

			if ( !is_plugin_active( $sixth_plugin_link ) && 
				 get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_eigth_notice_ignore' )   &&
				! get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_ninth_notice_ignore' ) 
			) {

				$eigth_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_eigth_notice_ignore_time' );
				$ts_seven_days   = strtotime( '+7 Days', $eigth_ignore_time[0] );

				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_ninth_notice_ignore', '0' );
					
					$ninth_message = self::$ts_pro_notices[9] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $ninth_message, $cancel_button );
				}
			}

			
			$tenth_plugin_link = self::$ts_pro_notices[10] ['plugin_link'];
			if ( !is_plugin_active( $tenth_plugin_link ) && 
				 get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_ninth_notice_ignore' )  &&
				! get_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_tenth_notice_ignore' ) 
			) {

				$ninth_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_ninth_notice_ignore_time' );
				$ts_seven_days   = strtotime( '+7 Days', $ninth_ignore_time[0] );

				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_tenth_notice_ignore', '0' );
					
					$tenth_message = self::$ts_pro_notices[10] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $tenth_message, $cancel_button );
				}

			} else if ( !is_plugin_active( $tenth_plugin_link ) && 
						is_plugin_active ( $sixth_plugin_link ) &&
						! get_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_tenth_notice_ignore' ) 
			) {
				/**
				 * If Ac Pro active then Directly show notice after 30 days, skip 4 notice period of ac pro.
				 */
				$ts_seven_days   = strtotime( '+30 Days', $activate_time );

				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_tenth_notice_ignore', '0' );
					
					$tenth_message = self::$ts_pro_notices[10] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $tenth_message, $cancel_button );
				}

			}

			$eleven_plugin_link = self::$ts_pro_notices[11] ['plugin_link'];
			if ( !is_plugin_active( $eleven_plugin_link ) && 
				 get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_tenth_notice_ignore' ) &&
				 ! get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_eleven_notice_ignore' )
			) {

				$tenth_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_tenth_notice_ignore_time' );
				$ts_seven_days   = strtotime( '+7 Days', $tenth_ignore_time[0] );

				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_eleven_notice_ignore', '0' );
					
					$eleven_message = self::$ts_pro_notices[11] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $eleven_message, $cancel_button );
				}

			} else if ( !is_plugin_active( $eleven_plugin_link ) && 
						get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_tenth_notice_ignore' ) &&
						! get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_eleven_notice_ignore' )
			) {

				/**
				 * If Tenth notice has been ignored, the consider the time.
				 */
				$tenth_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_tenth_notice_ignore_time' );
				$ts_seven_days   = strtotime( '+7 Days', $tenth_ignore_time[0] );

				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_eleven_notice_ignore', '0' );
					
					$eleven_message = self::$ts_pro_notices[11] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $eleven_message, $cancel_button );
				}

			} else if ( !is_plugin_active( $eleven_plugin_link ) && 
						is_plugin_active ( $sixth_plugin_link ) &&
						is_plugin_active ( $tenth_plugin_link ) &&
						!get_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_eleven_notice_ignore' ) 
			) {

				 /**
				  * If Ac pro and orrdd pro is activate then skip the time priod of both plugins
				  */
				$ts_seven_days   = strtotime( '+37 Days', $activate_time );

				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_eleven_notice_ignore', '0' );
					
					$eleven_message = self::$ts_pro_notices[11] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $eleven_message, $cancel_button );
				}

			}

			$twelve_plugin_link = self::$ts_pro_notices[12] ['plugin_link'];
			if ( !is_plugin_active( $twelve_plugin_link ) && 
				 get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_eleven_notice_ignore' ) &&
				 !get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_twelve_notice_ignore' )
			) {

				$eleventh_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_eleventh_notice_ignore_time' );
				$ts_seven_days   = strtotime( '+7 Days', $eleventh_ignore_time[0] );

				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_twelve_notice_ignore', '0' );
					
					$twelve_message = self::$ts_pro_notices[12] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $twelve_message, $cancel_button );
				}

			} else if ( !is_plugin_active( $twelve_plugin_link ) && 
						get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_tenth_notice_ignore' ) &&
						get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_eleven_notice_ignore' ) &&
						!get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_twelve_notice_ignore' )
			) {

				/**
				 * If ordd, booking notice ignored then consider booking plugin ignore time ( 11 )
				 */
				$eleventh_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_eleventh_notice_ignore_time' );
				$ts_seven_days   = strtotime( '+7 Days', $eleventh_ignore_time[0] );
				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_twelve_notice_ignore', '0' );
					
					$twelve_message = self::$ts_pro_notices[12] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $twelve_message, $cancel_button );
				}

			}else if ( !is_plugin_active( $twelve_plugin_link ) && 
						is_plugin_active ( $sixth_plugin_link ) &&
						is_plugin_active ( $tenth_plugin_link ) &&
						is_plugin_active ( $eleven_plugin_link ) &&
					   !get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_twelve_notice_ignore' )
			) {
				/**
				 * If ac pro, ordd pro, booking is active then skip the time period for these plugins and consider the plugin activate time
				 */

				$ts_seven_days   = strtotime( '+43 Days', $activate_time );

				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_twelve_notice_ignore', '0' );
					
					$twelve_message = self::$ts_pro_notices[12] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $twelve_message, $cancel_button );
				}

			}

			$thirteen_plugin_link = self::$ts_pro_notices[13] ['plugin_link'];
			if ( !is_plugin_active( $thirteen_plugin_link ) && 
				 get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_twelve_notice_ignore' ) &&
				 !get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_thirteen_notice_ignore' )
			) {

				$twelve_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_twelve_notice_ignore_time' );
				$ts_seven_days   = strtotime( '+7 Days', $twelve_ignore_time[0] );

				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_thirteen_notice_ignore', '0' );
					
					$thirteen_message = self::$ts_pro_notices[13] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $thirteen_message, $cancel_button );
				}

			} else if ( !is_plugin_active( $thirteen_plugin_link ) && 
						get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_tenth_notice_ignore' ) &&
						get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_eleven_notice_ignore' ) &&
						get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_twelve_notice_ignore' ) &&
						!get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_thirteen_notice_ignore' )
			) {

				/**
				 * If ordd, booking, and wc deposits notice is ignored, then consider the wc deposits ignore time.
				 */
				$twelve_ignore_time = get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_twelve_notice_ignore_time' );
				$ts_seven_days   = strtotime( '+7 Days', $twelve_ignore_time[0] );
				
				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_thirteen_notice_ignore', '0' );
					
					$thirteen_message = self::$ts_pro_notices[13] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $thirteen_message, $cancel_button );
				}

			}
			else if ( !is_plugin_active( $thirteen_plugin_link ) && 
						is_plugin_active ( $sixth_plugin_link ) &&
						is_plugin_active ( $tenth_plugin_link ) &&
						is_plugin_active ( $eleven_plugin_link ) &&
						is_plugin_active ( $twelve_plugin_link ) &&
					  !get_user_meta( get_current_user_id(),  self::$pro_plugin_prefix . '_thirteen_notice_ignore' )
			) {

				/**
				 * If ordd, booking, and wc deposits activate, then consider the plugin activate time.
				 */
				$ts_seven_days   = strtotime( '+50 Days', $activate_time );

				if ( $current_time > $ts_seven_days ) {
					
					$add_query_arguments = add_query_arg( self::$pro_plugin_prefix . '_thirteen_notice_ignore', '0' );
					
					$thirteen_message = self::$ts_pro_notices[13] ['message'];

					$class = 'updated notice-info point-notice';
					$style = 'position:relative';
					$cancel_button = '<a href="'.$add_query_arguments.'" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>';
					printf( '<div class="%1$s" style="%2$s"><p>%3$s %4$s</p></div>', $class, $style, $thirteen_message, $cancel_button );
				}

			}
	}

	/**
	 * Ignore pro notice
	 */
	public static function ts_ignore_pro_notices() {

		if( !get_option( self::$plugin_prefix . 'activate_time' ) ) {
            add_option( self::$plugin_prefix . '_activate_time', current_time( 'timestamp' ) );
		}
		
		// If user clicks to ignore the notice, add that to their user meta
		if ( isset( $_GET[ self::$pro_plugin_prefix . '_first_notice_ignore' ] ) && '0' === $_GET[ self::$pro_plugin_prefix . '_first_notice_ignore' ] ) {
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_first_notice_ignore', 'true', true );
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_first_notice_ignore_time', current_time( 'timestamp' ), true );
			wp_safe_redirect( remove_query_arg( self::$pro_plugin_prefix . '_first_notice_ignore' ) );

		}

		if ( isset( $_GET[ self::$pro_plugin_prefix . '_second_notice_ignore'] ) && '0' === $_GET[ self::$pro_plugin_prefix . '_second_notice_ignore'] ) {
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_second_notice_ignore', 'true', true );
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_second_notice_ignore_time', current_time( 'timestamp' ), true );
			wp_safe_redirect( remove_query_arg( self::$pro_plugin_prefix . '_second_notice_ignore' )  );
		}

		if ( isset( $_GET[ self::$pro_plugin_prefix . '_third_notice_ignore'] ) && '0' === $_GET[ self::$pro_plugin_prefix . '_third_notice_ignore'] ) {
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_third_notice_ignore', 'true', true );
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_third_notice_ignore_time', current_time( 'timestamp' ), true );
			wp_safe_redirect( remove_query_arg( self::$pro_plugin_prefix . '_third_notice_ignore' ) );
		}

		if ( isset( $_GET[ self::$pro_plugin_prefix . '_fourth_notice_ignore' ] ) && '0' === $_GET[ self::$pro_plugin_prefix . '_fourth_notice_ignore' ] ) {
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_fourth_notice_ignore', 'true', true );
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_fourth_notice_ignore_time', current_time( 'timestamp' ), true );
			wp_safe_redirect( remove_query_arg( self::$pro_plugin_prefix . '_fourth_notice_ignore' ) );
		}

		if ( isset( $_GET[ self::$pro_plugin_prefix . '_fifth_notice_ignore' ] ) && '0' === $_GET[ self::$pro_plugin_prefix . '_fifth_notice_ignore' ] ) {
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_fifth_notice_ignore', 'true', true );
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_fifth_notice_ignore_time', current_time( 'timestamp' ), true );
			wp_safe_redirect( remove_query_arg( self::$pro_plugin_prefix . '_fifth_notice_ignore' ) );
		}

		if ( isset( $_GET[ self::$pro_plugin_prefix . '_sixth_notice_ignore' ] ) && '0' === $_GET[ self::$pro_plugin_prefix . '_sixth_notice_ignore' ] ) {
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_sixth_notice_ignore', 'true', true );
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_sixth_notice_ignore_time', current_time( 'timestamp' ), true );
			wp_safe_redirect( remove_query_arg( self::$pro_plugin_prefix . '_sixth_notice_ignore' ) );
		}

		if ( isset( $_GET[ self::$pro_plugin_prefix . '_seventh_notice_ignore' ] ) && '0' === $_GET[ self::$pro_plugin_prefix . '_seventh_notice_ignore' ] ) {
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_seventh_notice_ignore', 'true', true );
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_seventh_notice_ignore_time', current_time( 'timestamp' ), true );
			wp_safe_redirect( remove_query_arg( self::$pro_plugin_prefix . '_seventh_notice_ignore' ) );
		}

		if ( isset( $_GET[ self::$pro_plugin_prefix . '_eigth_notice_ignore' ] ) && '0' === $_GET[ self::$pro_plugin_prefix . '_eigth_notice_ignore' ] ) {
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_eigth_notice_ignore', 'true', true );
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_eigth_notice_ignore_time', current_time( 'timestamp' ), true );
			wp_safe_redirect( remove_query_arg( self::$pro_plugin_prefix . '_eigth_notice_ignore' ) );
		}

		if ( isset( $_GET[ self::$pro_plugin_prefix . '_ninth_notice_ignore' ] ) && '0' === $_GET[ self::$pro_plugin_prefix . '_ninth_notice_ignore' ] ) {
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_ninth_notice_ignore', 'true', true );
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_ninth_notice_ignore_time', current_time( 'timestamp' ), true );
			wp_safe_redirect( remove_query_arg( self::$pro_plugin_prefix . '_ninth_notice_ignore' ) );
		}

		if ( isset( $_GET[ self::$pro_plugin_prefix . '_tenth_notice_ignore' ] ) && '0' === $_GET[ self::$pro_plugin_prefix . '_tenth_notice_ignore' ] ) {
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_tenth_notice_ignore', 'true', true );
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_tenth_notice_ignore_time', current_time( 'timestamp' ), true );
			wp_safe_redirect( remove_query_arg( self::$pro_plugin_prefix . '_tenth_notice_ignore' ) );
		}

		if ( isset( $_GET[ self::$pro_plugin_prefix . '_eleven_notice_ignore' ] ) && '0' === $_GET[ self::$pro_plugin_prefix . '_eleven_notice_ignore' ] ) {
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_eleven_notice_ignore', 'true', true );
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_eleventh_notice_ignore_time', current_time( 'timestamp' ), true );
			wp_safe_redirect( remove_query_arg( self::$pro_plugin_prefix . '_eleven_notice_ignore' ) );
		}

		if ( isset( $_GET[ self::$pro_plugin_prefix . '_twelve_notice_ignore' ] ) && '0' === $_GET[ self::$pro_plugin_prefix . '_twelve_notice_ignore' ] ) {
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_twelve_notice_ignore', 'true', true );
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_twelve_notice_ignore_time', current_time( 'timestamp' ), true );
			wp_safe_redirect( remove_query_arg( self::$pro_plugin_prefix . '_twelve_notice_ignore' ) );
		}

		if ( isset( $_GET[ self::$pro_plugin_prefix . '_thirteen_notice_ignore' ] ) && '0' === $_GET[ self::$pro_plugin_prefix . '_thirteen_notice_ignore' ] ) {
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_thirteen_notice_ignore', 'true', true );
			add_user_meta( get_current_user_id(), self::$pro_plugin_prefix . '_thirteen_notice_ignore_time', current_time( 'timestamp' ), true );
			wp_safe_redirect( remove_query_arg( self::$pro_plugin_prefix . '_thirteen_notice_ignore' ) );
		}

	}
}

