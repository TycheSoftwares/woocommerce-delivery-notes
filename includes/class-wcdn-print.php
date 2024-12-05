<?php
/**
 * Print class
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print class
 */
if ( ! class_exists( 'WCDN_Print' ) ) {

	/**
	 * WooCommerce Delivery Notes Print class.
	 */
	class WCDN_Print {

		/**
		 * Template registrations
		 *
		 * @var array $template_registrations
		 */
		public static $template_registrations;

		/**
		 * Template styles
		 *
		 * @var array $template_styles
		 */
		public static $template_styles;

		/**
		 * Template locations
		 *
		 * @var array $template_locations
		 */
		public $template_locations;

		/**
		 * Default Template
		 *
		 * @var array $template
		 */
		public $template;

		/**
		 * API Endpoints
		 *
		 * @var array $api_endpoints
		 */
		public $api_endpoints;

		/**
		 * Query vars
		 *
		 * @var array $query_vars
		 */
		public $query_vars;

		/**
		 * Order IDs
		 *
		 * @var array $order_ids
		 */
		public $order_ids;

		/**
		 * Order email
		 *
		 * @var array $order_email
		 */
		public $order_email;

		/**
		 * Orders
		 *
		 * @var array $orders
		 */
		public $orders;

		/**
		 * Constructor
		 */
		public function __construct() {
			// Define the templates.
			self::$template_registrations = apply_filters(
				'wcdn_template_registration',
				array(
					apply_filters(
						'wcdn_template_registration_invoice',
						array(
							'type'   => 'invoice',
							'labels' => array(
								'name'           => __( 'Invoice', 'woocommerce-delivery-notes' ),
								'name_plural'    => __( 'Invoices', 'woocommerce-delivery-notes' ),
								'print'          => __( 'Print Invoice', 'woocommerce-delivery-notes' ),
								'print_plural'   => __( 'Print Invoices', 'woocommerce-delivery-notes' ),
								'message'        => __( 'Invoice created.', 'woocommerce-delivery-notes' ),
								'message_plural' => __( 'Invoices created.', 'woocommerce-delivery-notes' ),
								'setting'        => __( 'Show "Print Invoice" button', 'woocommerce-delivery-notes' ),
							),
						)
					),
					apply_filters(
						'wcdn_template_registration_delivery_note',
						array(
							'type'   => 'delivery-note',
							'labels' => array(
								'name'           => __( 'Delivery Note', 'woocommerce-delivery-notes' ),
								'name_plural'    => __( 'Delivery Notes', 'woocommerce-delivery-notes' ),
								'print'          => __( 'Print Delivery Note', 'woocommerce-delivery-notes' ),
								'print_plural'   => __( 'Print Delivery Notes', 'woocommerce-delivery-notes' ),
								'message'        => __( 'Delivery Note created.', 'woocommerce-delivery-notes' ),
								'message_plural' => __( 'Delivery Notes created.', 'woocommerce-delivery-notes' ),
								'setting'        => __( 'Show "Print Delivery Note" button', 'woocommerce-delivery-notes' ),
							),
						)
					),
					apply_filters(
						'wcdn_template_registration_receipt',
						array(
							'type'   => 'receipt',
							'labels' => array(
								'name'           => __( 'Receipt', 'woocommerce-delivery-notes' ),
								'name_plural'    => __( 'Receipts', 'woocommerce-delivery-notes' ),
								'print'          => __( 'Print Receipt', 'woocommerce-delivery-notes' ),
								'print_plural'   => __( 'Print Receipts', 'woocommerce-delivery-notes' ),
								'message'        => __( 'Receipt created.', 'woocommerce-delivery-notes' ),
								'message_plural' => __( 'Receipts created.', 'woocommerce-delivery-notes' ),
								'setting'        => __( 'Show "Print Receipt" button', 'woocommerce-delivery-notes' ),
							),
						)
					),
				)
			);

			// Add the default template as first item after filter hooks passed.
			array_unshift(
				self::$template_registrations,
				array(
					'type'   => 'order',
					'labels' => array(
						'name'           => __( 'Order', 'woocommerce-delivery-notes' ),
						'name_plural'    => __( 'Orders', 'woocommerce-delivery-notes' ),
						'print'          => __( 'Print Order', 'woocommerce-delivery-notes' ),
						'print_plural'   => __( 'Print Orders', 'woocommerce-delivery-notes' ),
						'message'        => null,
						'message_plural' => null,
						'setting'        => null,
					),
				)
			);

			// Template styles.
			self::$template_styles = apply_filters( 'wcdn_template_styles', array() );

			// Add the default style as first item after filter hooks passed.
			array_unshift(
				self::$template_styles,
				array(
					'name' => __( 'Default', 'woocommerce-delivery-notes' ),
					'type' => 'default',
					'path' => WooCommerce_Delivery_Notes::$plugin_path . 'templates/print-order/',
					'url'  => WooCommerce_Delivery_Notes::$plugin_url . 'templates/print-order/',
				)
			);

			// Default template.
			$this->template = self::$template_registrations[0];

			// Build all template locations.
			$this->template_locations = $this->build_template_locations();

			// Add the endpoint for the frontend.
			$this->api_endpoints = array(
				'print-order' => get_option( 'wcdn_print_order_page_endpoint', 'print-order' ),
			);

			// Insert the query vars.
			$this->query_vars = array(
				'print-order-type',
				'print-order-email',
			);

			// Load the hooks.
			add_action( 'init', array( $this, 'load_hooks' ) );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
			add_action( 'parse_request', array( $this, 'parse_request' ) );
			add_action( 'template_redirect', array( $this, 'template_redirect_theme' ) );
			add_action( 'wp_ajax_print_order', array( $this, 'template_redirect_admin' ) );
			add_action( 'wcdn_after_items', array( $this, 'wdn_add_extra_data_after_items' ), 10, 1 );
		}

		/**
		 * Add extra data after items
		 *
		 * @param object $order Order.
		 */
		public function wdn_add_extra_data_after_items( $order ) {

			/**
			 * Local pickup plus plugin is active
			 */
			if ( class_exists( 'WC_Local_Pickup_Plus' ) ) {

				$cdn_local_pickup_plugin_plugins_version = wc_local_pickup_plus()->get_version();

				if ( version_compare( $cdn_local_pickup_plugin_plugins_version, '2.0.0', '>=' ) ) {
					$cdn_local_pickup_object           = new WC_Local_Pickup_Plus_Orders();
					$local_pickup                      = wc_local_pickup_plus();
					$cdn_local_pickup_locations        = $cdn_local_pickup_object->get_order_pickup_data( $order );
					$cdn_local_pickup__shipping_object = $local_pickup->get_shipping_method_instance();
					self::cdn_print_local_pickup_address( $cdn_local_pickup_locations, $cdn_local_pickup__shipping_object );
				}
			}
		}

		/**
		 * Print Local Pickup Address
		 *
		 * @param array  $cdn_local_pickup_locations Local pickup locations.
		 * @param object $shipping_method Shipping method.
		 */
		public function cdn_print_local_pickup_address( $cdn_local_pickup_locations, $shipping_method ) {

			$package_number = 1;
			$packages_count = count( $cdn_local_pickup_locations );
			foreach ( $cdn_local_pickup_locations as $pickup_meta ) :
				?>
				<div>
					<?php if ( $packages_count > 1 ) : ?>
						<h5><?php echo wp_kses_post( sprintf( is_rtl() ? '#%2$s %1$s' : '%1$s #%2$s', esc_html( $shipping_method->get_method_title() ), $package_number ) ); ?></h5>
					<?php endif; ?>
					<ul>
						<?php foreach ( $pickup_meta as $label => $value ) : ?>
							<li>
								<?php if ( is_rtl() ) : ?>
									<?php echo wp_kses_post( $value ); ?> <strong>:<?php echo esc_html( $label ); ?></strong>
								<?php else : ?>
									<strong><?php echo esc_html( $label ); ?>:</strong> <?php echo wp_kses_post( $value ); ?>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
					<?php $package_number++; ?>
				</div>
				<?php
			endforeach;
		}

		/**
		 * Load the init hooks
		 */
		public function load_hooks() {
			// Add the endpoints.
			$this->add_endpoints();
		}

		/**
		 * Add endpoints for query vars.
		 * the endpoint is used in the front-end to
		 * generate the print template and link.
		 */
		public function add_endpoints() {
			foreach ( $this->api_endpoints as $var ) {
				add_rewrite_endpoint( $var, EP_PAGES );
			}

			// Flush the rules when the transient is set.
			// This is important to make the endpoint work.
			if ( '1' === get_transient( 'wcdn_flush_rewrite_rules' ) ) {
				delete_transient( 'wcdn_flush_rewrite_rules' );
				flush_rewrite_rules();
			}
		}

		/**
		 * Add the query vars to the url
		 *
		 * @param array $vars Query variables.
		 */
		public function add_query_vars( $vars ) {
			foreach ( $this->query_vars as $var ) {
				$vars[] = $var;
			}
			return $vars;
		}

		/**
		 * Parse the query variables
		 *
		 * @param object $wp WP Object.
		 */
		public function parse_request( $wp ) {
			// Map endpoint keys to their query var keys, when another endpoint name was set.
			foreach ( $this->api_endpoints as $key => $var ) {
				if ( isset( $_GET[ $var ] ) ) {
					// changed.
					$wdn_get_end_point_var  = sanitize_text_field( wp_unslash( $_GET[ $var ] ) );
					$wp->query_vars[ $key ] = $wdn_get_end_point_var;
				} elseif ( isset( $wp->query_vars[ $var ] ) ) {
					$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
				}
			}
		}

		/**
		 * Build the template locations
		 */
		public function build_template_locations() {
			$wc_template_directory = WC_TEMPLATE_PATH . 'print-order/';

			// Get the paths for custom styles.
			$settings_type = get_option( 'wcdn_template_style' );
			$settings_path = null;
			$settings_url  = null;
			if ( isset( $settings_type ) && 'default' !== $settings_type ) {
				foreach ( self::$template_styles as $template_style ) {
					if ( $settings_type === $template_style['type'] ) {
						$settings_path = $template_style['path'];
						$settings_url  = $template_style['url'];
						break;
					}
				}
			}

			// Build the locations.
			$locations = array(
				'child_theme' => array(
					'path' => trailingslashit( get_stylesheet_directory() ) . $wc_template_directory,
					'url'  => trailingslashit( get_stylesheet_directory_uri() ) . $wc_template_directory,
				),

				'theme'       => array(
					'path' => trailingslashit( get_template_directory() ) . $wc_template_directory,
					'url'  => trailingslashit( get_template_directory_uri() ) . $wc_template_directory,
				),

				'settings'    => array(
					'path' => $settings_path,
					'url'  => $settings_url,
				),

				'plugin'      => array(
					'path' => self::$template_styles[0]['path'],
					'url'  => self::$template_styles[0]['url'],
				),
			);

			return $locations;
		}

		/**
		 * Template handling in the front-end
		 */
		public function template_redirect_theme() {
			global $wp;
			// Check the page url and display the template when on my-account page.
			if ( ! empty( $wp->query_vars['print-order'] ) && is_account_page() ) {
				$type  = ! empty( $wp->query_vars['print-order-type'] ) ? $wp->query_vars['print-order-type'] : null;
				$email = ! empty( $wp->query_vars['print-order-email'] ) ? $wp->query_vars['print-order-email'] : null;
				$this->generate_template( $wp->query_vars['print-order'], $type, $email );
				exit;
			}
		}

		/**
		 * Template handling in the back-end
		 */
		public function template_redirect_admin() {
			// Let the backend only access the page.
			// changed.
			if ( is_admin() && current_user_can( 'edit_shop_orders' ) && ! empty( $_REQUEST['print-order'] ) && ! empty( $_REQUEST['action'] ) ) {
				$type  = ! empty( $_REQUEST['print-order-type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['print-order-type'] ) ) : null;
				$email = ! empty( $_REQUEST['print-order-email'] ) ? sanitize_email( wp_unslash( $_REQUEST['print-order-email'] ) ) : null;
				// changed.
				$wdn_get_print_order = isset( $_GET['print-order'] ) ? sanitize_text_field( wp_unslash( $_GET['print-order'] ) ) : '';
				$this->generate_template( $wdn_get_print_order, $type, $email );
				exit;
			}
			exit;
		}

		/**
		 * Generate the template
		 *
		 * @param array  $order_ids Order IDs.
		 * @param string $template_type Template type.
		 * @param string $order_email  Order email.
		 */
		public function generate_template( $order_ids, $template_type = 'order', $order_email = null ) {
			global $post, $wp;

			// Explode the ids when needed.
			if ( ! is_array( $order_ids ) ) {
				$this->order_ids = array_filter( explode( '-', $order_ids ) );
			}

			// Set the current template.
			foreach ( self::$template_registrations as $template_registration ) {
				if ( $template_type === $template_registration['type'] ) {
					$this->template = $template_registration;
					break;
				}
			}

			// Set the email.
			if ( empty( $order_email ) ) {
				$this->order_email = null;
			} else {
				$this->order_email = strtolower( $order_email );
			}

			// Create the orders and check permissions.
			$populated = $this->populate_orders();

			// Only continue if the orders are populated.
			if ( ! $populated ) {
				die();
			}

			// Load the print template html.
			$location = $this->get_template_file_location( 'print-order.php' );
			$args     = array();
			wc_get_template( 'print-order.php', $args, $location, $location );
			exit;
		}

		/**
		 * Find the location of a template file.
		 *
		 * @param string  $name Template name.
		 * @param boolean $url_mode URL mode.
		 */
		public function get_template_file_location( $name, $url_mode = false ) {
			$found = '';
			foreach ( $this->template_locations as $template_location ) {
				if ( isset( $template_location['path'] ) && file_exists( trailingslashit( $template_location['path'] ) . $name ) ) {
					if ( $url_mode ) {
						$found = $template_location['url'];
					} else {
						$found = $template_location['path'];
					}
					break;
				}
			}
			return $found;
		}

		/**
		 * Get print page url
		 *
		 * @param array   $order_ids Order ids.
		 * @param string  $template_type Template type.
		 * @param string  $order_email Order email.
		 * @param boolean $permalink Permalink.
		 */
		public function get_print_page_url( $order_ids, $template_type = 'order', $order_email = null, $permalink = false ) {
			// Explode the ids when needed.
			if ( ! is_array( $order_ids ) ) {
				$order_ids = array_filter( explode( '-', $order_ids ) );
			}

			// Build the args.
			$args = array();

			// Set the template type arg.
			foreach ( self::$template_registrations as $template_registration ) {
				if ( $template_type === $template_registration['type'] && 'order' !== $template_type ) {
					$args = wp_parse_args( array( 'print-order-type' => $template_type ), $args );
					break;
				}
			}

			// Set the email arg.
			if ( ! empty( $order_email ) ) {
				$args = wp_parse_args( array( 'print-order-email' => $order_email ), $args );
			}

			// Generate the url.
			$order_ids_slug = implode( '-', $order_ids );

			// Check for guest access token in the order meta.
			$guest_token = '';
			foreach ( $order_ids as $order_id ) {
				$order = wc_get_order( $order_id );
				if ( $order && ! is_user_logged_in() ) {
					$guest_token = $order->get_meta( '_guest_access_token' );
					if ( $guest_token ) {
						break; // If we found a token, we can stop searching.
					}
				}
			}

			// Create another url depending on where the user prints. This prevents some issues with ssl when the my-account page is secured with ssl but the admin isn't.
			if ( is_admin() && current_user_can( 'edit_shop_orders' ) && false === $permalink ) {
				// For the admin we use the ajax.php for better security.
				$args     = wp_parse_args( array( 'action' => 'print_order' ), $args );
				$base_url = admin_url( 'admin-ajax.php' );
				$endpoint = 'print-order';

				// Add the order ids and create the url.
				$url = add_query_arg( $endpoint, $order_ids_slug, $base_url );
			} else {
				// For the theme.
				$base_url = wc_get_page_permalink( 'myaccount' );
				$endpoint = $this->api_endpoints['print-order'];

				// Add the order ids and create the url.
				if ( get_option( 'permalink_structure' ) ) {
					$url = trailingslashit( trailingslashit( $base_url ) . $endpoint . '/' . $order_ids_slug );
				} else {
					$url = add_query_arg( $endpoint, $order_ids_slug, $base_url );
				}
			}

			// Add all other args.
			$url = add_query_arg( $args, $url );

			// If a guest token exists, add it as a query parameter AFTER the email.
			if ( $guest_token ) {
				$url = add_query_arg( 'guest_token', $guest_token, $url );
			}

			return esc_url( $url );
		}

		/**
		 * Create the orders list and check the permissions.
		 */
		private function populate_orders() {
			$this->orders = array();

			// Get the orders.
			$posts = wc_get_orders(
				array(
					'limit'       => -1,
					'orderby'     => 'date',
					'order'       => 'DESC',
					'post__in'    => $this->order_ids,
					'post_status' => 'any',
				)
			);

			// All orders should exist.
			if ( count( $posts ) !== count( $this->order_ids ) ) {
				$this->orders = null;
				return false;
			}

			// Check permissons of the user to determine if the orders should be populated.
			foreach ( $posts as $post ) {
				$order = $post;

				$wdn_order_id = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) ? $order->get_id() : $order->id;
				// Allow admins to view all orders.
				if ( current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore
					$this->orders[ $wdn_order_id ] = $order;
					continue;
				}

				// Check if the guest token exists in the URL.
				$guest_token_from_url = isset( $_GET['guest_token'] ) ? sanitize_text_field( $_GET['guest_token'] ) : ''; // phpcs:ignore

				// If the guest token is present, bypass ownership check.
				if ( ! is_user_logged_in() && ! empty( $guest_token_from_url ) ) {
					// Check if the guest token matches the order's token.
					$order_guest_token = $order->get_meta( '_guest_access_token' );
					if ( $order_guest_token === $guest_token_from_url ) {
						// If the token matches, allow the order to be populated.
						$this->orders[ $wdn_order_id ] = $order;
						continue;
					}
				}

				// Logged in users.
				if ( is_user_logged_in() ) {
					// Check if user can edit shop orders or view this specific order.
					if ( ! current_user_can( 'edit_shop_orders' ) && ! current_user_can( 'view_order', $wdn_order_id ) ) { // phpcs:ignore
						$this->orders = null;
						return false;
					}
				} else {
					// Not logged in users require an email match with the order billing email.
					$wdn_order_billing_id = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ? $order->get_billing_email() : $order->billing_email );
					if ( empty( $this->order_email ) || strtolower( $wdn_order_billing_id ) !== $this->order_email ) {
						$this->orders = null;
						return false;
					}
				}

				// Additional check for user ownership if necessary.
				if ( ! is_user_logged_in() || ( get_current_user_id() !== $order->get_customer_id() ) ) {
					$this->orders = null;
					if ( isset( $_SERVER['REQUEST_URI'] ) ) {
						$redirect_url = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
					} else {
						$redirect_url = home_url();
					}
					if ( isset( $_GET['need_login_message'] ) && $_GET['need_login_message'] === 'true' ) { // phpcs:ignore
						echo '<div class="notice notice-info"><p>' . __( 'You need to be logged into your account to access the Invoice. Please login first.' ) . '</p></div>'; // phpcs:ignore
						// Display a confirmation button to redirect the user to the login page.
						echo '<a href="' . wp_login_url( $redirect_url ) . '" class="button">Proceed to Login</a>'; // phpcs:ignore
						exit;
					} else {
						wp_safe_redirect( add_query_arg( 'need_login_message', 'true', $redirect_url ) );
						exit;
					}
				}
				// Save the order to get it without an additional database call.
				$this->orders[ $wdn_order_id ] = $order;
			}
			return true;
		}

		/**
		 * Get the order.
		 *
		 * @param int $order_id Order id.
		 */
		public function get_order( $order_id ) {
			if ( isset( $this->orders[ $order_id ] ) ) {
				return $this->orders[ $order_id ];
			}
			return false;
		}

		/**
		 * Get the order invoice number.
		 *
		 * @param int $order_id Order id.
		 */
		public function get_order_invoice_number( $order_id ) {
			$invoice_count  = intval( get_option( 'wcdn_invoice_number_count', 1 ) );
			$invoice_prefix = get_option( 'wcdn_invoice_number_prefix' );
			$invoice_suffix = get_option( 'wcdn_invoice_number_suffix' );
			$order          = wc_get_order( $order_id );

			// Add the invoice number to the order when it doesn't yet exist.
			$meta_key       = '_wcdn_invoice_number';
			$invoice_number = $order->get_meta( $meta_key, true );

			if ( '' === $invoice_number ) {
				$meta_added = $order->add_meta_data( $meta_key, $invoice_prefix . $invoice_count . $invoice_suffix, true );
				$order->save();
				// Update the total count.
				update_option( 'wcdn_invoice_number_count', $invoice_count + 1 );
			}

			// Get the invoice number.
			return apply_filters( 'wcdn_order_invoice_number', $order->get_meta( $meta_key, true ) );
		}

		/**
		 * Get the order invoice date.
		 *
		 * @param int $order_id Order id.
		 */
		public function get_order_invoice_date( $order_id ) {
			$order = wc_get_order( $order_id );

			// Add the invoice date to the order when it doesn't yet exist.
			$meta_key = '_wcdn_invoice_date';
			// Get the invoice date.
			$meta_date = $order->get_meta( $meta_key, true );
			if ( '' === $meta_date ) {
				$meta_added = $order->add_meta_data( $meta_key, time(), true );
				$order->save();
			}

			$formatted_date = date_i18n( get_option( 'date_format' ), $meta_date );
			return apply_filters( 'wcdn_order_invoice_date', $formatted_date, $meta_date );
		}

	}

}
?>
