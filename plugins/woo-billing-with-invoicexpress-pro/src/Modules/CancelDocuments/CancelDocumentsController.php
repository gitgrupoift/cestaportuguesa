<?php
namespace Webdados\InvoiceXpressWooCommerce\Modules\CancelDocuments;

use Webdados\InvoiceXpressWooCommerce\BaseController;
use Webdados\InvoiceXpressWooCommerce\Notices as Notices;

/* WooCommerce CRUD ready */
/* JSON API ready */

class CancelDocumentsController extends BaseController {

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {

		if ( get_option( 'hd_wc_ie_plus_cancel_documents' ) ) {
			/**
			 * TODO: ver se existe recibo, se sim, não mostrar.
			 */
			add_filter(
				'woocommerce_order_actions', array(
					$this,
					'order_actions',
				), 10, 1
			);
			add_action(
				'woocommerce_order_action_hd_wc_ie_plus_cancel_document', array(
					$this,
					'doAction',
				), 10, 1
			);
			add_action(
				'woocommerce_order_status_cancelled', array(
					$this,
					'prepareAction',
				)
			);
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script_cancel_documents' ) );

		// AJAX request to check if order status can be changed to cancelled
		add_action( 'wp_ajax_hd_invoicexpress_cancelable', array( $this, 'is_order_cancellable_ajax' ) );
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

		$canceled            = $order_object->get_meta( 'hd_wc_ie_plus_been_canceled' );
		$receipt_id          = $order_object->get_meta( 'hd_wc_ie_plus_receipt_id' );
		$invoice_receipt_id  = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' );
		$invoice_id          = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' );
		$transport_guide_id  = $order_object->get_meta( 'hd_wc_ie_plus_transport_guide_id' );
		$devolution_guide_id = $order_object->get_meta( 'hd_wc_ie_plus_devolution_guide_id' );

		if ( ! empty( $canceled ) ) {
			$symbol = '&#x2713;';
		} else {
			if ( ! empty( $receipt_id ) || ! empty( $invoice_receipt_id ) ) {
				$symbol = '&#xd7;';
			} else {
				if ( empty( $invoice_id ) && empty( $transport_guide_id )  && empty( $devolution_guide_id ) ) {
					$symbol = '&#xd7;';
				} else {
					$symbol = '';
				}
			}
		}

		$actions['hd_wc_ie_plus_cancel_document'] = trim( sprintf(
			'%s %s: %s',
			$symbol,
			esc_html__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
			esc_html__( 'Cancel last document', 'woo-billing-with-invoicexpress' )
		) );

		if ( get_option( 'hd_wc_ie_plus_update_order_status' ) ) {
			$actions['hd_wc_ie_plus_cancel_document'] = trim( sprintf(
				'%s %s: %s (%s)',
				$symbol,
				esc_html__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
				esc_html__( 'Cancel last document', 'woo-billing-with-invoicexpress' ),
				esc_html__( 'and order', 'woo-billing-with-invoicexpress' )
			) );
		}

		return $actions;
	}

	// Script used to prevent cancelation of an order that has a receipt
	public function enqueue_script_cancel_documents() {
		global $post_type, $post;
		if ( $post_type && $post && $post_type == 'shop_order' ) {
			wp_register_script( 'hd_wc_ie_cancel_order', plugins_url( 'assets/js/cancel.js', INVOICEXPRESS_WOOCOMMERCE_PLUGIN_FILE ), array( 'jquery' ), INVOICEXPRESS_WOOCOMMERCE_VERSION, true );
			wp_localize_script( 'hd_wc_ie_cancel_order', 'hd_wc_ie_cancel_order', array(
				'alert_message' => __( 'This order cannot be cancelled since it was already issued a receipt', 'woo-billing-with-invoicexpress' ),
			) );
			wp_enqueue_script( 'hd_wc_ie_cancel_order' );
		}
	}

	public function cancelDocument( $document_id, $type, $state, $order_object ) {

		$reason = $this->getDocumentCancellationReason( $type, $order_object );

		$return = $this->changeOrderState( $document_id, $state, $type, $reason );
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
			do_action( 'invoicexpress_woocommerce_error', 'Cancel document: '.$error_notice, $order_object );
			return false;
		}
		return true;
	}

	public function getDocumentCancellationReason( $type, $order_object ) {

		switch ( $type ) {
			case 'invoice':
			case 'simplified_invoice':
			case 'invoice_receipt':
			case 'vat_moss_invoice':
				return $this->plugin->get_translated_option( 'hd_wc_ie_plus_cancellation_reason_invoices', null, $order_object );

			case 'transport':
				return $this->plugin->get_translated_option( 'hd_wc_ie_plus_cancellation_reason_transport_guides', null, $order_object );

			case 'devolution':
				return $this->plugin->get_translated_option( 'hd_wc_ie_plus_cancellation_reason_devolution_guides', null, $order_object );
		}

		return false;
	}

	// AJAX callable function that checks if order can be cancelled or not
	public function is_order_cancellable_ajax() {

		// The $_REQUEST contains all the data sent via ajax
		if ( isset( $_REQUEST ) ) {

			$order_id           = intval( $_REQUEST['order_id'] );
			$order_object       = wc_get_order( $order_id );
			$receipt_id         = $order_object->get_meta( 'hd_wc_ie_plus_receipt_id' );
			$invoice_receipt_id = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' );

			// Then the order has a receipt or an Invoice-receipt and thus cannot be cancelled
			if ( ! empty( $receipt_id ) || ! empty( $invoice_receipt_id ) ) {
				echo false;
			} else {
				echo true;
			}
		}

		// Always die in functions echoing ajax content
		die();
	}

	// Get Order Object from order_id and send it to do_action
	public function prepareAction( $order_id ) {
		$order_object = wc_get_order( $order_id );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;
		
		$canceled     = $order_object->get_meta( 'hd_wc_ie_plus_been_canceled' );
		if ( ! empty( $canceled ) ) {
			$order_object->delete_meta_data( 'hd_wc_ie_plus_been_canceled' );
			$order_object->save();
			$this->doAction( $order_obj );
		}
	}

	/*
	 * Produces the requested action.
	 */
	public function doAction( $order_object ) {

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;

		// in case of a invoice
		$invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' );
		if ( ! empty( $invoice_id ) ) {
			if ( $this->cancelDocument( $invoice_id, 'invoice', 'canceled', $order_object ) ) {
				$note = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					sprintf(
						/* translators: %s: document type, %d document number */
						__( '%s %d cancelled', 'woo-billing-with-invoicexpress' ),
						__( 'Invoice', 'woo-billing-with-invoicexpress' ),
						$invoice_id
					)
				);
				$order_object->add_order_note( $note );
				/* Add notice */
				Notices::add_notice(
					$note
				);
				do_action( 'invoicexpress_woocommerce_debug', $note, $order_object );

				do_action( 'invoicexpress_woocommerce_after_document_issue', $order_object->get_id(), 'cancel_document' );

				do_action( 'invoicexpress_woocommerce_after_document_finish', $order_object->get_id(), 'cancel_document' );

				//Get order again because it may have changed on the action above
				$order_object = wc_get_order( $order_object->get_id() );

				$order_object->update_meta_data( 'hd_wc_ie_plus_been_canceled', 'yes' );
				$order_object->save();
			}
		} else {

			// in case of a delivery note
			$transport_guide_id = $order_object->get_meta( 'hd_wc_ie_plus_transport_guide_id' );
			if ( ! empty( $transport_guide_id ) ) {
				if ( $this->cancelDocument( $transport_guide_id, 'transport', 'canceled', $order_object ) ) {
					$note = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
						sprintf(
							/* translators: %s: document type, %d document number */
							__( '%s %d cancelled', 'woo-billing-with-invoicexpress' ),
							__( 'Delivery note', 'woo-billing-with-invoicexpress' ),
							$transport_guide_id
						)
					);
					$order_object->add_order_note( $note );
					/* Add notice */
					Notices::add_notice(
						$note
					);
					do_action( 'invoicexpress_woocommerce_debug', $note, $order_object );
				}
			} else {

				// in case of a return delivery note
				$devolution_guide_id = $order_object->get_meta( 'hd_wc_ie_plus_devolution_guide_id' );
				if ( ! empty( $devolution_guide_id ) ) {
					if ( $this->cancelDocument( $devolution_guide_id, 'devolution', 'canceled', $order_object ) ) {
						$note = sprintf(
							'<strong>%s:</strong> %s',
							__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
							sprintf(
								/* translators: %s: document type, %d document number */
								__( '%s %d cancelled', 'woo-billing-with-invoicexpress' ),
								__( 'Return delivery note', 'woo-billing-with-invoicexpress' ),
								$devolution_guide_id
							)
						);
						$order_object->add_order_note( $note );
						/* Add notice */
						Notices::add_notice(
							$note
						);
						do_action( 'invoicexpress_woocommerce_debug', $note, $order_object );
					}
				}
			}
		}
	}

}


