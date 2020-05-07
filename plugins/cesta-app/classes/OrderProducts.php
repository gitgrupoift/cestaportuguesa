<?php 

namespace Cesta;

class OrderProducts {
    
    public function __construct() {

        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
        
    }
    
    
    public function enqueue( $hook ) {
        
        if ( $hook != ('woocommerce_page_hm_sbpf') ) { 		
			return;
		}
        
        wp_enqueue_style( 'bootstrap-admin-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css' );
        
        wp_enqueue_script( 'popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js', null, null, true );
		wp_enqueue_script( 'bootstrap-main', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js', null, null, true );
        wp_enqueue_script( 'font-awesome-5', 'https://use.fontawesome.com/releases/v5.0.9/js/all.js', null, null, true );
        
    }
    
    public function render_reports() {
        
        include CESTA_DIR . 'reports.php';
        
    }
    
    public function default_report_settings() {
        
    }
    /**
    * Retorna todos os produtos de todas as encomendas que estejam no status de 'picking-progress', ou seja, "Em Preparação"
    *
    * @since    1.2.0
    * @returns  array() 
    */
    public function wc_get_products_picking_progress() {
        
        // Realiza a consulta para todas as encomendas em preparação e retornas os seus IDs
        $args = array(
            'limit'     => -1,
            'status'    => 'picking-progress',
            'return'    => 'ids'
        );
        // Cria a query e a variável de retorno do array
        $query = new WC_Order_Query( $args ); 
        $orders = $query->get_orders();
        // Inicia o loop para aplicação da função de listagem dos produtos
        foreach( $orders as $order_id ) {
            // Realiza a consulta dos produtos ao acionar a função
            $product_map = $this->products_picking($order_id);
                        
        }
        
    }
    
    /**
    * Retorna os produtos e as respetivas quantidades para uma dada encomenda
    *
    * @since    1.2.0
    * @param    $order_id       integer     ID da encomenda.   
    * @returns  $products_list  array() 
    */
    public function products_picking( $order_id ) {
        
        global $woocommerce;
        $order = new WC_Order($order_id);
        
        $order_item = $order->get_items();
        $products_list = array();
        
        foreach ($order_item as $product) {
            
            $products_list['name'] = $product->get_name(); 
            $products_list['quantity'] = $product->get_quantity();
            
        }
        
        return $products_list;
        
    }
    
}