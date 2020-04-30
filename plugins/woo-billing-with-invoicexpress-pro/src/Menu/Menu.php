<?php

namespace Webdados\InvoiceXpressWooCommerce\Menu;

use \Webdados\InvoiceXpressWooCommerce\Notices;
use \Webdados\InvoiceXpressWooCommerce\Settings\Settings;
use \Webdados\InvoiceXpressWooCommerce\LicenseManager\License;

/**
 * Register menu.
 *
 * @package InvoiceXpressWooCommerce
 * @since   2.0.0
 */
class Menu extends \Webdados\InvoiceXpressWooCommerce\BaseMenu {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.0.0
	 * @param Settings $settings This settings's instance.
	 */
	public function __construct( Settings $settings, $plugin  ) {
		parent::__construct( $settings, $plugin );
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'admin_page' ), 90 );
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ), 20 );
		add_filter( 'plugin_action_links_' . INVOICEXPRESS_WOOCOMMERCE_BASENAME, array( $this, 'add_action_link' ), 10, 2 );

		add_action( 'admin_notices', array( $this, 'show_license_activation_admin_notice' ) );
		add_action( 'add_option_hd_wc_ie_plus_license_key', array( $this, 'add_license_key' ), 10, 2 );
		add_action( 'update_option_hd_wc_ie_plus_license_key', array( $this, 'update_license_key' ), 10, 2 );

		add_action( 'init', array( $this, 'invoicexpress_api_rewrite_rule' ) );
		add_filter( 'query_vars', array( $this, 'invoicexpress_api_query_var' ) );
		add_action( 'parse_request', array( $this, 'invoicexpress_api_parse_request' ) );
	}

	/**
	 * Add license key.
	 *
	 * @param string $option Name of the option to add.
	 * @param mixed  $value Value of the option.
	 * @return void
	 */
	public function add_license_key( $option, $value ) {
		$this->update_license_key( '', $value );
	}

	/**
	 * Update license key.
	 *
	 * @since  2.0.0
	 * @param  mixed $old_value The old option value.
	 * @param  mixed $value     The new option value.
	 * @return void
	 */
	public function update_license_key( $old_value, $value ) {

		if ( empty( $value ) ) {
			return;
		}

		$valid_license = $this->plugin->license->validate( $value );
		if ( ! $valid_license ) {
			Notices::add_notice(
				esc_html__( 'Invalid licence key.', 'woo-billing-with-invoicexpress' ),
				'error'
			);
		}
	}

	/**
	 * Shows license activation admin notice.
	 *
	 * @since  2.0.0
	 * @return void
	 */
	public function show_license_activation_admin_notice() {

		if ( ! empty( $this->plugin->license->verify() ) ) {
			return;
		}

		Notices::add_notice(
			sprintf(
				/* translators: %1$s: plugin name, %2$s: link opening tag, %3$s: link closing tag */
				esc_html__( '%1$s is inactive, please enter your %2$sLicense Key%3$s', 'woo-billing-with-invoicexpress' ),
				sprintf(
					'<strong>%s</strong>',
					INVOICEXPRESS_WOOCOMMERCE_PLUGIN_NAME
				),
				sprintf(
					'<a href="%s">',
					esc_url( admin_url( 'admin.php?page=invoicexpress_woocommerce' ) )
				),
				'</a>'
			),
			'warning'
		);
	}
}
