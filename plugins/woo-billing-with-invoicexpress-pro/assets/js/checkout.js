jQuery(document).ready(function ($) {

	if ( hd_wc_ie_checkout.disable_aelia_field_feedback ) {
		//Disable WooCommerce EU VAT Assistant field feedback
		$( document ).on( 'wc_aelia_euva_eu_vat_number_validation_complete', function( ev, response ) {
			// The response variable contains the validation response.
			// We remove any feedback if the number is not valid
			if ( !response.valid ) {
				$( '#vat_number_field' ).removeClass( 'woocommerce-invalid' );
				$( '#vat_number_field' ).removeClass( 'woocommerce-validated' );
			}
		});
	};

});