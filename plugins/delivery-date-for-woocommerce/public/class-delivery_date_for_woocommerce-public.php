<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.pixlogix.com
 * @since      1.0.0
 *
 * @package    Delivery_date_for_woocommerce
 * @subpackage Delivery_date_for_woocommerce/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Delivery_date_for_woocommerce
 * @subpackage Delivery_date_for_woocommerce/public
 * @author     Pixlogix <support@pixlogix.com>
 */
class DDFW_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->ddfw_plugin_name = $plugin_name;
		$this->ddfw_version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->ddfw_plugin_name, plugin_dir_url( __FILE__ ) . 'css/delivery_date_for_woocommerce-public.css', array(), $this->ddfw_version, 'all' );
		wp_enqueue_style( 'jquery_ui_css', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css', array(), $this->ddfw_version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->ddfw_plugin_name, plugin_dir_url( __FILE__ ) . 'js/delivery_date_for_woocommerce-public.js', array( 'jquery' ), $this->ddfw_version, false );

	}
	public function ddfw_delivery_options( $checkout ) {

      $IsVirtual = array();
	  foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	  	if($cart_item['data']->is_virtual() == 1){
	      $IsVirtual [] = 1;
	  	}else{
	  		$IsVirtual [] = 0;
	  	}
	   }
	   	

		$arr =   array( 
			'Monday'    => get_option('ddfw_disable_monday'),
			'Tuesday'   => get_option('ddfw_disable_tuesday'),
			'Wednesday' => get_option('ddfw_disable_wednesday'),
			'Thursday'  => get_option('ddfw_disable_thursday'),
			'Friday'    => get_option('ddfw_disable_friday'),
			'Saturday'  => get_option('ddfw_disable_saturday'),
			'Sunday'    => get_option('ddfw_disable_sunday')
			);
		$string_version     = implode(",",$arr);
		$specific_day_arrs  =  explode(',', get_option('ddfw_specific_day'));
		
		$delivery_date_title = "Delivery Date";
		if(get_option('ddfw_delivery_date_title') != ""){
			$delivery_date_title = get_option('ddfw_delivery_date_title');
		}
		$delivery_date_option_title = "Select Your Delivery Date";
		if(get_option('ddfw_delivery_date_option_title') != ""){
			$delivery_date_option_title = get_option('ddfw_delivery_date_option_title');
		}
		
		$jquery_array = array();
		foreach ($specific_day_arrs as $cal_date_arr ){
			if(get_option('ddfw_disable_x_days') ==0 && get_option('ddfw_specific_day') == date("d-m-Y")):
				if(get_option('ddfw_specific_day') != date("d-m-Y")):
					$jquery_array[] =$cal_date_arr;
				endif;
			else:
				$jquery_array[] =$cal_date_arr;
			endif;
		  
		}
		$str = implode (",",$jquery_array);
		$str = preg_replace('/\s+/', '', $str);
		

		if(in_array('0',$IsVirtual)){
	   		
	   		if(get_option('ddfw_enable_delivery')=="1" && get_option('ddfw_required_delivery')=="1" ){
			woocommerce_form_field( 'ddfw_delivery_date', array(
			'type'              => 'text',
			'class'             => array('my-field-class form-row-wide'),
			'id'                => 'datepicker',
			'custom_attributes' => array('data-hide-day'=>get_option('ddfw_disable_x_days'),'custom-day-hide'=> $string_version ,'cal-day-hide'=> $str),
			'required'          =>  true,
			'label'             => __($delivery_date_title),
			'placeholder'       => __($delivery_date_option_title)
			),$checkout->get_value( 'ddfw_delivery_date' ));
			}elseif(get_option('ddfw_enable_delivery')=="1"){
				woocommerce_form_field( 'ddfw_delivery_date', array(
				'type'              => 'text',
				'class'             => array('my-field-class form-row-wide'),
				'id'                => 'datepicker',
				'custom_attributes' => array('data-hide-day'=>get_option('ddfw_disable_x_days'),'custom-day-hide'=> $string_version ,'cal-day-hide'=> $str),
				'required'          =>  false,
				'label'             => __($delivery_date_title),
				'placeholder'       => __($delivery_date_option_title)
				),$checkout->get_value( 'ddfw_delivery_date' ));
			}

	   	}elseif(get_option('ddfw_disable_virtual')==0){
	   		if(get_option('ddfw_enable_delivery')=="1" && get_option('ddfw_required_delivery')=="1" ){
			woocommerce_form_field( 'ddfw_delivery_date', array(
			'type'              => 'text',
			'class'             => array('my-field-class form-row-wide'),
			'id'                => 'datepicker',
			'custom_attributes' => array('data-hide-day'=>get_option('ddfw_disable_x_days'),'custom-day-hide'=> $string_version ,'cal-day-hide'=> $str),
			'required'          =>  true,
			'label'             => __($delivery_date_title),
			'placeholder'       => __($delivery_date_option_title)
			),$checkout->get_value( 'ddfw_delivery_date' ));
			}elseif(get_option('ddfw_enable_delivery')=="1"){
				woocommerce_form_field( 'ddfw_delivery_date', array(
				'type'              => 'text',
				'class'             => array('my-field-class form-row-wide'),
				'id'                => 'datepicker',
				'custom_attributes' => array('data-hide-day'=>get_option('ddfw_disable_x_days'),'custom-day-hide'=> $string_version ,'cal-day-hide'=> $str),
				'required'          =>  false,
				'label'             => __($delivery_date_title),
				'placeholder'       => __($delivery_date_option_title)
				),$checkout->get_value( 'ddfw_delivery_date' ));
			}
	   	}
    }
	public function ddfw_delivery_options_process() {
		global $woocommerce;
		if(get_option('ddfw_required_delivery') == "1"){
			$delivery_error_msg = "Plz Select Your Delivery Date";
			if(get_option('ddfw_delivery_error_msg') != ""){
				$delivery_error_msg = get_option('ddfw_delivery_error_msg');
			}
			if (empty($_POST['ddfw_delivery_date'])){
				wc_add_notice( '<strong class="p_note">'.$delivery_error_msg.'</strong>', 'error' );
			}
		}
	}
	public function ddfw_enabling_date_picker() {
		
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				if( is_admin() || ! is_checkout() ) return;
				wp_enqueue_script( 'jquery-ui-datepicker' );
			}
	
	}
	public function ddfw_add_delivery_date_to_order($order_id) {

		if ( isset( $_POST ['ddfw_delivery_day'] ) &&  '' != $_POST ['ddfw_delivery_day'] ) {
		add_post_meta( $order_id, '_ddfw_delivery_date',  sanitize_text_field( $_POST ['ddfw_delivery_day'] ) );
		}
		
		if ( isset( $_POST ['ddfw_delivery_date'] ) &&  '' != $_POST ['ddfw_delivery_date'] ) {
		add_post_meta( $order_id, '_ddfw_delivery_date',  sanitize_text_field( $_POST ['ddfw_delivery_date'] ) );
		}

	}
	public function ddfw_add_order_delivery_date_email($order, $sent_to_admin){
		
		$delivery_date = get_post_meta( $order->id, '_ddfw_delivery_date', true );
		echo '<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr>
			<th scope="row" colspan="2" style="text-align:left;border-top-width:4px;color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px">'.get_option("ddfw_delivery_date_email_title").'</th>
			<td style="text-align:left;border-top-width:4px;color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px"><span>'. $delivery_date .'</span></td>
			</tr></tbody></table>';
	}

}
