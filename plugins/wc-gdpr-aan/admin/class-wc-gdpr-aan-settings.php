<?php
/**
 * The settings of the plugin.
 *
 * @link       http://devinvinson.com
 * @since      1.0.0
 *
 * @package    Wc_Gdpr_Aan
 * @subpackage Wc_Gdpr_Aan/admin
 */
/**
 * Class WordPress_Plugin_Template_Settings
 *
 */
class Wc_Gdpr_Aan_Settings {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}
	
	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
	    /**
	     * Add a settings page for this plugin to the Settings menu.
	     *
	     * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
	     *
	     *        Administration Menus: http://codex.wordpress.org/Administration_Menus
	     *
	     * add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);
	     *
	     * @link https://codex.wordpress.org/Function_Reference/add_options_page
	     */
	    add_submenu_page( 'woocommerce', 'Avisos e Notificações', 'Avisos e Notificações', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page')
	    );
	}
	
	
	 /**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {
	    /*
	    *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
	    */
	   $settings_link = array(
	    '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_name ) . '">' . __( 'Definições', $this->plugin_name ) . '</a>',
	   );
	   return array_merge(  $settings_link, $links );
	}
	
	
	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_setup_page() {
	    include_once( 'views/' . $this->plugin_name . '-admin-display.php' );
	}
	
	
	/**
	 * Validate fields from admin area plugin settings form ('exopite-lazy-load-xt-admin-display.php')
	 * @param  mixed $input as field form settings form
	 * @return mixed as validated fields
	 */
	public function validate($input) {
	    $valid = array();
	    
	    $valid['wc_gdpr_aan_cb1'] = ( isset( $input['wc_gdpr_aan_cb1'] ) && ! empty( $input['wc_gdpr_aan_cb1'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb2'] = ( isset( $input['wc_gdpr_aan_cb2'] ) && ! empty( $input['wc_gdpr_aan_cb2'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb3'] = ( isset( $input['wc_gdpr_aan_cb3'] ) && ! empty( $input['wc_gdpr_aan_cb3'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb4'] = ( isset( $input['wc_gdpr_aan_cb4'] ) && ! empty( $input['wc_gdpr_aan_cb4'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb5'] = ( isset( $input['wc_gdpr_aan_cb5'] ) && ! empty( $input['wc_gdpr_aan_cb5'] ) ) ? 1 : 0;	    
	    $valid['wc_gdpr_aan_cb6'] = ( isset( $input['wc_gdpr_aan_cb6'] ) && ! empty( $input['wc_gdpr_aan_cb6'] ) ) ? 1 : 0;
	    
	    $valid['wc_gdpr_aan_cb7'] = ( isset( $input['wc_gdpr_aan_cb7'] ) && ! empty( $input['wc_gdpr_aan_cb7'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb8'] = ( isset( $input['wc_gdpr_aan_cb8'] ) && ! empty( $input['wc_gdpr_aan_cb8'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb9'] = ( isset( $input['wc_gdpr_aan_cb9'] ) && ! empty( $input['wc_gdpr_aan_cb9'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb10'] = ( isset( $input['wc_gdpr_aan_cb10'] ) && ! empty( $input['wc_gdpr_aan_cb10'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb11'] = ( isset( $input['wc_gdpr_aan_cb11'] ) && ! empty( $input['wc_gdpr_aan_cb11'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb12'] = ( isset( $input['wc_gdpr_aan_cb12'] ) && ! empty( $input['wc_gdpr_aan_cb12'] ) ) ? 1 : 0;
	    
	    $valid['wc_gdpr_aan_cb13'] = ( isset( $input['wc_gdpr_aan_cb13'] ) && ! empty( $input['wc_gdpr_aan_cb13'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb14'] = ( isset( $input['wc_gdpr_aan_cb14'] ) && ! empty( $input['wc_gdpr_aan_cb14'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb15'] = ( isset( $input['wc_gdpr_aan_cb15'] ) && ! empty( $input['wc_gdpr_aan_cb15'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb16'] = ( isset( $input['wc_gdpr_aan_cb16'] ) && ! empty( $input['wc_gdpr_aan_cb16'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb17'] = ( isset( $input['wc_gdpr_aan_cb17'] ) && ! empty( $input['wc_gdpr_aan_cb17'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb18'] = ( isset( $input['wc_gdpr_aan_cb18'] ) && ! empty( $input['wc_gdpr_aan_cb18'] ) ) ? 1 : 0;
	    
	    $valid['wc_gdpr_aan_cb19'] = ( isset( $input['wc_gdpr_aan_cb19'] ) && ! empty( $input['wc_gdpr_aan_cb19'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb20'] = ( isset( $input['wc_gdpr_aan_cb20'] ) && ! empty( $input['wc_gdpr_aan_cb20'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb21'] = ( isset( $input['wc_gdpr_aan_cb21'] ) && ! empty( $input['wc_gdpr_aan_cb21'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb22'] = ( isset( $input['wc_gdpr_aan_cb22'] ) && ! empty( $input['wc_gdpr_aan_cb22'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb23'] = ( isset( $input['wc_gdpr_aan_cb23'] ) && ! empty( $input['wc_gdpr_aan_cb23'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cb24'] = ( isset( $input['wc_gdpr_aan_cb24'] ) && ! empty( $input['wc_gdpr_aan_cb24'] ) ) ? 1 : 0;
	    
	    // Second box validation
	    
	    $valid['wc_gdpr_aan_cba1'] = ( isset( $input['wc_gdpr_aan_cba1'] ) && ! empty( $input['wc_gdpr_aan_cba1'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba2'] = ( isset( $input['wc_gdpr_aan_cba2'] ) && ! empty( $input['wc_gdpr_aan_cba2'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba3'] = ( isset( $input['wc_gdpr_aan_cba3'] ) && ! empty( $input['wc_gdpr_aan_cba3'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba4'] = ( isset( $input['wc_gdpr_aan_cba4'] ) && ! empty( $input['wc_gdpr_aan_cba4'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba5'] = ( isset( $input['wc_gdpr_aan_cba5'] ) && ! empty( $input['wc_gdpr_aan_cba5'] ) ) ? 1 : 0;	    
	    $valid['wc_gdpr_aan_cba6'] = ( isset( $input['wc_gdpr_aan_cba6'] ) && ! empty( $input['wc_gdpr_aan_cba6'] ) ) ? 1 : 0;
	    
	    $valid['wc_gdpr_aan_cba7'] = ( isset( $input['wc_gdpr_aan_cba7'] ) && ! empty( $input['wc_gdpr_aan_cba7'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba8'] = ( isset( $input['wc_gdpr_aan_cba8'] ) && ! empty( $input['wc_gdpr_aan_cba8'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba9'] = ( isset( $input['wc_gdpr_aan_cba9'] ) && ! empty( $input['wc_gdpr_aan_cba9'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba10'] = ( isset( $input['wc_gdpr_aan_cba10'] ) && ! empty( $input['wc_gdpr_aan_cba10'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba11'] = ( isset( $input['wc_gdpr_aan_cba11'] ) && ! empty( $input['wc_gdpr_aan_cba11'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba12'] = ( isset( $input['wc_gdpr_aan_cba12'] ) && ! empty( $input['wc_gdpr_aan_cba12'] ) ) ? 1 : 0;
	    
	    $valid['wc_gdpr_aan_cba13'] = ( isset( $input['wc_gdpr_aan_cba13'] ) && ! empty( $input['wc_gdpr_aan_cba13'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba14'] = ( isset( $input['wc_gdpr_aan_cba14'] ) && ! empty( $input['wc_gdpr_aan_cba14'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba15'] = ( isset( $input['wc_gdpr_aan_cba15'] ) && ! empty( $input['wc_gdpr_aan_cba15'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba16'] = ( isset( $input['wc_gdpr_aan_cba16'] ) && ! empty( $input['wc_gdpr_aan_cba16'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba17'] = ( isset( $input['wc_gdpr_aan_cba17'] ) && ! empty( $input['wc_gdpr_aan_cba17'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba18'] = ( isset( $input['wc_gdpr_aan_cba18'] ) && ! empty( $input['wc_gdpr_aan_cba18'] ) ) ? 1 : 0;
	    
	    $valid['wc_gdpr_aan_cba19'] = ( isset( $input['wc_gdpr_aan_cba19'] ) && ! empty( $input['wc_gdpr_aan_cba19'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba20'] = ( isset( $input['wc_gdpr_aan_cba20'] ) && ! empty( $input['wc_gdpr_aan_cba20'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba21'] = ( isset( $input['wc_gdpr_aan_cba21'] ) && ! empty( $input['wc_gdpr_aan_cba21'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba22'] = ( isset( $input['wc_gdpr_aan_cba22'] ) && ! empty( $input['wc_gdpr_aan_cba22'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba23'] = ( isset( $input['wc_gdpr_aan_cba23'] ) && ! empty( $input['wc_gdpr_aan_cba23'] ) ) ? 1 : 0;
	    $valid['wc_gdpr_aan_cba24'] = ( isset( $input['wc_gdpr_aan_cba24'] ) && ! empty( $input['wc_gdpr_aan_cba24'] ) ) ? 1 : 0;
	    
	    $valid['wc_gdpr_aan_message'] = ( isset( $input['wc_gdpr_aan_message'] ) && ! empty( $input['wc_gdpr_aan_message'] ) ) ? $input['wc_gdpr_aan_message'] : false;
	    
	    $valid['wc_gdpr_aan_messagea'] = ( isset( $input['wc_gdpr_aan_messagea'] ) && ! empty( $input['wc_gdpr_aan_messagea'] ) ) ? $input['wc_gdpr_aan_messagea'] : false;
	    
	    $valid['wc_gdpr_aan_css'] = ( isset( $input['wc_gdpr_aan_css'] ) && ! empty( $input['wc_gdpr_aan_css'] ) ) ? $input['wc_gdpr_aan_css'] : 'css aqui';
	    
	    $valid['example_select'] = ( isset($input['example_select'] ) && ! empty( $input['example_select'] ) ) ? esc_attr($input['example_select']) : 1;
	    return $valid;
	}
	
	
	public function options_update() {
	
	    register_setting( $this->plugin_name, $this->plugin_name, array( $this, 'validate' ) );
	    
	}
	
	
	
	
	
}





























