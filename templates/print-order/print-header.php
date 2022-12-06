<?php
/**
 * Print order header
 *
 * @package WooCommerce Print Invoice & Delivery Note/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title><?php wcdn_document_title(); ?></title>

	<?php
		// wcdn_head hook.
		do_action( 'wcdn_head' );
	if ( 'yes' === get_option( 'wcdn_rtl_invoice', 'no' ) ) {
		?>
		<style>
			body {
				direction: rtl;
			}
			.order-items dt,
			.order-items dd {
				float: right;
			}
			.content{
				text-align:right;	
			}
			th {
				text-align:right;
			}
		</style>
		<?php
	}
	?>
</head>

<body class="<?php echo esc_attr( wcdn_get_template_type() ); ?>">

	<div id="container">

		<?php
			// wcdn_head hook.
			do_action( 'wcdn_before_page' );
		?>

		<div id="page">
