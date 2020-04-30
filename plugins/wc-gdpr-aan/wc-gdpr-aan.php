<?php

/**
 * Disclaimer, info and license disclosure
 *
 * This plugin 
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://meuppt.pt
 * @since             1.0.0
 * @package           Wc_Gdpr_Aan
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce GDPR All Around Notices
 * Plugin URI:        https://meuppt.pt/rgpd
 * Description:       Administre e edite mensagens necessárias para conformidade com o novo Regulamento Geral de Proteção de Dados (RGPD / GDPR) e outras disposições que necessite incluir no site e na loja, inserindo textos em qualquer parte das páginas principais do WooCommerce - Loja, Carrinho, Minha Conta e Checkout - de forma automatizada e sem qualquer inserção de código.
 * Version:           1.0.0
 * Author:            Carlos Matos | Grupo IFT
 * Author URI:        https://grupoift.pt
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-gdpr-aan
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
define( 'WC_AAN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wc-gdpr-aan-activator.php
 */
function activate_wc_gdpr_aan() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-gdpr-aan-activator.php';
	Wc_Gdpr_Aan_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wc-gdpr-aan-deactivator.php
 */
function deactivate_wc_gdpr_aan() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-gdpr-aan-deactivator.php';
	Wc_Gdpr_Aan_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wc_gdpr_aan' );
register_deactivation_hook( __FILE__, 'deactivate_wc_gdpr_aan' );



/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wc-gdpr-aan.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wc_gdpr_aan() {

	$plugin = new Wc_Gdpr_Aan();
	$plugin->run();

}
run_wc_gdpr_aan();
