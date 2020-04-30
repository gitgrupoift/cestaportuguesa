<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.pixlogix.com
 * @since      1.0.0
 *
 * @package    Delivery_date_for_woocommerce
 * @subpackage Delivery_date_for_woocommerce/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Delivery_date_for_woocommerce
 * @subpackage Delivery_date_for_woocommerce/includes
 * @author     Pixlogix <support@pixlogix.com>
 */
class DDFW_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function ddfw_load_plugin_textdomain() {

		load_plugin_textdomain(
			'delivery_date_for_woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
