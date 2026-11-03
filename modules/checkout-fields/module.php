<?php
/**
 * Checkout Field Editor: edit, reorder, hide and add checkout fields (classic and block checkout).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Checkout_Fields class.
 */
class ShopGlut_Module_Checkout_Fields extends ShopGlut_Module {

	/**
	 * Module ID.
	 *
	 * @return string
	 */
	public function id() {
		return 'checkout-fields';
	}

	/**
	 * Module info.
	 *
	 * @return array
	 */
	public function info() {
		return array(
			'title'        => __( 'Checkout Field Editor', 'shopglut' ),
			'description'  => __( 'Edit, reorder, hide and add billing, shipping and order fields on classic and block checkout.', 'shopglut' ),
			'icon'         => 'faListCheck',
			'category'     => 'cart',
			'order'        => 40,
			'features'     => array(
				__( 'Change label, placeholder, width, required and order of core fields', 'shopglut' ),
				__( 'Add text, textarea, select, radio, checkbox, date and number fields', 'shopglut' ),
				__( 'Classic and block checkout support', 'shopglut' ),
				__( 'Custom fields shown in orders and emails', 'shopglut' ),
			),
			'pro_features' => array(
				__( 'Conditional fields (show / hide by other fields, cart contents, role)', 'shopglut' ),
				__( 'File upload, color, time and multi-select fields', 'shopglut' ),
				__( 'Fields that add a fee to the order', 'shopglut' ),
			),
		);
	}

	/**
	 * Fields of a custom checkout field list (billing, shipping, order).
	 *
	 * @return array
	 */
	private function field_editor() {
		return array(
			array(
				'id'    => 'label',
				'type'  => 'text',
				'title' => __( 'Label', 'shopglut' ),
				'col'   => 6,
			),
			array(
				'id'    => 'name',
				'type'  => 'text',
				'title' => __( 'Name (meta key)', 'shopglut' ),
				'desc'  => __( 'Lowercase letters, numbers and underscores. Core fields keep their name, e.g. billing_phone.', 'shopglut' ),
				'col'   => 6,
			),
			array(
				'id'      => 'type',
				'type'    => 'select',
				'title'   => __( 'Type', 'shopglut' ),
				'options' => array(
					'text'     => __( 'Text', 'shopglut' ),
					'textarea' => __( 'Textarea', 'shopglut' ),
					'email'    => __( 'Email', 'shopglut' ),
					'tel'      => __( 'Phone', 'shopglut' ),
					'number'   => __( 'Number', 'shopglut' ),
					'select'   => __( 'Select', 'shopglut' ),
					'radio'    => __( 'Radio', 'shopglut' ),
					'checkbox' => __( 'Checkbox', 'shopglut' ),
					'date'     => __( 'Date', 'shopglut' ),
				),
				'default' => 'text',
				'col'     => 4,
			),
			array(
				'id'      => 'width',
				'type'    => 'button_set',
				'title'   => __( 'Width', 'shopglut' ),
				'options' => array(
					'form-row-wide'  => __( 'Full', 'shopglut' ),
					'form-row-first' => __( 'Half (left)', 'shopglut' ),
					'form-row-last'  => __( 'Half (right)', 'shopglut' ),
				),
				'default' => 'form-row-wide',
				'col'     => 8,
			),
			array(
				'id'    => 'placeholder',
				'type'  => 'text',
				'title' => __( 'Placeholder', 'shopglut' ),
				'col'   => 6,
			),
			array(
				'id'    => 'default',
				'type'  => 'text',
				'title' => __( 'Default value', 'shopglut' ),
				'col'   => 6,
			),
			array(
				'id'         => 'options',
				'type'       => 'textarea',
				'title'      => __( 'Options', 'shopglut' ),
				'desc'       => __( 'One per line: value | Label', 'shopglut' ),
				'dependency' => array( 'type', 'any', 'select,radio' ),
			),
			array(
				'id'      => 'required',
				'type'    => 'switcher',
				'title'   => __( 'Required', 'shopglut' ),
				'default' => false,
				'col'     => 4,
			),
			array(
				'id'      => 'enabled',
				'type'    => 'switcher',
				'title'   => __( 'Show on checkout', 'shopglut' ),
				'default' => true,
				'col'     => 4,
			),
			array(
				'id'      => 'show_in_order',
				'type'    => 'switcher',
				'title'   => __( 'Show in order & emails', 'shopglut' ),
				'default' => true,
				'col'     => 4,
			),
			array(
				'id'    => 'class',
				'type'  => 'text',
				'title' => __( 'Extra CSS class', 'shopglut' ),
			),
		);
	}

	/**
	 * Repeater of fields for one checkout section.
	 *
	 * @param string $id    Field ID.
	 * @param string $title Title.
	 * @return array
	 */
	private function section_fields( $id, $title ) {
		return array(
			array(
				'type'    => 'notice',
				'style'   => 'info',
				'content' => __( 'Drag to reorder. Add a row with the name of a core field (e.g. billing_company) to change or hide it; any other name adds a new field.', 'shopglut' ),
			),
			array(
				'id'           => $id,
				'type'         => 'group',
				'title'        => $title,
				'button_title' => __( 'Add field', 'shopglut' ),
				'title_field'  => 'label',
				'item_title'   => __( 'Field', 'shopglut' ),
				'fields'       => $this->field_editor(),
			),
		);
	}

	/**
	 * Settings panel.
	 *
	 * @return array
	 */
	public function settings_sections() {
		return array(
			array(
				'id'     => 'billing',
				'title'  => __( 'Billing Fields', 'shopglut' ),
				'icon'   => 'dashicons-id',
				'fields' => $this->section_fields( 'billing_fields', __( 'Billing fields', 'shopglut' ) ),
			),
			array(
				'id'     => 'shipping',
				'title'  => __( 'Shipping Fields', 'shopglut' ),
				'icon'   => 'dashicons-car',
				'fields' => $this->section_fields( 'shipping_fields', __( 'Shipping fields', 'shopglut' ) ),
			),
			array(
				'id'     => 'order',
				'title'  => __( 'Order Fields', 'shopglut' ),
				'icon'   => 'dashicons-clipboard',
				'fields' => $this->section_fields( 'order_fields', __( 'Additional order fields', 'shopglut' ) ),
			),
			array(
				'id'     => 'general',
				'title'  => __( 'Options', 'shopglut' ),
				'icon'   => 'dashicons-admin-settings',
				'fields' => array(
					array(
						'id'      => 'enable_label_override',
						'type'    => 'switcher',
						'title'   => __( 'Apply label changes to core fields', 'shopglut' ),
						'default' => true,
					),
					array(
						'id'      => 'enable_placeholder_override',
						'type'    => 'switcher',
						'title'   => __( 'Apply placeholder changes to core fields', 'shopglut' ),
						'default' => true,
					),
					array(
						'id'      => 'enable_class_override',
						'type'    => 'switcher',
						'title'   => __( 'Apply width / class changes to core fields', 'shopglut' ),
						'default' => true,
					),
					array(
						'id'      => 'enable_priority_override',
						'type'    => 'switcher',
						'title'   => __( 'Apply the order of the lists to core fields', 'shopglut' ),
						'default' => true,
					),
					array(
						'id'      => 'enable_required_override',
						'type'    => 'switcher',
						'title'   => __( 'Apply required changes to core fields', 'shopglut' ),
						'default' => true,
					),
					array(
						'id'      => 'block_checkout',
						'type'    => 'switcher',
						'title'   => __( 'Add custom fields to the block checkout', 'shopglut' ),
						'default' => true,
					),
				),
			),
		);
	}

	/**
	 * Front end: classic checkout fields, block checkout fields, order display.
	 *
	 * @return void
	 */
	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		require_once $this->dir . 'frontend/class-shopglut-checkout-fields.php';
		new ShopGlut_Checkout_Fields();
	}
}

return 'ShopGlut_Module_Checkout_Fields';
