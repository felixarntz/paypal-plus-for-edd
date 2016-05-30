<?php
/**
 * Script functions
 *
 * @package EDD\PayPalPlus
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load admin scripts
 *
 * @since 1.0.0
 *
 * @global array $edd_settings_page The slug for the EDD settings page
 * @global string $post_type The type of post that we are editing
 */
function edd_paypal_plus_admin_scripts( $hook ) {
    global $edd_settings_page, $post_type;

    if ( $hook !== $edd_settings_page ) {
        return;
    }

    // Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    wp_enqueue_script( 'edd_paypal_plus_admin_js', EDD_PAYPAL_PLUS_URL . '/assets/js/admin' . $suffix . '.js', array( 'jquery' ) );
    wp_enqueue_style( 'edd_paypal_plus_admin_css', EDD_PAYPAL_PLUS_URL . '/assets/css/admin' . $suffix . '.css' );
}
add_action( 'admin_enqueue_scripts', 'edd_paypal_plus_admin_scripts', 100 );

/**
 * Load frontend scripts
 *
 * @since 1.0.0
 */
function edd_paypal_plus_scripts( $hook ) {
    // Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    wp_enqueue_script( 'edd_paypal_plus_js', EDD_PAYPAL_PLUS_URL . '/assets/js/scripts' . $suffix . '.js', array( 'jquery' ) );
    wp_enqueue_style( 'edd_paypal_plus_css', EDD_PAYPAL_PLUS_URL . '/assets/css/styles' . $suffix . '.css' );
}
add_action( 'wp_enqueue_scripts', 'edd_paypal_plus_scripts' );
