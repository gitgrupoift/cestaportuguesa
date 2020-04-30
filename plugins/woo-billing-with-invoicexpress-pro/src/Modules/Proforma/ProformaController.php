<?php
namespace Webdados\InvoiceXpressWooCommerce\Modules\Proforma;

use Webdados\InvoiceXpressWooCommerce\BaseController as BaseController;
use Webdados\InvoiceXpressWooCommerce\JsonRequest as JsonRequest;
use Webdados\InvoiceXpressWooCommerce\ClientChecker as ClientChecker;
use Webdados\InvoiceXpressWooCommerce\Notices as Notices;

/* WooCommerce CRUD ready */

class ProformaController extends BaseController {

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {

		if ( get_option( 'hd_wc_ie_plus_create_proforma' ) ) {
			add_filter(
				'woocommerce_order_actions', array(
					$this,
					'order_actions',
				), 10, 1
			);
			add_action(
				'woocommerce_order_action_hd_wc_ie_plus_generate_proforma', array(
					$this,
					'doAction',
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
				__( 'Proforma', 'woo-billing-with-invoicexpress' )
			),
			__( 'PDF', 'woo-billing-with-invoicexpress' )
		) );
		$proforma_id = $order_object->get_meta( 'hd_wc_ie_plus_proforma_id' );
		if ( empty( $proforma_id ) ) {
			$symbol = '';
		} else {
			$symbol = '&#x2713;';
		}

		$actions['hd_wc_ie_plus_generate_proforma'] = trim( sprintf(
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
		
		$proforma_id = $order_object->get_meta( 'hd_wc_ie_plus_proforma_id' );

		$debug = 'Checking if Proforma document should be issued';
		do_action( 'invoicexpress_woocommerce_debug', $debug, $order_object, array(
			'hd_wc_ie_plus_proforma_id'  => $proforma_id,
		) );

		if ( empty( $proforma_id ) ) {
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
				if ( $mode == 'manual' ) { //Always manual in Proformas, but we may change that in the future
					Notices::add_notice(
						$error_notice,
						'error'
					);
				} else {
					if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && $mode == 'automatic' && $error_notice ) {
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

			$items_data = $this->getOrderItemsForDocument( $order_object, 'proforma' );

			$invoice_data = array(
				'date'             => date_i18n( 'd/m/Y' ),
				'due_date'         => $this->get_due_date( 'proforma' ),
				'reference'        => $this->get_order_number( $order_object ),
				'client'           => $client_data,
				'items'            => $items_data,
				'sequence_id'      => $this->find_sequence_id( $order_object->get_id(), 'proforma' ),
				//'owner_invoice_id' => '', //We shouldn't need to associate Proformas with any other document
				'observations'     => $order_object->get_meta( '_document_observations' ),
			);

			$tax_exemption = $order_object->get_meta( '_billing_tax_exemption_reason' );
			if ( ! empty( $tax_exemption ) ) {
				$invoice_data['tax_exemption'] = $tax_exemption;
			}

			$invoice_data = $this->process_items( $invoice_data, $order_object, 'proforma' );

			$invoice_data = apply_filters( 'invoicexpress_woocommerce_proforma_data', $invoice_data, $order_object );

			//Prevent issuing?
			$prevent = $this->preventDocumentIssuing( $order_object, 'proforma', $invoice_data, $mode );
			if ( isset( $prevent['prevent'] ) && $prevent['prevent'] ) {
				$this->preventDocumentIssuingLogger( $prevent, 'proforma', $order_object, $mode );
				return;
			}

			$params = array(
				'request' => 'proformas.json',
				'args'    => array(
					'proforma' => $invoice_data
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
							__( 'Proforma', 'woo-billing-with-invoicexpress' )
						)
					);
					if ( $mode == 'manual' ) { //Always manual in Proformas, but we may change that in the future
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
					if ( $mode == 'manual' ) {
						Notices::add_notice(
							$error_notice,
							'error'
						);
					}
				}
				if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && $mode == 'automatic' && $error_notice ) { //Always manual in Proformas, but we may change that in the future
					$this->sendErrorEmail( $order_object, $error_notice );
				}
				do_action( 'invoicexpress_woocommerce_error', 'Issue Proforma: '.$error_notice, $order_object );
				return;
			}

			$order_id_invoicexpress = $return['object']->proforma->id;

			//Update client data
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_id', $client_info['client_id'] );
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_code', $client_info['client_code'] );
			//Update proforma data
			$order_object->update_meta_data( 'hd_wc_ie_plus_proforma_id', $order_id_invoicexpress );
			$order_object->update_meta_data( 'hd_wc_ie_plus_proforma_permalink', $return['object']->proforma->permalink );
			$order_object->save();

			do_action( 'invoicexpress_woocommerce_after_document_issue', $order_object->get_id(), 'proforma' );

			//Get order again because it may have changed on the action above
			$order_object = wc_get_order( $order_object->get_id() );

			if ( get_option( 'hd_wc_ie_plus_leave_as_draft' ) ) {

				/* Leave as Draft */
				$this->draft_document_note( $order_object, __( 'Proforma', 'woo-billing-with-invoicexpress' ) );

				/* Add notice */
				$notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					sprintf(
						/* translators: %s: document type */
						__( 'Successfully created %s as draft', 'woo-billing-with-invoicexpress' ),
						__( 'Proforma', 'woo-billing-with-invoicexpress' )
					)
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice( $notice );
				}
				do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );

				return;

			} else {

				/* Change document state to final */
				$return = $this->changeOrderState( $order_id_invoicexpress, 'finalized', 'proforma' );
				if ( ! $return['success'] ) {
					$codeStr    = __( 'Code', 'woo-billing-with-invoicexpress' );
					$messageStr = __( 'Message', 'woo-billing-with-invoicexpress' );
					/* Add notice */
					$error_notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
						$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message']
					);
					if ( $mode == 'manual' ) {
						Notices::add_notice(
							$error_notice,
							'error'
						);
					}
					if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && $mode == 'automatic' && $error_notice ) { //Always manual in Proformas, but we may change that in the future
						$this->sendErrorEmail( $order_object, $error_notice );
					}
					do_action( 'invoicexpress_woocommerce_error', 'Change Proforma state to finalized: '.$error_notice, $order_object );
					return;
				} else {
					$notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
						sprintf(
							/* translators: %s: document type */
							__( 'Successfully finalized %s', 'woo-billing-with-invoicexpress' ),
							__( 'Proforma', 'woo-billing-with-invoicexpress' )
						)
					);
					do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );
				}

				$sequence_number = $return['object']->proforma->inverted_sequence_number;
				$order_object->update_meta_data( 'hd_wc_ie_plus_proforma_sequence_number', $sequence_number );
				$order_object->save();

				/* Add notice */
				$notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					trim(
						sprintf(
							/* translators: %1$s: document name, %2$s: document number */
							__( 'Successfully created %1$s %2$s', 'woo-billing-with-invoicexpress' ),
							__( 'Proforma', 'woo-billing-with-invoicexpress' ),
							! empty( $sequence_number ) ? $sequence_number : '' 
						)
					)
				);
				if ( $mode == 'manual' ) { //Always manual in Proformas, but we may change that in the future
					Notices::add_notice( $notice );
				}
				do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );

				do_action( 'invoicexpress_woocommerce_before_document_email', $order_object->get_id(), 'proforma' );

				/* Get and send the PDF */
				if ( ! $this->getAndSendPDF( $order_object, 'proforma', $order_id_invoicexpress, $mode ) ) {
					return;
				}

				do_action( 'invoicexpress_woocommerce_after_document_finish', $order_object->get_id(), 'proforma' );
			}

		} else {
			/* Add notice */
			$error_notice = sprintf(
				'<strong>%s:</strong> %s',
				__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
				sprintf(
					/* translators: %s: document type */
					__( "The %s wasn't created because this order already has one.", 'woo-billing-with-invoicexpress' ),
					__( 'Proforma', 'woo-billing-with-invoicexpress' )
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

}
