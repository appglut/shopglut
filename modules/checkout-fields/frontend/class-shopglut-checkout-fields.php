<?php
/**
 * Checkout Field Editor front end.
 *
 * Classic checkout: rows in the Billing / Shipping / Order lists change core fields (label, placeholder,
 * width, required, order, hide) or add new fields. New fields are saved on the order (meta key = the row's
 * name) and shown in the order admin, the customer's order page and emails.
 *
 * Block checkout: new fields are registered as WooCommerce "additional checkout fields" (billing → contact
 * step, shipping → address, order → order step); WooCommerce saves and shows them itself.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Checkout_Fields class.
 */
class ShopGlut_Checkout_Fields {

	/**
	 * Lists and their WooCommerce field group.
	 *
	 * @var array
	 */
	const LISTS = array(
		'billing_fields'  => 'billing',
		'shipping_fields' => 'shipping',
		'order_fields'    => 'order',
	);

	/**
	 * Module options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$values        = appglut_get_option( ShopGlut_Panels::module_option( 'checkout-fields' ) );
		$this->options = is_array( $values ) ? $values : array();

		add_filter( 'woocommerce_checkout_fields', array( $this, 'classic_fields' ), 1000 );
		add_filter( 'woocommerce_country_locale_field_selectors', array( $this, 'locale_selectors' ), 1000 );
		add_action( 'woocommerce_checkout_create_order', array( $this, 'save' ), 20, 2 );

		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'admin_billing' ) );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'admin_shipping' ) );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'admin_order' ) );
		add_action( 'woocommerce_order_details_after_customer_details', array( $this, 'customer_details' ) );
		add_action( 'shopglut_order_complete_after_layout', array( $this, 'thankyou_layout_details' ) );
		add_filter( 'woocommerce_email_order_meta_fields', array( $this, 'email_fields' ), 10, 3 );

		if ( $this->opt( 'block_checkout', true ) ) {
			$this->register_block_fields();
		}
	}

	/**
	 * A general option.
	 *
	 * @param string $key     Key.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	private function opt( $key, $default ) {
		return array_key_exists( $key, $this->options ) ? $this->options[ $key ] : $default;
	}

	/**
	 * Rows of a list, cleaned up.
	 *
	 * @param string $list billing_fields | shipping_fields | order_fields.
	 * @return array
	 */
	private function rows( $list ) {
		$rows = isset( $this->options[ $list ] ) && is_array( $this->options[ $list ] ) ? $this->options[ $list ] : array();
		$out  = array();
		foreach ( array_values( $rows ) as $i => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$name = isset( $row['name'] ) ? strtolower( preg_replace( '/[^A-Za-z0-9_]/', '', (string) $row['name'] ) ) : '';
			if ( '' === $name ) {
				continue;
			}
			$out[] = array(
				'name'          => $name,
				'label'         => isset( $row['label'] ) ? (string) $row['label'] : '',
				'type'          => isset( $row['type'] ) ? sanitize_key( $row['type'] ) : 'text',
				'width'         => isset( $row['width'] ) && in_array( $row['width'], array( 'form-row-wide', 'form-row-first', 'form-row-last' ), true ) ? $row['width'] : 'form-row-wide',
				'placeholder'   => isset( $row['placeholder'] ) ? (string) $row['placeholder'] : '',
				'default'       => isset( $row['default'] ) ? (string) $row['default'] : '',
				'options'       => self::parse_options( isset( $row['options'] ) ? (string) $row['options'] : '' ),
				'required'      => ! empty( $row['required'] ),
				'enabled'       => ! isset( $row['enabled'] ) || ! empty( $row['enabled'] ),
				'show_in_order' => ! isset( $row['show_in_order'] ) || ! empty( $row['show_in_order'] ),
				'class'         => isset( $row['class'] ) ? sanitize_html_class( (string) $row['class'] ) : '',
				'priority'      => ( $i + 1 ) * 10,
			);
		}
		return $out;
	}

	/**
	 * "value | Label" lines to an options array.
	 *
	 * @param string $text Lines.
	 * @return array value => label
	 */
	private static function parse_options( $text ) {
		$options = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$parts                          = array_map( 'trim', explode( '|', $line, 2 ) );
			$options[ sanitize_text_field( $parts[0] ) ] = isset( $parts[1] ) && '' !== $parts[1] ? $parts[1] : $parts[0];
		}
		return $options;
	}

	/**
	 * Core field names WooCommerce knows for a group (so rows with these names edit instead of add).
	 *
	 * @param array  $fields Checkout fields.
	 * @param string $group  Group.
	 * @return array
	 */
	private static function core_names( $fields, $group ) {
		return isset( $fields[ $group ] ) ? array_keys( $fields[ $group ] ) : array();
	}

	/**
	 * Classic checkout fields.
	 *
	 * @param array $fields Checkout fields by group.
	 * @return array
	 */
	public function classic_fields( $fields ) {
		foreach ( self::LISTS as $list => $group ) {
			if ( ! isset( $fields[ $group ] ) ) {
				$fields[ $group ] = array();
			}
			$core = self::core_names( $fields, $group );
			foreach ( $this->rows( $list ) as $row ) {
				$name = $row['name'];
				if ( in_array( $name, $core, true ) ) {
					if ( ! $row['enabled'] ) {
						unset( $fields[ $group ][ $name ] );
						continue;
					}
					$field = $fields[ $group ][ $name ];
					if ( $this->opt( 'enable_label_override', true ) && '' !== $row['label'] ) {
						$field['label'] = $row['label'];
					}
					if ( $this->opt( 'enable_placeholder_override', true ) && '' !== $row['placeholder'] ) {
						$field['placeholder'] = $row['placeholder'];
					}
					if ( $this->opt( 'enable_class_override', true ) ) {
						$classes        = array_diff( isset( $field['class'] ) ? (array) $field['class'] : array(), array( 'form-row-wide', 'form-row-first', 'form-row-last' ) );
						$field['class'] = array_values( array_filter( array_merge( array( $row['width'] ), $classes, array( $row['class'] ) ) ) );
					}
					if ( $this->opt( 'enable_required_override', true ) ) {
						$field['required'] = $row['required'];
					}
					if ( $this->opt( 'enable_priority_override', true ) ) {
						$field['priority'] = $row['priority'];
					}
					$fields[ $group ][ $name ] = $field;
					continue;
				}
				if ( ! $row['enabled'] ) {
					continue;
				}
				$fields[ $group ][ $name ] = $this->classic_field( $row );
			}
		}
		return $fields;
	}

	/**
	 * WooCommerce's checkout script re-applies per-country labels, required flags, widths and order to the
	 * address fields in the browser. Fields the store has customised are taken out of that list, so the
	 * customised values stay.
	 *
	 * @param array $selectors key => "#billing_x_field, #shipping_x_field".
	 * @return array
	 */
	public function locale_selectors( $selectors ) {
		foreach ( array( 'billing_fields' => 'billing', 'shipping_fields' => 'shipping' ) as $list => $group ) {
			foreach ( $this->rows( $list ) as $row ) {
				$key = substr( $row['name'], strlen( $group ) + 1 );
				if ( 0 !== strpos( $row['name'], $group . '_' ) || ! isset( $selectors[ $key ] ) ) {
					continue;
				}
				$parts              = array_map( 'trim', explode( ',', $selectors[ $key ] ) );
				$parts              = array_diff( $parts, array( '#' . $row['name'] . '_field' ) );
				$selectors[ $key ] = implode( ', ', $parts );
				if ( '' === $selectors[ $key ] ) {
					unset( $selectors[ $key ] );
				}
			}
		}
		return $selectors;
	}

	/**
	 * A new field in woocommerce_form_field() format.
	 *
	 * @param array $row Row.
	 * @return array
	 */
	private function classic_field( $row ) {
		$types = array( 'text', 'textarea', 'email', 'tel', 'number', 'select', 'radio', 'checkbox', 'date' );
		$field = array(
			'type'        => in_array( $row['type'], $types, true ) ? $row['type'] : 'text',
			'label'       => '' !== $row['label'] ? $row['label'] : ucwords( str_replace( '_', ' ', $row['name'] ) ),
			'placeholder' => $row['placeholder'],
			'required'    => $row['required'],
			'class'       => array_values( array_filter( array( $row['width'], $row['class'], 'shopglut-checkout-field' ) ) ),
			'priority'    => $row['priority'],
			'default'     => $row['default'],
		);
		if ( in_array( $field['type'], array( 'select', 'radio' ), true ) ) {
			$field['options'] = $row['options'];
			if ( 'select' === $field['type'] && ! $row['required'] ) {
				$field['options'] = array( '' => __( 'Select an option…', 'shopglut' ) ) + $field['options'];
			}
		}
		if ( 'email' === $field['type'] ) {
			$field['validate'] = array( 'email' );
		}
		if ( 'tel' === $field['type'] ) {
			$field['validate'] = array( 'phone' );
		}
		return $field;
	}

	/**
	 * New (non-core) rows of every list.
	 *
	 * @return array List of array( group, row ).
	 */
	private function custom_rows() {
		static $core = null;
		if ( null === $core ) {
			$core = array(
				'billing'  => array( 'billing_first_name', 'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_phone', 'billing_email' ),
				'shipping' => array( 'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_country', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode', 'shipping_phone' ),
				'order'    => array( 'order_comments' ),
			);
		}
		$out = array();
		foreach ( self::LISTS as $list => $group ) {
			foreach ( $this->rows( $list ) as $row ) {
				if ( $row['enabled'] && ! in_array( $row['name'], $core[ $group ], true ) ) {
					$out[] = array( $group, $row );
				}
			}
		}
		return $out;
	}

	/**
	 * Save new fields on the order (classic checkout).
	 *
	 * @param WC_Order $order Order.
	 * @param array    $data  Posted checkout data.
	 * @return void
	 */
	public function save( $order, $data ) {
		foreach ( $this->custom_rows() as $item ) {
			$row  = $item[1];
			$name = $row['name'];
			if ( ! array_key_exists( $name, $data ) ) {
				continue;
			}
			$value = $data[ $name ];
			if ( 'checkbox' === $row['type'] ) {
				$value = $value ? '1' : '';
			} elseif ( 'textarea' === $row['type'] ) {
				$value = sanitize_textarea_field( (string) $value );
			} else {
				$value = sanitize_text_field( (string) $value );
			}
			$order->update_meta_data( $name, $value );
		}
	}

	/**
	 * Saved values of new fields for display.
	 *
	 * @param WC_Order    $order Order.
	 * @param string|null $group Only this group.
	 * @return array label => value
	 */
	private function values( $order, $group = null ) {
		$out = array();
		foreach ( $this->custom_rows() as $item ) {
			list( $g, $row ) = $item;
			if ( ( $group && $g !== $group ) || ! $row['show_in_order'] ) {
				continue;
			}
			$value = $order->get_meta( $row['name'] );
			if ( '' === $value || null === $value ) {
				$value = $order->get_meta( '_' . $row['name'] );
			}
			if ( '' === $value || null === $value ) {
				continue;
			}
			if ( 'checkbox' === $row['type'] ) {
				$value = $value ? __( 'Yes', 'shopglut' ) : __( 'No', 'shopglut' );
			} elseif ( isset( $row['options'][ $value ] ) ) {
				$value = $row['options'][ $value ];
			}
			$label         = '' !== $row['label'] ? $row['label'] : ucwords( str_replace( '_', ' ', $row['name'] ) );
			$out[ $label ] = (string) $value;
		}
		return $out;
	}

	/**
	 * Print label: value lines.
	 *
	 * @param array $values Values.
	 * @return void
	 */
	private static function print_values( $values ) {
		foreach ( $values as $label => $value ) {
			echo '<p class="shopglut-checkout-field-value"><strong>' . esc_html( $label ) . ':</strong> ' . nl2br( esc_html( $value ) ) . '</p>';
		}
	}

	/**
	 * Admin order screen: billing fields.
	 *
	 * @param WC_Order $order Order.
	 * @return void
	 */
	public function admin_billing( $order ) {
		self::print_values( $this->values( $order, 'billing' ) );
	}

	/**
	 * Admin order screen: shipping fields.
	 *
	 * @param WC_Order $order Order.
	 * @return void
	 */
	public function admin_shipping( $order ) {
		self::print_values( $this->values( $order, 'shipping' ) );
	}

	/**
	 * Admin order screen: order fields.
	 *
	 * @param WC_Order $order Order.
	 * @return void
	 */
	public function admin_order( $order ) {
		$values = $this->values( $order, 'order' );
		if ( $values ) {
			echo '<div class="form-field form-field-wide">';
			self::print_values( $values );
			echo '</div>';
		}
	}

	/**
	 * Customer's order page and thank you page.
	 *
	 * @param WC_Order $order Order.
	 * @return void
	 */
	public function customer_details( $order ) {
		$values = $this->values( $order );
		if ( ! $values ) {
			return;
		}
		echo '<section class="woocommerce-additional-fields shopglut-checkout-fields"><h2>' . esc_html__( 'Additional information', 'shopglut' ) . '</h2>';
		self::print_values( $values );
		echo '</section>';
	}

	/**
	 * Inside a ShopGlut Thank You layout.
	 *
	 * @param WC_Order $order Order.
	 * @return void
	 */
	public function thankyou_layout_details( $order ) {
		$values = $this->values( $order );
		if ( ! $values ) {
			return;
		}
		echo '<div class="shopglut-checkout-fields" style="max-width:800px;margin:24px auto;padding:20px 24px;border:1px solid #e5e7eb;border-radius:8px;background:#fff">';
		echo '<h3 style="margin:0 0 12px;font-size:18px">' . esc_html__( 'Additional information', 'shopglut' ) . '</h3>';
		self::print_values( $values );
		echo '</div>';
	}

	/**
	 * Emails.
	 *
	 * @param array    $fields        Fields.
	 * @param bool     $sent_to_admin Sent to admin.
	 * @param WC_Order $order         Order.
	 * @return array
	 */
	public function email_fields( $fields, $sent_to_admin, $order ) {
		if ( ! $order instanceof WC_Order ) {
			return $fields;
		}
		foreach ( $this->values( $order ) as $label => $value ) {
			$fields[ 'shopglut_' . sanitize_key( $label ) ] = array(
				'label' => $label,
				'value' => $value,
			);
		}
		return $fields;
	}

	/**
	 * Block checkout: register new fields as additional checkout fields.
	 *
	 * @return void
	 */
	private function register_block_fields() {
		if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
			return;
		}
		$locations = array(
			'billing'  => 'contact',
			'shipping' => 'address',
			'order'    => 'order',
		);
		foreach ( $this->custom_rows() as $item ) {
			list( $group, $row ) = $item;
			$label = '' !== $row['label'] ? $row['label'] : ucwords( str_replace( '_', ' ', $row['name'] ) );
			$args  = array(
				'id'       => 'shopglut/' . str_replace( '_', '-', $row['name'] ),
				'label'    => $label,
				'location' => $locations[ $group ],
				'required' => $row['required'],
				'index'    => $row['priority'],
			);
			if ( in_array( $row['type'], array( 'select', 'radio' ), true ) && $row['options'] ) {
				$args['type']    = 'select';
				$args['options'] = array();
				foreach ( $row['options'] as $value => $text ) {
					$args['options'][] = array(
						'value' => (string) $value,
						'label' => (string) $text,
					);
				}
			} elseif ( 'checkbox' === $row['type'] ) {
				$args['type'] = 'checkbox';
			} else {
				$args['type'] = 'text';
				if ( '' !== $row['placeholder'] ) {
					$args['placeholder'] = $row['placeholder'];
				}
			}
			try {
				woocommerce_register_additional_checkout_field( $args );
			} catch ( Exception $e ) {
				// A field with the same ID is already registered: skip it.
				continue;
			}
		}
	}
}
