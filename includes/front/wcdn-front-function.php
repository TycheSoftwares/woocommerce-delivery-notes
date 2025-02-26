<?php
/**
 * Front end Functions
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'vendor/autoload.php';

// Reference the Dompdf namespace.
use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\FontMetrics;

/**
 * Create a invoice pdf by order id.
 *
 * @param array  $order Order Object.
 * @param string $type  Document type.
 *
 * @since 5.0
 */
function create_pdf( $order, $type ) {
	// Get order id from the order object.
	$order_id = $order->get_id();

	// Instantiate and use the dompdf class.
	$options = new \Dompdf\Options();
	$options->set( 'isRemoteEnabled', true );
	$options->set( 'isPhpEnabled', true );
	$dompdf = new Dompdf( $options );

	// Load content from html file.
	ob_start();
	wcdn_get_document_template( $order, $type );
	$html  = ob_get_clean();
	$html .= wcdn_get_pdf_template( $type );
	$dompdf->loadHtml( $html );

	// Setup the paper size and orientation.
	$dompdf->setPaper( 'A4', 'potrait' );

	// Render the HTML as PDF.
	$dompdf->render();

	// Generate unique filename.
	$unique_key = uniqid();
	$name       = 'wcdn_' . $order_id . '_' . $type . '_' . $unique_key . '.pdf';

	// Save the file.
	wcdn_save_document( $type, $name, $dompdf->output() );

	// Store filename in order meta.
	$order->update_meta_data( '_wcdn_' . $type . '_pdf', $name );
	$order->save();

	return $name;
}

/**
 * Get pdf template.
 *
 * @param string $order  Order object.
 * @param string $type  Document type.
 *
 * @since 5.0
 */
function wcdn_get_document_template( $order, $type ) {
	$setting = get_option( 'wcdn_' . $type . '_customization' );
	$setting['template_setting']['template_setting_template'] = get_option( 'wcdn_template_type' );
	$template = $setting['template_setting']['template_setting_template'];
	if ( 'simple' === $template ) {
		include_once WooCommerce_Delivery_Notes::$plugin_path . 'templates/pdf/' . $template . '/' . $type . '/template.php';
	} else {
		include_once WooCommerce_Delivery_Notes::$plugin_path . 'templates/pdf/default/' . $type . '/template.php';
	}
}

/**
 * Get css file.
 *
 * @param string $type Document type.
 *
 * @since 5.0
 */
function wcdn_get_pdf_template( $type ) {
	$setting = get_option( 'wcdn_' . $type . '_customization' );
	$setting['template_setting']['template_setting_template'] = get_option( 'wcdn_template_type' );
	$template   = $setting['template_setting']['template_setting_template'];
	$css_styles = '';
	if ( 'simple' === $template ) {
		$css_file = WooCommerce_Delivery_Notes::$plugin_path . 'templates/pdf/' . $template . '/style.css';
	} else {
		$css_file = WooCommerce_Delivery_Notes::$plugin_path . 'templates/pdf/default/style.css';
	}
	if ( file_exists( $css_file ) ) {
		$css_content = file_get_contents( $css_file ); // phpcs:ignore
		$css_styles  = '<style>' . $css_content . '</style>';
	}
	return $css_styles;
}

/**
 * This function save document in folder.
 *
 * @param string $type Document type.
 * @param string $name Document name.
 * @param string $data Pdf file content.
 *
 * @since 5.0
 */
function wcdn_save_document( $type, $name, $data ) {
	$upload_dir = wp_upload_dir();
	$base_path  = trailingslashit( $upload_dir['basedir'] ) . 'wcdn/';

	// Define the document paths based on type.
	$document_paths = array(
		'invoice'      => $base_path . 'invoice/',
		'receipt'      => $base_path . 'receipt/',
		'deliverynote' => $base_path . 'deliverynote/',
	);

	// Check if the type exists in our paths.
	if ( isset( $document_paths[ $type ] ) ) {
		$document_dir = $document_paths[ $type ];

		// Ensure directory exists.
		if ( ! file_exists( $document_dir ) ) {
			wp_mkdir_p( $document_dir );
		}

		// Save the document.
		file_put_contents( $document_dir . $name, $data ); // phpcs:ignore

		// Add security files (.htaccess & index.html).
		$security_files = array(
			array(
				'file'    => 'index.html',
				'content' => '', // Empty file to prevent directory listing.
			),
			array(
				'file'    => '.htaccess',
				'content' => "Deny from all\n", // Block direct access to the folder.
			),
		);

		foreach ( $security_files as $file ) {
			$file_path = $document_dir . $file['file'];
			if ( ! file_exists( $file_path ) ) {
				file_put_contents( $file_path, $file['content'] ); // phpcs:ignore
			}
		}
	}
}
