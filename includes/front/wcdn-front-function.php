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

	$output = $dompdf->output();
	$name   = wcdn_document_name( $order_id, $type );
	wcdn_save_document( $type, $name, $output );
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
