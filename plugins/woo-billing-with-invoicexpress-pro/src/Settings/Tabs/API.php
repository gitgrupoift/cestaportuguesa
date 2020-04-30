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

		$licensing_section = array(
			'title'       => __( 'Plugin licensing', 'woo-billing-with-invoicexpress' ),
			'description' => sprintf(
				/* translators: %1$s: link tag opening, %2$s: link tag closing */
				esc_html__( 'To use this plugin you need to get a valid license key %1$shere%2$s.', 'woo-billing-with-invoicexpress' ),
				'<a href="https://invoicewoo.com" target="_blank">',
				'</a>'
			),
			'fields'      => array(
				'hd_wc_ie_plus_license_key' => array(
					'title'             => __( 'License key', 'woo-billing-with-invoicexpress' ),
					'type'              => 'text',
				),
			),
		);

		//We should be checking this with the License verify() method
		if ( get_option( 'hd_wc_ie_plus_license_checksum', '' ) != md5( get_option( 'hd_wc_ie_plus_license_key', '' ) ) ) {
			$licensing_section['fields']['hd_wc_ie_plus_license_key']['description'] = '<span class="ix_error">'.__( 'Not valid', 'woo-billing-with-invoicexpress' ).'</span>';
			return array(
				'title'    => __( 'Licensing and API', 'woo-billing-with-invoicexpress' ),
				'sections' => array(
					'ix_licensing_api_licensing' => $licensing_section,
				),
			);
		} else {
			$licensing_section['fields']['hd_wc_ie_plus_license_key']['description'] = sprintf(
				'%1$s<br/>%2$s',
				//Validation and update checking
				sprintf(
					'<span class="ix_ok">%1$s</span> - <span id="ix_api_version_update_%2$s" data-plugin="%2$s" class="ix_api_version_update">%3$s</span>',
					__( 'Valid', 'woo-billing-with-invoicexpress' ),
					INVOICEXPRESS_WOOCOMMERCE_PRODUCT_ID,
					__( 'Checking for updates', 'woo-billing-with-invoicexpress' )
				),
				//Technical support link
				sprintf(
					/* translators: %1$s: link tag opening, %2$s: link tag closing */
					__( '%1$sClick here for technical support%2$s', 'woo-billing-with-invoicexpress' ),
					sprintf(
						'<a href="https://shop.webdados.com/?support=%s" target="_blank">',
						get_option( 'hd_wc_ie_plus_license_key' )
					),
					'</a>'
				)
			);
		}

		$licensing_section['fields']['hd_wc_ie_plus_license_key']['custom_attributes']['readonly'] = true;

		$licensing_section['fields'] = apply_filters( 'invoicexpress_woocommerce_licensing_fields', $licensing_section['fields'] ); //For extensions

		$settings = array(
			'title'    => __( 'Licensing and API', 'woo-billing-with-invoicexpress' ),
			'sections' => array(
				'ix_licensing_api_licensing' => $licensing_section,
				'ix_licensing_api_api'       => array(
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
		);

		return apply_filters( 'invoicexpress_woocommerce_registered_api_settings', $settings );
	}
}
