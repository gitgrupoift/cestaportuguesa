<?php

namespace Webdados\InvoiceXpressWooCommerce\Settings\Tabs;
use Webdados\InvoiceXpressWooCommerce\Modules\Vat\VatController as VatController;

/**
 * Register taxes settings.
 *
 * @package InvoiceXpressWooCommerce
 * @since   2.0.0
 */
class Taxes extends \Webdados\InvoiceXpressWooCommerce\Settings\Tabs {

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

		$exemption_reasons = VatController::get_exemption_reasons();

		$settings = array(
			'title'    => __( 'Taxes', 'woo-billing-with-invoicexpress' ),
			'sections' => array(
				'ix_taxes_misc'      => array(
					'title'       => __( 'General taxes settings', 'woo-billing-with-invoicexpress' ),
					'description' => sprintf(
						/* translators: %1$s: link tag opening, %2$s: plugin name, %3$s: link tag closing, %3$s: line break, %4$s: link for documentation */
						__( 'Before using the plugin, you have to make sure your %1$sWooCommerce taxes%2$s are properly configured.%3$s%4$s', 'woo-billing-with-invoicexpress' ),
						'<a href="admin.php?page=wc-settings&tab=tax">',
						'</a>',
						'<br/>',
						sprintf(
							'<a href="%s" target="_blank">%s</a>.',
							esc_html_x( 'https://invoicewoo.com/documentation/installation-guide/setting-up-woocommerce-taxes/', 'Documentation URL (Installation guide, Setting up WooCommerce taxes)', 'woo-billing-with-invoicexpress' ),
							esc_html__( 'Check the documentation', 'woo-billing-with-invoicexpress' )
						)
					),
					'fields'      => array(
						'hd_wc_ie_plus_tax_country' => array(
							'title'       => __( 'Portuguese company', 'woo-billing-with-invoicexpress' ),
							'suffix'      => __( 'This is a store of a Portuguese company', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'Check only if you have a Portuguese VAT number and your business is a company (or similar VAT passive subject)', 'woo-billing-with-invoicexpress' ),
							'type'        => 'checkbox',
						),
						'hd_wc_ie_plus_default_tax' => array(
							'title'       => __( 'Default tax', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'Tax to use, by default, when generating documents (this will also change your default tax on InvoiceXpress)', 'woo-billing-with-invoicexpress' ),
							'type'        => 'select_ix_tax',
						),
					),
				),
				'ix_taxes_vat_field' => array(
					'title'       => __( 'VAT field', 'woo-billing-with-invoicexpress' ),
					'description' => sprintf(
						/* translators: %1$s: link tag opening, %2$s: plugin name, %3$s: link tag closing, %4$s: new line, %5$s: current status */
						__( 'If you install and correctly configure the free %1$s%2$s%3$s plugin by Aelia, its VAT field is used instead of ours.<br/>There\'s no need to do use it unless you need to exempt VAT on B2B transactions inside the EU.%4$s%5$s', 'woo-billing-with-invoicexpress' ),
						'<a href="https://wordpress.org/plugins/woocommerce-eu-vat-assistant/" target="_blank">',
						__( 'WooCommerce EU VAT Assistant', 'woo-billing-with-invoicexpress' ),
						'</a>',
						'<br/>',
						sprintf(
							/* translators: %s: status (enabled or not enabled) */
							__( 'Current status: %s', 'woo-billing-with-invoicexpress' ),
							$this->get_settings()->get_aelia_eu_vat_assistant()
						)
					)
					.
					'<br/><br/>'.
					__( 'or', 'woo-billing-with-invoicexpress' )
					.'<br/><br/>'.
					sprintf(
						/* translators: %1$s: link tag opening, %2$s: plugin name, %3$s: link tag closing, %4$s: new line, %5$s: current status */
						__( '(<strong>Experimental</strong>) If you install and correctly configure the %1$s%2$s%3$s plugin by WooCommerce, its VAT field is used instead of ours.<br/>There\'s no need to do use it unless you need to exempt VAT on B2B transactions inside the EU.%4$s%5$s', 'woo-billing-with-invoicexpress' ),
						'<a href="https://woocommerce.com/products/eu-vat-number/" target="_blank">',
						__( 'EU VAT Number', 'woo-billing-with-invoicexpress' ),
						'</a>',
						'<br/>',
						sprintf(
							/* translators: %s: status (enabled or not enabled) */
							__( 'Current status: %s', 'woo-billing-with-invoicexpress' ),
							$this->get_settings()->get_woocommerce_eu_vat_field()
						)
					),
					'fields'      => array(
						'hd_wc_ie_plus_vat_field' => array(
							'title'       => __( 'VAT field', 'woo-billing-with-invoicexpress' ),
							'suffix'      => __( 'VAT field on the checkout', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'Include our own VAT field on the checkout or use the value from WooCommerce EU VAT Assistant by Aelia or EU VAT Field by WooCommerce', 'woo-billing-with-invoicexpress' ),
							'type'        => 'checkbox',
						),
						'hd_wc_ie_plus_vat_field_mandatory' => array(
							'title'        => __( 'Mandatory VAT field', 'woo-billing-with-invoicexpress' ),
							'suffix'       => __( 'Make the VAT field mandatory', 'woo-billing-with-invoicexpress' ),
							'type'         => 'checkbox',
							'parent_field' => 'hd_wc_ie_plus_vat_field',
							'parent_value' => '1',
						),
					),
				),
				'ix_taxes_exemption' => array(
					'title'       => __( 'Tax exemption', 'woo-billing-with-invoicexpress' ),
					'description' => sprintf(
						/* translators: %1$s: link tag opening, %2$s: plugin name, %3$s: link tag closing, %4$s: link tag opening, %5$s: plugin name, %6$s: link tag closing */
						__( 'On B2B transactions inside the EU, the exemption (M16 Artigo 14.º do RITI or M08 Artigo 6.º do CIVA) will only be applied if you install and correctly configure the free %1$s%2$s%3$s plugin by Aelia or the %4$s%5$s%6$s plugin by WooCommerce (experimental support).<br/>This is currently only supported for sellers that are Portuguese companies.<br/>You only need one of the plugins and only if you do B2B transactions inside the EU.', 'woo-billing-with-invoicexpress' ),
						'<a href="https://wordpress.org/plugins/woocommerce-eu-vat-assistant/" target="_blank">',
						__( 'WooCommerce EU VAT Assistant', 'woo-billing-with-invoicexpress' ),
						'</a>',
						'<a href="https://woocommerce.com/products/eu-vat-number/" target="_blank">',
						__( 'EU VAT Field', 'woo-billing-with-invoicexpress' ),
						'</a>'
					),
					'fields'      => array(
						'hd_wc_ie_plus_exemption_name'   => array(
							'title'       => __( 'Tax exemption name', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'This should be the 0% tax name defined on your InvoiceXpress account', 'woo-billing-with-invoicexpress' ),
							'type'        => 'text',
						),
						'hd_wc_ie_plus_exemption_reason' => array(
							'title'       => __( 'Tax exemption motive', 'woo-billing-with-invoicexpress' ),
							'description' => __( 'You should set a Tax exemption motive if your business is exempt from taxes', 'woo-billing-with-invoicexpress' ).(
								$this->plugin->aelia_eu_vat_assistant_active || $this->plugin->woocommerce_eu_vat_field_active
								?
								sprintf(
									' (%s)',
									__( 'not applicable for B2B within the EU, which will be M16 or M08 automatically', 'woo-billing-with-invoicexpress' )
								)
								:
								''
							),
							'type'        => 'select',
							'options'     => array_merge(
								array(
									'' => __( 'No exemption applicable', 'woo-billing-with-invoicexpress' )
								),
								$exemption_reasons
							),
							'parent_field' => 'hd_wc_ie_plus_tax_country',
							'parent_value' => '1',
						),
					),
				),
			),
		);

		if ( $this->plugin->aelia_eu_vat_assistant_active || $this->plugin->woocommerce_eu_vat_field_active ) {
			//$exemption_reasons
			$settings['sections']['ix_taxes_exemption']['fields']['hd_wc_ie_plus_exemption_reason_eu_b2b'] = array(
				'title'       => __( 'Tax exemption for EU B2B', 'woo-billing-with-invoicexpress' ),
				'description' => __( 'The exemption motive to use on B2B transactions inside the EU', 'woo-billing-with-invoicexpress' ),
				'type'        => 'select',
				'options'     => array(
					'M16' => $exemption_reasons['M16'].' ('.__( 'normally for products', 'woo-billing-with-invoicexpress' ).')',
					'M08' => $exemption_reasons['M08'].' ('.__( 'normally for services', 'woo-billing-with-invoicexpress' ).')',
				),
				'parent_field' => 'hd_wc_ie_plus_tax_country',
				'parent_value' => '1',
			);
		}

		return apply_filters( 'invoicexpress_woocommerce_registered_taxes_settings', $settings );
	}
}
