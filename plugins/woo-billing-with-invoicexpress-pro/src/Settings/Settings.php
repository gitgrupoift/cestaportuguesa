<?php

namespace Webdados\InvoiceXpressWooCommerce\Settings;

use Webdados\InvoiceXpressWooCommerce\Plugin;

/**
 * Register settings.
 *
 * @package InvoiceXpressWooCommerce
 * @since   2.0.0
 */
class Settings extends \Webdados\InvoiceXpressWooCommerce\BaseSettings {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.0.4 Add plugin instance parameter.
	 * @since 2.0.0
	 * @param Plugin $plugin This plugin's instance.
	 */
	public function __construct( Plugin $plugin ) {
		parent::__construct( $plugin );

		add_action( 'wp_ajax_hd_invoicexpress_check_update_version', array( $this, 'version_update_information' ) );
	}

	/**
	 * Get Aelia EU VAT Assistant status.
	 *
	 * @return string
	 */
	public function get_aelia_eu_vat_assistant() {
		$aelia_eu_vat_assistant = sprintf(
			'<span class="ix_error">%s</span>',
			__( 'Not installed or not active.', 'woo-billing-with-invoicexpress' )
		);
		if ( $this->plugin->aelia_eu_vat_assistant_active ) {
			$aelia_eu_vat_assistant = sprintf(
				'<span class="ix_ok">%s</span>, <a href="?page=wc_aelia_eu_vat_assistant" target="_blank">%s</a>.',
				__( 'Installed and active', 'woo-billing-with-invoicexpress' ),
				__( 'go to settings', 'woo-billing-with-invoicexpress' )
			);
		}
		return $aelia_eu_vat_assistant;
	}

	/**
	 * Get WooCommerce EU VAT Field status.
	 *
	 * @return string
	 */
	public function get_woocommerce_eu_vat_field() {
		$woocommerce_eu_vat_field = sprintf(
			'<span class="ix_error">%s</span>',
			__( 'Not installed or not active.', 'woo-billing-with-invoicexpress' )
		);
		if ( $this->plugin->woocommerce_eu_vat_field_active ) {
			$woocommerce_eu_vat_field = sprintf(
				'<span class="ix_ok">%s</span>, <a href="?page=wc-settings&tab=tax" target="_blank">%s</a>.',
				__( 'Installed and active', 'woo-billing-with-invoicexpress' ),
				__( 'go to settings', 'woo-billing-with-invoicexpress' )
			);
		}
		return $woocommerce_eu_vat_field;
	}

	/**
	 * Checks if required settings are satisfied.
	 *
	 * @return bool
	 */
	public function check_requirements() {
		return $this->plugin->license->verify()
			&& ! empty( get_option( 'hd_wc_ie_plus_subdomain', '' ) )
			&& ! empty( get_option( 'hd_wc_ie_plus_api_token', '' ) );
	}

	/**
	 * Retrieve settings tabs.
	 *
	 * @since  2.0.0
	 * @return array
	 */
	public function get_tabs() {

		$tabs = array(
			'ix_licensing_api_settings' => ( new Tabs\API( $this, $this->plugin ) )->get_registered_settings(),
			'ix_general_settings'       => ( new Tabs\General( $this, $this->plugin ) )->get_registered_settings(),
			'ix_taxes_settings'         => ( new Tabs\Taxes( $this, $this->plugin ) )->get_registered_settings(),
			'ix_invoices_settings'      => ( new Tabs\Invoices( $this, $this->plugin ) )->get_registered_settings(),
		);
		if ( '1' == get_option( 'hd_wc_ie_plus_tax_country' ) ) {
			$tabs['ix_vat_moss_settings'] = ( new Tabs\VatMoss( $this, $this->plugin ) )->get_registered_settings();
		}
		$tabs = array_merge( $tabs, array(
			'ix_quotes_settings'        => ( new Tabs\Quotes( $this, $this->plugin ) )->get_registered_settings(),
			'ix_guides_settings'        => ( new Tabs\Guides( $this, $this->plugin ) )->get_registered_settings(),
		) );

		// Example on using this filter: https://gist.github.com/webdados/8e1f1a893ca296300595f9b05318ef20
		return apply_filters( 'invoicexpress_woocommerce_settings_tabs', $tabs );
	}

	/**
	 * Gets update information about the plugin for the settings page
	 *
	 * @since  2.4.3
	 */
	public function version_update_information() {
		$this->plugin->updater->version_update_information();
	}

}
