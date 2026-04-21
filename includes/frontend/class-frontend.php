<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Frontend Handler Class.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Frontend
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN;

use Tyche\WCDN\Helpers\Templates;
use Tyche\WCDN\Helpers\Settings;
use Tyche\WCDN\Services\Template_Engine;
use Tyche\WCDN\Api\Templates as Templates_Api;
use Tyche\WCDN\Services\Template_Renderer;
use Tyche\WCDN\Service;
use Tyche\WCDN\Helpers\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend Handler Class.
 *
 * Handles print button rendering, email integration,
 * scripts enqueueing.
 *
 * @since 7.0
 */
class Frontend {

	/**
	 * Constructor.
	 *
	 * @since 7.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoint' ) );
		add_action( 'wp_loaded', array( $this, 'load_hooks' ) );
	}

	/**
	 * Load hooks after theme and plugins are ready.
	 *
	 * @return void
	 * @since 7.0
	 */
	public function load_hooks() {
		add_action( 'parse_request', array( $this, 'parse_request' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect_theme' ) );
		add_action( 'wp_ajax_print_order', array( $this, 'template_redirect_admin' ) );
		add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'create_print_button_account_page' ), 10, 2 );
		add_action( 'woocommerce_view_order', array( $this, 'create_print_button_order_page' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'create_print_button_order_page' ) );
		add_action( 'wcdn_after_items', array( $this, 'wdn_add_extra_data_after_items' ), 10, 1 );
		add_filter( 'woocommerce_get_item_count', array( $this, 'wcdn_order_item_count' ), 20, 3 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_myaccount_styles' ) );
		add_action( 'woocommerce_checkout_create_order', array( $this, 'generate_guest_access_token' ), 10, 1 );
		add_action( 'woocommerce_store_api_checkout_update_order_meta', array( $this, 'generate_guest_access_token' ), 10, 1 );
	}

	/**
	 * Add rewrite endpoint for printing.
	 *
	 * Used on the front-end to generate
	 * print template URLs.
	 *
	 * @return void
	 * @since 7.0
	 */
	public function add_endpoint() {
		add_rewrite_endpoint( Settings::get( 'printEndpoint' ), EP_ROOT | EP_PAGES );

		if ( get_option( 'wcdn_flush_rewrite_rules' ) ) {
			delete_option( 'wcdn_flush_rewrite_rules' );
			flush_rewrite_rules();
		}
	}

	/**
	 * Parse custom endpoint query variables.
	 *
	 * @param \WP $wp WP object.
	 * @return void
	 * @since 7.0
	 */
	public function parse_request( $wp ) {

		$endpoint = Settings::get( 'printEndpoint' );

		if ( isset( $_GET[ $endpoint ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- frontend print endpoint, no nonce applicable
			$wp->query_vars[ $endpoint ] = sanitize_text_field( wp_unslash( $_GET[ $endpoint ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( isset( $wp->query_vars[ $endpoint ] ) ) {
			$wp->query_vars[ $endpoint ] = $wp->query_vars[ $endpoint ];
		}
	}

	/**
	 * Add custom query vars.
	 *
	 * @param array $vars Query variables.
	 * @return array
	 * @since 7.0
	 */
	public function add_query_vars( $vars ) {
		return array_merge(
			$vars,
			array(
				'print-order-type',
				'print-order-email',
			)
		);
	}

	/**
	 * Create print button for My Account page.
	 *
	 * @param array     $actions My Account page actions.
	 * @param \WC_Order $order   Order object.
	 * @return array
	 * @since 7.0
	 */
	public function create_print_button_account_page( $actions, $order ) {

		if ( $order instanceof \WC_Order_Refund ) {
			return $actions;
		}

		$order_id       = wcdn_order_id( $order );
		$template_types = Utils::get_template_types( $order );

		if ( empty( $template_types ) || ! is_account_page() ) {
			return $actions;
		}

		$show_print_button = apply_filters(
			'wcdn_show_print_button_for_order_status',
			true,
			$order
		);

		if ( ! Settings::get( 'showPrintButtonMyAccountPage' ) || ! $show_print_button ) {
			return $actions;
		}

		// ✅ Add one button per template.
		foreach ( $template_types as $type ) {

			if ( ! Templates::get( $type, 'enabled' ) ) {
				continue;
			}

			$actions[ 'wcdn_print_' . $type ] = array(
				'url'  => Utils::get_print_page_url( $order_id, $type ),
				'name' => esc_html(
					Utils::get_label_for_template_type( $type )
				),
			);
		}

		return $actions;
	}

	/**
	 * Create print button for View Order and Thank You pages.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 * @since 7.0
	 */
	public function create_print_button_order_page( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( ! $order || $order instanceof \WC_Order_Refund ) {
			return;
		}

		$template_types = Utils::get_template_types( $order );

		if ( empty( $template_types ) ) {
			return;
		}

		if ( ! Settings::get( 'showViewOrderButton' ) ) {
			return;
		}

		$wdn_order_billing_id = wcdn_woocommerce_version_3_0_0()
		? $order->get_billing_email()
		: $order->billing_email;

		?>
<p class="order-print">
		<?php
		foreach ( $template_types as $type ) :

			if ( ! Templates::get( $type, 'enabled' ) ) {
				continue;
			}

			$print_url = Utils::get_print_page_url( $order_id, $type );

			// Tracking page support.
			if ( $this->is_woocommerce_tracking_page() ) {
				$wdn_order_email = isset( $_REQUEST['order_email'] ) ? sanitize_email( wp_unslash( $_REQUEST['order_email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WooCommerce order tracking page, no nonce applicable

				$print_url = Utils::get_print_page_url( $order_id, $type, $wdn_order_email );
			}

			// Thank you page (guest).
			if ( is_order_received_page() && ! is_user_logged_in() ) {

				if ( ! $wdn_order_billing_id ) {
					continue;
				}

				$print_url = Utils::get_print_page_url( $order_id, $type, $wdn_order_billing_id );
			}
			?>

	<a href="<?php echo esc_url( $print_url ); ?>" class="button print" target="_blank" rel="noopener">
			<?php
				echo esc_html(
					Utils::get_label_for_template_type( $type )
				);
			?>
	</a>

	<?php endforeach; ?>
</p>
		<?php
	}


	/**
	 * Check if current page is WooCommerce order tracking page.
	 *
	 * @return bool
	 * @since 7.0
	 */
	public function is_woocommerce_tracking_page() {
		return ( is_page( wc_get_page_id( 'order_tracking' ) ) && isset( $_REQUEST['order_email'] ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only presence check, no data processed
	}

	/**
	 * Handle template rendering on the front-end.
	 *
	 * @return void
	 * @since 7.0
	 */
	public function template_redirect_theme() {
		global $wp;

		$endpoint = Settings::get( 'printEndpoint' );

		if ( empty( $wp->query_vars[ $endpoint ] ) || ! is_account_page() ) {
			return;
		}

		$type      = ! empty( $wp->query_vars['print-order-type'] ) ? $wp->query_vars['print-order-type'] : null;
		$email     = ! empty( $wp->query_vars['print-order-email'] ) ? $wp->query_vars['print-order-email'] : null;
		$order_ids = array_filter( explode( '-', sanitize_text_field( $wp->query_vars[ $endpoint ] ) ) );

		$this->render_document( $order_ids, $type, $email );

		exit;
	}

	/**
	 * Handle template rendering in the admin.
	 *
	 * Validates permissions and renders document
	 * when triggered via AJAX or endpoint request.
	 *
	 * @return void
	 * @since 7.0
	 */
	public function template_redirect_admin() {

		$endpoint = Settings::get( 'printEndpoint' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.WP.Capabilities.Unknown
		if ( ! is_admin() || ! current_user_can( 'edit_shop_orders' ) || empty( $_REQUEST[ $endpoint ] ) || empty( $_REQUEST['action'] ) ) {
			return;
		}

		$type      = ! empty( $_REQUEST['print-order-type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['print-order-type'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- frontend print page, no nonce applicable
		$email     = ! empty( $_REQUEST['print-order-email'] ) ? sanitize_email( wp_unslash( $_REQUEST['print-order-email'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_ids = isset( $_GET[ $endpoint ] ) ? sanitize_text_field( wp_unslash( $_GET[ $endpoint ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$is_sample = ! empty( $_GET['sample'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- presence check only, value is not used

		$this->render_document( $order_ids, $type, $email );

		exit;
	}

	/**
	 * Render document output.
	 *
	 * Builds template data and outputs
	 * the rendered HTML with controls.
	 *
	 * @param array       $order_ids Order IDs.
	 * @param string|null $template  Template key.
	 * @param string|null $email     Order email.
	 * @return void
	 * @since 7.0
	 */
	protected function render_document( $order_ids, $template, $email ) {

		$documents = array();
		$template  = $template ? $template : 'invoice';

		// Normalize order IDs.
		if ( ! is_array( $order_ids ) ) {
			$order_ids = array_filter(
				explode( '-', $order_ids )
			);
		}

		if ( empty( $order_ids ) ) {
			wp_die( esc_html__( 'No orders provided.', 'woocommerce-delivery-notes' ) );
		}

		foreach ( $order_ids as $order_id ) {

			$is_sample = 'sample' === $order_id;
			$order     = '';

			if ( ! $is_sample ) {

				$order_id = absint( $order_id );
				$order    = wc_get_order( $order_id );

				if ( ! $order ) {
					wp_die( esc_html__( 'Invalid order.', 'woocommerce-delivery-notes' ) );
				}

				// Refund IDs resolve to WC_Order_Refund — use the parent order instead.
				if ( $order instanceof \WC_Order_Refund ) {
					$order = wc_get_order( $order->get_parent_id() );
					if ( ! $order ) {
						wp_die( esc_html__( 'Invalid order.', 'woocommerce-delivery-notes' ) );
					}
				}

				if ( ! $this->can_view_order( $order, $email ) ) {
					wp_die( esc_html__( 'Access denied.', 'woocommerce-delivery-notes' ) );
				}

				// Admins can print any enabled template regardless of order status.
				// The template type restriction only applies to customer-facing access.
				if ( ! current_user_can( 'manage_woocommerce' ) && ! in_array( $template, Utils::get_template_types( $order ), true ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
					wp_die( esc_html__( 'Access to this document is denied.', 'woocommerce-delivery-notes' ) );
				}
			}

			$documents[] = array(
				'order_id' => $order_id,
				'data'     => array(
					'order'    => Templates_Api::format_order_data( $order, $is_sample, $template ),
					'shop'     => Templates_Api::get_store_data(),
					'document' => Templates_Api::get_document_data(),
					'settings' => Templates::template( $template ),
					'template' => $template,
				),
			);
		}

		if ( empty( $documents ) ) {
			wp_die( esc_html__( 'No valid documents found.', 'woocommerce-delivery-notes' ) );
		}

		// Single order → simple HTML render.
		if ( 1 === count( $documents ) ) {

			$html = Template_Renderer::render(
				$template,
				$documents[0]['data'],
				'html'
			);

			$this->output_with_controls(
				array(
					array(
						'order_id' => $documents[0]['order_id'],
						'html'     => $html,
						'data'     => $documents[0]['data'],
					),
				),
				$template
			);

			return;
		}

		// Multiple orders → render all for print view.
		$rendered_documents = array();

		foreach ( $documents as $doc ) {

			$html = Template_Renderer::render(
				$template,
				$doc['data'],
				'html'
			);

			$rendered_documents[] = array(
				'order_id' => $doc['order_id'],
				'html'     => $html,
				'data'     => $doc['data'],
			);
		}

		$this->output_with_controls( $rendered_documents, $template );
	}


	/**
	 * Determine whether the current user can view the order.
	 *
	 * Validates permissions for admin, customer,
	 * guest token, and email-based access.
	 *
	 * @param \WC_Order   $order Order object.
	 * @param string|null $email Billing email for guest access.
	 * @return bool
	 * @since 7.0
	 */
	protected function can_view_order( $order, $email ) {

		$order_id = wcdn_order_id( $order );

		// Admin.
		if ( current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			return true;
		}

		// Guest token.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$token = isset( $_GET['guest_token'] ) ? sanitize_text_field( wp_unslash( $_GET['guest_token'] ) ) : '';
		$saved = $order->get_meta( '_guest_access_token' );

		if ( $token && $saved && hash_equals( $saved, $token ) ) {
			return true;
		}

		// Logged-in customer.
		if ( is_user_logged_in() ) {

			if ( get_current_user_id() === $order->get_customer_id() ) {
				return true;
			}

			// Check if user can edit shop orders or view this specific order.
			if ( current_user_can( 'edit_shop_orders' ) && current_user_can( 'view_order', $order_id ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				return true;
			}
		} else {
			// Non-logged in users require an email match with the order billing email.
			$order_billing_id = wcdn_woocommerce_version_3_0_0() ? $order->get_billing_email() : $order->billing_email;
			if ( empty( $email ) || strtolower( $order_billing_id ) !== $email ) {
				return false;
			}

			// Additional check for user ownership if necessary.
			$is_allowed_for_non_logged_in = apply_filters( 'allow_user_email_order_access', false, $order );

			if ( get_current_user_id() !== $order->get_customer_id() && ! $is_allowed_for_non_logged_in ) {

				$redirect_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url();

				if ( isset( $_GET['need_login_message'] ) && 'true' === $_GET['need_login_message'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag set by this plugin's own redirect
					echo '<div class="notice notice-info"><p>' . esc_html__( 'You need to be logged into your account to access this document. Please login first.', 'woocommerce-delivery-notes' ) . '</p></div>';
					// Display a confirmation button to redirect the user to the login page.
					echo '<a href="' . esc_url( wp_login_url( $redirect_url ) ) . '" class="button">' . esc_html__( 'Proceed to Login', 'woocommerce-delivery-notes' ) . '</a>';
					exit;
				} else {
					wp_safe_redirect( add_query_arg( 'need_login_message', 'true', $redirect_url ) );
					exit;
				}
			}
		}

		return false;
	}

	/**
	 * Output rendered documents wrapped with print controls.
	 *
	 * @param array  $documents Array of rendered documents.
	 * @param string $template  Template key.
	 * @return void
	 * @since 7.0
	 */
	protected function output_with_controls( $documents, $template ) {

		$order_ids = wp_list_pluck( $documents, 'order_id' );

		// Generate merged PDF if multiple.
		$pdf_url = '';

		if ( ! empty( $order_ids ) ) {

			if ( 1 === count( $order_ids ) ) {

				// Single order.
				$pdf_file = Service::pdf()->generate(
					$order_ids[0],
					$template,
					$documents[0]['data']
				);

			} else {

				// Multiple orders.
				$pdf_file = Service::pdf()->generate(
					$order_ids,
					$template,
					$documents
				);
			}

			if ( $pdf_file ) {

				$upload_dir = wp_upload_dir();

				$pdf_url = trailingslashit( $upload_dir['baseurl'] )
					. 'wcdn/'
					. $template
					. '/'
					. basename( $pdf_file );
			}
		}
		?>
<html>

<head>
	<title><?php echo esc_html( Settings::get( 'defaultDocumentLabel' ) ); ?></title>

	<style>
	body {
		background: #f5f5f5;
		margin: 0;
		padding-bottom: 90px;
	}

	.wcdn-container {
		max-width: 900px;
		margin: 40px auto;
	}

	.wcdn-document-wrapper {
		background: #fff;
		padding: 30px;
		margin-bottom: 30px;
		page-break-after: always;
	}

	.wcdn-document-wrapper:last-child {
		page-break-after: auto;
	}

	.wcdn-toolbar {
		position: fixed;
		left: 0;
		width: 100%;
		background: #ffffff;
		box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
		transition: opacity 0.25s ease, transform 0.25s ease;
		z-index: 9999;
		opacity: 0;
		transform: translateY(10px);
		animation: wcdnFadeIn 0.25s ease forwards;
	}

	.wcdn-toolbar-top {
		top: 0;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	}

	.wcdn-toolbar-bottom {
		bottom: 0;
	}

	.wcdn-toolbar-inner {
		max-width: 900px;
		margin: 0 auto;
		padding: 12px 20px;
		display: flex;
		justify-content: center;
		gap: 15px;
		align-items: center;
		flex-wrap: wrap;
	}

	.wcdn-btn {
		padding: 10px 18px;
		border-radius: 6px;
		font-size: 14px;
		font-weight: 500;
		border: none;
		cursor: pointer;
		text-decoration: none;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 120px;
	}

	.wcdn-btn-primary {
		background: #2271b1;
		color: #fff;
	}

	.wcdn-btn-primary:hover {
		background: #1b5c8f;
	}

	.wcdn-btn-secondary {
		background: #f1f1f1;
		color: #333;
		border: 1px solid #bab8b8;
	}

	.wcdn-btn-secondary:hover {
		background: #e0e0e0;
	}

	@keyframes wcdnFadeIn {
		to {
			opacity: 1;
			transform: translateY(0);
		}
	}

	@media (max-width: 768px) {

		body {
			padding-bottom: 120px;
		}

		.wcdn-toolbar-inner {
			flex-direction: column;
			gap: 10px;
		}

		.wcdn-btn {
			width: 100%;
		}
	}

	@media print {

		body {
			background: #fff;
			padding-bottom: 0;
		}

		.wcdn-toolbar {
			display: none;
		}
	}
	</style>
</head>

<body>

	<div class="wcdn-container">

		<?php foreach ( $documents as $doc ) : ?>
		<div class="wcdn-document-wrapper">
			<?php echo $doc['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered HTML from Template_Renderer, escaping would break the document output ?>
		</div>
		<?php endforeach; ?>

	</div>

	<div class="wcdn-toolbar wcdn-toolbar-bottom">
		<div class="wcdn-toolbar-inner">

			<button class="wcdn-btn wcdn-btn-secondary" onclick="window.print()">
				<?php count( $order_ids ) > 1 ? esc_html_e( 'Print All', 'woocommerce-delivery-notes' ) : esc_html_e( 'Print', 'woocommerce-delivery-notes' ); ?>
			</button>

			<?php if ( ! empty( $pdf_url ) ) : ?>
			<a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" class="wcdn-btn wcdn-btn-primary">
				<?php esc_html_e( 'Download PDF', 'woocommerce-delivery-notes' ); ?>
			</a>
			<?php endif; ?>

		</div>
	</div>

</body>

</html>
		<?php
	}

	/**
	 * Add extra data after order items.
	 *
	 * @param \WC_Order $order Order object.
	 * @return void
	 * @since 7.0
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
				$this->cdn_print_local_pickup_address( $cdn_local_pickup_locations, $cdn_local_pickup__shipping_object );
			}
		}
	}

	/**
	 * Print local pickup address.
	 *
	 * @param array  $cdn_local_pickup_locations Pickup locations.
	 * @param object $shipping_method            Shipping method instance.
	 * @return void
	 * @since 7.0
	 */
	public function cdn_print_local_pickup_address( $cdn_local_pickup_locations, $shipping_method ) {

		$package_number = 1;
		$packages_count = count( $cdn_local_pickup_locations );
		foreach ( $cdn_local_pickup_locations as $pickup_meta ) :
			?>
<div>
			<?php if ( $packages_count > 1 ) : ?>
	<h5><?php echo wp_kses_post( sprintf( is_rtl() ? '#%2$s %1$s' : '%1$s #%2$s', esc_html( $shipping_method->get_method_title() ), $package_number ) ); ?>
	</h5>
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
			<?php ++$package_number; ?>
</div>
			<?php
			endforeach;
	}

	/**
	 * Show the correct quantity on the frontend when Composite/Bundle products are there.
	 *
	 * @param int      $count Total Quantity.
	 * @param string   $type Item Type.
	 * @param WC_Order $order Order object.
	 */
	public function wcdn_order_item_count( $count, $type, $order ) {
		global $wp;

		$endpoint = Settings::get( 'printEndpoint' );

		// Check that print button is been clicked or not.
		if ( ! empty( $wp->query_vars[ $endpoint ] ) ) {
			if ( in_array( 'woocommerce-composite-products/woocommerce-composite-products.php', apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) || in_array( 'woocommerce-product-bundles/woocommerce-product-bundles.php', apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) ) {
				if ( function_exists( 'is_account_page' ) && is_account_page() ) {
					$count = 0;
					foreach ( $order->get_items() as $item ) {
						$count += $item->get_quantity();
					}
				}
			}
		}
		return $count;
	}

	/**
	 * Enqueue minimal styles for Print buttons on the My Account page.
	 *
	 * @return void
	 * @since 7.0
	 */
	public function enqueue_myaccount_styles() {

		// Only load on WooCommerce My Account page.
		if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
			return;
		}

		// Register a dummy handle for inline styles (no src or version needed).
		wp_register_style( 'wcdn-myaccount', false, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion

		wp_enqueue_style( 'wcdn-myaccount' );

		wp_add_inline_style(
			'wcdn-myaccount',
			'
		.hentry .entry-content a.button {
			margin: 0.3em;
        '
		);
	}

	/**
	 * Generate Guest Access Token.
	 *
	 * Generates and stores a unique access token for guest users on the order.
	 * This token can be used to securely grant access to order documents
	 * (e.g. print or download pages) without requiring authentication.
	 *
	 * The token is only generated if the current user is not logged in.
	 *
	 * @param \WC_Order $order WooCommerce order object.
	 * @return void
	 * @since 7.0
	 */
	public function generate_guest_access_token( $order ) {
		if ( ! is_user_logged_in() ) {
			$token = $order->get_meta( '_guest_access_token', true );

			if ( ! $token ) {
				$token = bin2hex( random_bytes( 16 ) ); // Generate a random token for the guest user.
				$order->update_meta_data( '_guest_access_token', $token );
				$order->save();
			}
		}
	}
}