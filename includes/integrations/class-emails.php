<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Email Integration Class.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Integrations
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Integrations;

use Tyche\WCDN\Helpers\Templates;
use Tyche\WCDN\Services\Template_Engine;
use Tyche\WCDN\Helpers\Utils;
use Tyche\WCDN\Service;
use Tyche\WCDN\Api\Templates as Templates_Api;
use Tyche\WCDN\Helpers\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Email Integration Class.
 *
 * Handles PDF attachment integration
 * with WooCommerce email notifications.
 *
 * @since 7.0
 */
class Emails {

	/**
	 * Constructor.
	 *
	 * Registers email attachment filter.
	 *
	 * @since 7.0
	 */
	public function __construct() {
		add_filter(
			'woocommerce_email_attachments',
			array( $this, 'attach_pdfs' ),
			10,
			3
		);

		add_action( 'woocommerce_email_after_order_table', array( $this, 'add_email_print_url' ), 100, 3 );
	}

	/**
	 * Check if a template should be attached to the current email.
	 *
	 * @param string    $template Template key.
	 * @param string    $email_id WooCommerce email ID.
	 * @param \WC_Order $order    Order object.
	 *
	 * @return bool
	 * @since 7.0
	 */
	protected function should_attach_template_for_customer( $template, $email_id, $order ) {

		// Customer email.
		if ( ! Templates::get( $template, 'attachCustomerEmail' ) ) {
			return false;
		}

		if ( Templates::get( $template, 'attachToWoocommerceEmails' ) ) {

			// Email type match.
			$allowed_emails = (array) Templates::get( $template, 'woocommerceEmailsToAttachTo' );

			if ( ! empty( $allowed_emails ) && ! in_array( $email_id, $allowed_emails, true ) ) {
				return false;
			}
		}

		if ( Templates::get( $template, 'attachToOrderStatus' ) ) {
			// Order status match.
			$allowed_statuses = (array) Templates::get( $template, 'orderStatusToAttachTo' );

			if ( ! empty( $allowed_statuses ) && ! in_array( $order->get_status(), $allowed_statuses, true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Attach PDFs to WooCommerce emails.
	 *
	 * @param array           $attachments Existing attachments.
	 * @param string          $email_id    WooCommerce email ID.
	 * @param \WC_Order|mixed $order       Order object.
	 * @return array
	 * @since 7.0
	 */
	public function attach_pdfs( $attachments, $email_id, $order ) {

		$upload_dir = wp_upload_dir();
		$filesystem = Utils::get_filesystem();

		if ( $order instanceof \WC_Order_Refund ) {
			$order = wc_get_order( $order->get_parent_id() );
		}

		if ( ! $order instanceof \WC_Order || ! $filesystem ) {
			return $attachments;
		}

		/**
		 * Global toggle for PDF.
		 */
		if ( ! Settings::get( 'enablePDF' ) ) {
			return $attachments;
		}

		$templates = Template_Engine::get_template_keys();

		foreach ( $templates as $template ) {

			$file_name = $order->get_meta( '_wcdn_' . $template . '_pdf' );
			$file_path = $upload_dir['basedir'] . '/wcdn/' . $template . '/' . $file_name;

			// Template enabled.
			if ( ! Templates::get( $template, 'enabled' ) ) {
				continue;
			}

			// Template is valid for order.
			if ( ! in_array( $template, Utils::get_template_types( $order ), true ) ) {
				continue;
			}

			if ( ! $file_name || ! $filesystem->exists( $file_path ) ) {
				/**
				 * Build document data.
				 */
				$data = apply_filters(
					'wcdn_email_template_data',
					$this->build_data( $order, $template ),
					$order,
					$template,
					$email_id
				);

				/**
				 * Generate PDF.
				 */
				$file_path = Service::pdf()->generate(
					wcdn_order_id( $order ),
					$template,
					$data
				);
			}

			if ( $this->should_attach_template_for_customer( $template, $email_id, $order ) ) {
				if ( $filesystem->exists( $file_path ) ) {
					$attachments[] = $file_path;
				}
			}

			// To all Administrators.
			if ( Templates::get( $template, 'attachAdminEmail' ) ) {
				$this->send_to_custom_email( wcdn_get_all_administrator_emails(), $order, $file_path, $template );
			}

			// Custom Email Addresses.
			if ( Templates::get( $template, 'attachCustomEmails' ) ) {
				$this->send_to_custom_email( Templates::get( $template, 'customEmailAddresses' ), $order, $file_path, $template );
			}
		}

		return $attachments;
	}

	/**
	 * Send PDF to custom email addresses.
	 *
	 * Supports multiple email addresses (comma or semicolon separated),
	 * validates them, and sends the PDF as an attachment.
	 *
	 * @param string|array $emails    Email address(es).
	 * @param \WC_Order    $order     Order object.
	 * @param string       $file_path Absolute path to PDF file.
	 * @param string       $template  Template key (e.g. 'invoice', 'receipt').
	 *
	 * @return void
	 * @since 7.0
	 */
	protected function send_to_custom_email( $emails, $order, $file_path, $template ) {

		if ( $order instanceof \WC_Order_Refund ) {
			$order = wc_get_order( $order->get_parent_id() );
			if ( ! $order ) {
				return;
			}
		}

		$already_sent = $order->get_meta( '_email_pdf_sent', true );

		if ( $already_sent || empty( $emails ) || empty( $file_path ) || ! file_exists( $file_path ) || '' === $emails ) {
			return;
		}

		// Normalize to array (supports string or array input).
		if ( ! is_array( $emails ) ) {
			$emails = preg_split( '/[,;]+/', $emails );
		}

		// Clean + validate emails.
		$emails = array_map( 'trim', $emails );
		$emails = array_filter( $emails, 'is_email' );
		$emails = array_unique( $emails );

		if ( empty( $emails ) ) {
			return;
		}

		$document_label = Utils::get_document_name_for_template_type( $template );
		$order_number   = $order->get_order_number();

		$subject = apply_filters(
			'wcdn_custom_email_subject',
			sprintf(
				/* translators: 1. Order number, 2. Document type (e.g. Invoice) */
				__( 'New Order #%1$s — %2$s Attached', 'woocommerce-delivery-notes' ),
				$order_number,
				$document_label
			),
			$order,
			$template
		);

		$body = sprintf(
			/* translators: 1. Document type (e.g. Invoice), 2. Order number */
			__( 'A new order has been placed. Please find the %1$s for Order #%2$s attached to this email.', 'woocommerce-delivery-notes' ),
			$document_label,
			$order_number
		);

		$message = apply_filters( 'wcdn_custom_email_message_body', WC()->mailer()->wrap_message( $subject, $body ), $order, $template );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		/**
		 * Filter custom email recipients.
		 *       * @param array      $emails   Email addresses.
		 *
		 * @param \WC_Order  $order    Order object.
		 * @param string     $template Template key.
		 *
		 * @since 7.0
		 */
		$emails = apply_filters( 'wcdn_custom_email_recipients', $emails, $order, $template );

		wp_mail(
			$emails,
			$subject,
			$message,
			$headers,
			array( $file_path )
		);

		$order->update_meta_data( '_email_pdf_sent', 1 );
		$order->save();
	}

	/**
	 * Build template data for PDF generation.
	 *
	 * @param \WC_Order $order    Order object.
	 * @param string    $template Template key.
	 * @return array
	 * @since 7.0
	 */
	protected function build_data( $order, $template ) {

		return array(
			'order'    => Templates_Api::format_order_data( $order, false, $template ),
			'shop'     => Templates_Api::get_store_data(),
			'document' => Templates_Api::get_document_data(),
			'settings' => Templates::template( $template ),
			'template' => $template,
		);
	}

	/**
	 * Add print URL to order emails.
	 *
	 * @param \WC_Order $order         Order object.
	 * @param bool      $sent_to_admin Whether email is sent to admin.
	 * @param bool      $plain_text    Whether email is plain text.
	 * @return void
	 * @since 7.0
	 */
	public function add_email_print_url( $order, $sent_to_admin = true, $plain_text = false ) {

		if ( $order instanceof \WC_Order_Refund ) {
			$order = wc_get_order( $order->get_parent_id() );
			if ( ! $order ) {
				return;
			}
		}

		$show_customer_email_link = Settings::get( 'showCustomerEmailLink' );
		$show_admin_email_link    = Settings::get( 'showAdminEmailLink' );

		if ( ! $show_customer_email_link && ! $show_admin_email_link ) {
			return;
		}

		$wdn_order_billing_id = wcdn_woocommerce_version_3_0_0()
		? $order->get_billing_email()
		: $order->billing_email;

		$wdn_order_id = wcdn_woocommerce_version_3_0_0()
		? $order->get_id()
		: $order->id;

		$template_types = Utils::get_template_types( $order );

		if ( empty( $template_types ) ) {
			return;
		}

		foreach ( $template_types as $type ) {

			if ( ! Templates::get( $type, 'enabled' ) ) {
				continue;
			}

			$url = Utils::get_print_page_url(
				$wdn_order_id,
				$type,
				$wdn_order_billing_id,
				true
			);

			$label = Utils::get_label_for_template_type( $type );

			// Customer email.
			if ( $show_customer_email_link && ! $sent_to_admin ) {

				if ( $wdn_order_billing_id ) {

					$this->print_link_in_email(
						$plain_text,
						$url,
						Settings::get( 'customerEmailText' ) . ' ' . $label
					);
				}
			}

			// Admin email.
			if ( $show_admin_email_link && $sent_to_admin ) {

				$this->print_link_in_email(
					$plain_text,
					$url,
					Settings::get( 'adminEmailText' ) . ' ' . $label
				);
			}
		}
	}

	/**
	 * Output print link in email.
	 *
	 * @param bool   $plain_text Whether email is plain text.
	 * @param string $url        Print URL.
	 * @param string $link_text  Link text.
	 * @return void
	 * @since 7.0
	 */
	public function print_link_in_email( $plain_text, $url, $link_text ) {

		if ( $plain_text ) {

			echo esc_html( $link_text ) . "\n\n";
			echo esc_url( $url ) . "\n";
			echo "\n****************************************************\n\n";

			return;
		}

		?>
<p>
	<strong>
		<?php
			echo esc_html(
				apply_filters(
					'wcdn_print_text_in_email',
					__( 'Print:', 'woocommerce-delivery-notes' )
				)
			);
		?>
	</strong>

	<a href="<?php echo esc_url( $url ); ?>">
		<?php
			echo esc_html(
				apply_filters(
					'wcdn_print_view_in_browser_text_in_email',
					$link_text
				)
			);
		?>
	</a>
</p>
		<?php
	}
}