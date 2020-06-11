<?php

namespace Cesta;

class ProductGift {
    
    public function __construct() {
        
        add_action( 'template_redirect', array($this, 'gift_product_to_cart') );
        
    }
    
    public function gift_product_to_cart() {
        global $woocommerce;

        $cart_total	= 30;	

        if ( $woocommerce->cart->total >= $cart_total ) {
            if ( ! is_admin() ) {
                $free_product_id = 4205;  
                $found 		= false;

                if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
                    foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
                        $_product = $values['data'];
                        if ( $_product->get_id() == $free_product_id )
                            $found = true;	                
                    }
                    if ( ! $found )
                        WC()->cart->add_to_cart( $free_product_id );
                } else {
                    WC()->cart->add_to_cart( $free_product_id );
                }        
            }
        }        
    }
}