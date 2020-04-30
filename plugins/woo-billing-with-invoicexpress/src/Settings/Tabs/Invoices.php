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

		$settings = array(
			'title'    => __( 'Invoices', 'woo-billing-with-invoicexpress' ),
			'sections' => array(
				'ix_invoices_invoices'            => array(
					'title'       => __( 'Invoices', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_create_invoice'     => array(
							'title'  => sprintf(
								/* translators: %s: document type */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice', 'woo-billing-with-invoicexpress' )
							),
							'suffix' => sprintf(
								/* translators: %s: document type */
								__( 'Allow issuing %s', 'woo-billing-with-invoicexpress' ),
								__( 'Invoices', 'woo-billing-with-invoicexpress' )
							),
							'type'   => 'checkbox',
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
							'title'  => sprintf(
								/* translators: %s: document type */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'Simplified invoice', 'woo-billing-with-invoicexpress' )
							),
							'suffix' => sprintf(
								/* translators: %s: document type */
								__( 'Allow issuing %s', 'woo-billing-with-invoicexpress' ),
								__( 'Simplified invoices', 'woo-billing-with-invoicexpress' )
							),
							'type'   => 'checkbox',
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
			),
		);

		/* 2.4.3 - discontinue bulk */
		if ( ! get_option( 'hd_wc_ie_plus_create_bulk_invoice' ) ) {
			unset( $settings['sections']['ix_invoices_invoices']['fields']['hd_wc_ie_plus_create_bulk_invoice'] );
		}

		return apply_filters( 'invoicexpress_woocommerce_registered_invoices_settings', $settings );
	}
}
