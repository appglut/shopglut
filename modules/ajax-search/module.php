<?php
/**
 * Ajax Search: live product search as you type (name, SKU, categories) with image and price, a
 * [shopglut_search] shortcode, and the theme's product search form upgraded. Results come from a small
 * REST endpoint (shopglut/v1/search).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Ajax_Search class.
 */
class ShopGlut_Module_Ajax_Search extends ShopGlut_Module {

	public function id() {
		return 'ajax-search';
	}

	public function info() {
		return array(
			'title'       => __( 'Ajax Product Search', 'shopglut' ),
			'description' => __( 'Live product search as you type, with images, prices, SKUs and categories.', 'shopglut' ),
			'icon'        => 'faFilter',
			'category'    => 'product',
			'order'       => 65,
			'features'    => array(
				__( 'Results while typing, keyboard friendly', 'shopglut' ),
				__( 'Product name, SKU and category matches', 'shopglut' ),
				__( '[shopglut_search] and the theme search form', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default = '' ) {
		return shopglut_get_module_setting( 'ajax-search', $key, $default );
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_shortcode( 'shopglut_search', array( $this, 'form' ) );
		add_action( 'rest_api_init', array( $this, 'route' ) );
		if ( $this->opt( 'replace_form', 1 ) ) {
			add_filter( 'get_product_search_form', array( $this, 'form' ), 20 );
		}
	}

	public function route() {
		register_rest_route(
			'shopglut/v1',
			'/search',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'rest' ),
				'args'                => array( 'q' => array( 'type' => 'string', 'required' => true ) ),
			)
		);
	}

	/**
	 * Search results.
	 *
	 * @param string $q Text.
	 * @return array products, categories.
	 */
	public function search( $q ) {
		$q     = trim( wp_strip_all_tags( (string) $q ) );
		$limit = max( 1, min( 20, (int) $this->opt( 'limit', 8 ) ) );
		if ( mb_strlen( $q ) < max( 1, (int) $this->opt( 'min_chars', 2 ) ) ) {
			return array( 'products' => array(), 'categories' => array() );
		}
		$ids = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 's' => $q, 'posts_per_page' => $limit, 'fields' => 'ids', 'tax_query' => array( array( 'taxonomy' => 'product_visibility', 'field' => 'slug', 'terms' => array( 'exclude-from-search' ), 'operator' => 'NOT IN' ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $this->opt( 'sku', 1 ) && count( $ids ) < $limit ) {
			global $wpdb;
			$like = '%' . $wpdb->esc_like( $q ) . '%';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$sku = $wpdb->get_col( $wpdb->prepare( "SELECT IF(p.post_type = 'product_variation', p.post_parent, p.ID) FROM {$wpdb->wc_product_meta_lookup} l JOIN {$wpdb->posts} p ON p.ID = l.product_id WHERE l.sku LIKE %s AND p.post_status = 'publish' LIMIT %d", $like, $limit ) );
			$ids = array_slice( array_unique( array_merge( $ids, array_map( 'intval', $sku ) ) ), 0, $limit );
		}
		$products = array();
		foreach ( $ids as $id ) {
			$p = wc_get_product( $id );
			if ( ! $p || ! $p->is_visible() ) {
				continue;
			}
			$products[] = array(
				'name'  => $p->get_name(),
				'url'   => $p->get_permalink(),
				'price' => wp_strip_all_tags( html_entity_decode( $p->get_price_html() ) ),
				'image' => (string) wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ),
				'sku'   => $p->get_sku(),
			);
		}
		$cats = array();
		if ( $this->opt( 'categories', 1 ) ) {
			foreach ( get_terms( array( 'taxonomy' => 'product_cat', 'name__like' => $q, 'number' => 3, 'hide_empty' => true ) ) as $t ) {
				$cats[] = array( 'name' => $t->name, 'url' => get_term_link( $t ), 'count' => (int) $t->count );
			}
		}
		return array( 'products' => $products, 'categories' => $cats );
	}

	public function rest( $request ) {
		return rest_ensure_response( $this->search( $request->get_param( 'q' ) ) );
	}

	/**
	 * Search form with live results.
	 *
	 * @return string
	 */
	public function form() {
		static $n = 0;
		++$n;
		$id   = 'sg-search-' . $n;
		$html = '<form role="search" method="get" class="sg-search woocommerce-product-search" action="' . esc_url( home_url( '/' ) ) . '"><label class="screen-reader-text" for="' . esc_attr( $id ) . '">' . esc_html__( 'Search for:', 'shopglut' ) . '</label>';
		$html .= '<input type="search" id="' . esc_attr( $id ) . '" class="search-field" name="s" autocomplete="off" placeholder="' . esc_attr( $this->opt( 'placeholder', __( 'Search products…', 'shopglut' ) ) ) . '" value="' . esc_attr( get_search_query() ) . '" data-min="' . esc_attr( (int) $this->opt( 'min_chars', 2 ) ) . '" aria-controls="' . esc_attr( $id ) . '-r" aria-autocomplete="list">';
		$html .= '<input type="hidden" name="post_type" value="product"><button type="submit">' . esc_html__( 'Search', 'shopglut' ) . '</button><div class="sg-search-results" id="' . esc_attr( $id ) . '-r" role="listbox" hidden></div></form>';
		if ( 1 === $n ) {
			$html .= '<style>.sg-search{position:relative;display:flex;gap:6px}.sg-search .search-field{flex:1;min-width:0}.sg-search-results{position:absolute;top:100%;left:0;right:0;z-index:9999;background:#fff;color:#111;border:1px solid rgba(0,0,0,.12);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.12);max-height:420px;overflow:auto;margin-top:4px}.sg-search-results a{display:flex;align-items:center;gap:10px;padding:8px 10px;text-decoration:none;color:inherit}.sg-search-results a:hover,.sg-search-results a.on{background:rgba(0,0,0,.05)}.sg-search-results img{width:40px;height:40px;object-fit:cover;border-radius:4px}.sg-search-results small{display:block;opacity:.65}.sg-search-results .sg-s-h{padding:6px 10px;font-size:12px;opacity:.6;text-transform:uppercase}</style>';
			$html .= '<script>(function(){var api=' . wp_json_encode( esc_url_raw( rest_url( 'shopglut/v1/search' ) ) ) . ';function e(s){return String(s).replace(/[&<>"]/g,function(c){return{"&":"&amp;","<":"&lt;",">":"&gt;","\\"":"&quot;"}[c]})}document.querySelectorAll(".sg-search").forEach(function(f){var i=f.querySelector(".search-field"),r=f.querySelector(".sg-search-results"),t,k=-1;function show(d){var h="";if(d.categories&&d.categories.length){h+="<div class=sg-s-h>' . esc_js( __( 'Categories', 'shopglut' ) ) . '</div>";d.categories.forEach(function(c){h+="<a role=option href=\\""+e(c.url)+"\\">"+e(c.name)+" <small>("+c.count+")</small></a>"})}if(d.products.length){h+="<div class=sg-s-h>' . esc_js( __( 'Products', 'shopglut' ) ) . '</div>";d.products.forEach(function(p){h+="<a role=option href=\\""+e(p.url)+"\\">"+(p.image?"<img src=\\""+e(p.image)+"\\" alt=\\"\\">":"")+"<span>"+e(p.name)+"<small>"+e(p.price)+(p.sku?" · "+e(p.sku):"")+"</small></span></a>"})}if(!h)h="<div class=sg-s-h>' . esc_js( __( 'No products found', 'shopglut' ) ) . '</div>";r.innerHTML=h;r.hidden=false;k=-1}i.addEventListener("input",function(){clearTimeout(t);var q=i.value.trim();if(q.length<(+i.dataset.min||2)){r.hidden=true;return}t=setTimeout(function(){fetch(api+(api.indexOf("?")>-1?"&":"?")+"q="+encodeURIComponent(q)).then(function(x){return x.json()}).then(show).catch(function(){})},220)});i.addEventListener("keydown",function(ev){var a=r.querySelectorAll("a");if(r.hidden||!a.length)return;if(ev.key==="ArrowDown"||ev.key==="ArrowUp"){ev.preventDefault();k=(k+(ev.key==="ArrowDown"?1:-1)+a.length)%a.length;a.forEach(function(x,j){x.classList.toggle("on",j===k)})}else if(ev.key==="Enter"&&k>-1){ev.preventDefault();location.href=a[k].href}else if(ev.key==="Escape"){r.hidden=true}});document.addEventListener("click",function(ev){if(!f.contains(ev.target))r.hidden=true})})})();</script>';
		}
		return $html;
	}
}

return 'ShopGlut_Module_Ajax_Search';
