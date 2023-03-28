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
// Reference the Dompdf namespace 
use Dompdf\Dompdf; 

function create_pdf( $order ) {


	$order_id = $order->id;
	// Instantiate and use the dompdf class 
	$dompdf = new Dompdf();

	// Load content from html file 
	ob_start();
	include_once WooCommerce_Delivery_Notes::$plugin_path.'templates/template.php';
	$html = ob_get_clean(); 
	//$html = file_get_contents(WooCommerce_Delivery_Notes::$plugin_path.'templates/template.php'); 
	$dompdf->loadHtml($html); 
	 
	// (Optional) Setup the paper size and orientation 
	$dompdf->setPaper('A4', 'landscape'); 
	 
	// Render the HTML as PDF 
	$dompdf->render(); 
	$output = $dompdf->output();
	// Output the generated PDF (1 = download and 0 = preview) 
	//$dompdf->stream("codexworld", array("Attachment" => 0));

	$upload_dir = wp_upload_dir();
	$upload_dir['basedir'];
	$name = wcdn_pdf_name($order_id);
	file_put_contents($upload_dir['basedir'].'/wcdn/invoice/'.$name, $output);
}


function wcdn_pdf_name($order_id) {
	$name = 'wcdn_'.$oredr_id.'_invoice.pdf';
	return $name;
}


function wcdn_get_data( $key ) {
	$wcdn_general_settings           = get_option( 'wcdn_general_settings' );
    $wcdn_document_settings          = get_option( 'wcdn_document_settings' );
    $wcdn_invoice_settings           = get_option( 'wcdn_invoice_settings' );
    $wcdn_receipt_settings           = get_option( 'wcdn_receipt_settings' );
    $wcdn_deliverynote_settings      = get_option( 'wcdn_deliverynote_settings' );
    $wcdn_invoice_customization      = get_option( 'wcdn_invoice_customization' );
    $wcdn_recepit_customization      = get_option( 'wcdn_recepit_customization' );
    $wcdn_deliverynote_customization = get_option( 'wcdn_deliverynote_customization' );
    $setting_array = array_merge($wcdn_general_settings,$wcdn_document_settings,$wcdn_invoice_settings,$wcdn_receipt_settings,$wcdn_deliverynote_settings);
    

	if(array_key_exists($key, $setting_array)) {
		echo $key;
	}
	
}
?>
