<?php

namespace Webdados\InvoiceXpressWooCommerce\Settings\Tabs;

/**
 * Register invoices settings.
 *
 * @package InvoiceXpressWooCommerce
 * @since   2.0.0
 */
class Invoices extends \Webdados\InvoiceXpressWooCommerce\Settings\Tabs {

	/**
	 * Retrieve the array of plugin settings.
	 *
	 * @since  2.0.0
	 * @return array
	 */
	public function get_registered_settings() {

		if ( ! $this->settings->check_requirements() ) {
			return;
		}

		$automatic_invoices_types = array(
			'invoice'            => __( 'Invoice', 'woo-billing-with-invoicexpress' ),
			'simplified_invoice' => __( 'Simplified invoice', 'woo-billing-with-invoicexpress' ),
			'invoice_receipt'    => __( 'Invoice-receipt', 'woo-billing-with-invoicexpress' ),
		);
		if ( get_option( 'hd_wc_ie_plus_create_vat_moss_invoice' ) ) {
			foreach ( $automatic_invoices_types as $key => $type ) {
				$automatic_invoices_types[ $key ] = sprintf(
					/* translators: %1$s: document type, %2$s: VAT MOSS */
					__( '%1$s (or %2$s)', 'woo-billing-with-invoicexpress' ),
					$type,
					__( 'VAT MOSS', 'woo-billing-with-invoicexpress' )
				);
			}
		}

		$settings = array(
			'title'    => __( 'Invoices and Credit notes', 'woo-billing-with-invoicexpress' ),
			'sections' => array(
				'ix_invoices_invoices_receipt'    => array(
					'title'       => __( 'Invoice-receipts', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_invoice_receipt' => array(
							'title'       => sprintf(
								/* translators: %s: document type */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice-receipt', 'woo-billing-with-invoicexpress' )
							),
							'description' => __( 'Recommended if you’re getting paid before issuing the document, which is the most common scenario in online shops.', 'woo-billing-with-invoicexpress' ),
							'suffix'      => sprintf(
								/* translators: %s: document type */
								__( 'Allow issuing %s', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice-receipts', 'woo-billing-with-invoicexpress' )
							),
							'type'        => 'checkbox',
						),
						'hd_wc_ie_plus_send_invoice_receipt' => array(
							'title'        => sprintf(
								/* translators: %s: document type */
								__( 'Email %s', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice-receipt', 'woo-billing-with-invoicexpress' )
							),
							'suffix'       => sprintf(
								/* translators: %s: document type */
								__( 'Send %s to customer by email', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice-receipt', 'woo-billing-with-invoicexpress' )
							),
							'type'         => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_invoice_receipt',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_invoice_receipt_email_subject' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email subject', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice-receipt', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number} on {site_title}', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice-receipt', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_invoice_receipt',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_invoice_receipt_email_heading' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email heading', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice-receipt', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number}', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice-receipt', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_invoice_receipt',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_invoice_receipt_email_body' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email body', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice-receipt', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'textarea',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( 'Hi {customer_name},

Please find attached your %s for order #{order_number} from {order_date} on {site_title}.

Thank you.', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice-receipt', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'custom_attributes'      => array(
								'rows' => 8,
							),
							'parent_field'           => 'hd_wc_ie_plus_send_invoice_receipt',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
					),
				),
				'ix_invoices_invoices'            => array(
					'title'       => __( 'Invoices', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_create_invoice'     => array(
							'title'       => sprintf(
								/* translators: %s: document type */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice', 'woo-billing-with-invoicexpress' )
							),
							'description' => __( 'Not recommended if you’re getting paid before issuing the document, because you’ll have to issue a receipt afterward.', 'woo-billing-with-invoicexpress' ),
							'suffix'      => sprintf(
								/* translators: %s: document type */
								__( 'Allow issuing %s', 'woo-billing-with-invoicexpress' ),
								__( 'Invoices', 'woo-billing-with-invoicexpress' )
							),
							'type'        => 'checkbox',
						),
						'hd_wc_ie_plus_create_bulk_invoice' => array(
							'title'  => sprintf(
								'%s (%s)',
								sprintf(
									/* translators: %s: document type */
									__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
									__( 'Invoice', 'woo-billing-with-invoicexpress' )
								),
								__( 'in bulk', 'woo-billing-with-invoicexpress' )
							),
							'suffix' => sprintf(
								/* translators: %s: document type */
								__( 'Allow issuing %s', 'woo-billing-with-invoicexpress' ),
								__( 'one single Invoice for several orders', 'woo-billing-with-invoicexpress' )
							),
							'description'  => sprintf(
								'%s<br/><strong>%s</strong>',
								__( 'Not recommended unless you frequently get several orders from the same clients at the same time', 'woo-billing-with-invoicexpress' ),
								__( 'This feature will be discontinued soon and if you disable it you will not be able to enable it again', 'woo-billing-with-invoicexpress' )
							),
							'type'   => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_create_invoice',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_send_invoice'       => array(
							'title'        => sprintf(
								/* translators: %s: document type */
								__( 'Email %s', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice', 'woo-billing-with-invoicexpress' )
							),
							'suffix'       => sprintf(
								/* translators: %s: document type */
								__( 'Send %s to customer by email', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice', 'woo-billing-with-invoicexpress' )
							),
							'type'         => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_create_invoice',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_invoice_email_subject' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email subject', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number} on {site_title}', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_invoice',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_invoice_email_heading' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email heading', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number}', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_invoice',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_invoice_email_body' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email body', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'textarea',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( 'Hi {customer_name},

Please find attached your %s for order #{order_number} from {order_date} on {site_title}.

Thank you.', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'custom_attributes'      => array(
								'rows' => 8,
							),
							'parent_field'           => 'hd_wc_ie_plus_send_invoice',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
					),
				),
				'ix_invoices_simplified_invoices' => array(
					'title'       => __( 'Simplified invoices', 'woo-billing-with-invoicexpress' ),
					'description' => __( 'Only available for Portuguese InvoiceXpress accounts.', 'woo-billing-with-invoicexpress' ),
					'fields'      => array(
						'hd_wc_ie_plus_create_simplified_invoice' => array(
							'title'       => sprintf(
								/* translators: %s: document type */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'Simplified invoice', 'woo-billing-with-invoicexpress' )
							),
							'description' => __( 'Not recommended if you’re getting paid before issuing the document, because you’ll have to issue a receipt afterward.', 'woo-billing-with-invoicexpress' ),
							'suffix'      => sprintf(
								/* translators: %s: document type */
								__( 'Allow issuing %s', 'woo-billing-with-invoicexpress' ),
								__( 'Simplified invoices', 'woo-billing-with-invoicexpress' )
							),
							'type'        => 'checkbox',
						),
						'hd_wc_ie_plus_send_simplified_invoice' => array(
							'title'        => sprintf(
								/* translators: %s: document type */
								__( 'Email %s', 'woo-billing-with-invoicexpress' ),
								__( 'Simplified invoice', 'woo-billing-with-invoicexpress' )
							),
							'suffix'       => sprintf(
								/* translators: %s: document type */
								__( 'Send %s to customer by email', 'woo-billing-with-invoicexpress' ),
								__( 'Simplified invoice', 'woo-billing-with-invoicexpress' )
							),
							'type'         => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_create_simplified_invoice',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_simplified_invoice_email_subject' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email subject', 'woo-billing-with-invoicexpress' ),
								__( 'Simplified invoice', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number} on {site_title}', 'woo-billing-with-invoicexpress' ),
								__( 'Simplified invoice', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_simplified_invoice',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_simplified_invoice_email_heading' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email heading', 'woo-billing-with-invoicexpress' ),
								__( 'Simplified invoice', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number}', 'woo-billing-with-invoicexpress' ),
								__( 'Simplified invoice', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_simplified_invoice',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_simplified_invoice_email_body' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email body', 'woo-billing-with-invoicexpress' ),
								__( 'Simplified invoice', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'textarea',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( 'Hi {customer_name},

Please find attached your %s for order #{order_number} from {order_date} on {site_title}.

Thank you.', 'woo-billing-with-invoicexpress' ),
								__( 'Simplified invoice', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'custom_attributes'      => array(
								'rows' => 8,
							),
							'parent_field'           => 'hd_wc_ie_plus_send_simplified_invoice',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
					),
				),
				'ix_invoices_receipts'    => array(
					'title'       => __( 'Receipts', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'parent_field' => array( 'hd_wc_ie_plus_create_invoice', 'hd_wc_ie_plus_create_simplified_invoice' ),
					'parent_value' => '1',
					'fields'      => array(
						'hd_wc_ie_plus_invoice_payment' => array(
							'title'       => sprintf(
								/* translators: %s: document type */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'Receipt', 'woo-billing-with-invoicexpress' )
							),
							'suffix'      => __( 'Allow manually setting Invoices and Simplified invoices as paid', 'woo-billing-with-invoicexpress' ),
							'type'        => 'checkbox',
						),
						'hd_wc_ie_plus_send_receipt'       => array(
							'title'        => sprintf(
								/* translators: %s: document type */
								__( 'Email %s', 'woo-billing-with-invoicexpress' ),
								__( 'Receipt', 'woo-billing-with-invoicexpress' )
							),
							'suffix'       => sprintf(
								/* translators: %s: document type */
								__( 'Send %s to customer by email', 'woo-billing-with-invoicexpress' ),
								__( 'Receipt', 'woo-billing-with-invoicexpress' )
							),
							'type'         => 'checkbox',
						),
						'hd_wc_ie_plus_receipt_email_subject' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email subject', 'woo-billing-with-invoicexpress' ),
								__( 'Receipt', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number} on {site_title}', 'woo-billing-with-invoicexpress' ),
								__( 'Receipt', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_receipt',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_receipt_email_heading' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email heading', 'woo-billing-with-invoicexpress' ),
								__( 'Receipt', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number}', 'woo-billing-with-invoicexpress' ),
								__( 'Receipt', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_receipt',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_receipt_email_body' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email body', 'woo-billing-with-invoicexpress' ),
								__( 'Receipt', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'textarea',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( 'Hi {customer_name},

Please find attached your %s for order #{order_number} from {order_date} on {site_title}.

Thank you.', 'woo-billing-with-invoicexpress' ),
								__( 'Receipt', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'custom_attributes'      => array(
								'rows' => 8,
							),
							'parent_field'           => 'hd_wc_ie_plus_send_receipt',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
					),
				),
				'ix_invoices_credit_notes'        => array(
					'title'       => __( 'Credit notes', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_create_credit_note' => array(
							'title'  => sprintf(
								/* translators: %s: document type */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'Credit note', 'woo-billing-with-invoicexpress' )
							),
							'suffix' => sprintf(
								/* translators: %s: document type */
								__( 'Allow issuing %s', 'woo-billing-with-invoicexpress' ),
								__( 'Credit notes', 'woo-billing-with-invoicexpress' )
							),
							'description' => __( 'A Credit note will be automatically issued when an order, that already has an Invoice-receipt or Receipt, is (partially or totally) refunded.', 'woo-billing-with-invoicexpress' ),
							'type'   => 'checkbox',
						),
						'hd_wc_ie_plus_send_credit_note'   => array(
							'title'        => sprintf(
								/* translators: %s: document type */
								__( 'Email %s', 'woo-billing-with-invoicexpress' ),
								__( 'Credit note', 'woo-billing-with-invoicexpress' )
							),
							'suffix'       => sprintf(
								/* translators: %s: document type */
								__( 'Send %s to customer by email', 'woo-billing-with-invoicexpress' ),
								__( 'Credit note', 'woo-billing-with-invoicexpress' )
							),
							'type'         => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_create_credit_note',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_credit_note_email_subject' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email subject', 'woo-billing-with-invoicexpress' ),
								__( 'Credit note', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number} on {site_title}', 'woo-billing-with-invoicexpress' ),
								__( 'Credit note', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_credit_note',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_credit_note_email_heading' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email heading', 'woo-billing-with-invoicexpress' ),
								__( 'Credit note', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number}', 'woo-billing-with-invoicexpress' ),
								__( 'Credit note', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_credit_note',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_credit_note_email_body' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email body', 'woo-billing-with-invoicexpress' ),
								__( 'Credit note', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'textarea',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( 'Hi {customer_name},

Please find attached your %s for order #{order_number} from {order_date} on {site_title}.

Thank you.', 'woo-billing-with-invoicexpress' ),
								__( 'Credit note', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'custom_attributes'      => array(
								'rows' => 8,
							),
							'parent_field'           => 'hd_wc_ie_plus_send_credit_note',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
					),
				),
				'ix_invoices_misc'                => array(
					'title'       => __( 'General Invoices and Credit notes settings', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_cancellation_reason_invoices' => array(
							'title'             => __( 'Cancellation motive', 'woo-billing-with-invoicexpress' ),
							'description'       => __( 'Default cancellation motive for Invoice and Simplified invoice', 'woo-billing-with-invoicexpress' ),
							'type'              => 'text',
							'required_field'    => 'hd_wc_ie_plus_cancel_documents',
							'required_value'    => '1',
							'wpml'              => true,
						),
						'hd_wc_ie_plus_refund_automatic_message' => array(
							'title'             => __( 'Refund motive', 'woo-billing-with-invoicexpress' ),
							'description'       => __( 'Default refund motive', 'woo-billing-with-invoicexpress' ),
							'type'              => 'text',
							'wpml'              => true,
						),
						'hd_wc_ie_plus_automatic_receipt' => array(
							'title'        => __( 'Issue automatic receipt', 'woo-billing-with-invoicexpress' ),
							'suffix'       => __( 'Automatically set Invoices and Simplified invoices as paid after issuing', 'woo-billing-with-invoicexpress' ).(
								get_option( 'hd_wc_ie_plus_create_vat_moss_invoice' )
								?
								' ('.__( 'applies to VAT MOSS also', 'woo-billing-with-invoicexpress' ).')'
								:
								''
							),
							'description'  => __( 'For both manual or automatic Invoices - Recommended if you are getting prepaid, before shipping and issuing the invoice', 'woo-billing-with-invoicexpress' ),
							'type'         => 'checkbox',
							'class'        => array( 'only-for-invoice-or-simplified' ),
						),
						'hd_wc_ie_plus_automatic_receipt_state' => array(
							'title'        => __( 'Automatic receipt trigger', 'woo-billing-with-invoicexpress' ),
							'description'  => __( 'Issue the automatic receipt immediately after the invoice or on a specific order status?', 'woo-billing-with-invoicexpress' ),
							'type'         => 'select_order_status',
							'parent_field' => 'hd_wc_ie_plus_automatic_receipt',
							'parent_value' => '1',
						),
					),
				),
				'ix_invoices_automatic'           => array(
					'title'       => __( 'Automatic invoicing', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_automatic_invoice' => array(
							'title'  => __( 'Automatic issuing', 'woo-billing-with-invoicexpress' ),
							'suffix' => __( 'Issue invoicing documents automatically', 'woo-billing-with-invoicexpress' ),
							'type'   => 'checkbox',
							'description'  => apply_filters( 'invoicexpress_woocommerce_delay_automatic_invoice', false )
												?
												sprintf(
													/* translators: %1$s: time, %2$s: number of documents */
													__( 'Delayed %1$s via developer hooks - Currently %2$s documents scheduled' , 'woo-billing-with-invoicexpress' ),
													'<code>'.apply_filters( 'invoicexpress_woocommerce_delay_automatic_document_time_readable', apply_filters( 'invoicexpress_woocommerce_delay_automatic_invoice_time', 'T2M' ) ).'</code>',
													'<code id="pending_scheduled_invoicing_documents">0</code>'
												)
												.
												'<br/>'
												:
												'',
						),
						'hd_wc_ie_plus_automatic_invoice_zero_value' => array(
							'title'  => __( 'Orders with no value', 'woo-billing-with-invoicexpress' ),
							'suffix' => __( 'Issue automatic invoicing documents for orders with no value', 'woo-billing-with-invoicexpress' ),
							'type'   => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_automatic_invoice',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_automatic_invoice_prevent_exempt' => array(
							'title'  => __( 'Not for tax exempt orders', 'woo-billing-with-invoicexpress' ),
							'suffix' => __( 'Do not issue the automatic document for orders that have no VAT', 'woo-billing-with-invoicexpress' ),
							'description'  => __( 'Useful if you have several tax exemption motives and want to check if the correct one is assigned before issuing the document', 'woo-billing-with-invoicexpress' ),
							'type'   => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_automatic_invoice',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_automatic_invoice_type' => array(
							'title'        => __( 'Document type', 'woo-billing-with-invoicexpress' ),
							'description'  => __( 'The invoicing document that should be automatically issued (must be enabled on the options above)', 'woo-billing-with-invoicexpress' ),
							'type'         => 'select',
							'options'      => $automatic_invoices_types,
							'parent_field' => 'hd_wc_ie_plus_automatic_invoice',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_automatic_invoice_state' => array(
							'title'        => __( 'Order status', 'woo-billing-with-invoicexpress' ),
							'description'  => __( 'Order status that will trigger the automatic issue of the invoicing document', 'woo-billing-with-invoicexpress' ),
							'type'         => 'select_order_status',
							'parent_field' => 'hd_wc_ie_plus_automatic_invoice',
							'parent_value' => '1',
						),
					),
				),
			),
		);

		/* 2.4.3 - discontinue bulk */
		if ( ! get_option( 'hd_wc_ie_plus_create_bulk_invoice' ) ) {
			unset( $settings['sections']['ix_invoices_invoices']['fields']['hd_wc_ie_plus_create_bulk_invoice'] );
		}

		return apply_filters( 'invoicexpress_woocommerce_registered_invoices_settings', $settings );
	}
}
