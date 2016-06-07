<?php
/**
 * Register Settings
 *
 * @package EDD/PayPalPlus
 * @subpackage Admin/Settings
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 * @since 1.0.0
 */

function edd_paypal_plus_register_settings( $settings ) {
	$settings['paypal_plus'] = array(
		'paypal_plus_settings' => array(
			'id'   => 'paypal_plus_settings',
			'name' => '<h3>' . __( 'PayPal Plus Settings', 'paypal-plus-for-edd' ),
			'type' => 'header',
		),
		'paypal_plus_purchase_page' => array(
			'id'          => 'paypal_plus_purchase_page',
			'name'        => __( 'Checkout Page', 'paypal-plus-for-edd' ),
			'desc'        => __( 'PayPal Plus requires an additional checkout page that the user is lead to after selecting his payment method.', 'paypal-plus-for-edd' ),
			'type'        => 'select',
			'options'     => edd_get_pages(),
			'chosen'      => true,
			'placeholder' => __( 'Select a page', 'paypal-plus-for-edd' ),
		),
		'paypal_plus_client_id' => array(
			'id'   => 'paypal_plus_client_id',
			'name' => __( 'Live Client ID', 'paypal-plus-for-edd' ),
			'desc' => __( 'Enter your PayPal Rest API Client ID.', 'paypal-plus-for-edd' ),
			'type' => 'text',
			'size' => 'regular',
		),
		'paypal_plus_client_secret' => array(
			'id'   => 'paypal_plus_client_secret',
			'name' => __( 'Live Client Secret', 'paypal-plus-for-edd' ),
			'desc' => __( 'Enter your PayPal Rest API Client Secret.', 'paypal-plus-for-edd' ),
			'type' => 'text',
			'size' => 'regular',
		),
		'paypal_plus_client_id_sandbox' => array(
			'id'   => 'paypal_plus_client_id_sandbox',
			'name' => __( 'Sandbox Client ID', 'paypal-plus-for-edd' ),
			'desc' => __( 'Enter your Sandbox PayPal Rest API Client ID.', 'paypal-plus-for-edd' ),
			'type' => 'text',
			'size' => 'regular',
		),
		'paypal_plus_client_secret_sandbox' => array(
			'id'   => 'paypal_plus_client_secret_sandbox',
			'name' => __( 'Sandbox Client Secret', 'paypal-plus-for-edd' ),
			'desc' => __( 'Enter your Sandbox PayPal Rest API Client Secret.', 'paypal-plus-for-edd' ),
			'type' => 'text',
			'size' => 'regular',
		),
		'paypal_plus_invoice_prefix' => array(
			'id'   => 'paypal_plus_invoice_prefix',
			'name' => __( 'Invoice Prefix', 'paypal-plus-for-edd' ),
			'desc' => __( 'Enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores, ensure this prefix is unique as PayPal will not allow orders with the same invoice number.', 'paypal-plus-for-edd' ),
			'type' => 'text',
			'size' => 'regular',
		),
		'paypal_plus_set_billing_address' => array(
			'id'   => 'paypal_plus_set_billing_address',
			'name' => __( 'Billing Address', 'paypal-plus-for-edd' ),
			'desc' => __( 'Automatically set billing address using the address returned by PayPal?', 'paypal-plus-for-edd' ),
			'type' => 'checkbox',
		),
	);

	return $settings;
}
add_filter( 'edd_settings_gateways', 'edd_paypal_plus_register_settings' );
