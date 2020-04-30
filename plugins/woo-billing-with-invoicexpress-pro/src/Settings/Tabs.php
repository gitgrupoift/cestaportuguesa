<?php

namespace Webdados\InvoiceXpressWooCommerce\Settings;

class Tabs {

	/**
	 * The settings's instance.
	 *
	 * @since  2.0.0
	 * @access protected
	 * @var    Settings
	 */
	protected $settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.0.0
	 * @param Settings $settings This settings's instance.
	 */
	public function __construct( Settings $settings, \Webdados\InvoiceXpressWooCommerce\Plugin $plugin ) {
		$this->settings = $settings;
		$this->plugin   = $plugin;
	}

	/**
	 * Get setting's instance.
	 *
	 * @return Settings
	 */
	public function get_settings() {
		return $this->settings;
	}
}
