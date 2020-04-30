<?php

/**
 * Fired during plugin activation
 *
 * @link       https://meuppt.pt
 * @since      1.0.0
 *
 * @package    Wc_Gdpr_Aan
 * @subpackage Wc_Gdpr_Aan/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wc_Gdpr_Aan
 * @subpackage Wc_Gdpr_Aan/includes
 * @author     Bnext1 e MeuPPT <geral@meuppt.pt>
 */
class Wc_Gdpr_Aan_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
	
	add_filter( 'admin_footer_text',    '__return_false', 11 );
    	add_filter( 'update_footer',        '__return_false', 11 );

	}

}
