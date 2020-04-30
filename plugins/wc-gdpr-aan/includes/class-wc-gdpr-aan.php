<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://meuppt.pt
 * @since      1.0.0
 *
 * @package    Wc_Gdpr_Aan
 * @subpackage Wc_Gdpr_Aan/includes
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
 * @package    Wc_Gdpr_Aan
 * @subpackage Wc_Gdpr_Aan/includes
 * @author     Bnext1 e MeuPPT <geral@meuppt.pt>
 */
class Wc_Gdpr_Aan {

	
	/**
	 * Slug
	 *
	 * @TODO Rename the plugin slug to your own.
	 * @var string
	 */
	public $plugin_slug = 'wc_gdpr_all_around';
	
	/**
	 * The WordPress version the plugin requires minumum.
	 *
	 * @var string
	 */
	public $wp_version_min = "3.8";

	/**
	 * The WooCommerce version this extension requires minimum.
	 *
	 * Set this to the minimum version your 
	 * extension works with WooCommerce.
	 *
	 * @var string
	 */
	public $woo_version_min = "2.1.12";
	
	/**
	 * The Plugin Name.
	 *
	 * @TODO Rename the plugin name to your own.
	 * @var string
	 */
	public $name = "WooCommerce GDPR All Around Notices";

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wc_Gdpr_Aan_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

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
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wc-gdpr-aan';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		
		// Define constants
		$this->define_constants();
		
		// Check plugin requirements
		$this->check_requirements();
		

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wc_Gdpr_Aan_Loader. Orchestrates the hooks of the plugin.
	 * - Wc_Gdpr_Aan_i18n. Defines internationalization functionality.
	 * - Wc_Gdpr_Aan_Admin. Defines all hooks for the admin area.
	 * - Wc_Gdpr_Aan_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-gdpr-aan-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-gdpr-aan-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wc-gdpr-aan-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wc-gdpr-aan-public.php';

		$this->loader = new Wc_Gdpr_Aan_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wc_Gdpr_Aan_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wc_Gdpr_Aan_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wc_Gdpr_Aan_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_settings = new Wc_Gdpr_Aan_Settings( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		// Save/Update our plugin options
		$this->loader->add_action('admin_init', $plugin_settings, 'options_update');
		// Add menu item
		$this->loader->add_action( 'admin_menu', $plugin_settings, 'add_plugin_admin_menu' );
		// Add Settings link to the plugin
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_settings, 'add_action_links' );
		

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wc_Gdpr_Aan_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_settings = new Wc_Gdpr_Aan_Settings( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		// Apply checkbox conditionals in order to show the messages in the right places
		
		$this->loader->add_action( 'woocommerce_before_main_content', $plugin_public, 'wc_gdpr_render_message_1');
		$this->loader->add_action( 'woocommerce_before_shop_loop', $plugin_public, 'wc_gdpr_render_message_2');	
		$this->loader->add_action( 'woocommerce_before_shop_loop_item', $plugin_public, 'wc_gdpr_render_message_3');
		$this->loader->add_action( 'woocommerce_after_shop_loop_item', $plugin_public, 'wc_gdpr_render_message_4');
		$this->loader->add_action( 'woocommerce_after_shop_loop', $plugin_public, 'wc_gdpr_render_message_5');	
		$this->loader->add_action( 'woocommerce_after_main_content', $plugin_public, 'wc_gdpr_render_message_6');
		
		$this->loader->add_action( 'woocommerce_before_cart', $plugin_public, 'wc_gdpr_render_message_7');
		$this->loader->add_action( 'woocommerce_after_cart_table', $plugin_public, 'wc_gdpr_render_message_8');	
		$this->loader->add_action( 'woocommerce_cart_totals_before_order_total', $plugin_public, 'wc_gdpr_render_message_9');
		$this->loader->add_action( 'woocommerce_proceed_to_checkout', $plugin_public, 'wc_gdpr_render_message_10');
		$this->loader->add_action( 'woocommerce_after_cart_totals', $plugin_public, 'wc_gdpr_render_message_11');	
		$this->loader->add_action( 'woocommerce_after_cart', $plugin_public, 'wc_gdpr_render_message_12');
		
		$this->loader->add_action( 'woocommerce_before_checkout_form', $plugin_public, 'wc_gdpr_render_message_13');
		$this->loader->add_action( 'woocommerce_checkout_before_customer_details', $plugin_public, 'wc_gdpr_render_message_14');	
		$this->loader->add_action( 'woocommerce_before_checkout_billing_form', $plugin_public, 'wc_gdpr_render_message_15');
		$this->loader->add_action( 'woocommerce_after_checkout_billing_form', $plugin_public, 'wc_gdpr_render_message_16');
		$this->loader->add_action( 'woocommerce_after_order_notes', $plugin_public, 'wc_gdpr_render_message_17');	
		$this->loader->add_action( 'woocommerce_review_order_before_payment', $plugin_public, 'wc_gdpr_render_message_18');
		
		$this->loader->add_action( 'woocommerce_before_customer_login_form', $plugin_public, 'wc_gdpr_render_message_19');
		$this->loader->add_action( 'woocommerce_register_form_start', $plugin_public, 'wc_gdpr_render_message_20');	
		$this->loader->add_action( 'woocommerce_login_form_start', $plugin_public, 'wc_gdpr_render_message_21');
		$this->loader->add_action( 'woocommerce_after_customer_login_form', $plugin_public, 'wc_gdpr_render_message_22');
		$this->loader->add_action( 'woocommerce_after_account_navigation', $plugin_public, 'wc_gdpr_render_message_23');	
		$this->loader->add_action( 'woocommerce_after_edit_account_address_form', $plugin_public, 'wc_gdpr_render_message_24');
		
		// Second box checkbox apply
		
		$this->loader->add_action( 'woocommerce_before_main_content', $plugin_public, 'wc_gdpr_render_messagea_1');
		$this->loader->add_action( 'woocommerce_before_shop_loop', $plugin_public, 'wc_gdpr_render_messagea_2');	
		$this->loader->add_action( 'woocommerce_before_shop_loop_item', $plugin_public, 'wc_gdpr_render_messagea_3');
		$this->loader->add_action( 'woocommerce_after_shop_loop_item', $plugin_public, 'wc_gdpr_render_messagea_4');
		$this->loader->add_action( 'woocommerce_after_shop_loop', $plugin_public, 'wc_gdpr_render_messagea_5');	
		$this->loader->add_action( 'woocommerce_after_main_content', $plugin_public, 'wc_gdpr_render_messagea_6');
		
		$this->loader->add_action( 'woocommerce_before_cart', $plugin_public, 'wc_gdpr_render_messagea_7');
		$this->loader->add_action( 'woocommerce_after_cart_table', $plugin_public, 'wc_gdpr_render_messagea_8');	
		$this->loader->add_action( 'woocommerce_cart_totals_before_order_total', $plugin_public, 'wc_gdpr_render_messagea_9');
		$this->loader->add_action( 'woocommerce_proceed_to_checkout', $plugin_public, 'wc_gdpr_render_messagea_10');
		$this->loader->add_action( 'woocommerce_after_cart_totals', $plugin_public, 'wc_gdpr_render_messagea_11');	
		$this->loader->add_action( 'woocommerce_after_cart', $plugin_public, 'wc_gdpr_render_messagea_12');
		
		$this->loader->add_action( 'woocommerce_before_checkout_form', $plugin_public, 'wc_gdpr_render_messagea_13');
		$this->loader->add_action( 'woocommerce_checkout_before_customer_details', $plugin_public, 'wc_gdpr_render_messagea_14');	
		$this->loader->add_action( 'woocommerce_before_checkout_billing_form', $plugin_public, 'wc_gdpr_render_messagea_15');
		$this->loader->add_action( 'woocommerce_after_checkout_billing_form', $plugin_public, 'wc_gdpr_render_messagea_16');
		$this->loader->add_action( 'woocommerce_after_order_notes', $plugin_public, 'wc_gdpr_render_messagea_17');	
		$this->loader->add_action( 'woocommerce_review_order_before_payment', $plugin_public, 'wc_gdpr_render_messagea_18');
		
		$this->loader->add_action( 'woocommerce_before_customer_login_form', $plugin_public, 'wc_gdpr_render_messagea_19');
		$this->loader->add_action( 'woocommerce_register_form_start', $plugin_public, 'wc_gdpr_render_messagea_20');	
		$this->loader->add_action( 'woocommerce_login_form_start', $plugin_public, 'wc_gdpr_render_messagea_21');
		$this->loader->add_action( 'woocommerce_after_customer_login_form', $plugin_public, 'wc_gdpr_render_messagea_22');
		$this->loader->add_action( 'woocommerce_after_account_navigation', $plugin_public, 'wc_gdpr_render_messagea_23');	
		$this->loader->add_action( 'woocommerce_after_edit_account_address_form', $plugin_public, 'wc_gdpr_render_messagea_24');
		
		// Custom CSS added to HEAD section
		$this->loader->add_action( 'wp_head', $plugin_public, 'wc_gdpr_custom_css');

	}
	
	
	/**
	 * Define Constants
	 *
	 * @access private
	 */
	private function define_constants() {
	
		// TODO: change 'WC_GDPR_ALL_AROUND' to the name of the plugin.
		if ( ! defined( 'WC_GDPR_ALL_AROUND' ) ) define( 'WC_GDPR_ALL_AROUND', $this->name );
		if ( ! defined( 'WC_GDPR_ALL_AROUND_FILE' ) ) define( 'WC_GDPR_ALL_AROUND_FILE', __FILE__ );
		if ( ! defined( 'WC_GDPR_ALL_AROUND_VERSION' ) ) define( 'WC_GDPR_ALL_AROUND_VERSION', $this->version );
		if ( ! defined( 'WC_GDPR_ALL_AROUND_WP_VERSION_REQUIRE' ) ) define( 'WC_GDPR_ALL_AROUND_WP_VERSION_REQUIRE', $this->wp_version_min );
		if ( ! defined( 'WC_GDPR_ALL_AROUND_WOO_VERSION_REQUIRE' ) ) define( 'WC_GDPR_ALL_AROUND_WOO_VERSION_REQUIRE', $this->woo_version_min );
		if ( ! defined( 'WC_GDPR_ALL_AROUND_PAGE' ) ) define( 'WC_GDPR_ALL_AROUND_PAGE', str_replace('_', '-', $this->plugin_slug) );

	}
	
	
	/**
	 * Checks that the WordPress setup meets the plugin requirements.
	 *
	 * @access private
	 * @global string $wp_version
	 * @global string $woocommerce
	 * @return boolean
	 */
	private function check_requirements() {
		global $wp_version, $woocommerce;

		$woo_version_installed = get_option('woocommerce_version');
		if( empty( $woo_version_installed ) ) { $woo_version_installed = WOOCOMMERCE_VERSION; }
		define( 'WC_EXTEND_WOOVERSION', $woo_version_installed );

		if (!version_compare($wp_version, WC_GDPR_ALL_AROUND_WP_VERSION_REQUIRE, '>=')) {
			add_action('admin_notices', array( &$this, 'display_req_notice' ) );
			return false;
		}

		if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			add_action('admin_notices', array( &$this, 'display_req_woo_not_active_notice' ) );
			return false;
		}
		else{
			if( version_compare(WC_EXTEND_WOOVERSION, WC_GDPR_ALL_AROUND_WOO_VERSION_REQUIRE, '<' ) ) {
				add_action('admin_notices', array( &$this, 'display_req_woo_notice' ) );
				return false;
			}
		}

		return true;
	}
	
	
	/**
	 * Display the WordPress requirement notice.
	 *
	 * @access static
	 */
	static function display_req_notice() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __('Sorry, <strong>%s</strong> requires WordPress ' . WC_GDPR_ALL_AROUND_WP_VERSION_REQUIRE . ' or higher. Please upgrade your WordPress setup', $this->plugin_name), WC_GDPR_ALL_AROUND );
		echo '</p></div>';
	}

	/**
	 * Display the WooCommerce requirement installation notice.
	 *
	 * @access static
	 */
	static function display_req_woo_not_active_notice() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __('Sorry, <strong>%s</strong> requires WooCommerce to be installed and activated first. Please <a href="%s">install WooCommerce</a> first.', $this->plugin_name), WC_GDPR_ALL_AROUND, admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce') );
		echo '</p></div>';
	}

	/**
	 * Display the WooCommerce requirement version notice.
	 *
	 * @access static
	 */
	static function display_req_woo_notice() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __('Sorry, <strong>%s</strong> requires WooCommerce ' . WC_GDPR_ALL_AROUND_WOO_VERSION_REQUIRE . ' or higher. Please update WooCommerce for %s to work.', $this->plugin_name), WC_GDPR_ALL_AROUND, WC_GDPR_ALL_AROUND );
		echo '</p></div>';
	}
	
	/**
	 * Get the plugin path for WooCommerce.
	 *
	 * @access public
	 * @return string
	 */
	public function wc_plugin_path() {
		return untrailingslashit( plugin_dir_path( plugin_dir_path( __FILE__ ) ) ) . '/woocommerce/';
	}
	

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wc_Gdpr_Aan_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
