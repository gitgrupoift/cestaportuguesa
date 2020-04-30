<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://meuppt.pt
 * @since      1.0.0
 *
 * @package    Wc_Gdpr_Aan
 * @subpackage Wc_Gdpr_Aan/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wc_Gdpr_Aan
 * @subpackage Wc_Gdpr_Aan/public
 * @author     Bnext1 e MeuPPT <geral@meuppt.pt>
 */
class Wc_Gdpr_Aan_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wc_Gdpr_Aan_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wc_Gdpr_Aan_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wc-gdpr-aan-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wc_Gdpr_Aan_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wc_Gdpr_Aan_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wc-gdpr-aan-public.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * Deal with placing messages all around WooCommerce pages.
	 *
	 * @since    1.0.0
	 */

	public function wc_gdpr_render_message_1() {
		// Before main content on shop pages
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb1'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_2() {
		// Below the shop page title
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb2'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_3() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb3'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_4() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb4'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_5() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb5'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_6() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb6'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}

	
	public function wc_gdpr_render_message_7() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb7'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_8() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb8'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_9() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb9'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_10() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb10'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}

	
	public function wc_gdpr_render_message_11() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb11'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_12() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb12'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	
	public function wc_gdpr_render_message_13() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb13'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_14() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb14'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_15() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb15'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_16() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb16'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	
	public function wc_gdpr_render_message_17() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb17'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_18() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb18'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}	
	
	public function wc_gdpr_render_message_19() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb19'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_20() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb20'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_21() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb21'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_22() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb22'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	
	public function wc_gdpr_render_message_23() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb23'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	public function wc_gdpr_render_message_24() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cb24'] == '1' ) {
			$message_gdpr = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_message'] . "</div>";
			echo $message_gdpr;
		}
	}
	
	// Second box messages
	
	public function wc_gdpr_render_messagea_1() {
		// Before main content on shop pages
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba1'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_2() {
		// Below the shop page title
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba2'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_3() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba3'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_4() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba4'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_5() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba5'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_6() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba6'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}

	
	public function wc_gdpr_render_messagea_7() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba7'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_8() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba8'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_9() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba9'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_10() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba10'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}

	
	public function wc_gdpr_render_messagea_11() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba11'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_12() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba12'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	
	public function wc_gdpr_render_messagea_13() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba13'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_14() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba14'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_15() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba15'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_16() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba16'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	
	public function wc_gdpr_render_messagea_17() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba17'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_18() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba18'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}	
	
	public function wc_gdpr_render_messagea_19() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba19'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_20() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba20'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_21() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba21'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_22() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba22'] == '1' ) {
			$message_gdpra = "<div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div>";
			echo $message_gdpra;
		}
	}
	
	
	public function wc_gdpr_render_messagea_23() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba23'] == '1' ) {
			$message_gdpra = "<article class='gdpr'><div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div></article>";
			echo $message_gdpra;
		}
	}
	
	public function wc_gdpr_render_messagea_24() {
		// Above shopping cart items
		$options = get_option($this->plugin_name);
		if ( $options['wc_gdpr_aan_cba24'] == '1' ) {
			$message_gdpra = "<article class='gdpr'><div class='wc-gdpr-textbox-1'>" . $options['wc_gdpr_aan_messagea'] . "</div></article>";
			echo $message_gdpra;
		}
	}
	
	
	public function wc_gdpr_custom_css() {
		?>
		<style>
			
			
			.wc-gdpr-textbox-1 {
				font-size: 0.9em;
				margin-top: 15px !important;
				margin-bottom: 15px !important;
			}			
			
			
			
		</style>
		
		<?php
	
	}	

}




















