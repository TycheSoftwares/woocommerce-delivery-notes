<?php

/**
 * 
 * @since 1.0.0
 */
class WCDN_TS_Faq_Support {

	/**
	 * @var string The capability users should have to view the page
	 */
	public static $minimum_capability = 'manage_options';

	/**
	* @var string Plugin name
	* @access public 
	*/

	public static $plugin_name = '';

	/**
	 * @var string Plugin prefix
	 * @access public
	 */
	public static $plugin_prefix = '';

	/**
	 * @var string Plugins page path
	 * @access public
	 */
	public static $plugin_page = '';

	/**
	 * @var string Plugins plugin local
	 * @access public
	 */
	public static $plugin_locale = '';

	/**
	 * @var string Plugin folder name
	 * @access public
	 */
	public static $plugin_folder = '';
	/**
	 * @var string  Plugin url
	 * @access public
	 */
	public static $plugin_url = '';
	/**
	 * @var string Template path
	 * @access public
	 */
	public static $template_base = '';
	/**
	 * @var string Slug on Main menu
	 * @access public
	 */
	public static $plugin_slug = '';

	/**
	 * @var array List of all questions and answers.
	 * @access public
	 */
	public static $ts_faq = array ();
	/**
	 * @var string Slug for FAQ submenu
	 * @access public 
	 */
	public static $ts_faq_submenu_slug = '';
	/**
	 * Initialization of hooks where we prepare the functionality to ask use for survey
	 */
	public function __construct( $ts_plugin_mame = '', $ts_plugin_prefix = '', $ts_plugin_page = '', $ts_plugin_locale = '', $ts_plugin_folder_name = '', $ts_plugin_slug = '', $ts_faq_array = array(), $faq_submenu_slug = '' ) {
		
		self::$plugin_name   = $ts_plugin_mame;
		self::$plugin_prefix = $ts_plugin_prefix;
		self::$plugin_page   = $ts_plugin_page;
		self::$plugin_locale = $ts_plugin_locale;
		self::$plugin_slug   = $ts_plugin_slug;
		self::$ts_faq        = $ts_faq_array;
		self::$ts_faq_submenu_slug =  ( '' == $faq_submenu_slug ) ? self::$plugin_slug : $faq_submenu_slug ;


		//Add a sub menu in the main menu of the plugin if added.
		add_action( self::$plugin_prefix . '_add_submenu', array( &$this, 'ts_add_submenu' ) );

		//Add a tab for FAQ & Support along with other plugin settings tab.
		add_action( self::$plugin_prefix . '_add_settings_tab', array( &$this, 'ts_add_new_settings_tab' ) );
		add_action( self::$plugin_prefix . '_add_tab_content', array( &$this, 'ts_add_tab_content' ) );

		add_action ( self::$plugin_prefix . '_add_meta_footer', array( &$this, 'ts_add_meta_footer_text' ), 10, 1 );

		add_action( 'admin_menu', 							    array( &$this, 'ts_admin_menus' ) );
		add_action( 'admin_head', 							    array( &$this, 'admin_head' ) );

		self::$plugin_folder  = $ts_plugin_folder_name; 		
		self::$plugin_url     = $this->ts_get_plugin_url();
		self::$template_base  = $this->ts_get_template_path();
		
	}


	public static function ts_add_meta_footer_text () {
		?>
		<tr> <td> <br></td> </tr>
		
		<tr> 
			<td colspan="2">
				You have any queries? Please check our <a href=<?php echo admin_url( 'index.php?page='.self::$plugin_prefix .'_faq_page' ) ; ?> >FAQ</a> page.
			</td>
		<tr>
		<?php
	}

	/**
	 * Register the Dashboard Page which is later hidden but this pages
	 * is used to render the Welcome page.
	 *
	 * @access public
	 * @since  7.7
	 * @return void
	 */
	public function ts_admin_menus() {
		
		// About Page
		add_dashboard_page(
			sprintf( esc_html__( 'Frequently Asked Questions for %s', self::$plugin_locale ), self::$plugin_name ),
			esc_html__( 'Frequently Asked Questions for ' . self::$plugin_name, self::$plugin_locale ),
			self::$minimum_capability,
			self::$plugin_prefix . '_faq_page',
			array( $this, 'ts_faq_support_page' )
		);

	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since  7.7
	 * @return void
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', self::$plugin_prefix . '_faq_page' );
	}
	/**
	* Adds a subment to the main menu of the plugin
	* 
	* @since 7.7 
	*/

	public function ts_add_submenu() {
		$page = add_submenu_page( self::$plugin_slug, 
								  'FAQ & Support', 
								  'FAQ & Support', 
								  'manage_woocommerce', 
								  self::$ts_faq_submenu_slug . 
								  '&action=faq_support_page', 
								  array( &$this, 'ts_add_tab_content' ) 
								);

	}

	/** 
	* Add a new tab on the settings page.
	*
	* @since 7.7
	*/
	public function ts_add_new_settings_tab() {
		$faq_support_page = '';
		if( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'faq_support_page' ) {
		    $faq_support_page = "nav-tab-active";
		}
		$ts_plugins_page_url = self::$plugin_page . "&action=faq_support_page" ;
		?>
		<a href="<?php echo $ts_plugins_page_url; ?>" class="nav-tab <?php echo $faq_support_page; ?>"> <?php _e( 'FAQ & Support', self::$plugin_locale ); ?> </a>
		<?php

		
	}

	/**
	* Add content to the new tab added.
	*
	* @since 7.7
	*/

	public function ts_add_tab_content() {
		if( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'faq_support_page' ) {
			$this->ts_faq_support_page();
		}
	}

	/**
	* Adds a page to display the FAQ's and support information
	*
	* @since 7.7
	*/
	public function ts_faq_support_page() {
		ob_start();
		wc_get_template( 'faq-page/faq-page.php', 	
						 array(
								'ts_plugin_name' => self::$plugin_name,
							  	'ts_faq'         => self::$ts_faq
						 ), 
						 self::$plugin_folder, 
						 self::$template_base );
        echo ob_get_clean();
	}

	/**
     * This function returns the plugin url 
     *
     * @access public 
     * @since 7.7
     * @return string
     */
    public function ts_get_plugin_url() {
        return plugins_url() . '/' . self::$plugin_folder;
    }

    /**
    * This function returns the template directory path
    *
    * @access public 
    * @since 7.7
    * @return string
    */
    public function ts_get_template_path() {
    	return untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/';
    } 
}