<?php
namespace Webdados\InvoiceXpressWooCommerce\Modules\Vat;

/* WooCommerce CRUD ready */

class VatController {

	/**
	 * The plugin's instance.
	 *
	 * @since  2.3.2
	 * @access protected
	 * @var    Plugin
	 */
	protected $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.3.2
	 * @param Plugin $plugin This plugin's instance.
	 */
	public function __construct( \Webdados\InvoiceXpressWooCommerce\Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {

		if ( get_option( 'hd_wc_ie_plus_vat_field' ) ) {

			add_filter(
				'woocommerce_checkout_fields', array(
					$this,
					'woocommerce_checkout_fields',
				),
				50 //After AELIA
			);

			add_action(
				'woocommerce_after_edit_address_form_billing', array(
					$this,
					'VAT_field_user_meta_keys',
				)
			);

			add_action(
				'woocommerce_customer_save_address', array(
					$this,
					'woocommerce_customer_save_address',
				), 10, 2
			);

			add_filter(
				'woocommerce_customer_meta_fields', array(
					$this,
					'woocommerce_customer_meta_fields',
				), 10, 1
			);

			add_filter(
				'woocommerce_admin_billing_fields', array(
					$this,
					'custom_billing_fields',
				), 60, 1 //yith-woocommerce-checkout-manager uses 50
			);

			add_filter(
				'woocommerce_api_customer_billing_address', array(
					$this,
					'api_custom_billing_fields',
				), 10, 1
			);

			add_filter(
				'woocommerce_api_customer_response', array(
					$this,
					'api_custom_billing_fields2',
				), 10, 2
			);

			add_filter(
				'woocommerce_found_customer_details', array(
					$this,
					'api_custom_billing_fields3',
				), 10, 3
			);

			add_action(
				'woocommerce_checkout_update_order_meta', array(
					$this,
					'vat_checkout_field_update_order_meta_frontend',
				), 100, 2
			);

			add_action(
				'woocommerce_process_shop_order_meta', array(
					$this,
					'VAT_checkout_field_update_order_meta',
				)
			);

			add_action(
				'woocommerce_checkout_process', array(
					$this,
					'validate_vat_frontend',
				), 1000
			);

			add_action(
				'admin_enqueue_scripts', array(
					$this,
					'enqueue_scripts',
				)
			);

			add_action(
				'wp_enqueue_scripts', array(
					$this,
					'enqueue_styles',
				)
			);

			add_filter(
				'woocommerce_email_order_meta_keys', array(
					$this,
					'add_vat_custom_meta',
				)
			);
		}

		// Order observations.
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'add_order_observations' ) );

		// Save observations.
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_order_observations' ), 10, 1 );
	}

	public function enqueue_scripts() {
		global $post_type, $post;
		if ( $post_type && $post && $post_type == 'shop_order' ) {
			$order_object = wc_get_order( $post->ID );
			wp_register_script( 'hd_wc_ie_order', plugins_url( 'assets/js/order.js', INVOICEXPRESS_WOOCOMMERCE_PLUGIN_FILE ), array( 'jquery' ), INVOICEXPRESS_WOOCOMMERCE_VERSION, true );
			wp_localize_script( 'hd_wc_ie_order', 'hd_wc_ie_order', array(
				'default_refund_reason' => $this->plugin->get_translated_option( 'hd_wc_ie_plus_refund_automatic_message', null, $order_object ),
			) );
			wp_enqueue_script( 'hd_wc_ie_order' );
		}
	}

	public function enqueue_styles() {
		wp_enqueue_style( 'invoicexpress-woocommerce-main-css', plugins_url( 'assets/css/main.css', INVOICEXPRESS_WOOCOMMERCE_PLUGIN_FILE ), array(), INVOICEXPRESS_WOOCOMMERCE_VERSION );
	}

	// Adds pickup location to metakeys
	public function add_vat_custom_meta( $keys ) {
		if ( apply_filters( 'invoicexpress_woocommerce_add_vat_to_email', true ) ) {
			$keys[ __( 'VAT number', 'woo-billing-with-invoicexpress' ) ] = '_billing_VAT_code';
			return $keys;
		}
	}

	/**
	 * Show the VAT field in the checkout according to the settings.
	 *
	 * @since  2.0.0 Code review and fix support to EU VAT Assistant.
	 * @since  1.0.0
	 * @param  array $fields The checkout fields.
	 * @return array
	 */
	public function woocommerce_checkout_fields( $fields ) {
		global $current_user;

		if ( apply_filters( 'invoicexpress_woocommerce_external_vat', false ) ) {
			//Aelia WooCommerce EU VAT Assistant active? make it required if needed
			if ( isset( $fields['billing']['vat_number'] ) && get_option( 'hd_wc_ie_plus_vat_field_mandatory' ) ) {
				$fields['billing']['vat_number']['required'] = true;
				return $fields;
			}
			//WooCommerce EU VAT Field active? make it required if needed - Not working because "WooCommerce EU VAT Field" doesn't use the right way of adding fields
			//if ( isset( $fields['billing']['_vat_number'] ) && get_option( 'hd_wc_ie_plus_vat_field_mandatory' ) ) {
			//	$fields['billing']['_vat_number']['required'] = true;
			//	return $fields;
			//}
			return $fields;
		}

		$fields['billing']['billing_VAT_code'] = array(
			'label'    => __( 'VAT number', 'woo-billing-with-invoicexpress' ),
			'required' => ( get_option( 'hd_wc_ie_plus_vat_field_mandatory' ) == 1 ),
			'type'     => 'text',
			'class'    => array(
				'form-row-wide',
			),
			'clear'    => true,
			'default'  => get_user_meta( $current_user->ID, 'billing_VAT_code', true ),
			'priority' => 120,
		);

		return $fields;
	}

	/**
	 * Show the VAT field in the customer meta fields according to the settings. (wp-admin edit user)
	 *
	 * @since  2.0.0 Code review.
	 * @since  1.0.0
	 * @param  array $fields The fields.
	 * @return array
	 */
	public function woocommerce_customer_meta_fields( $fields ) {

		$fields['billing']['fields']['billing_VAT_code'] = array(
			'label'       => __( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ).' - '.__( 'VAT number', 'woo-billing-with-invoicexpress' ),
			'required'    => ( get_option( 'hd_wc_ie_plus_vat_field_mandatory' ) == 1 ),
			'type'        => 'text',
			'description' => __( 'User VAT number', 'woo-billing-with-invoicexpress' ),
		);

		return $fields;
	}

	/*  wp-admin edit order */
	public function custom_billing_fields( $fields ) {
		global $post;
		$order_object = wc_get_order( $post->ID );
		$custom_attributes = array();
		if ( ( $client_id = $order_object->get_meta( 'hd_wc_ie_plus_client_id' ) ) && ( $client_code = $order_object->get_meta( 'hd_wc_ie_plus_client_code' ) ) ) {
			$custom_attributes['readonly'] = 'readonly';
		}
		$fields['VAT_code'] = array(
			'label'    => __( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ).' - '.__( 'VAT number', 'woo-billing-with-invoicexpress' ),
			'required' => ( get_option( 'hd_wc_ie_plus_vat_field_mandatory' ) == 1 ),
			'type'     => 'text',
			'class'    => 'short',
			'wrapper_class' => 'form-field form-field-wide',
			'custom_attributes' => $custom_attributes,
		);
		return $fields;
	}

	public function api_custom_billing_fields( $fields ) {
		$fields[] = 'billing_VAT_code';

		return $fields;
	}

	public function api_custom_billing_fields2( $customer_data, $customer ) {
		$customer_data['billing_address']['VAT_code'] = $customer->VAT_code;

		return $customer_data;
	}

	public function api_custom_billing_fields3( $customer_data, $user_id, $type_to_load ) {
		$vat = get_user_meta( $user_id, 'billing_VAT_code', true );

		$customer_data[ $type_to_load . '_VAT_code' ] = $vat;

		return $customer_data;
	}

	/* My account - edit address */
	public function VAT_field_user_meta_keys() {
		global $current_user;

		echo sprintf(
			'<p class="form-row form-row-wide"><label for="_billing_VAT_code">%1$s:</label><span class="woocommerce-input-wrapper"><input id="_billing_VAT_code" name="_billing_VAT_code" type="text" value="%2$s" class="input-text"/></span><p>',
			__( 'VAT number', 'woo-billing-with-invoicexpress' ),
			get_user_meta( $current_user->ID, 'billing_VAT_code', true )
		);
	}

	public function woocommerce_customer_save_address( $user_id, $load_address ) {
		if ( $load_address == 'billing' && isset( $_POST['_billing_VAT_code'] ) ) {
			if ( isset( $_POST['billing_country'] ) && $_POST['billing_country'] == 'PT' && ! empty( $_POST['_billing_VAT_code'] ) ) {
				if ( ! self::validate_portuguese_vat( $_POST['_billing_VAT_code'] ) ) {
					wc_add_notice( __( 'Invalid Portuguese VAT number.', 'woo-billing-with-invoicexpress' ), 'error' );
					return;
				}
			}
			update_user_meta( $user_id, 'billing_VAT_code', sanitize_text_field( $_POST['_billing_VAT_code'] ) );
		}
	}

	public function VAT_checkout_field_update_order_meta( $order_id ) {
		if ( isset( $_POST['_billing_VAT_code'] ) ) {
			$order_object = wc_get_order( $order_id );
			$order_object->update_meta_data( '_billing_VAT_code', sanitize_text_field( $_POST['_billing_VAT_code'] ) );
			$order_object->save();
		}
	}

	public function vat_checkout_field_update_order_meta_frontend( $order_id, $post ) {
		$updated = false;
		$order_object = wc_get_order( $order_id );

		if ( isset( $_POST['billing_VAT_code'] ) && ! empty( $_POST['billing_VAT_code'] ) ) {
			$order_object->update_meta_data( '_billing_VAT_code', sanitize_text_field( $_POST['billing_VAT_code'] ) );
			$updated = true;
		}

		// NIF (Num. de Contribuinte Português) for WooCommerce.
		if ( ! $updated && isset( $_POST['billing_nif'] ) && ! empty( $_POST['billing_nif'] ) ) {
			$order_object->update_meta_data( '_billing_VAT_code', sanitize_text_field( $_POST['billing_nif'] ) );
			$updated = true;
		}
		
		$order_object->save();
	}

	public function validate_vat_frontend() {

		// Check if set, if its not set add an error.
		if ( isset( $_POST['billing_VAT_code'] ) && ! empty( $_POST['billing_VAT_code'] ) && isset( $_POST['billing_country'] ) && $_POST['billing_country'] == 'PT' ) {
			if ( ! self::validate_portuguese_vat( $_POST['billing_VAT_code'] ) ) {
				wc_add_notice( __( 'Invalid Portuguese VAT number.', 'woo-billing-with-invoicexpress' ), 'error' );
			}
		}

		$required = false;
		if ( get_option( 'hd_wc_ie_plus_vat_field_mandatory' ) ) {
			$required = true;
		}

		if ( isset( $_POST['billing_VAT_code'] ) && ! $_POST['billing_VAT_code'] && $required ) {
			wc_add_notice( __( 'VAT is a required field.', 'woo-billing-with-invoicexpress' ), 'error' );
		}
	}

	public static function validate_portuguese_vat( $vat ) {
		/*
		 * Based on this rules (in portuguese):
		 * https://pt.wikipedia.org/wiki/N%C3%BAmero_de_identifica%C3%A7%C3%A3o_fiscal
		 */

		$vat = trim( $vat );

		if ( ! is_numeric( $vat ) ) {
			return false;
		}

		if ( strlen( $vat ) != 9 ) {
			return false;
		}

		$error = 0;

		if (
			substr( $vat, 0, 1 ) != '1' &&
			substr( $vat, 0, 1 ) != '2' &&
			substr( $vat, 0, 1 ) != '3' &&
			substr( $vat, 0, 2 ) != '45' &&
			substr( $vat, 0, 1 ) != '5' &&
			substr( $vat, 0, 1 ) != '6' &&
			substr( $vat, 0, 2 ) != '70' &&
			substr( $vat, 0, 2 ) != '71' &&
			substr( $vat, 0, 2 ) != '72' &&
			substr( $vat, 0, 2 ) != '74' &&
			substr( $vat, 0, 2 ) != '75' &&
			substr( $vat, 0, 2 ) != '77' &&
			substr( $vat, 0, 2 ) != '78' &&
			substr( $vat, 0, 2 ) != '79' &&
			substr( $vat, 0, 1 ) != '8' &&
			substr( $vat, 0, 2 ) != '90' &&
			substr( $vat, 0, 2 ) != '91' &&
			substr( $vat, 0, 2 ) != '98' &&
			substr( $vat, 0, 2 ) != '99'
		) {
			$error = 1;
		}
		$check1 = substr( $vat, 0, 1 ) * 9;
		$check2 = substr( $vat, 1, 1 ) * 8;
		$check3 = substr( $vat, 2, 1 ) * 7;
		$check4 = substr( $vat, 3, 1 ) * 6;
		$check5 = substr( $vat, 4, 1 ) * 5;
		$check6 = substr( $vat, 5, 1 ) * 4;
		$check7 = substr( $vat, 6, 1 ) * 3;
		$check8 = substr( $vat, 7, 1 ) * 2;

		$total = $check1 + $check2 + $check3 + $check4 + $check5 + $check6 + $check7 + $check8;

		$totalDiv11  = $total / 11;
		$modulusOf11 = $total - intval( $totalDiv11 ) * 11;
		if ( $modulusOf11 == 1 || $modulusOf11 == 0 ) {
			$check = 0;
		} else {
			$check = 11 - $modulusOf11;
		}

		$lastDigit = substr( $vat, 8, 1 ) * 1;
		if ( $lastDigit != $check ) {
			$error = 1;
		}

		if ( $error == 1 ) {
			return false;
		}

		return true;
	}

	public static function portugueseVATExemption( $vat ) {
		/*
		 * Based on this rules (in portuguese):
		 * https://pt.wikipedia.org/wiki/N%C3%BAmero_de_identifica%C3%A7%C3%A3o_fiscal
		 */

		$exempt = true;

		if ( empty( $vat ) ) {
			$exempt = false;
		} elseif ( substr( $vat, 0, 1 ) == '1' || substr( $vat, 0, 1 ) == '2' ||
		substr( $vat, 0, 1 ) == '3' || substr( $vat, 0, 1 ) == '5' ||
		substr( $vat, 0, 2 ) == '45' ) {
			$exempt = false;
		}

		return $exempt;

	}

	public function add_order_observations() {
		global $post;
		$order_object = wc_get_order( $post->ID );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;

		woocommerce_wp_textarea_input(
			array(
				'id'            => '_document_observations',
				'label'         => __( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ).' - '.__( 'Document observations', 'woo-billing-with-invoicexpress' ),
				'placeholder'   => __( 'Observations to be inserted into InvoiceXpress documents', 'woo-billing-with-invoicexpress' ),
				'class'         => 'widefat',
				'wrapper_class' => 'form-field form-field-wide',
			)
		);
	}

	public function save_order_observations( $post_id ) {
		$order_object = wc_get_order( $post_id );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;

		if ( isset( $_POST['_document_observations'] ) ) {
			$order_object->update_meta_data( '_document_observations', $_POST['_document_observations'] );
			$order_object->save();
		}
	}
}
