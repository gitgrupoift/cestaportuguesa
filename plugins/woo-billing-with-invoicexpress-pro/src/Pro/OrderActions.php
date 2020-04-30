<?php

namespace Webdados\InvoiceXpressWooCommerce\Pro;

use Webdados\InvoiceXpressWooCommerce\JsonRequest as JsonRequest;
use Webdados\InvoiceXpressWooCommerce\Plugin as Plugin;

/**
 * Order actions.
 *
 * @package Webdados
 * @since   2.0.0
 */
class OrderActions {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.4.0
	 * @param Plugin $plugin This plugin's instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'invoicexpress_woocommerce_after_document_issue', array( $this, 'update_order_status' ), 10, 2 );
		add_action( 'invoicexpress_woocommerce_before_document_email', array( $this, 'issue_automatic_receipt' ), 10, 2 );
		add_filter( 'invoicexpress_woocommerce_order_action_title', array( $this, 'order_action_title' ), 10, 4 );
		add_filter( 'invoicexpress_woocommerce_email_headers', array( $this, 'bcc_all_emails' ), 10, 3 );
		add_filter( 'invoicexpress_woocommerce_document_language', array( $this, 'document_language' ), 10, 2 );
		add_filter( 'invoicexpress_woocommerce_document_item_title', array( $this, 'document_item_title_with_meta' ), 10, 5 );
		add_filter( 'invoicexpress_woocommerce_document_item_unit', array( $this, 'document_item_unit' ), 9, 5 ); //We use a priority of 9 because developers may use the default of 10 and they should already have our value
		if ( get_option( 'hd_wc_ie_plus_prevent_issue_unknown_coupons' ) ) {
			//Unknow coupons
			add_filter( 'invoicexpress_woocommerce_prevent_document_issuing', array( $this, 'prevent_document_issuing_unknown_coupons', ), 10, 4 );
		}
		add_filter( 'invoicexpress_woocommerce_allow_ix_email', '__return_true' );
		add_action( 'invoicexpress_woocommerce_ix_email', array( $this, 'email_document_via_invoicexpress' ), 10, 7 );
		add_action( 'invoicexpress_woocommerce_hybrid_email', array( $this, 'email_document_via_hybrid' ), 10, 7 );
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'my_account_order_documents' ) );
		//Order type valid?
		add_filter( 'invoicexpress_woocommerce_is_valid_order_type', array( $this, 'is_valid_order_type' ), 10, 2 );
		//Subscriptions
		add_filter( 'wcs_renewal_order_meta', array( $this, 'wcs_filter_meta' ), 10, 3 );
		add_filter( 'wcs_resubscribe_order_meta', array( $this, 'wcs_filter_meta' ), 10, 3 );
		//Get client by ID
		add_filter( 'invoicexpress_woocommerce_get_client_info', array( $this, 'get_client_info_from_invoicexpress' ), 10, 2 );
	}

	/**
	 * Update order status after document issuing
	 *
	 * @param  int $order_id The order id
	 * @param  string    $document_type The document type
	 * @return void
	 */
	public function update_order_status( $order_id, $document_type = 'invoice' ) {
		if ( get_option( 'hd_wc_ie_plus_update_order_status' ) ) {
			$documents_status = array(
				'invoice'            => 'processing',
				'simplified_invoice' => 'processing',
				'invoice_receipt'    => 'completed',
				'vat_moss_invoice'   => 'processing',
				'receipt'            => 'completed',
				'cancel_document'    => 'cancelled',
				'proforma'           => 'on-hold',
				'quote'              => 'on-hold',
				'transport_guide'    => 'completed',
				'devolution_guide'   => 'completed',
			);
			if ( isset( $documents_status[ $document_type ] ) ) {
				$order_object = wc_get_order( $order_id );
				$order_object->update_status( $documents_status[ $document_type ] );
			}
		}
	}

	/**
	 * Issue the automatic receipt - If "Immediately after the invoice" is chosen
	 *
	 * @param  int $order_id The order id
	 * @return void
	 */
	public function issue_automatic_receipt( $order_id, $document_type = 'invoice' ) {
		if (
			get_option( 'hd_wc_ie_plus_automatic_receipt' )
			&&
			( get_option( 'hd_wc_ie_plus_automatic_receipt_state' ) == '' )
			&&
			(
				$document_type == 'invoice'
				||
				$document_type == 'simplified_invoice'
				||
				$document_type == 'vat_moss_invoice'
			) )
		{
			do_action( 'invoicexpress_woocommerce_do_automatic_receipt', $order_id );
		}
	}

	/**
	 * Filters the order action title
	 *
	 * @param  string    $title The title
	 * @param  \WC_Order $order_object The order
	 * @param  string    $document_type The document type
	 * @param  string    $action The action slug
	 * @return void
	 */
	public function order_action_title( $title, $order_object, $document_type, $action ) {
		switch ( $action ) {
			// Generate invoice.
			case 'hd_wc_ie_plus_generate_invoice':
				if ( get_option( 'hd_wc_ie_plus_automatic_receipt' ) ) {
					$title = esc_html(
						sprintf(
							/* translators: %1$s issue document name string, %2$s: PDF string */
							__( '%1$s (%2$s) and set as paid', 'woo-billing-with-invoicexpress' ),
							sprintf(
								/* translators: %s: document type */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'Invoice', 'woo-billing-with-invoicexpress' )
							),
							__( 'PDF', 'woo-billing-with-invoicexpress' )
						)
					);
				}
				break;
			// Generate Simplified invoice.
			case 'hd_wc_ie_plus_generate_simplified_invoice':
				if ( get_option( 'hd_wc_ie_plus_automatic_receipt' ) ) {
					$title = esc_html(
						sprintf(
							/* translators: %1$s issue document name string, %2$s: PDF string */
							__( '%1$s (%2$s) and set as paid', 'woo-billing-with-invoicexpress' ),
							sprintf(
								/* translators: %s: type of document */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'Simplified invoice', 'woo-billing-with-invoicexpress' )
							),
							__( 'PDF', 'woo-billing-with-invoicexpress' )
						)
					);
				}
				break;
			// Generate VAT MOSS invoice.
			case 'hd_wc_ie_plus_generate_vat_moss_invoice':
				if ( get_option( 'hd_wc_ie_plus_automatic_receipt' ) ) {
					$title = esc_html(
						sprintf(
							/* translators: %1$s issue document name string, %2$s: PDF string */
							__( '%1$s (%2$s) and set as paid', 'woo-billing-with-invoicexpress' ),
							sprintf(
								/* translators: %s: type of document */
								__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
								__( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' )
							),
							__( 'PDF', 'woo-billing-with-invoicexpress' )
						)
					);
				}
				break;
		}

		return $title;
	}

	/**
	 * Bcc all document emails
	 *
	 * @param  array $headers The email headers
	 * @return array
	 */
	public function bcc_all_emails( $headers, $order_object, $type ) {
		if ( get_option( 'hd_wc_ie_plus_email_bcc' ) ) {
			$headers[] = sprintf(
				'Bcc: %s',
				get_option( 'hd_wc_ie_plus_email_bcc' )
			);
		}
		return $headers;
	}

	/**
	 * Returns the document language
	 *
	 * @param  string $language The default language
	 * @param  \WC_Order $order_object The order
	 * @return string
	 */
	public function document_language( $language, $order_object ) {
		if ( get_option( 'hd_wc_ie_plus_document_language' ) ) {
			$language = get_option( 'hd_wc_ie_plus_document_language' );
		}
		return $language;
	}

	/**
	 * Prevent document issuing for unknown coupon types
	 *
	 * @since 2.1.4
	 * @param  WC_Order $order_object The order.
	 * @param  string $document_type Document type
	 * @param  array $data Document data to send to InvoiceXpress
	 * @return array
	 */
	function prevent_document_issuing_unknown_coupons( $result, $order_object, $document_type, $data ) {
		if ( version_compare( WC_VERSION, '3.7.0', '<' ) ) {
			$used_coupons = $order_object->get_used_coupons();
		} else {
			$used_coupons = $order_object->get_coupon_codes();
		}
		foreach ( $used_coupons as $code ) {
			if ( ! $code ) {
				continue;
			}
			$coupon  = new \WC_Coupon( $code );
			$allowed_coupon_types = apply_filters( 'invoicexpress_woocommerce_allowed_coupon_types', array( 'percent', 'fixed_cart', 'fixed_product' ) );
			if ( ! in_array( $coupon->get_discount_type(), $allowed_coupon_types ) ) {
				return array(
					'prevent' => true,
					'message' => sprintf(
						'Order #%1$d has unknown coupon type (%2$s)',
						$order_object->get_id(),
						$coupon->get_discount_type()
					)
				);
			}
		}
		return $result;
	}

	/*
	 * Order item title with full meta
	 *
	 * @since  2.1.4.3
	 * @param  string $title The item title
	 * @param  object $item The order item
	 * @param  object $product The product
	 * @param  object $order_object The order
	 * @param  string $type The document type
	 * @return string The item title
	 */
	public function document_item_title_with_meta( $title, $item, $product, $order_object, $type ) {
		if ( apply_filters( 'invoicexpress_woocommerce_document_item_title_with_meta', true ) ) {
			$title = $item->get_name();
			if ( $metas = $item->get_formatted_meta_data() ) {
				//If attributes are already included on $item->get_name() they will not be showned here
				foreach ( $metas as $meta ) {
					if ( trim( $meta->display_key ) != '' && trim( strip_tags( $meta->display_value ) ) != '' ) {
						$title .= PHP_EOL . trim( $meta->display_key ).': '.trim( strip_tags( $meta->display_value ) ); //PHP_EOL not working on InvoiceXpress PDF files (?)
					}
				}
			}
		}
		return $title;
	}

	/*
	 * Order item unit of measurement
	 *
	 * @since  2.2.0
	 * @param  string $unit The item default unit
	 * @param  object $item The order item
	 * @param  object $product The product (or false)
	 * @param  object $order_object The order
	 * @param  string $type The document type
	 * @return string The item title
	 */
	public function document_item_unit( $unit, $item, $product, $order_object, $type ) {
		if ( $product && $product->is_virtual() ) {
			return get_option( 'hd_wc_ie_plus_virtual_product_unit', 'service' );
		} else {
			return get_option( 'hd_wc_ie_plus_product_unit', 'unit' );
		}
	}
	
	/*
	 * Email document using InvoiceXpress
	 *
	 * @since  2.4.0
	 * @param  string $type The document type
	 * @param  object $order_object The order
	 * @param  int    $order_id_invoicexpress Document ID on InvoiceXpress
	 * @param  string $email The email address
	 * @param  string $subject The email subject
	 * @param  string $heading The email heading
	 * @param  string $body The email body
	 */
	public function email_document_via_invoicexpress( $type, $order_object, $order_id_invoicexpress, $email, $subject, $heading, $body ) {
		
		switch( $type ) {
			case 'transport_guide':
				$endpoint = 'transports';
				break;
			case 'devolution_guide':
				$endpoint = 'devolutions';
				break;
			case 'receipt':
				//Support email 2019-11-05
				$endpoint = 'invoices';
				break;
			default:
				$endpoint = $type.'s';
				break;
		}

		$params = array(
			'request' => $endpoint . '/' . $order_id_invoicexpress . '/email-document.json',
			'args'    => array(
				'message' => array(
					'client'  => array(
						'email'   => $email,
						'save'    => '0',
					),
					'subject' => $subject,
					'body'    => str_replace( PHP_EOL, '', '<h1>'.$heading.'</h1>'.wpautop( trim( $body ) ) ),
					'bcc'     => get_option( 'hd_wc_ie_plus_email_bcc' ),
				),
			),
		);
		if ( get_option( 'hd_wc_ie_plus_ix_email_logo' ) ) {
			$params['args']['message']['logo'] = '1';
		}
		$json_request = new JsonRequest( $params );
		$return = $json_request->putRequest();
		if ( ! $return['success'] ) {
			$codeStr    = __( 'Code', 'woo-billing-with-invoicexpress' );
			$messageStr = __( 'Message', 'woo-billing-with-invoicexpress' );
			/* Add notice */
			$error_notice = sprintf(
				'<strong>%s:</strong> %s',
				__( 'InvoiceXpress error while sending email', 'woo-billing-with-invoicexpress' ),
				$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message']
			);
			/*if ( $mode == 'manual' ) { //We need to get the $mode here
				Notices::add_notice(
					$error_notice,
					'error'
				);
			}*/
			$debug = $notice;
			$debug .= ' | params: '.serialize( $params );
			do_action( 'invoicexpress_woocommerce_error', $debug, $order_object );
		}
		return false;
	}
	
	/*
	 * Set filters so that email is sent by WordPress but with the InvoiceXpress document link instead of an attachment
	 *
	 * @since  2.4.0
	 * @param  string $type The document type
	 * @param  object $order_object The order
	 * @param  int    $order_id_invoicexpress Document ID on InvoiceXpress
	 * @param  string $email The email address
	 * @param  string $subject The email subject
	 * @param  string $heading The email heading
	 * @param  string $body The email body
	 */
	public function email_document_via_hybrid( $type, $order_object, $order_id_invoicexpress, $email, $subject, $heading, $body ) {
		//Add link to the end of the message
		add_action( 'invoicexpress_woocommerce_after_email_body', array( $this, 'email_document_via_hybrid_document_link' ), 10, 3 );
		//CSS for button - from email-styles.php
		add_filter( 'woocommerce_email_styles', array( $this, 'email_document_via_hybrid_css' ) );
	}
	public function email_document_via_hybrid_document_link( $order_object, $type, $order_id_invoicexpress ) {
		echo sprintf(
			'<p id="ix_download"><a href="%1$s">%2$s</a></p>',
			esc_url( $order_object->get_meta( 'hd_wc_ie_plus_'.$type.'_permalink' ) ),
			sprintf(
				/* translators: %1$s: document name, %2$s: document number */
				__( 'Download %1$s %2$s', 'woo-billing-with-invoicexpress' ),
				$this->plugin->type_names[$type],
				$order_object->get_meta( 'hd_wc_ie_plus_'.$type.'_sequence_number' )
			)
		);
	}
	public function email_document_via_hybrid_css( $css ) {
		$bg   = get_option( 'woocommerce_email_base_color' );
		$text = wc_light_or_dark( $bg, '#202020', '#ffffff' );
		$css .= apply_filters( 'invoicexpress_woocommerce_hybrid_email_css', 'p#ix_download {
			text-align: center;
			margin: 1em;
			margin-top: 2em;
		}
		p#ix_download a {
			display: inner-block;
			padding: 1em 2em;
			background-color: '.esc_attr( $bg ).';
			color: '.esc_attr( $text ).';
			font-weight: bold;
			border-radius: 3px;
			text-decoration: none;
		}
		p#ix_download a:hover {
			background-color: '.esc_attr( wc_hex_darker( $bg, 20 ) ).';
		}' );
		return $css;
	}
	
	/*
	 * Show documents list on My Account > Order
	 *
	 * @since  2.4.0
	 * @param  object $order_object The order
	 */
	public function my_account_order_documents( $order_object ) {
		if ( get_option( 'hd_wc_ie_plus_show_documents_my_account', 1 ) ) {
			$docs = array();
			foreach ( $this->plugin->type_names as $key => $value ) {
				if ( $pdf_link = $order_object->get_meta( 'hd_wc_ie_plus_'.$key.'_pdf' ) ) {
					$docs[$key] = $pdf_link;
				} else {
					if ( $permalink = $order_object->get_meta( 'hd_wc_ie_plus_'.$key.'_permalink' ) ) {
						$docs[$key] = $permalink;
					}
				}
			}
			if ( count( $docs ) > 0 ) {
				?>
				<div id="ix_documents_list">
					<h2><?php _e( 'Billing documents', 'woo-billing-with-invoicexpress' ); ?></h2>
					<ul>
					<?php
					foreach ( $docs as $key => $value ) {
						?>
						<li>
							<a href="<?php echo esc_url( $value ); ?>" target="_blank"><?php echo esc_html( $this->plugin->type_names[$key] ); ?></a>
						</li>
						<?php
					}
					?>
					</ul>
				</div>
				<?php
			}
		}
	}

	/**
	 * Check if order type is valid for invoicing
	 *
	 * @since  2.5.2
	 * @return array
	 */
	public function is_valid_order_type( $bool, $order_object ) {
		if ( in_array(
			get_class( $order_object ),
			apply_filters( 'invoicexpress_woocommerce_valid_order_classes',
				array(
					// WooCommerce regular Order
					'WC_Order',
					// WooCommerce Admin override Order
					'Automattic\WooCommerce\Admin\Overrides\Order'
				)
			)
		) ) return true;
		return false;
	}
	
	/*
	 * Exclude our fields from Subscriptions meta copy
	 *
	 * @since  2.5.2
	 * @param  object $order_object The order
	 */
	public function wcs_filter_meta( $meta, $to_order, $from_order ) {
		$fields = array(
			//'_billing_VAT_code', //We actually need the VAT to be copied over
		);
		foreach ( $meta as $key => $value ) {
			if ( isset( $value['meta_key'] ) ) {
				if ( in_array( $value['meta_key'], $fields ) ) {
					unset( $meta[$key] );
				} else {
					//Our fields starting by hd_wc_ie - Should not be needed because Subscriptions are not suposed to have invoices
					if ( strpos( $value['meta_key'], 'hd_wc_ie' ) !== false && strpos( $value['meta_key'], 'hd_wc_ie' ) === 0 ) {
						unset( $meta[$key] );
						break;
					}
				}
			}
		}
		return $meta;
	}

	/*
	 * Extracts customer client_id from order and searches it on InvoiceXpress 
	 *
	 * @since  2.6.1
	 * @param  bool   false
	 * @param  object $order_object The order
	 */
	public function get_client_info_from_invoicexpress( $bool, $order_object ) {
		if ( $vat = $order_object->get_meta( '_billing_VAT_code' ) ) {
			if ( $user_id = $order_object->get_customer_id() ) {
				if ( $customer = new \WC_Customer( $user_id ) ) {
					if ( $customer->get_id() ) {
						if ( ( $client_id = $customer->get_meta( 'hd_wc_ie_plus_client_id' ) ) && ( $client_code = $customer->get_meta( 'hd_wc_ie_plus_client_code' ) ) ) {
							if ( $client_info = $this->get_client_from_invoicexpress_by_id_and_vat( $client_id, $vat ) ) {
								return $client_info;
							}
						}
					}
				}
			}
		}
		return false;
	}

	/*
	 * Searches InvoiceXpress API for the client by ID, and then checks if the VAT is the same. 
	 *
	 * @since  2.6.1
	 * @param  int    $client_id The client ID to search for
	 * @param  string $vat The client VAT to match
	 */
	public function get_client_from_invoicexpress_by_id_and_vat( $client_id, $vat ) {
		$params = array(
			'request' => 'clients/'.$client_id.'.json',
		);
		$json_request = new JsonRequest( $params );
		$return = $json_request->getRequest();
		if ( $return['success'] ) {
			$client_info = $return['object']->client;
			if ( trim( $client_info->fiscal_id ) == $vat ) {
				return $client_info;
			}
		}
		return false;
	}

}
