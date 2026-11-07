<?php
/**
 * Product Video: a YouTube, Vimeo or .mp4 video per product, under the images, in a Video tab or under the
 * add to cart button.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Product_Video class.
 */
class ShopGlut_Module_Product_Video extends ShopGlut_Module {

	const META = '_sg_video_url';

	public function id() {
		return 'product-video';
	}

	public function info() {
		return array(
			'title'        => __( 'Product Video', 'shopglut' ),
			'description'  => __( 'Add a YouTube, Vimeo or MP4 video to any product page.', 'shopglut' ),
			'icon'         => 'faEye',
			'category'     => 'product',
			'order'        => 76,
			'features'     => array(
				__( 'YouTube, Vimeo and MP4', 'shopglut' ),
				__( 'Under the images, in a tab or under add to cart', 'shopglut' ),
				__( 'Lazy-loaded, responsive', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default = '' ) {
		return shopglut_get_module_setting( 'product-video', $key, $default );
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'field' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save' ) );
		$pos = $this->opt( 'position', 'after_gallery' );
		if ( 'tab' === $pos ) {
			add_filter( 'woocommerce_product_tabs', array( $this, 'tab' ) );
		} elseif ( 'summary' === $pos ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'show' ), 35 );
		} else {
			add_action( 'woocommerce_after_single_product_summary', array( $this, 'show' ), 5 );
		}
	}

	/**
	 * Video HTML for a product, or ''.
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public function html( $product ) {
		$url = esc_url_raw( (string) $product->get_meta( self::META ) );
		if ( '' === $url ) {
			return '';
		}
		if ( preg_match( '/\.(mp4|webm|ogg)(\?.*)?$/i', $url ) ) {
			$inner = '<video src="' . esc_url( $url ) . '" controls preload="metadata" playsinline></video>';
		} else {
			$embed = wp_oembed_get( $url, array( 'width' => 960 ) );
			if ( ! $embed && preg_match( '~(?:youtube\.com/(?:watch\?v=|shorts/|embed/)|youtu\.be/)([\w-]{6,})~', $url, $m ) ) {
				$embed = '<iframe src="https://www.youtube-nocookie.com/embed/' . esc_attr( $m[1] ) . '" loading="lazy" allowfullscreen title="' . esc_attr( $product->get_name() ) . '"></iframe>';
			}
			$inner = $embed ? $embed : '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Watch the video', 'shopglut' ) . '</a>';
		}
		return '<div class="sg-product-video">' . $inner . '</div><style>.sg-product-video{margin:1em 0;clear:both}.sg-product-video iframe,.sg-product-video video{width:100%;aspect-ratio:16/9;height:auto;border:0;border-radius:8px}</style>';
	}

	public function show() {
		global $product;
		if ( $product ) {
			echo $this->html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in html(); oEmbed HTML from WordPress.
		}
	}

	public function tab( $tabs ) {
		global $product;
		if ( $product && '' !== (string) $product->get_meta( self::META ) ) {
			$tabs['sg_video'] = array( 'title' => $this->opt( 'tab_title', __( 'Video', 'shopglut' ) ), 'priority' => 25, 'callback' => array( $this, 'show' ) );
		}
		return $tabs;
	}

	public function field() {
		woocommerce_wp_text_input( array( 'id' => self::META, 'label' => __( 'Product video URL', 'shopglut' ), 'placeholder' => 'https://youtu.be/…', 'description' => __( 'YouTube, Vimeo or an .mp4 file.', 'shopglut' ), 'desc_tip' => true ) );
	}

	public function save( $product ) {
		if ( isset( $_POST[ self::META ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$product->update_meta_data( self::META, esc_url_raw( wp_unslash( $_POST[ self::META ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
	}
}

return 'ShopGlut_Module_Product_Video';
