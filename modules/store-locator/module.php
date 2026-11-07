<?php
/**
 * Store Locator: [shopglut_stores] shows the shop's branches with address, phone, hours, a search box, an
 * OpenStreetMap map of the chosen store and a "Get directions" link. Stores are one line each in the
 * settings. The map is an iframe from openstreetmap.org (no map library is loaded).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Store_Locator class.
 */
class ShopGlut_Module_Store_Locator extends ShopGlut_Module {

	public function id() {
		return 'store-locator';
	}

	public function info() {
		return array(
			'title'       => __( 'Store Locator', 'shopglut' ),
			'description' => __( 'A list of your shops or pickup points with search, map, hours and directions.', 'shopglut' ),
			'icon'        => 'faStore',
			'category'    => 'engagement',
			'order'       => 125,
			'features'    => array(
				__( 'Stores with address, phone and hours', 'shopglut' ),
				__( 'Search by area or name', 'shopglut' ),
				__( 'OpenStreetMap map and directions link', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default = '' ) {
		return shopglut_get_module_setting( 'store-locator', $key, $default );
	}

	public function boot() {
		add_shortcode( 'shopglut_stores', array( $this, 'shortcode' ) );
	}

	/**
	 * Stores from the settings.
	 *
	 * @param string|null $text Lines (default: settings).
	 * @return array
	 */
	public function stores( $text = null ) {
		$text = null === $text ? (string) $this->opt( 'stores' ) : $text;
		$out  = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $line ) {
			$f = array_map( 'trim', explode( '|', $line ) );
			if ( '' === $f[0] ) {
				continue;
			}
			$lat = null;
			$lng = null;
			if ( isset( $f[4] ) && preg_match( '/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $f[4], $m ) && abs( $m[1] ) <= 90 && abs( $m[2] ) <= 180 ) {
				$lat = (float) $m[1];
				$lng = (float) $m[2];
			}
			$out[] = array( 'name' => $f[0], 'address' => $f[1] ?? '', 'phone' => $f[2] ?? '', 'hours' => $f[3] ?? '', 'lat' => $lat, 'lng' => $lng );
		}
		return $out;
	}

	/**
	 * Map embed URL for a point.
	 */
	public static function map_url( $lat, $lng ) {
		$d = 0.008;
		return 'https://www.openstreetmap.org/export/embed.html?bbox=' . rawurlencode( ( $lng - $d ) . ',' . ( $lat - $d ) . ',' . ( $lng + $d ) . ',' . ( $lat + $d ) ) . '&layer=mapnik&marker=' . rawurlencode( $lat . ',' . $lng );
	}

	/**
	 * Directions link.
	 */
	public static function directions( $s ) {
		$dest = null !== $s['lat'] ? $s['lat'] . ',' . $s['lng'] : $s['address'];
		return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $dest );
	}

	public function shortcode() {
		$stores = $this->stores();
		if ( ! $stores ) {
			return '';
		}
		$map   = $this->opt( 'show_map', 1 );
		$first = null;
		foreach ( $stores as $s ) {
			if ( null !== $s['lat'] ) {
				$first = $s;
				break;
			}
		}
		$h  = '<div class="sg-stores"><h3>' . esc_html( $this->opt( 'heading', __( 'Find a store', 'shopglut' ) ) ) . '</h3>';
		$h .= '<input type="search" class="sg-stores-q" placeholder="' . esc_attr__( 'Search by area or name…', 'shopglut' ) . '">';
		$h .= '<div class="sg-stores-grid' . ( $map && $first ? ' has-map' : '' ) . '"><ul class="sg-stores-list">';
		foreach ( $stores as $s ) {
			$data = null !== $s['lat'] ? ' data-map="' . esc_attr( self::map_url( $s['lat'], $s['lng'] ) ) . '"' : '';
			$h   .= '<li' . $data . '><strong>' . esc_html( $s['name'] ) . '</strong>';
			$h   .= $s['address'] ? '<br>' . esc_html( $s['address'] ) : '';
			$h   .= $s['phone'] ? '<br><a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $s['phone'] ) ) . '">' . esc_html( $s['phone'] ) . '</a>' : '';
			$h   .= $s['hours'] ? '<br><small>' . esc_html( $s['hours'] ) . '</small>' : '';
			$h   .= '<br><a href="' . esc_url( self::directions( $s ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Get directions', 'shopglut' ) . '</a></li>';
		}
		$h .= '</ul>';
		if ( $map && $first ) {
			$h .= '<iframe class="sg-stores-map" title="' . esc_attr__( 'Store map', 'shopglut' ) . '" loading="lazy" src="' . esc_url( self::map_url( $first['lat'], $first['lng'] ) ) . '"></iframe>';
		}
		$h .= '</div></div><style>.sg-stores-q{width:100%;max-width:420px;margin:0 0 12px}.sg-stores-grid.has-map{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.4fr);gap:16px}@media(max-width:760px){.sg-stores-grid.has-map{grid-template-columns:1fr}}.sg-stores-list{list-style:none;margin:0;padding:0;max-height:440px;overflow:auto}.sg-stores-list li{padding:12px;border:1px solid rgba(0,0,0,.1);border-radius:10px;margin:0 0 8px;cursor:pointer}.sg-stores-list li.on{border-color:currentColor}.sg-stores-map{width:100%;min-height:360px;border:0;border-radius:10px}</style>';
		$h .= '<script>document.querySelectorAll(".sg-stores").forEach(function(w){var q=w.querySelector(".sg-stores-q"),m=w.querySelector(".sg-stores-map");q.addEventListener("input",function(){var t=q.value.toLowerCase();w.querySelectorAll(".sg-stores-list li").forEach(function(li){li.hidden=t&&li.textContent.toLowerCase().indexOf(t)<0})});w.querySelectorAll(".sg-stores-list li").forEach(function(li){li.addEventListener("click",function(e){if(e.target.closest("a"))return;w.querySelectorAll(".sg-stores-list li").forEach(function(x){x.classList.toggle("on",x===li)});if(m&&li.dataset.map)m.src=li.dataset.map})})});</script>';
		return $h;
	}
}

return 'ShopGlut_Module_Store_Locator';
