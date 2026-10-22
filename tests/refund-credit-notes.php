<?php
/**
 * Real WooCommerce credit-note regression checks, run with wp eval-file.
 *
 * Requires an explicitly opted-in, disposable local/development site and an
 * existing private WCDN_TEST_ARTIFACT_DIR outside the web root. No mail is sent.
 *
 * @package WCDN
 */

use Tyche\WCDN\Api\Templates as Templates_Api;
use Tyche\WCDN\Helpers\Helper;
use Tyche\WCDN\Helpers\Settings;
use Tyche\WCDN\Helpers\Templates;
use Tyche\WCDN\Helpers\Utils;
use Tyche\WCDN\Service;
use Tyche\WCDN\Services\Template_Renderer;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || '1' !== getenv( 'WCDN_RUN_REFUND_TESTS' )
	|| ! in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) {
	throw new RuntimeException( 'Run explicitly on a disposable local/development WordPress site.' );
}

if ( ! class_exists( Templates_Api::class ) || ! function_exists( 'wc_create_refund' ) ) {
	throw new RuntimeException( 'Activate WooCommerce and Print Invoice & Delivery Notes first.' );
}

$artifact_path = getenv( 'WCDN_TEST_ARTIFACT_DIR' );
$artifacts     = $artifact_path ? realpath( $artifact_path ) : false;
$web_root      = realpath( dirname( ABSPATH ) );
$filesystem    = Utils::get_filesystem();
if ( ! $artifacts || ! $filesystem instanceof WP_Filesystem_Direct || ! $filesystem->is_dir( $artifacts ) || ! $filesystem->is_writable( $artifacts )
	|| ( $web_root && 0 === strpos( $artifacts . DIRECTORY_SEPARATOR, $web_root . DIRECTORY_SEPARATOR ) ) ) {
	throw new RuntimeException( 'Provide an existing writable private artifact directory outside the web root.' );
}

$results       = array();
$orders        = array();
$product       = null;
$marker        = 'wcdn-refund-test-' . wp_generate_uuid4();
$old_user      = get_current_user_id();
$option_names  = array( 'wcdn_settings', 'wcdn_template_settings', 'wcdn_invoice_number_counter' );
$saved_options = array();
foreach ( $option_names as $name ) {
	$saved_options[ $name ] = get_option( $name, null );
}

$reset_helpers = static function () {
	foreach ( array( array( Helper::class, 'settings' ), array( Helper::class, 'raw_settings' ), array( Templates::class, 'template_settings' ) ) as $property ) {
		$reflection = new ReflectionProperty( $property[0], $property[1] );
		$reflection->setAccessible( true );
		$reflection->setValue( null, null );
	}
};
$check = static function ( $name, $passed ) use ( &$results ) {
	$results[ $name ] = (bool) $passed;
	WP_CLI::log( ( $passed ? 'PASS ' : 'FAIL ' ) . $name );
};
$custom_mail = array();
$capture_mail = static function ( $result, $atts ) use ( &$custom_mail ) {
	if ( in_array( 'credit-note-tests@example.invalid', (array) $atts['to'], true ) ) {
		$custom_mail[] = $atts['attachments'];
	}
	return true;
};
$private_uploads = static function ( $dir ) use ( $artifacts ) {
	$dir['basedir'] = $artifacts . '/uploads';
	$dir['path']    = $dir['basedir'] . $dir['subdir'];
	$dir['baseurl'] = 'https://example.invalid/wcdn-refund-tests';
	$dir['url']     = $dir['baseurl'] . $dir['subdir'];
	return $dir;
};
add_filter( 'pre_wp_mail', $capture_mail, PHP_INT_MAX, 2 );
add_filter( 'woocommerce_defer_transactional_emails', '__return_false', PHP_INT_MAX );
add_filter( 'upload_dir', $private_uploads );

$build_data = static function ( $order, $template ) {
	return array(
		'order'    => Templates_Api::format_order_data( $order, false, $template ),
		'shop'     => Templates_Api::get_store_data(),
		'document' => Templates_Api::get_document_data(),
		'settings' => Templates::template( $template ),
		'template' => $template,
	);
};

try {
	$settings                        = Settings::all();
	$settings['storeName']           = 'Refund regression store';
	$settings['storeLogo']           = '';
	$settings['enablePDF']           = true;
	$settings['enablePayNow']        = false;
	$settings['invoiceNumberFormat'] = '{order_number}';
	$settings['pdfUseCurrencyCode']  = true;
	update_option( 'wcdn_settings', $settings );

	$templates = Templates::all();
	foreach ( $templates as $type => &$config ) {
		$config['enabled']                      = in_array( $type, array( 'invoice', 'creditnote' ), true );
		$config['attachCustomerEmail']          = true;
		$config['attachAdminEmail']             = false;
		$config['attachCustomEmails']           = 'creditnote' === $type;
		$config['customEmailAddresses']         = 'credit-note-tests@example.invalid';
		$config['attachToOrderStatus']          = false;
		$config['attachToWoocommerceEmails']    = true;
		$config['woocommerceEmailsToAttachTo'] = 'invoice' === $type
			? array( 'customer_completed_order' ) : array( 'customer_partially_refunded_order', 'customer_refunded_order' );
		$config['displayRefundItemsInTable']    = true;
		$config['displayPriceInProductDetailsTable'] = true;
	}
	unset( $config );
	update_option( 'wcdn_template_settings', $templates );
	$reset_helpers();

	$product = new WC_Product_Simple();
	$product->set_name( 'Original ten-euro product' );
	$product->set_regular_price( '10' );
	$product->set_status( 'private' );
	$product->update_meta_data( '_wcdn_refund_test', $marker );
	$product->save();

	$order = wc_create_order( array( 'status' => 'pending' ) );
	if ( is_wp_error( $order ) ) {
		throw new RuntimeException( 'Cannot create fixture order.' );
	}
	$orders[] = $order->get_id();
	$order->update_meta_data( '_wcdn_refund_test', $marker );
	$order->set_billing_email( 'refund-tests@example.invalid' );
	$order->set_currency( 'EUR' );
	$item_id = $order->add_product( $product, 1 );
	$order->calculate_totals( false );
	$order->save();
	$order->update_status( 'completed' );

	$invoice_data = $build_data( $order, 'invoice' );
	$invoice      = Service::pdf()->generate( $order->get_id(), 'invoice', $invoice_data );
	$invoice_hash = hash_file( 'sha256', $invoice );

	$refunds = array();
	foreach ( array( 3, 2, 1, 4 ) as $index => $amount ) {
		$args = array(
			'order_id'       => $order->get_id(),
			'amount'         => $amount,
			'reason'         => 'Refund regression ' . ( $index + 1 ),
			'refund_payment' => false,
			'restock_items'  => false,
		);
		if ( 2 === $index ) {
			$args['line_items'] = array( $item_id => array( 'qty' => 0.1, 'refund_total' => 1, 'refund_tax' => array() ) );
		}
		$refund = wc_create_refund( $args );
		if ( is_wp_error( $refund ) ) {
			throw new RuntimeException( 'Cannot create fixture refund.' );
		}
		$orders[] = $refund->get_id();
		$refund->update_meta_data( '_wcdn_refund_test', $marker );
		$refund->save();
		$refunds[] = $refund;

		// Use WooCommerce's real email object and get_attachments() filter path.
		$emails         = WC()->mailer()->get_emails();
		$email          = $emails['WC_Email_Customer_Refunded_Order'];
		$email->object  = wc_get_order( $order->get_id() );
		$email->refund  = $refund;
		$email->id      = 3 === $index ? 'customer_refunded_order' : 'customer_partially_refunded_order';
		$attachments    = $email->get_attachments();
		$credit_files   = array_values( array_filter( $attachments, static function ( $file ) {
			return false !== strpos( $file, '/wcdn/creditnote/' );
		} ) );
		$check( 'refund_' . $index . '_has_one_attachment', 1 === count( $credit_files ) );
		if ( 1 !== count( $credit_files ) ) {
			throw new RuntimeException( 'Expected a single credit-note attachment.' );
		}
		$file = $credit_files[0];
		$check( 'refund_' . $index . '_is_pdf', '%PDF-' === substr( $filesystem->get_contents( $file ), 0, 5 ) );
		$filesystem->copy( $file, $artifacts . '/refund-' . $index . '.pdf', true );
		$stored_refund = wc_get_order( $refund->get_id() );
		$check( 'refund_' . $index . '_custom_delivery_scoped_to_refund', (bool) $stored_refund->get_meta( '_email_pdf_sent_custom_creditnote', true ) );

		if ( 0 === $index ) {
			$first_file = $file;
			$first_hash = hash_file( 'sha256', $file );
		} else {
			$check( 'refund_' . $index . '_does_not_reuse_first_file', $file !== $first_file );
			$check( 'refund_' . $index . '_preserves_first_pdf', hash_file( 'sha256', $first_file ) === $first_hash );
		}

		$data = $build_data( $refund, 'creditnote' );
		$check( 'refund_' . $index . '_keeps_explicit_identity', ( $data['order']['refund']['id'] ?? 0 ) === $refund->get_id() );
		if ( 2 !== $index ) {
			$check( 'refund_' . $index . '_amount_only_row_matches_refund', $data['order']['refund']['items'][0]['total'] === $data['order']['refund']['total'] );
		}
		$html = Template_Renderer::render( 'creditnote', $data, 'html' );
		$check( 'refund_' . $index . '_renders_refund_total', false !== strpos( $html, $data['order']['refund']['total'] ) );
	}

	// A resend must select the older refund, even after a newer full refund exists.
	$email->refund = $refunds[0];
	$resend        = $email->get_attachments();
	$check( 'resend_selects_original_refund_pdf', in_array( $first_file, $resend, true ) );
	$check( 'resend_preserves_original_bytes', hash_file( 'sha256', $first_file ) === $first_hash );
	$check( 'custom_delivery_once_per_refund_not_per_order', 4 === count( $custom_mail ) );
	$check( 'invoice_pdf_unchanged', hash_file( 'sha256', $invoice ) === $invoice_hash );

	$first_data = $build_data( $refunds[0], 'creditnote' );
	$check( 'older_refund_data_not_replaced_by_latest', 'Refund regression 1' === $first_data['order']['refund']['reason'] );
	$check( 'no_original_product_price_in_amount_only_row', 'Original ten-euro product' !== $first_data['order']['refund']['items'][0]['name'] );
} catch ( Throwable $error ) {
	$check( 'execution_exception_' . get_class( $error ), false );
	WP_CLI::warning( $error->getMessage() );
} finally {
	foreach ( array_reverse( $orders ) as $id ) {
		$fixture = wc_get_order( $id );
		if ( $fixture && $marker === $fixture->get_meta( '_wcdn_refund_test' ) ) {
			$fixture->delete( true );
		}
	}
	if ( $product && $marker === $product->get_meta( '_wcdn_refund_test' ) ) {
		$product->delete( true );
	}
	foreach ( $saved_options as $name => $value ) {
		if ( null === $value ) {
			delete_option( $name );
		} else {
			update_option( $name, $value );
		}
	}
	$reset_helpers();
	wp_set_current_user( $old_user );
	remove_filter( 'pre_wp_mail', $capture_mail, PHP_INT_MAX );
	remove_filter( 'woocommerce_defer_transactional_emails', '__return_false', PHP_INT_MAX );
	remove_filter( 'upload_dir', $private_uploads );
}

$filesystem->put_contents( $artifacts . '/results.json', wp_json_encode( $results, JSON_PRETTY_PRINT ), 0600 );
$failed = count( array_filter( $results, static function ( $passed ) { return ! $passed; } ) );
if ( $failed ) {
	WP_CLI::error( sprintf( '%d of %d refund checks failed.', $failed, count( $results ) ) );
}
WP_CLI::success( sprintf( '%d refund checks passed; fixtures removed and settings restored.', count( $results ) ) );
