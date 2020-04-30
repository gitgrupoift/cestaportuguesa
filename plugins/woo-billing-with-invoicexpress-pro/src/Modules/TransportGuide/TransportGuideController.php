<?php
namespace Webdados\InvoiceXpressWooCommerce\Modules\TransportGuide;

use Webdados\InvoiceXpressWooCommerce\BaseController as BaseController;
use Webdados\InvoiceXpressWooCommerce\JsonRequest as JsonRequest;
use Webdados\InvoiceXpressWooCommerce\CountryTranslation as CountryTranslation;
use Webdados\InvoiceXpressWooCommerce\ClientChecker as ClientChecker;
use Webdados\InvoiceXpressWooCommerce\Notices as Notices;

/* WooCommerce CRUD ready */
/* JSON API ready */

class TransportGuideController extends BaseController {

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {

		if ( get_option( 'hd_wc_ie_plus_create_transport_guide' ) ) {

			add_filter(
				'woocommerce_order_actions', array(
					$this,
					'order_actions',
				), 10, 1
			);

			add_action(
				'woocommerce_order_action_hd_wc_ie_plus_generate_transport_guide', array(
					$this,
					'doAction',
				), 11, 2
			);

			add_action(
				'woocommerce_admin_order_data_after_shipping_address', array(
					$this,
					'loaded_at',
				), 11, 1
			);

			add_action(
				'woocommerce_admin_order_data_after_shipping_address', array(
					$this,
					'license_plate',
				), 11, 1
			);

			add_action(
				'woocommerce_process_shop_order_meta', array(
					$this,
					'save_meta',
				), 10, 1
			);

			add_action( 'hd_wc_ie_refetch_at_code', array( $this, 'get_at_code' ), 10, 3 );
		}
	}

	/**
	 * Add order action.
	 *
	 * @since  2.0.0 Code review.
	 * @since  1.0.0
	 * @param  array $actions Order actions.
	 * @return array
	 */
	public function order_actions( $actions ) {
		global $post;
		$order_object = wc_get_order( $post->ID );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return $actions;

		$generate_invoice = esc_html( sprintf(
			'%1$s (%2$s)',
			sprintf(
				/* translators: %s: document type */
				__( 'Issue %s', 'woo-billing-with-invoicexpress' ),
				__( 'Delivery note', 'woo-billing-with-invoicexpress' )
			),
			__( 'PDF', 'woo-billing-with-invoicexpress' )
		) );

		$transport_guide_id = $order_object->get_meta( 'hd_wc_ie_plus_transport_guide_id' );
		$has_scheduled      = apply_filters( 'invoicexpress_woocommerce_has_pending_scheduled_guide_document', false, $order_object->get_id() );

		if ( $has_scheduled ) {
			if ( apply_filters( 'invoicexpress_woocommerce_check_pending_scheduled_document', false, $order_object->get_id(), array( 'transport_guide' ) ) ) {
				//Has Delivery guide scheduled - Clock
				$symbol = '&#x1f550;';
			} else {
				//Has another guide document scheduled - Cross
				$symbol = '&#xd7;';
			}
		} else {
			if ( empty( $transport_guide_id ) ) {
				//Can be issued
				$symbol = '';
			} else {
				//There's already a delivery guide - Cross
				$symbol = '&#x2713;';
			}
		}

		$actions['hd_wc_ie_plus_generate_transport_guide'] = trim( sprintf(
			'%s %s: %s',
			$symbol,
			esc_html__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
			$generate_invoice
		) );

		return $actions;
	}

	public function doAction( $order_object, $mode = 'manual' ) {

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;

		$transport_guide_id = $order_object->get_meta( 'hd_wc_ie_plus_transport_guide_id' );
		$has_scheduled      = apply_filters( 'invoicexpress_woocommerce_has_pending_scheduled_guide_document', false, $order_object->get_id() );

		$debug = 'Checking if Delivery note document should be issued';
		do_action( 'invoicexpress_woocommerce_debug', $debug, $order_object, array(
			'hd_wc_ie_plus_quote_id' => $transport_guide_id,
			'has_scheduled'          => $has_scheduled,
		) );

		if (
			empty( $transport_guide_id )
			&&
			( ( ! $has_scheduled ) || $mode == 'scheduled' )
		) {

			// phpcs:disable WordPress.WP.TimezoneChange.timezone_change_date_default_timezone_set
			date_default_timezone_set( 'Europe/Lisbon' );
			// phpcs:enable WordPress.WP.TimezoneChange.timezone_change_date_default_timezone_set


			// Prevent auto invoice/receipt trigger when this document is created - Shouldn't we set this back to 0 after the document issuing?
			$order_object->update_meta_data( 'hd_wc_ie_plus_stop_automation', 1 );
			$order_object->save();

			$vat = $order_object->get_meta( '_billing_VAT_code' );
			// Check for VAT number.
			if ( get_option( 'hd_wc_ie_plus_vat_field_mandatory' ) && empty( $vat ) ) {
				/* Add notice */
				$error_notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
					__( 'The VAT number is required', 'woo-billing-with-invoicexpress' )
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice(
						$error_notice,
						'error'
					);
				} else {
					if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && $mode == 'automatic' && $error_notice ) {
						$this->sendErrorEmail( $order_object, $error_notice );
					}
				}
				do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object );
				return;
			}

			$client_name = $this->get_document_client_name( $order_object );
			$checker = new ClientChecker();
			$client_info = $checker->maybeCreateClient( $client_name, $order_object );

			$client_data = array(
				'name' => $client_name,
				'code' => $client_info['client_code'],
			);
			
			$items_data = $this->getOrderItemsForDocument( $order_object, 'transport_guide', array(
				'no_values' => get_option( 'hd_wc_ie_plus_transport_guide_no_value' ) ? true : false
			) );

			$address_from_data = array(
				'detail'      => get_option( 'hd_wc_ie_plus_warehouse_address' ),
				'city'        => get_option( 'hd_wc_ie_plus_warehouse_city' ),
				'postal_code' => get_option( 'hd_wc_ie_plus_warehouse_post_code' ),
				'country'     => get_option( 'hd_wc_ie_plus_warehouse_country' ),
			);
			if ( trim( $address_from_data['postal_code'] ) == '' ) $address_from_data['postal_code'] = '-';

			$countries      = new CountryTranslation();
			$countries_list = $countries->get_countries();
			$country = '';
			if ( isset( $countries_list[ $order_object->get_shipping_country() ] ) ) {
				$country = CountryTranslation::translate( $countries_list[ $order_object->get_shipping_country() ] );
			}

			$address_to_data = array(
				'detail'      => $order_object->get_shipping_address_1() . ' ' . $order_object->get_shipping_address_2(),
				'city'        => $order_object->get_shipping_city(),
				'postal_code' => $order_object->get_shipping_postcode(),
				'country'     => $country,
			);
			if ( trim( $address_to_data['postal_code'] ) == '' ) $address_to_data['postal_code'] = '-';

			$license_plate = $order_object->get_meta( '_license_plate' );
			if ( ! $license_plate ) {
				$license_plate = get_option( 'hd_wc_ie_plus_default_licence_plate' );
			}

			$owner_invoice_id = '';
			if ( $invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_invoice_id' ) ) {
				// Has invoice or simplified invoice? Let's associate it.
				$owner_invoice_id = $invoice_id;
			} elseif ( $invoice_id = $order_object->get_meta( 'hd_wc_ie_plus_invoice_receipt_id' ) ) {
				// Has invoice-receipt ? Let's associate it.
				$owner_invoice_id = $invoice_id;
			}

			$loaded_at = ( $mode == 'manual' ? $order_object->get_meta( '_loaded_at' ) : '' );
			if ( ! empty( $loaded_at ) ) {
				$loaded_at_time = $this->checkLoadTime( $loaded_at );
			} else {
				$now = new \DateTime();
				$add = new \DateInterval( 'PT5M' ); //Should have a filter
				$now->add( $add );

				$loaded_at_time = $now->format( 'd/m/Y H:i' ).':59';
			}

			$invoice_data = array(
				'date'             => date_i18n( 'd/m/Y' ),
				'due_date'         => $this->get_due_date( 'transport_guide' ),
				'loaded_at'        => $loaded_at_time,
				'license_plate'    => $license_plate,
				'address_from'     => $address_from_data,
				'address_to'       => $address_to_data,
				'reference'        => $this->get_order_number( $order_object ),
				'client'           => $client_data,
				'items'            => $items_data,
				'sequence_id'      => $this->find_sequence_id( $order_object->get_id(), 'transport' ),
				'owner_invoice_id' => $owner_invoice_id,
				'observations'     => $order_object->get_meta( '_document_observations' ),
			);

			$tax_exemption = $order_object->get_meta( '_billing_tax_exemption_reason' );
			if ( ! empty( $tax_exemption ) ) {
				$invoice_data['tax_exemption'] = $tax_exemption;
			}

			$invoice_data = $this->process_items( $invoice_data, $order_object, 'transport_guide' );

			$invoice_data = apply_filters( 'invoicexpress_woocommerce_transport_guide_data', $invoice_data, $order_object );

			//Prevent issuing?
			$prevent = $this->preventDocumentIssuing( $order_object, 'transport_guide', $invoice_data, $mode );
			if ( isset( $prevent['prevent'] ) && $prevent['prevent'] ) {
				$this->preventDocumentIssuingLogger( $prevent, 'transport_guide', $order_object, $mode );
				return;
			}

			$params = array(
				'request' => 'transports.json',
				'args'    => array(
					'transport' => $invoice_data
				),
			);
			$json_request = new JsonRequest( $params );
			$return = $json_request->postRequest();
			if ( ! $return['success'] ) {
				/* Error creating invoice */
				if ( intval( $return['error_code'] ) == 502 ) {
					/* Add notice */
					$error_notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
						sprintf(
							/* translators: %s: document type */
							__( "The %s wasn't created due to InvoiceXpress service being temporarily down.<br/>Try generating it again in a few minutes.", 'woo-billing-with-invoicexpress' ),
							__( 'Delivery note', 'woo-billing-with-invoicexpress' )
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
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
						$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message']
					);
					if ( $mode == 'manual' ) {
						Notices::add_notice(
							$error_notice,
							'error'
						);
					}
				}
				if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && $mode == 'automatic' && $error_notice ) {
					$this->sendErrorEmail( $order_object, $error_notice );
				}
				do_action( 'invoicexpress_woocommerce_error', 'Issue Delivery note: '.$error_notice, $order_object );
				return;
			}
			
			$order_id_invoicexpress = $return['object']->transport->id;

			//Update client data
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_id', $client_info['client_id'] );
			$order_object->update_meta_data( 'hd_wc_ie_plus_client_code', $client_info['client_code'] );
			//Update delivery note data
			$order_object->update_meta_data( '_loaded_at', $loaded_at_time ); //Update loaded date/time because we've probably changed it
			$order_object->update_meta_data( 'hd_wc_ie_plus_transport_guide_id', $order_id_invoicexpress );
			$order_object->update_meta_data( 'hd_wc_ie_plus_transport_guide_permalink', $return['object']->transport->permalink );
			$order_object->save();

			do_action( 'invoicexpress_woocommerce_after_document_issue', $order_object->get_id(), 'transport_guide' );

			//Get order again because it may have changed on the action above
			$order_object = wc_get_order( $order_object->get_id() );

			if ( get_option( 'hd_wc_ie_plus_leave_as_draft' ) ) {

				/* Leave as Draft */
				$this->draft_document_note( $order_object, __( 'Delivery note', 'woo-billing-with-invoicexpress' ) );

				/* Add notice */
				$notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					sprintf(
						/* translators: %s: document type */
						__( 'Successfully created %s as draft', 'woo-billing-with-invoicexpress' ),
						__( 'Delivery note', 'woo-billing-with-invoicexpress' )
					)
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice( $notice );
				}
				do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );

				return;

			} else {

				/* Change document state to final */
				$return = $this->changeOrderState( $order_id_invoicexpress, 'finalized', 'transport' );
				if ( ! $return['success'] ) {
					$codeStr    = __( 'Code', 'woo-billing-with-invoicexpress' );
					$messageStr = __( 'Message', 'woo-billing-with-invoicexpress' );
					/* Add notice */
					$error_notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
						$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message']
					);
					if ( $mode == 'manual' ) {
						Notices::add_notice(
							$error_notice,
							'error'
						);
					}
					if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && $mode == 'automatic' && $error_notice ) {
						$this->sendErrorEmail( $order_object, $error_notice );
					}
					do_action( 'invoicexpress_woocommerce_error', 'Change Delivery note state to finalized: '.$error_notice, $order_object );
					return;
				} else {
					$notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
						sprintf(
							/* translators: %s: document type */
							__( 'Successfully finalized %s', 'woo-billing-with-invoicexpress' ),
							__( 'Delivery note', 'woo-billing-with-invoicexpress' )
						)
					);
					do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );
				}
	
				$sequence_number = $return['object']->transport->inverted_sequence_number;
				$order_object->update_meta_data( 'hd_wc_ie_plus_transport_guide_sequence_number', $sequence_number );
				$order_object->save();

				/* Add notice */
				$notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					trim(
						sprintf(
							/* translators: %1$s: document name, %2$s: document number */
							__( 'Successfully created %1$s %2$s', 'woo-billing-with-invoicexpress' ),
							__( 'Delivery note', 'woo-billing-with-invoicexpress' ),
							! empty( $sequence_number ) ? $sequence_number : '' 
						)
					)
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice( $notice );
				}
				do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );

				do_action( 'invoicexpress_woocommerce_before_document_email', $order_object->get_id(), 'transport_guide' );

				/* Get and send the PDF */
				if ( ! $this->getAndSendPDF( $order_object, 'transport_guide', $order_id_invoicexpress, $mode ) ) {
					return;
				}

				/* AT Code */
				if ( get_option( 'hd_wc_ie_plus_guide_get_at_code' ) ) {
					$this->get_at_code( $order_object, $order_id_invoicexpress, $mode );
				}

				do_action( 'invoicexpress_woocommerce_after_document_finish', $order_object->get_id(), 'transport_guide' );
			}

		} else {
			/* Add notice */
			$error_notice = sprintf(
				'<strong>%s:</strong> %s',
				__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
				sprintf(
					/* translators: %s: document type */
					__( "The %s wasn't created because this order already has one or is scheduled to be issued.", 'woo-billing-with-invoicexpress' ),
					__( 'Delivery note', 'woo-billing-with-invoicexpress' )
				)
			);
			if ( $mode == 'manual' ) {
				Notices::add_notice(
					$error_notice,
					'error'
				);
			} else {
				if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) ) {
					$this->sendErrorEmail( $order_object, $error_notice );
				}
			}
			do_action( 'invoicexpress_woocommerce_error', $error_notice, $order_object );
			return;
		}
	}

	/*
	 * Renders a loaded at field
	 */
	public function loaded_at() {
		global $post;
		$order_object = wc_get_order( $post->ID );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;

		$meta = $order_object->get_meta( '_loaded_at' );
		if ( ! empty( $meta ) ) {
			$date_time = \DateTime::createFromFormat( 'd/m/Y H:i:s', $meta );
			if ( is_object( $date_time ) ) {
				$date    = $date_time->format( 'd/m/Y' );
				$hour    = $date_time->format( 'H' );
				$minutes = $date_time->format( 'i' );
			} else {
				$date    = date_i18n( 'd/m/Y' );
				$hour    = date_i18n( 'H' );
				$minutes = date_i18n( 'i' );
			}
		} else {
			$date    = date_i18n( 'd/m/Y' );
			$hour    = date_i18n( 'H' );
			$minutes = date_i18n( 'i' );
		}
		?>
<p class="form-field form-field-wide">
	<label for="loaded_at_date"><?php _e( 'InvoiceXpress', 'woo-billing-with-invoicexpress' );
	echo ' - ';
	printf(
		/* translators: %s: document type */
		__( 'Loaded at (%s):', 'woo-billing-with-invoicexpress' ),
		__( 'Delivery note', 'woo-billing-with-invoicexpress' )
	); ?></label>
	<input type="text" class="date-picker hasDatepicker"
		name="loaded_at_date" id="loaded_at_date" maxlength="10" size="10"
		value="<?php echo $date; ?>"
		pattern="(0[1-9]|1[0-9]|2[0-9]|3[01])/(0[1-9]|1[012])/[0-9]{4}" placeholder="dd/mm/YYYY">@<input
		type="text" class="hour" placeholder="hh" name="loaded_at_date_hour"
		id="loaded_at_date_hour" maxlength="2" size="2"
		value="<?php echo $hour; ?>" pattern="\-?\d+(\.\d{0,})?">:<input
		type="text" class="minute" placeholder="mm"
		name="loaded_at_date_minute" id="loaded_at_date_minute" maxlength="2"
		size="2" value="<?php echo $minutes; ?>" pattern="\-?\d+(\.\d{0,})?">
</p>
		<?php
	}

	/*
	 * Renders a license plate field
	 */
	public function license_plate() {
		global $post;
		$order_object = wc_get_order( $post->ID );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;

		$license_plate = $order_object->get_meta( '_license_plate' );
		?>
<p class="form-field form-field-wide">
	<label for="license_plate"><?php _e( 'InvoiceXpress', 'woo-billing-with-invoicexpress' );
	echo ' - ';
	printf(
		/* translators: %s: document type */
		__( 'License plate (%s):', 'woo-billing-with-invoicexpress' ),
		__( 'Delivery note', 'woo-billing-with-invoicexpress' )
	); ?></label>
	<input type="text" class="date-picker hasDatepicker"
		name="license_plate" id="license_plate"
		value="<?php echo $license_plate; ?>"
		placeholder="<?php echo esc_attr( get_option( 'hd_wc_ie_plus_default_licence_plate' ) ); ?>">
</p>
		<?php
	}

	/*
	 * Method to save the meta of a specific post id
	 */
	public function save_meta( $post_id ) {
		$order_object = wc_get_order( $post_id );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;
		
		if ( isset( $_POST['loaded_at_date'] ) && isset( $_POST['loaded_at_date_hour'] ) && isset( $_POST['loaded_at_date_minute'] )
			 &&
			 !empty( $_POST['loaded_at_date'] ) && !empty( $_POST['loaded_at_date_hour'] ) && !empty( $_POST['loaded_at_date_minute'] ) ) {
			$date = $_POST['loaded_at_date'] . ' ' . $_POST['loaded_at_date_hour'] . ':' . $_POST['loaded_at_date_minute'] . ':59';
			$order_object->update_meta_data( '_loaded_at', $date );
		} else {
			$order_object->update_meta_data( '_loaded_at', '' );
		}
		if ( isset( $_POST['license_plate'] ) ) {
			$order_object->update_meta_data( '_license_plate', sanitize_text_field( $_POST['license_plate'] ) );
		}
		$order_object->save();
	}

	/*
	 * Compares the load time against now. If it is less then now it will assign
	 * now and 5 more minutes.
	 */
	public function checkLoadTime( $time ) {
		$date = \DateTime::createFromFormat( 'd/m/Y H:i:s', $time );
		$now  = new \DateTime();
		if ( $date < $now ) {
			$add  = new \DateInterval( 'PT5M' ); //Should have a filter
			$date = $now;
			$date->add( $add );
		}
		return $date->format( 'd/m/Y H:i' ).':59';
	}

	/**
	 * Get AT Code
	 *
	 * @since 2.4.7
	 */
	public function get_at_code( $order_object, $doc_id, $mode = 'manual' ) {

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;

		$error = false;
		$params = array(
			'request' => 'transports/'.$doc_id.'.json',
		);
		$json_request = new JsonRequest( $params );
		$return = $json_request->getRequest();
		if ( ! $return['success'] ) {
			$error = true;
			/* Error getting AT Code */
			if ( intval( $return['error_code'] ) == 502 ) {
				/* Add notice */
				$error_notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
					sprintf(
						/* translators: %s: document type */
						__( "The %s AT Code wasn\'t obtained due to InvoiceXpress service being temporarily down.<br/>Try again in a few minutes.", 'woo-billing-with-invoicexpress' ),
						__( 'Delivery note', 'woo-billing-with-invoicexpress' )
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
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress error getting AT Code', 'woo-billing-with-invoicexpress' ),
					$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message']
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice(
						$error_notice,
						'error'
					);
				}
			}
			if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && $mode == 'automatic' && $error_notice ) {
				$this->sendErrorEmail( $order_object, $error_notice );
			}
			do_action( 'invoicexpress_woocommerce_error', 'Get Delivery note AT Code: '.$error_notice, $order_object );
		} else {
			//AT Code available already?
			$guide = $return['object'];
			//$property = 'saft_hash'; //Testing
			$property = 'at_doc_code_id'; //Production
			$at_code_found = false;
			if ( property_exists( $guide->transport, $property ) ) {
				$code = $guide->transport->$property;
				if ( trim( $code ) != '' ) {
					$at_code_found = true;
					//Add to meta
					$order_object->update_meta_data( 'hd_wc_ie_plus_transport_guide_at_code', trim( $code ) );
					$order_object->save();
					//Add order note
					$note = sprintf(
						/* translators: %1$s: document name (delivery note), %2$s: the AT code itself */
						__( '%1$s AT Code: %2$s', 'woo-billing-with-invoicexpress' ),
						__( 'Delivery note', 'woo-billing-with-invoicexpress' ),
						trim( $code )
					);
					$order_object->add_order_note( $note );
					/* Add notice */
					$notice = sprintf(
						'<strong>%s:</strong> %s',
						__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
						sprintf(
							/* translators: %1$s: document name (delivery note), %2$s: the AT code itself */
							__( 'Successfully fetched %1$s AT Code: %2$s', 'woo-billing-with-invoicexpress' ),
							__( 'Delivery note', 'woo-billing-with-invoicexpress' ),
							trim( $code )
						)
					);
					if ( $mode == 'manual' ) {
						Notices::add_notice( $notice );
					}
					do_action( 'invoicexpress_woocommerce_debug', $notice, $order_object );
					return;
				}
			}
			if ( !$at_code_found )  {
				$error = true;
				/* Add notice */
				$error_notice = sprintf(
					'<strong>%1$s:</strong> %2$s',
					__( 'InvoiceXpress error getting AT Code', 'woo-billing-with-invoicexpress' ),
					__( 'The AT Code is not available', 'woo-billing-with-invoicexpress' )
				);
				if ( $mode == 'manual' ) {
					Notices::add_notice(
						$error_notice,
						'error'
					);
				}
				if ( get_option( 'hd_wc_ie_plus_automatic_email_errors' ) && $mode == 'automatic' ) {
					$this->sendErrorEmail( $order_object, $error_notice );
				}
				do_action( 'invoicexpress_woocommerce_error', 'Get Delivery note AT Code: '.$error_notice, $order_object );
			}
		}
		if ( $error ) {
			//Add order note to refetch AT Code
			$url = get_site_url() . '/invoicexpress/get_at_code?order_id='.$order_object->get_id().'&document_id='.$doc_id.'&document_type=transport_guide';
			$note = sprintf(
				/* translators: %1$s: document name (delivery note), %2$s: HTML link code */
				__( 'Error getting the %1$s AT Code. Try again: %2$s.', 'woo-billing-with-invoicexpress' ),
				__( 'Delivery note', 'woo-billing-with-invoicexpress' ),
				sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( $url ),
					__( 'click here', 'woo-billing-with-invoicexpress' )
				)
			);
			$order_object->add_order_note( $note );
		}
	}
}
