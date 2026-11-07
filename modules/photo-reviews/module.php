<?php
/**
 * Photo Reviews: customers add photos to their product review; photos show under the review and in a strip
 * above all reviews. Photos go to the Media Library, attached to the product.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Photo_Reviews class.
 */
class ShopGlut_Module_Photo_Reviews extends ShopGlut_Module {

	const META = '_sg_review_photos';

	public function id() {
		return 'photo-reviews';
	}

	public function info() {
		return array(
			'title'       => __( 'Photo Reviews', 'shopglut' ),
			'description' => __( 'Customers add photos to their reviews; photos show with the review and in a strip above the reviews.', 'shopglut' ),
			'icon'        => 'faCircleCheck',
			'category'    => 'product',
			'order'       => 79,
			'features'    => array(
				__( 'Up to your limit of photos per review', 'shopglut' ),
				__( 'Size and type checks, verified buyers only if you want', 'shopglut' ),
				__( 'Photo strip above the reviews', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default = '' ) {
		return shopglut_get_module_setting( 'photo-reviews', $key, $default );
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_action( 'comment_form_logged_in_after', array( $this, 'field' ) );
		add_action( 'comment_form_after_fields', array( $this, 'field' ) );
		add_action( 'comment_post', array( $this, 'upload' ), 20, 2 );
		add_action( 'woocommerce_review_after_comment_text', array( $this, 'show' ) );
		add_action( 'woocommerce_reviews_before', array( $this, 'strip' ) );
		add_action( 'wp_footer', array( $this, 'form_enctype' ) );
	}

	private function allowed( $product_id ) {
		if ( ! $this->opt( 'verified_only', 0 ) ) {
			return true;
		}
		$user = wp_get_current_user();
		return $user->exists() && wc_customer_bought_product( $user->user_email, $user->ID, $product_id );
	}

	public function field() {
		if ( ! is_product() || ! $this->allowed( get_the_ID() ) ) {
			return;
		}
		$max = max( 1, (int) $this->opt( 'max_photos', 3 ) );
		/* translators: 1: count, 2: MB */
		echo '<p class="comment-form-sg-photos"><label for="sg_review_photos">' . esc_html( sprintf( __( 'Add photos (up to %1$d, %2$d MB each)', 'shopglut' ), $max, (int) $this->opt( 'max_mb', 4 ) ) ) . '</label><input type="file" id="sg_review_photos" name="sg_review_photos[]" accept="image/jpeg,image/png,image/webp" multiple></p>';
	}

	/**
	 * The review form needs multipart encoding for files.
	 */
	public function form_enctype() {
		if ( is_product() ) {
			echo '<script>(function(){var f=document.getElementById("commentform");if(f&&f.querySelector("#sg_review_photos"))f.enctype="multipart/form-data";})();</script>';
		}
	}

	/**
	 * Save uploaded photos for a new review.
	 *
	 * @param int        $comment_id Comment id.
	 * @param int|string $approved   Approval.
	 * @return int[] Attachment ids.
	 */
	public function upload( $comment_id, $approved ) {
		$comment = get_comment( $comment_id );
		if ( ! $comment || 'product' !== get_post_type( $comment->comment_post_ID ) || empty( $_FILES['sg_review_photos']['name'][0] ) || 'spam' === $approved ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WordPress checked the comment form.
			return array();
		}
		if ( ! $this->allowed( $comment->comment_post_ID ) ) {
			return array();
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$files = $_FILES['sg_review_photos']; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput -- checked per file below.
		$max   = max( 1, (int) $this->opt( 'max_photos', 3 ) );
		$bytes = max( 1, (int) $this->opt( 'max_mb', 4 ) ) * MB_IN_BYTES;
		$ids   = array();
		foreach ( (array) $files['name'] as $i => $name ) {
			if ( count( $ids ) >= $max || UPLOAD_ERR_OK !== (int) $files['error'][ $i ] || (int) $files['size'][ $i ] > $bytes ) {
				continue;
			}
			$check = wp_check_filetype_and_ext( $files['tmp_name'][ $i ], $name, array( 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp' ) );
			if ( empty( $check['type'] ) ) {
				continue;
			}
			$_FILES['sg_one'] = array( 'name' => sanitize_file_name( $name ), 'type' => $check['type'], 'tmp_name' => $files['tmp_name'][ $i ], 'error' => 0, 'size' => $files['size'][ $i ] );
			$id               = media_handle_upload( 'sg_one', (int) $comment->comment_post_ID, array(), array( 'test_form' => false, 'action' => 'sg_review_photo', 'test_type' => true ) );
			if ( ! is_wp_error( $id ) ) {
				$ids[] = (int) $id;
			}
		}
		unset( $_FILES['sg_one'] );
		if ( $ids ) {
			update_comment_meta( $comment_id, self::META, $ids );
		}
		return $ids;
	}

	/**
	 * Photos under a review.
	 *
	 * @param WP_Comment $comment Review.
	 */
	public function show( $comment ) {
		$ids = (array) get_comment_meta( $comment->comment_ID, self::META, true );
		$ids = array_filter( array_map( 'absint', $ids ) );
		if ( ! $ids ) {
			return;
		}
		echo '<div class="sg-review-photos">';
		foreach ( $ids as $id ) {
			echo '<a href="' . esc_url( wp_get_attachment_image_url( $id, 'large' ) ) . '" target="_blank" rel="noopener">' . wp_get_attachment_image( $id, 'thumbnail', false, array( 'loading' => 'lazy' ) ) . '</a>';
		}
		echo '</div><style>.sg-review-photos{display:flex;gap:6px;flex-wrap:wrap;margin:.4em 0}.sg-review-photos img{width:72px;height:72px;object-fit:cover;border-radius:6px}</style>';
	}

	/**
	 * All review photos of the current product, above the reviews.
	 */
	public function strip() {
		if ( ! $this->opt( 'gallery', 1 ) ) {
			return;
		}
		$ids = array();
		foreach ( get_comments( array( 'post_id' => get_the_ID(), 'status' => 'approve', 'type' => 'review', 'meta_key' => self::META, 'number' => 30 ) ) as $c ) { // phpcs:ignore WordPress.DB.SlowDBQuery
			$ids = array_merge( $ids, array_map( 'absint', (array) get_comment_meta( $c->comment_ID, self::META, true ) ) );
		}
		if ( $ids ) {
			echo '<div class="sg-review-photos sg-review-strip">';
			foreach ( array_slice( array_unique( $ids ), 0, 12 ) as $id ) {
				echo '<a href="' . esc_url( wp_get_attachment_image_url( $id, 'large' ) ) . '" target="_blank" rel="noopener">' . wp_get_attachment_image( $id, 'thumbnail', false, array( 'loading' => 'lazy' ) ) . '</a>';
			}
			echo '</div>';
		}
	}
}

return 'ShopGlut_Module_Photo_Reviews';
