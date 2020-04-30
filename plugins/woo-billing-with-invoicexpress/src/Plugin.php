<?php
// phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
// phpcs:disable WordPress.NamingConventions.ValidVariableName.MemberNotSnakeCase

namespace Webdados\InvoiceXpressWooCommerce;

/* WooCommerce CRUD ready */

class Plugin {

	/**
	 * Integrations active or not
	 *
	 * @since  2.0.7
	 * @var    string
	 */
	public $wpml_active = false;

	/**
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since 2.0.0
	 */
	public function run() {
		$this->set_locale();
		$this->define_hooks();

		$this->type_names = array(
			'invoice'            => __( 'Invoice', 'woo-billing-with-invoicexpress' ),
			'simplified_invoice' => __( 'Simplified invoice', 'woo-billing-with-invoicexpress' ),
			'invoice_receipt'    => __( 'Invoice-receipt', 'woo-billing-with-invoicexpress' ),
			'credit_note'        => __( 'Credit note', 'woo-billing-with-invoicexpress' ),
			'quote'              => __( 'Quote', 'woo-billing-with-invoicexpress' ),
			'proforma'           => __( 'Proforma', 'woo-billing-with-invoicexpress' ),
			'transport_guide'    => __( 'Delivery note', 'woo-billing-with-invoicexpress' ),
			'devolution_guide'   => __( 'Return delivery note', 'woo-billing-with-invoicexpress' ),
			'receipt'            => __( 'Receipt', 'woo-billing-with-invoicexpress' ),
		);
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since 2.0.0
	 */
	public function set_locale() {
		load_plugin_textdomain( 'woo-billing-with-invoicexpress' );
	}

	/**
	 * Register all of the hooks related to the functionality
	 * of the plugin.
	 *
	 * @since 2.0.0
	 */
	public function define_hooks() {
		$settings = new Settings\Settings( $this );

		$modules = [
			$settings,
			new Menu\Menu( $settings, $this ),
			new Modules\Invoice\InvoiceController( $this ),
			new Modules\SimplifiedInvoice\SimplifiedInvoiceController( $this ),
			new Modules\Taxes\TaxController( $this ),
			new Modules\Vat\VatController( $this ),
		];

		foreach ( $modules as $module ) {
			$module->register_hooks();
		}

		add_action( 'plugins_loaded', array( $this, 'database_version_upgrade' ), 30 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts_and_styles' ) );

		add_filter( 'woocommerce_screen_ids', array( $this, 'woocommerce_screen_ids' ) );

	}

	/**
	 * Register admin scripts and styles
	 *
	 * @since 2.4.10
	 */
	public function admin_register_scripts_and_styles() {
		//WooCommerce Admin Notices compatibility
		if ( function_exists( 'wc_admin_url' ) ) {
			if ( version_compare( WC_ADMIN_VERSION_NUMBER, '0.23.2', '>=' ) ) {
				if ( class_exists( 'Automattic\WooCommerce\Admin\Loader' ) ) {
					if ( \Automattic\WooCommerce\Admin\Loader::is_admin_page() || \Automattic\WooCommerce\Admin\Loader::is_embed_page() ) {
						wp_register_script( 'hd_wc_ie_woocommerce_admin_notices', plugins_url( 'assets/js/woocommerce-admin-notices.js', INVOICEXPRESS_WOOCOMMERCE_PLUGIN_FILE ), array( 'wp-hooks' ), INVOICEXPRESS_WOOCOMMERCE_VERSION.rand(0,999), true );
						wp_enqueue_script( 'hd_wc_ie_woocommerce_admin_notices' );
					}
				}
			}
		}
	}

	/**
	 * Handle database version upgrade
	 *
	 * @since  2.0.0
	 * @return void
	 */
	public function database_version_upgrade() {
		if ( ! is_admin() ) {
			return;
		}
		include( 'UpgradeFunctions.php' );
		$upgradeFunctions = new UpgradeFunctions( $this );
	}

	/**
	 * Create scheduled_docs_table
	 *
	 * @since 2.5
	 */
	public function create_scheduled_docs_table() {
		//Create table for scheduled automatic documents
		global $wpdb;
		$table_name = $wpdb->prefix.$this->scheduled_docs_table;
		$wpdb_collate = $wpdb->collate;
		$sql =
			"CREATE TABLE {$table_name} (
				task_id bigint(20) UNSIGNED NOT NULL auto_increment,
				order_id  bigint(20) UNSIGNED NOT NULL,
				date_time datetime NOT NULL,
				document_type varchar(30) NOT NULL,
				PRIMARY KEY (task_id)
			)
			COLLATE {$wpdb_collate}";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		do_action( 'invoicexpress_woocommerce_debug', "Created {$table_name} table" );
	}
	public function maybe_create_scheduled_docs_table() {
		global $wpdb;
		$table = $wpdb->prefix.$this->scheduled_docs_table;
		$query = "SHOW TABLES LIKE '{$table}'";
		if ( ! $wpdb->get_row( $query ) ) {
			$this->create_scheduled_docs_table();
		}
	}

	/**
	 * Get possible status.
	 *
	 * @since  2.0.4
	 * @return array
	 */
	public function get_possible_status() {
		return apply_filters( 'invoicexpress_woocommerce_automatic_invoice_possible_status', array( 'wc-pending', 'wc-on-hold', 'wc-processing', 'wc-completed' ) );
	}

	/**
	 * Get not recommended status.
	 *
	 * @since  2.0.4
	 * @return array
	 */
	public function get_not_recommended_status() {
		return apply_filters( 'invoicexpress_woocommerce_automatic_invoice_not_recommended_status', array( 'wc-pending', 'wc-on-hold' ) );
	}

	/**
	 * Get plugin translated option
	 *
	 * @since  2.0.7
	 * @return string
	 */
	public function get_translated_option( $option, $lang = null, $order_object = null ) {
		return get_option( $option );
	}

	/**
	 * Add our screen to WooCommerce screens so that the correct CSS is loaded
	 *
	 * @since  2.4.2
	 * @return array
	 */
	public function woocommerce_screen_ids( $screens ) {
		$screens[] = 'woocommerce_page_invoicexpress_woocommerce';
		return $screens;
	}

	/**
	 * Check if order type is valid for invoicing
	 *
	 * @since  2.5.2
	 * @return array
	 */
	public function is_valid_order_type( $order_object ) {
		return apply_filters( 'invoicexpress_woocommerce_is_valid_order_type', true, $order_object );
	}

}
