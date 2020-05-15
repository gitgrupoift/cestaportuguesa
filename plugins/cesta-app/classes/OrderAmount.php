<?php 

namespace Cesta;

class OrderAmount {
    
    public function __construct() {
        
        add_action( 'woocommerce_check_cart_items', array($this, 'minimum_order') );
        //add_action( 'woocommerce_before_checkout_shipping_form', array($this, 'zone_days_delivering') );
        
    }
    
    public function minimum_order() {
        global $woocommerce;

        $minimum = 20;
        $concelho = array('BR','BA','ES','CV','CM','PC','AV','AM','GU','VV','CB','CL','VZ','FM','TB','FF','VM','PH','VL','PB','ME','MO');
        
        $cart_tot_order = WC()->cart->total;
        
        if( $cart_tot_order < $minimum && in_array( WC()->customer->get_shipping_state(), $concelho )  ) {
            
            wc_add_notice( sprintf( '<strong>Apenas encomendas a partir de €%s são entregues para esta zona.</strong>' 
	        	. '<br />Encomenda Atual: €%s.',
	        	$minimum,
                $cart_tot_order	),
	        'error' );
            
        }
        
    }
    
    /*
    * Sistema de bloqueio dos dias consoante a região
    * Acionar via $.ajax com o Datepicker
<script type="text/javascript">
    $(function () {
        var date = new Date();
        var currentMonth = date.getMonth(); // current month
        var currentDate = date.getDate(); // current date
        var currentYear = date.getFullYear(); //this year
        $("#<%= tbxRequestDeliveryDate.ClientID %>").datepicker({
            changeMonth: true, // this will allow users to chnage the month
            changeYear: true, // this will allow users to chnage the year
            minDate: new Date(currentYear, currentMonth, currentDate),
            beforeShowDay: function (date) {
                if (date.getDay() == 0 || date.getDay() == 1 || date.getDay() == 6) {
                    return [false, ''];
                } else {
                    return [true, ''];
                }
            }
        });
    });
</script>
    */
    //update_option('ddfw_disable_friday', 5);
    public function zone_days_delivering() {
        global $woocommerce;
        $concelho = array('BR','BA','ES','CV','CM','PC','AV');
        
        if( in_array( WC()->customer->get_shipping_state(), $concelho )  ) {
            update_option('ddfw_disable_friday', 5);
        } else {
            update_option('ddfw_disable_friday', '');
        }
    }
    
}