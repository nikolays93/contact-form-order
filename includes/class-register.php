<?php
/**
 * Register plugin actions
 *
 * @package Newproject.WordPress.plugin
 */

namespace NikolayS93\ContactFormOrders;

use NikolayS93\WPAdminPage\Page;
use NikolayS93\WPAdminPage\Section;
use NikolayS93\WPAdminPage\Metabox;

/**
 * Class Register
 */
class Register {

	/**
	 * Call this method before activate plugin
	 */
	public static function activate() {
		Order::create_table();

		/**
		 * /order/{order_id}/return/
		 * /order/{order_id}/cancel/
		 * /payment/{payment_method}/confirm/
		 */
		add_rewrite_rule( 'order/([1-9]+)/return/?', 'index.php?order_id=$matches[1]', 'top' );
		add_rewrite_rule( 'order/([1-9]+)/cancel/?', 'index.php?order_id=$matches[1]', 'top' );
		add_rewrite_rule( 'payment/([^/]*)/confirm/?', 'index.php?payment_method=$matches[1]', 'top' );
		flush_rewrite_rules();
	}

	/**
	 * Call this method before disable plugin
	 */
	public static function deactivate() {
	}

	/**
	 * Call this method before delete plugin
	 */
	public static function uninstall() {
		Order::delete_table();
	}

	/**
	 * Register admin settings menu item (page)
	 *
	 * @param string $pagename h1 page title.
	 * @param array  $pageprops page properties
	 */
	public static function settings_page( $pagename = '', $pageprops = array() ) {
		$page = new Page( Option::get_option_name(), $pagename, $pageprops );

		$page->set_assets(
			static function () {
			}
		);

		$page->set_content(
			static function() {
			}
		);

		$page->add_metabox(
			new Metabox(
				'Settings',
				__( 'Настройки', DOMAIN ),
				realpath( PLUGIN_DIR . 'admin/template/settings.php' ),
				$position = 'side',
				$priority = 'high'
			)
		);

		$page->add_section(
			new Section(
				'Yookassa',
				__( 'Yookassa', DOMAIN ),
				realpath( PLUGIN_DIR . 'admin/template/yookassa.php' )
			)
		);

		$page->add_section(
			new Section(
				'Paypal',
				__( 'Paypal', DOMAIN ),
				realpath( PLUGIN_DIR . 'admin/template/paypal.php' )
			)
		);

		$page->add_section(
			new Section(
				'Qiwi',
				__( 'Qiwi', DOMAIN ),
				realpath( PLUGIN_DIR . 'admin/template/qiwi.php' )
			)
		);
	}
}
