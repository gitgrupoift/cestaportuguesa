<?php
/*
 * Plugin Name: Invoicing with InvoiceXpress for WooCommerce - Pro
 * Plugin URI: https://invoicewoo.com
 * Description: WooCommerce manual and automatic legal invoicing made easy with InvoiceXpress integration.
 * Version: 2.6.1
 * Author: Webdados
 * Author URI: https://www.webdados.pt
 * Text Domain: woo-billing-with-invoicexpress
 * Domain Path: /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 4.1.0
 */

namespace Webdados\InvoiceXpressWooCommerce;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

$composer_autoloader = __DIR__ . '/vendor/autoload.php';
if ( is_readable( $composer_autoloader ) ) {
	require $composer_autoloader;
}

//Plugin specific licensing information

if ( ! defined( 'INVOICEXPRESS_WOOCOMMERCE_PLUGIN_EDITION' ) ) {
	define( 'INVOICEXPRESS_WOOCOMMERCE_PLUGIN_EDITION', 'Pro' );
}

if ( ! defined( 'INVOICEXPRESS_WOOCOMMERCE_PLUGIN_BASENAME' ) ) {
	define( 'INVOICEXPRESS_WOOCOMMERCE_PLUGIN_BASENAME', 'Invoicing with InvoiceXpress for WooCommerce' );
}

if ( ! defined( 'INVOICEXPRESS_WOOCOMMERCE_PLUGIN_NAME' ) ) {
	define( 'INVOICEXPRESS_WOOCOMMERCE_PLUGIN_NAME', INVOICEXPRESS_WOOCOMMERCE_PLUGIN_BASENAME . ' - ' . INVOICEXPRESS_WOOCOMMERCE_PLUGIN_EDITION );
}

if ( ! defined( 'INVOICEXPRESS_WOOCOMMERCE_PLUGIN_FILE' ) ) {
	define( 'INVOICEXPRESS_WOOCOMMERCE_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'INVOICEXPRESS_WOOCOMMERCE_PLUGIN_PATH' ) ) {
	define( 'INVOICEXPRESS_WOOCOMMERCE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'INVOICEXPRESS_WOOCOMMERCE_BASENAME' ) ) {
	define( 'INVOICEXPRESS_WOOCOMMERCE_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'INVOICEXPRESS_WOOCOMMERCE_VERSION' ) ) {
	define( 'INVOICEXPRESS_WOOCOMMERCE_VERSION', '2.6.1' );
}

if ( ! defined( 'INVOICEXPRESS_WOOCOMMERCE_PRODUCT_ID' ) ) {
	define( 'INVOICEXPRESS_WOOCOMMERCE_PRODUCT_ID', 'IIXWCPRO' );
}

//Generic licensing info

if ( ! defined( 'WEBDADOS_LICENSE_MANAGER_API_URL' ) ) {
	define( 'WEBDADOS_LICENSE_MANAGER_API_URL', 'https://shop.webdados.com/index.php' );
}

if ( ! defined( 'WEBDADOS_LICENSE_MANAGER_INSTANCE' ) ) {
	$protocol = 'http://';
	if (
		! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off'
		|| ! empty( $_SERVER['SERVER_PORT'] ) && $_SERVER['SERVER_PORT'] === 443
	) {
		$protocol = 'https://';
	}
	define( 'WEBDADOS_LICENSE_MANAGER_INSTANCE', str_replace( $protocol, '', get_bloginfo( 'wpurl' ) ) );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in src/Activator.php
 */
register_activation_hook( __FILE__, '\Webdados\InvoiceXpressWooCommerce\Activator::activate' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in lib/Deactivator.php
 */
register_deactivation_hook( __FILE__, '\Webdados\InvoiceXpressWooCommerce\Deactivator::deactivate' );

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
function invoicexpress_woocommerce_pro_init() {
	( new Plugin() )->run();
}

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\invoicexpress_woocommerce_pro_init' );
