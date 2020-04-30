<?php 

namespace Cesta;

class QuantityCart {
    
    public function __construct() {
        
        /**
        * Orienta templates do Woocommerce para busca no diretório deste plugin
        * 
        * @var  $template
        * @var  $template_name
        * @var  @template_path
        */
        add_filter(
            'woocommerce_locate_template',
            function ( $template, $template_name, $template_path ) {

                $show_on_product_page = apply_filters( 'show_on_product_page', true );
                $show_on_cart_page    = apply_filters( 'show_on_cart_page', true );

                if ( false === $show_on_product_page && is_product() ) {
                    return $template;
                }

                if ( false === $show_on_cart_page && is_cart() ) {
                    return $template;
                }

                global $woocommerce;

                $_template     = $template;
                $plugin_path   = CESTA_TEMPLATES;
                $template_path = ( ! $template_path ) ? $woocommerce->template_url : null;
                $template      = locate_template( array( $template_path . $template_name, $template_name ) );

                if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
                    $template = $plugin_path . $template_name;
                }

                if ( ! $template ) {
                    $template = $_template;
                }

                return $template;
            },
            1,
            3
        );
        
        /**
        * Adiciona os ficheiros CSS e JS para a funcionalidade
        * 
        */
        add_action(
            'wp_enqueue_scripts',
            function () {

                wp_enqueue_script( 'qty-script', CESTA_JS .  'qty.js', array( 'jquery' ), CESTA_VERSION, true );
                wp_enqueue_style( 'qty-style', CESTA_CSS . 'qty.css', null, CESTA_VERSION, 'screen' );

                $show_on_product_page = apply_filters( 'show_on_product_page', true );
                $show_on_cart_page    = apply_filters( 'show_on_cart_page', true );

                if ( false === $show_on_product_page && is_product() ) {
                    wp_dequeue_script( 'qty-script' );
                    wp_dequeue_style( 'qty-style' );
                }

                if ( false === $show_on_cart_page && is_cart() ) {
                    wp_dequeue_script( 'qty-script' );
                    wp_dequeue_style( 'qty-style' );
                }

            }
        );
        
    }

}