<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://meuppt.pt
 * @since      1.0.0
 *
 * @package    Wc_Gdpr_Aan
 * @subpackage Wc_Gdpr_Aan/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wc_Gdpr_Aan
 * @subpackage Wc_Gdpr_Aan/includes
 * @author     Bnext1 e MeuPPT <geral@meuppt.pt>
 */
class Wc_Gdpr_Aan_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wc-gdpr-aan',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
