<?php
namespace Webdados\InvoiceXpressWooCommerce;

use Webdados\InvoiceXpressWooCommerce\JsonRequest as JsonRequest;
use Webdados\InvoiceXpressWooCommerce\CountryTranslation as CountryTranslation;

/* WooCommerce CRUD ready */
/* JSON API ready */

class ClientChecker {

	/*
	 * Checks if the client is already created and referenced in the order, or else creates it. It also updates the client if needed.
	 */
	public function maybeCreateClient( $client_name, $order_object ) {

		$needs_update  = false;
		$create_client = false;
		$vat           = false;
		$customer      = false;
		//Order already has client ID and client code?
		if ( ( $client_id = $order_object->get_meta( 'hd_wc_ie_plus_client_id' ) ) && ( $client_code = $order_object->get_meta( 'hd_wc_ie_plus_client_code' ) ) ) {
			$needs_update = true; //We always update if we already know the client. This ways we only do one API call (update) instead of potentialy two (get and update)
		} else {
			if ( $vat = $order_object->get_meta( '_billing_VAT_code' ) ) {

				//Allow the Pro plugin to get it by ID and VAT
				$client_info = apply_filters( 'invoicexpress_woocommerce_get_client_info', false, $order_object );

				//We don't have the client and the order has vat so let's try to find it
				if ( ! $client_info ) {
					//Try to get it by name then - Old way
					$client_info = $this->getClientByNameAndVat( $client_name, $vat );
				}

				//Do we have it?
				if ( $client_info ) {
					//Got the client
					$client_id = $client_info->id;
					if ( $client_info->code == '' ) {
						//No client code - let's create one
						$client_code = apply_filters( 'invoicexpress_woocommerce_new_client_code', $vat.'_'.$order_object->get_id(), $order_object );
						$needs_update = true;
					} else {
						$client_code = $client_info->code;
						$countries      = new CountryTranslation();
						$countries_list = $countries->get_countries();
						$country = '';
						if ( isset( $countries_list[ $order_object->get_billing_country() ] ) ) {
							$country  = CountryTranslation::translate( $countries_list[ $order_object->get_billing_country() ] );
						}
						//Needs update?
						if (
							$client_info->country != $country
							||
							$client_info->city != $order_object->get_billing_city()
							||
							$client_info->email != $order_object->get_billing_email()
							||
							$client_info->phone != $order_object->get_billing_phone()
							||
							$client_info->postal_code != $order_object->get_billing_postcode()
							||
							$client_info->address != $order_object->get_billing_address_1() . PHP_EOL . $order_object->get_billing_address_2()
							||
							$client_info->language != apply_filters( 'invoicexpress_woocommerce_document_language', 'pt', $order_object )
						) {
							$needs_update = true;
						}
					}
				} else {
					$create_client = true;
				}

			} else {
				$create_client = true;
			}
		}

		if ( $create_client ) {
			//Create the client
			if ( $vat ) {
				$client_code = apply_filters( 'invoicexpress_woocommerce_new_client_code', $vat.'_'.$order_object->get_id(), $order_object );
			} else {
				$client_code = apply_filters( 'invoicexpress_woocommerce_new_client_code', 'C'.time().'_'.$order_object->get_id(), $order_object );
			}
			$client_id = $this->createTheClient( $client_code, $client_name, $order_object );
		} elseif ( $needs_update ) {
			//Update the client
			$this->updateTheClient( $client_id, $client_code, $client_name, $order_object );
		}

		// Set the client_id and client_code on the user for later usage
		if ( $user_id = $order_object->get_customer_id() ) {
			if ( $customer = new \WC_Customer( $user_id ) ) {
				if ( $customer->get_id() ) {
					if ( ! empty( $client_id ) ) $customer->update_meta_data( 'hd_wc_ie_plus_client_id', $client_id );
					if ( ! empty( $client_code ) ) $customer->update_meta_data( 'hd_wc_ie_plus_client_code', $client_code );
					$customer->save();
				}
			}
		}

		// We should be dealing with errors
		return array(
			'client_id'   => $client_id,
			'client_code' => $client_code,
		);

	}

	/*
	 * Searches InvoiceXpress API for the client by name, and then checks if the VAT is the same. This is not very reliable, but it's the best we got.
	 */
	public function getClientByNameAndVat( $client_name, $vat ) {
		$params = array(
			'request' => 'clients/find-by-name.json',
			'args'    => array(
				'client_name' => $client_name,
			),
		);
		$json_request = new JsonRequest( $params );
		$return = $json_request->getRequest();
		if ( $return['success'] ) {
			$client_info = $return['object']->client;
			if ( trim( $client_info->fiscal_id ) == $vat ) {
				return $client_info;
			}
		}
		return false;
	}

	/*
	 * Translates WooCommerce country to InvoiceXpress country
	 */
	public function getClientInvoiceXpressCountry( $order_object ) {
		$countries      = new CountryTranslation();
		$countries_list = $countries->get_countries();
		$country = '';
		if ( isset( $countries_list[ $order_object->get_billing_country() ] ) ) {
			$country  = CountryTranslation::translate( $countries_list[ $order_object->get_billing_country() ] );
		}
		return $country;
	}

	/*
	 * Updates the client on InvoiceXpress
	 */
	public function updateTheClient( $client_id, $client_code, $client_name, $order_object ) {
		$params = array(
			'request' => 'clients/'.$client_id.'.json',
			'args'    => array(
				'client' => array(
					'name'        => $client_name,
					'code'        => $client_code,
					'email'       => $order_object->get_billing_email(),
					'phone'       => $order_object->get_billing_phone(),
					'address'     => $order_object->get_billing_address_1() . PHP_EOL . $order_object->get_billing_address_2(),
					'postal_code' => $order_object->get_billing_postcode(),
					'language'    => apply_filters( 'invoicexpress_woocommerce_document_language', 'pt', $order_object ),
					'city'        => $order_object->get_billing_city(),
					'country'     => $this->getClientInvoiceXpressCountry( $order_object ),
					'fiscal_id'   => $order_object->get_meta( '_billing_VAT_code' ), //Theoretically not possible
				),
			),
		);
		$json_request = new JsonRequest( $params );
		$return = $json_request->putRequest();
		if ( $return['success'] ) {
			// OK
		} else {
			// We should be dealing with errors
		}
	}

	/*
	 * Creates the client on InvoiceXpress
	 */
	public function createTheClient( $client_code, $client_name, $order_object ) {
		$params = array(
			'request' => 'clients.json',
			'args'    => array(
				'client' => array(
					'name'        => $client_name,
					'code'        => $client_code,
					'email'       => $order_object->get_billing_email(),
					'phone'       => $order_object->get_billing_phone(),
					'address'     => $order_object->get_billing_address_1() . PHP_EOL . $order_object->get_billing_address_2(),
					'postal_code' => $order_object->get_billing_postcode(),
					'language'    => apply_filters( 'invoicexpress_woocommerce_document_language', 'pt', $order_object ),
					'city'        => $order_object->get_billing_city(),
					'country'     => $this->getClientInvoiceXpressCountry( $order_object ),
					'fiscal_id'   => $order_object->get_meta( '_billing_VAT_code' ),
				),
			),
		);
		$json_request = new JsonRequest( $params );
		$return = $json_request->postRequest();
		if ( $return['success'] ) {
			return $return['object']->client->id;
		} else {
			// We should be dealing with errors - Check if client exists already for example, with getByCode...
			return 0;
		}
	}

}
