<?php

namespace Webdados\InvoiceXpressWooCommerce;

/**
 * Notices
 *
 * @package Webdados
 * @since   2.0.0
 */
class Notices {

	/**
	 * Add notice.
	 *
	 * @param string $message The notice message.
	 * @param string $type    The notice type.
	 *                        Can be success, error, warning or info.
	 * @return void
	 */
	public static function add_notice( $message, $type = 'success' ) {

		$admin_notices = get_option( 'hd_wc_ie_plus_notices', array() );

		$admin_notices[] = array(
			'type'    => $type,
			'message' => $message,
		);

		update_option( 'hd_wc_ie_plus_notices', $admin_notices );
	}

	/**
	 * Output notices.
	 *
	 * @param  array $notices Array of notices.
	 * @return void
	 */
	public static function output_notices() {

		$notices = get_option( 'hd_wc_ie_plus_notices', [] );

		if ( empty( $notices ) ) {
			return;
		}

		//BIG HACK: WooCommerce Admin exists and notices exist so there must be a .woocommerce-layout__activity-panel-tab-wordpress-notices and we need to click it
		//From 0.23.2 we can use a filter: https://github.com/woocommerce/woocommerce-admin/pull/3391
		if ( function_exists( 'wc_admin_url' ) ) {
			if ( version_compare( WC_ADMIN_VERSION_NUMBER, '0.23.2', '<' ) ) {
				?>
				<script type="text/javascript">
					var wc_admin_notices_interval_counter = 0;
					var wc_admin_notices_interval = setInterval(function() {
						wc_admin_notices_interval_counter++;
						if ( jQuery('.woocommerce-layout__activity-panel-tab-wordpress-notices').length && jQuery('.notice-ixwc').length ) {
							clearInterval( wc_admin_notices_interval );
							setTimeout(function(){
								jQuery('.woocommerce-layout__activity-panel-tab-wordpress-notices').click();
							}, 1000);
						} else {
							if ( wc_admin_notices_interval_counter >= 30 ) { //Stop after 6 seconds if not found
								clearInterval( wc_admin_notices_interval );
							}
						}
					}, 200);
				</script>
				<?php
			}
		}

		foreach ( $notices as $notice ) {
			static::output_notice( $notice );
		}

		update_option( 'hd_wc_ie_plus_notices', array() );
	}

	/**
	 * Output notice.
	 *
	 * @param  array $notice The notice data.
	 * @return void
	 */
	public static function output_notice( $notice ) {

		if ( empty( $notice['message'] ) ) {
			return;
		}

		printf(
			'<div class="notice notice-%1$s notice-ixwc">
				<p>%2$s</p>
			</div>',
			esc_attr( ! empty( $notice['type'] ) ? $notice['type'] : 'success' ),
			wp_kses_post( $notice['message'] )
		);
	}
}
