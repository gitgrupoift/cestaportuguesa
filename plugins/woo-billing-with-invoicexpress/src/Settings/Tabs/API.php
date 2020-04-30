<?php

namespace Webdados\InvoiceXpressWooCommerce\Settings\Tabs;

/**
 * Register API settings.
 *
 * @package InvoiceXpressWooCommerce
 * @since   2.0.0
 */
class API extends \Webdados\InvoiceXpressWooCommerce\Settings\Tabs {

	/**
	 * Retrieve the array of plugin settings.
	 *
	 * @since  2.0.0
	 * @return array
	 */
	public function get_registered_settings() {

		$settings = array(
			'title'    => __( 'API', 'woo-billing-with-invoicexpress' ),
			'sections' => array(
				'ix_licensing_api_api' => array(
					'title'       => __( 'InvoiceXpress API', 'woo-billing-with-invoicexpress' ),
					'description' => sprintf(
										/* translators: %1$s: link tag opening, %2$s: link tag closing */
										__( 'To connect to the InvoiceXpress API you need to get your API details on your %1$sInvoiceXpress%2$s account &gt; Account &gt; API.', 'woo-billing-with-invoicexpress' ),
										'<a href="https://www.app.invoicexpress.com/?token=webdadoslda-1_bb491d" target="_blank">',
										'</a>'
									),
					'fields'      => array(
						'hd_wc_ie_plus_subdomain' => array(
							'title'             => __( 'Subdomain', 'woo-billing-with-invoicexpress' ),
							/* translators: %s: InvoiceXpress account name */
							'description'       => sprintf( __( '%s on InvoiceXpress', 'woo-billing-with-invoicexpress' ), 'ACCOUNT_NAME' ),
							'type'              => 'text',
							'custom_attributes' => array(
								'autocomplete' => 'off',
							),
						),
						'hd_wc_ie_plus_api_token' => array(
							'title'             => __( 'API key', 'woo-billing-with-invoicexpress' ),
							/* translators: %s: InvoiceXpress API key */
							'description'       => sprintf( __( '%s on InvoiceXpress', 'woo-billing-with-invoicexpress' ), 'API_KEY' ),
							'type'              => 'password',
							'custom_attributes' => array(
								'autocomplete' => 'off',
							),
						),
					),
				),
			),
			'top'         => sprintf(
				'<h2>%1$s %2$s</h2>',
				__( 'Get more features!', 'woo-billing-with-invoicexpress' ),
				sprintf(
					/* translators: %s: Pro plugin website link */
					__( 'Buy the PRO plugin at %s and use the "upgrade" coupon to get a discount', 'woo-billing-with-invoicexpress' ),
					'<a href="https://invoicewoo.com" target="_blank">invoicewoo.com</a>'
				)
			),
			'bottom'      => '',
		);

		return apply_filters( 'invoicexpress_woocommerce_registered_api_settings', $settings );
	}
}
