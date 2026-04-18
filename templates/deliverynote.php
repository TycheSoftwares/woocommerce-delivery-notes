<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * Delivery Note Template.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Templates
 * @category    Templates
 * @since       7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Allow template-specific hooks before.
 */
do_action( 'wcdn_before_deliverynote_template', $data );

/**
 * Include shared template.
 */
require __DIR__ . '/base.php';

/**
 * Allow template-specific hooks after.
 */
do_action( 'wcdn_after_deliverynote_template', $data );
