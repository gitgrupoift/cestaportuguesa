<?php
// phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar

namespace Webdados\InvoiceXpressWooCommerce;

use \Webdados\InvoiceXpressWooCommerce\Settings\Settings;

abstract class BaseMenu {

	/**
	 * The plugin's instance.
	 *
	 * @since  2.0.4
	 * @access protected
	 * @var    Plugin
	 */
	protected $plugin;

	/**
	 * The current tab.
	 *
	 * @since  2.0.0
	 * @access protected
	 * @var    string
	 */
	protected $current_tab = '';

	/**
	 * The settings's instance.
	 *
	 * @since  2.0.0
	 * @access protected
	 * @var    Settings
	 */
	protected $settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.0.0
	 * @param Settings $settings This settings's instance.
	 */
	public function __construct( Settings $settings, Plugin $plugin ) {
		$this->settings = $settings;
		$this->plugin = $plugin;
	}

	/**
	 * Adds the submenu page into Woocommmerce menu.
	 *
	 * @since 2.0.0 Refactored the submenu page.
	 *              Code review.
	 * @since 1.0.0
	 */
	public function admin_page() {

		add_submenu_page(
			'woocommerce',
			sprintf(
				/* translators: %1$s: plugin name, %2$s plugin version */
				esc_html__( '%1$s %2$s - Settings', 'woo-billing-with-invoicexpress' ),
				INVOICEXPRESS_WOOCOMMERCE_PLUGIN_NAME,
				INVOICEXPRESS_WOOCOMMERCE_VERSION
			),
			sprintf(
				/* translators: %s: plugin edition (free or pro) */
				esc_html__( 'InvoiceXpress %s', 'woo-billing-with-invoicexpress' ),
				INVOICEXPRESS_WOOCOMMERCE_PLUGIN_EDITION
			),
			'manage_woocommerce',
			'invoicexpress_woocommerce',
			array( $this, 'options_page' )
		);
	}

	/**
	 * Adds options page.
	 *
	 * @todo move styles and scripts to external assets.
	 *
	 * @since  2.0.0
	 * @return void
	 */
	public function options_page() {

		if ( $this->plugin->wpml_active ) {
			//Load WooCommerce Multilingual Pointers CSS so we can use the WPML logo
			wp_enqueue_style( 'wcml-pointers' );
		}

		$this->current_tab = 'ix_licensing_api_settings';
		if ( ! empty( $_GET['tab'] ) ) {
			$this->current_tab = sanitize_title( wp_unslash( $_GET['tab'] ) ); // WPCS: input var okay, CSRF ok.
		}
		?>
		<?php $this->options_page_css(); ?>
		<div class="wrap woocommerce">
			<h1>
			<?php
			printf(
				/* translators: plugin name and version */
				esc_html__( '%1$s %2$s - Settings', 'woo-billing-with-invoicexpress' ),
				INVOICEXPRESS_WOOCOMMERCE_PLUGIN_NAME,
				INVOICEXPRESS_WOOCOMMERCE_VERSION
			); // WPCS: XSS ok.
			?>
			</h1>
			<?php
			if ( $this->plugin->wpml_active ) {
				global $sitepress;
				$default_locale = '';
				$languages = apply_filters( 'wpml_active_languages', NULL );
				foreach( $languages as $l ) {
					if ( $l['code'] == $sitepress->get_default_language() ) {
						$default_locale = $l['default_locale'];
						break;
					}
				}
				if ( version_compare( get_bloginfo( 'version' ), '5.0', '>=' ) ) {
					/* determine_locale() is 5.0 or above only */
					if ( determine_locale() != $default_locale ) {
						?>
						<div class="notice notice-error">
							<p>
								<?php printf (
									/* translators: %1$s: link tag opening, %2$s: link tag closing */
									__( 'Because you’re using WPML, we highly recommend that you %1$sset your WP-Admin language%2$s to the default WPML language, when setting up Invoicing with InvoiceXpress for WooCommerce.' , 'woo-billing-with-invoicexpress' ),
									'<a href="/wp-admin/profile.php">',
									'</a>'
								); ?>
							</p>
							<p>
								<?php printf(
									/* translators: %s: locale */
									__( 'Current WP-Admin language: %s' , 'woo-billing-with-invoicexpress' ),
									sprintf(
										'<strong>%s</strong>',
										determine_locale()
									)
								); ?>
								<br/>
								<?php printf(
									/* translators: %s: locale */
									__( 'Default WPML language: %s' , 'woo-billing-with-invoicexpress' ),
									sprintf(
										'<strong>%s</strong>',
										$default_locale
									)
								); ?>
							</p>
						</div>
						<?php
					}
				}
			}
			?>
			<?php $this->plugin_options_tabs(); ?>
			<?php $this->options_page_plugin_info(); ?>
			<div id="ix_plugin_settings">
				<?php $this->settings->output_tab_top( $this->current_tab ); ?>
				<form id="invoicexpress_woocommerce_form" action="options.php" method="post">
					<?php settings_errors(); ?>
					<?php settings_fields( $this->current_tab ); ?>
					<div>
						<?php $this->settings->output_tab( $this->current_tab ); ?>
					</div>
					<?php submit_button(); ?>
				</form>
				<?php $this->settings->output_tab_bottom( $this->current_tab ); ?>
			</div>
		</div>
		<?php $this->options_page_js(); ?>
		<?php
	}
	public function options_page_css() {
		?>
		<style type="text/css">
			#ix_plugin_info {
				display: none;
			}
			@media only screen and (min-width: 783px) {
				#ix_plugin_info {
					display: block;
					float: right;
					width: 200px;
					padding: 15px;
					margin-top: 1em;
					background-color: #fff;
				}
				#ix_plugin_info a {
					display: block;
				}
				#ix_plugin_info img {
					max-width: 100%;
				}
				#ix_plugin_settings {
					width: calc( 100% - 250px );
					float: left;
				}
			}
			#ix_mainform h2 {
				padding-top: 1em;
			}

			#ix_mainform h2:not(:first-child) {
				border-top: 1px solid #CCCCCC;
				padding-top: 1.5em;
			}

			p.description.ix_error,
			.ix_error {
				color: #FF0000;
				font-weight: bold;
			}

			.ix_ok {
				color: #008C00;
				font-weight: bold;
			}
		</style>
		<?php
	}
	public function options_page_js() {
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {

				//Show and hide fields
				function ix_show_hide_fiels() {
					// First run.
					$( '.has-parent-field' ).each( function() {
						var parent_field = $( this ).find( 'td span.parent_field_data' ).attr( 'data-parent-field' );
						var parent_value = $( this ).find( 'td span.parent_field_data' ).attr( 'data-parent-value' );
						var value = $( '#' + parent_field ).val();
						if ( $('#'+parent_field).attr( 'type' ) == 'checkbox' ) {
							if ( ! $('#'+parent_field).is( ':checked' ) ) {
								value = '';
							};
						}

						// If the parent is not visible, maybe there's a grand parent.
						if ( value === parent_value ) {
							$( this ).show();
						} else {
							$( this ).hide();
						}
					});

					// Second run - for grand children.
					$( '.has-parent-field' ).each( function() {
						if ( $( this ).is( ':visible' ) ) {
							var parent_field = $( this ).find( 'td span.parent_field_data' ).attr( 'data-parent-field' );
							if ( ! $( '#'+parent_field ).is( ':visible' ) ) {
								$( this ).hide();
							}
						}
					});
				}

				ix_show_hide_fiels();

				$( '.ix_form_field' ).change( function() {
					ix_show_hide_fiels();
				} );

				//Update scheduled documents count
				if ( $( '#pending_scheduled_guide_documents' ).length ) {
					var data = {
						'action': 'hd_invoicexpress_count_scheduled_documents',
						'type':   'guide'
					};
					jQuery.post(ajaxurl, data, function(response) {
						$( '#pending_scheduled_guide_documents' ).html( response );
					});
				}
				if ( $( '#pending_scheduled_invoicing_documents' ).length ) {
					var data = {
						'action': 'hd_invoicexpress_count_scheduled_documents',
						'type':   'invoicing'
					};
					jQuery.post(ajaxurl, data, function(response) {
						$( '#pending_scheduled_invoicing_documents' ).html( response );
					});
				}

				//Plugin update
				if ( $( '.ix_api_version_update' ).length ) {
					
					$( '.ix_api_version_update' ).each(function( el ) {
						var id     = $( this ).attr( 'id' );
						var plugin = $( this ).data( 'plugin' );
						var interval = setInterval( function() {
							$( '#'+id ).append( '.' );
						}, 100 );
						var data = {
							'action': 'hd_invoicexpress_check_update_version',
							'plugin': plugin
						};
						jQuery.post(ajaxurl, data, function( response ) {
							clearInterval( interval );
							$( '#'+id ).html( response );
						});
					});
				}

			});
		</script>
		<?php
	}

	/**
	 * Show plugin info sidebar.
	 *
	 * @return void
	 */
	public function options_page_plugin_info() {
		?>
		<div id="ix_plugin_info">
			<p>
				<img src="<?php echo esc_url( plugins_url( 'assets/images/logo.svg', INVOICEXPRESS_WOOCOMMERCE_PLUGIN_FILE ) ); ?>" width="200" height="104" alt="<?php echo esc_attr( INVOICEXPRESS_WOOCOMMERCE_PLUGIN_NAME ); ?>" title="<?php echo esc_attr( INVOICEXPRESS_WOOCOMMERCE_PLUGIN_NAME ); ?>"/>
			</p>
			<hr/>
			<h4>
				<?php esc_html_e( 'Commercial InvoiceXpress information:', 'woo-billing-with-invoicexpress' ); ?>
			</h4>
			<p>
				<a href="https://www.app.invoicexpress.com/accounts/new?token=webdadoslda-1_bb491d" target="_blank">
					<img src="<?php echo esc_url( plugins_url( 'assets/images/invoicexpress.png', INVOICEXPRESS_WOOCOMMERCE_PLUGIN_FILE ) ); ?>" width="203" height="25" alt="InvoiceXpress" title="InvoiceXpress"/>
				</a>
			</p>
			<hr/>
			<h4>
				<?php esc_html_e( 'WordPress/WooCommerce development:', 'woo-billing-with-invoicexpress' ); ?>
			</h4>
			<p>
				<a href="https://www.webdados.pt" target="_blank">
					<img src="<?php echo esc_url( plugins_url( 'assets/images/webdados.svg', INVOICEXPRESS_WOOCOMMERCE_PLUGIN_FILE ) ); ?>" alt="Webdados" title="Webdados" width="200" height="69"/>
				</a>
			</p>
			<hr/>
			<h4>
				<?php esc_html_e( 'Useful links:', 'woo-billing-with-invoicexpress' ); ?>
			</h4>
			<?php
			$links = array(
				array(
					'text' => __( 'Buy Pro version', 'woo-billing-with-invoicexpress' ),
					'url'  => __( 'https://shop.webdados.com/product/invoicing-with-invoicexpress-for-woocommerce-pro/', 'woo-billing-with-invoicexpress' ),
				),
				array(
					'text' => __( 'Get an InvoiceXpress free trial', 'woo-billing-with-invoicexpress' ),
					'url'  => 'https://www.app.invoicexpress.com/accounts/new?token=webdadoslda-1_bb491d',
				),
				array(
					'text' => __( 'Pro plugin website', 'woo-billing-with-invoicexpress' ),
					'url'  => __( 'https://invoicewoo.com', 'woo-billing-with-invoicexpress' ),
				),
				array(
					'text' => __( 'Free plugin on WordPress.org', 'woo-billing-with-invoicexpress' ),
					'url'  => __( 'https://wordpress.org/plugins/woo-billing-with-invoicexpress/', 'woo-billing-with-invoicexpress' ),
				),
				array(
					'text' => __( 'Documentation', 'woo-billing-with-invoicexpress' ),
					'url'  => __( 'https://invoicewoo.com/documentation/', 'woo-billing-with-invoicexpress' ),
				),
				array(
					'text' => __( 'Pro plugin technical support', 'woo-billing-with-invoicexpress' ),
					'url'  => __( 'https://invoicewoo.com/documentation/requesting-technical-support/', 'woo-billing-with-invoicexpress' ),
				),
				array(
					'text' => __( 'Free plugin support forum', 'woo-billing-with-invoicexpress' ),
					'url'  => __( 'https://wordpress.org/support/plugin/woo-billing-with-invoicexpress', 'woo-billing-with-invoicexpress' ),
				),
			);
			?>
			<ul>
				<?php
				foreach ( $links as $link ) {
					?>
					<li>
						<a href="<?php echo esc_url( trim( $link['url'] ) ); ?>" target="_blank">
							<?php echo wp_kses_post( $link['text'] ); ?>
						</a>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Adds options tabs.
	 *
	 * @since  2.0.0.
	 * @return void
	 */
	public function plugin_options_tabs() {
		echo '<nav class="nav-tab-wrapper woo-nav-tab-wrapper">';

		$tabs = $this->settings->tabs;

		foreach ( $tabs as $tab_key => $settings ) {

			if ( empty( $settings ) ) {
				continue;
			}

			printf( // WPCS: XSS ok.
				'<a href="?page=%1$s&amp;tab=%2$s" class="nav-tab%3$s">
					%4$s
				</a>',
				'invoicexpress_woocommerce',
				$tab_key,
				$this->current_tab === $tab_key ? ' nav-tab-active' : '',
				$settings['title']
			);
		}

		echo '</nav>';
	}

	/**
	 * Shows admin notices.
	 *
	 * @since  2.0.0
	 * @return void
	 */
	public function show_admin_notices() {
		Notices::output_notices();
	}

	/**
	 * Adds action links.
	 *
	 * @since 2.0.0 Code review.
	 * @since 1.0.0
	 */
	public function add_action_link( $links, $file ) {

		$custom_links = array(
			sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( 'admin.php?page=invoicexpress_woocommerce' ) ),
				esc_html__( 'Settings', 'woo-billing-with-invoicexpress' )
			),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_html_x( 'https://invoicewoo.com/documentation/', 'Documentation URL', 'woo-billing-with-invoicexpress' ),
				esc_html__( 'Documentation', 'woo-billing-with-invoicexpress' )
			),
		);

		return array_merge( $links, $custom_links );
	}

	/**
	 * Rewrites rule to change endpoint url.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function invoicexpress_api_rewrite_rule() {
		add_rewrite_rule( 'invoicexpress/download_pdf', 'index.php?invoicexpress_api_download_pdf=1', 'top' );
		add_rewrite_rule( 'invoicexpress/get_at_code', 'index.php?invoicexpress_api_get_at_code=1', 'top' );
		flush_rewrite_rules();
	}

	/**
	 * Adds internal endpoint.
	 *
	 * @since  1.0.0
	 * @param  array $query_vars Default query vars.
	 * @return array
	 */
	public function invoicexpress_api_query_var( $query_vars ) {
		$query_vars[] = 'invoicexpress_api_download_pdf';
		$query_vars[] = 'invoicexpress_api_get_at_code';
		return $query_vars;
	}

	/**
	 * Adds file to endpoint.
	 *
	 * @since  1.0.0
	 * @param  WP $wp Current WordPress environment instance (passed by reference).
	 * @return void
	 */
	public function invoicexpress_api_parse_request( &$wp ) {

		if ( array_key_exists( 'invoicexpress_api_download_pdf', $wp->query_vars ) ) {
			new ReDownloadPDF( $this->plugin );
			exit();
		}

		if ( array_key_exists( 'invoicexpress_api_get_at_code', $wp->query_vars ) ) {
			$order_object = wc_get_order( intval( $_GET['order_id'] ) );
			do_action( 'hd_wc_ie_refetch_at_code', $order_object, intval( $_GET['document_id'] ), 'manual' );
			wp_redirect( get_edit_post_link( intval( $_GET['order_id'] ), '' ) );
			die;
		}

	}
}
