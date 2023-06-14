<?php
/**
 * FAQ file.
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

$faq = WCDN_Component::wcdn_get_faq();
?>
<div class="accordion accordion-flush" id="wcdn_faq">
	<?php
	$i = 1;
	foreach ( $faq as $key => $singlefaq ) {
		?>
		<div class="accordion-item">
			<h2 class="accordion-header" id="<?php echo esc_attr( 'wcdn_faq_' . $i ); ?>">
				<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo esc_attr( 'wcdn_faq_content_' . $i ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( 'wcdn_faq_content_' . $i ); ?>">
					<?php echo esc_html( $singlefaq['question'] ); ?>
				</button>
			</h2>
			<div id="<?php echo esc_attr( 'wcdn_faq_content_' . $i ); ?>" class="accordion-collapse collapse" aria-labelledby="<?php echo esc_attr( 'wcdn_faq_' . $i ); ?>" data-bs-parent="#wcdn_faq">
				<?php echo $singlefaq['answer']; ?>
			</div>
		</div>
		<?php
		$i++;
	}
	?>
</div>
