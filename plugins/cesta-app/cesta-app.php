<?php
/**
 * Plugin Name:     Cesta Portuguesa Add-Ons
 * Plugin URI:      https://grupoift.pt
 * Description:     Ferramentas e alterações específicas para este site. Definições de segurança.
 * Author:          Carlos Matos
 * Author URI:      https://grupoift.pt
 * Text Domain:     cesta-app
 * Domain Path:     /languages
 * Version:         1.1.0
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

define('CESTA_VERSION', '1.0.0');
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

/*

function is_product_in_cart($product_id){

	$product_cart_id = WC()->cart->generate_cart_id( $product_id );
    $in_cart = WC()->cart->find_product_in_cart( $product_cart_id );
 
    if ( $in_cart ) {
        return true;
    }
	return false;
}



function if_cart_total_over($value) {
    global $woocommerce;
    $totals = $woocommerce->cart->total;
    if($totals > $value) {
        return true;
    } else {
        return false;
    }
}

add_action( 'woocommerce_cart_calculate_fees', 'change_price_of_product' );

function change_price_of_product( $cart_object ) {	
    $target_product_id = 3221;
    $totals = $cart_object->cart_contents_total * 1.088889;
    
	if(is_product_in_cart(3221)) {
        if($totals > 15){
            foreach ( $cart_object->get_cart() as $key => $value ) {
                if ( $value['product_id'] == $target_product_id ) {
                    $value['data']->set_price(3.99);
				    $new_price = $value['data']->get_price();
                    //$cart_object->calculate_totals();
                }
            }
        }
    }
    
}

