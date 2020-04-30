<?php 

namespace Cesta;

class OrderStatus {
    
    public function __construct() {
        
        add_action('init', array($this, 'wp_order_status_add'));
        add_filter('wc_order_statuses', array($this, 'wc_order_status_add'));

    }
    
    /**
    * Cria novo status no Wordpress
    * $order_status -> wc-shipping-progress
    *
    */
    public function wp_order_status_add() {
        
        register_post_status( 'wc-shipping-progress', array(
            'label'                     => 'Em distribuição',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Em distribuição (%s)', 'Em distribuição (%s)' )
        ) );
        
    }
    
    public function wc_order_status_add( $order_statuses ) {

        $order_statuses['wc-shipping-progress'] = 'Em distribuição';
        return $order_statuses;
        
    }
    
    public function shipping_change() {
        
        $tomorrow = date('l', time()+86400);
        $args = array(
            'status' => 'processing',
            'limit' => -1,
            'meta_key' => 'ddfw_delivery_date',
            'meta_value' => $tomorrow,
        );
        
        $orders = wc_get_orders( $args );
        
    }
    

}