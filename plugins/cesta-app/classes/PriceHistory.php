<?php
/* Cria e gere sistema de registo e manutenção do historial de preços e alterações respetivas, por produto.
 * @since 1.2.0
 * 
 *
 */

namespace Cesta;

class PriceHistory {
    
    protected static $table_name = 'woocommerce_prices_history_products';
    
    public function __construct() {}
    
    public function wc_price_history_sql() {

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . self::$table_name;

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
			product_id mediumint(9) NOT NULL,
			data text NOT NULL,
			UNIQUE KEY id (id)
        ) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		  dbDelta( $sql );
    }
    
    public function wc_price_history_update() {
        
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;
        
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $this->wc_price_history_sql();
        }
        
        $results = $wpdb->get_results( "SELECT * FROM {$table_name}", ARRAY_A );
        
        $args = array(
            'post_type'      => array('product'),
        	'posts_per_page' => -1,
        );
        $products = get_posts($args);
        
        foreach ($products as $product) {
            $id = $product->ID;
            $_product = wc_get_product( $id );
            
            if( $_product->is_type('variable') ){
                $variations = $_product->get_available_variations();
                
                foreach ($variations as $variation) {
                    $id_variation = $variation['variation_id'];
                    $_product_variation = wc_get_product( $id_variation ); 
                    $this->wc_db_price_handler( $id_variation, $_product_variation, $results );
                }
            } else {
                $this->wc_db_price_handler( $id, $_product, $results );
            }
            
            update_option('wc_prices_last_updated', current_time( 'mysql' ));
        }
        
    }
    
    private function wc_db_price_handler( $id, $_product, $results ){
        
        $regular_price = $_product->get_regular_price();
        $sale_price = $_product->get_sale_price();
        $position = array_search($id, array_column($results, 'product_id'));
        
        if( $position === false ){
            $product_prices_array = array(
                current_time( 'mysql' ) => array(
                    'r_p' => $regular_price,
    	    		's_p' => $sale_price,
                ),
            );
            
            $this->wc_insert_first_prices( $id, $product_prices_array );
        } else {
            $prices_history = unserialize( $results[$position]['data'] );
            $current_prices = end($prices_history);
            
            if( $regular_price == $current_prices['r_p'] && $sale_price == $current_prices['s_p'] ) return;
            
            $prices_history[current_time( 'mysql' )] = array(
                'r_p' => $regular_price,
        		's_p' => $sale_price,
            );
            
            $this->update_history_prices( $id, $prices_history  );
        }
        
    }
    
    private function wc_insert_first_prices($id, $product_prices_array) {
        
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;
        
        $wpdb->insert(
            $table_name,
            array(
                'product_id' => $id,
                'data'       => serialize( $product_prices_array )
            ),
            array(
                '%d',
                '%s'
            )
        );
        
    }
    
    private function update_history_prices($id, $product_prices_array) {
        
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;
        
        $wpdb->update(
            $table_name,
            array(
                'data'       => serialize( $product_prices_array )
            ),
            array( 'product_id' => $id ),
            array(
                '%s'
            ),
            array( '%d' )
        );
        
    }
    
}