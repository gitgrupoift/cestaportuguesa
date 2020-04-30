<?php

namespace Webdados\InvoiceXpressWooCommerce\Settings\Tabs;

/**
 * Register general settings.
 *
 * @package InvoiceXpressWooCommerce
 * @since   2.0.0
 */
class General extends \Webdados\InvoiceXpressWooCommerce\Settings\Tabs {

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
			'title'    => __( 'General', 'woo-billing-with-invoicexpress' ),
			'sections' => array(
				'ix_general_sequences' => array(
					'title'       => __( 'Document sequences', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_invoice_sequence_default' => array(
							'title'       => __( 'Default sequence', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'Sequence to use, by default, when generating documents', 'woo-billing-with-invoicexpress' ),
							'type'        => 'select_ix_sequence',
						),
						'hd_wc_ie_plus_invoice_sequence' => array(
							'title'  => __( 'Sequence selection', 'woo-billing-with-invoicexpress' ),
							'suffix' => __( 'Allow sequence selection when generating documents, overriding the default chosen above', 'woo-billing-with-invoicexpress' ),
							'type'   => 'checkbox',
						),
					),
				),
				'ix_general_email'      => array(
					'title'       => __( 'Email and PDF files', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_email_method'        => array(
							'title'             => __( 'PDF file method', 'woo-billing-with-invoicexpress' ),
							'type'        => 'select',
							'options'     => array(
								''       => __( 'WordPress / WooCommerce', 'woo-billing-with-invoicexpress' ),
								'ix'     => __( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ).' ('.__( 'Experimental', 'woo-billing-with-invoicexpress' ).')',
								'hybrid' => __( 'Hybrid', 'woo-billing-with-invoicexpress' ).' ('.__( 'Experimental', 'woo-billing-with-invoicexpress' ).')', //To be default in some versions
							),
							'description'       => sprintf(
								'- %1$s<br/>- %2$s<br/>- %3$s',
								__( 'WordPress / WooCommerce: the PDF is downloaded to your web server and then sent to the client, attached, using the WooCommerce email templates and the WordPress email delivery system (slower, stores the PDF locally)', 'woo-billing-with-invoicexpress' ),
								__( 'InvoiceXpress: the PDF is not downloaded to your web server and is sent to the client, attached, using the InvoiceXpress email templates and delivery system (faster, does not store the PDF locally)', 'woo-billing-with-invoicexpress' ),
								__( 'Hybrid: the PDF is not downloaded to your web server and is sent to the client, as a link, using the WooCommerce email templates and the WordPress email delivery system (faster, does not store the PDF locally)', 'woo-billing-with-invoicexpress' )
							),
						),
						'hd_wc_ie_plus_ix_email_logo'   => array(
							'title'       => __( 'Email logo', 'woo-billing-with-invoicexpress' ),
							'suffix'      => __( 'Include your InvoiceXpress personalized logo on the email', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'Will result in an error on trial accounts', 'woo-billing-with-invoicexpress' ),
							'type'        => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_email_method',
							'parent_value' => 'ix',
						),
						'hd_wc_ie_plus_email_bcc'        => array(
							'title'             => __( 'Bcc all emails to', 'woo-billing-with-invoicexpress' ),
							'type'              => 'email',
							'description'       => __( 'Type an email address to receive all invoicing documents on Bcc', 'woo-billing-with-invoicexpress' ),
						),
					),
				),
				'ix_general_misc'      => array(
					'title'       => __( 'Miscellaneous', 'woo-billing-with-invoicexpress' ),
					'description' => '',
					'fields'      => array(
						'hd_wc_ie_plus_product_code'     => array(
							'title'       => __( 'Product code', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'Product field to use when generating the product code on InvoiceXpress', 'woo-billing-with-invoicexpress' ),
							'type'        => 'select',
							'options'     => array(
								'sku' => __( 'SKU (if set, defaults to ID)', 'woo-billing-with-invoicexpress' ),
								'id'  => __( 'ID', 'woo-billing-with-invoicexpress' ),
							),
						),
						'hd_wc_ie_plus_product_unit'     => array(
							'title'       => __( 'Default product unit', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'What does your store sell? This is important for SAF-T reports.', 'woo-billing-with-invoicexpress' ),
							'type'        => 'select',
							'options'     => array(
								'unit'     => __( 'Product', 'woo-billing-with-invoicexpress' ),
								'service'  => __( 'Service', 'woo-billing-with-invoicexpress' ),
							),
						),
						'hd_wc_ie_plus_virtual_product_unit'     => array(
							'title'       => __( 'Virtual product unit', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'Product unit for virtual products', 'woo-billing-with-invoicexpress' ),
							'type'        => 'select',
							'options'     => array(
								'service'  => __( 'Service', 'woo-billing-with-invoicexpress' ).' ('.__( 'Recommended', 'woo-billing-with-invoicexpress' ).')',
								'unit'     => __( 'Product', 'woo-billing-with-invoicexpress' ),
							),
						),
						'hd_wc_ie_plus_document_entity'  => array(
							'title'       => __( 'Documents entity', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'Customer field to use when setting the documents client name on InvoiceXpress', 'woo-billing-with-invoicexpress' ),
							'type'        => 'select',
							'options'     => array(
								'company'  => __( 'Company (if not set defaults to First name + Last name)', 'woo-billing-with-invoicexpress' ),
								'customer' => __( 'First name + Last name', 'woo-billing-with-invoicexpress' ),
							),
						),
						'hd_wc_ie_plus_document_language'  => array(
							'title'       => __( 'Documents language', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'The language used to issue documents on InvoiceXpress', 'woo-billing-with-invoicexpress' ),
							'type'        => 'select',
							'options'     => array(
								'pt'  => __( 'Portuguese', 'woo-billing-with-invoicexpress' ),
								'en'  => __( 'English', 'woo-billing-with-invoicexpress' ),
								'es'  => __( 'Spanish', 'woo-billing-with-invoicexpress' ),
							),
						),
						'hd_wc_ie_plus_cancel_documents' => array(
							'title'       => __( 'Document cancelation', 'woo-billing-with-invoicexpress' ),
							'suffix'      => __( 'Allow cancelation of last document', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'This feature allows you to cancel the last document created. This is only valid for documents in the "final" state. Already paid invoices must be refunded in order to cancel the document. You must set cancelation motive for Invoices and Guides on the corresponding settings tab.', 'woo-billing-with-invoicexpress' ),
							'type'        => 'checkbox',
						),
						'hd_wc_ie_plus_leave_as_draft'   => array(
							'title'       => __( 'Leave documents as Draft', 'woo-billing-with-invoicexpress' ),
							'suffix'      => __( 'Instead of finishing documents, leave them as Draft', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'Not recommended', 'woo-billing-with-invoicexpress' ) . ' - ' . __( 'You\'ll have to finish the documents on InvoiceXpress and options like setting documents as paid or sending them via email will not work. Documents will also not be available to download directly on the website.', 'woo-billing-with-invoicexpress' ),
							'type'        => 'checkbox',
						),
						'hd_wc_ie_plus_automatic_email_errors' => array(
							'title'        => __( 'Automatic document errors', 'woo-billing-with-invoicexpress' ),
							/* translators: admin email */
							'suffix'       => sprintf( __( 'Send automatic documents errors to %s', 'woo-billing-with-invoicexpress' ), get_option( 'admin_email' ) ),
							'description'  => __( 'If any kind of automatic issuing of documents is activated', 'woo-billing-with-invoicexpress' ),
							'type'         => 'checkbox',
						),
						'hd_wc_ie_plus_update_order_status' => array(
							'title'       => __( 'Update order status', 'woo-billing-with-invoicexpress' ),
							'suffix'      => __( 'Change order status when issuing documents', 'woo-billing-with-invoicexpress' ),
							'description'  => sprintf(
								'%s<br/><strong>%s</strong>',
									__( 'Not recommended', 'woo-billing-with-invoicexpress' ) . ' - ' . sprintf(
									'<a href="%s" target="_blank">%s</a>',
									esc_html_x( 'https://invoicewoo.com/documentation/settings/general/#miscellaneous_update_order_status', 'Documentation URL (Settings, General, Update order status)', 'woo-billing-with-invoicexpress' ),
									esc_html__( 'Check the documentation', 'woo-billing-with-invoicexpress' )
								),
								__( 'This feature will be discontinued soon and if you disable it you will not be able to enable it again', 'woo-billing-with-invoicexpress' )
							),
							'type'        => 'checkbox',
						),
						'hd_wc_ie_plus_prevent_issue_unknown_coupons'   => array(
							'title'       => __( 'Prevent issuing with non-standard coupons', 'woo-billing-with-invoicexpress' ),
							'suffix'      => __( 'Prevent issuing any document if the order contains non-standard WooCommerce coupon types', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'Recommended if you use coupons that create a discount on the order total, like gift certificates, which can cause documents to end up with incorrect values', 'woo-billing-with-invoicexpress' ),
							'type'        => 'checkbox',
							'default'     => '1',
						),
						'hd_wc_ie_plus_show_documents_my_account'   => array(
							'title'       => __( 'Documents on My Account', 'woo-billing-with-invoicexpress' ),
							'suffix'      => __( 'List billing documents on My Account &gt; Orders', 'woo-billing-with-invoicexpress' ),
							'type'        => 'checkbox',
							'default'     => '1',
						),
					),
				),
			),
		);

		/* 2.4.6 - discontinue Update order status */
		if ( ! get_option( 'hd_wc_ie_plus_update_order_status' ) ) {
			unset( $settings['sections']['ix_general_misc']['fields']['hd_wc_ie_plus_update_order_status'] );
		}

		return apply_filters( 'invoicexpress_woocommerce_registered_general_settings', $settings );
	}
}
