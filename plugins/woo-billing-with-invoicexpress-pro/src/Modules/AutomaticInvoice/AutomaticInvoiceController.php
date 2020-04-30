<?php
namespace Webdados\InvoiceXpressWooCommerce\Modules\AutomaticInvoice;

use Webdados\InvoiceXpressWooCommerce\Plugin;
use Webdados\InvoiceXpressWooCommerce\BaseController as BaseController;
use Webdados\InvoiceXpressWooCommerce\Modules\Vat\VatController as VatController;

/* WooCommerce CRUD ready */

class AutomaticInvoiceController extends BaseController {

	// the instance of the documents scheduler
	private $scheduler;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.3.0
	 * @param Plugin $plugin This plugin's instance.
	 * @param DocumentsScheduler $scheduler The documents scheduler
	 */
	public function __construct( Plugin $plugin, $scheduler ) {
		parent::__construct( $plugin );

		$this->scheduler = $scheduler;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {

		if ( get_option( 'hd_wc_ie_plus_automatic_invoice' ) ) {

			// Automatic Invoice Creation at backoffice.
			add_action( 'save_post', array( $this, 'add_order_status_hooks_backoffice' ), 100, 2 );
		}

		if ( get_option( 'hd_wc_ie_plus_automatic_invoice' ) || get_option( 'hd_wc_ie_plus_automatic_transport_guide' ) ) {
			// Order status changed
			add_action( 'woocommerce_order_status_changed', array( $this, 'auto_invoice_check' ), 10, 3 );
		}

		if ( get_option( 'hd_wc_ie_plus_automatic_receipt' ) && ( get_option( 'hd_wc_ie_plus_automatic_receipt_state' ) != '' ) ) {
			// Order status changed
			add_action( 'woocommerce_order_status_changed', array( $this, 'auto_receipt_check' ), 10, 3 );
		}
	}

	public function add_order_status_hooks_backoffice( $post_id, $post ) {

		if ( is_admin() && ! is_ajax() ) {

			// Execute only when post type is like shop order -> We are not checking for other order types thay might be allowed by the 'invoicexpress_woocommerce_valid_order_types' filter
			if ( 'shop_order' != $post->post_type ) {
				return;
			}

			$order_object = wc_get_order( $post_id );

			// This is used to update receipt data on a case of partial refunds
			$new_meta = $order_object->get_meta( 'hd_wc_ie_plus_receipt_id_2' );
			if ( $new_meta != '' ) {
				$order_object->update_meta_data( 'hd_wc_ie_plus_receipt_id', $order_object->get_meta( 'hd_wc_ie_plus_receipt_id_2' ) );
				$order_object->update_meta_data( 'hd_wc_ie_plus_receipt_pdf', $order_object->get_meta( 'hd_wc_ie_plus_receipt_pdf_2' ) );
				$order_object->delete_meta_data( 'hd_wc_ie_plus_receipt_id_2' );
				$order_object->delete_meta_data( 'hd_wc_ie_plus_receipt_pdf_2' );
			}
			
			$order_object->save();

			$this->auto_invoice_check( $post_id, '', $order_object->get_status() );
			$this->auto_receipt_check( $post_id, '', $order_object->get_status() );
		}

	}

	public function auto_invoice_check( $post_id, $old_status, $current_status ) {

		$old_status     = str_replace( 'wc-wc-', 'wc-', 'wc-' . $old_status );
		$current_status = str_replace( 'wc-wc-', 'wc-', 'wc-' . $current_status );

		$order_object       = wc_get_order( $post_id );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;

		$prevent_automation = $order_object->get_meta( 'hd_wc_ie_plus_stop_automation' );
		$frontend           = $order_object->get_created_via() === 'checkout';

		$debug = 'Checking if automated invoicing document should be issued';
		do_action( 'invoicexpress_woocommerce_debug', $debug, $order_object, array(
			'old_status'         => $old_status,
			'current_status'     => $current_status,
			'prevent_automation' => $prevent_automation,
			'frontend'           => $frontend,
		) );

		// Prevent automation when status was changed automaticaly by creating a document.
		if ( $prevent_automation != 1 ) {

			//Stop duplicate documents if another request is made while the original document is still being created on InvoiceXpress
			$order_object->update_meta_data( 'hd_wc_ie_plus_stop_automation', 1 );
			$order_object->save();
			$debug = 'Preventing further automation';
			do_action( 'invoicexpress_woocommerce_debug', $debug, $order_object );

			// Prevent automation for order with no VAT and no exemption
			if (
				( '0' === get_option( 'hd_wc_ie_plus_tax_country' ) || '' === get_option( 'hd_wc_ie_plus_tax_country' ) ) // Not a portuguese company
				||
				(
					'1' === get_option( 'hd_wc_ie_plus_tax_country' ) // Portuguese company
					&&
					(
						floatval( $order_object->get_total_tax() ) > 0            // Has VAT
						|| // or
						$order_object->get_meta( '_billing_tax_exemption_reason' ) != '' // Has Tax Exemption
						|| // or
						( floatval( $order_object->get_total_tax() ) == 0 && floatval( $order_object->get_total() ) == 0 ) // Has no VAT but also has no value
					)
				)
			) {

				/*
				We need to completely refactor this

				1) Get status for invoices and for guides
				2) Get all statuses (??)
				3) Check if it should be triggered (trigger -> change at)
					- Estado = Faz sempre
					- Pending -> =
					- On hold -> Tb faz no Pending
					- Processing -> Tb faz no Pending, On hold
					- Completed -> Tb faz no Pending, On hold, Processing
					- Além disso verifica se ainda não existe documento do tipo (invoice ou guia)
				4) Se passou os checks anteriores, avança então com a action

				*/

				$all_status = $this->plugin->get_possible_status();

				$all_status_order = array();
				$i                = 0;
				foreach ( $all_status as $temp_status ) {
					$i++;
					$all_status_order[ $temp_status ] = $i;
				}

				/* DELIVERY NOTE */
				if ( get_option( 'hd_wc_ie_plus_automatic_transport_guide' ) ) {

					// Get trigger status for automatic invoice
					$status = get_option( 'hd_wc_ie_plus_automatic_guide_state' );

						$trigger = false;

					if ( in_array( $status, $all_status ) && isset( $all_status_order[ $current_status ] ) ) {
						if ( $status == $current_status || $all_status_order[ $current_status ] >= $all_status_order[ $status ] ) {
							$trigger = true;
						}
					}

					do_action( 'invoicexpress_woocommerce_debug', 'Automatic delivery note trigger: '.( $trigger ? 'true' : 'false' ), $order_object, array(
						'hd_wc_ie_plus_automatic_transport_guide' => $status,
						'current_status'                          => $current_status,
					) );

					if ( $trigger ) {

						//Prevent for orders with vat exemption
						if (
							$order_object->get_meta( '_billing_tax_exemption_reason' ) == ''
							||
							( '1' != get_option( 'hd_wc_ie_plus_automatic_guide_prevent_exempt' ) )
						) {

							$this->automaticGuideCreation( $post_id );

						} else {

							$notice = $order_object->get_meta( 'invoicexpress_not_automatic_guide_notice' );
							if ( ! $notice && $current_status !== 'auto-draft' && $frontend ) {
								$order_object = wc_get_order( $post_id ); //Why getting it again?
								$note           = sprintf(
									'<strong>%s:</strong> %s',
									__( 'InvoiceXpress warning', 'woo-billing-with-invoicexpress' ),
									sprintf(
										/* translators: %s: document type */
										__( 'This automatic %s was not created because the order is tax exempt', 'woo-billing-with-invoicexpress' ),
										__( 'Delivery note', 'woo-billing-with-invoicexpress' )
									)
								);
								$order_object->add_order_note( $note );
								$order_object->update_meta_data( 'invoicexpress_not_automatic_guide_notice', true );
								$order_object->save();
								$this->sendExemptionEmail( $order_object, 'vat_exemption', __( 'Delivery note', 'woo-billing-with-invoicexpress' ) );

								do_action( 'invoicexpress_woocommerce_error', $note, $order_object, array(
									'hd_wc_ie_plus_automatic_guide_prevent_exempt'    => get_option( 'hd_wc_ie_plus_automatic_guide_prevent_exempt' ),
									'order_object meta _billing_tax_exemption_reason' => $order_object->get_meta( '_billing_tax_exemption_reason' ),
								) );
							}

						}

					}
				}

				/* INVOICES */
				if ( get_option( 'hd_wc_ie_plus_automatic_invoice' ) ) {

					// Get trigger status for automatic invoice
					$status = get_option( 'hd_wc_ie_plus_automatic_invoice_state' );

					$trigger = false;

					if ( in_array( $status, $all_status ) && isset( $all_status_order[ $current_status ] ) ) {
						if ( $status == $current_status || $all_status_order[ $current_status ] >= $all_status_order[ $status ] ) {
							$trigger = true;
						}
					}

					do_action( 'invoicexpress_woocommerce_debug', 'Automatic invoice trigger: '.( $trigger ? 'true' : 'false' ), $order_object, array(
						'hd_wc_ie_plus_automatic_invoice_state' => $status,
						'current_status'                        => $current_status,
					) );

					if ( $trigger ) {

						// Prevent automatic invoice for orders with no value
						if (
							floatval( $order_object->get_total() ) > 0
							||
							( '1' === get_option( 'hd_wc_ie_plus_automatic_invoice_zero_value' ) )
						) {

							//Prevent for orders with vat exemption
							if (
								$order_object->get_meta( '_billing_tax_exemption_reason' ) == ''
								||
								( '1' != get_option( 'hd_wc_ie_plus_automatic_invoice_prevent_exempt' ) )
							) {

								$type = apply_filters( 'invoicexpress_woocommerce_automatic_invoice_type', get_option( 'hd_wc_ie_plus_automatic_invoice_type' ), $order_object );

								$this->automaticInvoiceCreation( $post_id, $type );

							} else {

								$notice = $order_object->get_meta( 'invoicexpress_not_automatic_notice' );
								if ( ! $notice && $current_status !== 'auto-draft' && $frontend ) {
									$order_object = wc_get_order( $post_id ); //Why getting it again?
									$note           = sprintf(
										'<strong>%s:</strong> %s',
										__( 'InvoiceXpress warning', 'woo-billing-with-invoicexpress' ),
										sprintf(
											/* translators: %s: document type */
											__( 'This automatic %s was not created because the order is tax exempt', 'woo-billing-with-invoicexpress' ),
											__( 'Invoice', 'woo-billing-with-invoicexpress' )
										)
									);
									$order_object->add_order_note( $note );
									$order_object->update_meta_data( 'invoicexpress_not_automatic_notice', true );
									$order_object->save();
									$this->sendExemptionEmail( $order_object, 'vat_exemption', __( 'Invoice', 'woo-billing-with-invoicexpress' ) );

									do_action( 'invoicexpress_woocommerce_error', $note, $order_object, array(
										'hd_wc_ie_plus_automatic_invoice_prevent_exempt'  => get_option( 'hd_wc_ie_plus_automatic_invoice_prevent_exempt' ),
										'order_object meta _billing_tax_exemption_reason' => $order_object->get_meta( '_billing_tax_exemption_reason' ),
									) );
								}

							}

						} else {

							$notice = $order_object->get_meta( 'invoicexpress_not_automatic_notice' );
							if ( ! $notice && $current_status !== 'auto-draft' && $frontend ) {
								$order_object = wc_get_order( $post_id );
								$note           = sprintf(
									'<strong>%s:</strong> %s',
									__( 'InvoiceXpress warning', 'woo-billing-with-invoicexpress' ),
									__( 'This automatic invoice was not created because it has no value', 'woo-billing-with-invoicexpress' )
								);
								$order_object->add_order_note( $note );
								$order_object->update_meta_data( 'invoicexpress_not_automatic_notice', true );
								$order_object->save();
								$this->sendExemptionEmail( $order_object, 'no_value', __( 'Invoice', 'woo-billing-with-invoicexpress' ) );

								do_action( 'invoicexpress_woocommerce_error', $note, $order_object, array(
									'hd_wc_ie_plus_automatic_invoice_zero_value'      => get_option( 'hd_wc_ie_plus_automatic_invoice_zero_value' ),
									'order_object get_total'                          => floatval( $order_object->get_total() ),
								) );
							}

						}
					}
				}

			} else {

				$notice = $order_object->get_meta( 'invoicexpress_not_automatic_notice' );
				if ( ! $notice && $current_status !== 'auto-draft' && $frontend ) {
					$order_object = wc_get_order( $post_id );
					$note         = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress warning', 'woo-billing-with-invoicexpress' ),
						__( 'This automatic invoice was not created, or will not be created, because there is no exemption motive – and it needs one', 'woo-billing-with-invoicexpress' )
					);
					$order_object->add_order_note( $note );
					$order_object->update_meta_data( 'invoicexpress_not_automatic_notice', true );
					$order_object->save();
					$this->sendExemptionEmail( $order_object, 'no_vat_exemption', __( 'Invoice', 'woo-billing-with-invoicexpress' ) );

					do_action( 'invoicexpress_woocommerce_error', $note, $order_object, array(
						'hd_wc_ie_plus_tax_country'                       => get_option( 'hd_wc_ie_plus_tax_country' ),
						'order_object meta _billing_tax_exemption_reason' => $order_object->get_meta( '_billing_tax_exemption_reason' ),
						'order_object get_total_tax'                      => floatval( $order_object->get_total_tax() ),
						'order_object get_total'                          => floatval( $order_object->get_total() ),
					) );
				}

			}

		}

		$order_object->update_meta_data( 'hd_wc_ie_plus_stop_automation', 0 );
		$order_object->save();
		$debug = 'Releasing further automation';
		do_action( 'invoicexpress_woocommerce_debug', $debug, $order_object );
	}

	/**
	 * Automatic receipt - If "Immediately after the invoice" is NOT chosen
	 *
	 * @since 2.4.5
	 */
	public function auto_receipt_check( $post_id, $old_status, $current_status ) {

		$old_status     = str_replace( 'wc-wc-', 'wc-', 'wc-' . $old_status );
		$current_status = str_replace( 'wc-wc-', 'wc-', 'wc-' . $current_status );

		$order_object       = wc_get_order( $post_id );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;
		
		$prevent_automation = $order_object->get_meta( 'hd_wc_ie_plus_stop_automation' );
		$frontend           = $order_object->get_created_via() === 'checkout';

		$debug = 'Checking if automated receipt should be issued';
		do_action( 'invoicexpress_woocommerce_debug', $debug, $order_object, array(
			'old_status'         => $old_status,
			'current_status'     => $current_status,
			'prevent_automation' => $prevent_automation,
			'frontend'           => $frontend,
		) );

		// Prevent automation when status was changed automaticaly by creating a document.
		if ( $prevent_automation != 1 ) {

			//Stop duplicate documents if another request is made while the original document is still being created on InvoiceXpress
			$order_object->update_meta_data( 'hd_wc_ie_plus_stop_automation', 1 );
			$order_object->save();
			$debug = 'Preventing further automation';
			do_action( 'invoicexpress_woocommerce_debug', $debug, $order_object );

			if ( get_option( 'hd_wc_ie_plus_automatic_receipt' ) && ( get_option( 'hd_wc_ie_plus_automatic_receipt_state' ) != '' ) ) {

				$all_status = $this->plugin->get_possible_status();

				$all_status_order = array();
				$i                = 0;
				foreach ( $all_status as $temp_status ) {
					$i++;
					$all_status_order[ $temp_status ] = $i;
				}

				// Get trigger status for automatic invoice
				$status = get_option( 'hd_wc_ie_plus_automatic_receipt_state' );

				$trigger = false;

				if ( in_array( $status, $all_status ) && isset( $all_status_order[ $current_status ] ) ) {
					if ( $status == $current_status || $all_status_order[ $current_status ] >= $all_status_order[ $status ] ) {
						$trigger = true;
					}
				}

				do_action( 'invoicexpress_woocommerce_debug', 'Automatic receipt trigger: '.( $trigger ? 'true' : 'false' ), $order_object, array(
					'hd_wc_ie_plus_automatic_receipt_state' => $status,
					'current_status'                        => $current_status,
				) );

				if ( $trigger ) {
					do_action( 'invoicexpress_woocommerce_do_automatic_receipt', $order_object->get_id() );
				}

			}
		}

		$order_object->update_meta_data( 'hd_wc_ie_plus_stop_automation', 0 );
		$order_object->save();
		$debug = 'Releasing further automation';
		do_action( 'invoicexpress_woocommerce_debug', $debug, $order_object );
	}

	public function automaticInvoiceCreation( $post_id, $type ) {
		$order_object = wc_get_order( $post_id );

		$invoice_id          = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' );
		$invoice_receipt_id  = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' );
		$vat_moss_invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_vat_moss_invoice_id' );
		$has_scheduled       = apply_filters( 'invoicexpress_woocommerce_has_pending_scheduled_invoicing_document', false, $order_object->get_id() );

		if ( empty( $invoice_id ) && empty( $invoice_receipt_id ) && empty( $vat_moss_invoice_id ) && ( ! $has_scheduled ) ) { //Or already scheduled?
			switch ( $type ) {
				case 'simplified_invoice':
					//Schedule or do it right away?
					if ( apply_filters( 'invoicexpress_woocommerce_delay_automatic_invoice', false ) ) {
						$this->scheduler->schedule_automatic_document( $order_object, 'simplified_invoice' );
					} else {
						do_action( 'woocommerce_order_action_hd_wc_ie_plus_generate_simplified_invoice', $order_object, 'automatic' );
					}
					break;
				case 'invoice_receipt':
					//Schedule or do it right away?
					if ( apply_filters( 'invoicexpress_woocommerce_delay_automatic_invoice', false ) ) {
						$this->scheduler->schedule_automatic_document( $order_object, 'invoice_receipt' );
					} else {
						do_action( 'woocommerce_order_action_hd_wc_ie_plus_generate_invoice_receipt', $order_object, 'automatic' );
					}
					break;
				case 'vat_moss_invoice':
					//Schedule or do it right away?
					if ( apply_filters( 'invoicexpress_woocommerce_delay_automatic_invoice', false ) ) {
						$this->scheduler->schedule_automatic_document( $order_object, 'vat_moss_invoice' );
					} else {
						do_action( 'woocommerce_order_action_hd_wc_ie_plus_generate_vat_moss_invoice', $order_object, 'automatic' );
					}
					break;
				case 'invoice':
				default:
					//Schedule or do it right away?
					if ( apply_filters( 'invoicexpress_woocommerce_delay_automatic_invoice', false ) ) {
						$this->scheduler->schedule_automatic_document( $order_object, 'invoice' );
					} else {
						do_action( 'woocommerce_order_action_hd_wc_ie_plus_generate_invoice', $order_object, 'automatic' );
					}
					break;
			}
		} else {
			$error_notice = sprintf(
				/* translators: %s: document type */
				__( "The %s wasn't created because this order already has an invoice type document or one is scheduled to be issued.", 'woo-billing-with-invoicexpress' ),
				$this->plugin->type_names[$type]
			);
			do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object, array(
				'order_object meta hd_wc_ie_plus_invoice_id' => $invoice_id,
				'order_object meta invoice_receipt_id'       => $invoice_receipt_id,
				'has_scheduled'                              => $has_scheduled
			) );
		}
	}

	public function sendExemptionEmail( $order_object, $email_type, $document_type ) {

		$email   = get_option( 'admin_email' );

		switch ( $email_type ) {

			case 'vat_exemption':
				$subject = __( 'InvoiceXpress warning', 'woo-billing-with-invoicexpress' ) . ' - ' . sprintf(
					__( 'Order #%d is tax exempt', 'woo-billing-with-invoicexpress' ),
					$order_object->get_id()
				);
				$heading = __( 'Order is tax exempt', 'woo-billing-with-invoicexpress' );
				$body    = sprintf(
					__( 'Order #%d needs to have VAT in order to issue an automatic %s.', 'woo-billing-with-invoicexpress' ),
					$order_object->get_id(),
					$document_type
				);
				break;

			case 'no_vat_exemption':
				$subject = __( 'InvoiceXpress warning', 'woo-billing-with-invoicexpress' ) . ' - ' . sprintf(
					__( 'Order #%d needs tax exemption motive', 'woo-billing-with-invoicexpress' ),
					$order_object->get_id()
				);
				$heading = __( 'Order needs tax exemption motive', 'woo-billing-with-invoicexpress' );
				$body    = sprintf(
					__( 'Order #%d needs tax exemption motive in order to issue an automatic %s.', 'woo-billing-with-invoicexpress' ),
					$order_object->get_id(),
					$document_type
				);
				break;

			case 'no_value':
				$subject= __( 'InvoiceXpress warning', 'woo-billing-with-invoicexpress' ) . ' - ' . sprintf(
					__( 'Order #%d has no value', 'woo-billing-with-invoicexpress' ),
					$order_object->get_id()
				);
				$heading = __( 'Order has no value', 'woo-billing-with-invoicexpress' );
				$body    = sprintf(
					__( 'Order #%d needs to have value in order to issue an automatic %s.', 'woo-billing-with-invoicexpress' ),
					$order_object->get_id(),
					$document_type
				);
				break;
				
		}

		// Apply filters.
		$subject = apply_filters( 'invoicexpress_woocommerce_automatic_invoice_exemption_email_subject', $subject, $order_object, $email_type );
		$heading = apply_filters( 'invoicexpress_woocommerce_automatic_invoice_exemption_email_heading', $heading, $order_object, $email_type );
		$body    = nl2br( apply_filters( 'invoicexpress_woocommerce_automatic_invoice_exemption_email_body', $body, $order_object, $email_type ) );

		$email_headers[] = 'From: ' . get_option( 'woocommerce_email_from_name' ) . ' <' . get_option( 'woocommerce_email_from_address' ) . '>';

		add_filter( 'wp_mail_content_type', array( $this, 'set_email_to_html' ) );

		// Email Message - Start
		ob_start();
		// Get email header
		wc_get_template( 'emails/email-header.php', array( 'email_heading' => $heading ) );
		echo $body;
		// Get email footer
		wc_get_template( 'emails/email-footer.php' );
		// Email Message - End
		$message = ob_get_clean();

		// Send Email
		$status = wc_mail( $email, $subject, $message, $email_headers );

		remove_filter( 'wp_mail_content_type', array( $this, 'set_email_to_html' ) );
	}

	public function automaticGuideCreation( $post_id ) {
		$order_object = wc_get_order( $post_id );

		$meta          = $order_object->get_meta( 'hd_wc_ie_plus_transport_guide_id' );
		$has_scheduled = apply_filters( 'invoicexpress_woocommerce_has_pending_scheduled_guide_document', false, $order_object->get_id() );

		if ( empty( $meta ) && ( ! $has_scheduled ) ) {
			//Schedule or do it right away?
			if ( apply_filters( 'invoicexpress_woocommerce_delay_automatic_guide', false ) ) {
				$this->scheduler->schedule_automatic_document( $order_object, 'transport_guide' );
			} else {
				do_action( 'woocommerce_order_action_hd_wc_ie_plus_generate_transport_guide', $order_object, 'automatic' );
			}
		} else {
			$error_notice = sprintf(
				/* translators: %s: document type */
				__( "The %s wasn't created because this order already has one or is scheduled to be issued.", 'woo-billing-with-invoicexpress' ),
				$this->plugin->type_names['transport_guide']
			);
			do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object, array(
				'order_object meta hd_wc_ie_plus_transport_guide_id' => $meta,
				'has_scheduled'                                      => $has_scheduled
			) );
		}
	}
}
