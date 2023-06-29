<?php
/**
 * Tyche Softwares.
 *
 * Plugin Deactivation Class.
 *
 * @author      Tyche Softwares
 * @package     TycheSoftwares/PluginDeactivation
 * @category    Classes
 * @since       1.1
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Tyche_Plugin_Deactivation' ) ) {

	/**
	 * Plugin Deactivation.
	 *
	 * @since 1.1
	 */
	class Tyche_Plugin_Deactivation {

		/**
		 * Version.
		 *
		 * @var string $version
		 */
		private $version = '';

		/**
		 * API Url.
		 *
		 * @var string $api_url
		 */
		protected $api_url = 'https://tracking.tychesoftwares.com/v2/';

		/**
		 * Plugin Name.
		 *
		 * @var string $plugin_name
		 */
		private $plugin_name = '';

		/**
		 * Plugin Short Name.
		 *
		 * @var string $plugin_short_name
		 */
		private $plugin_short_name = '';

		/**
		 * Plugin Base.
		 *
		 * @var string $plugin_base
		 */
		private $plugin_base = '';

		/**
		 * JS script file for handling the JS events.
		 *
		 * @var string $script_file
		 */
		private $script_file = '';

		/**
		 * Construct
		 *
		 * @since 1.1
		 * @param array $options Options.
		 */
		public function __construct( $options ) {

			if ( ! $this->init_vars( $options ) ) {
				return;
			}

			add_action( 'admin_print_scripts-plugins.php', array( $this, 'enqueue_scripts' ), 30 );
			add_action( 'wp_ajax_tyche_plugin_deactivation_submit_action', array( &$this, 'tyche_plugin_deactivation_submit_action' ) );
			add_filter( 'plugin_action_links_' . $this->plugin_base, array( &$this, 'plugin_action_links' ) );
		}

		/**
		 * Initialize variables from options array.
		 *
		 * @param array $options Options.
		 *
		 * @since 1.1
		 */
		public function init_vars( $options ) {

			if ( ! is_array( $options ) ) {
				return false;
			}

			if ( ! isset( $options['plugin_name'] ) || ! isset( $options['plugin_base'] ) || ! isset( $options['script_file'] ) || ! isset( $options['plugin_short_name'] ) || ! isset( $options['version'] ) ) {
				return false;
			}

			$this->plugin_name       = $options['plugin_name'];
			$this->plugin_base       = $options['plugin_base'];
			$this->script_file       = $options['script_file'];
			$this->plugin_short_name = $options['plugin_short_name'];
			$this->version           = $options['version'];

			return true;
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @param mixed $links Plugin Action links.
		 *
		 * @return    array
		 * @since 1.1
		 */
		public function plugin_action_links( $links ) {

			if ( isset( $links['deactivate'] ) ) {
				$links['deactivate'] .= '<i class="' . $this->plugin_short_name . ' ts-slug" data-slug="' . $this->plugin_base . '"></i>';
			}

			return $links;
		}

		/**
		 * Enqueue styles and scripts from the tracking server.
		 *
		 * @since 1.1
		 */
		public function enqueue_scripts() {

			wp_enqueue_style(
				'tyche_plugin_deactivation',
				$this->api_url . '/assets/plugin-deactivation/css/style.css',
				array(),
				$this->version
			);

			wp_register_script(
				'tyche_plugin_deactivation_' . $this->plugin_short_name,
				$this->script_file,
				array( 'jquery', 'tyche' ),
				$this->version,
				true
			);

			$request = wp_remote_get( $this->api_url . '?action=fetch-deactivation-data&plugin=' . $this->plugin_short_name . '&language=' . apply_filters( 'tyche_plugin_deactivation_language', 'en' ) );

			if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) ) {
				return false; // In case the user is offline or something else that could have probably caused an error.
			}

			$data = json_decode( wp_remote_retrieve_body( $request ), true );

			if ( ! is_array( $data ) ) {
				return false;
			}

			wp_localize_script(
				'tyche_plugin_deactivation_' . $this->plugin_short_name,
				'tyche_plugin_deactivation_' . $this->plugin_short_name . '_js',
				array(
					'deactivation_data' => $data,
					'ajax_url'          => admin_url( 'admin-ajax.php' ),
					'nonce'             => wp_create_nonce( 'tyche_plugin_deactivation_submit_action' ),
				)
			);

			wp_enqueue_script( 'tyche_plugin_deactivation_' . $this->plugin_short_name );
		}

		/**
		 * Called after the user has submitted his reason for deactivating the plugin.
		 *
		 * @since  1.1
		 */
		public function tyche_plugin_deactivation_submit_action() {

			if ( ! wp_verify_nonce( $_POST['nonce'], 'tyche_plugin_deactivation_submit_action' ) || ! isset( $_POST['reason_id'] ) || ! isset( $_POST['reason_text'] ) ) { // phpcs:ignore
				wp_send_json_error( 0 );
			}

			wp_safe_remote_post(
				$this->api_url,
				array(
					'method'      => 'POST',
					'timeout'     => 60,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => false,
					'headers'     => array( 'user-agent' => 'TSTracker/' . md5( esc_url( home_url( '/' ) ) ) . ';' ),
					'body'        => wp_json_encode(
						array(
							'action'      => 'plugin-deactivation',
							'plugin_slug' => $this->plugin_short_name,
							'url'         => home_url(),
							'email'       => apply_filters( 'ts_tracker_admin_email', get_option( 'admin_email' ) ),
							'plugin_name' => $this->plugin_name,
							'reason_id'   => isset( $_POST['reason_id'] ) ? sanitize_text_field( wp_unslash( $_POST['reason_id'] ) ) : '',
							'reason_text' => isset( $_POST['reason_text'] ) ? sanitize_text_field( wp_unslash( $_POST['reason_text'] ) ) : '',
							'reason_info' => isset( $_POST['reason_info'] ) ? sanitize_text_field( wp_unslash( $_POST['reason_info'] ) ) : '',
						)
					),
				)
			);

			wp_send_json_success();
		}
	}
}
