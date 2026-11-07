<?php
/**
 * Product Questions & Answers: customers ask questions in a product tab; the shop answers by replying in
 * Comments. Questions are comments of type "sg_question" on the product, so moderation, spam checks and
 * replies use WordPress's own tools.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Product_Qa class.
 */
class ShopGlut_Module_Product_Qa extends ShopGlut_Module {

	const TYPE = 'sg_question';

	public function id() {
		return 'product-qa';
	}

	public function info() {
		return array(
			'title'        => __( 'Product Questions & Answers', 'shopglut' ),
			'description'  => __( 'Customers ask questions on the product page and you answer them; answers help the next buyers.', 'shopglut' ),
			'icon'         => 'faPenToSquare',
			'category'     => 'product',
			'order'        => 78,
			'features'     => array(
				__( 'Questions & Answers tab on product pages', 'shopglut' ),
				__( 'Answer from Comments; moderation and spam checks', 'shopglut' ),
				__( 'Email to the shop for new questions', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default = '' ) {
		return shopglut_get_module_setting( 'product-qa', $key, $default );
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_filter( 'woocommerce_product_tabs', array( $this, 'tab' ) );
		add_action( 'template_redirect', array( $this, 'submit' ) );
		add_filter( 'admin_comment_types_dropdown', array( $this, 'admin_type' ) );
		// Keep questions out of the review list and rating counts.
		add_filter( 'woocommerce_product_review_list_args', array( $this, 'review_args' ) );
		add_action( 'pre_get_comments', array( $this, 'hide_from_reviews' ) );
	}

	public function admin_type( $types ) {
		$types[ self::TYPE ] = __( 'Product questions', 'shopglut' );
		return $types;
	}

	public function review_args( $args ) {
		$args['type'] = 'review';
		return $args;
	}

	/**
	 * Product review queries (type "review" or the comments template) should not list questions.
	 *
	 * @param WP_Comment_Query $q Query.
	 */
	public function hide_from_reviews( $q ) {
		if ( is_admin() || ! empty( $q->query_vars['type'] ) || ! is_singular( 'product' ) ) {
			return;
		}
		$q->query_vars['type__not_in'] = array_merge( (array) $q->query_vars['type__not_in'], array( self::TYPE ) );
	}

	public function tab( $tabs ) {
		$tabs['sg_qa'] = array( 'title' => $this->opt( 'tab_title', __( 'Questions & Answers', 'shopglut' ) ), 'priority' => 35, 'callback' => array( $this, 'render' ) );
		return $tabs;
	}

	/**
	 * Approved questions of a product with their answers.
	 *
	 * @param int $product_id Product id.
	 * @return array question => answers
	 */
	public function questions( $product_id ) {
		$out = array();
		$qs  = get_comments( array( 'post_id' => $product_id, 'type' => self::TYPE, 'parent' => 0, 'status' => 'approve', 'orderby' => 'comment_date_gmt', 'order' => 'DESC' ) );
		foreach ( $qs as $q ) {
			$out[] = array( 'q' => $q, 'a' => get_comments( array( 'post_id' => $product_id, 'parent' => $q->comment_ID, 'status' => 'approve' ) ) );
		}
		return $out;
	}

	public function render() {
		global $product;
		if ( ! $product ) {
			return;
		}
		echo '<div class="sg-qa">';
		if ( isset( $_GET['sg_qa'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<p class="woocommerce-message">' . esc_html__( 'Thank you! Your question was sent and will show here once it is answered.', 'shopglut' ) . '</p>';
		}
		$list = $this->questions( $product->get_id() );
		if ( ! $list ) {
			echo '<p>' . esc_html__( 'No questions yet. Ask the first one!', 'shopglut' ) . '</p>';
		}
		foreach ( $list as $row ) {
			echo '<div class="sg-qa-item"><p class="sg-qa-q"><strong>' . esc_html__( 'Q:', 'shopglut' ) . '</strong> ' . esc_html( $row['q']->comment_content ) . ' <small>— ' . esc_html( $row['q']->comment_author ) . '</small></p>';
			foreach ( $row['a'] as $a ) {
				echo '<p class="sg-qa-a"><strong>' . esc_html__( 'A:', 'shopglut' ) . '</strong> ' . wp_kses_post( $a->comment_content ) . '</p>';
			}
			echo '</div>';
		}
		if ( is_user_logged_in() || $this->opt( 'guests', 1 ) ) {
			echo '<form method="post" class="sg-qa-form"><h4>' . esc_html__( 'Ask a question', 'shopglut' ) . '</h4>' . wp_nonce_field( 'sg_qa_' . $product->get_id(), 'sg_qa_nonce', true, false );
			echo '<input type="hidden" name="sg_qa_product" value="' . esc_attr( $product->get_id() ) . '">';
			if ( ! is_user_logged_in() ) {
				echo '<p><input type="text" name="sg_qa_name" required placeholder="' . esc_attr__( 'Your name', 'shopglut' ) . '"> <input type="email" name="sg_qa_email" required placeholder="' . esc_attr__( 'Your email', 'shopglut' ) . '"></p>';
			}
			echo '<p><textarea name="sg_qa_text" required rows="3" maxlength="1000" style="width:100%"></textarea></p><p><button type="submit" class="button">' . esc_html__( 'Send question', 'shopglut' ) . '</button></p></form>';
		}
		echo '</div><style>.sg-qa-item{padding:10px 0;border-bottom:1px solid rgba(0,0,0,.08)}.sg-qa-a{margin-left:1.2em;opacity:.9}.sg-qa-form{margin-top:1em}</style>';
	}

	/**
	 * Save a question.
	 *
	 * @return int|null New comment id.
	 */
	public function submit() {
		if ( empty( $_POST['sg_qa_product'] ) || empty( $_POST['sg_qa_text'] ) ) {
			return null;
		}
		$pid = absint( $_POST['sg_qa_product'] );
		if ( ! isset( $_POST['sg_qa_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sg_qa_nonce'] ) ), 'sg_qa_' . $pid ) || 'product' !== get_post_type( $pid ) ) {
			return null;
		}
		$user = wp_get_current_user();
		if ( ! $user->exists() && ! $this->opt( 'guests', 1 ) ) {
			return null;
		}
		$name  = $user->exists() ? $user->display_name : sanitize_text_field( wp_unslash( isset( $_POST['sg_qa_name'] ) ? $_POST['sg_qa_name'] : '' ) );
		$email = $user->exists() ? $user->user_email : sanitize_email( wp_unslash( isset( $_POST['sg_qa_email'] ) ? $_POST['sg_qa_email'] : '' ) );
		if ( '' === $name || ! is_email( $email ) ) {
			return null;
		}
		$data = array(
			'comment_post_ID'      => $pid,
			'comment_author'       => $name,
			'comment_author_email' => $email,
			'comment_content'      => sanitize_textarea_field( wp_unslash( $_POST['sg_qa_text'] ) ),
			'comment_type'         => self::TYPE,
			'user_id'              => $user->ID,
		);
		$data['comment_approved'] = $this->opt( 'moderate', 1 ) ? 0 : wp_allow_comment( $data, true );
		if ( is_wp_error( $data['comment_approved'] ) ) {
			return null;
		}
		$id = wp_insert_comment( wp_slash( $data ) );
		if ( $id && $this->opt( 'notify', 1 ) ) {
			/* translators: %s: product */
			wp_mail( get_option( 'admin_email' ), sprintf( __( 'New product question: %s', 'shopglut' ), get_the_title( $pid ) ), $data['comment_content'] . "\n\n" . admin_url( 'comment.php?action=editcomment&c=' . $id ) );
		}
		if ( ! defined( 'SHOPGLUT_TESTING' ) ) {
			wp_safe_redirect( add_query_arg( 'sg_qa', 1, get_permalink( $pid ) ) . '#tab-sg_qa' );
			exit;
		}
		return $id;
	}
}

return 'ShopGlut_Module_Product_Qa';
