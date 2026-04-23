<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Main Class.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Main
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN;

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce Delivery Notes Core Class.
 *
 * @class WooCommerce_Delivery_Notes.
 */
final class WooCommerce_Delivery_Notes {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected static $plugin_version = '7.0.2';

	/**
	 * Minimum version of WordPress required.
	 *
	 * @var string
	 */
	private static $wordpress_version = '5.2';

	/**
	 * Minimum version of WooCommerce required.
	 *
	 * @var string
	 */
	private static $woocommerce_version = '3.3.0';

	/**
	 * Minimum version of PHP required.
	 *
	 * @var string
	 */
	private static $php_version = '7.4';

	/**
	 * Slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wcdn';

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	protected static $plugin_slug = 'woocommerce-delivery-notes';

	/**
	 * Plugin Name.
	 *
	 * @var string
	 */
	protected static $plugin_name = 'Print Invoice & Delivery Notes for WooCommerce';

	/**
	 * Services.
	 *
	 * @var array
	 */
	protected $services = array();

	/**
	 * The single instance of the class.
	 *
	 * @var WooCommerce_Delivery_Notes
	 */
	protected static $instance = null;

	/**
	 * Retrieve the instance of the class and ensures only one instance is loaded or can be loaded.
	 *
	 * @return WooCommerce_Delivery_Notes
	 *
	 * @since 7.0
	 */
	public static function instance() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof WooCommerce_Delivery_Notes ) ) {
			self::$instance = new WooCommerce_Delivery_Notes();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor to prevent WCDN from being loaded more than once.
	 *
	 * @since 7.0.0
	 */
	private function __construct() {}

	/**
	 * A dummy magic method to prevent WCDN from being cloned.
	 *
	 * @since 7.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Not allowed.', 'woocommerce-delivery-notes' ), '1.0' );
	}

	/**
	 * A dummy magic method to prevent WCDN from being un-serialized.
	 *
	 * @since 7.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Not allowed.', 'woocommerce-delivery-notes' ), '1.0' );
	}

	/**
	 * Default constructor
	 *
	 * @since 7.0
	 */
	private function setup() {

		/**
		 * Define Constants.
		 */
		self::define_constants();

		if ( ! self::check_requirements() ) {
			return;
		}

		/**
		 * Hooks.
		 */
		self::init_hooks();

		/**
		 * Include Files.
		 */
		self::maybe_include_files();

		/**
		 * Setup Services.
		 */
		self::setup_services();
	}

	/**
	 * Action Hooks.
	 *
	 * @since 7.0
	 */
	private static function init_hooks() {
		register_activation_hook( WCDN_FILE, array( 'Tyche\\WCDN\\Install', 'install' ) );
		register_deactivation_hook( WCDN_FILE, array( 'Tyche\\WCDN\\Uninstall', 'deactivate_plugin' ) );

		// WCDN Hooks.
		self::include_file( 'core/class-hooks.php' );
		Hooks::init();
	}

	/**
	 * Function for defining constants.
	 *
	 * @param string $variable Constant which is to be defined.
	 * @param string $value Value of the Constant.
	 *
	 * @since 7.0
	 */
	public static function define( $variable, $value ) {
		if ( ! defined( $variable ) ) {
			define( $variable, $value );
		}
	}

	/**
	 * Include File.
	 *
	 * @param string $file File to be included.
	 * @param bool   $is_plugin_include_file If it's a plugin file, then we can add the path.
	 * @since 7.0
	 */
	public static function include_file( $file, $is_plugin_include_file = true ) {
		$file = $is_plugin_include_file ? WCDN_PLUGIN_PATH . '/includes/' . $file : $file;

		if ( file_exists( $file ) ) {
			include_once $file; // nosemgrep: audit.php.lang.security.file.inclusion-arg -- all callers pass hardcoded string literals; path is prefixed with WCDN_PLUGIN_PATH.
		}
	}

	/**
	 * Define constants to be used ac ross the plugin.
	 *
	 * @since 7.0
	 */
	public static function define_constants() {
		self::define( 'WCDN_PLUGIN_NAME', self::$plugin_name );
		self::define( 'WCDN_SLUG', self::$slug );
		self::define( 'WCDN_PLUGIN_SLUG', self::$plugin_slug );
		self::define( 'WCDN_PLUGIN_VERSION', self::$plugin_version );
		self::define( 'WCDN_PLUGIN_PATH', untrailingslashit( plugin_dir_path( WCDN_FILE ) ) );
		self::define( 'WCDN_PLUGIN_URL', untrailingslashit( plugins_url( '/', WCDN_FILE ) ) );
		self::define( 'WCDN_IMAGE_URL', WCDN_PLUGIN_URL . '/assets/images' );
		self::define( 'WCDN_AJAX_URL', get_admin_url() . 'admin-ajax.php' );
	}

	/**
	 * Checks that all requirements are met.
	 *
	 * @return bool
	 */
	public static function check_requirements() {

		$messages = array();

		// Check WordPress version.
		if ( version_compare( get_bloginfo( 'version' ), self::$wordpress_version, '<' ) ) {
			/* translators: 1. Plugin Name, 2. WordPress Version */
			$messages[] = sprintf( esc_html__( 'You are using an outdated version of WordPress. %1$s requires WP version %2$s or higher.', 'woocommerce-delivery-notes' ), self::$plugin_name, self::$wordpress_version );
		}

		// Check PHP version.
		if ( version_compare( phpversion(), self::$php_version, '<' ) ) {
			/* translators: 1. Plugin Name, 2. PHP Version */
			$messages[] = sprintf( esc_html__( '%1$s requires PHP version %2$s or above. Please update PHP to run this plugin.', 'woocommerce-delivery-notes' ), self::$plugin_name, self::$php_version );
		}

		// Check WooCommerce is active.
		if ( ! self::is_woocommerce_active() ) {
			/* translators: 1. Plugin Name, 2. WooCommerce Version */
			$messages[] = sprintf( esc_html__( 'WooCommerce not found. %1$s requires WooCommerce v%2$s or higher.', 'woocommerce-delivery-notes' ), self::$plugin_name, self::$woocommerce_version );
		} elseif ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, self::$woocommerce_version, '<' ) ) {
			/* translators: 1. Plugin Name, 2. WooCommerce Version */
			$messages[] = sprintf( esc_html__( 'You are using an outdated version of WooCommerce. %1$s requires WooCommerce v%2$s or higher.', 'woocommerce-delivery-notes' ), self::$plugin_name, self::$woocommerce_version );
		}

		if ( empty( $messages ) ) {
			return true;
		}

		self::include_file( 'class-wcdn-notices.php' );

		foreach ( $messages as $index => $message ) {
			WCDN_Notices::add_notice(
				'plugin_installation_error_notice_' . $index,
				$message
			);
		}

		add_action( 'admin_init', array( __CLASS__, 'deactivate' ) );

		return false;
	}

	/**
	 * Auto-deactivate plugin if requirements are not met.
	 */
	public static function deactivate() {
		if ( is_plugin_active( plugin_basename( WCDN_FILE ) ) ) {
			deactivate_plugins( plugin_basename( WCDN_FILE ) );
		}

		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- standard WP activation flag, no user data processed
			unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}

	/**
	 * Checks if WooCommerce is installed and active.
	 *
	 * @since 7.0
	 */
	public static function is_woocommerce_active() {

		// WooCommerce is required.
		$woocommerce_path = 'woocommerce/woocommerce.php';
		$active_plugins   = (array) get_option( 'active_plugins', array() );
		$active           = false;

		if ( is_multisite() ) {
			$plugins = get_site_option( 'active_sitewide_plugins' );
			$active  = isset( $plugins[ $woocommerce_path ] );
		}

		return in_array( $woocommerce_path, $active_plugins, true ) || array_key_exists( $woocommerce_path, $active_plugins ) || $active;
	}

	/**
	 * Checks whether to include the plugin files.
	 *
	 * @since 7.0
	 */
	public static function maybe_include_files() {
		self::include_file( 'core/class-files.php' );
		Files::include();
	}

	/**
	 * Return path/URL for asset file.
	 *
	 * @param string $path Path to the asset file.
	 * @param string $plugin The plugin file path to be relative to. Blank string if no plugin is specified.
	 * @since 7.0
	 */
	public static function get_asset_url( $path, $plugin = '' ) {
		$debug = ( defined( 'WCDN_SCRIPT_DEBUG' ) && WCDN_SCRIPT_DEBUG )
			|| ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
			|| self::is_local();

		$is_build = ( 0 === strpos( ltrim( $path, '/' ), 'build/' ) );

		if ( ! $debug && ! $is_build ) {
			$path = preg_replace( '/\.(js|css)$/', '.min.$1', $path );
		}

		return '' === $plugin ? plugins_url( $path ) : plugins_url( $path, $plugin );
	}

	/**
	 * Check if the site is running on a local environment.
	 *
	 * @return bool
	 * @since 7.0
	 */
	protected static function is_local() {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		return in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true )
			|| str_ends_with( $host, '.local' )
			|| str_ends_with( $host, '.test' )
			|| str_ends_with( $host, '.localhost' );
	}

	/**
	 * Initialize and register core services.
	 *
	 * @return void
	 * @since 7.0
	 */
	private function setup_services() {
		$this->services['frontend'] = new Frontend();
		$this->services['backend']  = new Backend();
		$this->services['pdf']      = new \Tyche\WCDN\Services\Pdf();
		$this->services['renderer'] = new \Tyche\WCDN\Services\Template_Renderer();
	}

	/**
	 * Retrieve a registered service instance.
	 *
	 * @param string $service Service identifier.
	 * @return object|null
	 * @since 7.0
	 */
	public function service( $service ) {

		if ( ! isset( $this->services[ $service ] ) ) {
			return null;
		}

		if ( is_callable( $this->services[ $service ] ) ) {
			$this->services[ $service ] = call_user_func( $this->services[ $service ] );
		}

		return $this->services[ $service ];
	}
}
