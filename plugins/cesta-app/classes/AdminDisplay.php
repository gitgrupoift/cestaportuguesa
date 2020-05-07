<?php 

namespace Cesta;

class AdminDisplay {
    
    private static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
			return self::$instance;
    }
    
    public function __construct() {
        
        add_action('admin_menu', array($this, 'admin_main'), 999);
        
    }
    
    public function admin_main() {
        
        global $menu;
        global $current_user;
        get_currentuserinfo();

        if($current_user->user_login !== 'hortaporta_h4lzxr')
        {
            remove_menu_page('tools.php');
            remove_menu_page('themes.php');
            remove_menu_page('options-general.php');
            remove_menu_page('plugins.php');
            remove_menu_page( 'moove-gdpr' );
            remove_menu_page( 'edit.php?post_type=acf-field-group' );
            remove_submenu_page( 'woocommerce', 'wmsc-settings' );
            remove_menu_page( 'elementor' );
            remove_menu_page( 'loco' );

        }
 
    }
    
}