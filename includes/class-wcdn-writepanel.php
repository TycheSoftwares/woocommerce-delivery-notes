<?php
/**
 * Write Panel class
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
 * Writepanel class
 */
if ( ! class_exists( 'WCDN_Writepanel' ) ) {

	/**
	 * Write Panel class
	 */
	class WCDN_Writepanel {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Load the hooks.
			add_action( 'admin_init', array( $this, 'load_admin_hooks' ) );
		}

		/**
		 * Load the admin hooks
		 */
		public function load_admin_hooks() {
			// Hooks.
			add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_listing_actions' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_styles' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_box' ) );
			add_filter( 'bulk_actions-edit-shop_order', array( $this, 'register_my_bulk_actions' ) );
			add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'my_bulk_action_handler' ), 10, 3 );
			add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'register_my_bulk_actions' ) );
			add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', array( $this, 'my_bulk_action_handler' ), 10, 3 );

			add_action( 'admin_notices', array( $this, 'confirm_bulk_actions' ) );
		}

		/**
		 * Add the styles
		 */
		public function add_styles() {
			if ( $this->is_order_edit_page() || $this->is_order_post_page() ) {
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_style( 'woocommerce-delivery-notes-admin', WooCommerce_Delivery_Notes::$plugin_url . 'assets/css/admin.css', '', WooCommerce_Delivery_Notes::$plugin_version );
			}
		}

		/**
		 * Add the scripts
		 */
		public function add_scripts() {
			if ( $this->is_order_edit_page() || $this->is_order_post_page() ) {
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_script( 'woocommerce-delivery-notes-print-link', WooCommerce_Delivery_Notes::$plugin_url . 'assets/js/jquery.print-link.js', array( 'jquery' ), WooCommerce_Delivery_Notes::$plugin_version, false );
				wp_enqueue_script( 'woocommerce-delivery-notes-admin', WooCommerce_Delivery_Notes::$plugin_url . 'assets/js/admin.js', array( 'jquery', 'woocommerce-delivery-notes-print-link' ), WooCommerce_Delivery_Notes::$plugin_version, false );
			}
		}

		/**
		 * Is order edit page
		 */
		public function is_order_edit_page() {
			global $typenow, $pagenow;
			if ( 'shop_order' === $typenow && 'edit.php' === $pagenow ) {
				return true;
			} elseif ( isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] ) { // phpcs:ignore
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Is order edit page
		 */
		public function is_order_post_page() {
			global $typenow, $pagenow;
			if ( 'shop_order' === $typenow && ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) ) {
				return true;
			} elseif ( isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] && isset( $_GET['action'] ) && 'new' === $_GET['action'] ) { // phpcs:ignore
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Add print actions to the orders listing
		 *
		 * @param object $order Order Object.
		 */
		public function add_listing_actions( $order ) {

			$wdn_order_id = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) ? $order->get_id() : $order->id;
			?>
			<?php foreach ( WCDN_Print::$template_registrations as $template_registration ) : ?>
				<?php if ( 'yes' === get_option( 'wcdn_template_type_' . $template_registration['type'] ) && 'order' !== $template_registration['type'] ) : ?>
					<?php // phpcs:disable
						$print_url = apply_filters(
							'wcdn_custom_print_url',
							wcdn_get_print_link( $wdn_order_id, $template_registration['type'] ),
							$wdn_order_id,
							$template_registration['type']
						);
						?>
						<a href="<?php echo esc_url( $print_url ); ?>
						" class="button tips print-preview-button <?php echo esc_attr( $template_registration['type'] ); ?>" target="_blank" alt="<?php esc_attr_e( __( $template_registration['labels']['print'], 'woocommerce-delivery-notes' ) ); ?>" data-tip="<?php esc_attr_e( __( $template_registration['labels']['print'], 'woocommerce-delivery-notes' ) ); ?>">
						<?php esc_html_e( $template_registration['labels']['print'], 'woocommerce-delivery-notes' ); ?>
					</a>
					<?php // phpcs:enable ?>
				<?php endif; ?>
			<?php endforeach; ?>
			<span class="print-preview-loading spinner"></span>
			<?php
		}

		/**
		 * Add bulk actions to the dropdown.
		 *
		 * @param array $bulk_actions Array of the list in dropdown.
		 */
		public function register_my_bulk_actions( $bulk_actions ) {
			$bulk_actions['wcdn_print_invoice']       = apply_filters( 'wcdn_change_text_of_print_invoice_in_bulk_option', __( 'Print Invoice', 'woocommerce-delivery-notes' ) );
			$bulk_actions['wcdn_print_delivery-note'] = apply_filters( 'wcdn_change_text_of_print_delivery_note_in_bulk_option', __( 'Print Delivery Note', 'woocommerce-delivery-notes' ) );
			$bulk_actions['wcdn_print_receipt']       = apply_filters( 'wcdn_change_text_of_print_receipt_in_bulk_option', __( 'Print Receipt', 'woocommerce-delivery-notes' ) );
			return $bulk_actions;
		}

		/**
		 * Add bulk print actions to the orders listing
		 *
		 * @param string $redirect_to The redirect URL.
		 * @param string $doaction    The action being taken.
		 * @param array  $post_ids    Array of an IDs.
		 */
		public function my_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {

			// stop if there are no post ids.
			if ( empty( $post_ids ) ) {
				return $redirect_to;
			}
			// stop if the action is not of bulk printing.
			if ( ! in_array( $_REQUEST['action'], array( 'wcdn_print_invoice', 'wcdn_print_delivery-note', 'wcdn_print_receipt' ) ) ) { // phpcs:ignore
				return $redirect_to;
			}
			// only for specified actions.
			foreach ( WCDN_Print::$template_registrations as $template_registration ) {
				if ( 'wcdn_print_' . $template_registration['type'] === $doaction ) {
					$template_type = $template_registration['type'];
					$report_action = 'printed_' . $template_registration['type'];
					break;
				}
			}
			if ( ! isset( $report_action ) ) {
				return $redirect_to;
			}

			// Get the total number of post IDs.
			$total        = count( $post_ids );
			$print_url    = htmlspecialchars_decode( wcdn_get_print_link( $post_ids, $template_type ) );
			$templatetype = ucwords( str_replace( '-', ' ', $template_type ) );

			// WooCommerce orders page URL.
			if ( class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) &&
				wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ) {
				$orders_page_url = admin_url( 'admin.php?page=wc-orders' );
			} else {
				$orders_page_url = admin_url( 'edit.php?post_type=shop_order' );
			}

			if ( is_plugin_active( 'sales-by-state/sales-by-state.php' ) ) {
				// Check if the XML Content Type header is set and remove it.
				$headers = headers_list();
				foreach ( $headers as $header ) {
					if ( stripos( $header, 'Content-Type: application/xml' ) !== false ) {
						header_remove( 'Content-Type' );
						header( 'Content-Type: text/html; charset=utf-8' );
						break;
					}
				}
			}

			// Output the modal with Vue.js.
			// phpcs:disable
			?>
			<div id="custom-modal-app">
				<div v-if="showModal" class="custom-modal">
					<div class="custom-modal-content">
						<!-- Modal Header -->
						<div class="custom-modal-header">
						<h1>Print {{ templateType }}</h1>
							<button class="custom-modal-close" @click="closeModal()">×</button>
						</div>

						<!-- Modal Body -->
						<div class="custom-modal-body">
						<p>Ready to print {{ totalOrders }} {{ pluralizedTemplate }}.</p>
						</div>

						<!-- Modal Footer -->
						<div class="custom-modal-footer">
							<button class="cancel-button button" @click="closeModal()">Cancel</button>
							<button class="primary-button button" @click="printDocument()">Print</button>
						</div>
					</div>
				</div>
			</div>
			<script src="<?php echo esc_url( WooCommerce_Delivery_Notes::$plugin_url . 'assets/js/vue.js' ); ?>"></script>
			<script>
				document.addEventListener("DOMContentLoaded", function() {
					new Vue({
						el: "#custom-modal-app",
						data: {
							showModal: true,
							totalOrders: "<?php echo esc_js( ucfirst( $total ) ); ?>",
							templateType: "<?php echo esc_js( ucfirst( $templatetype ) ); ?>",
							printUrl: "<?php echo htmlspecialchars_decode( wcdn_get_print_link( $post_ids, $template_type ) ); // phpcs:ignore ?>",
							ordersPageUrl: "<?php echo esc_url( $orders_page_url ); ?>"
						},
						computed: {
							pluralizedTemplate() {
								let templateMapping = {
									"Invoice": "Invoices",
									"Delivery Note": "Delivery Notes",
									"Receipt": "Receipts"
								};

								if (this.totalOrders > 1 && templateMapping[this.templateType]) {
									return templateMapping[this.templateType];
								} else {
									return this.templateType;
								}
							}
						},
						methods: {
							closeModal() {
								this.showModal = false;
								window.location.href = this.ordersPageUrl; // Redirect to orders page
							},
							printDocument() {
								let printWindow = window.open(this.printUrl, "_blank");
								if (!printWindow) {
									alert("Pop-up blocked! Please allow pop-ups and try again.");
									return;
								}
								let interval = setInterval(function () {
									if (printWindow.document.readyState === "complete") {
										clearInterval(interval);
										printWindow.print();
									}
								}, 500);
								this.closeModal();
							}
						}
					});
				});
			</script>

			<style>
				.custom-modal {
					display: flex;
					justify-content: center;
					align-items: center;
					position: fixed;
					z-index: 1000;
					left: 0;
					top: 0;
					width: 100%;
					height: 100%;
					background-color: rgba(0, 0, 0, 0.5);
				}

				.custom-modal-content {
					font-family: "HelveticaNeue", Helvetica, Arial, sans-serif;
					background-color: #fff;
					padding: 20px 20px 0px 20px;
					width: 623px;
					height: 160px;
					position: relative;
					display: flex;
					flex-direction: column;
				}

				.custom-modal-header {
					display: flex;
					justify-content: space-between;
					align-items: center;
					border-bottom: 1px solid #ddd;
					padding-bottom: 10px;
				}

				.custom-modal-header h1 {
					font-size: 18px;
					margin: 0;
				}

				.custom-modal-close {
					cursor: pointer;
					font-size: 20px;
					border: none;
					background: none;
					color: #777;
				}

				.custom-modal-body {
					padding: 25px 0px 0px 0px;
				}

				.custom-modal-body p {
					font-size: 14px;
				}

				.custom-modal-footer {
					display: flex;
					justify-content: flex-end;
					gap: 10px;
					padding-top: 10px;
					border-top: 1px solid #ddd;
				}

				.custom-modal-footer .button {
					padding: 10px 15px;
					border: none;
					border-radius: 5px;
					cursor: pointer;
				}

				.custom-modal-footer .primary-button {
					background-color:rgb(8, 114, 163);
					color: white;
				}

				.custom-modal-footer .cancel-button {
					background-color: #f1f1f1;
					color: black;
				}

				/* Button hover effects */
				.primary-button:hover {
					background-color: #005177;
				}
				.primary-button button {
					width : 10px;
				}

				.cancel-button:hover {
					background-color: #ddd;
				}
				.primary-button {
					width: 70px;
				}
			</style>
			<?php
			exit; // Prevent further processing.
		}

		/**
		 * Show confirmation message that orders are printed
		 */
		public function confirm_bulk_actions() {
			if ( $this->is_order_edit_page() ) {
				foreach ( WCDN_Print::$template_registrations as $template_registration ) {
					if ( isset( $_REQUEST[ 'printed_' . $template_registration['type'] ] ) ) { // phpcs:ignore

						// use singular or plural form.
						$total   = isset( $_REQUEST['total'] ) ? absint( $_REQUEST['total'] ) : 0; // phpcs:ignore
						$message = $total <= 1 ? $message = $template_registration['labels']['message'] : $template_registration['labels']['message_plural'];

						// Print URL - Fix Issue #214: Reflected XSS Vulnerability in Plugin.
						$print_url = isset( $_REQUEST['print_url'] ) ? $_REQUEST['print_url'] : ''; // phpcs:ignore
						$print_url = '' !== $print_url && strtolower( esc_url_raw( $print_url ) ) === strtolower( $print_url ) ? esc_url_raw( $print_url ) : '';

						if ( '' !== $print_url ) {
							?>
							<div id="woocommerce-delivery-notes-bulk-print-message" class="updated">
								<p><?php echo wp_kses_post( $message, 'woocommerce-delivery-notes' ); ?>
								<a href="<?php echo $print_url; // phpcs:ignore ?>" target="_blank" class="print-preview-button" id="woocommerce-delivery-notes-bulk-print-button"><?php esc_attr_e( 'Print now', 'woocommerce-delivery-notes' ); ?></a> <span class="print-preview-loading spinner"></span></p>
							</div>
							<?php
						}
						break;
					}
				}
			}
		}

		/**
		 * Add the meta box on the single order page
		 */
		public function add_box() {
			if ( function_exists( 'wc_get_page_screen_id' ) ) {
				$screen = wc_get_page_screen_id( 'shop_order' );
			} else {
				$screen = 'shop_order';
			}
			add_meta_box( 'woocommerce-delivery-notes-box', __( 'Order Printing', 'woocommerce-delivery-notes' ), array( $this, 'create_box_content' ), $screen, 'side', 'low' );
		}

		/**
		 * Create the meta box content on the single order page
		 */
		public function create_box_content( $post ) {
			global $post_id, $wcdn;

			$order_id = ( $post instanceof WP_Post ) ? $post->ID : $post->get_id();
			$order    = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}
			?>
			<div class="print-actions">
				<?php foreach ( WCDN_Print::$template_registrations as $template_registration ) : ?>
					<?php if ( 'yes' === get_option( 'wcdn_template_type_' . $template_registration['type'] ) && 'order' !== $template_registration['type'] ) : ?>
						<?php // phpcs:disable ?>
						<a href="<?php echo esc_url( wcdn_get_print_link( $order_id, $template_registration['type'] ) ); ?>" class="button print-preview-button <?php echo esc_attr( $template_registration['type'] ); ?>" target="_blank" alt="<?php esc_attr_e( __( $template_registration['labels']['print'], 'woocommerce-delivery-notes' ) ); ?>"><?php esc_attr_e( $template_registration['labels']['print'], 'woocommerce-delivery-notes' ); ?></a>
						<?php // phpcs:enable ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<span class="print-preview-loading spinner"></span>
			</div>
			<?php
			$invoice_data = get_option( 'wcdn_invoice_customization' );
			if ( $invoice_data && isset( $invoice_data['numbering']['active'] ) && 'on' === $invoice_data['numbering']['active'] ) {
				$has_invoice_number = $order->get_meta( '_wcdn_invoice_number', true );
				if ( $has_invoice_number ) {
					$invoice_number = wcdn_get_order_invoice_number( $order_id );
					$invoice_date   = wcdn_get_order_invoice_date( $order_id );
					?>
					<ul class="print-info">
						<li><strong><?php esc_html_e( 'Invoice number: ', 'woocommerce-delivery-notes' ); ?></strong> 
							<?php echo esc_html( $invoice_number ); ?>
						</li>
						<li><strong><?php esc_html_e( 'Invoice date: ', 'woocommerce-delivery-notes' ); ?></strong> 
							<?php echo esc_html( $invoice_date ); ?>
						</li>
					</ul>
					<?php
				}
			}
		}
	}
}
