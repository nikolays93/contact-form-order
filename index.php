<?php
/**
 * Plugin Name: Contact Form Order
 * Plugin URI: https://github.com/nikolays93
 * Description: Contact Form (Flamingo) payment module
 * Version: 0.1.0
 * Author: NikolayS93
 * Author URI: https://vk.com/nikolays_93
 * Author EMAIL: NikolayS93@ya.ru
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpcf0
 * Domain Path: /languages/
 *
 * @php 7.1
 * @package WordPress.ContactForm.Orders
 */

namespace NikolayS93\ContactFormOrders;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'You shall not pass' );
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

// Plugin top doc properties.
$plugin_data = get_plugin_data( __FILE__ );

if ( ! defined( __NAMESPACE__ . '\PLUGIN_DIR' ) ) {
	define( __NAMESPACE__ . '\PLUGIN_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );
}

if ( ! defined( __NAMESPACE__ . 'DOMAIN' ) ) {
	define( __NAMESPACE__ . '\DOMAIN', $plugin_data['TextDomain'] );
}

if ( ! defined( __NAMESPACE__ . 'PREFIX' ) ) {
	define( __NAMESPACE__ . '\PREFIX', DOMAIN . '_' );
}

// load plugin languages.
load_plugin_textdomain( DOMAIN, false, basename( PLUGIN_DIR ) . $plugin_data['DomainPath'] );

require_once PLUGIN_DIR . 'vendor/autoload.php';
require_once PLUGIN_DIR . 'includes/autoload.php';

register_activation_hook( __FILE__, array( Register::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Register::class, 'deactivate' ) );
register_uninstall_hook( __FILE__, array( Register::class, 'uninstall' ) );

/**
 * Initialize this plugin once all other plugins have finished loading.
 */
add_action(
	'plugins_loaded',
	function() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( !is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			return;
		}

		add_filter('wpcf0_payment_methods', function(array $payment_methods) {
			return array_merge($payment_methods, [
				YooKassa_Payment::TYPE => YooKassa_Payment::class,
				Paypal_Payment::TYPE => Paypal_Payment::class,
				Qiwi_Payment::TYPE => Qiwi_Payment::class,
			]);
		});

		// Register::register_plugin_page(
		// 	__( 'New plugin', DOMAIN ),
		// 	array(
		// 		'parent'      => '', // for ex. woocommerce.
		// 		'menu'        => __( 'Example', DOMAIN ),
		// 		'permissions' => 'manage_options',
		// 		'columns'     => 2,
		// 	)
		// );

        Register::settings_page(
            __( 'Настройки платежей', DOMAIN ),
            array(
                'parent'      => 'options-general.php', // for ex. woocommerce.
                'menu'        => __( 'Contact Form Orders', DOMAIN ),
                'permissions' => 'manage_options',
                'columns'     => 2,
            )
        );

		require 'includes/wpcf7/form-tag-order-amount.php';
		require 'includes/wpcf7/form-tag-payment-type.php';

		// Before flamingo submit required
		add_action( 'wpcf7_submit', [Order_Controller::class, 'payment_request'], 8, 2 );

		add_filter( 'query_vars', [Register::class, 'vars'] );
		add_action('pre_get_posts', [Order_Controller::class, 'paymentResultPage']);
		add_action('pre_get_posts', [Order_Controller::class, 'payment_confirm']);
	},
	10
);
