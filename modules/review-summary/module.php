<?php
/**
 * Review Summary: a short "Customers say…" paragraph above the reviews, written by the AI connected in
 * Settings → Connectors from the product's approved reviews. It is made in the background (Action
 * Scheduler) when a product has at least 3 reviews and again after new ones, and saved on the product,
 * so pages never wait for the AI.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Review_Summary class.
 */
class ShopGlut_Module_Review_Summary extends ShopGlut_Module {

	const META = '_sg_review_summary';
	const HOOK = 'shopglut_review_summary';
	const MIN  = 3;

	public function id() {
		return 'review-summary';
	}

	public function info() {
		return array(
			'title'       => __( 'AI Review Summary', 'shopglut' ),
			'description' => __( 'A short "Customers say…" summary of the reviews above the review list, written by your connected AI.', 'shopglut' ),
			'icon'        => 'faPenToSquare',
			'category'    => 'product',
			'order'       => 80,
			'default_on'  => false,
			'features'    => array(
				__( 'Pros and cons customers mention most', 'shopglut' ),
				__( 'Made in the background, saved on the product', 'shopglut' ),
				__( 'Updated after new reviews', 'shopglut' ),
			),
		);
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_action( 'woocommerce_reviews_before', array( $this, 'show' ), 5 );
		add_action( 'wp_set_comment_status', array( $this, 'queue_for_comment' ), 20, 2 );
		add_action( 'comment_post', array( $this, 'queue_for_comment' ), 30, 2 );
		add_action( self::HOOK, array( $this, 'generate' ) );
	}

	public function queue_for_comment( $comment_id, $status ) {
		$c = get_comment( $comment_id );
		if ( $c && 'review' === $c->comment_type && in_array( $status, array( 1, '1', 'approve' ), true ) ) {
			$this->queue( (int) $c->comment_post_ID );
		}
	}

	public function queue( $product_id ) {
		if ( function_exists( 'as_enqueue_async_action' ) && ! as_has_scheduled_action( self::HOOK, array( 'product_id' => $product_id ), 'shopglut' ) ) {
			as_enqueue_async_action( self::HOOK, array( 'product_id' => $product_id ), 'shopglut' );
		}
	}

	/**
	 * Write (or refresh) the summary.
	 *
	 * @param int $product_id Product id.
	 * @return string|WP_Error Summary.
	 */
	public function generate( $product_id ) {
		$reviews = get_comments( array( 'post_id' => $product_id, 'status' => 'approve', 'type' => 'review', 'number' => 60 ) );
		if ( count( $reviews ) < self::MIN ) {
			return new WP_Error( 'sg_few', 'Not enough reviews.' );
		}
		$saved = get_post_meta( $product_id, self::META, true );
		if ( is_array( $saved ) && (int) $saved['count'] === count( $reviews ) ) {
			return $saved['text'];
		}
		if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
			return new WP_Error( 'sg_no_ai', 'No AI client.' );
		}
		$lines = array();
		foreach ( $reviews as $r ) {
			$rating  = (int) get_comment_meta( $r->comment_ID, 'rating', true );
			$lines[] = ( $rating ? $rating . '/5: ' : '' ) . wp_trim_words( wp_strip_all_tags( $r->comment_content ), 80, '…' );
		}
		$system = 'Summarise customer reviews of one product for shoppers. Use only what the reviews say. 2-3 sentences, under 60 words, start with "Customers say", mention the most common praise and the most common complaint if any. Write in the language most reviews use. Plain text, no quotes of names.';
		try {
			$res  = wp_ai_client_prompt( "Product: " . get_the_title( $product_id ) . "\nReviews:\n- " . implode( "\n- ", $lines ) )->using_system_instruction( $system )->using_max_tokens( 200 )->generate_text_result();
			$text = is_wp_error( $res ) ? '' : trim( (string) $res->toText() );
		} catch ( \Throwable $e ) {
			return new WP_Error( 'sg_ai', $e->getMessage() );
		}
		if ( '' === $text ) {
			return new WP_Error( 'sg_ai', 'Empty answer.' );
		}
		$text = sanitize_textarea_field( $text );
		update_post_meta( $product_id, self::META, array( 'text' => $text, 'count' => count( $reviews ), 'at' => time() ) );
		return $text;
	}

	public function show() {
		$id    = get_the_ID();
		$saved = get_post_meta( $id, self::META, true );
		if ( ! is_array( $saved ) || '' === (string) $saved['text'] ) {
			return;
		}
		/* translators: %d: count */
		echo '<div class="sg-review-summary"><p>' . esc_html( $saved['text'] ) . '</p><small>' . esc_html( sprintf( _n( 'AI summary of %d review', 'AI summary of %d reviews', (int) $saved['count'], 'shopglut' ), (int) $saved['count'] ) ) . '</small></div><style>.sg-review-summary{padding:12px 14px;border-radius:10px;background:rgba(0,0,0,.04);margin:0 0 1.2em}.sg-review-summary p{margin:0 0 4px}.sg-review-summary small{opacity:.65}</style>';
	}
}

return 'ShopGlut_Module_Review_Summary';
