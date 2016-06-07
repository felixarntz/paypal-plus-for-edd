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

function edd_paypal_plus_check_enabled_gateway( $gateway_list ) {
	if ( isset( $gateway_list['paypal_plus'] ) && ! edd_paypal_plus_is_available() ) {
		unset( $gateway_list['paypal_plus'] );
	}

	return $gateway_list;
}
add_filter( 'edd_enabled_payment_gateways', 'edd_paypal_plus_check_enabled_gateway' );

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

	$paypal_plus_args = array();

	$paypal_plus_args = apply_filters( 'edd_paypal_plus_redirect_args', $paypal_plus_args, $purchase_data );

	$paypal_plus_redirect = get_permalink( edd_get_option( 'paypal_plus_purchase_page' ) );
	$paypal_plus_redirect = add_query_arg( $paypal_plus_args, $paypal_plus_redirect );

	edd_empty_cart();
	wp_redirect( $paypal_plus_redirect );
	exit;
}
add_action( 'edd_gateway_paypal_plus', 'edd_paypal_plus_process_purchase' );

function edd_paypal_plus_render_iframe() {
	//TODO: render iFrame
	if ( ! edd_paypal_plus_is_available() ) {
		return;
	}

	$approval_url = edd_paypal_plus_get_approval_url();
	if ( is_wp_error( $approval_url ) ) {
		?>
		<div class="alert alert-error"><p><?php echo $approval_url->get_error_message(); ?></p></div>
		<?php
		return;
	}

	?>
	<script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js" type="text/javascript"></script>
	<div id="ppplus"><?php _e( 'Loading payment gateway...', 'paypal-plus-for-edd' ); ?></div>
	<script type="text/javascript">
		var ppp = PAYPAL.apps.PPP({
			approvalUrl: '<?php echo $approval_url; ?>',
			placeholder: 'ppplus',
			useraction: 'commit',
			buttonLocation: 'outside',
			<?php if ( get_locale() != '' ) : ?>
			country: '<?php echo substr( get_locale(), -2 ); ?>',
			language: '<?php echo get_locale(); ?>',
			<?php endif; ?>
			mode: '<?php echo edd_is_test_mode() ? 'sandbox' : 'live'; ?>',
			onLoad: paypalPlusSetPayment,
			disableContinue: 'place_order', //TODO: set to id of button
			enableContinue: paypalPlusSetPayment,
			styles: {
				psp: {
					'font-size': '13px'
				}
			}
		});

		function paypalPlusSetPayment() {
			//TODO
		}
	</script>
	<?php
}
add_action( 'edd_paypal_plus_cc_form', 'edd_paypal_plus_render_iframe' );

function edd_paypal_plus_get_approval_url( $payment_id, $purchase_data ) {
	try {
		$items = array();
		if ( $purchase_data['cart_details'] ) {
			foreach ( $purchase_data['cart_details'] as $cart_item ) {
				$amount = round( ( $cart_item['subtotal'] / $cart_item['quantity'] ) - ( $cart_item['discount'] / $cart_item['quantity'] ), 2 );

				$item = new PayPal\Api\Item();
				$item->setName( $cart_item['name'] );
				$item->setCurrency( edd_get_currency() );
				$item->setQuantity( $cart_item['quantity'] );
				$item->setPrice( $amount );
				if ( edd_use_skus() ) {
					$item->setSku( edd_get_download_sku( $cart_item['id'] ) );
				}
				$items[] = $item;
			}
		}

		if ( $purchase_data['fees'] ) {
			foreach ( $purchase_data['fees'] as $fee ) {
				if ( 0.0 < floatval( $fee['amount'] ) ) {
					$item = new PayPal\Api\Item();
					$item->setName( stripslashes_deep( html_entity_decode( wp_strip_all_tags( $fee['label'] ), ENT_COMPAT, 'UTF-8' ) ) );
					$item->setCurrency( edd_get_currency() );
					$item->setQuantity( '1' );
					$item->setPrice( edd_sanitize_amount( $fee['amount'] ) );
					$items[] = $item;
				}
			}
		}

		$redirect_urls = new PayPal\Api\RedirectUrls();
		$redirect_urls->setReturnUrl( add_query_arg( array(
			'payment-confirmation' => 'paypal',
			'payment-id'           => $payment_id,
		), get_permalink( edd_get_option( 'success_page', false ) ) ) );
		$redirect_urls->setCancelUrl( edd_get_failed_transaction_uri( '?payment-id=' . $payment_id ) );

		$payer = new PayPal\Api\Payer();
		$payer->setPaymentMethod( 'paypal' );

		//TODO: adjust this function from here

		$details = new PayPal\Api\Details();
		if ( edd_use_taxes() ) {
			$details->setTax( edd_sanitize_amount( $purchase_data['tax'] ) );
		}
		$details->setSubtotal( (float) edd_get_cart_subtotal() - (float) edd_get_cart_discounted_amount() + (float) edd_get_cart_fee_total() );

		$amount = new PayPal\Api\Amount();
		$amount->setCurrency( edd_get_currency() );
		$amount->setTotal( (float) edd_get_cart_total() );
		$amount->setDetails( $details );

		$item_list = new PayPal\Api\ItemList();
		$item_list->setItems( $items );

		$transaction = new PayPal\Api\Transaction();
		$transaction->setAmount( $amount );
		$transaction->setDescription( '' );
		$transaction->setItemList( $item_list );

		$payment = new PayPal\Api\Payment();
		$payment->setRedirectUrls( $redirect_urls );
		$payment->setIntent( 'sale' );
		$payment->setPayer( $payer );
		$payment->setTransactions( array( $transaction ) );

		$payment->create( edd_paypal_plus_get_auth() );
		if ( 'created' === $payment->state && 'paypal' === $payment->payer->payment_method ) {
			$blablabla = $payment->id; //TODO: store persistently

			return $payment->links[1]->href;
		}
	} catch ( PayPal\Exception\PayPalConnectionException $e ) {
		return new WP_Error( 'paypal_plus_checkout_connection_error', __( 'Connection error while processing checkout. Please try again.', 'paypal-plus-for-edd' ) );
	} catch ( Exception $e ) {
		return new WP_Error( 'paypal_plus_checkout_error', __( 'Error while processing checkout. Please try again.', 'paypal-plus-for-edd' ) );
	}
}

function edd_paypal_plus_get_auth() {
	list( $client_id, $client_secret ) = edd_paypal_plus_get_auth_data();

	$auth = new PayPal\Rest\ApiContext( new PayPal\Auth\OAuthTokenCredential( $client_id, $client_secret ) );
	$auth->setConfig( array(
		'mode'                                       => edd_is_test_mode() ? 'SANDBOX' : 'LIVE',
		'http.headers.PayPal-Partner-Attribution-Id' => 'Easy_Digital_Downloads',
	) );
	return $auth;
}

function edd_paypal_plus_is_available() {
	if ( ! edd_paypal_plus_is_valid_currency() ) {
		return false;
	}

	list( $client_id, $client_secret ) = edd_paypal_plus_get_auth_data();
	if ( ! $client_id || ! $client_secret ) {
		return false;
	}

	return true;
}

function edd_paypal_plus_is_valid_currency() {
	return in_array( edd_get_currency(), array( 'EUR', 'CAD' ) );
}

function edd_paypal_plus_get_auth_data() {
	if ( edd_is_test_mode() ) {
		return array(
			edd_get_option( 'paypal_plus_client_id_sandbox' ),
			edd_get_option( 'paypal_plus_client_secret_sandbox' ),
		);
	}

	return array(
		edd_get_option( 'paypal_plus_client_id' ),
		edd_get_option( 'paypal_plus_client_secret' ),
	);
}
