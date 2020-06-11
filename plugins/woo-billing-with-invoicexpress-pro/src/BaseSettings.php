<?php

namespace Webdados\InvoiceXpressWooCommerce;

use Webdados\InvoiceXpressWooCommerce\CountryTranslation as CountryTranslation;
use Webdados\InvoiceXpressWooCommerce\BaseController as BaseController;
use Webdados\InvoiceXpressWooCommerce\JsonRequest as JsonRequest;

/* WooCommerce CRUD ready */
/* JSON API ready */

class BaseSettings {

	/**
	 * The plugin's instance.
	 *
	 * @since  2.0.4
	 * @access protected
	 * @var    Plugin
	 */
	protected $plugin;

	/**
	 * Our tabs
	 *
	 * @since  2.3.0
	 * @access public
	 * @var    array
	 */
	public $tabs = array();

	/**
	 * Email fields info.
	 *
	 * @since  2.0.0
	 * @access protected
	 * @var    string
	 */
	protected $email_fields_info = '';

	/**
	 * Aelia EU VAT Assistant status.
	 *
	 * @since  2.0.0
	 * @access protected
	 * @var    string
	 */
	protected $aelia_eu_vat_assistant = '';

	/**
	 * WooCommerce EU VAT Field status.
	 *
	 * @since  2.0.0
	 * @access protected
	 * @var    string
	 */
	protected $woocommerce_eu_vat_field = '';

	/**
	 * InvoiceXpress zero tax name.
	 *
	 * @since  2.0.0
	 * @access protected
	 * @var    string
	 */
	protected $ix_zero_tax_name = 'Isento';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.0.4 Add plugin instance parameter.
	 * @since 2.0.0
	 * @param Plugin $plugin This plugin's instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->email_fields_info = __( 'You can use the <code>{site_title}</code>, <code>{order_date}</code>, <code>{order_number}</code>, and <code>{customer_name}</code> placeholders', 'woo-billing-with-invoicexpress' );
	}

	/**
	 * Get email fields info.
	 *
	 * @since  2.0.0
	 * @return string
	 */
	public function get_email_fields_info() {
		return $this->email_fields_info;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_init', array( $this, 'settings_init' ) );
	}

	/**
	 * Initialize sections and fields.
	 *
	 * @since  2.0.0
	 * @return void
	 */
	public function settings_init() {

		$this->tabs = $this->get_tabs();

		foreach ( $this->tabs as $tab_key => $tab ) {

			if ( empty( $tab['sections'] ) ) {
				continue;
			}

			if ( ! is_array( $tab['sections'] ) ) {
				continue;
			}

			foreach ( $tab['sections'] as $section_key => $section ) {

				if ( empty( $section['fields'] ) ) {
					continue;
				}

				if ( ! is_array( $section['fields'] ) ) {
					continue;
				}

				foreach ( $section['fields'] as $option_name => $field ) {

					$register = true;

					// Required field/value?
					if ( ! empty( $field['required_field'] ) && ! empty( $field['required_value'] ) ) {
						if ( get_option( $field['required_field'] ) !== $field['required_value'] ) {
							$register = false;
						}
					}

					if ( ! $register ) {
						continue;
					}

					$tr_classes = array();

					// Has parent?
					if ( ! empty( $field['parent_field'] ) && ! empty( $field['parent_value'] ) ) {
						$tr_classes[] = 'has-parent-field';
					}

					add_settings_field(
						$option_name,
						$field['title'],
						array( $this, 'output_field' ),
						'invoicexpress_woocommerce',
						$section_key,
						array(
							'class'   => implode( ' ', $tr_classes ),
							'tab'     => $tab_key,
							'section' => $section_key,
							'field'   => $option_name,
						)
					);

					register_setting( $tab_key, $option_name );
				}
			}
		}
	}

	/**
	 * Output tab contents.
	 *
	 * @since  2.0.0
	 * @param  string $tab Tab identifier.
	 * @return void
	 */
	public function output_tab( $tab ) {

		if ( empty( $this->tabs ) ) {
			return;
		}

		$tab = $this->tabs[ $tab ];
		if ( ! isset( $tab['sections'] ) ) {
			return;
		}

		if ( ! is_array( $tab['sections'] ) ) {
			return;
		}

		if ( count( $tab['sections'] ) <= 0 ) {
			return;
		}

		foreach ( $tab['sections'] as $section_key => $section ) {
			?>
			<div id="section-<?php echo esc_attr( trim( $section_key ) ); ?>">
				<h2><?php echo esc_html( $section['title'] ); ?></h2>
				<?php
				if ( ! empty( $section['description'] ) ) {
					printf(
						'<p>%s</p>',
						wp_kses_post( trim( $section['description'] ) )
					);
				}
				$class                  = array( 'form-table' );
				$custom_attributes      = array();
				$custom_attributes_html = '';
				/*if ( isset( $section['parent_field'] ) && isset( $section['parent_value'] ) ) {
					$class[] = 'section-has-parent-field';
					if ( is_array( $section['parent_field'] ) ) {
						foreach ( $section['parent_field'] as $field ) {
							$custom_attributes[$field] = $section['parent_value'];
						}
					} elseif ( is_string( $section['parent_field'] ) ) {
						$custom_attributes[$section['parent_field']] = $section['parent_value'];
					}
				}
				if ( count( $custom_attributes ) > 0 ) {
					foreach ( $custom_attributes as $key => $value ) {
						$custom_attributes_html.='data-'.$key.'-value="'.trim( esc_attr( $value ) ).'"';
					}
				}*/
				?>
				<table class="<?php echo esc_attr( implode( ' ', $class ) ); ?>" <?php echo $custom_attributes_html; ?>>
					<?php do_settings_fields( 'invoicexpress_woocommerce', $section_key ); ?>
				</table>
			</div>
			<?php
		}
	}


	/**
	 * Output tab top.
	 *
	 * @since  2.0.0
	 * @param  string $tab Tab identifier.
	 * @return void
	 */
	public function output_tab_top( $tab ) {

		if ( empty( $this->tabs ) ) {
			return;
		}

		$tab = $this->tabs[ $tab ];
		if ( ! isset( $tab['top'] ) ) {
			return;
		}

		echo wp_kses_post( $tab['top'] );
	}

	/**
	 * Output tab bottom.
	 *
	 * @since  2.0.0
	 * @param  string $tab Tab identifier.
	 * @return void
	 */
	public function output_tab_bottom( $tab ) {

		if ( empty( $this->tabs ) ) {
			return;
		}

		$tab = $this->tabs[ $tab ];
		if ( ! isset( $tab['bottom'] ) ) {
			return;
		}

		echo wp_kses_post( $tab['bottom'] );
	}

	/**
	 * Outputs a settings field.
	 *
	 * @since  2.0.0
	 * @param  array $args Field args.
	 * @return void
	 */
	public function output_field( $args ) {

		if ( empty( $this->tabs ) ) {
			return;
		}

		$field  = $this->tabs[ $args['tab'] ]['sections'][ $args['section'] ]['fields'][ $args['field'] ];
		$value  = get_option( $args['field'] );
		$suffix = isset( $field['suffix'] ) ? trim( $field['suffix'] ) : '';
		$style  = isset( $field['style'] ) ? trim( $field['style'] ) : '';
		$class  = array( 'ix_form_field' );
		if ( isset( $field['class'] ) && is_array( $field['class'] ) ) {
			$class = array_merge( $class, $field['class'] );
		}

		$placeholder = isset( $field['placeholder'] ) ? trim( $field['placeholder'] ) : '';

		// Placeholder as default? Usefull for email fields.
		if ( empty( $value ) && isset( $field['placeholder'] ) && isset( $field['placeholder_as_default'] ) && $field['placeholder_as_default'] === true ) {
			$value = trim( $field['placeholder'] );
		}

		//Default for checkboxes
		if ( empty( $value ) && $value === false && $field['type'] == 'checkbox' && isset( $field['default'] ) && $field['default'] == '1' ) {
			$value = '1';
		}

		$description = '';
		if ( isset( $field['description'] ) ) {
			$description = sprintf(
				'<p class="description">%s</p>',
				trim( $field['description'] )
			);
		}

		if ( $args['field'] === 'hd_wc_ie_plus_exemption_name' && $this->ix_zero_tax_name !== '' ) {
			$description = str_replace( '</p>', ' (' . sprintf(
				/* translators: %s: probable zero tax name */
				__( 'probably "%s"', 'woo-billing-with-invoicexpress' ),
				$this->ix_zero_tax_name
			) . ')</p>', $description );
			$field['default'] = $this->ix_zero_tax_name;
		}

		// Custom attribute handling.
		$custom_attributes = array();
		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
			foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {

				if ( $attribute === 'readonly' && ! $attribute_value ) {
					continue;
				}

				$custom_attributes[] = sprintf(
					'%s="%s"',
					esc_attr( $attribute ),
					esc_attr( $attribute_value )
				);
			}
		}

		// Parent fields.
		if ( isset( $field['parent_field'] ) && trim( $field['parent_field'] ) !== '' && isset( $field['parent_value'] ) && trim( $field['parent_value'] ) !== '' ) {
			printf(
				'<span class="parent_field_data" data-parent-field="%1$s" data-parent-value="%2$s"></span>',
				esc_attr( trim( $field['parent_field'] ) ),
				esc_attr( trim( $field['parent_value'] ) )
			);
		}

		//WPML
		$wpml = false;
		if ( $this->plugin->wpml_active && isset( $field['wpml'] ) && $field['wpml'] ) {
			$wpml = true;
		}

		switch ( $field['type'] ) {
			case 'text':
			case 'password':
			case 'datetime':
			case 'datetime-local':
			case 'date':
			case 'month':
			case 'time':
			case 'week':
			case 'number':
			case 'email':
			case 'url':
			case 'tel':
				$this->output_text_field( [
					'type'              => $field['type'],
					'field'             => $args['field'],
					'style'             => $style,
					'value'             => $value,
					'class'             => $class,
					'placeholder'       => $placeholder,
					'custom_attributes' => $custom_attributes,
					'suffix'            => $suffix,
					'description'       => $description,
					'wpml'				=> $wpml,
				] );
				break;

			case 'textarea':
				$this->output_textarea_field( [
					'field'             => $args['field'],
					'style'             => $style,
					'value'             => $value,
					'class'             => $class,
					'placeholder'       => $placeholder,
					'custom_attributes' => $custom_attributes,
					'description'       => $description,
					'wpml'				=> $wpml,
				] );
				break;

			case 'checkbox':
				$this->output_checkbox_field( [
					'field'             => $args['field'],
					'value'             => $value,
					'class'             => $class,
					'custom_attributes' => $custom_attributes,
					'suffix'            => $suffix,
					'description'       => $description,
					'wpml'				=> $wpml,
				] );
				break;

			case 'select':
			case 'multiselect':
			case 'select_ix_sequence':
			case 'select_ix_tax':
			case 'select_order_status':
			case 'select_ix_countries':
				$options = array();

				try {
					$options = $this->get_select_options( $field, $args );
				} catch ( \Exception $ex ) {
					$description .= sprintf(
						'<p class="description ix_error">%s</p>',
						$ex->getMessage()
					);
				}
				if ( $args['field'] != 'hd_wc_ie_plus_automatic_invoice_type' ) {
					$class = array_merge( $class, array( 'wc-enhanced-select' ) );
				}
				$this->output_select_field( [
					'type'              => $field['type'],
					'field'             => $args['field'],
					'value'             => $value,
					'options'           => $options,
					'style'             => $style,
					'class'             => $class,
					'custom_attributes' => $custom_attributes,
					'suffix'            => $suffix,
					'description'       => $description,
					'wpml'				=> $wpml,
				] );
				break;

			case 'pro_link':
				echo sprintf(
					'<a href="https://shop.webdados.com/product/invoicing-with-invoicexpress-for-woocommerce-pro/" target="_blank">%s</a>',
					__( 'Available on the Pro version', 'woo-billing-with-invoicexpress' )
				).'<br/>'.$description;
				break;

			default:
				echo '* Field type ERROR *';
				break;
		}
	}

	/**
	 * Outputs the WPML String Translation button
	 *
	 * @since  2.0.7
	 * @access private
	 * @param  string $field The field ID
	 * @param  bool   $wpml  Has WPML Translation or not
	 * @return void
	 */
	private function output_wpml_button( $field, $wpml ) {
		global $sitepress;
		if ( ! $wpml ) {
			return '';
		}
		return sprintf(
			'&nbsp;<img src="%1$s" style="margin-top: 0.5em;"/> &nbsp;<a href="admin.php?page=wpml-string-translation/menu/string-translation.php&amp;context=admin_texts_%2$s" class="button button-small button-wpml" target="_blank" title="%3$s">%4$s</a>',
			$sitepress->get_flag_url( $sitepress->get_default_language() ),
			$field,
			__( 'Translate with WPML String Translation', 'woo-billing-with-invoicexpress' ),
			__( 'Translate', 'woo-billing-with-invoicexpress' )
		);
	}

	/**
	 * Outputs an input field.
	 *
	 * @since  2.0.0
	 * @access private
	 * @param  array $data Field data.
	 * @return void
	 */
	private function output_text_field( $data ) {
		printf( // WPCS: XSS ok.
			'<input
				type="%1$s"
				id="%2$s"
				name="%2$s"
				style="%3$s"
				value="%4$s"
				class="%5$s"
				placeholder="%6$s"
				%7$s
			>
			%8$s
			%9$s
			%10$s',
			esc_attr( $data['type'] ),
			esc_attr( $data['field'] ),
			esc_attr( $data['style'] ),
			esc_attr( $data['value'] ),
			esc_attr( implode( ' ', $data['class'] ) ),
			esc_attr( $data['placeholder'] ),
			implode( ' ', $data['custom_attributes'] ),
			$this->output_wpml_button( $data['field'], $data['wpml'] ),
			esc_html( $data['suffix'] ),
			$data['description']
		);
	}

	/**
	 * Outputs a textarea field.
	 *
	 * @since  2.0.0
	 * @access private
	 * @param  array $data Field data.
	 * @return void
	 */
	private function output_textarea_field( $data ) {
		printf( // WPCS: XSS ok.
			'<textarea
				id="%1$s"
				name="%1$s"
				style="%2$s"
				class="%3$s"
				placeholder="%4$s"
				%5$s
			>%6$s</textarea>
			%7$s
			%8$s',
			esc_attr( $data['field'] ),
			esc_attr( $data['style'] ),
			esc_attr( implode( ' ', $data['class'] ) ),
			esc_attr( $data['placeholder'] ),
			implode( ' ', $data['custom_attributes'] ),
			esc_textarea( trim( $data['value'] ) ),
			$this->output_wpml_button( $data['field'], $data['wpml'] ),
			$data['description']
		);
	}

	/**
	 * Outputs a checkbox field.
	 *
	 * @since  2.0.0
	 * @access private
	 * @param  array $data Field data.
	 * @return void
	 */
	private function output_checkbox_field( $data ) {
		printf( // WPCS: XSS ok.
			'<input
				type="checkbox"
				id="%1$s"
				name="%1$s"
				value="1"
				class="%2$s"
				%3$s
				%4$s
			>
			<label for="%1$s">%5$s</label>
			%6$s
			%7$s',
			esc_attr( $data['field'] ),
			esc_attr( implode( ' ', $data['class'] ) ),
			checked( $data['value'], '1', false ),
			implode( ' ', $data['custom_attributes'] ),
			esc_html( $data['suffix'] ),
			$this->output_wpml_button( $data['field'], $data['wpml'] ),
			$data['description']
		);
	}

	/**
	 * Outputs a select field.
	 *
	 * @since  2.0.0
	 * @access private
	 * @param  array $data Field data.
	 * @return void
	 */
	private function output_select_field( $data ) {

		$options = '';
		$selected = $data['value'];

		if ( ! empty( $data['options'] ) ) {
			foreach ( $data['options'] as $key => $text ) {

				if ( is_array( $text ) ) {
					
					$options .= sprintf(
						'<optgroup label="%s">',
						esc_attr( $text['label'] )
					);
					foreach ( $text['options'] as $key2 => $text2 ) {

						$current  = (string) $key2;
						if ( is_array( $data['value'] ) ) {
							$selected = in_array( (string) $key2, $data['value'], true );
							$current  = true;
						}
						$options .= sprintf(
							'<option value="%1$s"%2$s>
								%3$s
							</option>',
							esc_attr( $key2 ),
							selected( $selected, $current, false ),
							esc_html( $text2 )
						);

					}
					$options .= '</optgroup>';

				} else {

					$current  = (string) $key;
					if ( is_array( $data['value'] ) ) {
						$selected = in_array( (string) $key, $data['value'], true );
						$current  = true;
					}
					$options .= sprintf(
						'<option value="%1$s"%2$s>
							%3$s
						</option>',
						esc_attr( $key ),
						selected( $selected, $current, false ),
						esc_html( $text )
					);
				}

			}
		}

		printf( // WPCS: XSS ok.
			'<select id="%1$s" name="%1$s%2$s" style="%3$s" class="%4$s" %5$s %6$s>
				%7$s
			</select>
			%8$s
			%9$s
			%10$s',
			esc_attr( $data['field'] ),
			'multiselect' === $data['type'] ? '[]' : '',
			esc_attr( $data['style'] ),
			esc_attr( implode( ' ', $data['class'] ) ),
			esc_attr( implode( ' ', $data['custom_attributes'] ) ),
			'multiselect' === $data['type'] ? 'multiple="multiple" size="10"' : '',
			$options,
			$this->output_wpml_button( $data['field'], $data['wpml'] ),
			esc_html( $data['suffix'] ),
			$data['description']
		);
	}

	/**
	 * Get the options based on the select type.
	 *
	 * @since  2.0.0
	 * @access private
	 * @return array
	 */
	private function get_select_options( $field, $args ) {
		switch ( $field['type'] ) {
			case 'select_ix_sequence':
				return $this->get_select_ix_sequence();

			case 'select_ix_tax':
				return $this->get_select_ix_tax();

			case 'select_order_status':
				return $this->get_select_order_status( $args );

			case 'select_ix_countries':
				return $this->get_select_ix_countries();

			default:
				return $field['options'];
		}
	}

	/**
	 * Get InvoiceXpress sequences.
	 *
	 * @since  2.0.0
	 * @access private
	 * @return array
	 */
	private function get_select_ix_sequence() {

		$options = array(
			'' => esc_html__( 'None (InvoiceXpress default)', 'woo-billing-with-invoicexpress' ),
		);

		$cache = array();

		$params = array(
			'request' => 'sequences.json',
			'args'    => array(),
		);
		$json_request = new JsonRequest( $params );
		$return = $json_request->getRequest();
		if ( $return['success'] ) {
			$cache_id = '';
			foreach ( $return['object']->sequences as $sequence ) {
				$options[ (string) $sequence->id ] = $sequence->serie;
				foreach ( $sequence as $key => $temp2 ) {
					$cache[ (string) $sequence->id ][ $key ] = (string) $temp2;
				}
			}
			update_option( 'hd_wc_ie_plus_sequences_cache', $cache );
			return $options;
		} else {
			//Return a single option with the error
			return array(
				'' => $return['error_message']
			);
		}

	}

	/**
	 * Get InvoiceXpress taxes.
	 *
	 * @since  2.0.0
	 * @access private
	 * @return array
	 */
	private function get_select_ix_tax() {

		/* First let's set the default tax at InvoiceXpress */
		$default_tax = get_option( 'hd_wc_ie_plus_default_tax' );
		if ( ! empty( $default_tax ) ) {
			$default_tax = explode( '|', $default_tax );
			if ( count( $default_tax ) == 4 && ! empty( $default_tax[0] ) ) {
				$tax_id = $default_tax[0];
				$params = array(
					'request' => 'taxes/'.$tax_id.'.json',
					'args'    => array(
						'tax' => array(
							//'name'        => trim( $default_tax[1] ),
							//'value'       => floatval( $default_tax[2] ),
							//'region'      => trim( $default_tax[3] ),
							'default_tax' => '1',
						),
					),
				);
				$json_request = new JsonRequest( $params );
				$return = $json_request->putRequest();
				if ( $return['success'] ) {
					//OK
				} else {
					// We should be dealing with errors
				}
			}
		}

		/* Now we get the options */
		$options = array(
			'' => esc_html__( 'None', 'woo-billing-with-invoicexpress' ),
		);

		$params = array(
			'request' => 'taxes.json',
			'args'    => array(),
		);
		$json_request = new JsonRequest( $params );
		$return = $json_request->getRequest();
		if ( $return['success'] ) {
			foreach ( $return['object']->taxes as $tax ) {
				$options[ sprintf(
					'%s|%s|%s|%s',
					$tax->id,
					$tax->name,
					floatval( $tax->value ),
					$tax->region
				) ] = sprintf(
					'%s (%s%%)',
					$tax->name,
					$tax->value
				);
				if ( floatval( $tax->value ) == 0 ) {
					$this->ix_zero_tax_name = $tax->name;
				}
			}
			return $options;
		} else {
			//Return a single option with the error
			return array(
				'' => $return['error_message']
			);
		}
	}

	/**
	 * Get WooCommerce order status.
	 *
	 * @since  2.0.0
	 * @access private
	 * @return array
	 */
	private function get_select_order_status( $args ) {

		$all_statuses           = wc_get_order_statuses();
		$options_statuses       = array_keys( $all_statuses );
		$not_recommended_status = array();
		$options                = array();

		if (
			$args['field'] === 'hd_wc_ie_plus_automatic_invoice_state'
			||
			$args['field'] === 'hd_wc_ie_plus_automatic_guide_state'
			||
			$args['field'] === 'hd_wc_ie_plus_automatic_receipt_state'
		) {
			$options_statuses       = $this->plugin->get_possible_status();
			$not_recommended_status = $this->plugin->get_not_recommended_status();
			if ( $args['field'] === 'hd_wc_ie_plus_automatic_receipt_state' ) {
				$options[''] = __( 'Immediately after the invoice', 'woo-billing-with-invoicexpress' );
			}
		}

		foreach ( $options_statuses as $status_key ) {
			$options[ $status_key ] = $all_statuses[ $status_key ];
			if ( in_array( $status_key, $not_recommended_status, true ) ) {
				$options[ $status_key ] .= sprintf(
					' (%s)',
					esc_html__( 'Not recommended', 'woo-billing-with-invoicexpress' )
				);
			}
		}

		return $options;
	}

	/**
	 * Get InvoiceXpress countries.
	 *
	 * @since  2.0.0
	 * @access private
	 * @return array
	 */
	private function get_select_ix_countries() {

		$options = array(
			'' => esc_html__( 'None', 'woo-billing-with-invoicexpress' ),
		);

		$countries = new CountryTranslation();

		$countries_list = $countries->get_countries();
		foreach ( $countries_list as $key => $country ) {
			$country             = trim( $countries->translate( $country ) );
			$options[ $country ] = $country;
		}

		ksort( $options );

		return array_unique( $options );
	}
}
