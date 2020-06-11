<?php
/**
 * Plugin Name:     Cesta Portuguesa Add-Ons
 * Plugin URI:      https://grupoift.pt
 * Description:     Ferramentas e alterações específicas para este site. Definições de segurança.
 * Author:          Carlos Matos
 * Author URI:      https://grupoift.pt
 * Text Domain:     cesta-app
 * Domain Path:     /languages
 * Version:         1.2.0
 *
 * @package         Cesta_App
 */


if (!defined('WPINC')) {
    die;
}

use Cesta\Analytics;
use Cesta\Woocommerce;
use Cesta\AdminStyle;
use Cesta\AdminDisplay;
use Cesta\Security;
use Cesta\Optimize;
use Cesta\Elementor as CEW;
use Cesta\SEO;
//use Cesta\PriceHistory;

define('CESTA_VERSION', '1.2.0');
define('CESTA_DIR', plugin_dir_path( __FILE__ ));
define('CESTA_CLASSES', CESTA_DIR . 'classes/');
define('CESTA_DATA', CESTA_DIR . 'data/');
define('CESTA_JS', site_url() . '/wp-content/plugins/cesta-app/assets/js/');
define('CESTA_CSS', site_url() . '/wp-content/plugins/cesta-app/assets/css/');
define('CESTA_TEMPLATES', CESTA_DIR . 'templates/');
define('CESTA_BOT', CESTA_DIR . 'bot/');

require __DIR__ .'/vendor/autoload.php';

new AdminStyle();
new Security();
CEW::instance();
Optimize::instance();
AdminDisplay::get_instance();

$GLOBALS['wc_city_select'] = new Woocommerce();
new SEO();

/*
 * Cria nova tabela SQL para historial de preços na ativação
 *

$wc_price_history = new PriceHistory();
register_activation_hook( __FILE__, array( $wc_price_history,  'wc_price_history_sql' ) );*/