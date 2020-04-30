<?php

namespace Webdados\InvoiceXpressWooCommerce\Pro;

/**
 * Automatic ocuments scheduler
 *
 * @package Webdados
 * @since   2.3.0
 */
class DocumentsScheduler {

	/**
	 * The plugin's instance.
	 *
	 * @since  2.0.4
	 * @access protected
	 * @var    Plugin
	 */
	protected $plugin;

	/* Scheduled documents table name */
	public $scheduled_docs_table = 'wc_ie_scheduled_docs';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.3.0
	 * @param Plugin $plugin This plugin's instance.
	 */
	public function __construct( \Webdados\InvoiceXpressWooCommerce\Plugin $plugin ) {
		$this->plugin = $plugin;
		$this->plugin->scheduled_docs_table = $this->scheduled_docs_table; //So that other modules (like UpgradeFunctions) can use it
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.3.0
	 */
	public function register_hooks() {
		add_filter( 'invoicexpress_woocommerce_has_pending_scheduled_invoicing_document', array( $this, 'order_has_pending_scheduled_invoicing_document' ), 10, 2 );
		add_filter( 'invoicexpress_woocommerce_has_pending_scheduled_guide_document', array( $this, 'order_has_pending_scheduled_guide_document' ), 10, 2 );
		add_filter( 'invoicexpress_woocommerce_check_pending_scheduled_document', array( $this, 'check_pending_scheduled_document' ), 10, 3 );
		add_filter( 'invoicexpress_woocommerce_delay_automatic_document_time_readable', array( $this, 'readable_scheduled_delay' ) );
		add_action( 'invoicexpress_woocommerce_cron_five_minutes', array( $this, 'cron_run_scheduled_docs' ) );
		add_action( 'wp_ajax_hd_invoicexpress_count_scheduled_documents', array( $this, 'wp_ajax_count_scheduled_documents' ) );
	}

	/**
	 * Schedule automatic document creation to later
	 *
	 * @since  2.3.0
	 * @param  object $order_object The order
	 * @param  string $document_type The document type
	 * @return array
	 */
	public function schedule_automatic_document( $order_object, $document_type ) {
		global $wpdb;

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;
		
		switch ( $document_type ) {
			case 'transport_guide':
				$note_document = __( 'Delivery note', 'woo-billing-with-invoicexpress' );
				//Get interval from guides options
				$interval = apply_filters( 'invoicexpress_woocommerce_delay_automatic_guide_time', 'PT2M' );
				break;
			case 'invoice':
				$note_document = __( 'Invoice', 'woo-billing-with-invoicexpress' );
			case 'simplified_invoice':
				$note_document = __( 'Simplified invoice', 'woo-billing-with-invoicexpress' );
			case 'invoice_receipt':
				$note_document = __( 'Invoice-receipt', 'woo-billing-with-invoicexpress' );
			case 'vat_moss_invoice':
				$note_document = __( 'VAT MOSS invoice', 'woo-billing-with-invoicexpress' );
			default:
				//Get interval from invoices options
				$interval = apply_filters( 'invoicexpress_woocommerce_delay_automatic_invoice_time', 'PT2M' );
				break;
		}

		$date = \DateTime::createFromFormat( 'Y-m-d H:i:s', date_i18n( 'Y-m-d H:i:s' ) );
		$add = new \DateInterval( $interval );
		$date->add( $add );
		$date_time = $date->format( 'Y-m-d H:i:s' );

		$table = $wpdb->prefix.$this->scheduled_docs_table;
		$data = array(
			'order_id'      => $order_object->get_id(),
			'document_type' => $document_type,
			'date_time'     => $date_time,
		);
		$format = array( '%d', '%s', '%s' );

		$wpdb->insert( $table, $data, $format ); //We should do error handling

		$note = sprintf(
			/* translators: %1$s: document name, %2$s: date and time */
			__( '%1$s automatic issuing scheduled to %2$s', 'woo-billing-with-invoicexpress' ),
			$note_document,
			$date_time
		);
		$order_object->add_order_note( $note );
		do_action( 'invoicexpress_woocommerce_debug', $note, $order_object );
	}

	/**
	 * Check if there's a pending scheduled document for an order
	 *
	 * @since  2.3.0
	 * @param  bool $bool Default valoue from the filter (always false)
	 * @param  int $order_id Order ID
	 * @param  array $document_types Document types to check for
	 * @return array
	 */
	public function check_pending_scheduled_document( $bool, $order_id, $document_types = array() ) {
		global $wpdb;

		$table = $wpdb->prefix.$this->scheduled_docs_table;

		$where = array( '1' );

		if ( intval( $order_id ) > 0 ) $where[] = " order_id = ".intval( $order_id );

		$where_document_type = array();
		foreach ( $document_types as $document_type ) {
			$where_document_type[] = "document_type LIKE '".trim( $document_type )."'";
		}
		$where[] = '( '.implode( ' OR ', $where_document_type ).' )';

		$query = "SELECT * FROM {$table} WHERE ".implode( ' AND ' , $where );
		$results = $wpdb->get_results( $query );
		return count( $results ); //if 0 => false
	}
	public function order_has_pending_scheduled_invoicing_document( $bool, $order_id ) {
		return $this->check_pending_scheduled_document( $bool, $order_id, array( 'invoice', 'simplified_invoice', 'invoice_receipt', 'vat_moss_invoice' ) );
	}
	public function order_has_pending_scheduled_guide_document( $bool, $order_id ) {
		return $this->check_pending_scheduled_document( $bool, $order_id, array( 'transport_guide' ) );
	}
	public function wp_ajax_count_scheduled_documents() {
		if ( isset( $_REQUEST['type'] ) ) {
			switch( trim( $_REQUEST['type'] ) ) {
				case 'guide':
					echo $this->order_has_pending_scheduled_guide_document( 0, 0 );
					break;
				case 'invoicing':
					echo $this->order_has_pending_scheduled_invoicing_document( 0, 0 );
					break;
			}
		}
		die();
	}

	/**
	 * Readable scheduled delay 
	 *
	 * @since  2.3.0
	 */
	public function readable_scheduled_delay( $time ) {
		$time = trim( $time );
		if ( substr( $time, 0, 2 ) == 'PT' ) {
			$time = substr( $time, 2 );
			switch( substr( $time, -1, 1 ) ) {
				case 'S':
					$time = sprintf( __( '%d second(s)', 'woo-billing-with-invoicexpress' ), substr( $time, 0, -1 ) );
					break;
				case 'M':
					$time = sprintf( __( '%d minute(s)', 'woo-billing-with-invoicexpress' ), substr( $time, 0, -1 ) );
					break;
				case 'H':
					$time = sprintf( __( '%d hour(s)', 'woo-billing-with-invoicexpress' ), substr( $time, 0, -1 ) );
					break;
			}
		} else {
			if ( substr( $time, 0, 1 ) == 'P' ) {
				$time = substr( $time, 1 );
				switch( substr( $time, -1, 1 ) ) {
					case 'D':
						$time = sprintf( __( '%d day(s)', 'woo-billing-with-invoicexpress' ), substr( $time, 0, -1 ) );
						break;
				}
			}
		}
		return $time;
	}

	/**
	 * Process scheduled document cronjob
	 *
	 * @since  2.3.0
	 */
	public function cron_run_scheduled_docs() {
		//Check database
		$this->plugin->maybe_create_scheduled_docs_table();
		//Do it
		$amount = intval( apply_filters( 'invoicexpress_woocommerce_process_scheduled_documents_amount', 2 ) );
		if ( $amount <= 0 ) $amount = 1;
		do_action( 'invoicexpress_woocommerce_debug', sprintf(
			'Running the scheduled documents cronjob - Processing up to %d documents',
			$amount
		) );
		foreach( range( 1, $amount ) as $i ) {
			$this->process_next_scheduled_document();
		}
	}

	/**
	 * Process next pending scheduled document
	 *
	 * @since  2.3.0
	 */
	public function process_next_scheduled_document() {
		global $wpdb;
		$table = $wpdb->prefix.$this->scheduled_docs_table;
		$query = $wpdb->prepare(
			"SELECT * FROM {$table} WHERE date_time <= '%s' ORDER BY date_time ASC, task_id ASC LIMIT 0,1",
			date_i18n( 'Y-m-d H:i:s' )
		);
		if ( $task = $wpdb->get_row( $query ) ) {
			if ( $order_object = wc_get_order( intval( $task->order_id ) ) ) {
				do_action( 'invoicexpress_woocommerce_debug', 'Found scheduled document to issue', $order_object );
				do_action( 'woocommerce_order_action_hd_wc_ie_plus_generate_'.trim( $task->document_type ), $order_object, 'scheduled' );
			}
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$table} WHERE task_id = %d",
				$task->task_id
			) );
			do_action( 'invoicexpress_woocommerce_debug', 'Deleted scheduled task '.intval( $task->task_id ), $order_object );
		}
	}

}
