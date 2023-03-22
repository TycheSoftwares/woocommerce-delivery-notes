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
	
	 
	// Instantiate and use the dompdf class 
	$dompdf = new Dompdf();


	// Load content from html file 
	ob_start();
	include_once WooCommerce_Delivery_Notes::$plugin_path.'templates/template.php';
	$html=ob_get_clean(); 
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
	file_put_contents($upload_dir['basedir'].'/wcdn/invoice/abc.pdf', $output);

}

?>
