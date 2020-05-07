<?php

namespace Cesta;

class Coupons {

    private static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
			return self::$instance;
    }
    
    private function __clone() {
	   wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'cesta-app' ), '1.1.0' );
    }
    
    private function __wakeup() {
	   wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'cesta-app' ), '1.1.0' );
    }
    
    public function __construct() {}
    
}