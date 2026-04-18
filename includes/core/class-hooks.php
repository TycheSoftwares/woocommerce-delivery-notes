<?php
/**
 * Actions and Filters for Print Invoice & Delivery Notes for WooCommerce
 *
 * @author      Tyche Softwares
 * @package     WCDN/Hooks
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN;

defined( 'ABSPATH' ) || exit;

/**
 * WCDN Install Class.
 *
 * @since 7.0
 */
class Hooks {

	/**
	 * Hooks.
	 *
	 * @since 7.0
	 */
	public static function init() {
		add_action(
			'before_woocommerce_init',
			function () {
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', 'woocommerce-delivery-notes/woocommerce-delivery-notes.php', true );
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'orders_cache', 'woocommerce-delivery-notes/woocommerce-delivery-notes.php', true );
				}
			}
		);

		add_action(
			'init',
			function () {

				$domain = 'woocommerce-delivery-notes';
				$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
				$loaded = load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '-' . $locale . '.mo' );

				if ( $loaded ) {
					return;
				}

				load_plugin_textdomain( $domain, false, basename( dirname( WCDN_FILE ) ) . '/languages/' );
			}
		);

		add_filter(
			'plugin_action_links_' . plugin_basename( WCDN_FILE ),
			function ( $links ) {
				$url = esc_url(
					admin_url(
						add_query_arg(
							array(
								'page' => 'wcdn_page#settings',
							),
							'admin.php'
						)
					)
				);

				$settings = sprintf(
					'<a href="%s" title="%s">%s</a>',
					$url,
					esc_attr__( 'Go to the settings page', 'woocommerce-delivery-notes' ),
					esc_html__( 'Settings', 'woocommerce-delivery-notes' )
				);

				array_unshift( $links, $settings );

				return $links;
			}
		);
	}
}
