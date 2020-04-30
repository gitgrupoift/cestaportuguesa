<?php

namespace Webdados\InvoiceXpressWooCommerce;

class UpgradeFunctions {

	/**
	 * The plugin's instance.
	 *
	 * @since  2.0.4
	 * @access protected
	 * @var    Plugin
	 */
	protected $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.3.0
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		$db_version  = get_option( 'hd_wc_ie_plus_db_version', '0' );
		if ( version_compare( $db_version, INVOICEXPRESS_WOOCOMMERCE_VERSION, '<' ) ) {
			// Do whatever needed for each version update
			if ( version_compare( $db_version, '2.3.0', '<' ) ) {
				$this->upgrade_2_3_0();
			}
			if ( version_compare( $db_version, '2.4.10', '<' ) ) {
				$this->upgrade_2_4_10();
			}
			// Upgrade the database version
			update_option( 'hd_wc_ie_plus_db_version', INVOICEXPRESS_WOOCOMMERCE_VERSION );
			do_action( 'invoicexpress_woocommerce_debug', 'Database upgraded to '.INVOICEXPRESS_WOOCOMMERCE_VERSION );
		}

	}

	/**
	 * 2.3.0 Upgrade routines
	 *
	 * @since 2.3.0
	 */
	public function upgrade_2_3_0() {
		//Create table for scheduled automatic documents
		$this->plugin->create_scheduled_docs_table();
	}

	/**
	 * 2.4.10 Upgrade routines
	 *
	 * @since 2.4.10
	 */
	public function upgrade_2_4_10() {
		//Create table for scheduled automatic documents - Again, because some installs don't have it yet
		$this->plugin->create_scheduled_docs_table();
	}

}
