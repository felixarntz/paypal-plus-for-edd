<?php
/**
 * PayPal Plus Gateway
 *
 * @package EDD/PayPalPlus
 * @subpackage Gateways
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 * @since 1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

function edd_paypal_plus_register_gateway( $gateways ) {
	$gateways['paypal_plus'] = array(
		'admin_label'    => __( 'PayPal Plus', 'paypal-plus-for-edd' ),
		'checkout_label' => __( 'PayPal Plus', 'paypal-plus-for-edd' ),
	);

	return $gateways;
}
add_filter( 'edd_payment_gateways', 'edd_paypal_plus_register_gateway' );

function edd_paypal_plus_process_purchase( $purchase_data ) {
	if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'edd-gateway' ) ) {
		wp_die( __( 'Nonce verification has failed', 'paypal-plus-for-edd' ), __( 'Error', 'paypal-plus-for-edd' ), array( 'response' => 403 ) );
	}

	// Collect payment data
	$payment_data = array(
		'price'         => $purchase_data['price'],
		'date'          => $purchase_data['date'],
		'user_email'    => $purchase_data['user_email'],
		'purchase_key'  => $purchase_data['purchase_key'],
		'currency'      => edd_get_currency(),
		'downloads'     => $purchase_data['downloads'],
		'user_info'     => $purchase_data['user_info'],
		'cart_details'  => $purchase_data['cart_details'],
		'gateway'       => 'paypal_plus',
		'status'        => ! empty( $purchase_data['buy_now'] ) ? 'private' : 'pending'
	);

	// Record the pending payment
	$payment = edd_insert_payment( $payment_data );

	// Check payment
	if ( ! $payment ) {
		// Record the error
		edd_record_gateway_error( __( 'Payment Error', 'paypal-plus-for-edd' ), sprintf( __( 'Payment creation failed before sending buyer to PayPal. Payment data: %s', 'paypal-plus-for-edd' ), json_encode( $payment_data ) ), $payment );
		// Problems? send back
		edd_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['edd-gateway'] );
	}

	//TODO: handle PayPal Plus
}
add_action( 'edd_gateway_paypal_plus', 'edd_paypal_plus_process_purchase' );

function edd_paypal_plus_render_iframe() {
	//TODO: render iFrame
}
add_action( 'edd_paypal_plus_cc_form', 'edd_paypal_plus_render_iframe' );

function edd_paypal_plus_is_available() {
	if ( ! edd_paypal_plus_is_valid_currency() ) {
		return false;
	}

	list( $client_id, $secret_id ) = edd_paypal_plus_get_auth();
	if ( ! $client_id || ! $secret_id ) {
		return false;
	}

	return true;
}

function edd_paypal_plus_is_valid_currency() {
	return in_array( edd_get_currency(), array( 'EUR', 'CAD' ) );
}

function edd_paypal_plus_get_auth() {
	if ( edd_is_test_mode() ) {
		return array(
			edd_get_option( 'paypal_plus_client_id_sandbox' ),
			edd_get_option( 'paypal_plus_secret_id_sandbox' ),
		);
	}

	return array(
		edd_get_option( 'paypal_plus_client_id' ),
		edd_get_option( 'paypal_plus_secret_id' ),
	);
}
