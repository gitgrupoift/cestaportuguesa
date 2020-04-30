<?php
namespace Webdados\InvoiceXpressWooCommerce\Modules\Invoice;

use Webdados\InvoiceXpressWooCommerce\BaseController as BaseController;
use Webdados\InvoiceXpressWooCommerce\JsonRequest as JsonRequest;
use Webdados\InvoiceXpressWooCommerce\ClientChecker as ClientChecker;
use Webdados\InvoiceXpressWooCommerce\Notices as Notices;

/* WooCommerce CRUD ready */
/* JSON API ready */

class InvoiceController extends BaseController {

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {

		if ( get_option( 'hd_wc_ie_plus_create_invoice' ) ) {

			//Regular invoices
			add_filter(
				'woocommerce_order_actions', array(
					$this,
					'order_actions',
				), 10, 1
			);
			add_action(
				'woocommerce_order_action_hd_wc_ie_plus_generate_invoice', array(
					$this,
					'doAction',
				), 10, 2
			);

			if ( get_option( 'hd_wc_ie_plus_create_bulk_invoice' ) ) {
				// Add custom bulk action to WooCommerce Orders
				add_action(
					'admin_footer-edit.php', array(
						$this,
						'create_bulk_invoice_add_action'
					)
				);
				// Execute bulk action
				add_action(
					'load-edit.php', array(
						$this,
						'process_bulk_invoice'
					)
				);
			}

		}

		// AJAX request to load custom forms
		add_action( 'wp_ajax_hd_wc_ie_prevent_invoice', array( $this, 'prevent_invoice_ajax' ) );
	}

	public function create_bulk_invoice_add_action() {
		global $post_type;

		if ( $post_type == 'shop_order' ) {
			?>
	  <script type="text/javascript">
		  jQuery(document).ready(function() {
			  jQuery('<option>').val('single_invoice').text('<?php printf(
					'%s: %s',
					esc_html__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					__( 'Issue single Invoice from Orders', 'woo-billing-with-invoicexpress' )
				); ?>').appendTo("select[name='action']");
			  jQuery('<option>').val('single_invoice').text('<?php printf(
					'%s: %s',
					esc_html__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					__( 'Issue single Invoice from Orders', 'woo-billing-with-invoicexpress' )
				); ?>').appendTo("select[name='action2']");
		  });
	  </script>
			<?php
		}
	}

	public function process_bulk_invoice() {

		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$action        = $wp_list_table->current_action();

		// Bail out if this is not a status-changing action
		if ( $action !== 'single_invoice' ) {
			return;
		}

		$post_ids     = array_map( 'absint', (array) $_REQUEST['post'] );
		$result_array = $this->doBulkAction( $post_ids );
		$result       = $result_array[0];
		$message      = $result_array[1];

		$sendback = add_query_arg(
			array(
				'post_type'       => 'shop_order',
				'ids'             => join( ',', $post_ids ),
			), ''
		);

		/* Add notice */
		Notices::add_notice( sprintf(
				'<strong>%s:</strong> %s',
				__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
				$message
			),
			$result == 'error' ? 'error' : 'success'
		);

		wp_redirect( esc_url_raw( $sendback ) );
		exit();
	}

	public function doBulkAction( $post_ids ) {
		$order_array   = array();
		$error         = array();
		$order_numbers = array();
		foreach ( $post_ids as $post_id ) {
			$order           = wc_get_order( $post_id );
			$order_array[]   = $order;
			$order_numbers[] = $this->get_order_number( $order );
			if ( isset( $vat ) ) {
				$new_vat = $order->get_meta( '_billing_VAT_code' );
				if ( $vat == $new_vat ) {
					$vat = $order->get_meta( '_billing_VAT_code' );
				} else {
					if ( ! in_array( __( 'Orders must have same VAT.', 'woo-billing-with-invoicexpress' ), $error ) ) {
						$error[] = __( 'Orders must have same VAT.', 'woo-billing-with-invoicexpress' );
						break;
					}
				}
			} else {
				$vat = $order->get_meta( '_billing_VAT_code' );
			}

			$invoice_id            = $order->get_meta( 'hd_wc_ie_plus_invoice_id' );
			$simplified_invoice_id = $order->get_meta( 'hd_wc_ie_plus_simplified_invoice_id' );
			$invoice_receipt_id    = $order->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' );
			$vat_moss_invoice_id   = $order_object->get_meta( 'hd_wc_ie_plus_vat_moss_invoice_id' );
			$credit_note_id        = $order->get_meta( 'hd_wc_ie_plus_credit_note_id' );
			$has_scheduled         = apply_filters( 'invoicexpress_woocommerce_has_pending_scheduled_invoicing_document', false, $order->get_id() );

			if (
				(
					! empty( $invoice_id )
					||
					! empty( $simplified_invoice_id )
					||
					! empty( $invoice_receipt_id )
					||
					! empty( $vat_moss_invoice_id )
					||
					! $has_scheduled
				)
				&&
				empty( $credit_note_id )
			) {
				$error[] = sprintf( __( 'Order %d already has an invoice document or one is scheduled to be issued.', 'woo-billing-with-invoicexpress' ), $post_id );
			}
		}

		if ( ! empty( $error ) ) {
			$error_notice = implode( ', ', $error );
			do_action( 'invoicexpress_woocommerce_error', 'Bulk Invoice: '.$error_notice );
			return array( 'error', $error_notice );
		}

		if ( get_option( 'hd_wc_ie_plus_vat_field_mandatory' ) && empty( $vat ) ) {
			// se não tiver NIF lança aviso
			$error_notice = __( 'The VAT number is required', 'woo-billing-with-invoicexpress' );
			do_action( 'invoicexpress_woocommerce_error', 'Bulk Invoice: '.$error_notice );
			return array( 'error', $error_notice );
		}

		$first_order_object = $order_array[0];

		$client_name = $this->get_document_client_name( $first_order_object );
		$checker = new ClientChecker();
		$client_info = $checker->maybeCreateClient( $client_name, $first_order_object );

		$client_data = array(
			'name' => $client_name,
			'code' => $client_info['client_code'],
		);

		$items = array();
		foreach ( $order_array as $order_object ) {
			$items_data = $this->getOrderItemsForDocument( $order_object, 'invoice' );
			$items = array_merge( $items, $items_data );
		}

		$invoice_data = array(
			'date'             => date_i18n( 'd/m/Y' ),
			'due_date'         => $this->get_due_date( 'invoice' ),
			'reference'        => implode( ', ', $order_numbers ),
			'client'           => $client_data,
			'items'            => $items,
			'sequence_id'      => $this->find_sequence_id( $first_order_object->get_id(), 'invoice' ),
		);
		if ( $first_order_object->get_meta( '_billing_tax_exemption_reason' ) ) {
			$invoice_data['tax_exemption'] = $first_order_object->get_meta( '_billing_tax_exemption_reason' );
		}

		$invoice_data = $this->process_items( $invoice_data, $first_order_object, 'invoice' );

		$invoice_data = apply_filters( 'invoicexpress_woocommerce_bulk_invoice_data', $invoice_data, $first_order_object, $order_array );

		//Prevent issuing?
		foreach ( $order_array as $order_object ) {
			$prevent = $this->preventDocumentIssuing( $order_object, 'invoice', $invoice_data, 'manual' );
			if ( isset( $prevent['prevent'] ) && $prevent['prevent'] ) {
				$error_notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'Document not issued', 'woo-billing-with-invoicexpress' ),
					isset( $prevent['message'] ) && trim( $prevent['message'] ) != '' ? trim( $prevent['message'] ) : __( 'Reason unknown', 'woo-billing-with-invoicexpress' )
				);
				do_action( 'invoicexpress_woocommerce_error', 'Bulk Invoice: '.$error_notice, $first_order_object );
				return array( 'error', $error_notice );
			}
		}

		$params = array(
			'request' => 'invoices.json',
			'args'    => array(
				'invoice' => $invoice_data
			),
		);
		$json_request = new JsonRequest( $params );
		$return = $json_request->postRequest();
		if ( ! $return['success'] ) {
			$codeStr    = __( 'Code', 'woo-billing-with-invoicexpress' );
			$messageStr = __( 'Message', 'woo-billing-with-invoicexpress' );
			/* Add notice */
			$error_notice = sprintf(
				'<strong>%s:</strong> %s',
				__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
				$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message']
			);
			do_action( 'invoicexpress_woocommerce_error', 'Bulk Invoice (issue): '.$error_notice, $first_order_object );
			return array( 'error', $error_notice );
		}

		$order_id_invoicexpress = $return['object']->invoice->id;

		foreach ( $order_array as $order_object ) {
			//Update client data
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_id', $client_info['client_id'] );
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_code', $client_info['client_code'] );
			//Update invoice data
			$order_object->update_meta_data( 'hd_wc_ie_plus_invoice_id', $order_id_invoicexpress );
			$order_object->update_meta_data( 'hd_wc_ie_plus_invoice_permalink', $return['object']->invoice->permalink );
			$order_object->update_meta_data( 'hd_wc_ie_plus_invoice_type', 'invoice' );
			$order_object->save();

			do_action( 'invoicexpress_woocommerce_after_document_issue', $order_object->get_id(), 'invoice' );

			//Get order again because it may have changed on the action above
			$order_object = wc_get_order( $order_object->get_id() );
		}

		if ( get_option( 'hd_wc_ie_plus_leave_as_draft' ) ) {

			/* Leave as Draft */
			$this->draft_document_note( $order_object, __( 'Invoice', 'woo-billing-with-invoicexpress' ) );
			return array( 'updated', esc_html( sprintf(
				/* translators: %s: document type */
				__( 'The document (%s) was created as draft on InvoiceXpress and you should finalize it there.', 'woo-billing-with-invoicexpress' ),
				__( 'Invoice', 'woo-billing-with-invoicexpress' )
			) ) );

		} else {

			/* Change document state to final */
			$return = $this->changeOrderState( $order_id_invoicexpress, 'finalized', 'invoice' );
			if ( ! $return['success'] ) {
				$codeStr    = __( 'Code', 'woo-billing-with-invoicexpress' );
				$messageStr = __( 'Message', 'woo-billing-with-invoicexpress' );
				/* Add notice */
				$error_notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
					$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message']
				);
				do_action( 'invoicexpress_woocommerce_error', 'Bulk Invoice (finalize): '.$error_notice, $first_order_object );
				return array( 'error', $error_notice );
			}

			$sequence_number = $return['object']->invoice->inverted_sequence_number;

			/* Get a PDF - We should migrate this to the new getAndSendPDF function (or similar) */
			$return = $this->getDocumentPDF( $order_id_invoicexpress );
			if ( ! $return['success'] ) {
				$codeStr    = __( 'Code', 'woo-billing-with-invoicexpress' );
				$messageStr = __( 'Message', 'woo-billing-with-invoicexpress' );
				/* Add notice */
				$error_notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
					$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message']
				);
				do_action( 'invoicexpress_woocommerce_error', 'Bulk Invoice (get PDF): '.$error_notice, $first_order_object );
				return array( 'error', $error_notice );
			}

			$document_url = $return['object']->output->pdfUrl;

			$this->storeAndNoteMassDocument( $order_array, $document_url, 'invoice', $order_id_invoicexpress, $sequence_number );

			do_action( 'invoicexpress_woocommerce_before_document_email', $first_order_object->get_id(), 'invoice' );

			if ( get_option( 'hd_wc_ie_plus_send_invoice' ) ) {
				// Check if there is a email set.
				if ( ! empty( $first_order_object->get_billing_email() ) ) {
					$attachment = $first_order_object->get_meta( 'hd_wc_ie_plus_invoice_pdf' );
					$this->send_invoice_email( $first_order_object->get_billing_email(), $order_id_invoicexpress, $first_order_object->get_id(), $first_order_object, $attachment );
				}
			}

			do_action( 'invoicexpress_woocommerce_after_document_finish', $first_order_object->get_id(), 'invoice' );

			return array( 'updated', sprintf(
				/* translators: %1$s: document name, %2$s: document number */
				__( 'Successfully created %1$s %2$s', 'woo-billing-with-invoicexpress' ),
				__( 'Invoice', 'woo-billing-with-invoicexpress' ),
				! empty( $sequence_number ) ? $sequence_number : '' 
			) );
		}

	}

	public function prevent_invoice_ajax() {

		// The $_REQUEST contains all the data sent via ajax
		if ( isset( $_REQUEST ) ) {

			$post_id = $_REQUEST['orderID'];
			$order = wc_get_order( $post_id );

			$result = $order_object->get_created_via() === 'checkout';

			// Send response in JSON format
			echo json_encode( $result );

		}

		// Always die in functions echoing ajax content
		die();
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
				__( 'Invoice', 'woo-billing-with-invoicexpress' )
			),
			__( 'PDF', 'woo-billing-with-invoicexpress' )
		) );

		$generate_invoice = apply_filters( 'invoicexpress_woocommerce_order_action_title', $generate_invoice, $order_object, 'invoice', 'hd_wc_ie_plus_generate_invoice' );

		$invoice_id            = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' );
		$simplified_invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_simplified_invoice_id' );
		$invoice_receipt_id    = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' );
		$vat_moss_invoice_id   = $order_object->get_meta( 'hd_wc_ie_plus_vat_moss_invoice_id' );
		$credit_note_id        = $order_object->get_meta( 'hd_wc_ie_plus_credit_note_id' );
		$has_scheduled         = apply_filters( 'invoicexpress_woocommerce_has_pending_scheduled_invoicing_document', false, $order_object->get_id() );

		if ( $has_scheduled ) {
			if ( apply_filters( 'invoicexpress_woocommerce_check_pending_scheduled_document', false, $order_object->get_id(), array( 'invoice' ) ) ) {
				//Has Invoice scheduled - Clock
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
				if ( ! empty( $invoice_id ) ) {
					//There's already a Invoice - Check
					$symbol = '&#x2713;';
				}
			}
		}

		$actions['hd_wc_ie_plus_generate_invoice'] = trim( sprintf(
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

		$invoice_id            = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' );
		$simplified_invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_simplified_invoice_id' );
		$invoice_receipt_id    = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' );
		$vat_moss_invoice_id   = $order_object->get_meta( 'hd_wc_ie_plus_vat_moss_invoice_id' );
		$credit_note_id        = $order_object->get_meta( 'hd_wc_ie_plus_credit_note_id' );
		$has_scheduled         = apply_filters( 'invoicexpress_woocommerce_has_pending_scheduled_invoicing_document', false, $order_object->get_id() );

		$debug = 'Checking if Invoice document should be issued';
		do_action( 'invoicexpress_woocommerce_debug', $debug, $order_object, array(
			'hd_wc_ie_plus_invoice_id'            => $invoice_id,
			'hd_wc_ie_plus_simplified_invoice_id' => $simplified_invoice_id,
			'hd_wc_ie_plus_invoice_receipt_id'    => $invoice_receipt_id,
			'hd_wc_ie_plus_vat_moss_invoice_id'   => $vat_moss_invoice_id,
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
					__( 'VAT is a required field.', 'woo-billing-with-invoicexpress' )
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

			$items_data = $this->getOrderItemsForDocument( $order_object, 'invoice' );

			$invoice_data = array(
				'date'             => date_i18n( 'd/m/Y' ),
				'due_date'         => $this->get_due_date( 'invoice' ),
				'reference'        => $this->get_order_number( $order_object ),
				'client'           => $client_data,
				'items'            => $items_data,
				'sequence_id'      => $this->find_sequence_id( $order_object->get_id(), 'invoice' ),
				'owner_invoice_id' => $order_object->get_meta( 'hd_wc_ie_plus_transport_guide_id' ),
				'observations'     => $order_object->get_meta( '_document_observations' ),
			);

			$tax_exemption = $order_object->get_meta( '_billing_tax_exemption_reason' );
			if ( ! empty( $tax_exemption ) ) {
				$invoice_data['tax_exemption'] = $tax_exemption;
			}

			$invoice_data = $this->process_items( $invoice_data, $order_object, 'invoice' );

			$invoice_data = apply_filters( 'invoicexpress_woocommerce_invoice_data', $invoice_data, $order_object );

			//Prevent issuing?
			$prevent = $this->preventDocumentIssuing( $order_object, 'invoice', $invoice_data, $mode );
			if ( isset( $prevent['prevent'] ) && $prevent['prevent'] ) {
				$this->preventDocumentIssuingLogger( $prevent, 'invoice', $order_object, $mode );
				return;
			}

			$params = array(
				'request' => 'invoices.json',
				'args'    => array(
					'invoice' => $invoice_data
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
							__( 'Invoice', 'woo-billing-with-invoicexpress' )
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
				do_action( 'invoicexpress_woocommerce_error', 'Issue Invoice: '.$error_notice, $order_object );
				return;
			}
			
			$order_id_invoicexpress = $return['object']->invoice->id;

			//Update client data
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_id', $client_info['client_id'] );
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_code', $client_info['client_code'] );
			//Update invoice data
			$order_object->update_meta_data( 'hd_wc_ie_plus_invoice_id', $order_id_invoicexpress );
			$order_object->update_meta_data( 'hd_wc_ie_plus_invoice_permalink', $return['object']->invoice->permalink );
			$order_object->update_meta_data( 'hd_wc_ie_plus_invoice_type', 'invoice' );
			$order_object->save();

			do_action( 'invoicexpress_woocommerce_after_document_issue', $order_object->get_id(), 'invoice' );

			//Get order again because it may have changed on the action above
			$order_object = wc_get_order( $order_object->get_id() );

			if ( get_option( 'hd_wc_ie_plus_leave_as_draft' ) ) {

				/* Leave as Draft */
				$this->draft_document_note( $order_object, __( 'Invoice', 'woo-billing-with-invoicexpress' ) );

				/* Add notice */
				$notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					sprintf(
						/* translators: %s: document type */
						__( 'Successfully created %s as draft', 'woo-billing-with-invoicexpress' ),
						__( 'Invoice', 'woo-billing-with-invoicexpress' )
					)
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice( $notice );
				}
				do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );

				return;

			} else {

				/* Change document state to final */
				$return = $this->changeOrderState( $order_id_invoicexpress, 'finalized', 'invoice' );
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
					do_action( 'invoicexpress_woocommerce_error', 'Change Invoice state to finalized: '.$error_notice, $order_object );
					return;
				} else {
					$notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
						sprintf(
							/* translators: %s: document type */
							__( 'Successfully finalized %s', 'woo-billing-with-invoicexpress' ),
							__( 'Invoice', 'woo-billing-with-invoicexpress' )
						)
					);
					do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );
				}

				$sequence_number = $return['object']->invoice->inverted_sequence_number;
				$order_object->update_meta_data( 'hd_wc_ie_plus_invoice_sequence_number', $sequence_number );
				$order_object->save();

				/* Add notice */
				$notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					trim(
						sprintf(
							/* translators: %1$s: document name, %2$s: document number */
							__( 'Successfully created %1$s %2$s', 'woo-billing-with-invoicexpress' ),
							__( 'Invoice', 'woo-billing-with-invoicexpress' ),
							! empty( $sequence_number ) ? $sequence_number : '' 
						)
					)
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice( $notice );
				}
				do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );

				do_action( 'invoicexpress_woocommerce_before_document_email', $order_object->get_id(), 'invoice' );

				/* Get and send the PDF */
				if ( ! $this->getAndSendPDF( $order_object, 'invoice', $order_id_invoicexpress, $mode ) ) {
					return;
				}

				do_action( 'invoicexpress_woocommerce_after_document_finish', $order_object->get_id(), 'invoice' );
			}

		} else {
			/* Add notice */
			$error_notice = sprintf(
				'<strong>%s:</strong> %s',
				__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
				sprintf(
					/* translators: %s: document type */
					__( "The %s wasn't created because this order already has an invoice type document or one is scheduled to be issued.", 'woo-billing-with-invoicexpress' ),
					__( 'Invoice', 'woo-billing-with-invoicexpress' )
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

}
