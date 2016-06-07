<?php
/**
 * Admin Notices
 *
 * @package EDD/PayPalPlus
 * @subpackage Admin/Notices
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 * @since 1.0.0
 */

function edd_paypal_plus_admin_notices() {
	if ( ! edd_is_gateway_active( 'paypal_plus' ) ) {
		return;
	}

	if ( ! edd_paypal_plus_is_valid_currency() ) {
		?>
		<div class="error">
			<p><?php _e( 'PayPal Plus does not support your store currency. Only EUR and CAD are supported.', 'paypal-plus-for-edd' ); ?></p>
		</div>
		<?php
	}

	list( $client_id, $client_secret ) = edd_paypal_plus_get_auth_data();
	if ( ! $client_id || ! $client_secret ) {
		?>
		<div class="error">
			<p><?php printf( __( 'Please enter your PayPal Plus Rest API Cient ID and Secret ID <a href="%s">here</a>.', 'paypal-plus-for-edd' ), admin_url( 'edit.php?post_type=download&page=edd-settings' ) ); ?></p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'edd_paypal_plus_admin_notices' );
