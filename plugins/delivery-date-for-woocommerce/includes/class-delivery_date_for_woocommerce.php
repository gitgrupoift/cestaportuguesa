<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.pixlogix.com
 * @since      1.0.0
 *
 * @package    Delivery_date_for_woocommerce
 * @subpackage Delivery_date_for_woocommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Delivery_date_for_woocommerce
 * @subpackage Delivery_date_for_woocommerce/includes
 * @author     Pixlogix <support@pixlogix.com>
 */
class DDFW {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Delivery_date_for_woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $ddfw_loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $ddfw_plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $ddfw_version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'DELIVERY_DATE_FOR_WOOCOMMERCE_VERSION' ) ) {
			$this->ddfw_version = DELIVERY_DATE_FOR_WOOCOMMERCE_VERSION;
		} else {
			$this->ddfw_version = '1.0.0';
		}
		$this->ddfw_plugin_name = 'delivery_date_for_woocommerce';

		$this->ddfw_load_dependencies();
		$this->ddfw_set_locale();
		$this->ddfw_define_admin_hooks();
		$this->ddfw_define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Delivery_date_for_woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Delivery_date_for_woocommerce_i18n. Defines internationalization functionality.
	 * - Delivery_date_for_woocommerce_Admin. Defines all hooks for the admin area.
	 * - Delivery_date_for_woocommerce_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function ddfw_load_dependencies() {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-delivery_date_for_woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-delivery_date_for_woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-delivery_date_for_woocommerce-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-delivery_date_for_woocommerce-public.php';
		

		$this->ddfw_loader = new DDFW_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Delivery_date_for_woocommerce_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function ddfw_set_locale() {

		$plugin_i18n = new DDFW_i18n();

		$this->ddfw_loader->add_action( 'plugins_loaded', $plugin_i18n, 'ddfw_load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function ddfw_define_admin_hooks() {

		$ddfw_plugin_admin = new DDFW_Admin( $this->ddfw_get_plugin_name(), $this->ddfw_get_version() );
		$this->ddfw_loader->add_action( 'admin_enqueue_scripts', $ddfw_plugin_admin, 'ddfw_enqueue_styles' );
		$this->ddfw_loader->add_action( 'admin_enqueue_scripts', $ddfw_plugin_admin, 'ddfw_enqueue_scripts' );
		$this->ddfw_loader->add_action( 'admin_init', $ddfw_plugin_admin,'ddfw_delivery_register_settings' );
		$this->ddfw_loader->add_action('admin_menu', $ddfw_plugin_admin,'ddfw_delivery_register_options_page');
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			
		/*
		 * Plugin Name: Delivery Date for WooCommerce
		 * Plugin URI: https://wordpress.org/plugins/delivery-date-for-woocommerce/
		 * Description: With our plugin customer can choose preferred date of delivery at the time of checkout.
		 * Author: Pixlogix
		 * Author URI: https://www.pixlogix.com
		 * Version: 1.1.4
		 * Text Domain: Delivery_date_for_woocommerce
		 * Domain Path: /languages
		 * WC requires at least: 3.0.0
		 * WC tested up to: 3.9.1
		 */
			 
		$this->ddfw_loader->add_action( 'woocommerce_admin_order_data_after_billing_address',$ddfw_plugin_admin,'ddfw_checkout_delivery_date_display_admin');
		$this->ddfw_loader->add_filter( 'manage_edit-shop_order_columns', $ddfw_plugin_admin,'ddfw_delivery_date_new_order_column' );	
		$this->ddfw_loader->add_action( 'manage_shop_order_posts_custom_column', $ddfw_plugin_admin,'ddfw_delivery_date_new_order_column_value', 2 );	
		$this->ddfw_loader->add_action( 'woocommerce_saved_order_items', $ddfw_plugin_admin,'ddfw_woocommerce_saved_order_items',10 , 2);	
		
		}
		
			
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function ddfw_define_public_hooks() {

		$ddfw_plugin_public = new DDFW_Public( $this->ddfw_get_plugin_name(), $this->ddfw_get_version() );

		$this->ddfw_loader->add_action( 'wp_enqueue_scripts', $ddfw_plugin_public, 'ddfw_enqueue_styles' );
		$this->ddfw_loader->add_action( 'wp_enqueue_scripts', $ddfw_plugin_public, 'ddfw_enabling_date_picker' );
		$this->ddfw_loader->add_action( 'wp_enqueue_scripts', $ddfw_plugin_public, 'ddfw_enqueue_scripts' );

		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			
			$this->ddfw_loader->add_action( 'woocommerce_after_order_notes', $ddfw_plugin_public, 'ddfw_delivery_options' );
			$this->ddfw_loader->add_action( 'woocommerce_checkout_process', $ddfw_plugin_public, 'ddfw_delivery_options_process' );
			$this->ddfw_loader->add_action( 'woocommerce_checkout_update_order_meta',$ddfw_plugin_public,'ddfw_add_delivery_date_to_order');
			$this->ddfw_loader->add_action( 'woocommerce_email_after_order_table',$ddfw_plugin_public,'ddfw_add_order_delivery_date_email',10,2);
		} 
		

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->ddfw_loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function ddfw_get_plugin_name() {
		return $this->ddfw_plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Delivery_date_for_woocommerce_Loader    Orchestrates the hooks of the plugin.
	 */
	public function ddfw_get_loader() {
		return $this->ddfw_loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function ddfw_get_version() {
		return $this->ddfw_version;
	}

}
