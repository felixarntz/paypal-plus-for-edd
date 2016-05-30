<?php
/**
 * EDD_PayPal_Plus_Extension_Activation class
 *
 * @package EDD\PayPalPlus
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * EDD Extension Activation Handler class for PayPal Plus.
 *
 * @since 1.0.0
 */
class EDD_PayPal_Plus_Extension_Activation {
	/**
	 * Stores the EDD_PayPal_Plus_Extension_Activation instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 * @var EDD_PayPal_Plus_Extension_Activation
	 */
	private static $instance = null;

	/**
	 * Returns the EDD_PayPal_Plus_Extension_Activation instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return EDD_PayPal_Plus_Extension_Activation The EDD_PayPal_Plus_Extension_Activation instance.
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * The plugin name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $plugin_name;

	/**
	 * The minimum required PHP version.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $min_php_version;

	/**
	 * The minimum required WordPress version.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $min_wp_version;

	/**
	 * Stores whether the PHP version requirement is met.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var bool
	 */
	public $has_php;

	/**
	 * Stores whether the WordPress version requirement is met.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var bool
	 */
	public $has_wp;

	/**
	 * Stores whether Easy Digital Downloads is detected.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var bool
	 */
	public $has_edd;

	/**
	 * Stores whether Easy Digital Downloads is installed (if it is not detected).
	 *
	 * @since 1.0.0
	 * @access public
	 * @var bool
	 */
	public $has_edd_installed;

	/**
	 * Stores the basename for the Easy Digital Downloads plugin (if it is not detected).
	 *
	 * @since 1.0.0
	 * @access public
	 * @var bool
	 */
	public $edd_base;

	/**
	 * Stores whether the requirements have been checked yet.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var bool
	 */
	private $checked = false;

	/**
	 * Constructor.
	 *
	 * Private because of Singleton.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __construct() {
		$this->plugin_name = 'PayPal Plus for Easy Digital Downloads';
		$this->min_php_version = '5.3';
		$this->min_wp_version = '4.0';
		$this->php_extensions = array( 'curl', 'openssl' );
	}

	/**
	 * Checks whether the requirements are met.
	 *
	 * If not, an error admin notice is hooked in.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether the requirements are met.
	 */
	public function run() {
		if ( ! $this->checked ) {
			$this->has_php = true;
			$this->has_wp = true;
			$this->has_edd = true;

			if ( version_compare( phpversion(), $this->min_php_version ) < 0 ) {
				$this->has_php = false;
			}

			if ( version_compare( get_bloginfo( 'version' ), $this->min_wp_version ) < 0 ) {
				$this->has_wp = false;
			}

			$ext_statuses = array();
			foreach ( $this->php_extensions as $extension ) {
				if ( extension_loaded( $extension ) ) {
					$ext_statuses[ $extension ] = true;
				} else {
					$ext_statuses[ $extension ] = false;
				}
			}
			$this->php_extensions = $ext_statuses;

			if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
				$this->has_edd = false;

				$this->has_edd_installed = false;

				require_once ABSPATH . 'wp-admin/includes/plugin.php';

				foreach ( $plugins as $plugin_path => $plugin ) {
					if ( $plugin['Name'] == 'Easy Digital Downloads' ) {
						$this->has_edd_installed = true;
						$this->edd_base = $plugin_path;
						break;
					}
				}
			}
		}

		if ( ! $this->has_php || ! $this->has_wp || ! $this->has_edd || count( $this->php_extensions ) !== count( array_filter( $this->php_extensions ) ) ) {
			if ( ! $this->checked ) {
				add_action( 'admin_notices', array( $this, 'missing_requirements_notice' ) );
			}
			$this->checked = true;
			return false;
		}

		$this->checked = true;
		return true;
	}

	/**
	 * Displays an error notice for the unmet requirements.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function missing_requirements_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$message = '';

		if ( ! $this->has_php ) {
			$message = sprintf( __( '%1$s requires PHP version %2$s! Please update your hosting environment accordingly to continue.', 'paypal-plus-for-edd' ), $this->plugin_name, $this->min_php_version );
		} elseif ( ! $this->has_wp ) {
			$message = sprintf( __( '%1$s requires WordPress version %2$s! Please update WordPress to this version to continue.', 'paypal-plus-for-edd' ), $this->plugin_name, $this->min_wp_version );
		} elseif ( ! $this->has_edd ) {
			if ( $this->has_edd_installed ) {
				$url  = esc_url( wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . $this->edd_base ), 'activate-plugin_' . $this->edd_base ) );
				$message = sprintf( __( '%1$s requires Easy Digital Downloads! Please <a href="%2$s">activate it</a> to continue.', 'paypal-plus-for-edd' ), $this->plugin_name, $url );
			} else {
				$url  = esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=easy-digital-downloads' ), 'install-plugin_easy-digital-downloads' ) );
				$message = sprintf( __( '%1$s requires Easy Digital Downloads! Please <a href="%2$s">install it</a> to continue.', 'paypal-plus-for-edd' ), $this->plugin_name, $url );
			}
		} else {
			$missing = array();
			foreach ( $this->php_extensions as $extension => $status ) {
				if ( ! $status ) {
					$missing[] = '<code>' . $extension . '</code>';
				}
			}
			$message = sprintf( __( '%1$s requires the PHP extensions %2$s! Please enable these to continue.', 'paypal-plus-for-edd' ), $this->plugin_name, implode( ', ', $missing ) );
		}

		echo '<div class="error"><p>' . $message . '</p></div>';
	}
}
