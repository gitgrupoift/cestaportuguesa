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
	 * @since 2.5
	 * @param Plugin $plugin This plugin's instance.
	 */
	public function __construct( Plugin $plugin ) {
		parent::__construct( $plugin );
	}

	/**
	 * Checks if required settings are satisfied.
	 *
	 * @return bool
	 */
	public function check_requirements() {
		return ! empty( get_option( 'hd_wc_ie_plus_subdomain', '' ) )
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

		return $tabs;
	}
}
