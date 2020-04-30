<?php

namespace Webdados\InvoiceXpressWooCommerce\Menu;

use \Webdados\InvoiceXpressWooCommerce\Settings\Settings;

/**
 * Register menu.
 *
 * @package InvoiceXpressWooCommerce
 * @since   2.0.0
 */
class Menu extends \Webdados\InvoiceXpressWooCommerce\BaseMenu {

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'admin_page' ), 90 );
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ), 20 );
		add_filter( 'plugin_action_links_' . INVOICEXPRESS_WOOCOMMERCE_BASENAME, array( $this, 'add_action_link' ), 10, 2 );

		add_action( 'init', array( $this, 'invoicexpress_api_rewrite_rule' ) );
		add_filter( 'query_vars', array( $this, 'invoicexpress_api_query_var' ) );
		add_action( 'parse_request', array( $this, 'invoicexpress_api_parse_request' ) );
	}
}
