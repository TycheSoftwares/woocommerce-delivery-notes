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
			} elseif ( isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] ) {
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
			} elseif ( isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] && isset( $_GET['action'] ) && 'new' === $_GET['action'] ) {
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
					<?php // phpcs:disable ?>
					<a href="<?php echo esc_url( wcdn_get_print_link( $wdn_order_id, $template_registration['type'] ) ); ?>" class="button tips print-preview-button <?php echo esc_attr( $template_registration['type'] ); ?>" target="_blank" alt="<?php esc_attr_e( __( $template_registration['labels']['print'], 'woocommerce-delivery-notes' ) ); ?>" data-tip="<?php esc_attr_e( __( $template_registration['labels']['print'], 'woocommerce-delivery-notes' ) ); ?>">
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

			// security check.
			if ( isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] ) {
				check_admin_referer( 'bulk-orders' );
			} else {
				check_admin_referer( 'bulk-posts' );
			}

			// get referrer.
			if ( ! wp_get_referer() ) {
				return $redirect_to;
			}

			// filter the referer args.
			$referer_args = array();
			parse_str( wp_parse_url( wp_get_referer(), PHP_URL_QUERY ), $referer_args );

			// set the basic args for the sendback.
			$args = array();
			if ( isset( $referer_args['post_status'] ) ) {
				$args = wp_parse_args( array( 'post_status' => $referer_args['post_status'] ), $args );
			}
			if ( isset( $referer_args['paged'] ) ) {
				$args = wp_parse_args( array( 'paged' => $referer_args['paged'] ), $args );
			}
			if ( isset( $referer_args['orderby'] ) ) {
				$args = wp_parse_args( array( 'orderby' => $referer_args['orderby'] ), $args );
			}
			if ( isset( $referer_args['order'] ) ) {
				$args = wp_parse_args( array( 'orderby' => $referer_args['order'] ), $args );
			}

			if ( isset( $referer_args['post_type'] ) ) {
				$args = wp_parse_args( array( 'post_type' => $referer_args['post_type'] ), $args );
			}

			if ( isset( $referer_args['page'] ) ) {
				$args = wp_parse_args( array( 'page' => $referer_args['page'] ), $args );
			}

			// do the action.
			$total = count( $post_ids );
			$url   = wcdn_get_print_link( $post_ids, $template_type );

			// generate more args and the sendback string.
			$args     = wp_parse_args(
				array(
					$report_action => true,
					'total'        => $total,
					'print_url'    => rawurlencode( $url ),
				),
				$args
			);
			$sendback = add_query_arg( $args, '' );
			wp_safe_redirect( $sendback );
			exit;
		}

		/**
		 * Show confirmation message that orders are printed
		 */
		public function confirm_bulk_actions() {
			if ( $this->is_order_edit_page() ) {
				foreach ( WCDN_Print::$template_registrations as $template_registration ) {
					if ( isset( $_REQUEST[ 'printed_' . $template_registration['type'] ] ) ) {
						
						// use singular or plural form.
						$total   = isset( $_REQUEST['total'] ) ? absint( $_REQUEST['total'] ) : 0;
						$message = $total <= 1 ? $message = $template_registration['labels']['message'] : $template_registration['labels']['message_plural'];

						// Print URL - Fix Issue #214: Reflected XSS Vulnerability in Plugin.
						$print_url = isset( $_REQUEST['print_url'] ) ? $_REQUEST['print_url'] : ''; // phpcs:ignore
						$print_url = '' !== $print_url && strtolower( esc_url_raw( $print_url ) ) === strtolower( $print_url ) ? esc_url_raw( $print_url ) : '';

						if ( '' !== $print_url ) {
							?>
							<div id="woocommerce-delivery-notes-bulk-print-message" class="updated">
								<p><?php wp_kses_post( $message, 'woocommerce-delivery-notes' ); ?>
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
			$invoice_data       = get_option( 'wcdn_invoice_customization' );
			$has_invoice_number = $order->get_meta( '_wcdn_invoice_number', true );
			if ( $invoice_data && isset( $invoice_data['numbering']['active'] ) && 'on' === $invoice_data['numbering']['active'] && $has_invoice_number ) :
				$invoice_number = wcdn_get_order_invoice_number( $order_id );
				$invoice_date   = wcdn_get_order_invoice_date( $order_id );
				?>

				<ul class="print-info">
					<li><strong><?php esc_html_e( 'Invoice number: ', 'woocommerce-delivery-notes' ); ?></strong> <?php echo esc_attr( $invoice_number ); ?></li>
					<li><strong><?php esc_html_e( 'Invoice date: ', 'woocommerce-delivery-notes' ); ?></strong> <?php echo esc_attr( $invoice_date ); ?></li>
				</ul>

			<?php endif; ?>
			<?php
		}

	}
}
