<?php
// phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
// phpcs:disable WordPress.NamingConventions.ValidVariableName.MemberNotSnakeCase

namespace Webdados\InvoiceXpressWooCommerce;

use \Webdados\InvoiceXpressWooCommerce\LicenseManager\License;

/* WooCommerce CRUD ready */

class Plugin {

	/**
	 * Integrations active or not
	 *
	 * @since  2.0.7
	 * @var    string
	 */
	public $wpml_active                     = false;
	public $aelia_eu_vat_assistant_active   = false;
	public $woocommerce_eu_vat_field_active = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.3.0
	 * @param Settings $settings This settings's instance.
	 */
	public function __construct() {
		$this->license = new License(
			WEBDADOS_LICENSE_MANAGER_API_URL,
			get_option( 'hd_wc_ie_plus_license_key', '' ),
			'hd_wc_ie_plus_license_checksum',
			INVOICEXPRESS_WOOCOMMERCE_PRODUCT_ID,
			WEBDADOS_LICENSE_MANAGER_INSTANCE,
			'hd_wc_ie_plus_license_last_check'
		);
	}

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
			'vat_moss_invoice'   => __( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' ),
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
		add_filter( 'load_textdomain_mofile', array( $this, 'load_textdomain_mofile' ), 10, 2 );
		load_plugin_textdomain( 'woo-billing-with-invoicexpress' );
	}

	/**
	 * Make sure the translations are NOT loaded from the wp-languages folder
	 *
	 * @since 2.0.3
	 */
	public function load_textdomain_mofile( $mofile, $domain ) {

		if ( 'woo-billing-with-invoicexpress' !== $domain ) {
			return $mofile;
		}

		return str_replace( WP_LANG_DIR . '/plugins/', INVOICEXPRESS_WOOCOMMERCE_PLUGIN_PATH . 'languages/', $mofile );
	}

	/**
	 * Register all of the hooks related to the functionality
	 * of the plugin.
	 *
	 * @since 2.0.0
	 */
	public function define_hooks() {
		$settings        = new Settings\Settings( $this );

		$modules = array(
			$settings,
			new Menu\Menu( $settings, $this ),
		);

		if ( $this->license->verify() ) {

			$scheduler       = new Pro\DocumentsScheduler( $this );
			$invoice_receipt = new Modules\InvoiceReceipt\InvoiceReceiptController( $this );
			$payment         = new Modules\Payment\PaymentController( $this );
			$vat_pro         = new Pro\Vat( $this );

			$modules = array_merge( $modules, array(
				new Modules\Vat\VatController( $this ),
				new Modules\Invoice\InvoiceController( $this ),
				new Modules\SimplifiedInvoice\SimplifiedInvoiceController( $this ),
				new Modules\VatMossInvoice\VatMossInvoiceController( $this, $vat_pro ),
				$invoice_receipt,
				$payment,
				new Modules\CreditNote\CreditNoteController( $this, $payment, $invoice_receipt ),
				new Modules\Proforma\ProformaController( $this ),
				new Modules\Quote\QuoteController( $this ),
				new Modules\TransportGuide\TransportGuideController( $this ),
				new Modules\DevolutionGuide\DevolutionGuideController( $this ),
				new Modules\AutomaticInvoice\AutomaticInvoiceController( $this, $scheduler ),
				new Modules\CancelDocuments\CancelDocumentsController( $this ),
				new Modules\Sequence\SequenceController( $this ),
				new Modules\Taxes\TaxController( $this ),
				new Pro\OrderActions( $this ),
				$vat_pro,
				$scheduler,
			) );

			add_action( 'plugins_loaded', array( $this, 'update_checker' ), 40 );

			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_and_styles' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts_and_styles' ) );

			add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );

			add_action( 'plugins_loaded', array( $this, 'cronstarter_activation' ), 50 );

		}

		foreach ( $modules as $module ) {
			$module->register_hooks();
		}

		add_action( 'plugins_loaded', array( $this, 'init_integrations_status' ), 20 );

		add_action( 'plugins_loaded', array( $this, 'database_version_upgrade' ), 30 );

		add_filter( 'woocommerce_screen_ids', array( $this, 'woocommerce_screen_ids' ) );

	}

	/**
	 * Init cronjobs
	 *
	 * @since 2.3.0
	 */
	public function cron_schedules( $schedules ) {
		$schedules['ix_five_minutes'] = array(
			'interval' => 5 * 60,
			'display' => __( 'Every 5 minutes', 'woo-billing-with-invoicexpress' )
		);
		return $schedules;
	}
	public function cronstarter_activation() {
		//Scheduled documents cronjob
		if ( ! wp_next_scheduled( 'invoicexpress_woocommerce_cron_five_minutes' ) ) {
			wp_schedule_event( time(), 'ix_five_minutes', 'invoicexpress_woocommerce_cron_five_minutes' );
		}
	}

	/**
	 * Init integration status for third party plugins
	 *
	 * @since 2.0.7
	 */
	public function init_integrations_status() {
		//WPML and WooCommerce Multilingual
		$this->wpml_active = function_exists( 'icl_object_id' ) && function_exists( 'icl_register_string' ) && class_exists( 'woocommerce_wpml' );
		//WooCommerce EU VAT Assistant (Aelia)
		$this->aelia_eu_vat_assistant_active = class_exists( 'Aelia\WC\EU_VAT_Assistant\WC_Aelia_EU_VAT_Assistant' );
		//EU VAT Field (WooCommerce)
		$this->woocommerce_eu_vat_field_active = class_exists( 'WC_EU_VAT_Number_Init' );
	}

	/**
	 * Register scripts and styles (they'll be enqueue later only if needed)
	 *
	 * @since 2.1.0
	 */
	public function register_scripts_and_styles() {
		//Checkout
		if ( is_checkout() && apply_filters( 'invoicexpress_woocommerce_checkout_script_enqueue', false ) ) {
			// checkout.js
			wp_register_script( 'hd_wc_ie_checkout', plugins_url( 'assets/js/checkout.js', INVOICEXPRESS_WOOCOMMERCE_PLUGIN_FILE ), array( 'jquery' ), INVOICEXPRESS_WOOCOMMERCE_VERSION, true );
			wp_localize_script( 'hd_wc_ie_checkout', 'hd_wc_ie_checkout', apply_filters( 'invoicexpress_woocommerce_checkout_localize_script_values', array() ) );
			wp_enqueue_script( 'hd_wc_ie_checkout' );
		}
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
	 * Handle update checker.
	 *
	 * @since  2.0.0
	 * @return void
	 */
	public function update_checker() {

		if ( ! is_admin() ) {
			return;
		}

		$this->updater = new LicenseManager\Updater(
			WEBDADOS_LICENSE_MANAGER_API_URL,
			'woo-billing-with-invoicexpress-pro',
			'woo-billing-with-invoicexpress-pro/woocommerce-billing-invoicexpress-pro-edition.php',
			INVOICEXPRESS_WOOCOMMERCE_VERSION,
			INVOICEXPRESS_WOOCOMMERCE_PRODUCT_ID,
			get_option( 'hd_wc_ie_plus_license_key' ),
			WEBDADOS_LICENSE_MANAGER_INSTANCE
		);

		// Take over the update check.
		add_filter( 'pre_set_site_transient_update_plugins', array( $this->updater, 'check_for_plugin_update' ) );

		// Take over the Plugin info screen.
		add_filter( 'plugins_api', array( $this->updater, 'plugins_api_call' ), 10, 3 );
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
	 * Get order WPML language
	 *
	 * @since  2.0.7
	 * @return string
	 */
	public function get_order_wpml_language( $order_object ) {
		return $order_object->get_meta( 'wpml_language' );
	}

	/**
	 * Get plugin translated option
	 *
	 * @since  2.0.7
	 * @return string
	 */
	public function get_translated_option( $option, $lang = null, $order_object = null ) {
		if ( ! $this->wpml_active || ! defined('ICL_LANGUAGE_CODE') ) {
			//No WPML just return the option
			return get_option( $option );
		}
		if ( empty( $lang ) && ! empty( $order_object ) ) {
			//Try to the the language from the order
			$lang = $this->get_order_wpml_language( $order_object );
		}
		if ( empty( $lang ) || $lang == ICL_LANGUAGE_CODE ) {
			//No language or same as current, return the option
			return get_option( $option );
		}
		//Change the WPML language, get the translated option, revert to active language and return the value
		global $sitepress;
		$sitepress->switch_lang( $lang );
		$value = get_option( $option );
		$sitepress->switch_lang( ICL_LANGUAGE_CODE );
		return $value;
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
