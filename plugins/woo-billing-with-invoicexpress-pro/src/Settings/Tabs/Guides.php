<?php

namespace Webdados\InvoiceXpressWooCommerce\Settings\Tabs;

/**
 * Register guides settings.
 *
 * @package InvoiceXpressWooCommerce
 * @since   2.0.0
 */
class Guides extends \Webdados\InvoiceXpressWooCommerce\Settings\Tabs {

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
			'title'    => __( 'Delivery and Return guides', 'woo-billing-with-invoicexpress' ),
			'sections' => array(
				'ix_guides_transport'  => array(
					'title'       => __( 'Delivery notes', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_create_transport_guide' => array(
							'title'  => sprintf(
								/* translators: %s: document type */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery note', 'woo-billing-with-invoicexpress' )
							),
							'suffix' => sprintf(
								/* translators: %s: document type */
								__( 'Allow issuing %s', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery notes', 'woo-billing-with-invoicexpress' )
							),
							'type'   => 'checkbox',
						),

						'hd_wc_ie_plus_send_transport_guide'       => array(
							'title'        => sprintf(
								/* translators: %s: document type */
								__( 'Email %s', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery note', 'woo-billing-with-invoicexpress' )
							),
							'suffix'       => sprintf(
								/* translators: %s: document type */
								__( 'Send %s to a custom email', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery note', 'woo-billing-with-invoicexpress' )
							),
							'type'         => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_create_transport_guide',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_transport_guide_email_address' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email address', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery note', 'woo-billing-with-invoicexpress' )
							),
							'description'            => sprintf(
								/* translators: %s: document type */
								__( 'Set the email address to sent the %s to (normally you, the warehouse or the dropshipping supplier)', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery note', 'woo-billing-with-invoicexpress' )
							),
							'type'                   => 'text',
							'placeholder'            => get_option( 'admin_email' ),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_transport_guide',
							'parent_value'           => '1',
						),
						'hd_wc_ie_plus_transport_guide_email_subject' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email subject', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery note', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number} on {site_title}', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery note', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_transport_guide',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_transport_guide_email_heading' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email heading', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery note', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'text',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( '%s for order #{order_number}', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery note', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'parent_field'           => 'hd_wc_ie_plus_send_transport_guide',
							'parent_value'           => '1',
							'wpml'                   => true,
						),
						'hd_wc_ie_plus_transport_guide_email_body' => array(
							'title'                  => sprintf(
								/* translators: %s: document type */
								__( '%s email body', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery note', 'woo-billing-with-invoicexpress' )
							),
							'description'            => $this->get_settings()->get_email_fields_info(),
							'type'                   => 'textarea',
							'placeholder'            => sprintf(
								/* translators: %s: document type */
								__( 'Hi,

Please find attached the %s for order #{order_number}, from the customer {customer_name}, from {order_date} on {site_title}.

Thank you.', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery note', 'woo-billing-with-invoicexpress' )
							),
							'placeholder_as_default' => true,
							'custom_attributes'      => array(
								'rows' => 8,
							),
							'parent_field'           => 'hd_wc_ie_plus_send_transport_guide',
							'parent_value'           => '1',
							'wpml'                   => true,
						),

						'hd_wc_ie_plus_default_licence_plate' => array(
							'title'             => __( 'License plate', 'woo-billing-with-invoicexpress' ),
							'description'       => __( 'Default vehicle registration plate, if applicable', 'woo-billing-with-invoicexpress' ),
							'type'              => 'text',
							'placeholder'       => '00-XX-00',
							'custom_attributes' => array(
								'size'      => 12,
								'maxlength' => 10,
							),
							'parent_field'      => 'hd_wc_ie_plus_create_transport_guide',
							'parent_value'      => '1',
						),
						'hd_wc_ie_plus_warehouse_address' => array(
							'title'             => __( 'Load site address', 'woo-billing-with-invoicexpress' ),
							'description'       => __( 'Address of the warehouse from which goods are dispatched', 'woo-billing-with-invoicexpress' ),
							'type'              => 'text',
							'placeholder'       => 'Street name, house number, ...',
							'parent_field'      => 'hd_wc_ie_plus_create_transport_guide',
							'parent_value'      => '1',
						),
						'hd_wc_ie_plus_warehouse_post_code' => array(
							'title'             => __( 'Load site postcode', 'woo-billing-with-invoicexpress' ),
							'description'       => __( 'Postcode of the warehouse from which goods are dispatched', 'woo-billing-with-invoicexpress' ),
							'type'              => 'text',
							'placeholder'       => '0000-000',
							'custom_attributes' => array(
								'size'      => 12,
								'maxlength' => 10,
							),
							'parent_field'      => 'hd_wc_ie_plus_create_transport_guide',
							'parent_value'      => '1',
						),
						'hd_wc_ie_plus_warehouse_city'    => array(
							'title'             => __( 'Load site city', 'woo-billing-with-invoicexpress' ),
							'description'       => __( 'City of the warehouse from which goods are dispatched', 'woo-billing-with-invoicexpress' ),
							'type'              => 'text',
							'placeholder'       => 'Usually the postcode city',
							'custom_attributes' => array(
								'size'      => 30,
								'maxlength' => 30,
							),
							'parent_field'      => 'hd_wc_ie_plus_create_transport_guide',
							'parent_value'      => '1',
						),
						'hd_wc_ie_plus_warehouse_country' => array(
							'title'        => __( 'Load site country', 'woo-billing-with-invoicexpress' ),
							'description'  => __( 'Country of the warehouse from which goods are dispatched', 'woo-billing-with-invoicexpress' ),
							'type'         => 'select_ix_countries',
							'parent_field' => 'hd_wc_ie_plus_create_transport_guide',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_cancellation_reason_transport_guides' => array(
							'title'             => __( 'Cancellation motive', 'woo-billing-with-invoicexpress' ),
							'description'       => __( 'Default cancellation motive for Delivery note', 'woo-billing-with-invoicexpress' ),
							'type'              => 'text',
							'parent_field'      => 'hd_wc_ie_plus_create_transport_guide',
							'parent_value'      => '1',
							'required_field'    => 'hd_wc_ie_plus_cancel_documents',
							'required_value'    => '1',
							'wpml'              => true,
						),
						'hd_wc_ie_plus_guide_get_at_code' => array(
							'title'        => __( 'Get AT Code', 'woo-billing-with-invoicexpress' ),
							'description'  => sprintf(
								'%s<br/>%s',
								__( 'Will result in an error on trial accounts', 'woo-billing-with-invoicexpress' ),
								__( 'Do not enable this option unless you absolutely need AT Code for some integration with 3rd party software', 'woo-billing-with-invoicexpress' )
							),
							'suffix'       => sprintf(
								/* translators: %s: document type */
								__( 'Save the %s AT Code into a custom field', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery note', 'woo-billing-with-invoicexpress' )
							),
							'type'         => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_create_transport_guide',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_automatic_transport_guide' => array(
							'title'        => __( 'Automatic issuing', 'woo-billing-with-invoicexpress' ),
							'suffix'       => sprintf(
								/* translators: %s: document type */
								__( 'Issue %s automatically', 'woo-billing-with-invoicexpress' ),
								__( 'Delivery note', 'woo-billing-with-invoicexpress' )
							),
							'type'         => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_create_transport_guide',
							'parent_value' => '1',
							'description'  => apply_filters( 'invoicexpress_woocommerce_delay_automatic_guide', false )
												?
												sprintf(
													/* translators: %1$s: time, %2$s: number of documents */
													__( 'Delayed %1$s via developer hooks - Currently %2$s documents scheduled' , 'woo-billing-with-invoicexpress' ),
													'<code>'.apply_filters( 'invoicexpress_woocommerce_delay_automatic_document_time_readable', apply_filters( 'invoicexpress_woocommerce_delay_automatic_guide_time', 'T2M' ) ).'</code>',
													'<code id="pending_scheduled_guide_documents">0</code>'
												)
												.
												'<br/>'
												:
												'',
						),
						'hd_wc_ie_plus_automatic_guide_prevent_exempt' => array(
							'title'  => __( 'Not for tax exempt orders', 'woo-billing-with-invoicexpress' ),
							'suffix' => __( 'Do not issue the automatic document for orders that have no VAT', 'woo-billing-with-invoicexpress' ),
							'description'  => __( 'Useful if you have several tax exemption motives and want to check if the correct one is assigned before issuing the document', 'woo-billing-with-invoicexpress' ),
							'type'   => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_automatic_transport_guide',
							'parent_value' => '1',
						),
						'hd_wc_ie_plus_automatic_guide_state' => array(
							'title'        => __( 'Order status', 'woo-billing-with-invoicexpress' ),
							'description'  => __( 'Order status that will trigger the Delivery note automatic issue', 'woo-billing-with-invoicexpress' ),
							'type'         => 'select_order_status',
							'parent_field' => 'hd_wc_ie_plus_automatic_transport_guide',
							'parent_value' => '1',
						),
					),
				),
				'ix_guides_devolution' => array(
					'title'       => __( 'Return delivery notes', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_devolution_guide' => array(
							'title'  => sprintf(
								/* translators: %s: document type */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'Return delivery note', 'woo-billing-with-invoicexpress' )
							),
							'suffix' => sprintf(
								/* translators: %s: document type */
								__( 'Allow issuing %s', 'woo-billing-with-invoicexpress' ),
								__( 'Return delivery notes', 'woo-billing-with-invoicexpress' )
							),
							'type'   => 'checkbox',
						),
						'hd_wc_ie_plus_cancellation_reason_devolution_guides' => array(
							'title'          => __( 'Cancellation motive', 'woo-billing-with-invoicexpress' ),
							'description'    => __( 'Default cancellation motive for Return delivery note', 'woo-billing-with-invoicexpress' ),
							'type'           => 'text',
							'parent_field'   => 'hd_wc_ie_plus_devolution_guide',
							'parent_value'   => '1',
							'required_field' => 'hd_wc_ie_plus_cancel_documents',
							'required_value' => '1',
							'wpml'                   => true,
						),
					),
				),
				'ix_guides_misc'       => array(
					'title'       => __( 'General guides settings', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_transport_guide_no_value' => array(
							'title'  => __( 'Guides without prices', 'woo-billing-with-invoicexpress' ),
							'suffix' => __( 'Issue guides without prices', 'woo-billing-with-invoicexpress' ),
							'type'   => 'checkbox',
						),
					),
				),
			),
		);

		return apply_filters( 'invoicexpress_woocommerce_registered_guides_settings', $settings );
	}
}
