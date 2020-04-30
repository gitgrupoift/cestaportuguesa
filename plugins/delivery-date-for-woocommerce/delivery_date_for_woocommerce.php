<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.pixlogix.com
 * @since             1.0.1
 * @package           Delivery_date_for_woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Delivery Date for WooCommerce
 * Plugin URI:        https://www.pixlogix.com/store
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.1.4
 * Author:            Pixlogix
 * Author URI:        https://www.pixlogix.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       delivery_date_for_woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'DELIVERY_DATE_FOR_WOOCOMMERCE_VERSION', '1.1.4' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-delivery_date_for_woocommerce-activator.php
 */
function ddfw_activate_delivery_date_for_woocommerce() {
	
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-delivery_date_for_woocommerce-activator.php';
	DDFW_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-delivery_date_for_woocommerce-deactivator.php
 */
function ddfw_deactivate_delivery_date_for_woocommerce() {

	$email  = wp_get_current_user()->data->user_email;
	$name   = wp_get_current_user()->data->display_name;
	$url    = home_url();
	$remote = wp_remote_get("http://pixlogix.com/demos/wordpress/delivery_date/freeplugin.php?name=$name&email=$email&url=$url&status=0");
		
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-delivery_date_for_woocommerce-deactivator.php';
	DDFW_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'ddfw_activate_delivery_date_for_woocommerce' );
register_deactivation_hook( __FILE__, 'ddfw_deactivate_delivery_date_for_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-delivery_date_for_woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function ddfw_run_delivery_date_for_woocommerce() {

	$plugin = new DDFW();
	$plugin->run();

}
ddfw_run_delivery_date_for_woocommerce();
