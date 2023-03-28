<?php
/**
 * Admin header load.
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

?>
<div class="wcdn_container">
<?php
$h_tab = array(
	'wcdn_general'   => array( 'General', 'wcdn-general.php' ),
	'wcdn_document'  => array( 'Document', 'wcdn-document.php' ),
	'wcdn_helpguide' => array( 'Help & Guide', 'wcdn-helpguide.php' ),
);
$file  = 'wcdn-general.php';
?>
	<ul class="nav-tabs wcdn_main_tab">
		<li style="padding: 0px 30px;">
			<img src="<?php echo esc_url( WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/invoice-logo.svg' ); ?>">
		</li>
		<?php
		foreach ( $h_tab as $key => $value ) {
			$class = '';
			if ( isset( $_GET['setting'] ) && $_GET['setting'] === $key ) { // phpcs:ignore
				$class = 'active';
				$file  = $value[1];
			} elseif ( ! isset( $_GET['setting'] ) && 'wcdn_general' === $key ) { // phpcs:ignore
				$class = 'active';
			}
			?>
			<li class="nav-item">
				<a class="nav-link <?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( get_admin_url() . 'admin.php?page=wc-settings&tab=wcdn-settings&setting=' . $key ); ?>"><?php echo esc_html( $value[0] ); ?></a>
			</li>
			<?php
		}
		?>
		<li>
			<?php echo esc_html( WooCommerce_Delivery_Notes::$plugin_version ); ?>
		</li>
	</ul>
	<div class="tab_container">
		<?php
			require_once $file;
		?>
	</div>
</div>
