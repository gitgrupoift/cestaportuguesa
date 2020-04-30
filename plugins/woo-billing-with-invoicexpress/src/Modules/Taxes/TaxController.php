<?php
namespace Webdados\InvoiceXpressWooCommerce\Modules\Taxes;

use Webdados\InvoiceXpressWooCommerce\BaseController as BaseController;
use Webdados\InvoiceXpressWooCommerce\Modules\Vat\VatController as VatController;

/* WooCommerce CRUD ready */

class TaxController extends BaseController {

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {

		add_action(
			'woocommerce_admin_order_data_after_billing_address', array(
				$this,
				'taxExemptionField',
			)
		);
		//Save exemption - Frontend
		add_action(
			'woocommerce_checkout_update_order_meta', array(
				$this,
				'taxExemptionFieldUpdateOrderMetaFrontend',
			)
		);
		//Save exemption - Backend
		add_action(
			'woocommerce_process_shop_order_meta', array(
				$this,
				'taxExemptionFieldUpdateOrderMeta',
			)
		);
	}

	public function taxExemptionField( $order_object ) {

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;

		$exempt = false;
		$taxes  = $order_object->get_taxes();
		foreach ( $taxes as $tax ) {
			if ( floatval( $tax->get_tax_total() ) === 0 && floatval( $tax->get_shipping_tax_total() ) === 0 ) {
				$exempt = true;
			}
		}

		if (
			floatval( $order_object->get_total() ) > 0
			&&
			(
				( get_option( 'hd_wc_ie_plus_tax_country' ) && $exempt )
				||
				( get_option( 'hd_wc_ie_plus_tax_country' ) && ( $order_object->get_total_tax() == 0 ) )
			)
		) {

			$selected_exemption_reason = $order_object->get_meta( '_billing_tax_exemption_reason' );

			$exemptions = array(
				'M01' => 'Artigo 16.º n.º 6 alínea c) do CIVA',
				'M02' => 'Artigo 6.º do Decreto‐Lei n.º 198/90, de 19 de Junho',
				'M03' => 'Exigibilidade de caixa',
				'M04' => 'Isento - Artigo 13.º do CIVA',
				'M05' => 'Isento - Artigo 14.º do CIVA',
				'M06' => 'Isento - Artigo 15.º do CIVA	',
				'M07' => 'Isento - Artigo 9.º do CIVA',
				'M08' => 'IVA - Autoliquidação',
				'M09' => 'IVA - não confere direito a dedução',
				'M10' => 'Regime de isenção de IVA - Artigo 53.º do CIVA',
				'M11' => 'Não tributado',
				'M12' => 'Regime da margem de lucro – Agências de Viagens',
				'M13' => 'Regime da margem de lucro – Bens em segunda mão',
				'M14' => 'Regime da margem de lucro – Objetos de arte',
				'M15' => 'Regime da margem de lucro – Objetos de coleção e antiguidades',
				'M16' => 'Isento - Artigo 14.º do RITI',
				'M99' => 'Não sujeito; não tributado (ou similar)',
			);

			?>
		<div class="options_group">
			<p class='form-field form-field-wide'>
				<label for='_billing_tax_exemption_reason'><?php _e( 'Tax Exemption', 'woo-billing-with-invoicexpress' ); ?>:</label>
				<select id='_billing_tax_exemption_reason' name='_billing_tax_exemption_reason'>
					<option value="" <?php selected( '', $selected_exemption_reason ); ?>><?php _e( 'No Exemption', 'woo-billing-with-invoicexpress' ); ?></option>
					<?php foreach ( $exemptions as $key => $value ) { ?>
						<option value="<?php echo $key; ?>" <?php selected( $key, $selected_exemption_reason ); ?>><?php echo $value; ?></option>
					<?php } ?>
				</select>
			</p>
		</div>
			<?php
		} else {
			$order_object->delete_meta_data( '_billing_tax_exemption_reason' );
			$order_object->save();
		}
	}

	public function taxExemptionFieldUpdateOrderMetaFrontend( $order_id ) {
		$order_object = wc_get_order( $order_id );
		// Apply exemption?
		if (
			// Store is Portuguese.
			( '1' == get_option( 'hd_wc_ie_plus_tax_country' ) )
			// No tax in order.
			&& ( 0 == floatval( $order_object->get_total_tax() ) )
			// Default exemption is set
			&& ( get_option( 'hd_wc_ie_plus_exemption_reason' ) )
		) {
			$order_object->update_meta_data( '_billing_tax_exemption_reason', get_option( 'hd_wc_ie_plus_exemption_reason' ) );
			$order_object->save();
		}
	}

	public function taxExemptionFieldUpdateOrderMeta( $order_id ) {
		$order_object = wc_get_order( $order_id );

		//We only invoice regular orders, not subscriptions or other special types of orders
		if ( ! $this->plugin->is_valid_order_type( $order_object ) ) return;
		
		if ( isset( $_POST['_billing_tax_exemption_reason'] ) ) {
			$order_object->update_meta_data( '_billing_tax_exemption_reason',  sanitize_text_field( $_POST['_billing_tax_exemption_reason'] ) );
			$order_object->save();
		}
	}

}
