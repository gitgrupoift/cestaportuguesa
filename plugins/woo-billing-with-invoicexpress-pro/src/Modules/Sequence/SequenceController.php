<?php

namespace Webdados\InvoiceXpressWooCommerce\Modules\Sequence;

/* WooCommerce CRUD ready */
/* JSON API ready */

class SequenceController {

	/**
	 * The plugin's instance.
	 *
	 * @since  2.5.2
	 * @access protected
	 * @var    Plugin
	 */
	protected $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.5.2
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

		add_filter( 'invoicexpress_woocommerce_default_sequence', array( $this, 'get_default_sequence' ) );

		if ( get_option( 'hd_wc_ie_plus_invoice_sequence' ) ) {
			add_action(
				'woocommerce_admin_order_data_after_billing_address', array(
					$this,
					'sequenceCustomCheckoutFieldOrderMetaKeys',
				)
			);
			add_action(
				'woocommerce_checkout_update_order_meta', array(
					$this,
					'sequenceCheckoutFieldUpdateOrderMeta',
				)
			);
			add_action(
				'woocommerce_process_shop_order_meta', array(
					$this,
					'sequenceCheckoutFieldUpdateOrderMeta',
				)
			);
		}
	}

	public function sequenceCustomCheckoutFieldOrderMetaKeys( $order_object ) {

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;

		//Sequences cache?
		$cache = get_option( 'hd_wc_ie_plus_sequences_cache' );
		if ( is_array( $cache ) && count( $cache ) > 0 ) {

			foreach ( $cache as $key => $value ) {
				$options[(string)$key] = $value['serie'];
			}

		} else {

			?>
			<p class='form-field form-field-wide'>
			<?php
			printf(
				'<strong>%s</strong> %s',
				__( 'No invoice sequences available.', 'woo-billing-with-invoicexpress' ),
				__( 'Go to the General InvoiceXpress settings screen to reload the sequences.', 'woo-billing-with-invoicexpress' )
			);
			?>
			</p>
			<?php

		}

		$selected_sequence_id = $order_object->get_meta( '_billing_sequence_id' );
		if ( empty( $selected_sequence_id ) ) $selected_sequence_id  = $this->get_default_sequence( '' );
		?>

		<p class='form-field form-field-wide'>
			<?php
			printf(
				'<label for="_billing_sequence_id">%s:</label>',
				esc_html__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ).' - '.esc_html__( 'Sequence for documents', 'woo-billing-with-invoicexpress' )
			);
			?>
			<select id='_billing_sequence_id' name='_billing_sequence_id'>
				<option value="" <?php selected( '', $selected_sequence_id ); ?>><?php _e( 'None', 'woo-billing-with-invoicexpress' ); ?></option>
				<?php
				foreach ( $options as $key => $value ) {
					?>
					<option value="<?php echo $key; ?>"<?php selected($key, $selected_sequence_id); ?>><?php echo $value; ?></option>
					<?php
				}
				?>
			  </select>
		</p>
		<?php
	}

	public function sequenceCheckoutFieldUpdateOrderMeta( $order_id ) {
		$order_object = wc_get_order( $order_id );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;
		
		if ( isset( $_POST['_billing_sequence_id'] ) ) {
			$order_object->update_meta_data( '_billing_sequence_id', sanitize_text_field( $_POST['_billing_sequence_id'] ) );
			$order_object->save();
		}
	}

	public function get_default_sequence( $sequence = '' ) {
		return get_option( 'hd_wc_ie_plus_invoice_sequence_default' );
	}
}
