<?php
namespace Webdados\InvoiceXpressWooCommerce\Modules\VatMossInvoice;

use Webdados\InvoiceXpressWooCommerce\Plugin;
use Webdados\InvoiceXpressWooCommerce\BaseController as BaseController;
use Webdados\InvoiceXpressWooCommerce\JsonRequest as JsonRequest;
use Webdados\InvoiceXpressWooCommerce\ClientChecker as ClientChecker;
use Webdados\InvoiceXpressWooCommerce\Notices as Notices;

/* WooCommerce CRUD ready */
/* JSON API ready */

class VatMossInvoiceController extends BaseController {

	// the instance of the vat controller
	private $pro_vat_controller;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.6.0
	 * @param Plugin $plugin This plugin's instance.
	 * @param Class  $vat_controller The Pro VAT controller instance
	 */
	public function __construct( Plugin $plugin, $pro_vat_controller ) {
		parent::__construct( $plugin );

		$this->pro_vat_controller = $pro_vat_controller;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.6.0
	 */
	public function register_hooks() {

		if ( get_option( 'hd_wc_ie_plus_create_vat_moss_invoice' ) ) {
			add_filter(
				'woocommerce_order_actions', array(
					$this,
					'order_actions',
				), 10, 1
			);
			add_action(
				'woocommerce_order_action_hd_wc_ie_plus_generate_vat_moss_invoice', array(
					$this,
					'doAction',
				), 10, 2
			);
			add_filter( 'invoicexpress_woocommerce_automatic_invoice_type', array( $this, 'automatic_invoice_type' ), 10, 2 );
		}
	}

	/**
	 * Add order action.
	 *
	 * @since  2.6.0
	 * @param  array $actions Order actions.
	 * @return array
	 */
	public function order_actions( $actions ) {
		global $post;
		$order_object = wc_get_order( $post->ID );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return $actions;

		$generate_vat_moss_invoice = esc_html( sprintf(
			'%1$s (%2$s)',
			sprintf(
				/* translators: %s: document type */
				__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
				__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
			),
			__( 'PDF', 'woo-billing-with-invoicexpress' )
		) );

		$generate_vat_moss_invoice = apply_filters( 'invoicexpress_woocommerce_order_action_title', $generate_vat_moss_invoice, $order_object, 'vat_moss_invoice', 'hd_wc_ie_plus_generate_vat_moss_invoice' );

		$invoice_id            = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' );
		$simplified_invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_simplified_invoice_id' );
		$invoice_receipt_id    = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' );
		$vat_moss_invoice_id   = $order_object->get_meta( 'hd_wc_ie_plus_vat_moss_invoice_id' );
		$credit_note_id        = $order_object->get_meta( 'hd_wc_ie_plus_credit_note_id' );
		$has_scheduled         = apply_filters( 'invoicexpress_woocommerce_has_pending_scheduled_invoicing_document', false, $order_object->get_id() );

		if ( $has_scheduled ) {
			if ( apply_filters( 'invoicexpress_woocommerce_check_pending_scheduled_document', false, $order_object->get_id(), array( 'vat_moss_invoice' ) ) ) {
				//Has VAT MOSS invoice scheduled - Clock
				$symbol = '&#x1f550;';
			} else {
				//Has another invoicing document scheduled - Cross
				$symbol = '&#xd7;';
			}
		} else {
			if ( empty( $invoice_id ) && empty( $simplified_invoice_id ) && empty( $invoice_receipt_id ) && empty( $vat_moss_invoice_id ) ) {
				//Can be invoiced
				$symbol = '';
			} else {
				//There's already a invoicing document - Cross
				$symbol = '&#xd7;';
				if ( ! empty( $vat_moss_invoice_id ) ) {
					//There's already a VAT MOSS invoice - Check
					$symbol = '&#x2713;';
				}
			}
		}

		$actions['hd_wc_ie_plus_generate_vat_moss_invoice'] = trim( sprintf(
			'%s %s: %s',
			$symbol,
			esc_html__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
			$generate_vat_moss_invoice
		) );

		return $actions;
	}

	public function doAction( $order_object, $mode = 'manual' ) {

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;
		
		$invoice_id            = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' );
		$simplified_invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_simplified_invoice_id' );
		$invoice_receipt_id    = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' );
		$vat_moss_invoice_id   = $order_object->get_meta( 'hd_wc_ie_plus_vat_moss_invoice_id' );
		$credit_note_id        = $order_object->get_meta( 'hd_wc_ie_plus_credit_note_id' );
		$has_scheduled         = apply_filters( 'invoicexpress_woocommerce_has_pending_scheduled_invoicing_document', false, $order_object->get_id() );

		$debug = 'Checking if VAT MOSS invoice document should be issued';
		do_action( 'invoicexpress_woocommerce_debug', $debug, $order_object, array(
			'hd_wc_ie_plus_invoice_id'            => $invoice_id,
			'hd_wc_ie_plus_simplified_invoice_id' => $simplified_invoice_id,
			'hd_wc_ie_plus_invoice_receipt_id'    => $invoice_receipt_id,
			'hd_wc_ie_plus_vat_moss_invoice_id'    => $vat_moss_invoice_id,
			'hd_wc_ie_plus_credit_note_id'        => $credit_note_id,
			'has_scheduled'                       => $has_scheduled,
		) );

		if (
			(
				empty( $invoice_id )
				&&
				empty( $simplified_invoice_id )
				&&
				empty( $invoice_receipt_id )
				&&
				empty( $vat_moss_invoice_id )
				&&
				( ( ! $has_scheduled ) || $mode == 'scheduled' )
			)
			//2.3.1 - Should we really allow to issue an invoicing document after a credit note?
			//||
			//! empty( $credit_note_id )
		) {

			$vat = $order_object->get_meta( '_billing_VAT_code' );
			// Check for VAT number.
			if ( get_option( 'hd_wc_ie_plus_vat_field_mandatory' ) && empty( $vat ) ) {
				/* Add notice */
				$error_notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
					__( 'The VAT number is required', 'woo-billing-with-invoicexpress' )
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice(
						$error_notice,
						'error'
					);
				} else {
					if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && ( $mode == 'automatic' || $mode == 'scheduled' ) && $error_notice ) {
						$this->sendErrorEmail( $order_object, $error_notice );
					}
				}
				do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object );
				return;
			}

			//VAT MOSS eligible?
			if ( ! $this->order_is_vat_moss_eligible( $order_object ) ) {
				/* Add notice */
				$error_notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
					__( 'The order is not VAT MOSS eligible', 'woo-billing-with-invoicexpress' )
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice(
						$error_notice,
						'error'
					);
				} else {
					if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && ( $mode == 'automatic' || $mode == 'scheduled' ) && $error_notice ) {
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

			$items_data = $this->getOrderItemsForDocument( $order_object, 'vat_moss_invoice' );

			$invoice_data = array(
				'date'             => date_i18n( 'd/m/Y' ),
				'due_date'         => $this->get_due_date( 'vat_moss_invoice', $order_object ),
				'reference'        => $this->get_order_number( $order_object ),
				'client'           => $client_data,
				'items'            => $items_data,
				'sequence_id'      => $this->find_sequence_id( $order_object->get_id(), 'vat_moss_invoice' ),
				//'owner_invoice_id' => $order_object->get_meta( 'hd_wc_ie_plus_transport_guide_id' ), // VAT MOSS invoices are for digital products - no delivery guide
				'observations'     => $order_object->get_meta( '_document_observations' ),
			);

			$tax_exemption = $order_object->get_meta( '_billing_tax_exemption_reason' );
			if ( ! empty( $tax_exemption ) ) {
				$invoice_data['tax_exemption'] = $tax_exemption;
			}

			$invoice_data = $this->process_items( $invoice_data, $order_object, 'vat_moss_invoice' );

			$invoice_data = apply_filters( 'invoicexpress_woocommerce_vat_moss_invoice_data', $invoice_data, $order_object );

			//Prevent issuing?
			$prevent = $this->preventDocumentIssuing( $order_object, 'vat_moss_invoice', $invoice_data, $mode );
			if ( isset( $prevent['prevent'] ) && $prevent['prevent'] ) {
				$this->preventDocumentIssuingLogger( $prevent, 'vat_moss_invoice', $order_object, $mode );
				return;
			}

			$params = array(
				'request' => 'vat_moss_invoices.json',
				'args'    => array(
					'vat_moss_invoice' => $invoice_data
					//'invoice' => $invoice_data
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
							__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
						)
					);
					if ( $mode == 'manual' ) {
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
				if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && ( $mode == 'automatic' || $mode == 'scheduled' ) && $error_notice ) {
					$this->sendErrorEmail( $order_object, $error_notice );
				}
				do_action( 'invoicexpress_woocommerce_error', 'Issue VAT MOSS invoice: '.$error_notice, $order_object );
				return;
			}
			
			$order_id_invoicexpress = $return['object']->vat_moss_invoice->id;

			//Update client data
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_id', $client_info['client_id'] );
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_code', $client_info['client_code'] );
			//Update invoice data
			$order_object->update_meta_data( 'hd_wc_ie_plus_invoice_id', $order_id_invoicexpress );
			$order_object->update_meta_data( 'hd_wc_ie_plus_vat_moss_invoice_permalink', $return['object']->vat_moss_invoice->permalink );
			$order_object->update_meta_data( 'hd_wc_ie_plus_invoice_type', 'vat_moss_invoice' );
			$order_object->save();

			do_action( 'invoicexpress_woocommerce_after_document_issue', $order_object->get_id(), 'vat_moss_invoice' );

			//Get order again because it may have changed on the action above
			$order_object = wc_get_order( $order_object->get_id() );

			if ( get_option( 'hd_wc_ie_plus_leave_as_draft' ) ) {

				/* Leave as Draft */
				$this->draft_document_note( $order_object, __( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' ) );

				/* Add notice */
				$notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					sprintf(
						/* translators: %s: document type */
						__( 'Successfully created %s as draft', 'woo-billing-with-invoicexpress' ),
						__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
					)
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice( $notice );
				}
				do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );

				return;

			} else {

				/* Change document state to final */
				$return = $this->changeOrderState( $order_id_invoicexpress, 'finalized', 'vat_moss_invoice' );
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
					if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && ( $mode == 'automatic' || $mode == 'scheduled' ) && $error_notice ) {
						$this->sendErrorEmail( $order_object, $error_notice );
					}
					do_action( 'invoicexpress_woocommerce_error', 'Change VAT MOSS invoice state to finalized: '.$error_notice, $order_object );
					return;
				} else {
					$notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
						sprintf(
							/* translators: %s: document type */
							__( 'Successfully finalized %s', 'woo-billing-with-invoicexpress' ),
							__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
						)
					);
					do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );
				}
	
				$sequence_number = $return['object']->vat_moss_invoice->inverted_sequence_number;
				$order_object->update_meta_data( 'hd_wc_ie_plus_vat_moss_invoice_sequence_number', $sequence_number );
				$order_object->save();

				/* Add notice */
				$notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					trim(
						sprintf(
							/* translators: %1$s: document name, %2$s: document number */
							__( 'Successfully created %1$s %2$s', 'woo-billing-with-invoicexpress' ),
							__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' ),
							! empty( $sequence_number ) ? $sequence_number : '' 
						)
					)
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice( $notice );
				}
				do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );

				do_action( 'invoicexpress_woocommerce_before_document_email', $order_object->get_id(), 'vat_moss_invoice' );

				/* Get and send the PDF */
				if ( ! $this->getAndSendPDF( $order_object, 'vat_moss_invoice', $order_id_invoicexpress, $mode ) ) {
					return;
				}

				do_action( 'invoicexpress_woocommerce_after_document_finish', $order_object->get_id(), 'vat_moss_invoice' );
			}

		} else {
			/* Add notice */
			$error_notice = sprintf(
				'<strong>%s:</strong> %s',
				__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
				sprintf(
					/* translators: %s: document type */
					__( "The %s wasn't created because this order already has an invoice type document or one is scheduled to be issued.", 'woo-billing-with-invoicexpress' ),
					__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
				)
			);
			if ( $mode == 'manual' ) {
				Notices::add_notice(
					$error_notice,
					'error'
				);
			} else {
				if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) ) {
					$this->sendErrorEmail( $order_object, $error_notice );
				}
			}
			do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object );
			return;
		}
	}

	/**
	 * Do VAT MOSS or original document type?
	 *
	 * @since  2.6.0
	 * @param  string $document_type The document type
	 * @param  object $order_object The order
	 * @return string
	 */
	public function automatic_invoice_type( $document_type, $order_object ) {
		if ( $this->order_is_vat_moss_eligible( $order_object ) ) {
			return 'vat_moss_invoice';
		}
		return $document_type;
	}

	/**
	 * Check if order is elegible for VAT MOSS
	 *
	 * @since  2.6.0
	 * @param  object $order_object The order
	 * @return bool
	 */
	public function order_is_vat_moss_eligible( $order_object ) {
		do_action( 'invoicexpress_woocommerce_debug', 'Checking if order '.$order_object->get_id().' is eligible for VAT MOSS', $order_object );
		//VAT Exempt?
		if (
			( 'yes' === $order_object->get_meta( 'is_vat_exempt' ) )
			||
			( 0 == floatval( $order_object->get_total_tax() ) )
		) {
			do_action( 'invoicexpress_woocommerce_error', 'Order has no VAT', $order_object );
			return false;
		}
		//EU country and not the same country as shop?
		$eu_countries  = $this->pro_vat_controller->get_eu_vat_countries();
		$order_country = $order_object->get_billing_country();
		if (
			$order_country == WC()->countries->get_base_country()
			||
			! in_array( $order_country, $eu_countries )
		) {
			do_action( 'invoicexpress_woocommerce_error', 'Order country is nor part of the EU VAT zone or is the same as the store', $order_object );
			return false;
		}
		//All products virtual?
		foreach( $order_object->get_items() as $item_id => $product_item ) {
			if ( $product = $product_item->get_product() ) {
				if ( ! $product->is_virtual() ) {
					do_action( 'invoicexpress_woocommerce_error', $product->get_name().' is not virtual', $order_object );
					return false;
				}
			} else {
				//The product doesn't exist anymore... we may have a problem here...
				//We should probably track this and use an alternative way of checking
				do_action( 'invoicexpress_woocommerce_error', $product_item->get_name().' no longer exists as a product so we can not check if it is virtual', $order_object );
				return false;
			}
		}
		if ( ! apply_filters( 'invoicexpress_woocommerce_order_is_vat_moss_eligible', true, $order_object ) ) {
			do_action( 'invoicexpress_woocommerce_error', 'Developer filter invoicexpress_woocommerce_order_is_vat_moss_eligible returned false', $order_object );
			return false;
		}
		//All good then
		return true;
	}

}
