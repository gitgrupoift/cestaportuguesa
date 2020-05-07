<?php

namespace Cesta;

class Security {

    private static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
			return self::$instance;
    }
    
    public function __construct() { 
        
        add_action( 'wp_headers', array( $this, 'security_headers' ) );
        
    }
    
    function security_headers() {
        
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: no-referrer-when-downgrade");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        header("Expect-CT: enforce, max-age=300, report-uri='https://aulas.grupoift.pt/reporting/'");
        header("X-Content-Type-Options: nosniff");
        header("Feature-Policy: fullscreen 'none'; geolocation 'none'");
        
    }

}