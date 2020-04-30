<?php

namespace Webdados\InvoiceXpressWooCommerce\Pro;

/* WooCommerce CRUD ready */

/**
 * VAT actions.
 *
 * @package Webdados
 * @since   2.0.0
 */
class Vat {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.5
	 * @param Plugin $plugin This plugin's instance.
	 */
	public function __construct( \Webdados\InvoiceXpressWooCommerce\Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_filter( 'invoicexpress_woocommerce_external_vat', array( $this, 'external_vat_check' ), 10, 4 );
		add_action( 'wp_head', array( $this, 'manage_eu_vat' ) );

		remove_action( 'woocommerce_checkout_update_order_meta', '\Webdados\InvoiceXpressWooCommerce\Modules\Vat\var_checkout_field_update_order_meta_frontend', 100 );
		add_action(
			'woocommerce_checkout_update_order_meta', array(
				$this,
				'var_checkout_field_update_order_meta_frontend',
			), 100, 2
		);

		remove_action( 'woocommerce_checkout_process', '\Webdados\InvoiceXpressWooCommerce\Modules\Vat\validate_vat_frontend', 1000 );
		add_action(
			'woocommerce_checkout_process', array(
				$this,
				'validate_vat_frontend',
			), 1000
		);

		add_filter( 'invoicexpress_woocommerce_checkout_script_enqueue', array( $this, 'checkout_script_enqueue_aelia' ) );
		add_filter( 'invoicexpress_woocommerce_checkout_localize_script_values', array( $this, 'checkout_localize_script_values_aelia' ) );
	}

	/**
	 * Checks if Aelia WooCommerce EU VAT Assistant or WooCommerce EU VAT Number is active - This should in integrations?
	 *
	 * @since  2.0.0
	 * @return bool
	 */
	public function external_vat_check() {
		return $this->plugin->aelia_eu_vat_assistant_active || $this->plugin->woocommerce_eu_vat_field_active;
	}


	/**
	 * Loads checkout.js if needed and sets localize values
	 *
	 * @since  2.1.0
	 * @return bool
	 */
	public function checkout_script_enqueue_aelia( $bool ) {
		if ( $this->plugin->aelia_eu_vat_assistant_active && apply_filters( 'invoicexpress_woocommerce_checkout_disable_aelia_field_feedback', false ) ) {
			return true;
		}
		return $bool;
	}
	public function checkout_localize_script_values_aelia( $values ) {
		if ( $this->plugin->aelia_eu_vat_assistant_active && apply_filters( 'invoicexpress_woocommerce_checkout_disable_aelia_field_feedback', false ) ) {
			$values['disable_aelia_field_feedback'] = true;
		}
		return $values;
	}

	/**
	 * Manage Aelia EU VAT field state, if required.
	 *
	 * @return void
	 */
	public function manage_eu_vat() {

		if ( ! is_page( array( 'checkout' ) ) ) {
			return;
		}

		if ( ! class_exists( 'Aelia\WC\EU_VAT_Assistant\WC_Aelia_EU_VAT_Assistant' ) ) {
			return;
		}

		$eu_vat_settings = get_option( 'wc_aelia_eu_vat_assistant' );
		if ( isset( $eu_vat_settings['eu_vat_number_field_required'] ) && $eu_vat_settings['eu_vat_number_field_required'] != 'hidden' && $eu_vat_settings['eu_vat_number_field_required'] != 'required' ) {
			echo '<script>
				jQuery(document).ready(function ( $ ) {
					if(jQuery("#vat_number_field").is(":visible")){
						jQuery("#billing_VAT_code_field > input").attr("disabled",true);
						jQuery( "#billing_VAT_code_field" ).addClass( "hidden-vat" );
					}
					else{
						jQuery("#billing_VAT_code_field > input").attr("disabled",false);
						jQuery( "#billing_VAT_code_field" ).removeClass( "hidden-vat" );
					}

					var target = document.querySelector("#vat_number_field");
					var observer = new MutationObserver(function(mutations) {
						if(jQuery("#vat_number_field").is(":visible")){
							jQuery("#billing_VAT_code_field > input").attr("disabled",true);
							jQuery( "#billing_VAT_code_field" ).addClass( "hidden-vat" );
						}
						else{
							jQuery("#billing_VAT_code_field > input").attr("disabled",false);
							jQuery( "#billing_VAT_code_field" ).removeClass( "hidden-vat" );
						}
					});
					observer.observe(target, {
						attributes: true
					});
				});
				</script>';
		}
	}

	public function var_checkout_field_update_order_meta_frontend( $order_id, $post ) {
		$updated = false;

		$order_object = wc_get_order( $order_id );

		// Aelia WooCommerce EU VAT Assistant - https://wordpress.org/plugins/woocommerce-eu-vat-assistant/
		$vat_number = $order_object->get_meta( 'vat_number' );
		if ( ! empty( $vat_number ) ) {
			$order_object->update_meta_data( '_billing_VAT_code', $vat_number );
			$updated = true;
		} elseif ( isset( $_POST['vat_number'] ) && ! empty( $_POST['vat_number'] ) ) {
			$order_object->update_meta_data( '_billing_VAT_code', sanitize_text_field( $_POST['vat_number'] ) );
			$updated = true;
		}

		// WooCommerce EU VAT Number - https://woocommerce.com/products/eu-vat-number/
		$vat_number = $order_object->get_meta( '_vat_number' );
		if ( ! empty( $vat_number ) ) {
			$order_object->update_meta_data( '_billing_VAT_code', $vat_number );
			$updated = true;
		} elseif ( isset( $_POST['_vat_number'] ) && ! empty( $_POST['_vat_number'] ) ) {
			$order_object->update_meta_data( '_billing_VAT_code', sanitize_text_field( $_POST['_vat_number'] ) );
			$updated = true;
		}

		// Fallback to our own field
		if ( isset( $_POST['billing_VAT_code'] ) && ! empty( $_POST['billing_VAT_code'] ) ) {
			$order_object->update_meta_data( '_billing_VAT_code', sanitize_text_field( $_POST['billing_VAT_code'] ) );
			$updated = true;
		}

		// NIF (Num. de Contribuinte Português) for WooCommerce.
		if ( ! $updated && isset( $_POST['billing_nif'] ) && ! empty( $_POST['billing_nif'] ) ) {
			$order_object->update_meta_data( '_billing_VAT_code', sanitize_text_field( $_POST['billing_nif'] ) );
			$updated = true;
		}

		// Apply EU exemption?
		if (
			// There's a VAT number.
			$updated
			// Client is from EU - This is only available on OrderActions but maybe it's not necessary because we check for _vat_number_validated === 'yes' which must mean it's ok
			&& ( $this->is_customer_from_eu( $order_object->get_billing_country() ) )
			// Store is Portuguese.
			&& ( '1' == get_option( 'hd_wc_ie_plus_tax_country' ) )
			// One of the plugins is active.
			&& ( $this->external_vat_check() )
			// No tax in order.
			&& ( 0 == floatval( $order_object->get_total_tax() ) )
			// Validated by Aelia or by WooCommerce EU VAT Field
			&& (
				( 'valid' == $order_object->get_meta( '_vat_number_validated' ) ) //Aelia
				||
				( 'true' == $order_object->get_meta( '_vat_number_is_validated' ) ) //WooCommerce EU VAT Field
			)
		) {
			// Artigo 14.º do RITI.
			$order_object->update_meta_data( '_billing_tax_exemption_reason', 'M16' );
			$order_object->add_order_note( __( 'VAT exemption applied (Artigo 14.º do RITI)', 'woo-billing-with-invoicexpress' ) );
		}
		$order_object->save();
	}

	//VAT Validation only for our field or Aelia
	public function validate_vat_frontend() {

		$eu_vat_settings = get_option( 'wc_aelia_eu_vat_assistant' );
		$vat_field       = get_option( 'hd_wc_ie_plus_vat_field' );
		$vat_mandatory   = get_option( 'hd_wc_ie_plus_vat_field_mandatory' );
		if (
			$vat_field
			&& $vat_mandatory
			&& ( $eu_vat_settings['eu_vat_number_field_required'] == 'required_if_company_field_filled_eu_only' || $eu_vat_settings['eu_vat_number_field_required'] == 'required_if_company_field_filled' )
		) {
			if ( isset( $_POST['vat_number'] ) && empty( $_POST['vat_number'] ) ) {
				if (
					! isset( $_POST['billing_VAT_code'] )
					|| ( $vat_mandatory && isset( $_POST['billing_VAT_code'] ) && ! $_POST['billing_VAT_code'] )
				) {
					wc_add_notice( __( 'VAT is a required field.', 'woo-billing-with-invoicexpress' ), 'error' );
				}
			}
		}
	}

	/**
	 * Check if the current customer is from an EU country.
	 *
	 * @param  string $country The customer country code.
	 * @return bool
	 */
	public function is_customer_from_eu( $country ) {

		if ( empty( $country ) ) {
			return false;
		}

		$key = array_search( $country, $this->get_eu_vat_countries(), true );
		if ( false !== $key ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the list of EU VAT countries.
	 *
	 * @return array
	 */
	public function get_eu_vat_countries() {
		if ( version_compare( WC_VERSION, '4.0.0', '<' ) ) {
			return WC()->countries->get_european_union_countries( 'eu_vat' );
		} else {
			return array_merge( WC()->countries->get_european_union_countries(), array( 'MC', 'IM', 'GB' ) );
		}
	}
}
