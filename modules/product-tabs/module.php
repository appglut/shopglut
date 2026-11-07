<?php
/**
 * Product Tabs: extra tabs on every product page, one own tab per product, and rename / hide the default tabs.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Product_Tabs class.
 */
class ShopGlut_Module_Product_Tabs extends ShopGlut_Module {

	public function id() {
		return 'product-tabs';
	}

	public function info() {
		return array(
			'title'        => __( 'Product Tabs', 'shopglut' ),
			'description'  => __( 'Extra tabs on product pages (shipping, warranty, FAQ), a tab per product, and rename or hide the default tabs.', 'shopglut' ),
			'icon'         => 'faListCheck',
			'category'     => 'product',
			'order'        => 72,
			'features'     => array(
				__( 'Up to 3 tabs on every product', 'shopglut' ),
				__( 'One own tab per product', 'shopglut' ),
				__( 'Rename Description, hide Additional information or Reviews', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default = '' ) {
		return shopglut_get_module_setting( 'product-tabs', $key, $default );
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_filter( 'woocommerce_product_tabs', array( $this, 'tabs' ), 98 );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'product_fields' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_product' ) );
	}

	/**
	 * Change the tab list.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public function tabs( $tabs ) {
		$rename = trim( (string) $this->opt( 'rename_description' ) );
		if ( '' !== $rename && isset( $tabs['description'] ) ) {
			$tabs['description']['title'] = $rename;
		}
		if ( $this->opt( 'hide_additional', 0 ) ) {
			unset( $tabs['additional_information'] );
		}
		if ( $this->opt( 'hide_reviews', 0 ) ) {
			unset( $tabs['reviews'] );
		}
		for ( $i = 1; $i <= 3; $i++ ) {
			$title   = trim( (string) $this->opt( 'tab' . $i . '_title' ) );
			$content = (string) $this->opt( 'tab' . $i . '_content' );
			if ( '' !== $title && '' !== trim( $content ) ) {
				$tabs[ 'sg_tab_' . $i ] = array( 'title' => $title, 'priority' => 40 + $i, 'callback' => $this->render( $content ) );
			}
		}
		global $product;
		if ( $product ) {
			$title   = (string) $product->get_meta( '_sg_tab_title' );
			$content = (string) $product->get_meta( '_sg_tab_content' );
			if ( '' !== trim( $title ) && '' !== trim( $content ) ) {
				$tabs['sg_tab_product'] = array( 'title' => $title, 'priority' => 45, 'callback' => $this->render( $content ) );
			}
		}
		return $tabs;
	}

	/**
	 * Tab callback that prints the content.
	 *
	 * @param string $content Content.
	 * @return callable
	 */
	private function render( $content ) {
		return function () use ( $content ) {
			echo '<div class="sg-tab-content">' . wp_kses_post( wpautop( do_shortcode( $content ) ) ) . '</div>';
		};
	}

	public function product_fields() {
		echo '<div class="options_group">';
		woocommerce_wp_text_input( array( 'id' => '_sg_tab_title', 'label' => __( 'ShopGlut tab title', 'shopglut' ) ) );
		woocommerce_wp_textarea_input( array( 'id' => '_sg_tab_content', 'label' => __( 'ShopGlut tab content', 'shopglut' ), 'description' => __( 'An own tab for this product. HTML allowed.', 'shopglut' ), 'desc_tip' => true ) );
		echo '</div>';
	}

	/**
	 * Save the product tab (nonce checked by WooCommerce's product save).
	 *
	 * @param WC_Product $product Product.
	 */
	public function save_product( $product ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['_sg_tab_title'] ) ) {
			$product->update_meta_data( '_sg_tab_title', sanitize_text_field( wp_unslash( $_POST['_sg_tab_title'] ) ) );
		}
		if ( isset( $_POST['_sg_tab_content'] ) ) {
			$product->update_meta_data( '_sg_tab_content', wp_kses_post( wp_unslash( $_POST['_sg_tab_content'] ) ) );
		}
		// phpcs:enable
	}
}

return 'ShopGlut_Module_Product_Tabs';
