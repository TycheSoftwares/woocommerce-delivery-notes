<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Backend Handler Class.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Admin
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN;

use Tyche\WCDN\Helpers\Templates;
use Tyche\WCDN\Helpers\Settings;
use Tyche\WCDN\Services\Template_Engine;
use Tyche\WCDN\Helpers\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Backend Handler Class.
 *
 * Handles admin order actions, bulk printing,
 * meta boxes, scripts and styles.
 *
 * @since 7.0
 */
class Backend {
	/**
	 * Constructor.
	 *
	 * @since 7.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_hooks' ) );
		add_action( 'admin_notices', array( $this, 'locale_font_notice' ) );
		add_filter( 'wcdn_ts_tracker_data', array( $this, 'tracker_data' ), 10, 1 );
	}

	/**
	 * Load admin hooks.
	 *
	 * @return void
	 * @since 7.0
	 */
	public function admin_hooks() {
		add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_listing_actions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'meta_box' ) );
		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'register_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'bulk_action_handler' ), 10, 3 );
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'register_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', array( $this, 'bulk_action_handler' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'confirm_bulk_actions' ) );
	}

	/**
	 * Add print actions to order listing.
	 *
	 * @param \WC_Order $order Order object.
	 * @return void
	 * @since 7.0
	 */
	public function add_listing_actions( $order ) {

		if ( $order instanceof \WC_Order_Refund ) {
			return;
		}

		$wdn_order_id = wcdn_order_id( $order );
		?>
		<?php foreach ( Template_Engine::get_template_keys() as $template ) : ?>
			<?php if ( Templates::get( $template, 'enabled' ) && in_array( $template, Utils::get_template_types( $order ), true ) ) : ?>
				<?php
						$print_url = apply_filters(
							'wcdn_custom_print_url',
							Utils::get_print_page_url( $wdn_order_id, $template ),
							$wdn_order_id,
							$template
						);
				?>
<a href="<?php echo esc_url( $print_url ); ?>"
	class="button tips print-preview-button <?php echo esc_attr( $template ); ?>" target="_blank"
	alt="<?php echo esc_attr( Utils::template_label( $template )['labels']['print'] ); ?>"
	data-tip="<?php echo esc_attr( Utils::template_label( $template )['labels']['print'] ); ?>">
				<?php echo esc_html( Utils::template_label( $template )['labels']['print'] ); ?>
</a>
	<?php endif; ?>
		<?php endforeach; ?>
		<?php
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @return void
	 * @since 7.0
	 */
	public function add_scripts() {
		if ( $this->is_order_edit_page() || $this->is_order_post_page() ) {
			wp_enqueue_style( 'woocommerce-delivery-notes-admin', WCDN()::get_asset_url( 'assets/css/admin.css', WCDN_FILE ), array(), WCDN_PLUGIN_VERSION );
		}

		wp_enqueue_script(
			'tyche',
			WCDN()::get_asset_url( 'assets/js/tyche.js', WCDN_FILE ),
			array( 'jquery' ),
			WCDN_PLUGIN_VERSION,
			true
		);

		wp_enqueue_script(
			'wcdn_ts_dismiss_notice',
			WCDN()::get_asset_url( 'assets/js/dismiss-tracking-notice.js', WCDN_FILE ),
			array( 'jquery' ),
			WCDN_PLUGIN_VERSION,
			false
		);

		wp_localize_script(
			'wcdn_ts_dismiss_notice',
			'wcdn_ts_dismiss_notice',
			array(
				'ts_prefix_of_plugin' => WCDN_SLUG,
				'ts_admin_url'        => admin_url( 'admin-ajax.php' ),
				'tracking_notice'     => wp_create_nonce( 'tracking_notice' ),
			)
		);
	}

	/**
	 * Check if current screen is order listing page.
	 *
	 * @return bool
	 * @since 7.0
	 */
	public function is_order_edit_page() {
		global $typenow, $pagenow;

		if ( 'shop_order' === $typenow && 'edit.php' === $pagenow ) {
			return true;
		} elseif ( isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading admin URL param for context detection only
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if current screen is single order page.
	 *
	 * @return bool
	 * @since 7.0
	 */
	public function is_order_post_page() {
		global $typenow, $pagenow;

		if ( 'shop_order' === $typenow && ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) ) {
			return true;
		} elseif ( isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] && isset( $_GET['action'] ) && 'new' === $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading admin URL param for context detection only
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add meta box to single order page.
	 *
	 * @return void
	 * @since 7.0
	 */
	public function meta_box() {
		$screen = function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop_order' ) : 'shop_order';
		add_meta_box( 'woocommerce-delivery-notes-box', __( 'Order Printing', 'woocommerce-delivery-notes' ), array( $this, 'create_box_content' ), $screen, 'side', 'low' );
	}

	/**
	 * Render meta box content on single order page.
	 *
	 * @param \WP_Post|\WC_Order $post Post or order object.
	 * @return void
	 * @since 7.0
	 */
	public function create_box_content( $post ) {
		global $post_id;

		$order_id = ( $post instanceof \WP_Post ) ? $post->ID : $post->get_id();
		$order    = wc_get_order( $order_id );

		if ( ! $order || $order instanceof \WC_Order_Refund ) {
			return;
		}
		?>
<div class="print-actions">
		<?php foreach ( Template_Engine::get_template_keys() as $template ) : ?>
			<?php if ( Templates::get( $template, 'enabled' ) && in_array( $template, Utils::get_template_types( $order ), true ) ) : ?>
	<a href="<?php echo esc_url( Utils::get_print_page_url( $order_id, $template ) ); ?>"
		class="button print-preview-button <?php echo esc_attr( $template ); ?>" target="_blank"
		alt="<?php echo esc_attr( Utils::template_label( $template )['labels']['print'] ); ?>"><?php echo esc_attr( Utils::template_label( $template )['labels']['print'] ); ?></a>
	<?php endif; ?>
	<?php endforeach; ?>
</div>
		<?php
			$invoice_number = Utils::get_order_invoice_number( $order_id );
			$invoice_date   = Utils::get_order_invoice_date( $order_id );
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

	/**
	 * Register bulk print actions.
	 *
	 * @param array $bulk_actions Bulk actions array.
	 * @return array
	 * @since 7.0
	 */
	public function register_bulk_actions( $bulk_actions ) {

		if ( Templates::get( 'invoice', 'enabled' ) ) {
			$bulk_actions['wcdn_print_invoice'] = Settings::get( 'invoiceButtonLabel' );
		}

		if ( Templates::get( 'deliverynote', 'enabled' ) ) {
			$bulk_actions['wcdn_print_deliverynote'] = Settings::get( 'deliveryNoteButtonLabel' );
		}

		if ( Templates::get( 'receipt', 'enabled' ) ) {
			$bulk_actions['wcdn_print_receipt'] = Settings::get( 'receiptButtonLabel' );
		}

		if ( Templates::get( 'creditnote', 'enabled' ) ) {
			$bulk_actions['wcdn_print_creditnote'] = Settings::get( 'creditNoteButtonLabel' );
		}

		if ( Templates::get( 'packingslip', 'enabled' ) ) {
			$bulk_actions['wcdn_print_packingslip'] = Settings::get( 'packingSlipButtonLabel' );
		}

		return $bulk_actions;
	}

	/**
	 * Handle bulk print actions.
	 *
	 * @param string $redirect_to Redirect URL.
	 * @param string $doaction    Action being executed.
	 * @param array  $post_ids    Order IDs.
	 * @return string
	 * @since 7.0
	 */
	public function bulk_action_handler( $redirect_to, $doaction, $post_ids ) {

		// Stop if no post IDs.
		if ( empty( $post_ids ) ) {
			return $redirect_to;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';

		// Must start with expected prefix.
		if ( 0 !== strpos( $action, 'wcdn_print_' ) ) {
			return $redirect_to;
		}

		// Extract template type.
		$template = substr( $action, strlen( 'wcdn_print_' ) );

		$allowed_templates = array(
			'invoice',
			'deliverynote',
			'receipt',
			'creditnote',
			'packingslip',
		);

		if ( ! in_array( $template, $allowed_templates, true ) ) {
			return $redirect_to;
		}

		// Get the total number of post IDs.
		$total = count( $post_ids );

		// Generate print URL safely.
		$print_url = Utils::get_print_page_url( $post_ids, $template );

		// WooCommerce orders page URL.
		$orders_page_url = admin_url( 'edit.php?post_type=shop_order' );

		if ( class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) &&
		wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ) {
			$orders_page_url = admin_url( 'admin.php?page=wc-orders' );
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

		$template_label = Utils::template_label( $template )['labels']['name_plural'];
		?>
<div id="wcdn-modal-overlay" class="wcdn-modal-overlay">
	<div class="wcdn-modal" role="dialog" aria-modal="true">

		<h2 class="wcdn-modal-title">
			<?php
			// translators: %1$d: number of documents, %2$s: document type label.
			echo esc_html( sprintf( __( 'Print %1$d %2$s', 'woocommerce-delivery-notes' ), $total, $template_label ) );
			?>
		</h2>

		<p>
			<?php esc_html_e( 'Are you sure you want to proceed?', 'woocommerce-delivery-notes' ); ?>
		</p>

		<div class="wcdn-modal-actions">
			<button type="button" id="wcdn-cancel" class="button">
				<?php esc_html_e( 'Cancel', 'woocommerce-delivery-notes' ); ?>
			</button>

			<button type="button" id="wcdn-confirm" class="button button-primary">
				<?php esc_html_e( 'Print', 'woocommerce-delivery-notes' ); ?>
			</button>
		</div>

	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

	const overlay = document.getElementById('wcdn-modal-overlay');
	const confirmBtn = document.getElementById('wcdn-confirm');
	const cancelBtn = document.getElementById('wcdn-cancel');

	const printUrl = "<?php echo esc_url_raw( $print_url ); ?>";
	const ordersPageUrl = "<?php echo esc_url_raw( $orders_page_url ); ?>";

	function closeModal() {
		window.location.href = ordersPageUrl;
	}

	cancelBtn.addEventListener('click', closeModal);

	confirmBtn.addEventListener('click', function() {

		const printWindow = window.open(printUrl, '_blank');

		if (!printWindow) {
			alert(
				'<?php echo esc_js( __( 'Pop-up blocked. Please allow pop-ups and try again.', 'woocommerce-delivery-notes' ) ); ?>'
			);
			return;
		}

		const interval = setInterval(function() {
			if (printWindow.document.readyState === 'complete') {
				clearInterval(interval);
				printWindow.print();
			}
		}, 500);

		closeModal();
	});

});
</script>

<style>
.wcdn-modal-overlay {
	position: fixed;
	inset: 0;
	background: rgba(0, 0, 0, 0.5);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 9999;
}

.wcdn-modal {
	background: #fff;
	padding: 25px 30px;
	width: 420px;
	border-radius: 6px;
	box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
	font-family: sans-serif;
}

.wcdn-modal-title {
	margin-top: 0;
	margin-bottom: 10px;
	font-size: 18px;
}

.wcdn-modal-actions {
	margin-top: 20px;
	display: flex;
	justify-content: flex-end;
	gap: 10px;
}

.wcdn-modal-actions button {
	font-size: 0.9em;
	padding: 5px 15px;
}
</style>
		<?php
		exit;
	}

	/**
	 * Display confirmation notice after bulk print.
	 *
	 * @return void
	 * @since 7.0
	 */
	public function confirm_bulk_actions() {
		if ( $this->is_order_edit_page() ) {
			foreach ( Template_Engine::get_template_keys() as $template ) {
				if ( isset( $_REQUEST[ 'printed_' . $template ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- bulk action result display, no data processed

					// use singular or plural form.
					$total   = isset( $_REQUEST['total'] ) ? absint( $_REQUEST['total'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only, sanitized with absint
					$message = $total <= 1 ? Utils::template_label( $template )['labels']['message'] : Utils::template_label( $template )['labels']['message_plural'];

					// Print URL - Fix Issue #214: Reflected XSS Vulnerability in Plugin.
					$print_url = isset( $_REQUEST['print_url'] ) ? esc_url_raw( wp_unslash( $_REQUEST['print_url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only after bulk action, no state change

					if ( '' !== $print_url ) {
						?>
<div id="woocommerce-delivery-notes-bulk-print-message" class="updated">
	<p><?php echo wp_kses_post( $message, 'woocommerce-delivery-notes' ); ?>
		<a href="<?php echo esc_url( $print_url ); ?>" target="_blank" class="print-preview-button"
			id="woocommerce-delivery-notes-bulk-print-button"><?php esc_attr_e( 'Print now', 'woocommerce-delivery-notes' ); ?></a>
		<span class="print-preview-loading spinner"></span>
	</p>
</div>
						<?php
					}
					break;
				}
			}
		}
	}

	/**
	 * Append plugin-specific data to the tracker payload. Only feature flags and settings are included.
	 *
	 * @param array $data Existing tracker data.
	 * @return array
	 * @since 7.0
	 */
	public function tracker_data( $data ) {

		$data = array_merge(
			$data,
			array(
				'ts_meta_data_table_name' => 'ts_tracking_wcdn_meta_data',
				'ts_plugin_name'          => WCDN_PLUGIN_NAME,
				'version'                 => WCDN_PLUGIN_VERSION,
				'data'                    => array(),
			)
		);

		$settings = Settings::all();

		$data['data']['plugin_settings'] = array(
			'textDirection'                => $settings['textDirection'] ?? 'ltr',
			'enablePDF'                    => $settings['enablePDF'] ?? false,
			'enablePDFStorage'             => $settings['enablePDFStorage'] ?? false,
			'numberDaysPdfExpiration'      => $settings['numberDaysPdfExpiration'] ?? 7,
			'showCustomerEmailLink'        => $settings['showCustomerEmailLink'] ?? false,
			'showAdminEmailLink'           => $settings['showAdminEmailLink'] ?? false,
			'showViewOrderButton'          => $settings['showViewOrderButton'] ?? false,
			'showPrintButtonMyAccountPage' => $settings['showPrintButtonMyAccountPage'] ?? false,
			'processingTemplate'           => $settings['processingTemplate'] ?? '',
			'processingAuto'               => $settings['processingAuto'] ?? false,
			'processingAttach'             => $settings['processingAttach'] ?? false,
			'completedTemplate'            => $settings['completedTemplate'] ?? '',
			'completedAuto'                => $settings['completedAuto'] ?? false,
			'completedAttach'              => $settings['completedAttach'] ?? false,
			'refundedTemplate'             => $settings['refundedTemplate'] ?? '',
			'refundedAuto'                 => $settings['refundedAuto'] ?? false,
			'refundedAttach'               => $settings['refundedAttach'] ?? false,
			'enablePayNow'                 => $settings['enablePayNow'] ?? false,
			'resetInvoiceNumberYearly'     => $settings['resetInvoiceNumberYearly'] ?? false,
			'startingNumberForEachYear'    => $settings['startingNumberForEachYear'] ?? 1,
		);

		// Per-template: enabled state and key display/PDF flags.
		$template_data = array();

		foreach ( Template_Engine::get_template_keys() as $key ) {
			$template_data[ $key ] = array(
				'enabled'       => (bool) Templates::get( $key, 'enabled' ),
				'showLogo'      => (bool) Templates::get( $key, 'showLogo' ),
				'pdfEnabled'    => (bool) Templates::get( $key, 'pdfEnabled' ),
				'textDirection' => Templates::get( $key, 'textDirection' ),
			);
		}

		$data['data']['template_data'] = $template_data;

		return $data;
	}

	/**
	 * Show an admin notice when the current locale requires a font that has not been uploaded yet.
	 *
	 * The notice is persistent — it reappears on every admin page load until the font is present.
	 *
	 * @return void
	 * @since 7.0
	 */
	public function locale_font_notice() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			return;
		}

		$status = \Tyche\WCDN\Services\Template_Renderer::get_font_admin_status();

		if ( empty( $status['needed'] ) || ! empty( $status['regular'] ) ) {
			return;
		}

		$settings_url = add_query_arg(
			array(
				'page' => 'wcdn_page',
				'tab'  => 'fonts',
			),
			admin_url( 'admin.php' )
		);

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			wp_kses_post(
				sprintf(
					/* translators: 1: font display name, 2: settings page URL */
					__( '<strong>Print Invoice & Delivery Notes:</strong> Your store language requires the <strong>%1$s</strong> font for correct PDF output. <a href="%2$s">Upload it in Font Settings &rarr;</a>', 'woocommerce-delivery-notes' ),
					esc_html( $status['display_name'] ?? '' ),
					esc_url( $settings_url )
				)
			)
		);
	}
}