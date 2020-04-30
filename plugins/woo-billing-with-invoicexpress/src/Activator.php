<?php

namespace Webdados\InvoiceXpressWooCommerce;

/**
 * Fired during plugin activation
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package Webdados
 * @since   2.0.0
 */
class Activator {

	/**
	 * Activation handler.
	 *
	 * @since 2.0.0
	 * @param bool $network_wide True if WPMU superadmin uses "Network Activate" action,
	 *                           false if WPMU is disabled or plugin is activated on an
	 *                           individual blog.
	 */
	public static function activate( $network_wide = false ) {

		if ( ! ini_get( 'allow_url_fopen' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( esc_html__( 'allow_url_fopen must be enabled on your php.ini.', 'woo-billing-with-invoicexpress' ) );
		}

		if ( ! function_exists( 'curl_version' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( esc_html__( 'cURL is required.', 'woo-billing-with-invoicexpress' ) );
		}

		if ( ! class_exists( 'WooCommerce' ) || version_compare( WC_VERSION, '3.0.0', '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( esc_html__( 'Requires WooCommerce 3.0.0 or above.', 'woo-billing-with-invoicexpress' ) );
		}

		if ( is_plugin_active( 'woo-billing-with-invoicexpress-pro/woocommerce-billing-invoicexpress-pro-edition.php' ) ) {
			wp_die(
				esc_html(
					sprintf(
						/* translators: %s: plugin name. */
						__( '%s - Free cannot be activated alongside with the Pro version.', 'woo-billing-with-invoicexpress' ),
						INVOICEXPRESS_WOOCOMMERCE_PLUGIN_BASENAME
					)
				)
			);
		}
	}
}
