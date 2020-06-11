<?php
namespace Webdados\InvoiceXpressWooCommerce\Modules\CreditNote;

use Webdados\InvoiceXpressWooCommerce\Plugin;
use Webdados\InvoiceXpressWooCommerce\BaseController as BaseController;
use Webdados\InvoiceXpressWooCommerce\JsonRequest as JsonRequest;
use Webdados\InvoiceXpressWooCommerce\ClientChecker as ClientChecker;
use Webdados\InvoiceXpressWooCommerce\Notices as Notices;

/* WooCommerce CRUD ready */
/* JSON API ready */

class CreditNoteController extends BaseController {

	// the instance of the payment controller
	private $payment_controller;
	private $invoice_receipt_controller;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.0.4 Add plugin instance parameter.
	 * @since 2.0.0
	 * @param Plugin $plugin This plugin's instance.
	 */
	public function __construct( Plugin $plugin, $payment_controller, $invoice_receipt_controller = null ) {
		parent::__construct( $plugin );

		$this->payment_controller         = $payment_controller;
		$this->invoice_receipt_controller = $invoice_receipt_controller;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {

		if ( get_option( 'hd_wc_ie_plus_create_credit_note' ) ) {
			add_action( 'woocommerce_order_refunded', array( $this, 'doAction' ), 50, 2 );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script_credit' ) );

			// AJAX request to load custom forms.
			add_action( 'wp_ajax_hd_invoicexpress_reload', array( $this, 'reload_page_ajax' ) );
		}
	}

	public function enqueue_script_credit() {
		global $post_type, $post;
		if ( $post_type && $post && $post_type == 'shop_order' ) {
			wp_register_script( 'hd_wc_ie_credit_order', plugins_url( 'assets/js/credit.js', INVOICEXPRESS_WOOCOMMERCE_PLUGIN_FILE ), array( 'jquery' ), INVOICEXPRESS_WOOCOMMERCE_VERSION, true );
			wp_enqueue_script( 'hd_wc_ie_credit_order' );
		}
	}

	public function doAction( $order_id, $refund_id ) {

		$order_object       = wc_get_order( $order_id );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;
		
		$receipt_id         = $order_object->get_meta( 'hd_wc_ie_plus_receipt_id' );
		$invoice_receipt_id = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' );
		$has_scheduled      = apply_filters( 'invoicexpress_woocommerce_has_pending_scheduled_invoicing_document', false, $order_object->get_id() );

		$debug = 'Checking if Credit note document should be issued';
		do_action( 'invoicexpress_woocommerce_debug', $debug, $order_object, array(
			'hd_wc_ie_plus_receipt_id'         => $receipt_id,
			'hd_wc_ie_plus_invoice_receipt_id' => $invoice_receipt_id,
			'has_scheduled'                    => $has_scheduled,
		) );

		if (
			( ! empty( $receipt_id ) || ! empty( $invoice_receipt_id ) )
			&&
			! $has_scheduled
		) {

			$client_name = $this->get_document_client_name( $order_object );

			$vat = $order_object->get_meta( '_billing_VAT_code' );

			$checker = new ClientChecker();
			$client_info = $checker->maybeCreateClient( $client_name, $order_object );

			$client_data = array(
				'name' => $client_name,
				'code' => $client_info['client_code'],
			);

			//We can not use the BaseController::getOrderItemsForDocument for credit notes
			$items  = array();
			$refund = new \WC_Order_Refund( $refund_id );

			$invoice_type = $order_object->get_meta( 'hd_wc_ie_plus_invoice_type' );

			if ( $order_object->get_total() + $refund->get_total() > 0 ) {
				//Parial refund
				$refund_is_total = false;
				// support@invoicexpress.com 2020-04-20
				/*if ( 'invoice_receipt' === $invoice_type ) {
					//Partial refund of Invoice-receipt - We do NOT support this and should return now
					$error_notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
						__( 'Partial Credit note of an Invoice-receipt not supported', 'woo-billing-with-invoicexpress' )
					);
					Notices::add_notice(
						$error_notice,
						'error'
					);
					do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object );
					return;
				}*/
				if (
					( ! $refund->get_items() )
					&&
					( ! $refund->get_shipping_method() )
					&&
					( ! $refund->get_fees() )
				) {
					//Partial refund by value - We do NOT support this and should return now - We do allow refund of shipping costs and fees alone
					$error_notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
						__( 'Partial credit note by value is not supported', 'woo-billing-with-invoicexpress' )
					);
					Notices::add_notice(
						$error_notice,
						'error'
					);
					do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object );
					return;
				}
				//Partial refund by items (or shipping cost or fees)
				$refund_object = 'refund';
			} else {
				//Total refund
				$refund_is_total = true;
				$refund_object = 'order_object';
			}

			foreach ( $$refund_object->get_items() as $item ) { // Total: $order_object | Parcial: $refund

				$refunded_item_id = $item['refunded_item_id'];

				// We do this to calculate the items left in the order.
				if ( $item->get_variation_id() ) {
					$pid = $item->get_variation_id();
				} else {
					$pid = $item->get_product_id();
				}

				$quantity = $item->get_quantity();

				if ( $quantity < 0 ) {
					$quantity = abs( $quantity );
				}

				if ( $quantity > 0 ) {

					$unit_price     = (double) $item->get_total() / (double) $item->get_quantity();
					$taxes_per_line = $item->get_taxes();

					$tax_ids = array();

					foreach ( $taxes_per_line['subtotal'] as $key => $value ) {
						if ( $value != '' ) {
							if ( $value != 0 ) {
								$tax_ids[0] = $key;
							}
							$tax_ids[] = $key;
						}
					}

					if ( isset( $tax_ids[0] ) ) {
						$vat = \WC_Tax::get_rate_label( $tax_ids[0] );
					}

					if ( $order_object->get_total() > 0 && $order_object->get_total_tax() == 0 && $order_object->get_meta( '_billing_tax_exemption_reason' ) != '' ) {
						  $vat = get_option( 'hd_wc_ie_plus_exemption_name' );
					}

					/* Issue #108 */
					$name = '#' . $pid;
					if ( $product = wc_get_product( $pid ) ) {
						$product_code = get_option( 'hd_wc_ie_plus_product_code' );
						if ( $product->get_sku() && $product_code != 'id' ) {
							$name = $product->get_sku();
						}
					}
					/* End of Issue #108 */

					$item_data = array(
						'name'        => $name,
						'description' => $this->order_item_title( $item, $product, $order_object, 'credit_note' ),
						'unit_price'  => abs( $unit_price ),
						'quantity'    => $quantity,
						'unit'        => 'unit',
					);

					if ( ! empty( $vat ) ) {
						$item_data['tax'] = array(
							'name' => $vat,
						);
					}

					$items[] = $item_data;
				}
			}

			$shipping_method = $$refund_object->get_shipping_method(); // Total: $order_object | Parcial: $refund ?

			if ( ! empty( $shipping_method ) ) {

				foreach ( $$refund_object->get_shipping_methods() as $key => $item ) { // Total: $order_object | Parcial: $refund ?

					$taxes_per_line = $item['taxes'];

					if ( $taxes_per_line && ! is_array( $taxes_per_line ) ) {
						$taxes_per_line = unserialize( $taxes_per_line );
					}

					$tax_ids = array();
					foreach ( $taxes_per_line as $key => $value ) {
						if ( $key === 'total' ) {
							foreach ( $value as $k => $v ) {
								if ( floatval( $v ) > 0 ) {
									$tax_ids[] = $k;
								}
							}
						} elseif ( $key !== '' ) {
							if ( floatval( $value ) > 0 ) {
								$tax_ids[] = $key;
							}
						}
					}

					if ( isset( $tax_ids[0] ) ) {
						$vat = \WC_Tax::get_rate_label( $tax_ids[0] );
					}

					if ( $order_object->get_total() > 0 && $order_object->get_total_tax() == 0 && $order_object->get_meta( '_billing_tax_exemption_reason' ) != '' ) {
						$vat = get_option( 'hd_wc_ie_plus_exemption_name' );
					}
					if ( apply_filters( 'invoicexpress_woocommerce_shipping_and_fee_ref_unique', true ) ) {
						$ref = 'SHIP';
					} else {
						//Old way
						$ref = '#S-' . $key;
					}

					$item_data = array(
						'name'        => $ref,
						'description' => $item['name'],
						'unit_price'  => abs( $item['cost'] ),
						'quantity'    => 1,
					);

					if ( ! empty( $vat ) ) {
						$item_data['tax'] = array(
							'name' => $vat,
						);
					}

					$items[] = $item_data;
				}
			}

			foreach ( $$refund_object->get_fees() as $key => $item ) { // Total: $order_object | Parcial: $refund ?
				if ( apply_filters( 'invoicexpress_woocommerce_shipping_and_fee_ref_unique', true ) ) {
					$ref = 'FEE';
				} else {
					//Old way
					$ref = '#F-' . $key;
				}
				$item_data = array(
					'name'        => $ref,
					'description' => $item['name'],
					'unit_price'  => abs( $item['line_total'] ),
					'quantity'    => 1,
				);

				if ( ! empty( $vat ) ) {
					$item_data['tax'] = array(
						'name' => $vat,
					);
				}

				$items[] = $item_data;
			}

			$owner_invoice_id = '';
			switch ( $invoice_type ) {
				case 'invoice_receipt':
					$owner_invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' );
					break;
				case 'invoice':
					$owner_invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' );
					break;
				case 'simplified_invoice':
					$owner_invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' ); //We also have hd_wc_ie_plus_simplified_invoice_id set by storeAndNoteDocument...
					break;
				case 'vat_moss_invoice':
					$owner_invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' ); //We also have hd_wc_ie_plus_simplified_invoice_id set by storeAndNoteDocument...
					break;
			}

			//First cancel the receipt - If partial and Invoice-receipt there's no receipt_id, so we're ok
			if ( ! empty( $receipt_id ) ) {
				$motive = $refund->get_reason();
				if ( empty( $motive ) ) {
					$motive = $this->plugin->get_translated_option( 'hd_wc_ie_plus_refund_automatic_message', null, $order_object );
				}
				$return_cancel = $this->changeOrderState( $receipt_id, 'canceled', 'receipt', $motive );
				if ( ! $return_cancel['success'] ) {
					$codeStr    = __( 'Code', 'woo-billing-with-invoicexpress' );
					$messageStr = __( 'Message', 'woo-billing-with-invoicexpress' );
					/* Add notice */
					$error_notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
						$codeStr . ': ' . $return_cancel['error_code'] . " - " . $messageStr . ': ' . $return_cancel['error_message']
					);
					Notices::add_notice(
						$error_notice,
						'error'
					);
					do_action( 'invoicexpress_woocommerce_error', 'Cancel receipt: '.$error_notice, $order_object );
				}
			}

			//Now let's create the credit note
			if ( empty( $motive ) ) {
				$observations = trim( $order_object->get_meta( '_document_observations' ) );
			} else {
				$observations = implode( PHP_EOL, array(
					trim( $order_object->get_meta( '_document_observations' ) ),
					__( 'Refund motive', 'woo-billing-with-invoicexpress' ).': '.trim( $motive ),
				) );
			}
			$invoice_data = array(
				'date'             => date_i18n( 'd/m/Y' ),
				'due_date'         => $this->get_due_date( 'credit_note', $$refund_object ),
				'reference'        => $this->get_order_number( $order_object ),
				'client'           => $client_data,
				'items'            => $items,
				'sequence_id'      => $this->find_sequence_id( $order_object->get_id(), 'credit_note' ),
				'owner_invoice_id' => $owner_invoice_id,
				'observations'     => trim( $observations ),
			);

			$tax_exemption = $order_object->get_meta( '_billing_tax_exemption_reason' );
			if ( ! empty( $tax_exemption ) ) {
				$invoice_data['tax_exemption'] = $tax_exemption;
			}

			$invoice_data = $this->process_items( $invoice_data, $order_object, 'credit_note' );

			$invoice_data = apply_filters( 'invoicexpress_woocommerce_credit_note_data', $invoice_data, $order_object );

			//Prevent issuing?
			$prevent = $this->preventDocumentIssuing( $order_object, 'credit_note', $invoice_data );
			if ( isset( $prevent['prevent'] ) && $prevent['prevent'] ) {
				$this->preventDocumentIssuingLogger( $prevent, 'credit_note', $order_object );
				return;
			}

			//Now issue the credit note
			$params = array(
				'request' => 'credit_notes.json',
				'args'    => array(
					'credit_note' => $invoice_data
				),
			);
			$json_request = new JsonRequest( $params );
			$return = $json_request->postRequest();
			if ( ! $return['success'] ) {
				/* Error creating credit note */
				if ( intval( $return['error_code'] ) == 502 ) {
					/* Add notice */
					$error_notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
						sprintf(
							/* translators: %s: document type */
							__( "The %s wasn't created due to InvoiceXpress service being temporarily down.<br/>Try generating it again in a few minutes.", 'woo-billing-with-invoicexpress' ),
							__( 'Credit note', 'woo-billing-with-invoicexpress' )
						)
					);
					Notices::add_notice(
						$error_notice,
						'error'
					);
				} else {
					$codeStr    = __( 'Code', 'woo-billing-with-invoicexpress' );
					$messageStr = __( 'Message', 'woo-billing-with-invoicexpress' );
					/* Add notice */
					$error_notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
						$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message']
					);
					Notices::add_notice(
						$error_notice,
						'error'
					);
				}
				do_action( 'invoicexpress_woocommerce_error', 'Issue Credit note: '.$error_notice, $order_object );
				return;
			}

			$order_id_invoicexpress = $return['object']->credit_note->id;

			//Update client data
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_id', $client_info['client_id'] );
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_code', $client_info['client_code'] );
			//Update credit note data
			$order_object->update_meta_data( 'hd_wc_ie_plus_credit_note_id', $order_id_invoicexpress );
			$order_object->update_meta_data( 'hd_wc_ie_plus_credit_note_permalink', $return['object']->credit_note->permalink );
			$order_object->save();

			if ( get_option( 'hd_wc_ie_plus_leave_as_draft' ) ) {

				/* Leave as Draft */
				$this->draft_document_note( $order_object, __( 'Credit note', 'woo-billing-with-invoicexpress' ) );

				/* Add notice */
				$notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					sprintf(
						/* translators: %s: document type */
						__( 'Successfully created %s as draft', 'woo-billing-with-invoicexpress' ),
						__( 'Credit note', 'woo-billing-with-invoicexpress' )
					)
				);
				Notices::add_notice( $notice );
				do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );

				return;

			} else {

				/* Change document state to final */
				$return = $this->changeOrderState( $order_id_invoicexpress, 'finalized', 'credit_note' );
				if ( ! $return['success'] ) {
					$codeStr    = __( 'Code', 'woo-billing-with-invoicexpress' );
					$messageStr = __( 'Message', 'woo-billing-with-invoicexpress' );
					/* Add notice */
					$error_notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
						$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message']
					);
					Notices::add_notice(
						$error_notice,
						'error'
					);
					do_action( 'invoicexpress_woocommerce_error', 'Change Credit note state to finalized: '.$error_notice, $order_object );
					return;
				} else {
					$notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
						sprintf(
							/* translators: %s: document type */
							__( 'Successfully finalized %s', 'woo-billing-with-invoicexpress' ),
							__( 'Credit note', 'woo-billing-with-invoicexpress' )
						)
					);
					do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );
				}
	
				$sequence_number = $return['object']->credit_note->inverted_sequence_number;
				$order_object->update_meta_data( 'hd_wc_ie_plus_credit_note_sequence_number', $sequence_number );
				$order_object->save();

				/* Tell the instance of payment controller to handle this receipt */
				// Why this if? Because if the refund is total there is no need to make a new receipt.
				if ( (double) $order_object->get_total() != (double) $order_object->get_total_refunded() ) {
					if ( ! empty( $receipt_id ) ) {
						add_filter( 'invoicexpress_woocommerce_receipt_exists_error', '__return_false' );
						$this->payment_controller->doActionInvoice( $order_object );
					} elseif ( ! empty( $invoice_receipt_id ) && ! empty( $this->invoice_receipt_controller ) ) {
						if ( $refund_is_total ) { //This will never happen, I think...
							$this->invoice_receipt_controller->doAction( $order_object );
						}
					}
					// This is used to update receipt data on a case of partial refunds
					$new_meta = $order_object->get_meta( 'hd_wc_ie_plus_receipt_id_2' );
					if ( $new_meta != '' ) {
						$order_object->update_meta_data( 'hd_wc_ie_plus_receipt_id', $order_object->get_meta( 'hd_wc_ie_plus_receipt_id_2' ) );
						$order_object->update_meta_data( 'hd_wc_ie_plus_receipt_pdf', $order_object->get_meta( 'hd_wc_ie_plus_receipt_pdf_2' ) );
						$order_object->delete_meta_data( 'hd_wc_ie_plus_receipt_id_2' );
						$order_object->delete_meta_data( 'hd_wc_ie_plus_receipt_pdf_2' );
						$order_object->save();
					}
				}

				/* Add notice */
				$notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					trim(
						sprintf(
							/* translators: %1$s: document name, %2$s: document number */
							__( 'Successfully created %1$s %2$s', 'woo-billing-with-invoicexpress' ),
							__( 'Credit note', 'woo-billing-with-invoicexpress' ),
							! empty( $sequence_number ) ? $sequence_number : '' 
						)
					)
				);
				Notices::add_notice( $notice );
				do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );

				do_action( 'invoicexpress_woocommerce_before_document_email', $order_object->get_id(), 'credit_note' );

				/* Get and send the PDF */
				if ( ! $this->getAndSendPDF( $order_object, 'credit_note', $order_id_invoicexpress ) ) {
					return;
				}

				do_action( 'invoicexpress_woocommerce_after_document_finish', $order_object->get_id(), 'credit_note' );

				return;

			}

		} else {
			/* Add notice */
			$error_notice = sprintf(
				'<strong>%s:</strong> %s',
				__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
				__( 'Credit note was not created because order lacks a Receipt or an invoicing document is scheduled to be issued.', 'woo-billing-with-invoicexpress' )
			);
			Notices::add_notice(
				$error_notice,
				'error'
			);
			do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object );
			return;
		}
	}

	public function reload_page_ajax() {

		// The $_REQUEST contains all the data sent via ajax
		if ( isset( $_REQUEST ) ) {

			$order_id           = intval( $_REQUEST['order_id'] );
			$order_object       = wc_get_order( $order_id );
			$receipt_id         = $order_object->get_meta( 'hd_wc_ie_plus_receipt_id' );
			$invoice_receipt_id = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' );

			if ( ! empty( $receipt_id ) || ! empty( $invoice_receipt_id ) ) {
				sleep( 30 );
				echo true;
			} else {
				echo false;
			}
		}

		// Always die in functions echoing ajax content
		die();
	}
}
