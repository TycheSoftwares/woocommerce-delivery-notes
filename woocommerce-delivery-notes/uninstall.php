<?php
/**
 * WooCommerce Print Invoice & Delivery Note Uninstall
 *
 * Uninstalling WooCommerce Print Invoice & Delivery Note options.
 *
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Delete the data for the WordPress Multisite.
 */
if ( is_multisite() ) {
    $wcdn_blog_list = get_sites( );

    foreach( $wcdn_blog_list as $wcdn_blog_list_key => $wcdn_blog_list_value ) {
        $wcdn_blog_id = $wcdn_blog_list_value->blog_id;
        delete_blog_option( $wcdn_blog_id, 'wcdn_welcome_page_shown' );
        delete_blog_option( $wcdn_blog_id, 'wcdn_welcome_page_shown_time' );
    }
    
} else {
    /**
     * Delete the data for the single website ( Non-Multisite )
     */
    delete_option( 'wcdn_welcome_page_shown' );
    delete_option( 'wcdn_welcome_page_shown_time' );
}
    
wp_cache_flush();