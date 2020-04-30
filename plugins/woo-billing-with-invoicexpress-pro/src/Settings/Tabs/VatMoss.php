<?php

namespace Webdados\InvoiceXpressWooCommerce\Settings\Tabs;

/**
 * Register VAT MOSS settings.
 *
 * @package InvoiceXpressWooCommerce
 * @since   2.6.0
 */
class VatMoss extends \Webdados\InvoiceXpressWooCommerce\Settings\Tabs {

	/**
	 * Retrieve the array of plugin settings.
	 *
	 * @since  2.6.0
	 * @return array
	 */
	public function get_registered_settings() {

		if ( ! $this->settings->check_requirements() ) {
			return;
		}

		$settings = array(
			'title'    => __( 'VAT MOSS', 'woo-billing-with-invoicexpress' ).' β',
			'sections' => array(
				'ix_vat_moss_invoices'            => array(
					'title'       => __( 'VAT MOSS', 'woo-billing-with-invoicexpress' ).' (beta)',
					'description' => implode(
						'<br/>',
						array(
							'- '.__( 'VAT MOSS invoices support is experimental and we do not currently provide technical support on this feature.', 'woo-billing-with-invoicexpress' ),
							'- '.sprintf(
								__( 'DO NOT activate this option before talking to your accountant and %1$schecking the documentation%2$s.', 'woo-billing-with-invoicexpress' ),
								sprintf(
									'<a href="%s" target="_blank">',
									/* translators: %s: VAT MOSS documentation link */
									__( 'https://invoicewoo.com/documentation/settings/vat-moss/', 'woo-billing-with-invoicexpress' )
								),
								'</a>'
							),
							'- '.__( 'VAT MOSS invoices should only be issued for digital products sold to end consumers in the EU (except those in your country).', 'woo-billing-with-invoicexpress' ),
							'- '.__( 'You are responsible for setting the correct taxes on both InvoiceXpress and WooCommerce.', 'woo-billing-with-invoicexpress' ),
						)
					),
					'fields'      => array(
						'hd_wc_ie_plus_create_vat_moss_invoice' => array(
							'title'  => sprintf(
								/* translators: %s: document type */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
							),
							'suffix' => sprintf(
								/* translators: %s: document type */
								__( 'Allow issuing %s', 'woo-billing-with-invoicexpress' ),
								__( 'VAT MOSS invoices', 'woo-billing-with-invoicexpress' )
							),
							'description' => __( 'Manually or automatically (if set on the invoices settings tab)', 'woo-billing-with-invoicexpress' ),
							'type'   => 'checkbox',
						),
						'hd_wc_ie_plus_vat_moss_sequence' => array(
							'title'       => __( 'VAT MOSS sequence', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'Sequence to use when generating VAT MOSS invoices (exclusive for this purpose)', 'woo-billing-with-invoicexpress' ),
							'type'        => 'select_ix_sequence',
							'parent_field' => 'hd_wc_ie_plus_create_vat_moss_invoice',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_send_vat_moss_invoice' => array(
							'title'        => sprintf(
								/* translators: %s: document type */
								__( 'Email %s', 'woo-billing-with-invoicexpress' ),
								__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
							),
							'suffix'       => sprintf(
								/* translators: %s: document type */
								__( 'Send %s to customer by email', 'woo-billing-with-invoicexpress' ),
								__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
							),
							'type'         => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_create_vat_moss_invoice',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_vat_moss_invoice_email_subject' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email subject', 'woo-billing-with-invoicexpress' ),
								__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number} on {site_title}', 'woo-billing-with-invoicexpress' ),
								__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_vat_moss_invoice',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_vat_moss_invoice_email_heading' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email heading', 'woo-billing-with-invoicexpress' ),
								__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number}', 'woo-billing-with-invoicexpress' ),
								__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_vat_moss_invoice',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_vat_moss_invoice_email_body' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email body', 'woo-billing-with-invoicexpress' ),
								__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'textarea',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( 'Hi {customer_name},

Please find attached your %s for order #{order_number} from {order_date} on {site_title}.

Thank you.', 'woo-billing-with-invoicexpress' ),
								__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'custom_attributes'      => array(
								'rows' => 8,
							),
							'parent_field'           => 'hd_wc_ie_plus_send_vat_moss_invoice',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
					),
				
				),
			),
		);

		return apply_filters( 'invoicexpress_woocommerce_registered_vat_moss_settings', $settings );
	}
}
