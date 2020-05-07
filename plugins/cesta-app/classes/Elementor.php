<?php 

namespace Cesta;
use \Cesta\Elementor\Widgets\OneProduct as One;
use \Cesta\Elementor\Widgets\PdfView as Pdf;

final class Elementor {
    
    private static $_instance = null;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}
    
    public function __construct() {

		add_action( 'plugins_loaded', [ $this, 'init' ] );

	}

	public function init() {
        
        if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return;
		}
        
        add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
		add_action( 'elementor/controls/controls_registered', [ $this, 'init_controls' ] );
        add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories' ] );
        
    }
    
    public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requer o "%2$s" instalado e ativado.', 'elementor-test-extension' ),
			'<strong>' . esc_html__( 'Elementor Cesta Portuguesa', 'cesta-app' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'cesta-app' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}
    
    function add_elementor_widget_categories( $elements_manager ) {

        $elements_manager->add_category(
            'cesta-app',
            [
                'title' => __( 'Cesta Portuguesa', 'cesta-app' ),
                'icon' => 'fas fa-carrot',
            ]
        );

    }
    
    public function init_widgets() {

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new One() );
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Pdf() );

	}

	public function init_controls() {

		//\Elementor\Plugin::$instance->controls_manager->register_control( 'control-type-', new \Test_Control() );

	}
    
    
}