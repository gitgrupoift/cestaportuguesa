<?php
namespace Webdados\InvoiceXpressWooCommerce\LicenseManager;

class License {

	/**
	 * The API url.
	 *
	 * @since 2.0.0
	 * @var   string
	 */
	public $api_url;

	/**
	 * The license key
	 *
	 * @access private
	 * @since  2.1.9
	 * @var    string
	 */
	private $licence_key;

	/**
	 * The license checksum option name
	 *
	 * @access private
	 * @since  2.1.9
	 * @var    string
	 */
	private $licence_checksum_option;

	/**
	 * The plugin product ID on the licensing system
	 *
	 * @access private
	 * @since  2.1.9
	 * @var    string
	 */
	private $product_id;

	/**
	 * The license domain
	 *
	 * @access private
	 * @since  2.1.9
	 * @var    string
	 */
	private $domain;

	/**
	 * The license last checkec option
	 *
	 * @access private
	 * @since  2.1.9
	 * @var    string
	 */
	private $licence_last_check_option;

	/**
	 * Constructor.
	 *
	 * @param string $api_url
	 * @param string $slug
	 * @param string $plugin
	 */
	public function __construct( $api_url, $licence_key, $licence_checksum_option, $product_id, $domain, $licence_last_check_option ) {
		$this->api_url                   = $api_url;
		$this->licence_key               = $licence_key;
		$this->licence_checksum_option   = $licence_checksum_option;
		$this->product_id                = $product_id;
		$this->domain                    = $domain;
		$this->licence_last_check_option = $licence_last_check_option;
	}

	/**
	 * Verifies the license key.
	 *
	 * @since  2.0.0
	 * @return bool
	 */
	public function verify() {

		if ( empty( $this->licence_key ) ) {
			return false;
		}

		return ( ! empty( get_option( $this->licence_checksum_option, '' ) ) ) && get_option( $this->licence_checksum_option, '' ) == md5( $this->licence_key );
	}

	/**
	 * Validates the license key.
	 *
	 * @since  2.0.0
	 * @param  string $value The license key value.
	 * @return bool True if the license is valid. False, otherwise.
	 */
	public function validate( $value ) {

		if ( empty( $value ) ) {
			return false;
		}

		// If there's a checksum we don't need to revalidate the license.
		if ( empty( get_option( $this->licence_checksum_option, '' ) ) ) {
			$args = array(
				'woo_sl_action'     => 'activate',
				'licence_key'       => $value,
				'product_unique_id' => $this->product_id,
				'domain'            => $this->domain,
			);

			$request_uri = $this->api_url . '?' . http_build_query( $args, '', '&' );
			$data        = wp_remote_get( $request_uri );

			if ( empty( $data ) || $data['response']['code'] !== 200 ) {
				$error_notice = sprintf(
					/* translators: %s: license manager link */
					esc_html__( 'There was a problem connecting to %s.', 'woo-billing-with-invoicexpress' ),
					$this->api_url
				);
				Notices::add_notice(
					$error_notice,
					'error'
				);
				do_action( 'invoicexpress_woocommerce_error', $error_notice );
			}

			$response_block = json_decode( $data['body'] );
			$response_block = $response_block[ count( $response_block ) - 1 ];
			$response       = $response_block->message;

			if ( isset( $response_block->status ) ) {

				// The license is active and the software is active.
				if (
				$response_block->status === 'success' &&
				(
					$response_block->status_code === 's100' ||
					$response_block->status_code === 's101'
				)
				) {
					update_option( $this->licence_checksum_option, md5( $value ) );
					do_action( 'invoicexpress_woocommerce_debug', 'License validated and activated' );
					return true;
				} else {
					do_action( 'invoicexpress_woocommerce_error', 'License not validated' );
				}
			}
		} else {
			return get_option( $this->licence_checksum_option, '' ) == md5( $value );
		}

		update_option( $this->licence_last_check_option, time() );
		return false;
	}
}
