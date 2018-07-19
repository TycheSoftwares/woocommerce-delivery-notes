<?php 
/**
 * WooCommerce Print Invoice & Delivery Note Welcome Page Class
 *
 * Displays on plugin activation
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCDN_Welcome Class
 *
 * A general class for About page.
 *
 * @since 4.4
 */
class WCDN_Welcome {

	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Get things started
	 *
	 * @since 4.4
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'wcdn_admin_menus' ) );
		add_action( 'admin_head', array( $this, 'wcdn_admin_head' ) );

		if ( !isset( $_GET[ 'page' ] ) || 
			( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] != 'wcdn-about' ) ) {
			add_action( 'admin_init', array( $this, 'wcdn_welcome' ) );
		}
	}

	/**
	 * Register the Dashboard Page which is later hidden but this pages
	 * is used to render the Welcome page.
	 *
	 * @access public
	 * @since  4.4
	 * @return void
	 */
	public function wcdn_admin_menus() {
		// About Page
		add_dashboard_page(
			sprintf( esc_html__( 'Welcome to WooCommerce Print Invoice & Delivery Note %s', 'woocommerce-delivery-notes' ), WooCommerce_Delivery_Notes::$plugin_version ),
			esc_html__( 'Welcome to WooCommerce Print Invoice & Delivery Note', 'woocommerce-delivery-notes' ),
			$this->minimum_capability,
			'wcdn-about',
			array( $this, 'wcdn_about_screen' )
		);
	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since  4.4
	 * @return void
	 */
	public function wcdn_admin_head() {
		remove_submenu_page( 'index.php', 'wcdn-about' );
	}

	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since  4.4
	 * @return void
	 */
	public function wcdn_about_screen() {
		$display_version = WooCommerce_Delivery_Notes::$plugin_version;
		// Badge for welcome page
		$badge_url = WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/icon-256x256.png';
		$wcdn_plugin_url = WooCommerce_Delivery_Notes::$plugin_url;		
		?>
		<style>
			.feature-section .feature-section-item {
				float:left;
				width:48%;
			}
		</style>
        <div class="wrap about-wrap">

			<?php $this->wcdn_get_welcome_header() ?>

            <div style="float:left;width: 80%;">
            <p class="about-text" style="margin-right:20px;"><?php
				printf(
					__( "Thank you for activating or updating to the latest version of WooCommerce Print Invoice & Delivery Note! If you're a first time user, welcome! You're well on your way to explore the print functionality for your WooCommerce orders." )
				);
				?></p>
			</div>
            <div class="wcdn-badge"><img src="<?php echo $badge_url; ?>" style="width:150px;"/></div>

            <p>&nbsp;</p>

            <div class="feature-section clearfix introduction">

                <h3><?php esc_html_e( "Get Started with WooCommerce Print Invoice & Delivery Note", 'woocommerce-delivery-notes' ); ?></h3>

                <div class="video feature-section-item" style="float:left;padding-right:10px;">
                    <img src="<?php echo $wcdn_plugin_url . '/assets/images/wcdn-settings.png' ?>"
                         alt="<?php esc_attr_e( 'WooCommerce Print Invoice & Delivery Note', 'woocommerce-delivery-notes' ); ?>" style="width:600px;">
                </div>

                <div class="content feature-section last-feature">
                    <h3><?php esc_html_e( 'Add settings', 'woocommerce-delivery-notes' ); ?></h3>

                    <p><?php esc_html_e( 'To enable the print functionality for your invoices, delivery notes & receipts, you just need to set it up under WooCommerce -> Settings -> Print page. Here you can also setup the Company Logo that will appear on the printed items, Company Address & other information.', 'woocommerce-delivery-notes' ); ?></p>
                    <a href="admin.php?page=wc-settings&tab=wcdn-settings" target="_blank" class="button-secondary">
						<?php esc_html_e( 'Click Here to go to Print page', 'woocommerce-delivery-notes' ); ?>
                        <span class="dashicons dashicons-external"></span>
                    </a>
                </div>
            </div>

            <div class="content">

                <div class="feature-section clearfix">
	                <div class="content feature-section-item">

	                	<h3><?php esc_html_e( 'Enable Print button for Customers.', 'woocommerce-delivery-notes' ); ?></h3>

		                    <p><?php esc_html_e( 'Allow customers to print the WooCommerce order invoice from the customer notification email, from the My Account page or from the View Order page under My Account.', 'woocommerce-delivery-notes' ); ?></p>
		                    <a href="admin.php?page=wc-settings&tab=wcdn-settings" target="_blank" class="button-secondary">
								<?php esc_html_e( 'Click Here to Enable Print button for Customers', 'woocommerce-delivery-notes' ); ?>
		                        <span class="dashicons dashicons-external"></span>
		                    </a>
	                </div>

	                <div class="content feature-section-item last-feature">
	                    <img src="<?php echo $wcdn_plugin_url . 'assets/images/wcdn-email-myaccount.png'; ?>" alt="<?php esc_attr_e( 'WooCommerce Print Invoice & Delivery Note', 'woocommerce-delivery-notes' ); ?>" style="width:500px;">
	                </div>
	            </div>

   				<div class="feature-section clearfix introduction">
	                <div class="video feature-section-item" style="float:left;padding-right:10px;">
	                    <img src="<?php echo $wcdn_plugin_url . 'assets/images/wcdn-invoice-numbering.png'; ?>" alt="<?php esc_attr_e( 'WooCommerce Print Invoice & Delivery Note', 'woocommerce-delivery-notes' ); ?>" style="width:500px;">
	                </div>

	                <div class="content feature-section-item last-feature">
	                    <h3><?php esc_html_e( 'Enable Invoice Numbering', 'woocommerce-delivery-notes' ); ?></h3>

	                    <p><?php esc_html_e( 'f you want to change the default invoice numbers & set some numbering scheme of your own, then you can set it here with a starting invoice number, a prefix & suffix. For example, you could set it as: TS/001/17-18.', 'woocommerce-delivery-notes' ); ?></p>
	                    <a href="admin.php?page=wc-settings&tab=wcdn-settings" target="_blank" class="button-secondary">
							<?php esc_html_e( 'Click Here to Enable Invoice Numbering', 'woocommerce-delivery-notes' ); ?>
	                        <span class="dashicons dashicons-external"></span>
	                    </a>
	                </div>
	            </div>
			</div>

            <div class="feature-section clearfix">

                <div class="content feature-section-item">

                    <h3><?php esc_html_e( 'Getting to Know Tyche Softwares', 'woocommerce-delivery-notes' ); ?></h3>

                    <ul class="ul-disc">
                        <li><a href="https://tychesoftwares.com/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank"><?php esc_html_e( 'Visit the Tyche Softwares Website', 'woocommerce-delivery-notes' ); ?></a></li>
                        <li><a href="https://tychesoftwares.com/premium-plugins/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank"><?php esc_html_e( 'View all Premium Plugins', 'woocommerce-delivery-notes' ); ?></a>
                        <ul class="ul-disc">
                        	<li><a href="https://www.tychesoftwares.com/store/premium-plugins/woocommerce-abandoned-cart-pro/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank">Abandoned Cart Pro Plugin for WooCommerce</a></li>
                        	<li><a href="https://www.tychesoftwares.com/store/premium-plugins/woocommerce-booking-plugin/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank">Booking & Appointment Plugin for WooCommerce</a></li>
                        	<li><a href="https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank">Order Delivery Date for WooCommerce</a></li>
                        	<li><a href="https://www.tychesoftwares.com/store/premium-plugins/product-delivery-date-pro-for-woocommerce/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank">Product Delivery Date for WooCommerce</a></li>
                        	<li><a href="https://www.tychesoftwares.com/store/premium-plugins/deposits-for-woocommerce/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank">Deposits for WooCommerce</a></li>
                        </ul>
                        </li>
                        <li><a href="https://tychesoftwares.com/about/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank"><?php esc_html_e( 'Meet the team', 'woocommerce-delivery-notes' ); ?></a></li>
                    </ul>
				</div>

				<div class="content feature-section-item">

                    <h3><?php esc_html_e( 'Current Offers', 'woocommerce-delivery-notes' ); ?></h3>

                    <p>Buy all our <a href="https://tychesoftwares.com/premium-plugins/?utm_source=wpaboutpage&utm_medium=link&utm_campaign=WCDeliveryNotes" target="_blank">premium plugins</a> at 30% off till 31st December 2017</p>
				</div>
			</div>            
            <!-- /.feature-section -->
		</div>
		<?php

		update_option( 'wcdn_welcome_page_shown', 'yes' );
		update_option( 'wcdn_welcome_page_shown_time', current_time( 'timestamp' ) );
	}


	/**
	 * The header section for the welcome screen.
	 *
	 * @since 4.4
	 */
	public function wcdn_get_welcome_header() {
		// Badge for welcome page
		$badge_url = WooCommerce_Delivery_Notes::$plugin_url . 'assets/images/icon-256x256.png';
		?>
        <h1 class="welcome-h1"><?php echo get_admin_page_title(); ?></h1>
		<?php $this->wcdn_social_media_elements(); ?>

	<?php }


	/**
	 * Social Media Like Buttons
	 *
	 * Various social media elements to Tyche Softwares
	 * @since: 4.4
	 */
	public function wcdn_social_media_elements() { ?>

        <div class="social-items-wrap">

            <iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Ftychesoftwares&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=false&amp;font&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=220596284639969"
                    scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;"
                    allowTransparency="true"></iframe>

            <a href="https://twitter.com/tychesoftwares" class="twitter-follow-button" data-show-count="false"><?php
				printf(
					esc_html_e( 'Follow %s', 'tychesoftwares' ),
					'@tychesoftwares'
				);
				?></a>
            <script>!function (d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
                    if (!d.getElementById(id)) {
                        js = d.createElement(s);
                        js.id = id;
                        js.src = p + '://platform.twitter.com/widgets.js';
                        fjs.parentNode.insertBefore(js, fjs);
                    }
                }(document, 'script', 'twitter-wjs');
            </script>

        </div>
        <!--/.social-items-wrap -->

		<?php
	}


	/**
	 * Sends user to the Welcome page on first activation of WooCommerce Print Invoice & Delivery Note as well as each
	 * time WooCommerce Print Invoice & Delivery Note is upgraded to a new version
	 *
	 * @access public
	 * @since  4.4
	 *
	 * @return void
	 */
	public function wcdn_welcome() {

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		if( !get_option( 'wcdn_welcome_page_shown' ) ) {
			wp_safe_redirect( admin_url( 'index.php?page=wcdn-about' ) );
			exit;
		}
	}

}

new WCDN_Welcome();