<?php
namespace Webdados\InvoiceXpressWooCommerce\Modules\DevolutionGuide;

use Webdados\InvoiceXpressWooCommerce\BaseController as BaseController;
use Webdados\InvoiceXpressWooCommerce\JsonRequest as JsonRequest;
use Webdados\InvoiceXpressWooCommerce\CountryTranslation as CountryTranslation;
use Webdados\InvoiceXpressWooCommerce\ClientChecker as ClientChecker;
use Webdados\InvoiceXpressWooCommerce\Notices as Notices;

/* WooCommerce CRUD ready */
/* JSON API ready */

class DevolutionGuideController extends BaseController {

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		if ( get_option( 'hd_wc_ie_plus_devolution_guide' ) ) {

			add_filter(
				'woocommerce_order_actions', array(
					$this,
					'order_actions',
				), 10, 1
			);

			add_action(
				'woocommerce_order_action_hd_wc_ie_plus_devolution_guide', array(
					$this,
					'doAction',
				), 11, 2
			);

			add_action(
				'woocommerce_process_shop_order_meta', array(
					$this,
					'save_meta',
				), 10, 1
			);
		}
	}

	/**
	 * Add order action.
	 *
	 * @since  2.0.0 Code review.
	 * @since  1.0.0
	 * @param  array $actions Order actions.
	 * @return array
	 */
	public function order_actions( $actions ) {
		global $post;
		$order_object = wc_get_order( $post->ID );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return $actions;

		$generate_invoice = esc_html( sprintf(
			'%1$s (%2$s)',
			sprintf(
				/* translators: %s: document type */
				__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
				__( 'Return delivery note', 'woo-billing-with-invoicexpress' )
			),
			__( 'PDF', 'woo-billing-with-invoicexpress' )
		) );

		$devolution_guide_id = $order_object->get_meta( 'hd_wc_ie_plus_devolution_guide_id' );
		if ( empty( $devolution_guide_id ) ) {
			$symbol = '';
		} else {
			$symbol = '&#x2713;';
		}

		$actions['hd_wc_ie_plus_devolution_guide'] = trim( sprintf(
			'%s %s: %s',
			$symbol,
			esc_html__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
			$generate_invoice
		) );

		return $actions;
	}

	public function doAction( $order_object, $mode = 'manual' ) {

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;
		
		$devolution_guide_id = $order_object->get_meta( 'hd_wc_ie_plus_devolution_guide_id' );

		$debug = 'Checking if Return delivery note document should be issued';
		do_action( 'invoicexpress_woocommerce_debug', $debug, $order_object, array(
			'hd_wc_ie_plus_devolution_guide_id'  => $devolution_guide_id,
		) );

		if ( empty( $devolution_guide_id ) ) {

			// phpcs:disable WordPress.WP.TimezoneChange.timezone_change_date_default_timezone_set
			date_default_timezone_set( 'Europe/Lisbon' );
			// phpcs:enable WordPress.WP.TimezoneChange.timezone_change_date_default_timezone_set

			// Prevent auto invoice/receipt trigger when this document is created - Shouldn't we set this back to 0 after the document issuing?
			$order_object->update_meta_data( 'hd_wc_ie_plus_stop_automation', 1 );
			$order_object->save();

			$vat = $order_object->get_meta( '_billing_VAT_code' );
			// Check for VAT number.
			if ( get_option( 'hd_wc_ie_plus_vat_field_mandatory' ) && empty( $vat ) ) {
				/* Add notice */
				$error_notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
					__( 'The VAT number is required', 'woo-billing-with-invoicexpress' )
				);
				if ( $mode == 'manual' ) { //Always manual in Devolutions, but we may change that in the future
					Notices::add_notice(
						$error_notice,
						'error'
					);
				} else {
					if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && $mode == 'automatic' && $error_notice ) { //Always manual in Devolutions, but we may change that in the future
						$this->sendErrorEmail( $order_object, $error_notice );
					}
				}
				do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object );
				return;
			}

			$client_name = $this->get_document_client_name( $order_object );
			$checker = new ClientChecker();
			$client_info = $checker->maybeCreateClient( $client_name, $order_object );

			$client_data = array(
				'name' => $client_name,
				'code' => $client_info['client_code'],
			);
			
			$items_data = $this->getOrderItemsForDocument( $order_object, 'devolution_guide', array(
				'no_values' => get_option( 'hd_wc_ie_plus_transport_guide_no_value' ) ? true : false
			) );

			$address_from_data = array(
				'detail'      => get_option( 'hd_wc_ie_plus_warehouse_address' ),
				'city'        => get_option( 'hd_wc_ie_plus_warehouse_city' ),
				'postal_code' => get_option( 'hd_wc_ie_plus_warehouse_post_code' ),
				'country'     => get_option( 'hd_wc_ie_plus_warehouse_country' ),
			);
			if ( trim( $address_from_data['postal_code'] ) == '' ) $address_from_data['postal_code'] = '-';

			$countries      = new CountryTranslation();
			$countries_list = $countries->get_countries();
			$country = '';
			if ( isset( $countries_list[ $order_object->get_shipping_country() ] ) ) {
				$country = CountryTranslation::translate( $countries_list[ $order_object->get_shipping_country() ] );
			}

			$address_to_data = array(
				'detail'      => $order_object->get_shipping_address_1() . ' ' . $order_object->get_shipping_address_2(),
				'city'        => $order_object->get_shipping_city(),
				'postal_code' => $order_object->get_shipping_postcode(),
				'country'     => $country,
			);
			if ( trim( $address_to_data['postal_code'] ) == '' ) $address_to_data['postal_code'] = '-';

			$license_plate = $order_object->get_meta( '_license_plate' );
			if ( ! $license_plate ) {
				$license_plate = get_option( 'hd_wc_ie_plus_default_licence_plate' );
			}

			$owner_invoice_id = '';
			if ( $invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' ) ) {
				// Has invoice or simplified invoice? Let's associate it.
				$owner_invoice_id = $invoice_id;
			} elseif ( $invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' ) ) {
				// Has invoice-receipt ? Let's associate it.
				$owner_invoice_id = $invoice_id;
			}

			$loaded_at = ( $mode == 'manual' ? $order_object->get_meta( '_loaded_at' ) : '' ); //Always manual in Devolutions, but we may change that in the future
			if ( ! empty( $loaded_at ) ) {
				$loaded_at_time = $this->checkLoadTime( $loaded_at );
			} else {
				$now = new \DateTime();
				$add = new \DateInterval( 'PT5M' ); //Should have a filter
				$now->add( $add );

				$loaded_at_time = $now->format( 'd/m/Y H:i' ).':59';
			}

			$invoice_data = array(
				'date'             => date_i18n( 'd/m/Y' ),
				'due_date'         => $this->get_due_date( 'devolution_guide' ),
				'loaded_at'        => $loaded_at_time,
				'license_plate'    => $license_plate,
				'address_from'     => $address_from_data,
				'address_to'       => $address_to_data,
				'reference'        => $this->get_order_number( $order_object ),
				'client'           => $client_data,
				'items'            => $items_data,
				'sequence_id'      => $this->find_sequence_id( $order_object->get_id(), 'devolution' ),
				'owner_invoice_id' => $owner_invoice_id,
				'observations'     => $order_object->get_meta( '_document_observations' ),
			);

			$tax_exemption = $order_object->get_meta( '_billing_tax_exemption_reason' );
			if ( ! empty( $tax_exemption ) ) {
				$invoice_data['tax_exemption'] = $tax_exemption;
			}

			$invoice_data = $this->process_items( $invoice_data, $order_object, 'devolution_guide' );

			$invoice_data = apply_filters( 'invoicexpress_woocommerce_devolution_guide_data', $invoice_data, $order_object );

			//Prevent issuing?
			$prevent = $this->preventDocumentIssuing( $order_object, 'devolution_guide', $invoice_data, $mode );
			if ( isset( $prevent['prevent'] ) && $prevent['prevent'] ) {
				$this->preventDocumentIssuingLogger( $prevent, 'devolution_guide', $order_object, $mode );
				return;
			}

			$params = array(
				'request' => 'devolutions.json',
				'args'    => array(
					'devolution' => $invoice_data
				),
			);
			$json_request = new JsonRequest( $params );
			$return = $json_request->postRequest();
			if ( ! $return['success'] ) {
				/* Error creating invoice */
				if ( intval( $return['error_code'] ) == 502 ) {
					/* Add notice */
					$error_notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
						sprintf(
							/* translators: %s: document type */
							__( "The %s wasn't created due to InvoiceXpress service being temporarily down.<br/>Try generating it again in a few minutes.", 'woo-billing-with-invoicexpress' ),
							__( 'Return delivery note', 'woo-billing-with-invoicexpress' )
						)
					);
					if ( $mode == 'manual' ) { //Always manual in Devolutions, but we may change that in the future
						Notices::add_notice(
							$error_notice,
							'error'
						);
					}
				} else {
					$codeStr    = __( 'Code', 'woo-billing-with-invoicexpress' );
					$messageStr = __( 'Message', 'woo-billing-with-invoicexpress' );
					/* Add notice */
					$error_notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
						$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message']
					);
					if ( $mode == 'manual' ) { //Always manual in Devolutions, but we may change that in the future
						Notices::add_notice(
							$error_notice,
							'error'
						);
					}
				}
				if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && $mode == 'automatic' && $error_notice ) { //Always manual in Devolutions, but we may change that in the future
					$this->sendErrorEmail( $order_object, $error_notice );
				}
				do_action( 'invoicexpress_woocommerce_error', 'Issue Return delivery note: '.$error_notice, $order_object );
				return;
			}
			
			$order_id_invoicexpress = $return['object']->devolution->id;

			//Update client data
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_id', $client_info['client_id'] );
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_code', $client_info['client_code'] );
			//Update devolution guide data
			$order_object->update_meta_data( '_loaded_at', $loaded_at_time ); //Update loaded date/time because we've probably changed it
			$order_object->update_meta_data( 'hd_wc_ie_plus_devolution_guide_id', $order_id_invoicexpress );
			$order_object->update_meta_data( 'hd_wc_ie_plus_devolution_guide_permalink', $return['object']->devolution->permalink );
			$order_object->save();

			do_action( 'invoicexpress_woocommerce_after_document_issue', $order_object->get_id(), 'devolution_guide' );

			//Get order again because it may have changed on the action above
			$order_object = wc_get_order( $order_object->get_id() );

			if ( get_option( 'hd_wc_ie_plus_leave_as_draft' ) ) {

				/* Leave as Draft */
				$this->draft_document_note( $order_object, __( 'Return delivery note', 'woo-billing-with-invoicexpress' ) );

				/* Add notice */
				$notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					sprintf(
						/* translators: %s: document type */
						__( 'Successfully created %s as draft', 'woo-billing-with-invoicexpress' ),
						__( 'Return delivery note', 'woo-billing-with-invoicexpress' )
					)
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice( $notice );
				}
				do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );

				return;

			} else {

				/* Change document state to final */
				$return = $this->changeOrderState( $order_id_invoicexpress, 'finalized', 'devolution' );
				if ( ! $return['success'] ) {
					$codeStr    = __( 'Code', 'woo-billing-with-invoicexpress' );
					$messageStr = __( 'Message', 'woo-billing-with-invoicexpress' );
					/* Add notice */
					$error_notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
						$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message']
					);
					if ( $mode == 'manual' ) { //Always manual in Devolutions, but we may change that in the future
						Notices::add_notice(
							$error_notice,
							'error'
						);
					}
					if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && $mode == 'automatic' && $error_notice ) { //Always manual in Devolutions, but we may change that in the future
						$this->sendErrorEmail( $order_object, $error_notice );
					}
					do_action( 'invoicexpress_woocommerce_error', 'Change Return delivery note state to finalized: '.$error_notice, $order_object );
					return;
				} else {
					$notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
						sprintf(
							/* translators: %s: document type */
							__( 'Successfully finalized %s', 'woo-billing-with-invoicexpress' ),
							__( 'Return delivery note', 'woo-billing-with-invoicexpress' )
						)
					);
					do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );
				}
	
				$sequence_number = $return['object']->devolution->inverted_sequence_number;
				$order_object->update_meta_data( 'hd_wc_ie_plus_devolution_guide_sequence_number', $sequence_number );
				$order_object->save();

				/* Add notice */
				$notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					trim(
						sprintf(
							/* translators: %1$s: document name, %2$s: document number */
							__( 'Successfully created %1$s %2$s', 'woo-billing-with-invoicexpress' ),
							__( 'Return delivery note', 'woo-billing-with-invoicexpress' ),
							! empty( $sequence_number ) ? $sequence_number : '' 
						)
					)
				);
				if ( $mode == 'manual' ) { //Always manual in Devolutions, but we may change that in the future
					Notices::add_notice( $notice );
				}
				do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );

				do_action( 'invoicexpress_woocommerce_before_document_email', $order_object->get_id(), 'devolution_guide' );

				/* Get and (NOT) send the PDF */
				if ( ! $this->getAndSendPDF( $order_object, 'devolution_guide', $order_id_invoicexpress ) ) {
					return;
				}

				do_action( 'invoicexpress_woocommerce_after_document_finish', $order_object->get_id(), 'devolution_guide' );
			}

		} else {
			/* Add notice */
			$error_notice = sprintf(
				'<strong>%s:</strong> %s',
				__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
				sprintf(
					/* translators: %s: document type */
					__( "The %s wasn't created because this order already has one.", 'woo-billing-with-invoicexpress' ),
					__( 'Return delivery note', 'woo-billing-with-invoicexpress' )
				)
			);
			Notices::add_notice(
				$error_notice,
				'error'
			);
			do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object );
			return;
		}
	}

	/*
	 * Method to save the meta of a specific post id
	 */
	public function save_meta( $post_id ) {
		$order_object = wc_get_order( $post_id );
		if ( isset( $_POST['loaded_at_date'] ) && isset( $_POST['loaded_at_date_hour'] ) && isset( $_POST['loaded_at_date_minute'] )
			 &&
			 !empty( $_POST['loaded_at_date'] ) && !empty( $_POST['loaded_at_date_hour'] ) && !empty( $_POST['loaded_at_date_minute'] ) ) {
			$date = $_POST['loaded_at_date'] . ' ' . $_POST['loaded_at_date_hour'] . ':' . $_POST['loaded_at_date_minute'] . ':59';
			$order_object->update_meta_data( '_loaded_at', $date );
		} else {
			$order_object->update_meta_data( '_loaded_at', '' );
		}
		if ( isset( $_POST['license_plate'] ) ) {
			$order_object->update_meta_data( '_license_plate', sanitize_text_field( $_POST['license_plate'] ) );
		}
		$order_object->save();
	}

	/*
	 * Compares the load time against now. If it is less then now it will assign
	 * now and 5 more minutes.
	 */
	public function checkLoadTime( $time ) {
		$date = \DateTime::createFromFormat( 'd/m/Y H:i:s', $time );
		$now  = new \DateTime();
		if ( $date < $now ) {
			$add  = new \DateInterval( 'PT5M' ); //Should have a filter
			$date = $now;
			$date->add( $add );
		}
		return $date->format( 'd/m/Y H:i' ).':59';
	}
}
