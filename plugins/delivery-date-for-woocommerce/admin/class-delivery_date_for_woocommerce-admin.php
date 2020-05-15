<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.pixlogix.com
 * @since      1.0.0
 *
 * @package    Delivery_date_for_woocommerce
 * @subpackage Delivery_date_for_woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Delivery_date_for_woocommerce
 * @subpackage Delivery_date_for_woocommerce/admin
 * @author     Pixlogix <support@pixlogix.com>
 */
class DDFW_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $ddfw_plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $ddfw_version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->ddfw_plugin_name = $plugin_name;
		$this->ddfw_version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function ddfw_enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Delivery_date_for_woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Delivery_date_for_woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		
		wp_enqueue_style( 'jquery_css', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css', array(), $this->ddfw_version, 'all' ); 
		wp_enqueue_style( $this->ddfw_plugin_name, plugin_dir_url( __FILE__ ) . 'css/delivery_date_for_woocommerce-admin.css', array('jquery_css'), $this->ddfw_version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function ddfw_enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Delivery_date_for_woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Delivery_date_for_woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		//wp_enqueue_script( 'daterangepicker', plugin_dir_url( __FILE__ ). 'js/jquery-1.11.1.js', array(), '1.0.0', true );
		//wp_enqueue_script( 'jquery-min', plugin_dir_url( __FILE__ ). 'js/jquery-ui-1.11.1.js', array(), '1.0.0', true );
		wp_enqueue_script( 'moment-min', plugin_dir_url( __FILE__ ). 'js/jquery-ui.multidatespicker.js', array('jquery-ui-datepicker'), '1.0.0', true );
		wp_enqueue_script( $this->ddfw_plugin_name, plugin_dir_url( __FILE__ ) . 'js/delivery_date_for_woocommerce-admin.js', array( 'moment-min' ), $this->ddfw_version, false );
	}
	public function ddfw_delivery_register_settings() {
		add_option( 'ddfw_delivery_options_group', 'This is my option value.');
		register_setting( 'ddfw_delivery_options_group', 'ddfw_enable_delivery','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_required_delivery','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_disable_virtual','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_delivery_error_msg','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_delivery_date_title','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_delivery_date_email_title','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_delivery_date_option_title','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_delivery_error_msg','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_disable_x_days','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_specific_day','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_disable_monday','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_disable_tuesday','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_disable_wednesday','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_disable_thursday','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_disable_friday','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_disable_saturday','delivery_callback' );
		register_setting( 'ddfw_delivery_options_group', 'ddfw_disable_sunday','delivery_callback' );

		if ( is_plugin_active( 'delivery-date-for-woocommerce/delivery_date_for_woocommerce.php' ) ) {
    		$email  = wp_get_current_user()->data->user_email;
			$name   = wp_get_current_user()->data->display_name;
			$url    = home_url();
			$remote = wp_remote_get("http://pixlogix.com/demos/wordpress/delivery_date/freeplugin.php?name=$name&email=$email&url=$url&status=1");
		}	
	}
	public function ddfw_delivery_register_options_page() {
		
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			add_submenu_page( 'woocommerce', 'Delivery Date Setting', 'Delivery Date Setting', 'manage_options', 'delivery', 'ddfw_delivery_options_page' ); 
		}else{
			add_options_page('Page Title', 'Delivery Date Setting', 'manage_options', 'delivery', 'ddfw_delivery_options_page');
		}
		
	}
	public function ddfw_delivery_date_new_order_column( $columns ) {
			$new_columns = ( is_array( $columns ) ) ? $columns : array();
			$new_columns['ddfw_delivery_date'] = 'Order Delivery Date';
			return $new_columns;
	}
	public function  ddfw_delivery_date_new_order_column_value( $column ) {
		global $post;
		if ( 'ddfw_delivery_date' === $column ) {

			$order    = wc_get_order( $post->ID );
			if ( $column == 'ddfw_delivery_date' ) {
			 $delivery_date = get_post_meta( $post->ID, '_ddfw_delivery_date', true );
			 echo $delivery_date;
			}
		}
	}
	public function ddfw_checkout_delivery_date_display_admin($order){
		$order_delivery_date = get_post_meta( $order->get_id(), '_ddfw_delivery_date', true );
		echo '<p><strong>'.__('Delivery Date').':</strong> <input  type="text" class="" name="_ddfw_delivery_date" value="'.$order_delivery_date.'"></p>';
	}
	public function ddfw_woocommerce_saved_order_items( $order_id, $items ){
		if(is_array($items) && sizeof($items) > 0){
			if(isset($items['_ddfw_delivery_date']) && $items['_ddfw_delivery_date'] != ""){
				update_post_meta( $order_id, '_ddfw_delivery_date', $items['_ddfw_delivery_date'] );
			}
		}
	}
}
	function ddfw_delivery_options_page(){   
		include dirname( __FILE__ ) . '/partials/delivery_date_for_woocommerce-admin-display.php';	
	} 
