<?php 

namespace Cesta;
use Cesta\QuantityCart;
use Cesta\OrderStatus;

class Woocommerce {
    
    protected $price;
    
    private $cities;
    private $dropdown_cities;
    
    public function __construct() {
        
        add_filter('woocommerce_get_price_html', array($this, 'custom_price_message'));
        add_filter('woocommerce_billing_fields', array($this, 'required_fields'), 10, 1);
        add_filter( 'woocommerce_shipping_fields', array( $this, 'shipping_fields' ), 10, 2 );
        add_filter( 'woocommerce_form_field_city', array( $this, 'form_field_city' ), 10, 4 );
        add_filter( 'woocommerce_default_address_fields', array( $this, 'reorder_places' ));
        
        add_filter('woocommerce_states', array($this, 'portugal_concelhos'));
        add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
        add_action( 'woocommerce_archive_description', array( $this, 'back_shop_category' ) );
        add_action( 'woocommerce_before_cart_table', array( $this, 'back_shop_category' ) );
        
        add_filter('manage_edit-shop_order_columns', array($this, 'location_columns'));
        add_filter('manage_posts_custom_column', array($this, 'location_columns_data'));
        add_filter('manage_edit-shop_order_sortable_columns', array($this, 'sortable_product_columns'));

        $this->includes();
        
    }
    
    protected function includes() {
        
        new QuantityCart();
        new OrderStatus();
        
    }
    
    /**
    * Inclui colunas por localidade
    * @param    $columns    string  Nome da coluna a ordenar.
    */
    public function location_columns( $columns ) {
        
        $new_columns = ( is_array( $columns ) ) ? $columns : array();
        
        $new_columns['localizacao'] = 'Localidade';
        
        return $new_columns;
        
    }
    
    public function location_columns_data( $column ) {
        
        global $post;
 
        if ( 'localizacao' === $column ) {

            $order = wc_get_order( $post->ID );
            echo '<ul class="localizacao"><li class="badge">';
            echo $order->get_shipping_state() . '</li>';
            echo '<li class="freguesia">';
            echo $order->get_shipping_city() . '</li></ul>';
        }

    }
    
    /**
    * Permite a ordenação da coluna das datas de entrega no view das encomendas
    * @param    $columns    string  Nome da coluna a ordenar.
    */
    public function sortable_product_columns( $columns ) {
        
        $columns['ddfw_delivery_date'] = 'ddfw_delivery_date';
        return $columns;
        
    }
    
    public function closing_column_orderby( $query ) {  
        if( ! is_admin() )  
            return;  

        $orderby = $query->get( 'orderby');  

        if( 'ddfw_delivery_date' == $orderby ) {  
            $query->set('meta_key','ddfw_delivery_date');
        }  
    } 

    
    public function back_shop_category() {
        ?>

            <a class="button wc-backward" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"> <?php _e( 'Return to shop', 'woocommerce' ) ?> </a>

        <?php
        
    }
    
    public function delivery_hours( $checkout ) {

        echo '<h2>'.__('Horário da Entrega').'</h2>';

        woocommerce_form_field( 'daypart', array(
            'type'          => 'select',
            'class'         => array( 'wps-drop' ),
            'label'         => __( 'Preferência de Horário' ),
            'options'       => array(
                'blank'		=> __( 'Escolha um horário...', 'cesta-app' ),
                'morning'	=> __( 'Antes das 12h00', 'cesta-app' ),
                'afternoon'	=> __( 'Das 12h00 às 16h00', 'cesta-app' ),
                'evening' 	=> __( 'Após as 16h00', 'cesta-app' )
            )
     ),

        $checkout->get_value( 'daypart' ));

    }
    
    public function order_data( $order_id ) {
        
        if ($_POST['daypart']) update_post_meta( $order_id, 'daypart', esc_attr($_POST['daypart']));
        
    }
    
    public function order_details( $order_id ) {
        
        echo get_post_meta( $order->id, 'daypart', true );
        
    }
    
    /**
    * Insere unidade ao lado do preço, consoante preenchimento do campo no backend
    * @param    auto    $price      Render dos preços nos produtos
    * @var      html    $mt         Modificação ou inserção ao campo
    */
    public function custom_price_message( $price ) {
        
        $mt = ' <span style="font-size: 0.65em;">' . get_field('unidade_do_produto') . '</span>';
        
        return $price . $mt;
    }

    public function shipping_fields( $fields, $country ) {
        
        $fields['shipping_city']['type'] = 'city';
        return $fields;
    }
    
    public function reorder_places( $fields ) {
        
        $fields['city']['priority'] = 80;
        $fields['state']['priority'] = 70;
        $fields['state']['required'] = true;
        
        return $fields;
        
    }
    /**
    *
    *
    */
    public function required_fields($fields) {
        
        $fields['billing_state']['required'] = true;
        $fields['billing_city']['type'] = 'city';
        
        return $fields;
    }
    
    /**
    * Adiciona conselhos à entregas e faturação
    *
    * @return   $states    string    Conselhos a adicionar.
    */
    public function portugal_concelhos($states) {
        
        $states['PT'] = array(
            'VC'    =>  'Viana do Castelo',
            'BA'    =>  'Barcelos',
            'PL'    =>  'Ponte de Lima'
        );
        
        return $states;
        
    }
    
    public function get_cities( $cc = null ) {
			
        if ( empty( $this->cities ) ) {
            $this->load_country_cities();
        }

        if ( ! is_null( $cc ) ) {
            return isset( $this->cities[ $cc ] ) ? $this->cities[ $cc ] : false;
        } else {
            return $this->cities;
        }
    }
    
    public function load_country_cities() {
			
        global $cities;

        $allowed = array_merge( WC()->countries->get_allowed_countries(), WC()->countries->get_shipping_countries() );

        if ( $allowed ) {
				
            foreach ( $allowed as $code => $country ) {
                if ( ! isset( $cities[ $code ] ) && file_exists( CESTA_DATA . 'cities/' . $code . '.php' ) ) {
						
                    include( CESTA_DATA . 'cities/' . $code . '.php' );
				}
            }
        }

        $this->cities = apply_filters( 'wc_city_select_cities', $cities );
    }
    
    private function add_to_dropdown($item) {
        
        $this->dropdown_cities[] = $item;
        
    }
    
    public function form_field_city( $field, $key, $args, $value ) {
        
        if ( ( ! empty( $args['clear'] ) ) ) {
            $after = '<div class="clear"></div>';
        } else {
            $after = '';
        }
        
        if ( $args['required'] ) {
				
            $args['class'][] = 'validate-required';
            $required = ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce'  ) . '">*</abbr>';
			
        } else {
            $required = '';
        }
        
        $custom_attributes = array();
			
        if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
				
            foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
					
                $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
            }
        }
        
        if ( ! empty( $args['validate'] ) ) {
            foreach( $args['validate'] as $validate ) {
					
                $args['class'][] = 'validate-' . $validate;
            }
        }
        
        $field  = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $args['id'] ) . '_field">';
			
        if ( $args['label'] ) {
				
            $field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) .'">' . $args['label']. $required . '</label>';
        }
        
        $country_key = $key == 'billing_city' ? 'billing_country' : 'shipping_country';			
        $current_cc  = WC()->checkout->get_value( $country_key );
        
        $state_key = $key == 'billing_freg' ? 'billing_state' : 'shipping_state';			
        $current_sc  = WC()->checkout->get_value( $state_key );
        
        $cities = $this->get_cities( $current_cc );
        
        if ( is_array( $cities ) ) {
				
            $field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="city_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '">
					<option value="">'. __( 'A sua freguesia...', 'woocommerce' ) .'</option>';
				
            if ( $current_sc && $cities[ $current_sc ] ) {
					$this->dropdown_cities = $cities[ $current_sc ];
				
            } else {
					
                $this->dropdown_cities = [];
					array_walk_recursive( $cities, array( $this, 'add_to_dropdown' ) );
					sort( $this->dropdown_cities );
            }

				
                foreach ( $this->dropdown_cities as $city_name ) {

                    $field .= '<option value="' . esc_attr( $city_name ) . '" '.selected( $value, $city_name, false ) . '>' . $city_name .'</option>';
                }

                $field .= '</select>';

			} else {

				$field .= '<input type="text" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
			}
        
        if ( $args['description'] ) {
				
            $field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
        }

        $field .= '</p>' . $after;

        return $field;
        
    }
    
    public function load_scripts() {
			
        if ( is_cart() || is_checkout() || is_wc_endpoint_url( 'edit-address' ) ) {
				
            $city_select_path = CESTA_JS . 'city-select.js';
				
            wp_enqueue_script( 'wc-city-select', $city_select_path, array( 'jquery', 'woocommerce' ), CESTA_VERSION, true );

				
            $cities = json_encode( $this->get_cities() );
				wp_localize_script( 'wc-city-select', 'wc_city_select_params', array(
					'cities' => $cities,
					'i18n_select_city_text' => esc_attr__( 'A sua freguesia...', 'woocommerce' )
                ) );
        }
    }
    

    
}