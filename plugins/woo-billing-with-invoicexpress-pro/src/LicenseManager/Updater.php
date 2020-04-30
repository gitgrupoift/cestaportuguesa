<?php
namespace Webdados\InvoiceXpressWooCommerce\LicenseManager;

class Updater {

	/**
	 * The API url.
	 *
	 * @since 2.0.0
	 * @var   string
	 */
	public $api_url;

	/**
	 * The plugin path.
	 *
	 * @since 2.0.0
	 * @var   string
	 */
	public $plugin;

	/**
	 * The plugin slug.
	 *
	 * @access private
	 * @since  2.0.0
	 * @var    string
	 */
	private $slug;

	/**
	 * The plugin version.
	 *
	 * @access private
	 * @since  2.1.9
	 * @var    string
	 */
	private $plugin_version;

	/**
	 * The plugin product ID on the licensing system
	 *
	 * @access private
	 * @since  2.1.9
	 * @var    string
	 */
	private $product_id;

	/**
	 * The license key
	 *
	 * @access private
	 * @since  2.1.9
	 * @var    string
	 */
	private $licence_key;

	/**
	 * The license domain
	 *
	 * @access private
	 * @since  2.1.9
	 * @var    string
	 */
	private $domain;

	/**
	 * Constructor.
	 *
	 * @param string $api_url
	 * @param string $slug
	 * @param string $plugin
	 */
	public function __construct( $api_url, $slug, $plugin, $plugin_version, $product_id, $licence_key, $domain ) {
		$this->api_url        = $api_url;
		$this->slug           = $slug;
		$this->plugin         = $plugin;
		$this->plugin_version = $plugin_version;
		$this->product_id     = $product_id;
		$this->licence_key    = $licence_key;
		$this->domain         = $domain;
	}

	/**
	 * Runs in a cron thread, or in a visitor thread if triggered
	 * by _maybe_update_plugins(), or in an auto-update thread.
	 *
	 * @since  2.0.0
	 * @param  object $transient The update_plugins transient object.
	 * @return object The same or a modified version of the transient.
	 */
	public function check_for_plugin_update( $checked_data ) {

		if ( empty( $checked_data->checked ) ) {
			return $checked_data;
		}
		
		if ( $response = $this->get_plugin_update_information( 'plugin_update' ) ) {
			if ( is_object( $response ) && ! empty( $response ) ) {
				$checked_data->response[ $this->plugin ] = $response;
			}
		}

		return $checked_data;
	}

	/**
	 * Gets plugin update information
	 *
	 * @since  2.4.3
	 * @param  object $transient The update_plugins transient object.
	 * @return object The same or a modified version of the transient.
	 */
	public function get_plugin_update_information( $method = 'plugin_information' ) {
		$request_string = $this->prepare_request( $method );

		// Start checking for an update.
		$request_uri = $this->api_url . '?' . http_build_query( $request_string, '', '&' );

		$data = wp_remote_get( $request_uri );
		if ( is_wp_error( $data ) || $data['response']['code'] !== 200 ) {
			return false;
		}

		$response_block = json_decode( $data['body'] );
		if ( ! is_array( $response_block ) || count( $response_block ) < 1 ) {
			return false;
		}

		// Retrieve the last message within the $response_block.
		$response_block = $response_block[ count( $response_block ) - 1 ];
		$response       = isset( $response_block->message ) ? $response_block->message : '';
		if ( is_object( $response ) && ! empty( $response ) ) {
			$response = $this->postprocess_response( $response );
			return $response;
		}
		return false;
	}

	/**
	 * Filter the response for the current WordPress.org Plugin Installation API request.
	 *
	 * @since  2.0.0
	 * @param  false|object|array $result The result object or array. Default false.
	 * @param  string             $action The type of information being requested from the Plugin Installation API.
	 * @param  object             $args   Plugin API arguments.
	 * @return false|object|array
	 */
	public function plugins_api_call( $result, $action, $args ) {

		// Not our plugin - Do not mess with it.
		if ( isset( $args->slug ) && $this->slug !== $args->slug ) {
			return $result;
		}

		if ( ! is_object( $args ) || ! isset( $args->slug ) || $this->slug !== $args->slug ) {
			return $result;
		}

		$request_string = $this->prepare_request( $action, $args );
		if ( $request_string === false ) {
			return new WP_Error( 'plugins_api_failed', __( 'An error occurred while trying to identify the plugin.', 'woo-billing-with-invoicexpress' ) . '&lt;/p> &lt;p>&lt;a href=&quot;?&quot; onclick=&quot;document.location.reload(); return false;&quot;>' . __( 'Try again', 'woo-billing-with-invoicexpress' ) . '&lt;/a>' );
		}

		$request_uri = $this->api_url . '?' . http_build_query( $request_string, '', '&' );

		$data = wp_remote_get( $request_uri );
		if ( is_wp_error( $data ) || $data['response']['code'] !== 200 ) {
			return new WP_Error( 'plugins_api_failed', __( 'An Unexpected HTTP Error occurred during the API request.', 'woo-billing-with-invoicexpress' ) . '&lt;/p> &lt;p>&lt;a href=&quot;?&quot; onclick=&quot;document.location.reload(); return false;&quot;>' . __( 'Try again', 'woo-billing-with-invoicexpress' ) . '&lt;/a>', $data->get_error_message() );
		}

		$response_block = json_decode( $data['body'] );

		// Retrieve the last message within the $response_block.
		$response_block = $response_block[ count( $response_block ) - 1 ];
		$response       = $response_block->message;
		if ( is_object( $response ) && ! empty( $response ) ) {

			// Include slug and plugin data.
			$response = $this->postprocess_response( $response );

			return $response;
		}

		return $result;
	}

	/**
	 * Prepare request.
	 *
	 * @since  2.0.0
	 * @param  string $action
	 * @param  array  $args
	 * @return array
	 */
	public function prepare_request( $action, $args = array() ) {
		global $wp_version;

		return array(
			'woo_sl_action'     => $action,
			'version'           => $this->plugin_version,
			'product_unique_id' => $this->product_id,
			'licence_key'       => $this->licence_key,
			'domain'            => $this->domain,
			'wp-version'        => $wp_version,
			'api_version'       => '1.1',
		);
	}

	/**
	 * Process the response gotten from post
	 *
	 * @since  2.4.2
	 * @param  string $action
	 * @param  array  $args
	 * @return array
	 */

	 private function postprocess_response( $response ) {
		//include slug and plugin data
		$response->slug    =   $this->slug;
		$response->plugin  =   $this->plugin;
		//if sections are being set
		if ( isset ( $response->sections ) )
			$response->sections = (array)$response->sections;

		//if banners are being set
		if ( isset ( $response->banners ) )
			$response->banners = (array)$response->banners;

		//if icons being set, convert to array
		if ( isset ( $response->icons ) )
			$response->icons    =   (array)$response->icons;

		return $response;
	}

	/**
	 * Gets update information about the plugin - Moved from Settings.php on 2.4.9
	 *
	 * @since  2.4.3
	 * @return array
	 */
	public function get_version_update_information( $plugin ) {
		//Verify from transient first
		if ( false === ( $updated_version = get_transient( 'hd_wc_ie_plus_updated_version_'.$plugin ) ) ) {
			if ( $information = $this->get_plugin_update_information() ) {
				//Set transient
				set_transient( 'hd_wc_ie_plus_updated_version_'.$plugin, $information->version, 2 * HOUR_IN_SECONDS );
				//Return
				return $information->version;
			}
		} else {
			return $updated_version;
		}
		return false;
	}

	public function version_update_information() {
		if ( isset( $_POST['plugin'] ) && trim( $_POST['plugin'] ) !='' ) {
			if ( $_POST['plugin'] == $this->product_id ) {
				if ( $version_update = $this->get_version_update_information( $_POST['plugin'] ) ) {
					if ( version_compare( $version_update, $this->plugin_version, '>' ) ) {
						echo '<a href="update-core.php?force-check=1">'.__( 'Update available', 'woo-billing-with-invoicexpress' ).': '.$version_update.'</a>';
					} else {
						echo __( 'Plugin up to date', 'woo-billing-with-invoicexpress' );
					}
				} else {
					echo __( 'Unable to get update information', 'woo-billing-with-invoicexpress' ); 
				}
			} else {
				if ( has_action( 'wp_ajax_hd_invoicexpress_check_update_version_'.trim( $_POST['plugin'] ) ) ) {
					do_action( 'wp_ajax_hd_invoicexpress_check_update_version_'.trim( $_POST['plugin'] ) );
				} else {
					echo __( 'Unable to get update information', 'woo-billing-with-invoicexpress' ); 
				}
			}
		} else {
			echo __( 'Unable to get update information', 'woo-billing-with-invoicexpress' ); 
		}
		wp_die();
	}

}
