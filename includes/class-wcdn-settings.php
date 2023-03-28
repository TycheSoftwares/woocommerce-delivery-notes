<?php
/**
 * Settings class
 *
 * @package woocommerce-print-invoice-delivery-notes
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings class
 */
if ( ! class_exists( 'WCDN_Settings' ) ) {

	/**
	 * WooCommerce Print Delivery Notes
	 *
	 * @author Tyche Softwares
	 * @package WooCommerce-Delivery-Notes/Settings
	 */
	class WCDN_Settings {

		/**
		 * Id variable
		 *
		 * @var int $id ID variable
		 */
		public $id;

		/**
		 * Constructor
		 */
		public function __construct() {
			// Define default variables.
			$this->id = 'wcdn-settings';

			// Load the hooks.
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 200 );
			add_action( 'woocommerce_settings_start', array( $this, 'add_assets' ) );
			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'wp_ajax_wcdn_remove_shoplogo', array( $this, 'wcdn_remove_shoplogo' ) );
			add_action( 'wp_ajax_nopriv_wcdn_remove_shoplogo', array( $this, 'wcdn_remove_shoplogo' ) );
			add_action( 'woocommerce_admin_field_link', array( &$this, 'wcdn_add_admin_field_reset_button' ) );
		}

		/**
		 * It will add a reset tracking data button on the settting page.
		 *
		 * @hook woocommerce_admin_field_link
		 *
		 * @param string $value Check if tracking is reset.
		 */
		public static function wcdn_add_admin_field_reset_button( $value ) {
			if ( 'ts_reset_tracking' === $value ['id'] ) {
				do_action( 'wcdn_add_new_settings', $value );
			}
		}

		/**
		 * Add the scripts
		 */
		public function add_assets() {
			// Styles.
			wp_enqueue_style( 'woocommerce-delivery-notes-admin', WooCommerce_Delivery_Notes::$plugin_url . 'assets/css/admin.css', '', WooCommerce_Delivery_Notes::$plugin_version );

			if ( isset( $_GET['tab'] ) && 'wcdn-settings' === $_GET['tab'] ) { // phpcs:ignore
				wp_enqueue_style( 'woocommerce-delivery-notes-bootstrap-style', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css', '', WooCommerce_Delivery_Notes::$plugin_version );
				wp_enqueue_style( 'woocommerce-delivery-notes-select2-style', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css', '', WooCommerce_Delivery_Notes::$plugin_version );
			}

			// Scripts.
			wp_enqueue_media();
			wp_enqueue_script( 'woocommerce-delivery-notes-print-link', WooCommerce_Delivery_Notes::$plugin_url . 'assets/js/jquery.print-link.js', array( 'jquery' ), WooCommerce_Delivery_Notes::$plugin_version, false );
			wp_enqueue_script( 'woocommerce-delivery-notes-admin', WooCommerce_Delivery_Notes::$plugin_url . 'assets/js/admin.js', array( 'jquery', 'custom-header', 'woocommerce-delivery-notes-print-link' ), WooCommerce_Delivery_Notes::$plugin_version, false );
			wp_localize_script(
				'woocommerce-delivery-notes-admin',
				'admin_object',
				array(
					'ajax_url'  => admin_url( 'admin-ajax.php' ),
					'admin_url' => admin_url(),
				)
			);

			if ( isset( $_GET['tab'] ) && 'wcdn-settings' == $_GET['tab'] ) { // phpcs:ignore
				wp_enqueue_script( 'woocommerce-delivery-notes-bootstrap-script', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), WooCommerce_Delivery_Notes::$plugin_version, false );
				wp_enqueue_script( 'woocommerce-delivery-notes-select2-script', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js', array( 'jquery' ), WooCommerce_Delivery_Notes::$plugin_version, false );
			}

			// Localize the script strings.
			$translation = array( 'resetCounter' => __( 'Do you really want to reset the counter to zero? This process can\'t be undone.', 'woocommerce-delivery-notes' ) );
			wp_localize_script( 'woocommerce-delivery-notes-admin', 'WCDNText', $translation );
		}

		/**
		 * Create a new settings tab
		 *
		 * @param array $settings_tabs Add settings tab in WooCommerce->Settings page.
		 */
		public function add_settings_page( $settings_tabs ) {
			$settings_tabs[ $this->id ] = __( 'Print', 'woocommerce-delivery-notes' );
			return $settings_tabs;
		}

		/**
		 * Output the settings fields into the tab.
		 */
		public function output() {
			include_once __DIR__ . '/admin/views/wcdn-header.php';
		}

		/**
		 * Ajax Call For remove shop logo.
		 */
		public function wcdn_remove_shoplogo() {
			if ( ! empty( $_POST['shop_logoid'] ) ) { // phpcs:ignore
				update_option( 'wcdn_company_logo_image_id', '' );
				wp_delete_attachment( $_POST['shop_logoid'] ); // phpcs:ignore
			}
		}

		/**
		 * Generate the template type setting fields
		 *
		 * @param array  $settings Settings fields.
		 * @param string $section Section name.
		 */
		public function generate_template_type_fields( $settings, $section = '' ) {
			$position = $this->get_setting_position( 'wcdn_email_print_link', $settings );
			if ( false !== $position ) {
				$new_settings = array();

				// Go through all registrations but remove the default 'order' type.
				$template_registrations = WCDN_Print::$template_registrations;
				array_splice( $template_registrations, 0, 1 );
				$end = count( $template_registrations ) - 1;
				foreach ( $template_registrations as $index => $template_registration ) {
					$title         = '';
					$desc_tip      = '';
					$checkboxgroup = '';

					// Define the group settings.
					if ( 0 === $index ) {
						$title         = __( 'Admin', 'woocommerce-delivery-notes' );
						$checkboxgroup = 'start';
					} elseif ( $index === $end ) {
						$desc_tip      = __( 'The print buttons are available on the order listing and on the order detail screen.', 'woocommerce-delivery-notes' );
						$checkboxgroup = 'end';
					}

					// Create the setting.
					$new_settings[] = array(
						'title'         => $title,
						'desc'          => $template_registration['labels']['setting'],
						'id'            => 'wcdn_template_type_' . $template_registration['type'],
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => $checkboxgroup,
						'desc_tip'      => $desc_tip,
					);
				}

				// Add the settings.
				$settings = $this->array_merge_at( $settings, $new_settings, $position );
			}

			return $settings;
		}

		/**
		 * Generate the description for the template settings.
		 */
		public function get_template_description() {
			$description = '';
			$args        = array(
				'post_type'      => 'shop_order',
				'post_status'    => array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed' ),
				'posts_per_page' => 1,
			);
			$query       = new WP_Query( $args );

			// show template preview links when an order is available.
			if ( $query->have_posts() ) {
				$results           = $query->get_posts();
				$test_id           = $results[0]->ID;
				$invoice_url       = wcdn_get_print_link( $test_id, 'invoice' );
				$delivery_note_url = wcdn_get_print_link( $test_id, 'delivery-note' );
				$receipt_url       = wcdn_get_print_link( $test_id, 'receipt' );
				/* translators: %s: invoice url, delivery note url, receipt url */
				$description = sprintf( __( 'This section lets you customise the content. You can preview the <a href="%1$s" target="%4$s" class="%5$s">invoice</a>, <a href="%2$s" target="%4$s" class="%5$s">delivery note</a> or <a href="%3$s" target="%4$s" class="%5$s">receipt</a> template.', 'woocommerce-delivery-notes' ), $invoice_url, $delivery_note_url, $receipt_url, '_blank', '' );
			}

			return $description;
		}

		/**
		 * Generate the options for the template styles field.
		 */
		public function get_options_styles() {
			$options = array();

			foreach ( WCDN_Print::$template_styles as $template_style ) {
				if ( is_array( $template_style ) && isset( $template_style['type'] ) && isset( $template_style['name'] ) ) {
					$options[ $template_style['type'] ] = $template_style['name'];
				}
			}

			return $options;
		}

		/**
		 * Load image with ajax.
		 */
		public function load_image_ajax() {
			// Verify the nonce.
			if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'woocommerce-settings' ) ) {
				die();
			}

			// Verify the id.
			if ( empty( $_POST['attachment_id'] ) && is_numeric( $_POST['attachment_id'] ) ) {
				die();
			}

			$wdn_image_attachmnet_id = sanitize_text_field( wp_unslash( $_POST['attachment_id'] ) );
			// create the image.
			$this->create_image( $wdn_image_attachmnet_id );

			exit;
		}

		/**
		 * Create image.
		 *
		 * @param int $attachment_id Attachment ID.
		 */
		public function create_image( $attachment_id ) {
			$attachment_src = wp_get_attachment_image_src( $attachment_id, 'medium', false );
			$orientation    = 'landscape';
			if ( ( $attachment_src[1] / $attachment_src[2] ) < 1 ) {
				$orientation = 'portrait';
			}

			?>
			<img src="<?php echo esc_url( $attachment_src[0] ); ?>" class="<?php echo esc_attr( $orientation ); ?>" alt="" />
			<?php
		}

		/**
		 * Output image select field.
		 *
		 * @param array $value Title.
		 */
		public function output_image_select( $value ) {
			// Define the defaults.
			if ( ! isset( $value['title_select'] ) ) {
				$value['title_select'] = __( 'Select', 'woocommerce-delivery-notes' );
			}

			if ( ! isset( $value['title_remove'] ) ) {
				$value['title_remove'] = __( 'Remove', 'woocommerce-delivery-notes' );
			}

			// Get additional data fields.
			$field        = WC_Admin_Settings::get_field_description( $value );
			$description  = $field['description'];
			$tooltip_html = $field['tooltip_html'];
			$option_value = WC_Admin_Settings::get_option( $value['id'], $value['default'] );
			$class_name   = 'wcdn-image-select';

			?>
			<tr valign="top">


				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $tooltip_html ); ?></label>
				</th>
				<td class="forminp image_width_settings">
					<input name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" type="hidden" value="<?php echo esc_attr( $option_value ); ?>" class="<?php echo esc_attr( $class_name ); ?>-image-id <?php echo esc_attr( $value['class'] ); ?>" />

					<div id="<?php echo esc_attr( $value['id'] ); ?>_field" class="<?php echo esc_attr( $class_name ); ?>-field <?php echo esc_attr( $value['class'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>">
						<span id="<?php echo esc_attr( $value['id'] ); ?>_spinner" class="<?php echo esc_attr( $class_name ); ?>-spinner spinner"></span>
						<div id="<?php echo esc_attr( $value['id'] ); ?>_attachment" class="<?php echo esc_attr( $class_name ); ?>-attachment <?php echo esc_attr( $value['class'] ); ?> ">
							<div class="thumbnail">
								<div class="centered">
								<?php if ( ! empty( $option_value ) ) : ?>
									<?php $this->create_image( $option_value ); ?>
								<?php endif; ?>
								</div>
							</div>
						</div>

						<div id="<?php echo esc_attr( $value['id'] ); ?>_buttons" class="<?php echo esc_attr( $class_name ); ?>-buttons <?php echo esc_attr( $value['class'] ); ?>">
							<a href="#" id="<?php echo esc_attr( $value['id'] ); ?>_remove_button" class="<?php echo esc_attr( $class_name ); ?>-remove-button 
												<?php
												if ( empty( $option_value ) ) :
													?>
								hidden<?php endif; ?> button">
								<?php echo esc_html( $value['title_remove'] ); ?>
							</a>
							<a href="#" id="<?php echo esc_attr( $value['id'] ); ?>_add_button" class="<?php echo esc_attr( $class_name ); ?>-add-button 
													<?php
													if ( ! empty( $option_value ) ) :
														?>
								hidden<?php endif; ?> button" data-uploader-title="<?php echo esc_attr( $value['title'] ); ?>" data-uploader-button-title="<?php echo esc_attr( $value['title_select'] ); ?>">
								<?php echo esc_html( $value['title_select'] ); ?>
							</a>
						</div>
					</div>

					<?php echo wp_kses_post( $description ); ?>
				</td>
			</tr><?php
		}

		/**
		 * Merge array at given position.
		 *
		 * @param array $array Parent array.
		 * @param array $insert New array.
		 * @param int   $position Position to merge at.
		 */
		public function array_merge_at( $array, $insert, $position ) {
			$new_array = array();
			// if pos is start, just merge them.
			if ( 0 === $position ) {
				$new_array = array_merge( $insert, $array );
			} else {
				// if pos is end just merge them.
				if ( $position >= ( count( $array ) - 1 ) ) {
					$new_array = array_merge( $array, $insert );
				} else {
					// split into head and tail, then merge head+inserted bit+tail.
					$head      = array_slice( $array, 0, $position );
					$tail      = array_slice( $array, $position );
					$new_array = array_merge( $head, $insert, $tail );
				}
			}
			return $new_array;
		}
	}

}
?>
