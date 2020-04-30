<?php

namespace Webdados\InvoiceXpressWooCommerce;

/**
 * Fired during plugin deactivation
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @package Webdados
 * @since   2.0.0
 */
class Deactivator {

	/**
	 * Deactivation handler.
	 *
	 * @since 2.0.0
	 * @param bool $network_wide True if WPMU superadmin uses "Network Deactivate" action,
	 *                           false if WPMU is disabled or plugin is deactivated on an
	 *                           individual blog.
	 */
	public static function deactivate( $network_wide = false ) {
		delete_option( 'hd_wc_ie_plus_notices' );
	}
}
