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
 * Load frontend scripts
 *
 * @since 1.0.0
 */
function edd_paypal_plus_scripts( $hook ) {
	if ( ! edd_is_checkout() ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_script( 'edd_paypal_plus_js', EDD_PAYPAL_PLUS_URL . '/assets/dist/js/checkout' . $suffix . '.js', array( 'jquery' ), '1.0.0' );
	wp_enqueue_style( 'edd_paypal_plus_css', EDD_PAYPAL_PLUS_URL . '/assets/dist/css/checkout' . $suffix . '.css', array(), '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'edd_paypal_plus_scripts' );
