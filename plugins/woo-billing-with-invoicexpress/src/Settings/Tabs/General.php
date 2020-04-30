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
						'hd_wc_ie_plus_document_entity'  => array(
							'title'       => __( 'Documents entity', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'Customer field to use when setting the documents client name on InvoiceXpress', 'woo-billing-with-invoicexpress' ),
							'type'        => 'select',
							'options'     => array(
								'company'  => __( 'Company (if not set defaults to First name + Last name)', 'woo-billing-with-invoicexpress' ),
								'customer' => __( 'First name + Last name', 'woo-billing-with-invoicexpress' ),
							),
						),
					),
				),
			),
		);

		return apply_filters( 'invoicexpress_woocommerce_registered_general_settings', $settings );
	}
}
