<?php
/**
 * WooCommerce Yoast SEO plugin file.
 *
 * @package WPSEO/WooCommerce
 */

/**
 * Represents the product's price amount.
 */
class WPSEO_WooCommerce_Product_Price_Amount_Presenter extends WPSEO_WooCommerce_Abstract_Product_Presenter {

	/**
	 * The tag format including placeholders.
	 *
	 * @var string
	 */
	protected $tag_format = '<meta property="product:price:amount" content="%s" />';

	/**
	 * Gets the raw value of a presentation.
	 *
	 * @return string The raw value.
	 */
	public function get() {
		return (string) WPSEO_WooCommerce_Utils::get_product_display_price( $this->product );
	}
}
