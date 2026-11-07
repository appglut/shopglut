<?php
/**
 * AI Shopping Assistant: a chat on the store. The customer says what they want; ShopGlut finds matching
 * products (text search + price limit), and the AI connected in Settings → Connectors picks the best ones
 * and explains why. The AI only sees and recommends real products from the store. Without an AI provider
 * the chat still answers with the search results.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Ai_Assistant class.
 */
class ShopGlut_Module_Ai_Assistant extends ShopGlut_Module {

	public function id() {
		return 'ai-assistant';
	}

	public function info() {
		return array(
			'title'       => __( 'AI Shopping Assistant', 'shopglut' ),
			'description' => __( 'A chat that helps customers find products by what they need and their budget, using your connected AI.', 'shopglut' ),
			'icon'        => 'faBullhorn',
			'category'    => 'engagement',
			'order'       => 140,
			'default_on'  => false,
			'features'    => array(
				__( 'Understands needs and budgets ("gift under 2000")', 'shopglut' ),
				__( 'Recommends only real products, with add to cart', 'shopglut' ),
				__( 'Any AI provider from Settings → Connectors', 'shopglut' ),
				__( 'Works as smart search without AI; rate limited', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default = '' ) {
		return shopglut_get_module_setting( 'ai-assistant', $key, $default );
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_action( 'rest_api_init', array( $this, 'route' ) );
		add_action( 'wp_footer', array( $this, 'widget' ) );
	}

	public function route() {
		register_rest_route(
			'shopglut/v1',
			'/assistant',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'rest' ),
				'args'                => array(
					'message' => array( 'type' => 'string', 'required' => true, 'maxLength' => 500 ),
					'history' => array( 'type' => 'array', 'default' => array() ),
				),
			)
		);
	}

	/**
	 * Price limits in a message ("under 5000", "below ৳2,000", "between 500 and 900").
	 *
	 * @param string $text Message.
	 * @return array min, max (null when none).
	 */
	public static function budget( $text ) {
		$t = str_replace( array( ',', '৳', '$', '€', '£' ), '', strtolower( $text ) );
		$t = strtr( $t, array( '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9' ) );
		if ( preg_match( '/between\s*(\d+(?:\.\d+)?)\s*(?:and|-|to)\s*(\d+(?:\.\d+)?)/', $t, $m ) ) {
			return array( (float) min( $m[1], $m[2] ), (float) max( $m[1], $m[2] ) );
		}
		if ( preg_match( '/(?:under|below|less than|max(?:imum)?|up to|within|er moddhe|er niche)\s*(\d+(?:\.\d+)?)|(\d+(?:\.\d+)?)\s*(?:er moddhe|er niche|taka er moddhe)/', $t, $m ) ) {
			return array( null, (float) ( '' !== $m[1] ? $m[1] : $m[2] ) );
		}
		if ( preg_match( '/(?:over|above|more than|at least|min(?:imum)?)\s*(\d+(?:\.\d+)?)/', $t, $m ) ) {
			return array( (float) $m[1], null );
		}
		return array( null, null );
	}

	/**
	 * Candidate products for a message.
	 *
	 * @param string $text Message (plus earlier customer messages).
	 * @param int    $limit Count.
	 * @return array
	 */
	public function candidates( $text, $limit = 12 ) {
		list( $min, $max ) = self::budget( $text );
		$stop  = array( 'i', 'need', 'want', 'looking', 'for', 'a', 'an', 'the', 'some', 'please', 'show', 'me', 'under', 'below', 'above', 'over', 'and', 'with', 'my', 'to', 'buy', 'gift', 'taka', 'tk', 'budget', 'price', 'between', 'less', 'than', 'up', 'any', 'do', 'you', 'have' );
		$words = array_filter( preg_split( '/[^\p{L}\p{N}]+/u', mb_strtolower( $text ) ), function ( $w ) use ( $stop ) {
			return mb_strlen( $w ) > 2 && ! in_array( $w, $stop, true ) && ! is_numeric( $w );
		} );
		$ids = array();
		$base = array( 'post_type' => 'product', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 40, 'tax_query' => array( array( 'taxonomy' => 'product_visibility', 'field' => 'slug', 'terms' => array( 'exclude-from-search', 'outofstock' ), 'operator' => 'NOT IN' ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( null !== $min || null !== $max ) {
			$base['meta_query'] = array( array( 'key' => '_price', 'value' => array( null !== $min ? $min : 0, null !== $max ? $max : PHP_INT_MAX ), 'compare' => 'BETWEEN', 'type' => 'DECIMAL(12,2)' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		if ( $words ) {
			$ids = get_posts( $base + array( 's' => implode( ' ', array_slice( $words, 0, 6 ) ) ) );
			foreach ( array_slice( $words, 0, 6 ) as $w ) {
				if ( count( $ids ) >= $limit ) {
					break;
				}
				$ids = array_merge( $ids, get_posts( $base + array( 's' => $w ) ) );
				$cat = get_term_by( 'slug', sanitize_title( $w ), 'product_cat' );
				if ( $cat ) {
					$ids = array_merge( $ids, get_posts( $base + array( 'tax_query' => array( array( 'taxonomy' => 'product_cat', 'terms' => $cat->term_id ) ) ) ) );
				}
			}
		}
		if ( ! $ids ) {
			$ids = get_posts( $base + array( 'orderby' => 'meta_value_num', 'meta_key' => 'total_sales', 'order' => 'DESC' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		$out = array();
		foreach ( array_unique( $ids ) as $id ) {
			$p = wc_get_product( $id );
			if ( ! $p || ! $p->is_visible() || ! $p->is_in_stock() ) {
				continue;
			}
			$out[ $p->get_id() ] = array(
				'id'         => $p->get_id(),
				'name'       => $p->get_name(),
				'price'      => (float) $p->get_price(),
				'price_html' => wp_strip_all_tags( html_entity_decode( $p->get_price_html() ) ),
				'categories' => wp_list_pluck( (array) get_the_terms( $p->get_id(), 'product_cat' ), 'name' ),
				'summary'    => wp_trim_words( wp_strip_all_tags( $p->get_short_description() ? $p->get_short_description() : $p->get_description() ), 30, '…' ),
				'url'        => $p->get_permalink(),
				'image'      => (string) wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ),
				'cart_url'   => $p->is_type( 'simple' ) && $p->is_purchasable() ? add_query_arg( 'add-to-cart', $p->get_id(), wc_get_cart_url() ) : $p->get_permalink(),
			);
			if ( count( $out ) >= $limit ) {
				break;
			}
		}
		return array_values( $out );
	}

	/**
	 * Answer a message.
	 *
	 * @param string $message Message.
	 * @param array  $history Earlier turns: array( role, text ).
	 * @return array reply, products.
	 */
	public function answer( $message, $history = array() ) {
		$message = trim( wp_strip_all_tags( (string) $message ) );
		$context = $message;
		foreach ( array_slice( (array) $history, -6 ) as $turn ) {
			if ( is_array( $turn ) && isset( $turn['role'], $turn['text'] ) && 'user' === $turn['role'] ) {
				$context = wp_strip_all_tags( (string) $turn['text'] ) . ' ' . $context;
			}
		}
		$cands = $this->candidates( $context );
		$fallback = array(
			'reply'    => $cands ? __( 'Here are some products that match:', 'shopglut' ) : __( 'Sorry, I could not find a match. Try other words, or browse the shop.', 'shopglut' ),
			'products' => array_slice( $cands, 0, 4 ),
		);
		if ( ! function_exists( 'wp_ai_client_prompt' ) || ( function_exists( 'wp_supports_ai' ) && ! wp_supports_ai() ) || ! $cands ) {
			return $fallback;
		}
		$facts  = trim( (string) $this->opt( 'store_notes', '' ) );
		$system = 'You are the friendly shopping assistant of the online store "' . get_bloginfo( 'name' ) . '". Recommend only products from the CANDIDATES list (by id), never invent products, prices, stock or features. Respect the customer\'s budget. Pick 1-4 products and say in one short sentence each why they fit. If nothing fits, say so kindly and ask one short question. Reply in the customer\'s language, under 90 words, plain text.' . ( $facts ? ' Store facts you may use: ' . $facts : '' ) . ' Return JSON: {"reply": string, "product_ids": [numbers]}.';
		$trim   = array_map( function ( $c ) { return array_intersect_key( $c, array_flip( array( 'id', 'name', 'price', 'categories', 'summary' ) ) ); }, $cands );
		$turns  = '';
		foreach ( array_slice( (array) $history, -6 ) as $turn ) {
			if ( is_array( $turn ) && isset( $turn['role'], $turn['text'] ) ) {
				$turns .= ( 'user' === $turn['role'] ? 'Customer: ' : 'Assistant: ' ) . wp_strip_all_tags( (string) $turn['text'] ) . "\n";
			}
		}
		$prompt = 'Currency: ' . get_woocommerce_currency() . "\nCANDIDATES: " . wp_json_encode( $trim ) . "\n" . $turns . 'Customer: ' . $message;
		try {
			$res  = wp_ai_client_prompt( $prompt )->using_system_instruction( $system )->using_max_tokens( 500 )->as_json_response( array( 'type' => 'object', 'properties' => array( 'reply' => array( 'type' => 'string' ), 'product_ids' => array( 'type' => 'array', 'items' => array( 'type' => 'integer' ) ) ), 'required' => array( 'reply', 'product_ids' ) ) )->generate_text_result();
			$data = is_wp_error( $res ) ? null : json_decode( preg_replace( '/^```(?:json)?|```$/m', '', (string) $res->toText() ), true );
		} catch ( \Throwable $e ) {
			$data = null;
		}
		if ( ! is_array( $data ) || ! isset( $data['reply'] ) ) {
			return $fallback;
		}
		$by_id = array();
		foreach ( $cands as $c ) {
			$by_id[ $c['id'] ] = $c;
		}
		$picked = array();
		foreach ( (array) $data['product_ids'] as $id ) {
			if ( isset( $by_id[ (int) $id ] ) ) {
				$picked[] = $by_id[ (int) $id ];
			}
		}
		return array( 'reply' => sanitize_textarea_field( $data['reply'] ), 'products' => array_slice( $picked, 0, 4 ) );
	}

	public function rest( $request ) {
		$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
		$key   = 'sg_ai_rate_' . md5( $ip );
		$count = (int) get_transient( $key );
		if ( $count >= max( 1, (int) $this->opt( 'per_hour', 20 ) ) ) {
			return new WP_REST_Response( array( 'reply' => __( 'You have sent many messages. Please try again a little later, or use the search.', 'shopglut' ), 'products' => array() ), 429 );
		}
		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
		$out = $this->answer( $request->get_param( 'message' ), (array) $request->get_param( 'history' ) );
		foreach ( $out['products'] as &$p ) {
			unset( $p['summary'], $p['categories'], $p['price'] );
		}
		return rest_ensure_response( $out );
	}

	public function widget() {
		if ( 'all' !== $this->opt( 'where', 'shop' ) && ! ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) ) {
			return;
		}
		$api = esc_url_raw( rest_url( 'shopglut/v1/assistant' ) );
		echo '<div id="sg-ai" class="sg-ai"><button type="button" class="sg-ai-open" aria-expanded="false" aria-controls="sg-ai-panel">💬 ' . esc_html( $this->opt( 'title', __( 'Shopping assistant', 'shopglut' ) ) ) . '</button>';
		echo '<div id="sg-ai-panel" class="sg-ai-panel" hidden><div class="sg-ai-head"><strong>' . esc_html( $this->opt( 'title', __( 'Shopping assistant', 'shopglut' ) ) ) . '</strong><button type="button" class="sg-ai-close" aria-label="' . esc_attr__( 'Close', 'shopglut' ) . '">×</button></div><div class="sg-ai-log" aria-live="polite"><div class="sg-ai-msg bot">' . esc_html( $this->opt( 'greeting', __( 'Hi! Tell me what you are looking for, and your budget.', 'shopglut' ) ) ) . '</div></div><form class="sg-ai-form"><input type="text" maxlength="500" required placeholder="' . esc_attr__( 'Type your message…', 'shopglut' ) . '"><button type="submit">' . esc_html__( 'Send', 'shopglut' ) . '</button></form></div></div>';
		echo '<style>.sg-ai{position:fixed;right:16px;bottom:16px;z-index:99990;font-size:15px}.sg-ai-open{border:0;border-radius:99px;padding:12px 18px;background:#111827;color:#fff;cursor:pointer;box-shadow:0 6px 20px rgba(0,0,0,.2)}.sg-ai-panel{position:absolute;right:0;bottom:56px;width:min(360px,calc(100vw - 32px));height:min(520px,70vh);display:flex;flex-direction:column;background:#fff;color:#111;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.25);overflow:hidden}.sg-ai-panel[hidden]{display:none}.sg-ai-head{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:#111827;color:#fff}.sg-ai-close{background:none;border:0;color:#fff;font-size:22px;cursor:pointer}.sg-ai-log{flex:1;overflow:auto;padding:12px;display:flex;flex-direction:column;gap:8px}.sg-ai-msg{max-width:85%;padding:8px 12px;border-radius:12px;white-space:pre-wrap}.sg-ai-msg.bot{background:#f3f4f6;align-self:flex-start}.sg-ai-msg.me{background:#111827;color:#fff;align-self:flex-end}.sg-ai-card{display:flex;gap:8px;align-items:center;border:1px solid #e5e7eb;border-radius:10px;padding:6px;text-decoration:none;color:inherit}.sg-ai-card img{width:44px;height:44px;object-fit:cover;border-radius:6px}.sg-ai-card small{display:block;opacity:.7}.sg-ai-card .sg-ai-add{margin-left:auto;font-size:12px;padding:4px 8px;border-radius:6px;background:#111827;color:#fff;text-decoration:none}.sg-ai-form{display:flex;gap:6px;padding:10px;border-top:1px solid #e5e7eb}.sg-ai-form input{flex:1;min-width:0;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px}.sg-ai-form button{border:0;border-radius:8px;padding:8px 12px;background:#111827;color:#fff;cursor:pointer}</style>';
		echo '<script>(function(){var w=document.getElementById("sg-ai");if(!w)return;var o=w.querySelector(".sg-ai-open"),p=w.querySelector(".sg-ai-panel"),log=w.querySelector(".sg-ai-log"),f=w.querySelector(".sg-ai-form"),i=f.querySelector("input"),hist=[],api=' . wp_json_encode( $api ) . ';function e(s){return String(s).replace(/[&<>"]/g,function(c){return{"&":"&amp;","<":"&lt;",">":"&gt;","\\"":"&quot;"}[c]})}function add(cls,html){var d=document.createElement("div");d.className="sg-ai-msg "+cls;d.innerHTML=html;log.appendChild(d);log.scrollTop=log.scrollHeight;return d}function toggle(s){p.hidden=!s;o.setAttribute("aria-expanded",s);if(s)i.focus()}o.addEventListener("click",function(){toggle(p.hidden)});w.querySelector(".sg-ai-close").addEventListener("click",function(){toggle(false)});f.addEventListener("submit",function(ev){ev.preventDefault();var m=i.value.trim();if(!m)return;i.value="";add("me",e(m));var t=add("bot","…");fetch(api,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({message:m,history:hist})}).then(function(r){return r.json()}).then(function(d){var h=e(d.reply||"");(d.products||[]).forEach(function(x){h+="<a class=sg-ai-card href=\\""+e(x.url)+"\\">"+(x.image?"<img src=\\""+e(x.image)+"\\" alt=\\"\\">":"")+"<span>"+e(x.name)+"<small>"+e(x.price_html)+"</small></span><span class=sg-ai-add data-href=\\""+e(x.cart_url)+"\\">' . esc_js( __( 'Add', 'shopglut' ) ) . '</span></a>"});t.innerHTML=h;hist.push({role:"user",text:m},{role:"assistant",text:d.reply||""});hist=hist.slice(-12);log.scrollTop=log.scrollHeight}).catch(function(){t.textContent="' . esc_js( __( 'Sorry, something went wrong. Please try again.', 'shopglut' ) ) . '"})});log.addEventListener("click",function(ev){var a=ev.target.closest(".sg-ai-add");if(a){ev.preventDefault();location.href=a.dataset.href}})})();</script>';
	}
}

return 'ShopGlut_Module_Ai_Assistant';
