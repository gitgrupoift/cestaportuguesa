jQuery( function( $ ) {

	var order_id;
	var order_key;
	var interval = mbway_ifthenpay.interval * 1000;
	var mbway_expire = ( parseInt( mbway_ifthenpay.mbway_minutes ) + 1 ) * 60 * 1000;
	var total_interval = 0;

	function mbway_ifthen_order_check_status_init() {
		order_id = $( '#mbway-order-id' ).val();
		order_key = $( '#mbway-order-key' ).val();
		setTimeout( function(){
			mbway_ifthen_order_check_status();
		}, interval );
	}

	function mbway_ifthen_order_check_status() {
		total_interval = total_interval + interval;
		console.log( 'Checking MB WAY payment status, after '+interval+'ms (total: '+total_interval+'ms)' );
		var data = {
			action: 'wc_mbway_ifthen_order_status',
			order_id: order_id,
			order_key: order_key
		};
		$.post( woocommerce_params.ajax_url, data, function( response ) {
			var response = JSON.parse( response );
			console.log( 'Status: ' + response.order_status );
			if ( response.order_status && response.order_status == 'processing' ) {
				//DONE
				location.reload();
			} else {
				interval = Math.round( interval * 1.2 );
				if ( total_interval <= mbway_expire ) {
					setTimeout( function(){
						mbway_ifthen_order_check_status();
					}, interval );
				}
			}
		} );
	}

	if ( $( '.mbway_ifthen_for_woocommerce_table' ).length ) {
		mbway_ifthen_order_check_status_init();
	}

} );