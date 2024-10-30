<?php
/*
 * Plugin Name: KadePay Payment Gateway for WooCommerce
 * Plugin URI: https://kadepay.com/
 * Description: Process payments on your store using KadePay Gateway.
 * Author: KadePay
 * Author URI: https://www.kadepay.com
 * Version: 1.1.1
 * Requires at least: 4.4
 * Tested up to: 4.8
 * WC requires at least: 2.5
 * WC tested up to: 3.7
 * Text Domain: woo-gateway-kadepay
 *
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if (!class_exists('WC_KadePay')) {

    class WC_KadePay {

        var $version = '1.0';
        var $php_version = '2.5.0';
        var $wc_version = '2.5.0';
        var $plugin_url;
        var $plugin_path;
        var $notices = array();

        function __construct() {
            $this->define_constants();
            $this->includes();
            $this->loader_operations();
            //Handle any db install and upgrade task
            add_action( 'admin_init', array( $this, 'check_environment' ) );
            add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	    	add_filter( 'woocommerce_order_button_text', 'kadepay_custom_button_text' );
        }
        /**
		 * Required minimums and constants
		 */
        function define_constants() {
            define('WC_KADEPAY_VERSION', $this->version);
            define( 'WC_KADEPAY_MIN_PHP_VER', $this->php_version );
            define( 'WC_KADEPAY_MIN_WC_VER', $this->wc_version );
            define('WC_KADEPAY_PLUGIN_URL', $this->plugin_url());
            define('WC_KADEPAY_PLUGIN_PATH', $this->plugin_path());
        }

        function includes() {
        	include_once( dirname( __FILE__ ) . '/includes/class-wc-utility-kadepay.php' );
        }

        function loader_operations() {
            add_action('plugins_loaded', array(&$this, 'plugins_loaded_handler')); //plugins loaded hook		
        }

        function plugins_loaded_handler() {
            //Runs when plugins_loaded action gets fired
			include_once( dirname( __FILE__ ) . '/includes/class-wc-gateway-kadepay.php' );
            add_filter('woocommerce_payment_gateways', array(&$this, 'init_kadepay_gateway'));
        }
        

        function check_environment() {
            if (  WC_KADEPAY_VERSION !== get_option( 'wc_kadepay_version' ) )  {
				$this->_update_plugin_version();
			}
			$environment_warning = $this->get_environment_warning();

			if ( $environment_warning && is_plugin_active( plugin_basename( __FILE__ ) ) ) {
				$this->add_admin_notice( 'bad_environment', 'error', $environment_warning );
			}
			$options = get_option( 'woocommerce_kadepay_settings' );
			//$secret = $options['merchant_id'];
            if ((isset($options['merchant_id']) && empty( $options['merchant_id'])) && ! ( isset( $_GET['page'], $_GET['section'] ) && 'wc-settings' === $_GET['page'] && 'kadepay' === $_GET['section'] ) ) {
				$setting_link = $this->get_setting_link();
				$this->add_admin_notice( 'prompt_connect', 'notice notice-warning', sprintf( __( 'KadePay is almost ready. To get started, <a href="%s">set your KadePay merchant ID</a>.', 'woo-gateway-kadepay' ), $setting_link ) );
			}
        }

        private static function _update_plugin_version() {
			delete_option( 'wc_kadepay_version' );
			update_option( 'wc_kadepay_version', WC_KADEPAY_VERSION );
			return true;
		}

        function plugin_url() {
            if ($this->plugin_url)
                return $this->plugin_url;
            return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
        }

        function plugin_path() {
            if ($this->plugin_path)
                return $this->plugin_path;
            return $this->plugin_path = untrailingslashit(plugin_dir_path(__FILE__));
        }



        function plugin_action_links($links){
           $setting_link = $this->get_setting_link();

			$plugin_links = array(
				'<a href="' . $setting_link . '">' . __( 'Settings', 'woo-gateway-kadepay' ) . '</a>',
				'<a href="https://www.kadepay.com">' . __( 'Docs', 'woo-gateway-kadepay' ) . '</a>',
				'<a href="https://www.kadepay.com">' . __( 'Support', 'woo-gateway-kadepay' ) . '</a>',
			);
			return array_merge( $plugin_links, $links );        
        }

        function get_setting_link() {
			$use_id_as_section = function_exists( 'WC' ) ? version_compare( WC()->version, '2.6', '>=' ) : false;

			$section_slug = $use_id_as_section ? 'kadepay' : strtolower( 'WC_Gateway_Kadepay' );

			return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $section_slug );
		}
        
        function init_kadepay_gateway($methods) {        
			$methods[] = 'WC_Gateway_Kadepay';			
			return $methods;
        }
        /**
		 * Allow this class and other classes to add slug keyed notices (to avoid duplication)
		 */
		function add_admin_notice( $slug, $class, $message ) {
			$this->notices[ $slug ] = array(
				'class'   => $class,
				'message' => $message,
			);
		}
		/**
		 * Display any notices we've collected thus far (e.g. for connection, disconnection)
		 */
	    function admin_notices() {
			foreach ( (array) $this->notices as $notice_key => $notice ) {
				echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
				echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) );
				echo '</p></div>';
			}
		}
        /**
		 * Checks the environment for compatibility problems.  Returns a string with the first incompatibility
		 * found or false if the environment has no problems.
		 */
		function get_environment_warning() {
			if ( version_compare( phpversion(), WC_KADEPAY_MIN_PHP_VER, '<' ) ) {
				$message = __( 'WooCommerce KadePay - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'woo-gateway-kadepay' );

				return sprintf( $message, WC_KADEPAY_MIN_PHP_VER, phpversion() );
			}

			if ( ! defined( 'WC_VERSION' ) ) {
				return __( 'WooCommerce KadePay requires WooCommerce to be activated to work.', 'woo-gateway-kadepay' );
			}

			if ( version_compare( WC_VERSION, WC_KADEPAY_MIN_WC_VER, '<' ) ) {
				$message = __( 'WooCommerce KadePay - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'woo-gateway-kadepay' );

				return sprintf( $message, WC_KADEPAY_MIN_WC_VER, WC_VERSION );
			}

			if ( ! function_exists( 'curl_init' ) ) {
				return __( 'WooCommerce KadePay - cURL is not installed.', 'woo-gateway-kadepay' );
			}

			return false;
		}
		/**
		* load admin script for setting page
		*/
		public function admin_scripts() {
    		$screen    = get_current_screen();
    		$screen_id = $screen ? $screen->id : '';
    
    		if ( 'woocommerce_page_wc-settings' !== $screen_id ) {
    			return;
    		}
    
    		wp_enqueue_script( 'kadepay_woocommerce_admin', $this->plugin_url() . '/assets/js/kadepay-script.js', array(), WC_KADEPAY_VERSION, true );
    	}

    }

    function kadepay_custom_button_text( $button_text ) {
		return 'Proceed to Payment'; 
    }

    //End of plugin class
}//End of class not exists check

$GLOBALS['WC_KadePay'] = new WC_KadePay();
