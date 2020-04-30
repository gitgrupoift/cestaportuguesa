<?php

namespace Webdados\InvoiceXpressWooCommerce\Settings\Tabs;

/**
 * Register quotes settings.
 *
 * @package InvoiceXpressWooCommerce
 * @since   2.0.0
 */
class Quotes extends \Webdados\InvoiceXpressWooCommerce\Settings\Tabs {

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
			'title'    => __( 'Quotes and Proformas', 'woo-billing-with-invoicexpress' ),
			'sections' => array(
				'ix_quotes_quotes'    => array(
					'title'       => __( 'Quotes', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_quote'            => array(
							'title'  => sprintf(
								/* translators: %s: document type */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'Quote', 'woo-billing-with-invoicexpress' )
							),
							'suffix' => sprintf(
								/* translators: %s: document type */
								__( 'Allow issuing %s', 'woo-billing-with-invoicexpress' ),
								__( 'Quotes', 'woo-billing-with-invoicexpress' )
							),
							'type'   => 'checkbox',
						),
						'hd_wc_ie_plus_send_quote'       => array(
							'title'        => sprintf(
								/* translators: %s: document type */
								__( 'Email %s', 'woo-billing-with-invoicexpress' ),
								__( 'Quote', 'woo-billing-with-invoicexpress' )
							),
							'suffix'       => sprintf(
								/* translators: %s: document type */
								__( 'Send %s to customer by email', 'woo-billing-with-invoicexpress' ),
								__( 'Quote', 'woo-billing-with-invoicexpress' )
							),
							'type'         => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_quote',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_quote_email_subject' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email subject', 'woo-billing-with-invoicexpress' ),
								__( 'Quote', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number} on {site_title}', 'woo-billing-with-invoicexpress' ),
								__( 'Quote', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_quote',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_quote_email_heading' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email heading', 'woo-billing-with-invoicexpress' ),
								__( 'Quote', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number}', 'woo-billing-with-invoicexpress' ),
								__( 'Quote', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_quote',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_quote_email_body' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email body', 'woo-billing-with-invoicexpress' ),
								__( 'Quote', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'textarea',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( 'Hi {customer_name},

Please find attached your %s for order #{order_number} from {order_date} on {site_title}.

Thank you.', 'woo-billing-with-invoicexpress' ),
								__( 'Quote', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'custom_attributes'      => array(
								'rows' => 8,
							),
							'parent_field'           => 'hd_wc_ie_plus_send_quote',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
					),
				),
				'ix_quotes_proformas' => array(
					'title'       => __( 'Proformas', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_create_proforma' => array(
							'title'  => sprintf(
								/* translators: %s: document type */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'Proforma', 'woo-billing-with-invoicexpress' )
							),
							'suffix' => sprintf(
								/* translators: %s: document type */
								__( 'Allow issuing %s', 'woo-billing-with-invoicexpress' ),
								__( 'Proformas', 'woo-billing-with-invoicexpress' )
							),
							'type'   => 'checkbox',
						),
						'hd_wc_ie_plus_send_proforma'   => array(
							'title'        => sprintf(
								/* translators: %s: document type */
								__( 'Email %s', 'woo-billing-with-invoicexpress' ),
								__( 'Proforma', 'woo-billing-with-invoicexpress' )
							),
							'suffix'       => sprintf(
								/* translators: %s: document type */
								__( 'Send %s to customer by email', 'woo-billing-with-invoicexpress' ),
								__( 'Proforma', 'woo-billing-with-invoicexpress' )
							),
							'type'         => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_create_proforma',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_proforma_email_subject' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email subject', 'woo-billing-with-invoicexpress' ),
								__( 'Proforma', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number} on {site_title}', 'woo-billing-with-invoicexpress' ),
								__( 'Proforma', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_proforma',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_proforma_email_heading' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email heading', 'woo-billing-with-invoicexpress' ),
								__( 'Proforma', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number}', 'woo-billing-with-invoicexpress' ),
								__( 'Proforma', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_proforma',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_proforma_email_body' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email body', 'woo-billing-with-invoicexpress' ),
								__( 'Proforma', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'textarea',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( 'Hi {customer_name},

Please find attached your %s for order #{order_number} from {order_date} on {site_title}.

Thank you.', 'woo-billing-with-invoicexpress' ),
								__( 'Proforma', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'custom_attributes'      => array(
								'rows' => 8,
							),
							'parent_field'           => 'hd_wc_ie_plus_send_proforma',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
					),
				),
			),
		);

		return apply_filters( 'invoicexpress_woocommerce_registered_quotes_settings', $settings );
	}
}
