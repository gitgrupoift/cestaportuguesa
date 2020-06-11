<?php

namespace Webdados\InvoiceXpressWooCommerce;

use Webdados\InvoiceXpressWooCommerce\JsonRequest as JsonRequest;

/* WooCommerce CRUD ready */
/* JSON API ready */

class BaseController {

	/**
	 * The plugin's instance.
	 *
	 * @since  2.0.4
	 * @access protected
	 * @var    Plugin
	 */
	protected $plugin;

	/**
	 * Strings to find/replace in subjects/headings.
	 *
	 * @var array
	 */
	protected $placeholders = array();

	/**
	 * Documents validity in days.
	 *
	 * @var int
	 */
	protected $validity_invoicing_docs = 30;
	protected $validity_guides_docs    = 30;
	protected $validity_quotes_docs    = 30;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.0.4 Add plugin instance parameter.
	 * @since 1.0.0
	 * @param Plugin $plugin This plugin's instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->placeholders = array(
			'{site_title}'    => trim( $this->get_blogname() ),
			'{order_date}'    => '',
			'{order_number}'  => '',
			'{customer_name}' => '',
		);
	}

	/**
	 * Gets order items for the document
	 *
	 * @since 2.1.0
	 * @param WC_Order $order_object The order.
	 * @param string $type Document type - not used for the moment.
	 * @param array $args Additional arguments / options.
	 */
	public function getOrderItemsForDocument( $order_object, $type, $args = array() ) {
		//Arguments
		$no_values = false;
		if ( isset( $args['no_values'] ) && $args['no_values']) {
			$no_values = true;
		}
		$items  = array();
		//Products
		foreach ( $order_object->get_items() as $key => $item ) {
			$vat = '';
			if ( $item->get_variation_id() ) {
				$pid = $item->get_variation_id();
			} else {
				$pid = $item->get_product_id();
			}
			$quantity = $item->get_quantity() - abs( $order_object->get_qty_refunded_for_item( $key ) );
			if ( $quantity > 0 ) {
				if ( $no_values ) {
					$unit_price = 0;
				} else {
					$unit_price = (double) $item->get_total() / (double) $item->get_quantity();
				}
				$taxes_per_line = $item->get_taxes();
				$tax_ids = array();
				foreach ( $taxes_per_line['subtotal'] as $key2 => $value ) {
					if ( $value != '' ) {
						$tax_ids[] = $key2;
					}
				}
				if ( isset( $tax_ids[0] ) ) {
					$vat = \WC_Tax::get_rate_label( $tax_ids[0] );
				}
				//if ( $item->get_total_tax() == 0 && $order_object->get_meta( '_billing_tax_exemption_reason' ) != '' ) {  //2.1.7
				if ( $unit_price > 0 && $item->get_total_tax() == 0 ) {
					  $vat = get_option( 'hd_wc_ie_plus_exemption_name' );
				}
				/* Issue #108 */
				$name = '#' . $pid;
				if ( $product = wc_get_product( $pid ) ) {
					$product_code = get_option( 'hd_wc_ie_plus_product_code' );
					if ( $product->get_sku() && $product_code != 'id' ) {
						$name = $product->get_sku();
					}
				}
				/* End of Issue #108 */
				$item_data = array(
					'ixwc'        => array(
						'type'       => 'item',
						'key'        => $key,
						'product_id' => $pid,
					), //Removed later by process_items
					'name'        => $name,
					'description' => $this->order_item_title( $item, $product, $order_object, $type ),
					'unit_price'  => $unit_price,
					'quantity'    => $quantity,
					'unit'        => apply_filters( 'invoicexpress_woocommerce_document_item_unit', 'unit', $item, $product, $order_object, $type, $args ), //We should deprecate this filter because we have the global one below
				);
				if ( ! empty( $vat ) ) {
					$item_data['tax'] = array(
						'name' => $vat,
					);
				}
				//Allow developers to manipulate the $item_data or exclude from the invoice if false is returned
				$item_data = apply_filters( 'invoicexpress_woocommerce_document_item', $item_data, $item, $product, $order_object, $type, $args );
				//Still an array? Add it
				if ( is_array( $item_data ) ) {
					$items[] = $item_data;
					//Allow developers do add other items based on this one
					$items = apply_filters( 'invoicexpress_woocommerce_items_after_document_item_add', $items, $item_data, $item, $product, $order_object, $type, $args );
				}
			}
		}
		if ( ! $no_values ) {
			//Shipping
			$shipping_method = $order_object->get_shipping_method();
			if ( ! empty( $shipping_method ) ) {
				foreach ( $order_object->get_shipping_methods() as $key => $item ) {
					$vat = '';
					if ( $no_values ) {
						$cost = 0;
					} else {
						$cost = abs( $item['cost'] );
						//Shipping refunds?
						foreach ( $order_object->get_refunds() as $refund ) {
							foreach ( $refund->get_shipping_methods() as $refund_item ) {
								if ( (string) $refund_item['item_meta']['_refunded_item_id'] == (string) $key ) {
									$cost -= abs( $refund_item['cost'] );
								}
							}
						}
					}
					if ( $cost > 0 ) {
						$taxes_per_line = $item['taxes'];
						if ( $taxes_per_line && ! is_array( $taxes_per_line ) ) {
							$taxes_per_line = unserialize( $taxes_per_line );
						}
						$tax_ids = array();
						foreach ( $taxes_per_line as $key2 => $value ) {
							if ( $key2 === 'total' ) {
								foreach ( $value as $k => $v ) {
									if ( floatval( $v ) > 0 ) {
										$tax_ids[] = $k;
									}
								}
							} elseif ( $key2 !== '' ) {
								if ( floatval( $value ) > 0 ) {
									$tax_ids[] = $key2;
								}
							}
						}
						if ( isset( $tax_ids[0] ) ) {
							$vat = \WC_Tax::get_rate_label( $tax_ids[0] );
						}
						//if ( $item->get_total_tax() == 0 && $order_object->get_meta( '_billing_tax_exemption_reason' ) != '' ) {  //2.1.7
						if ( $cost > 0 && $item->get_total_tax() == 0 ) {
							$vat = get_option( 'hd_wc_ie_plus_exemption_name' );
						}
						if ( apply_filters( 'invoicexpress_woocommerce_shipping_and_fee_ref_unique', true ) ) {
							$ref = 'SHIP';
						} else {
							//Old way
							$ref = '#S-' . $key;
						}
						$item_data = array(
							'ixwc'        => array(
								'type'      => 'shipping',
								'key'       => $key,
							), //Removed later by process_items
							'name'        => $ref,
							'description' => $item['name'],
							'unit_price'  => $cost,
							'quantity'    => 1,
							'unit'        => apply_filters( 'invoicexpress_woocommerce_document_shipping_unit', 'service', $item, $order_object, $type, $args ), //We should deprecate this filter because we have the global one below
						);
						if ( ! empty( $vat ) ) {
								$item_data['tax'] = array(
									'name' => $vat,
								);
						}
						//Allow developers to manipulate the $item_data or exclude from the invoice if false is returned
						$item_data = apply_filters( 'invoicexpress_woocommerce_document_shipping', $item_data, $item, $order_object, $type, $args );
						//Still an array? Add it
						if ( is_array( $item_data ) ) {
							$items[] = $item_data;
							//Allow developers do add other items based on this one
							$items = apply_filters( 'invoicexpress_woocommerce_items_after_document_shipping_add', $items, $item_data, $item, $order_object, $type, $args );
						}
					}
				}
			}
			//Fees
			foreach ( $order_object->get_fees() as $key => $item ) {
				$vat = '';
				if ( $no_values ) {
					$fee_price = 0;
				} else {
					$fee_price = abs( $item['line_total'] );
					//Fee refunds?
					foreach ( $order_object->get_refunds() as $refund ) {
						foreach ( $refund->get_fees() as $refund_item ) {
							if ( (string) $refund_item['item_meta']['_refunded_item_id'] == (string) $key ) {
								$fee_price -= abs( $refund_item['line_total'] );
							}
						}
					}
				}
				if ( $fee_price > 0 ) {
					$taxes_per_line = $item['taxes'];
					if ( $taxes_per_line && ! is_array( $taxes_per_line ) ) {
						$taxes_per_line = unserialize( $taxes_per_line );
					}
					$tax_ids = array();
					foreach ( $taxes_per_line as $key2 => $value ) {
						if ( $key2 === 'total' ) {
							foreach ( $value as $k => $v ) {
								if ( floatval( $v ) > 0 ) {
									$tax_ids[] = $k;
								}
							}
						} elseif ( $key2 !== '' ) {
							if ( floatval( $value ) > 0 ) {
								$tax_ids[] = $key2;
							}
						}
					}
					if ( isset( $tax_ids[0] ) ) {
						$vat = \WC_Tax::get_rate_label( $tax_ids[0] );
					}
					//if ( $item->get_total_tax() == 0 && $order_object->get_meta( '_billing_tax_exemption_reason' ) != '' ) {  //2.1.7
					if ( $fee_price > 0 && $item->get_total_tax() == 0 ) {
						$vat = get_option( 'hd_wc_ie_plus_exemption_name' );
					}
					if ( apply_filters( 'invoicexpress_woocommerce_shipping_and_fee_ref_unique', true ) ) {
						$ref = 'FEE';
					} else {
						//Old way
						$ref = '#F-' . $key;
					}
					$item_data = array(
						'ixwc'        => array(
							'type'      => 'fee',
							'key'       => $key,
						), //Removed later by process_items
						'name'        => $ref,
						'description' => $item['name'],
						'unit_price'  => $fee_price,
						'quantity'    => 1,
						'unit'        => apply_filters( 'invoicexpress_woocommerce_document_fee_unit', 'service', $item, $order_object, $type, $args ), //We should deprecate this filter because we have the global one below
					);
					if ( ! empty( $vat ) ) {
						$item_data['tax'] = array(
							'name' => $vat,
						);
					}
					//Allow developers to manipulate the $item_data or exclude from the invoice if false is returned
					$item_data = apply_filters( 'invoicexpress_woocommerce_document_fee', $item_data, $item, $order_object, $type, $args );
					//Still an array? Add it
					if ( is_array( $item_data ) ) {
						$items[] = $item_data;
						//Allow developers do add other items based on this one
						$items = apply_filters( 'invoicexpress_woocommerce_items_after_document_fee_add', $items, $item_data, $item, $order_object, $type, $args );
					}
				}
			}
		}
		return $items;
	}

	/*
	 * Order item title
	 *
	 * @since  2.1.4.2
	 * @param  object $item The order item
	 * @param  object $product The product (or false)
	 * @param  object $order_object The order
	 * @param  string $type The document type
	 * @return string The item title
	 */
	public function order_item_title( $item, $product, $order_object, $type ) {
		$title = $item->get_name();
		return apply_filters( 'invoicexpress_woocommerce_document_item_title', $title, $item, $product, $order_object, $type );
	}

	/**
	 * Fix invoice data items: remove our type and apply exemption if needed
	 *
	 * @since 2.1.7
	 * @param array $invoice_data The invoice data
	 * @param object $order_object The order
	 * @param string $type The document type
	 */
	public function process_items( $invoice_data, $order_object, $type ) {
		//Partial exemption?
		foreach ( $invoice_data['items'] as $key => $item ) {
			if ( isset( $item['ixwc']['type'] ) ) {
				//Set partial exemption if global exemption is not set - Really? http://contabilistas.info/index.php?topic=8818.0
				if ( empty( $invoice_data['tax_exemption'] ) && apply_filters( 'invoicexpress_woocommerce_partial_exemption', false ) ) {
					switch( $item['ixwc']['type'] ) {
						case 'item':
						case 'shipping':
						case 'fee':
							if ( isset( $item['tax']['name'] ) && trim( $item['tax']['name'] ) != '' && trim( $item['tax']['name'] ) == get_option( 'hd_wc_ie_plus_exemption_name' ) ) {
								$exemption = get_option( 'hd_wc_ie_plus_exemption_reason' );
								$invoice_data['tax_exemption'] = apply_filters( 'invoicexpress_woocommerce_partial_exemption_reason', $exemption, $item, $invoice_data, $order_object->get_id(), $type );
								do_action( 'invoicexpress_woocommerce_partial_exemption_applied', $item, $invoice_data, $order_object->get_id(), $type );
								break; //No need to keep going because we can only set one exemption reason per document (Maybe InvoiceXpress should look into that...)
							}
							break;
						default:
							break;
					}
				}
				//Other stuff??
				//...
			}
		}
		//Clear our extra information
		foreach ( $invoice_data['items'] as $key => $item ) {
			//Important: Clear our data or InvoiceXpress will throw an error
			if ( isset( $item['ixwc'] ) ) unset( $invoice_data['items'][$key]['ixwc'] );
		}
		return $invoice_data;
	}

	/*
	 * Finds the sequence id for the provided document type.
	 *
	 * @since  2.0.0 Return the default sequence.
	 * @since  1.0.0
	 * @param  int    $order_id The order ID.
	 * @param  string $type     The document type.
	 * @return string The document sequence ID.
	 */
	public function find_sequence_id( $order_id, $type ) {

		$order_object = wc_get_order( $order_id );

		switch( $type ) {
			case 'vat_moss_invoice':
				$order_sequence_id = get_option( 'hd_wc_ie_plus_vat_moss_sequence' );
				break;
			default:
				$order_sequence_id = $order_object->get_meta( '_billing_sequence_id' );
				if ( empty( $order_sequence_id ) ) {
					$order_sequence_id = apply_filters( 'invoicexpress_woocommerce_default_sequence', '' );
				}
				break;
		}

		//Get from sequences cache
		$cache = get_option( 'hd_wc_ie_plus_sequences_cache' );
		if ( is_array( $cache ) && count( $cache ) > 0 ) {
			if ( isset( $cache[$order_sequence_id]['current_' . $type . '_sequence_id'] ) ) {
				//Found in cache
				return $cache[$order_sequence_id]['current_' . $type . '_sequence_id'];
			}
		}
		
		return '';
	}

	/*
	 * Stores the document as a order note and a custom field
	 */
	public function storeAndNoteDocument( $order_object, $document_url, $type, $invoicexpress_id, $another_doc = '' ) {
		//Legacy XML support
		if ( is_array( $document_url ) && isset( $document_url['value'] ) ) {
			$value = $document_url['value'];

			foreach ( $value as $v ) {
				if ( $v['name'] == '{}pdfUrl' ) {
					$document_url = $v['value'];
					break;
				}
			}
		}
		//Legacy XML support - END

		$wp_upload_path = wp_upload_dir();
		$plugin_path    = $wp_upload_path['basedir'];

		if ( ! file_exists( $wp_upload_path['basedir'] . '/invoicexpress/documents/' ) ) {
			mkdir( $wp_upload_path['basedir'] . '/invoicexpress/documents/', 0755, true );
		}

		if ( ! file_exists( $wp_upload_path['basedir'] . '/invoicexpress/index.php' ) ) {
			touch( $wp_upload_path['basedir'] . '/invoicexpress/index.php' );
		}

		if ( ! file_exists( $wp_upload_path['basedir'] . '/invoicexpress/documents/index.php' ) ) {
			touch( $wp_upload_path['basedir'] . '/invoicexpress/documents/index.php' );
		}

		$file_name = apply_filters( 'invoicexpress_woocommerce_document_filename', $type .'_'. $invoicexpress_id . '.pdf', $order_object, $document_url, $type, $invoicexpress_id, $another_doc );

		$response = wp_remote_get( $document_url );
		if ( is_wp_error( $response ) ) {
			//We should deal with this...
		} else {
			file_put_contents( $plugin_path . '/invoicexpress/documents/' . $file_name, wp_remote_retrieve_body( $response ) );
		}

		$url_local = $wp_upload_path['baseurl'] . '/invoicexpress/documents/' . $file_name;

		$type_name = isset( $this->plugin->type_names[$type] ) ? $this->plugin->type_names[$type] : $type;

		// If it as a valid URL
		if ( ! empty( $document_url ) ) {
			$site_url = get_site_url() . '/invoicexpress/download_pdf' . "?order_id={$order_object->get_id()}&document_id=$invoicexpress_id&document_type=$type";
			$order_object->update_meta_data( 'hd_wc_ie_plus_' . $type . '_id' . $another_doc, $invoicexpress_id );
			$order_object->update_meta_data( 'hd_wc_ie_plus_' . $type . '_pdf' . $another_doc, $url_local );
			$note = sprintf(
				'%1$s<br/>%2$s',
				sprintf(
					/* translators: %1$s: document name, %2$s document number, %3$s: PDF string, %4$s: PDF download link */
					__( 'Download %1$s %2$s (%3$s): %4$s.', 'woo-billing-with-invoicexpress' ),
					$type_name,
					$order_object->get_meta( 'hd_wc_ie_plus_'.$type.'_sequence_number' ),
					__( 'PDF', 'woo-billing-with-invoicexpress' ),
					sprintf(
						'<a target="_blank" href="%1$s">%2$s</a>',
						esc_url( $url_local ),
						__( 'click here', 'woo-billing-with-invoicexpress' )
					)
				),
				sprintf(
					/* translators: %1$s: document name, %2$s: download link */
					__( 'Problem accessing the %1$s PDF? Download again: %2$s.', 'woo-billing-with-invoicexpress' ),
					$this->plugin->type_names[$type],
					sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( $site_url ),
						__( 'click here', 'woo-billing-with-invoicexpress' )
					)
				)
			);
			$order_object->save();
			$order_object->add_order_note( $note );
			//Send it
			if ( get_option( 'hd_wc_ie_plus_send_'.$type ) ) {
				switch( $type ) {
					case 'transport_guide':
						$email = get_option( 'hd_wc_ie_plus_transport_guide_email_address' );
						break;
					default:
						$email = $order_object->get_billing_email();
						break;
				}
				if ( ! empty( $email ) ) {
					$attachment = $order_object->get_meta( 'hd_wc_ie_plus_'.$type.'_pdf' );
					$this->send_invoice_email( $email, $invoicexpress_id, $order_object->get_id(), $order_object, $attachment, $type );
				} else {
					do_action( 'invoicexpress_woocommerce_error', 'storeAndNoteDocument '.$this->plugin->type_names[$type].' PDF: No email address', $order_object );
				}
			}
		} else {
			do_action( 'invoicexpress_woocommerce_error', 'storeAndNoteDocument '.$this->plugin->type_names[$type].' PDF: No document URL', $order_object );
		}
	}

	/*
	 * Creates a order note with the possibility of redownloading the PDF
	 *
	 * @since  2.4.0
	 * @param  WC_Order $order_object     The order object.
	 * @param  string   $type The type of document
	 * @param  int      $invoicexpress_id The InvoiceXpress document ID.
	 */
	public function noteDocumentFailedPDF( $order_object, $type, $invoicexpress_id ) {
		$site_url = get_site_url() . '/invoicexpress/download_pdf?order_id='.$order_object->get_id().'&document_id='.$invoicexpress_id.'&document_type='.$type;
		$note = sprintf(
			/* translators: %1$s: document name, %2$s: download link */
			__( 'Problem accessing the %1$s PDF? Download again: %2$s.', 'woo-billing-with-invoicexpress' ),
			$this->plugin->type_names[$type],
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $site_url ),
				__( 'click here', 'woo-billing-with-invoicexpress' )
			)
		);
		$order_object->add_order_note( $note );
	}


	/*
	 * Stores the document as an orders note and a custom field
	 */
	public function storeAndNoteMassDocument( $order_array, $document_url, $type, $invoicexpress_id, $sequence_number, $another_doc = '' ) {
		//Legacy XML support
		if ( is_array( $document_url ) && isset( $document_url['value'] ) ) {
			$value = $document_url['value'];

			foreach ( $value as $v ) {
				if ( $v['name'] == '{}pdfUrl' ) {
					$document_url = $v['value'];
					break;
				}
			}
		}
		//Legacy XML support - END

		$wp_upload_path = wp_upload_dir();
		$plugin_path    = $wp_upload_path['basedir'];

		if ( ! file_exists( $wp_upload_path['basedir'] . '/invoicexpress/documents/' ) ) {
			mkdir( $wp_upload_path['basedir'] . '/invoicexpress/documents/', 0777, true );
		}

		$file_name = apply_filters( 'invoicexpress_woocommerce_document_filename', $type .'_'. $invoicexpress_id . '.pdf', $order_array[0], $document_url, $type, $invoicexpress_id, $another_doc );

		$response = wp_remote_get( $document_url );
		if ( is_wp_error( $response ) ) {
			//We should deal with this...
		} else {
			file_put_contents( $plugin_path . '/invoicexpress/documents/' . $file_name, wp_remote_retrieve_body( $response ) );
		}

		$url_local = $wp_upload_path['baseurl'] . '/invoicexpress/documents/' . $file_name;

		$type_name = isset( $this->plugin->type_names[$type] ) ? $this->plugin->type_names[$type] : $type;

		// If it as a valid URL
		if ( ! empty( $document_url ) ) {
			foreach ( $order_array as $order_object ) {
				$site_url = get_site_url() . '/invoicexpress/download_pdf' . "?order_id={$order_object->get_id()}&document_id=$invoicexpress_id&document_type=$type";
				$order_object->update_meta_data( 'hd_wc_ie_plus_' . $type . '_id' . $another_doc, $invoicexpress_id );
				$order_object->update_meta_data( 'hd_wc_ie_plus_' . $type . '_pdf' . $another_doc, $url_local );
				if ( ! empty( $sequence_number ) ) {
					$order_object->update_meta_data( 'hd_wc_ie_plus_' . $type . '_sequence_number' . $another_doc, $sequence_number );
				}

				$note = sprintf(
					'%1$s<br/>%2$s',
					sprintf(
						/* translators: %1$s: document name, %2$s document number, %3$s: PDF string, %4$s: PDF download link */
						__( 'Download %1$s %2$s (%3$s): %4$s.', 'woo-billing-with-invoicexpress' ),
						$type_name,
						$sequence_number,
						__( 'PDF', 'woo-billing-with-invoicexpress' ),
						sprintf(
							'<a target="_blank" href="%1$s">%2$s</a>',
							esc_url( $url_local ),
							__( 'click here', 'woo-billing-with-invoicexpress' )
						)
					),
					sprintf(
						/* translators: %1$s: document name, %2$s: download link */
						__( 'Problem accessing the %1$s PDF? Download again: %2$s.', 'woo-billing-with-invoicexpress' ),
						$this->plugin->type_names[$type],
						sprintf(
							'<a href="%1$s">%2$s</a>',
							esc_url( $site_url ),
							__( 'click here', 'woo-billing-with-invoicexpress' )
						)
					)
				);
				$order_object->save();
				$order_object->add_order_note( $note );
			}
		}
	}

	/*
	 * Use Invoicexpress API to return a PDF of a document
	 *
	 * @since  2.4.0
	 * @param  WC_Order $order_object     The order object.
	 * @param  string   $type The type of document
	 * @param  int      $order_id_invoicexpress The InvoiceXpress document ID.
	 * @param  string   $mode Issuing mode: manual or automatic
	 */
	public function getAndSendPDF( $order_object, $type, $order_id_invoicexpress, $mode = 'manual', $receipt_count = 1 ) {
		if ( get_option( 'hd_wc_ie_plus_email_method' ) != '' && apply_filters( 'invoicexpress_woocommerce_allow_ix_email', true ) ) {
			do_action( 'invoicexpress_woocommerce_debug', $this->plugin->type_names[ $type ].' email method: '.get_option( 'hd_wc_ie_plus_email_method' ), $order_object );
			//Send it
			if ( get_option( 'hd_wc_ie_plus_send_'.$type ) ) {
				switch( $type ) {
					case 'transport_guide':
						$email = get_option( 'hd_wc_ie_plus_transport_guide_email_address' );
						break;
					default:
						$email = $order_object->get_billing_email();
						break;
				}
				if ( ! empty( $email ) ) {
					$this->send_invoice_email(
						$email,
						$order_id_invoicexpress,
						$order_object->get_id(),
						$order_object,
						false,
						$type
					);
				} else {
					do_action( 'invoicexpress_woocommerce_error', 'getAndSendPDF '.$this->plugin->type_names[$type].' PDF: No email address', $order_object );
				}
			}
			//Note it
			$note = sprintf(
				/* translators: %1$s: document name, %2$s document number, %3$s: PDF string, %4$s: PDF download link */
				__( 'Download %1$s %2$s (%3$s): %4$s.', 'woo-billing-with-invoicexpress' ),
				$this->plugin->type_names[$type],
				$order_object->get_meta( 'hd_wc_ie_plus_'.$type.'_sequence_number' ),
				__( 'PDF', 'woo-billing-with-invoicexpress' ),
				sprintf(
					'<a target="_blank" href="%1$s">%2$s</a>',
					esc_url( $order_object->get_meta( 'hd_wc_ie_plus_'.$type.'_permalink' ) ),
					__( 'click here', 'woo-billing-with-invoicexpress' )
				)
			);
			$order_object->add_order_note( $note );
			return true;
		} else {
			$return = $this->getDocumentPDF( $order_id_invoicexpress );
			$site_url = get_site_url() . '/invoicexpress/download_pdf?order_id='.$order_object->get_id().'&document_id='.$order_id_invoicexpress.'&document_type='.$type;
			if ( ! $return['success'] ) {
				$error_notice = false;
				if ( intval( $return['error_code'] ) == 502 ) {
					/* Add notice */
					$error_notice = sprintf(
						'<strong>%s:</strong> %s<br/>%s',
						__( 'InvoiceXpress error while getting PDF', 'woo-billing-with-invoicexpress' ),
						sprintf(
							/* translators: %s: document name */
							__( "The %s PDF wasn't created due to InvoiceXpress service being temporarily down.", 'woo-billing-with-invoicexpress' ),
							$this->plugin->type_names[$type]
						),
						sprintf(
							/* translators: %1$s: document name, %2$s: download link */
							__( 'Problem accessing the %1$s PDF? Download again: %2$s.', 'woo-billing-with-invoicexpress' ),
							$this->plugin->type_names[$type],
							sprintf(
								'<a href="%1$s">%2$s</a>',
								esc_url( $site_url ),
								__( 'click here', 'woo-billing-with-invoicexpress' )
							)
						)
					);
					if ( $mode == 'manual' ) {
						Notices::add_notice(
							$error_notice,
							'error'
						);
					}
				} else {
					$codeStr    = __( 'Code', 'woo-billing-with-invoicexpress' );
					$messageStr = __( 'Message', 'woo-billing-with-invoicexpress' );
					/* Add notice */
					$error_notice = sprintf(
						'<strong>%s:</strong> %s<br/>%s',
						__( 'InvoiceXpress error while getting PDF', 'woo-billing-with-invoicexpress' ),
						$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message'],
						sprintf(
							/* translators: %1$s: document name, %2$s: download link */
							__( 'Problem accessing the %1$s PDF? Download again: %2$s.', 'woo-billing-with-invoicexpress' ),
							$this->plugin->type_names[$type],
							sprintf(
								'<a href="%1$s">%2$s</a>',
								esc_url( $site_url ),
								__( 'click here', 'woo-billing-with-invoicexpress' )
							)
						)
					);
					if ( $mode == 'manual' ) {
						Notices::add_notice(
							$error_notice,
							'error'
						);
					}
				}
				if ( $error_notice ) {
					do_action( 'invoicexpress_woocommerce_error', 'Get '.$this->plugin->type_names[$type].' PDF: '.$error_notice, $order_object );
					$this->noteDocumentFailedPDF( $order_object, $type, $order_id_invoicexpress );
					if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && ( $mode == 'automatic' || $mode == 'scheduled' ) ) {
						$this->sendErrorEmail( $order_object, $error_notice );
					}
				}
				return false;
			} else {
				$document_url = $return['object']->output->pdfUrl;
				if ( $type == 'receipt' && $receipt_count > 1 ) {
					$this->storeAndNoteDocument( $order_object, $document_url, $type, $order_id_invoicexpress, '_2' );
				} else {
					$this->storeAndNoteDocument( $order_object, $document_url, $type, $order_id_invoicexpress );
				}
				return true;
			}
		}
	}
	public function getDocumentPDF( $invoicexpress_id, $second_copy = 'false' ) {
		$params = array(
			'request' => 'api/pdf/'.$invoicexpress_id.'.json',
			'args'    => array(
				'second_copy' => $second_copy,
			),
		);
		$json_request = new JsonRequest( $params );
		return $json_request->getRequestWhileStatusCode( 200 );
	}

	/**
	 * Method to register an array of settings to a page.
	 *
	 * @param array $options
	 */
	public function registerSettingsOptions( $options, $section ) {
		foreach ( $options as $option_name => $option_value ) {

			add_settings_field(
				$option_name,
				$option_value,
				array( $this, $option_name ),
				'invoicexpress_woocommerce',
				$section
			);

			register_setting( $section, $option_name );
		}
	}

	public function registerSettingsOptionsValidation( $options, $section, $type ) {
		foreach ( $options as $option_name => $option_value ) {

			add_settings_field(
				$option_name,
				$option_value,
				array(
					$this,
					$option_name,
				),
				'invoicexpress_woocommerce',
				$section
			);

			register_setting( $section, $option_name, array( $this, $type ) );
		}
	}

	/**
	 * Format email placeholders.
	 *
	 * @since  2.0.0
	 * @param  mixed $string Text to replace placeholders in.
	 * @param  array $placeholders The email placeholders
	 * @return string
	 */
	public function format_string( $string, $placeholders = [] ) {

		if ( empty( $placeholders ) ) {
			$placeholders = $this->get_email_placeholders();
		}

		$find    = array_keys( $placeholders );
		$replace = array_map( 'trim', array_values( $placeholders ) );
		return str_replace( $find, $replace, $string );
	}

	/**
	 * Get WordPress blog name.
	 *
	 * @since  2.0.0
	 * @return string
	 */
	public function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	/**
	 * Get email placeholders.
	 *
	 * @since  2.0.0
	 * @param  string $type The type of document:
	 *                      - invoice
	*                       - invoice_receipt
	 *                      - credit_note
	 *                      - quote
	 *                      - proforma
	 *                      The default value is invoice.
	 * @return array
	 */
	public function get_email_placeholders( $type = 'invoice' ) {
		return apply_filters( 'invoicexpress_woocommerce_email_placeholders', $this->placeholders, $type );
	}

	/**
	 * Send the invoice by email - The default way
	 *
	 * @since  2.0.0 New email subject, body and heading fields.
	 *               Email placeholders.
	 *               Code review
	 * @since  1.0.0
	 * @param  string   $email            The email address.
	 * @param  int      $invoicexpress_id The InvoiceXpress document ID.
	 * @param  int      $order_id         The order ID.
	 * @param  WC_Order $order_object     The order object.
	 * @param  string   $attachment_url   The attachment url.
	 * @param  string   $type             The type of document:
	 *                                    - invoice
	 *                                    - invoice_receipt
	 *                                    - credit_note
	 *                                    - quote
	 *                                    - proforma
	 *                                    The default value is invoice.
	 * @return void
	 */
	public function send_invoice_email( $email, $invoicexpress_id, $order_id, $order_object, $attachment_url, $type = 'invoice' ) {

		$placeholders = $this->get_email_placeholders( $type );

		$placeholders['{order_date}']    = trim( wc_format_datetime( $order_object->get_date_created() ) );
		$placeholders['{order_number}']  = $this->get_order_number( $order_object );
		$placeholders['{customer_name}'] = trim( sprintf(
			'%s %s',
			$order_object->get_billing_first_name(),
			$order_object->get_billing_last_name()
		) );

		$subject = $this->plugin->get_translated_option( "hd_wc_ie_plus_{$type}_email_subject", null, $order_object );
		if ( $subject === false ) { // Backwards compatibility
			$subject = get_option( 'hd_wc_ie_plus_send_invoice_subject' );
		}

		$subject = apply_filters( "invoicexpress_woocommerce_{$type}_email_subject", $this->format_string( $subject, $placeholders ), $order_object );

		$heading = $this->plugin->get_translated_option( "hd_wc_ie_plus_{$type}_email_heading", null, $order_object );
		if ( $heading === false ) { // Backwards compatibility
			$heading = get_option( 'hd_wc_ie_plus_send_invoice_heading' );
		}

		$heading = apply_filters( "invoicexpress_woocommerce_{$type}_email_heading", $this->format_string( $heading, $placeholders ), $order_object );

		$body = $this->plugin->get_translated_option( "hd_wc_ie_plus_{$type}_email_body", null, $order_object );
		if ( $body === false ) { // Backwards compatibility
			$body = get_option( 'hd_wc_ie_plus_send_invoice_body' );
		}

		$body = apply_filters( "invoicexpress_woocommerce_{$type}_email_body", $this->format_string( $body, $placeholders ), $order_object );

		if ( get_option( 'hd_wc_ie_plus_email_method' ) != '' && apply_filters( 'invoicexpress_woocommerce_allow_ix_email', true ) ) {
			do_action( 'invoicexpress_woocommerce_debug', $this->plugin->type_names[ $type ].' email method: '.get_option( 'hd_wc_ie_plus_email_method' ), $order_object );
			do_action( 'invoicexpress_woocommerce_'.get_option( 'hd_wc_ie_plus_email_method' ).'_email', $type, $order_object, $invoicexpress_id, $email, $subject, $heading, $body );
			if ( get_option( 'hd_wc_ie_plus_email_method' ) == 'ix' ) return;
		} else {
			do_action( 'invoicexpress_woocommerce_debug', $this->plugin->type_names[ $type ].' email method: Default', $order_object );
		}

		if ( $attachment_url ) {
			$url_explode = explode( '/', $attachment_url );
			$wp_upload_path = wp_upload_dir();
			$plugin_path    = $wp_upload_path['basedir'];
			$attachment     = $plugin_path . '/invoicexpress/documents/' . end( $url_explode );
		} else {
			$attachment = false;
		}
		
		$headers[] = sprintf(
			'From: %1$s <%2$s>',
			get_option( 'woocommerce_email_from_name' ),
			get_option( 'woocommerce_email_from_address' )
		);

		add_filter( 'wp_mail_content_type', array( $this, 'set_email_to_html' ) );

		$message = nl2br( sprintf( $body ) );

		ob_start();

		wc_get_template( 'emails/email-header.php', array( 'email_heading' => $heading ) );

		do_action( 'invoicexpress_woocommerce_before_email_body', $order_object, $type, $invoicexpress_id );

		echo $message; // WPCS: XSS Ok.

		do_action( 'invoicexpress_woocommerce_after_email_body', $order_object, $type, $invoicexpress_id );

		wc_get_template( 'emails/email-footer.php' );

		$message = ob_get_clean();
		$message = str_replace( '{site_title}', trim( $this->get_blogname() ), $message );

		$headers = apply_filters( 'invoicexpress_woocommerce_email_headers', $headers, $order_object, $type );

		$status = wc_mail( $email, $subject, $message, $headers, $attachment ); //wc_mail returns nothing: https://github.com/woocommerce/woocommerce/issues/24504

		remove_filter( 'wp_mail_content_type', array( $this, 'set_email_to_html' ) );

		//This will not run until wc_mail returns true or false - Webdados PR: https://github.com/woocommerce/woocommerce/pull/24505/
		if ( version_compare( WC_VERSION, '3.8.0', '>=' ) ) {
			do_action( 'invoicexpress_woocommerce_debug', $this->plugin->type_names[ $type ].' email sent: '.( $status ? 'true' : 'false' ), $order_object );
			if ( ! $status ) {
				$note = sprintf(
					'<strong>%1$s</strong> %2$s',
					__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
					sprintf(
						/* translators: %s: document type */
						__( 'An error occured while sending the %s email', 'woo-billing-with-invoicexpress' ),
						$this->plugin->type_names[ $type ]
					)
				);
				$order_object->add_order_note( $note );
				do_action( 'invoicexpress_woocommerce_error', $note, $order_object );
			}
		} else {
			do_action( 'invoicexpress_woocommerce_debug', $this->plugin->type_names[ $type ].' email sent: not possible to know because WooCommerce is below 3.8.0', $order_object );
		}
	}

	/**
	 * Send the error by email.
	 *
	 * @since  2.0.0 Code review
	 * @since  1.0.0
	 * @param  WC_Order $order_object  The order object.
	 * @param  string   $error_message The error message.
	 * @return void
	 */
	public function sendErrorEmail( $order_object, $error_message ) {

		$order_id = $order_object->get_id();
		$subject  = apply_filters( 'invoicexpress_woocommerce_error_email_subject', esc_html( 'Automatic document failed', 'woo-billing-with-invoicexpress' ) );
		$heading  = apply_filters( 'invoicexpress_woocommerce_error_email_heading', esc_html( 'Automatic document failed', 'woo-billing-with-invoicexpress' ) );

		$body = sprintf(
			'<p>%1$s</p>%2$s',
			sprintf(
				/* translators: %s: order number */
				esc_html__( 'Order #%s failed to issue automatic document:', 'woo-billing-with-invoicexpress' ),
				$order_id
			),
			$error_message
		);

		$body = apply_filters( 'invoicexpress_woocommerce_error_email_body', $body );

		$headers[] = sprintf(
			'From: %1$s <%2$s>',
			get_option( 'woocommerce_email_from_name' ),
			get_option( 'woocommerce_email_from_address' )
		);

		add_filter( 'wp_mail_content_type', array( $this, 'set_email_to_html' ) );

		$message = nl2br( sprintf( $body ) );

		ob_start();

		wc_get_template( 'emails/email-header.php', array( 'email_heading' => $heading ) );

		echo $message;

		wc_get_template( 'emails/email-footer.php' );

		$message = ob_get_clean();
		$message = str_replace( '{site_title}', trim( $this->get_blogname() ), $message );

		wc_mail( get_option( 'admin_email' ), $subject, $message, $headers );

		remove_filter( 'wp_mail_content_type', array( $this, 'set_email_to_html' ) );
	}

	/**
	 * Format email to HTML
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function set_email_to_html() {
		return 'text/html';
	}

	/**
	 * Get document client name.
	 *
	 * @since  2.0.0
	 * @param  WC_Order $order_object The order object.
	 * @return string The document client name.
	 */
	public function get_document_client_name( $order_object ) {
		$entity = get_option( 'hd_wc_ie_plus_document_entity' );

		// Set client name.
		if ( $entity === 'company' && ! empty( $order_object->get_billing_company() ) ) {
			$client_name = $order_object->get_billing_company();
		} else {
			$client_name = sprintf(
				'%s %s',
				$order_object->get_billing_first_name(),
				$order_object->get_billing_last_name()
			);
		}

		return apply_filters( 'invoicexpress_woocommerce_document_client_name', $client_name, $order_object, $entity );
	}

	/**
	 * Gets the document due_date.
	 *
	 * @since  2.0.0
	 * @param  $type The document type.
	 * @return string The document client name.
	 */
	public function get_due_date( $type, $order_object ) {
		switch ( $type ) {
			// Invoicing documents (except Invoice-receipt)
			case 'invoice':
			case 'simplified_invoice':
			case 'vat_moss_invoice':
			case 'credit_note':
				$validity = apply_filters( "invoicexpress_woocommerce_{$type}_validity", $this->validity_invoicing_docs );
				break;
			// Quotes and proformas
			case 'quote':
			case 'proforma':
				$validity = apply_filters( "invoicexpress_woocommerce_{$type}_validity", $this->validity_quotes_docs );
				break;
			// Guides
			case 'devolution_guide':
			case 'transport_guide':
				$validity = apply_filters( "invoicexpress_woocommerce_{$type}_validity", $this->validity_guides_docs );
				break;
			// Default - No validity
			case 'invoice_receipt':
			default:
				$validity = 0;
				break;
		}
		if ( $validity > 0 ) {
			$d = date_create( date_i18n( \DateTime::ISO8601 ) );
			date_add( $d, date_interval_create_from_date_string( $validity . ' days' ) );
			return date_format( $d, 'd/m/Y' );
		}
		return date_i18n( 'd/m/Y' );
	}

	/**
	 * Add draft document note to order.
	 *
	 * @param  WC_Order $order_object The order object.
	 * @param  string   $document_type
	 * @return void
	 */
	public function draft_document_note( $order_object, $document_type ) {

		$message         = esc_html__( 'Message', 'woo-billing-with-invoicexpress' );
		$message_content = sprintf(
			/* translators: %s: document type */
			__( 'The document (%s) was created as draft on InvoiceXpress and you should finalize it there.', 'woo-billing-with-invoicexpress' ),
			$document_type
		);

		$note = "<strong>InvoiceXpress:</strong>\n" . $message . ': ' . $message_content;
		$order_object->add_order_note( $note );
	}

	/**
	 * Change document state
	 *
	 * @param  int    $document_id_invoicexpress
	 * @param  string $state
	 * @param  string $document_type
	 * @return array
	 */
	public function changeOrderState( $document_id_invoicexpress, $state, $document_type, $message = '' ) {
		$params = array(
			'request' => $document_type.'s/' . $document_id_invoicexpress . '/change-state.json',
			'args'    => array(
				$document_type => array(
					'state' => $state
				),
			),
		);
		if ( ! empty( $message ) ) {
			$params['args'][$document_type]['message'] = $message;
		}
		$json_request = new JsonRequest( $params );
		if ( in_array( $state, array( 'canceled' ) ) ) {
			return $json_request->putRequest();
		} else {
			return $json_request->postRequest();
		}
	}

	/**
	 * Get order number or id
	 *
	 * @since 2.3.0
	 *
	 * @param  object  $order_object
	 * @return string
	 */
	public function get_order_number( $order_object ) {
		return (string) trim( $order_object->get_order_number() ) != '' ? trim( $order_object->get_order_number() ) : trim( $order_object->get_id() );
	}

	/**
	 * Prevent document issuing
	 *
	 * @since 2.1.4
	 * @param  WC_Order $order_object The order.
	 * @param  string $document_type Document type
	 * @param  array $data Document data to send to InvoiceXpress
	 * @param  string $mode Manual or Automatic
	 * @return array
	 */
	public function preventDocumentIssuing( $order_object, $document_type, $data, $mode = 'manual' ) {
		$prevent = false;
		$message = '';
		//Maybe some external plugin decided to prevent and did it by adding that to the document data with a filter
		if (
			isset( $data['_prevent'] )
			&&
			isset( $data['_prevent_message'] )
			&&
			$data['_prevent']
			&&
			$data['_prevent_message']
		) {
			$prevent = true;
			$message = $data['_prevent_message'];
		}
		return apply_filters( 'invoicexpress_woocommerce_prevent_document_issuing', array(
			'prevent'       => $prevent,
			'message'       => $message,
			'supress_error' => false,
		), $order_object, $document_type, $data, $mode );
	}

	/**
	 * Prevent document issuing error and logger
	 *
	 * @since 2.4.4
	 * @param array    $prevent Prevent data
	 * @param string   $document_type Document type
	 * @param WC_Order $order_object
	 * @param string   $mode Document issuing mode
	 */
	public function preventDocumentIssuingLogger( $prevent, $document_type, $order_object, $mode = 'manual' ) {
		//Some implementations may choose not to error log the document issuing prevention because it's the expected behavior
		if ( ! isset( $prevent['supress_error'] ) || ! $prevent['supress_error'] ) {
			$error_notice = sprintf(
				'<strong>%s:</strong> %s',
				sprintf(
					/* translators: %s: document type */
					__( '%s not issued', 'woo-billing-with-invoicexpress' ),
					$this->plugin->type_names[$document_type]
				),
				isset( $prevent['message'] ) && trim( $prevent['message'] ) != '' ? trim( $prevent['message'] ) : __( 'Reason unknown', 'woo-billing-with-invoicexpress' )
			);
			if ( $mode == 'manual' ) {
				Notices::add_notice(
					$error_notice,
					'error'
				);
			} else {
				if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && ( $mode == 'automatic' || $mode == 'scheduled' ) && $error_notice ) {
					$this->sendErrorEmail( $order_object, $error_notice );
				}
			}
			do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object );
		}
		//But we should add it to the order notes anyway
		if ( isset( $prevent['message'] ) && trim( $prevent['message'] ) != '' ) {
			$order_object->add_order_note( $prevent['message'] );
		}
	}

}
