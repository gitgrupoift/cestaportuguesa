<?php
namespace Webdados\InvoiceXpressWooCommerce\Modules\Payment;

use Webdados\InvoiceXpressWooCommerce\JsonRequest as JsonRequest;
use Webdados\InvoiceXpressWooCommerce\BaseController;
use Webdados\InvoiceXpressWooCommerce\Notices as Notices;

/* WooCommerce CRUD ready */
/* JSON API ready */

class PaymentController extends BaseController {

	private $invoice_id;

	private $invoice_type;

	private $document_id;

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {

		if ( get_option( 'hd_wc_ie_plus_invoice_payment' ) ) {
			add_filter(
				'woocommerce_order_actions', array(
					$this,
					'order_actions',
				), 10, 1
			);
		}

		if ( get_option( 'hd_wc_ie_plus_invoice_payment' ) || get_option( 'hd_wc_ie_plus_automatic_receipt' ) ) {
			// This action can be called even if manual receipts are not enabled.
			add_action(
				'woocommerce_order_action_hd_wc_ie_plus_pay_invoice', array(
					$this,
					'doActionInvoice',
				), 10, 2
			);
		}

		if ( get_option( 'hd_wc_ie_plus_automatic_receipt' ) ) {
			add_action(
				'invoicexpress_woocommerce_do_automatic_receipt', array(
					$this,
					'automaticReceiptCreation',
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
				__( 'Receipt', 'woo-billing-with-invoicexpress' )
			),
			__( 'PDF', 'woo-billing-with-invoicexpress' )
		) );

		$invoice_id            = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' );
		$simplified_invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_simplified_invoice_id' );
		$invoice_receipt_id    = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' );
		$vat_moss_invoice_id   = $order_object->get_meta( 'hd_wc_ie_plus_vat_moss_invoice_id' );
		$credit_note_id        = $order_object->get_meta( 'hd_wc_ie_plus_credit_note_id' );
		$receipt_id            = $order_object->get_meta( 'hd_wc_ie_plus_receipt_id' );

		if ( ! empty( $invoice_receipt_id ) || ! empty( $receipt_id ) ) {
			$symbol = '&#x2713;';
		} else {
			if ( empty( $invoice_id ) && empty( $simplified_invoice_id ) && empty( $vat_moss_invoice_id ) ) {
				$symbol = '&#xd7;';
			} else {
				$symbol = '';
			}
		}

		$actions['hd_wc_ie_plus_pay_invoice'] = trim( sprintf(
			'%s %s: %s / %s',
			$symbol,
			esc_html__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
			$generate_invoice,
			esc_html__( 'Set invoice as paid', 'woo-billing-with-invoicexpress' )
		) );

		return $actions;
	}

	public function doActionInvoice( $order_object, $mode = 'manual' ) {

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;
		
		//try {

		$this->invoice_id   = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' );
		$this->invoice_type = $order_object->get_meta( 'hd_wc_ie_plus_invoice_type' );
		$receipt_id         = $order_object->get_meta( 'hd_wc_ie_plus_receipt_id' );

		$debug = 'Checking if Receipt document should be issued';
		$debug .= ' | hd_wc_ie_plus_invoice_id: '.$order_object->get_meta( 'hd_wc_ie_plus_invoice_id' );
		$debug .= ' | hd_wc_ie_plus_invoice_type: '.$order_object->get_meta( 'hd_wc_ie_plus_invoice_type' );
		$debug .= ' | hd_wc_ie_plus_receipt_id: '.$order_object->get_meta( 'hd_wc_ie_plus_receipt_id' );
		do_action( 'invoicexpress_woocommerce_debug', $debug, $order_object );

		if ( ! empty( $receipt_id ) ) {
			if ( apply_filters( 'invoicexpress_woocommerce_receipt_exists_error', true ) ) {
				/* Add notice */
				$error_notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
					__( "There's already a Receipt.", 'woo-billing-with-invoicexpress' )
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice(
						$error_notice,
						'error'
					);
				}
				do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object );
				// We do not return because for some reason this test wasn't being done, and their might be a reason to issue the receipt after all
				// Partial refunds are the reason
			}
		}

		if ( empty( $this->invoice_id ) || empty( $this->invoice_type ) ) {
			/* Add notice */
			$error_notice = sprintf(
				'<strong>%s:</strong> %s',
				__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
				__( 'There\'s no invoice to set as paid', 'woo-billing-with-invoicexpress' )
			);
			if ( $mode == 'manual' ) {
				Notices::add_notice(
					$error_notice,
					'error'
				);
			}
			if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && $mode == 'automatic' && $error_notice ) {
				$this->sendErrorEmail( $order_object, $error_notice );
			}
			do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object );
			return;
		}

		/* Change document (invoice) state to settled */
		$return = $this->changeOrderState( $this->invoice_id, 'settled', $this->invoice_type );
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
			if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && $mode == 'automatic' && $error_notice ) {
				$this->sendErrorEmail( $order_object, $error_notice );
			}
			do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object );
			return;
		}

		/* Get receipt info */
		$params = array(
			'request' => 'document/' . $this->invoice_id . '/related_documents.json',
		);
		$json_request = new JsonRequest( $params );
		$return = $json_request->getRequest();
		if ( ! $return['success'] ) {
			// We should be dealing with errors
		}

		$receipt_count = 0;
		$id = '';
		$sequence_number = '';
		$type = '';
		$permalink = '';
		if ( count( $return['object']->documents ) > 0 ) {
			foreach ( $return['object']->documents as $document ) {
				if ( $document->status == 'final' ) {
					$id = $document->id;
					$sequence_number = $document->inverted_sequence_number;
					$permalink = $document->permalink;
					if ( $document->type == 'Receipt' || $document->type == 'VatMossReceipt'  ) {
						$receipt_count++;
					}
					$type = $document->type;
				}
			}
		}

		if ( ! empty( $sequence_number ) ) {
			$order_object->update_meta_data( 'hd_wc_ie_plus_receipt_sequence_number', $sequence_number );
			$order_object->save();
		}

		if ( $type == 'Receipt' || $type == 'VatMossReceipt' ) {
			$this->document_id = $id;

			//Update receipt data
			$order_object->update_meta_data( 'hd_wc_ie_plus_receipt_id', $id );
			$order_object->update_meta_data( 'hd_wc_ie_plus_receipt_permalink', $permalink );
			$order_object->save();

			do_action( 'invoicexpress_woocommerce_after_document_issue', $order_object->get_id(), 'receipt' );

			/* Add notice */
			$notice = sprintf(
				'<strong>%s:</strong> %s',
				__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
				trim(
					sprintf(
						/* translators: %1$s: document name, %2$s: document number */
						__( 'Successfully created %1$s %2$s', 'woo-billing-with-invoicexpress' ),
						__( 'Receipt', 'woo-billing-with-invoicexpress' ),
						! empty( $sequence_number ) ? $sequence_number : '' 
					)
				)
			);
			if ( $mode == 'manual' ) {
				Notices::add_notice( $notice );
			}
			do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );

			do_action( 'invoicexpress_woocommerce_before_document_email', $order_object->get_id(), 'receipt' );

			/* Get and send the PDF */
			if ( ! $this->getAndSendPDF( $order_object, 'receipt', $this->document_id, $mode, $receipt_count ) ) {
				return;
			}
			
			do_action( 'invoicexpress_woocommerce_after_document_finish', $order_object->get_id(), 'receipt' );

		}

	}

	/* Automatic Receipt */
	public function automaticReceiptCreation( $post_id ) {

		$order_object = wc_get_order( $post_id );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;

		$meta                = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' );
		$receipt_meta        = $order_object->get_meta( 'hd_wc_ie_plus_receipt_id' );
		$invoicereceipt_meta = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' );

		if ( ( ! empty( $meta ) ) && ( empty( $receipt_meta ) ) && ( empty( $invoicereceipt_meta ) ) ) {
			do_action( 'woocommerce_order_action_hd_wc_ie_plus_pay_invoice', $order_object, 'automatic' );
		}
	}

}
