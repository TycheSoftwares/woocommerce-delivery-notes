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
	$order_id = $order->id;

	// Instantiate and use the dompdf class.
	
	$options = new \Dompdf\Options();
	$options->set('isRemoteEnabled', TRUE);
	$options->set('isPhpEnabled', TRUE);
	$dompdf = new Dompdf($options);

	// Load content from html file.
	ob_start();
	wcdn_get_document_template( $order, $type );
	$html = ob_get_clean();
	$html .= '<link type="text/css" href="' . esc_url( WooCommerce_Delivery_Notes::$plugin_url . 'templates/pdf/style.css' ) . '" rel="stylesheet" />';
	$dompdf->loadHtml( $html );

	// (Optional) Setup the paper size and orientation.
	$dompdf->setPaper( 'A4', 'landscape' );

	// Render the HTML as PDF.
	$dompdf->render();

	$data = get_option( 'wcdn_receipt_customization' );
	if ( $type == 'receipt' && isset( $data['payment_received_stamp']['active'] ) ) {
		$canvas = $dompdf->getCanvas(); 
		$fontMetrics = new FontMetrics($canvas, $options); 
	 
		// Get height and width of page 
		$w = $canvas->get_width(); 
		$h = $canvas->get_height();

		$font = $fontMetrics->getFont('times'); 
	 
		// Specify watermark text 
		$text = $data['payment_received_stamp']['payment_received_stamp_text']; 
		 
		// Get height and width of text 
		$txtHeight = $fontMetrics->getFontHeight($font, 75); 
		$textWidth = $fontMetrics->getTextWidth($text, $font, 75); 
		 
		// Set text opacity 
		$canvas->set_opacity(.2); 
		 
		// Specify horizontal and vertical position 
		$x = (($w-$textWidth)/2); 
		$y = (($h-$txtHeight)/2); 
		 
		// Writes text at the specified x and y coordinates 
		$canvas->text($x, $y, $text, $font, 75); 
	}

	$output = $dompdf->output();

	$name = wcdn_document_name( $order_id, $type );
	wcdn_save_document( $type, $name, $output );
	return $name;
}

/**
 * Get pdf template.
 *
 * @param array  $order Order Object.
 * @param string $type  Document type.
 *
 * @since 5.0
 */
function wcdn_get_document_template( $order, $type ) {
	$setting = get_option( 'wcdn_' . $type . '_customization' );
	if ( isset( $setting['template_setting']['active'] ) ) {
		$template = $setting['template_setting']['template_setting_template'];
		include_once WooCommerce_Delivery_Notes::$plugin_path . 'templates/pdf/' . $template . '/' . $type . '/template.php';
	} else {
		include_once WooCommerce_Delivery_Notes::$plugin_path . 'templates/pdf/default/' . $type . '/template.php';
	}
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
	if ( 'invoice' === $type ) {
		file_put_contents( $upload_dir['basedir'] . '/wcdn/invoice/' . $name, $data ); // phpcs:ignore
	} elseif ( 'receipt' === $type ) {
		file_put_contents( $upload_dir['basedir'] . '/wcdn/receipt/' . $name, $data ); // phpcs:ignore
	} elseif ( 'deliverynote' === $type ) {
		file_put_contents( $upload_dir['basedir'] . '/wcdn/deliverynote/' . $name, $data ); // phpcs:ignore
	}
}

/**
 * This function returns document name.
 *
 * @param int    $order_id Order Id.
 * @param string $type  Document type.
 *
 * @since 5.0
 */
function wcdn_document_name( $order_id, $type ) {
	$name = 'wcdn_' . $order_id . '_' . $type . '.pdf';
	return $name;
}
