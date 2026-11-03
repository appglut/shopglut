<?php
/**
 * Cart page template 3 (ported from CartPage Glut).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;


class ShopGlut_Cart_Template3_Markup {

	public function layout_render($template_data) {
		// Get settings for this layout
		$settings = $this->getLayoutSettings($template_data['layout_id'] ?? 0);

		// Check if WooCommerce is active
		if (!class_exists('WooCommerce')) {
			echo '<div class="shopglut-error">' . esc_html__('WooCommerce is required for this cart layout.', 'shopglut') . '</div>';
			return;
		}

		// Initialize WooCommerce session and cart if not in admin
		if (!is_admin() || wp_doing_ajax()) {
			// Initialize WooCommerce if needed
			if (!did_action('woocommerce_init')) {
				WC()->init();
			}

			// Initialize session and cart safely
			if (WC()->session === null && method_exists(WC(), 'initialize_session')) {
				WC()->initialize_session();
			}
			if (WC()->cart === null && method_exists(WC(), 'initialize_cart')) {
				WC()->initialize_cart();
			}
		}

		// Check if we're in admin area or cart is not available
		$is_admin_preview = ( is_admin() && ! wp_doing_ajax() ) || WC()->cart === null;

		if ($is_admin_preview) {
			// In admin preview mode, show demo content
			$cart_items = array();
			$cart_totals = array();
			$is_cart_empty = true;
		} else {
			// Get WooCommerce cart for frontend
			$cart = WC()->cart;
			$cart_items = $cart->get_cart();
			$cart_totals = $cart->get_totals();
			$is_cart_empty = $cart->is_empty();
		}



		if ( $is_admin_preview ) {
			echo '<div class="shopglut-cart template3" data-layout-id="' . esc_attr( $template_data['layout_id'] ?? 0 ) . '">';
			$this->render_demo_cart( $settings );
			echo '</div>';
			return;
		}
		$this->render_live( $cart, (int) ( $template_data['layout_id'] ?? 0 ), $settings );
	}

	/**
	 * Coupon form and applied coupons (shared by the cart script).
	 *
	 * @param WC_Cart $cart Cart.
	 * @return void
	 */
	private function render_coupon( $cart ) {
		if ( ! wc_coupons_enabled() ) {
			return;
		}
		?>
		<form class="coupon-form" id="shopglut-coupon-form">
			<div class="coupon-input-group">
				<input type="text" class="coupon-input" id="couponCode" name="coupon_code" placeholder="<?php esc_attr_e( 'Enter coupon code', 'shopglut' ); ?>">
				<button type="submit" class="apply-btn"><?php esc_html_e( 'Apply', 'shopglut' ); ?></button>
			</div>
			<div class="coupon-message" id="couponMessage"></div>
		</form>
		<?php if ( $cart->get_applied_coupons() ) : ?>
			<div class="applied-coupons">
				<?php foreach ( $cart->get_applied_coupons() as $code ) : ?>
					<span class="applied-coupon"><?php echo esc_html( $code ); ?> <button type="button" class="remove-coupon" data-coupon="<?php echo esc_attr( $code ); ?>" aria-label="<?php esc_attr_e( 'Remove coupon', 'shopglut' ); ?>">&times;</button></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Quantity control (shared by the cart script).
	 *
	 * @param string     $key      Cart item key.
	 * @param int        $quantity Quantity.
	 * @param WC_Product $product  Product.
	 * @param string[]   $cls      Template classes: wrapper, button, input.
	 * @return void
	 */
	private function render_qty( $key, $quantity, $product, $cls ) {
		list( $wrap, $btn, $input ) = $cls;
		$max = $product->get_max_purchase_quantity();
		if ( $product->is_sold_individually() ) {
			echo '<span class="' . esc_attr( $input ) . '">1</span>';
			return;
		}
		?>
		<div class="qty-control <?php echo esc_attr( $wrap ); ?>">
			<button type="button" class="qty-decrease <?php echo esc_attr( $btn ); ?>" data-cart-key="<?php echo esc_attr( $key ); ?>" aria-label="<?php esc_attr_e( 'Decrease quantity', 'shopglut' ); ?>">&minus;</button>
			<input type="number" class="qty-input <?php echo esc_attr( $input ); ?>" value="<?php echo esc_attr( $quantity ); ?>" min="1" max="<?php echo esc_attr( $max > 0 ? $max : 999 ); ?>" step="1" data-cart-key="<?php echo esc_attr( $key ); ?>" aria-label="<?php esc_attr_e( 'Quantity', 'shopglut' ); ?>">
			<button type="button" class="qty-increase <?php echo esc_attr( $btn ); ?>" data-cart-key="<?php echo esc_attr( $key ); ?>" aria-label="<?php esc_attr_e( 'Increase quantity', 'shopglut' ); ?>">+</button>
		</div>
		<?php
	}

	/**
	 * Live cart: clean technical table with a summary sidebar.
	 *
	 * @param WC_Cart $cart      Cart.
	 * @param int     $layout_id Layout ID.
	 * @param array   $settings  Settings.
	 * @return void
	 */
	private function render_live( $cart, $layout_id, $settings ) {
		$shop = $this->getContinueShoppingUrl( $settings );
		?>
		<div class="shopglut-cart template3" data-layout-id="<?php echo esc_attr( $layout_id ); ?>">
		<div class="cart-container clean-technical">
			<div class="technical-grid">
				<div class="technical-main">
					<div class="technical-header">
						<h1><?php esc_html_e( 'Shopping Cart', 'shopglut' ); ?></h1>
						<p class="header-description"><?php esc_html_e( 'Review your items before checkout', 'shopglut' ); ?></p>
					</div>

					<?php if ( $cart->is_empty() ) : ?>
						<div class="technical-cart technical-empty">
							<p><?php esc_html_e( 'Your cart is currently empty.', 'shopglut' ); ?></p>
							<a href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Return to shop', 'shopglut' ); ?></a>
						</div>
					<?php else : ?>
					<div class="technical-cart">
						<div class="cart-header-row">
							<span class="col-product"><?php esc_html_e( 'Product', 'shopglut' ); ?></span>
							<span class="col-details"><?php esc_html_e( 'Details', 'shopglut' ); ?></span>
							<span class="col-qty"><?php esc_html_e( 'Qty', 'shopglut' ); ?></span>
							<span class="col-price"><?php esc_html_e( 'Price', 'shopglut' ); ?></span>
							<span class="col-action"></span>
						</div>
						<div class="cart-items-rows">
							<?php
							foreach ( $cart->get_cart() as $key => $item ) :
								$product = $item['data'];
								if ( ! $product || ! $product->exists() || $item['quantity'] < 1 ) {
									continue;
								}
								$link = $product->is_visible() ? $product->get_permalink( $item ) : '';
								?>
							<div class="cart-row-item cart-item" data-cart-item-key="<?php echo esc_attr( $key ); ?>">
								<div class="row-product">
									<div class="product-thumb-small"><?php echo wp_kses_post( $product->get_image( 'woocommerce_gallery_thumbnail' ) ); ?></div>
									<?php if ( $product->get_sku() ) : ?>
										<span class="product-sku"><?php echo esc_html__( 'SKU:', 'shopglut' ) . ' ' . esc_html( $product->get_sku() ); ?></span>
									<?php endif; ?>
								</div>
								<div class="row-details">
									<h3 class="product-title">
										<?php if ( $link ) : ?>
											<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
										<?php else : ?>
											<?php echo esc_html( $product->get_name() ); ?>
										<?php endif; ?>
									</h3>
									<span class="product-attr"><?php echo wp_kses_post( wc_get_formatted_cart_item_data( $item, true ) ); ?> <?php echo wp_kses_post( $cart->get_product_price( $product ) ); ?></span>
								</div>
								<div class="row-qty"><?php $this->render_qty( $key, $item['quantity'], $product, array( 'qty-compact', 'qty-btn-sm', 'qty-input-sm' ) ); ?></div>
								<div class="row-price"><span class="price-current"><?php echo wp_kses_post( $cart->get_product_subtotal( $product, $item['quantity'] ) ); ?></span></div>
								<div class="row-action">
									<button type="button" class="btn-remove-sm remove-btn" data-cart-key="<?php echo esc_attr( $key ); ?>" title="<?php esc_attr_e( 'Remove item', 'shopglut' ); ?>">
										<svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M1 1L13 13M1 13L13 1" stroke="currentColor" stroke-width="1.5"/></svg>
									</button>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
					</div>
					<a class="technical-continue" href="<?php echo esc_url( $shop ); ?>">&larr; <?php esc_html_e( 'Continue Shopping', 'shopglut' ); ?></a>
					<?php endif; ?>
				</div>

				<?php if ( ! $cart->is_empty() ) : ?>
				<aside class="technical-sidebar">
					<div class="summary-box">
						<div class="summary-header">
							<h2><?php esc_html_e( 'Order Summary', 'shopglut' ); ?></h2>
							<span class="summary-count">
								<?php
								/* translators: %d: number of items */
								echo esc_html( sprintf( _n( '%d item', '%d items', $cart->get_cart_contents_count(), 'shopglut' ), $cart->get_cart_contents_count() ) );
								?>
							</span>
						</div>
						<div class="summary-sections"><div class="summary-section">
							<div class="summary-line"><span class="line-label"><?php esc_html_e( 'Subtotal', 'shopglut' ); ?></span><span class="line-value"><?php echo wp_kses_post( $cart->get_cart_subtotal() ); ?></span></div>
							<?php if ( $cart->get_discount_total() > 0 ) : ?>
								<div class="summary-line"><span class="line-label"><?php esc_html_e( 'Discount', 'shopglut' ); ?></span><span class="line-value">-<?php echo wp_kses_post( wc_price( $cart->get_discount_total() ) ); ?></span></div>
							<?php endif; ?>
							<?php if ( $cart->needs_shipping() ) : ?>
								<div class="summary-line"><span class="line-label"><?php esc_html_e( 'Shipping', 'shopglut' ); ?></span><span class="line-value"><?php echo $cart->show_shipping() ? wp_kses_post( wc_price( $cart->get_shipping_total() ) ) : esc_html__( 'Calculated at checkout', 'shopglut' ); ?></span></div>
							<?php endif; ?>
						</div></div>
						<div class="summary-total-section">
							<div class="summary-line total-line"><span class="total-label"><?php esc_html_e( 'Total', 'shopglut' ); ?></span><span class="total-value"><?php echo wp_kses_post( $cart->get_total() ); ?></span></div>
						</div>
					</div>
					<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="btn-checkout-primary"><?php esc_html_e( 'Proceed to Checkout', 'shopglut' ); ?></a>
					<div class="coupon-box">
						<h3><?php esc_html_e( 'Add Coupon Code', 'shopglut' ); ?></h3>
						<?php $this->render_coupon( $cart ); ?>
					</div>
				</aside>
				<?php endif; ?>
			</div>
		</div>
		</div>
		<?php
	}


	/**
	 * Render demo cart for preview when cart is empty
	 */
	private function render_demo_cart($settings) {
		// Get the placeholder image URL
		$placeholder_url = wc_placeholder_img_src();

		?>
		<div class="cart-container clean-technical">
  <div class="technical-grid">
    <div class="technical-main">
      <div class="technical-header">
        <nav class="breadcrumb">
          <a href="#">Home</a>
          <span class="separator">/</span>
          <span>Cart</span>
        </nav>
        <h1>Shopping Cart</h1>
        <p class="header-description">Review your items before checkout</p>
      </div>

      <div class="technical-cart">
        <div class="cart-header-row">
          <span class="col-product">Product</span>
          <span class="col-details">Details</span>
          <span class="col-qty">Qty</span>
          <span class="col-price">Price</span>
          <span class="col-action"></span>
        </div>

        <div class="cart-items-rows">
          <!-- Item 1 -->
          <div class="cart-row-item">
            <div class="row-product">
              <div class="product-thumb-small">
                <div class="thumb-placeholder" style="background: #e8eef5;">IMG</div>
              </div>
              <span class="product-sku">SKU: NT-2024-01</span>
            </div>
            <div class="row-details">
              <h3 class="product-title">Noise-Cancelling Headphones</h3>
              <span class="product-attr">Color: Silver · Warranty: 2 Years</span>
            </div>
            <div class="row-qty">
              <div class="qty-compact">
                <button class="qty-btn-sm">−</button>
                <input type="number" value="1" class="qty-input-sm" readonly>
                <button class="qty-btn-sm">+</button>
              </div>
            </div>
            <div class="row-price">
              <span class="price-current">$279.00</span>
            </div>
            <div class="row-action">
              <button class="btn-remove-sm" title="Remove item">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                  <path d="M1 1L13 13M1 13L13 1" stroke="currentColor" stroke-width="1.5"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Item 2 -->
          <div class="cart-row-item">
            <div class="row-product">
              <div class="product-thumb-small">
                <div class="thumb-placeholder" style="background: #e5eaf0;">IMG</div>
              </div>
              <span class="product-sku">SKU: CB-2024-02</span>
            </div>
            <div class="row-details">
              <h3 class="product-title">USB-C Cable Braided</h3>
              <span class="product-attr">Length: 2m · Color: Black</span>
            </div>
            <div class="row-qty">
              <div class="qty-compact">
                <button class="qty-btn-sm">−</button>
                <input type="number" value="3" class="qty-input-sm" readonly>
                <button class="qty-btn-sm">+</button>
              </div>
            </div>
            <div class="row-price">
              <span class="price-current">$36.00</span>
            </div>
            <div class="row-action">
              <button class="btn-remove-sm" title="Remove item">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                  <path d="M1 1L13 13M1 13L13 1" stroke="currentColor" stroke-width="1.5"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Item 3 -->
          <div class="cart-row-item">
            <div class="row-product">
              <div class="product-thumb-small">
                <div class="thumb-placeholder" style="background: #ebf0f5;">IMG</div>
              </div>
              <span class="product-sku">SKU: SC-2024-03</span>
            </div>
            <div class="row-details">
              <h3 class="product-title">Screen Protector Premium</h3>
              <span class="product-attr">Size: 6.7" · Material: Tempered Glass</span>
            </div>
            <div class="row-qty">
              <div class="qty-compact">
                <button class="qty-btn-sm">−</button>
                <input type="number" value="1" class="qty-input-sm" readonly>
                <button class="qty-btn-sm">+</button>
              </div>
            </div>
            <div class="row-price">
              <span class="price-current">$24.00</span>
            </div>
            <div class="row-action">
              <button class="btn-remove-sm" title="Remove item">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                  <path d="M1 1L13 13M1 13L13 1" stroke="currentColor" stroke-width="1.5"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="technical-footer">
        <a href="#" class="link-back">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M11 7H3M3 7L6 4M3 7L6 10" stroke="currentColor" stroke-width="1.5"/>
          </svg>
          Continue Shopping
        </a>
        <button class="btn-update">Update Cart</button>
      </div>
    </div>

    <aside class="technical-sidebar">
      <div class="summary-box">
        <div class="summary-header">
          <h2>Order Summary</h2>
          <span class="summary-count">4 items</span>
        </div>

        <div class="summary-sections">
          <div class="summary-section">
            <div class="summary-line">
              <span class="line-label">Subtotal</span>
              <span class="line-value">$339.00</span>
            </div>
            <div class="summary-line">
              <span class="line-label">Shipping</span>
              <div class="shipping-select">
                <select>
                  <option>Standard (Free)</option>
                  <option>Express ($12.00)</option>
                  <option>Overnight ($25.00)</option>
                </select>
              </div>
            </div>
          </div>

          <div class="summary-total-section">
            <div class="summary-line total-line">
              <span class="total-label">Total</span>
              <span class="total-value">$339.00</span>
            </div>
          </div>
        </div>

        <button class="btn-checkout-primary">
          Proceed to Checkout
        </button>

        <div class="checkout-features">
          <div class="feature-item">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path d="M8 1L1 4V8C1 11.5 3.5 14.5 8 15C12.5 14.5 15 11.5 15 8V4L8 1Z" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <span>Secure checkout</span>
          </div>
          <div class="feature-item">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path d="M8 1C4.13 1 1 4.13 1 8C1 11.87 4.13 15 8 15C11.87 15 15 11.87 15 8C15 4.13 11.87 1 8 1Z" stroke="currentColor" stroke-width="1.5"/>
              <path d="M8 4V8L11 10" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <span>30-day returns</span>
          </div>
        </div>
      </div>

      <div class="coupon-box">
        <h3>Add Coupon Code</h3>
        <div class="coupon-input-group">
          <input type="text" placeholder="Enter code">
          <button type="button">Apply</button>
        </div>
      </div>
    </aside>
  </div>
</div>
		<?php
	}

	/**
	 * Get layout settings from database
	 */
	private function getLayoutSettings($layout_id) {
		if (!$layout_id) {
			return $this->getDefaultSettings();
		}

		$layout_data = ShopGlut_Layouts::get( (int) $layout_id );

		if ( $layout_data && ! empty( $layout_data['settings'] ) ) {
			$settings = $layout_data['settings'];

			// Handle different possible data structures
			if (isset($settings['shopg_cartpage_settings_template1'])) {
				$template_data = $settings['shopg_cartpage_settings_template1'];

				// Check for double nesting (key inside itself)
				if (isset($template_data['shopg_cartpage_settings_template1']['cart-page-settings'])) {
					return $this->flattenSettings($template_data['shopg_cartpage_settings_template1']['cart-page-settings']);
				}

				// Check for single nesting (direct cart-page-settings)
				if (isset($template_data['cart-page-settings'])) {
					return $this->flattenSettings($template_data['cart-page-settings']);
				}
			}

			// Direct check for single level (compatibility)
			if (isset($settings['shopg_cartpage_settings_template1']['cart-page-settings'])) {
				return $this->flattenSettings($settings['shopg_cartpage_settings_template1']['cart-page-settings']);
			}
		}

		return $this->getDefaultSettings();
	}

	/**
	 * Flatten nested settings structure to simple key-value pairs
	 * Handles deeply nested fieldsets by recursively extracting all settings
	 */
	private function flattenSettings($nested_settings) {
		$flat_settings = array();

		foreach ($nested_settings as $group_key => $group_values) {
			if (is_array($group_values)) {
				foreach ($group_values as $setting_key => $setting_value) {
					// If the value is an array, check if it's a nested fieldset or a slider field
					if (is_array($setting_value)) {
						// Check if it's a slider field with self-referencing key
						if (isset($setting_value[$setting_key])) {
							$flat_settings[$setting_key] = $setting_value[$setting_key];
						} elseif (isset($setting_value['value']) && isset($setting_value['unit'])) {
							// Handle slider fields with value/unit structure
							$flat_settings[$setting_key] = $setting_value;
						} elseif ($this->isPreservableArray($setting_value)) {
							// Keep arrays with 'unit' key or only numeric/string values (like padding arrays)
							$flat_settings[$setting_key] = $setting_value;
						} else {
							// Recursively flatten nested fieldsets
							$flattened = $this->flattenSettings(array($setting_key => $setting_value));
							$flat_settings = array_merge($flat_settings, $flattened);
						}
					} else {
						$flat_settings[$setting_key] = $setting_value;
					}
				}
			}
		}

		return array_merge($this->getDefaultSettings(), $flat_settings);
	}

	/**
	 * Check if an array should be preserved as-is (not flattened further)
	 * Arrays like padding arrays, margin arrays, font size arrays with unit should be preserved
	 */
	private function isPreservableArray($array) {
		// Check if array has a 'unit' key - these are dimension/size arrays
		if (isset($array['unit'])) {
			return true;
		}

		// Check if array has 'value' and 'unit' keys - slider field structure
		if (isset($array['value']) && isset($array['unit'])) {
			return true;
		}

		// Check if all keys are known directional keys (for padding/margin/size arrays)
		$known_keys = array('top', 'right', 'bottom', 'left', 'width', 'height');
		$all_known = true;
		foreach (array_keys($array) as $key) {
			if (!in_array($key, $known_keys, true)) {
				$all_known = false;
				break;
			}
		}
		if ($all_known && count($array) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Get default settings values
	 */
	private function getDefaultSettings() {
		return array(
			// Table Header Settings
			'show_table_header' => true,
			'header_background_color' => '#f3f4f6',
			'header_text_color' => '#374151',
			'header_font_weight' => '600',
			'header_padding' => array('top' => '16', 'right' => '12', 'bottom' => '16', 'left' => '12', 'unit' => 'px'),

			// Product Image Settings
			'product_image_size' => array('width' => 60, 'height' => 60, 'unit' => 'px'),
			'image_background_color' => '#f9fafb',
			'image_border_radius' => 8,
			'image_border_color' => '#e5e7eb',
			'image_border_width' => 1,

			// Product Title Settings
			'product_title_color' => '#111827',
			'product_title_font_size' => 16,
			'product_title_font_weight' => '600',
			'show_product_link' => true,

			// Product Meta Settings
			'show_product_meta' => true,
			'product_meta_color' => '#6b7280',
			'product_meta_font_size' => 14,
			'show_product_badges' => true,
			'badge_background_color' => '#3b82f6',
			'badge_text_color' => '#ffffff',

			// Quantity Settings
			'quantity_button_color' => '#f3f4f6',
			'quantity_button_text_color' => '#374151',
			'quantity_button_hover_color' => '#e5e7eb',
			'quantity_input_background' => '#ffffff',
			'quantity_input_border' => '#d1d5db',
			'quantity_control_border_radius' => 6,

			// Pricing Settings
			'price_color' => '#111827',
			'price_font_size' => 16,
			'price_font_weight' => '600',
			'total_price_highlight' => true,
			'total_price_color' => '#059669',

			// Table Styling
			'table_background_color' => '#ffffff',
			'table_border_color' => '#e5e7eb',
			'table_border_width' => 1,
			'table_border_radius' => 8,
			'row_padding' => array('top' => '16', 'right' => '12', 'bottom' => '16', 'left' => '12', 'unit' => 'px'),
			'row_hover_effect' => true,
			'row_hover_color' => '#f8fafc',

			// Summary Section Settings
			'show_summary_section' => true,
			'summary_background_color' => '#f9fafb',
			'summary_border_color' => '#e5e7eb',
			'summary_border_radius' => 8,
			'summary_padding' => array('top' => '24', 'right' => '20', 'bottom' => '24', 'left' => '20', 'unit' => 'px'),

			// Summary Header
			'show_summary_header' => true,
			'summary_title_text' => 'Order Summary',
			'summary_title_color' => '#111827',
			'summary_title_font_size' => 20,
			'show_summary_icon' => true,
			'summary_icon_color' => '#3b82f6',

			// Summary Rows
			'show_subtotal' => true,
			'show_shipping' => true,
			'show_tax' => true,
			'show_discount_row' => true,
			'row_label_color' => '#6b7280',
			'row_value_color' => '#111827',
			'row_font_size' => 14,
			'row_spacing' => 12,

			// Total Row
			'total_label_color' => '#111827',
			'total_value_color' => '#059669',
			'total_font_size' => 18,
			'total_font_weight' => '700',
			'total_row_separator' => true,
			'total_separator_color' => '#e5e7eb',

			// Checkout Button
			'checkout_button_text' => 'Secure Checkout',
			'checkout_button_background' => '#059669',
			'checkout_button_text_color' => '#ffffff',
			'checkout_button_hover_background' => '#047857',
			'checkout_button_font_size' => 16,
			'checkout_button_padding' => array('top' => '16', 'right' => '24', 'bottom' => '16', 'left' => '24', 'unit' => 'px'),
			'checkout_button_border_radius' => 8,
			'show_checkout_icon' => true,

			// Security Badges
			'show_security_badges' => true,
			'security_badges_layout' => 'horizontal',
			'security_badge_spacing' => 8,
			'show_ssl_badge' => true,
			'ssl_badge_text' => 'SSL Secured',
			'ssl_badge_text_color' => '#6b7280',
			'ssl_badge_icon' => 'fas fa-shield-alt',
			'ssl_badge_icon_color' => '#059669',
			'ssl_badge_font_size' => 12,
			'show_payment_badge' => true,
			'payment_badge_text' => 'Safe Payment',
			'payment_badge_text_color' => '#6b7280',
			'payment_badge_icon' => 'fas fa-credit-card',
			'payment_badge_icon_color' => '#3b82f6',
			'payment_badge_font_size' => 12,
			'show_return_badge' => true,
			'return_badge_text' => '30-Day Return',
			'return_badge_text_color' => '#6b7280',
			'return_badge_icon' => 'fas fa-undo',
			'return_badge_icon_color' => '#f59e0b',
			'return_badge_font_size' => 12,

			// Discount Section
			'show_discount_section' => true,
			'discount_section_background' => '#ffffff',
			'discount_section_border' => '#e5e7eb',
			'discount_section_padding' => array('top' => '20', 'right' => '20', 'bottom' => '20', 'left' => '20', 'unit' => 'px'),
			'show_discount_title' => true,
			'discount_title_text' => 'Discount Code',
			'discount_title_color' => '#111827',
			'discount_title_font_size' => 18,
			'show_discount_icon' => true,
			'discount_icon_color' => '#3b82f6',
			'coupon_input_placeholder' => 'Enter coupon code',
			'coupon_input_background' => '#ffffff',
			'coupon_input_border' => '#d1d5db',
			'coupon_input_text_color' => '#374151',
			'coupon_input_border_radius' => 6,
			'coupon_input_padding' => array('top' => '12', 'right' => '16', 'bottom' => '12', 'left' => '16', 'unit' => 'px'),
			'apply_button_text' => 'Apply',
			'apply_button_background' => '#3b82f6',
			'apply_button_text_color' => '#ffffff',
			'apply_button_hover_background' => '#2563eb',
			'apply_button_border_radius' => 6,
			'apply_button_padding' => array('top' => '12', 'right' => '20', 'bottom' => '12', 'left' => '20', 'unit' => 'px'),
			'show_coupon_messages' => true,
			'success_message_color' => '#059669',
			'error_message_color' => '#dc2626',
			'message_font_size' => 14,

			// Continue Shopping
			'show_continue_shopping' => true,
			'continue_shopping_text' => 'Continue Shopping',
			'continue_shopping_url' => 'shop',
			'custom_continue_url' => '',
			'continue_link_color' => '#3b82f6',
			'continue_link_hover_color' => '#2563eb',
			'continue_link_font_size' => 14,
			'show_continue_icon' => true,
		);
	}

	/**
 	 * Get continue shopping URL based on settings
	 */
	private function getContinueShoppingUrl($settings) {
		$url_option = $settings['continue_shopping_url'] ?? 'shop';

		switch ($url_option) {
			case 'shop':
				return wc_get_page_permalink('shop');
			case 'home':
				return home_url();
			case 'previous':
				return isset($_SERVER['HTTP_REFERER']) ? esc_url($_SERVER['HTTP_REFERER']) : wc_get_page_permalink('shop');
			case 'custom':
				return !empty($settings['custom_continue_url']) ? esc_url($settings['custom_continue_url']) : wc_get_page_permalink('shop');
			default:
				return wc_get_page_permalink('shop');
		}
	}
}