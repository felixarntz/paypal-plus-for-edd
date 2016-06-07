<?php
/*
Plugin Name: PayPal Plus for Easy Digital Downloads
Plugin URI:  https://wordpress.org/plugins/paypal-plus-for-edd/
Description: Accept payments through PayPal Plus for your store powered by Easy Digital Downloads.
Version:     1.0.0
Author:      Felix Arntz
Author URI:  https://leaves-and-love.net
License:     GNU General Public License v3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: paypal-plus-for-edd
Tags:        easy-digital-downloads, extension, payment gateway, paypal, paypal plus
*/
/**
 * EDD_PayPal_Plus class
 *
 * @package EDD/PayPalPlus
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'EDD_PayPal_Plus' ) ) {

	/**
	 * Main EDD_PayPal_Plus class
	 *
	 * @since 1.0.0
	 */
	class EDD_PayPal_Plus {

		/**
		 * Stores the EDD_PayPal_Plus instance.
		 *
		 * @since 1.0.0
		 * @access private
		 * @static
		 * @var EDD_PayPal_Plus
		 */
		private static $instance;

		/**
		 * Returns the EDD_PayPal_Plus instance.
		 *
		 * @since 1.0.0
		 * @access public
		 * @static
		 *
		 * @return EDD_PayPal_Plus The EDD_PayPal_Plus instance.
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new EDD_PayPal_Plus();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();
			}

			return self::$instance;
		}

		/**
		 * Defines the plugin constants.
		 *
		 * @since 1.0.0
		 * @access private
		 */
		private function setup_constants() {
			define( 'EDD_PAYPAL_PLUS_VER', '1.0.0' );
			define( 'EDD_PAYPAL_PLUS_DIR', plugin_dir_path( __FILE__ ) );
			define( 'EDD_PAYPAL_PLUS_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Includes necessary files.
		 *
		 * @since 1.0.0
		 * @access private
		 */
		private function includes() {
			require_once EDD_PAYPAL_PLUS_DIR . 'includes/scripts.php';
			require_once EDD_PAYPAL_PLUS_DIR . 'includes/gateways/paypal-plus.php';
			require_once EDD_PAYPAL_PLUS_DIR . 'includes/admin/admin-notices.php';
			require_once EDD_PAYPAL_PLUS_DIR . 'includes/admin/settings/register-settings.php';

			// only load composer file if PayPal classes are not already loaded by some other plugin
			if ( ! class_exists( 'PayPal\Api\Authorization' ) && file_exists( EDD_PAYPAL_PLUS_DIR . 'vendor/autoload.php' ) ) {
				require_once EDD_PAYPAL_PLUS_DIR . 'vendor/autoload.php';
			}
		}

		/**
		 * Runs action and filter hooks.
		 *
		 * @since 1.0.0
		 * @access private
		 */
		private function hooks() {
			add_filter( 'edd_settings_extensions', array( $this, 'settings' ), 1 );
		}

		/**
		 * Loads the plugin's textdomain.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'paypal-plus-for-edd' );
		}

		/**
		 * Adds settings for the plugin.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param array $settings The existing EDD settings array.
		 * @return array The modified EDD settings array.
		 */
		public function settings( $settings ) {
			$new_settings = array(
				array(
					'id'	=> 'edd_paypal_plus_settings',
					'name'	=> '<strong>' . __( 'PayPal Plus Settings', 'paypal-plus-for-edd' ) . '</strong>',
					'desc'	=> __( 'Configure PayPal Plus Settings', 'paypal-plus-for-edd' ),
					'type'	=> 'header',
				)
			);

			return array_merge( $settings, $new_settings );
		}
	}
}

/**
 * The main function responsible for returning the one true EDD_PayPal_Plus
 * instance to functions everywhere.
 *
 * Returns null if the plugin requirements are not met.
 *
 * @since 1.0.0
 *
 * @return EDD_PayPal_Plus|null The one true EDD_PayPal_Plus
 */
function EDD_PayPal_Plus_load() {
	if ( ! class_exists( 'EDD_PayPal_Plus_Extension_Activation' ) ) {
        require_once 'includes/class.extension-activation.php';
    }

	$activation = EDD_PayPal_Plus_Extension_Activation::instance();
	if ( $activation->run() ) {
		return EDD_PayPal_Plus::instance();
	}
}
add_action( 'plugins_loaded', 'EDD_PayPal_Plus_load' );
