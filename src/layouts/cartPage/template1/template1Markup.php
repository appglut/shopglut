<?php
namespace Shopglut\layouts\cartPage\template1;

if (!defined('ABSPATH')) {
	exit;
}

class template1Markup {

	public function layout_render($template_data) {
		// Get settings for this layout
		$settings = $this->getLayoutSettings($template_data['layout_id'] ?? 0);

		// Check if WooCommerce is active
		if (!class_exists('WooCommerce')) {
			echo '<div class="shopglut-error">' . esc_html__('WooCommerce is required for this cart layout.', 'shopglut') . '</div>';
			return;
		}

		// Initialize WooCommerce session and cart if not in admin
		if (!is_admin()) {
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
		$is_admin_preview = is_admin() || WC()->cart === null;

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


		?>
		<div class="shopglut-cart template1" data-layout-id="<?php echo esc_attr($template_data['layout_id'] ?? 0); ?>">
			<div class="cart-container">
				<?php if ($is_admin_preview): ?>
					<!-- Admin Preview Mode -->
					<div class="demo-content">
						<?php $this->render_demo_cart($settings); ?>
					</div>
				<?php elseif ($is_cart_empty): ?>
					<!-- Empty Cart State -->
					<div class="empty-cart-state">
						<div class="empty-cart-icon">
							<i class="fas fa-shopping-cart"></i>
						</div>
						<h2><?php echo esc_html__('Your cart is empty', 'shopglut'); ?></h2>
						<p><?php echo esc_html__('Looks like you haven\'t added anything to your cart yet.', 'shopglut'); ?></p>
						<a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="continue-shopping-btn">
							<i class="fas fa-arrow-left"></i>
							<?php echo esc_html__('Continue Shopping', 'shopglut'); ?>
						</a>
					</div>

				<?php else: ?>
					<!-- Real Cart Content -->
					<div class="cart-content">
						<?php $this->render_cart_table($cart_items, $settings); ?>
						<?php $this->render_cart_footer($cart, $cart_totals, $settings); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render cart table with real cart items
	 */
	private function render_cart_table($cart_items, $settings) {
		// Extract inline style values for price cells
		$price_color = $settings['price_color'] ?? '#111827';
		$price_font_size = $settings['price_font_size'] ?? 16;
		$price_font_weight = $settings['price_font_weight'] ?? '600';
		$total_price_color = $settings['total_price_highlight'] ? ($settings['total_price_color'] ?? '#059669') : $price_color;

		?>
		<div class="cart-table-container">
			<table class="cart-table">
				<?php if ($settings['show_table_header']): ?>
				<thead>
					<tr>
						<th><?php echo esc_html__('Product', 'shopglut'); ?></th>
						<th><?php echo esc_html__('Price', 'shopglut'); ?></th>
						<th><?php echo esc_html__('Quantity', 'shopglut'); ?></th>
						<th><?php echo esc_html__('Total', 'shopglut'); ?></th>
						<th></th>
					</tr>
				</thead>
				<?php endif; ?>
				<tbody>
					<?php foreach ($cart_items as $cart_item_key => $cart_item):
						$product = $cart_item['data'];
						$product_id = $cart_item['product_id'];
						$variation_id = $cart_item['variation_id'];
						$quantity = $cart_item['quantity'];
						$line_total = $cart_item['line_total'];
						$line_subtotal = $cart_item['line_subtotal'];
						?>
						<tr class="cart-item" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
							<td>
								<div class="product-cell">
									<div class="product-image">
										<?php
										$thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $product->get_image(), $cart_item, $cart_item_key);
										echo wp_kses_post($thumbnail);
										?>
									</div>
									<div class="product-details">
										<?php if ($settings['show_product_link']): ?>
											<a href="<?php echo esc_url($product->get_permalink($cart_item)); ?>" class="product-name" style="color: <?php echo esc_attr($settings['product_title_color'] ?? '#111827'); ?>; font-size: <?php echo esc_attr($settings['product_title_font_size'] ?? 16); ?>px; font-weight: <?php echo esc_attr($settings['product_title_font_weight'] ?? '600'); ?>;">
												<?php echo wp_kses_post($product->get_name()); ?>
											</a>
										<?php else: ?>
											<div class="product-name" style="color: <?php echo esc_attr($settings['product_title_color'] ?? '#111827'); ?>; font-size: <?php echo esc_attr($settings['product_title_font_size'] ?? 16); ?>px; font-weight: <?php echo esc_attr($settings['product_title_font_weight'] ?? '600'); ?>;"><?php echo wp_kses_post($product->get_name()); ?></div>
										<?php endif; ?>

										<?php if ($settings['show_product_meta']): ?>
											<div class="product-meta" style="color: <?php echo esc_attr($settings['product_meta_color'] ?? '#6b7280'); ?>; font-size: <?php echo esc_attr($settings['product_meta_font_size'] ?? 14); ?>px;">
												<?php
												// Display variation attributes
												if ($variation_id && $variation_data = wc_get_formatted_variation($cart_item['variation'], true)) {
													echo '<div class="variation-data">' . wp_kses_post($variation_data) . '</div>';
												}

												// Display product SKU
												if ($product->get_sku()) {
													echo '<div class="product-sku">' . esc_html__('SKU:', 'shopglut') . ' ' . esc_html($product->get_sku()) . '</div>';
												}

												// Custom product badges
												if ($settings['show_product_badges']):
													if ($product->is_on_sale()) {
														echo '<span class="product-badge sale-badge" style="background: ' . esc_attr($settings['badge_background_color'] ?? '#3b82f6') . '; color: ' . esc_attr($settings['badge_text_color'] ?? '#ffffff') . ';">' . esc_html__('Sale', 'shopglut') . '</span>';
													}
													if ($product->is_featured()) {
														echo '<span class="product-badge featured-badge" style="background: ' . esc_attr($settings['badge_background_color'] ?? '#3b82f6') . '; color: ' . esc_attr($settings['badge_text_color'] ?? '#ffffff') . ';">' . esc_html__('Featured', 'shopglut') . '</span>';
													}
												endif;
												?>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</td>
							<td class="price-cell" style="color: <?php echo esc_attr($price_color); ?>; font-size: <?php echo esc_attr($price_font_size); ?>px; font-weight: <?php echo esc_attr($price_font_weight); ?>;">
								<?php echo wp_kses_post(wc_price($product->get_price())); ?>
							</td>
							<td class="quantity-cell">
								<div class="qty-control">
									<button class="qty-btn qty-decrease" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">−</button>
									<input type="number"
										   value="<?php echo esc_attr($quantity); ?>"
										   min="1"
										   max="<?php echo esc_attr($product->get_max_purchase_quantity() > 0 ? $product->get_max_purchase_quantity() : 999); ?>"
										   class="qty-input"
										   data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
										   step="1">
									<button class="qty-btn qty-increase" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">+</button>
								</div>
							</td>
							<td class="price-cell item-total" style="color: <?php echo esc_attr($total_price_color); ?>; font-size: <?php echo esc_attr($price_font_size); ?>px; font-weight: <?php echo esc_attr($price_font_weight); ?>;">
								<?php echo wp_kses_post(wc_price($line_total)); ?>
							</td>
							<td>
								<button class="remove-btn" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" title="<?php echo esc_attr__('Remove item', 'shopglut'); ?>">
									<i class="fas fa-times"></i>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render cart footer with totals and checkout
	 */
	private function render_cart_footer($cart, $cart_totals, $settings) {
		// Safety check for cart availability
		if (!$cart) {
			return;
		}

		// Extract inline style values for discount section
		$discount_section_background = $settings['discount_section_background'] ?? '#ffffff';
		$discount_section_border = $settings['discount_section_border'] ?? '#e5e7eb';
		$discount_section_padding = $settings['discount_section_padding'] ?? array('top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20, 'unit' => 'px');
		$discount_title_font_size = $settings['discount_title_font_size'] ?? 18;
		$discount_title_color = $settings['discount_title_color'] ?? '#111827';
		$discount_icon_color = $settings['discount_icon_color'] ?? '#3b82f6';
		$coupon_input_background = $settings['coupon_input_background'] ?? '#ffffff';
		$coupon_input_border = $settings['coupon_input_border'] ?? '#d1d5db';
		$coupon_input_text_color = $settings['coupon_input_text_color'] ?? '#374151';
		$coupon_input_border_radius = $settings['coupon_input_border_radius'] ?? 6;
		$coupon_input_padding = $settings['coupon_input_padding'] ?? array('top' => 12, 'right' => 16, 'bottom' => 12, 'left' => 16, 'unit' => 'px');
		$apply_button_background = $settings['apply_button_background'] ?? '#3b82f6';
		$apply_button_text_color = $settings['apply_button_text_color'] ?? '#ffffff';
		$apply_button_border_radius = $settings['apply_button_border_radius'] ?? 6;
		$apply_button_padding = $settings['apply_button_padding'] ?? array('top' => 12, 'right' => 20, 'bottom' => 12, 'left' => 20, 'unit' => 'px');

		// Extract inline style values for summary section
		$summary_background_color = $settings['summary_background_color'] ?? '#f9fafb';
		$summary_border_color = $settings['summary_border_color'] ?? '#e5e7eb';
		$summary_border_radius = $settings['summary_border_radius'] ?? 8;
		$summary_padding = $settings['summary_padding'] ?? array('top' => 24, 'right' => 20, 'bottom' => 24, 'left' => 20, 'unit' => 'px');
		$summary_title_font_size = $settings['summary_title_font_size'] ?? 20;
		$summary_title_color = $settings['summary_title_color'] ?? '#111827';
		$summary_icon_color = $settings['summary_icon_color'] ?? '#3b82f6';
		$row_label_color = $settings['row_label_color'] ?? '#6b7280';
		$row_value_color = $settings['row_value_color'] ?? '#111827';
		$row_font_size = $settings['row_font_size'] ?? 14;
		$row_spacing = $settings['row_spacing'] ?? 12;
		$total_label_color = $settings['total_label_color'] ?? '#111827';
		$total_value_color = $settings['total_value_color'] ?? '#059669';
		$total_font_size = $settings['total_font_size'] ?? 18;
		$total_font_weight = $settings['total_font_weight'] ?? '700';
		$total_row_separator = $settings['total_row_separator'] ?? true;
		$total_separator_color = $settings['total_separator_color'] ?? '#e5e7eb';
		$checkout_button_background = $settings['checkout_button_background'] ?? '#059669';
		$checkout_button_text_color = $settings['checkout_button_text_color'] ?? '#ffffff';
		$checkout_button_hover_background = $settings['checkout_button_hover_background'] ?? '#047857';
		$checkout_button_font_size = $settings['checkout_button_font_size'] ?? 16;
		$checkout_button_border_radius = $settings['checkout_button_border_radius'] ?? 8;
		$checkout_button_padding = $settings['checkout_button_padding'] ?? array('top' => 16, 'right' => 24, 'bottom' => 16, 'left' => 24, 'unit' => 'px');
		$security_badges_layout = $settings['security_badges_layout'] ?? 'horizontal';
		$security_badge_spacing = $settings['security_badge_spacing'] ?? 8;
		$ssl_badge_font_size = $settings['ssl_badge_font_size'] ?? 12;
		$ssl_badge_text_color = $settings['ssl_badge_text_color'] ?? '#6b7280';
		$ssl_badge_icon_color = $settings['ssl_badge_icon_color'] ?? '#059669';
		$payment_badge_font_size = $settings['payment_badge_font_size'] ?? 12;
		$payment_badge_text_color = $settings['payment_badge_text_color'] ?? '#6b7280';
		$payment_badge_icon_color = $settings['payment_badge_icon_color'] ?? '#3b82f6';
		$return_badge_font_size = $settings['return_badge_font_size'] ?? 12;
		$return_badge_text_color = $settings['return_badge_text_color'] ?? '#6b7280';
		$return_badge_icon_color = $settings['return_badge_icon_color'] ?? '#f59e0b';

		// Extract inline style values for continue shopping link
		$continue_link_color = $settings['continue_link_color'] ?? '#3b82f6';
		$continue_link_font_size = $settings['continue_link_font_size'] ?? 14;

		?>
		<div class="cart-footer">
			<div class="footer-grid">
				<!-- Coupon Section -->
				<?php if ($settings['show_discount_section'] ?? true): ?>
				<div class="footer-section" style="background: <?php echo esc_attr($discount_section_background); ?>; border: 1px solid <?php echo esc_attr($discount_section_border); ?>; padding: <?php echo esc_attr($discount_section_padding['top'] . $discount_section_padding['unit']); ?> <?php echo esc_attr($discount_section_padding['right'] . $discount_section_padding['unit']); ?> <?php echo esc_attr($discount_section_padding['bottom'] . $discount_section_padding['unit']); ?> <?php echo esc_attr($discount_section_padding['left'] . $discount_section_padding['unit']); ?>; border-radius: 8px;">
					<?php if ($settings['show_discount_title'] ?? true): ?>
					<h3 class="section-title" style="font-size: <?php echo esc_attr($discount_title_font_size); ?>px; color: <?php echo esc_attr($discount_title_color); ?>;">
						<?php if ($settings['show_discount_icon'] ?? true): ?>
						<i class="fas fa-tag" style="color: <?php echo esc_attr($discount_icon_color); ?>;"></i>
						<?php endif; ?>
						<?php echo esc_html($settings['discount_title_text'] ?? __('Discount Code', 'shopglut')); ?>
					</h3>
					<?php endif; ?>
					<form class="coupon-form" id="shopglut-coupon-form">
						<div class="input-group" style="border-color: <?php echo esc_attr($coupon_input_border); ?>; border-radius: <?php echo esc_attr($coupon_input_border_radius); ?>px;">
							<input type="text"
								   placeholder="<?php echo esc_attr($settings['coupon_input_placeholder'] ?? __('Enter coupon code', 'shopglut')); ?>"
								   class="coupon-input"
								   id="couponCode"
								   name="coupon_code"
								   style="background: <?php echo esc_attr($coupon_input_background); ?>; color: <?php echo esc_attr($coupon_input_text_color); ?>; padding: <?php echo esc_attr($coupon_input_padding['top'] . $coupon_input_padding['unit']); ?> <?php echo esc_attr($coupon_input_padding['right'] . $coupon_input_padding['unit']); ?> <?php echo esc_attr($coupon_input_padding['bottom'] . $coupon_input_padding['unit']); ?> <?php echo esc_attr($coupon_input_padding['left'] . $coupon_input_padding['unit']); ?>;">
							<button type="submit" class="apply-btn" style="background: <?php echo esc_attr($apply_button_background); ?>; color: <?php echo esc_attr($apply_button_text_color); ?>; border-radius: <?php echo esc_attr($apply_button_border_radius); ?>px; padding: <?php echo esc_attr($apply_button_padding['top'] . $apply_button_padding['unit']); ?> <?php echo esc_attr($apply_button_padding['right'] . $apply_button_padding['unit']); ?> <?php echo esc_attr($apply_button_padding['bottom'] . $apply_button_padding['unit']); ?> <?php echo esc_attr($apply_button_padding['left'] . $apply_button_padding['unit']); ?>;">
								<?php echo esc_html($settings['apply_button_text'] ?? __('Apply', 'shopglut')); ?>
							</button>
						</div>
						<?php if ($settings['show_coupon_messages'] ?? true): ?>
						<div class="coupon-message" id="couponMessage"></div>
						<?php endif; ?>
					</form>

					<!-- Applied Coupons -->
					<?php if ($applied_coupons = $cart->get_applied_coupons()): ?>
					<div class="applied-coupons">
						<h4><?php echo esc_html__('Applied Coupons:', 'shopglut'); ?></h4>
						<?php foreach ($applied_coupons as $coupon_code): ?>
							<div class="applied-coupon">
								<span class="coupon-code"><?php echo esc_html($coupon_code); ?></span>
								<button class="remove-coupon" data-coupon="<?php echo esc_attr($coupon_code); ?>">
									<i class="fas fa-times"></i>
								</button>
							</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<!-- Cart Summary -->
				<?php if ($settings['show_summary_section'] ?? true): ?>
				<div class="footer-section">
					<div class="cart-summary" style="background: <?php echo esc_attr($summary_background_color); ?>; border: 2px solid <?php echo esc_attr($summary_border_color); ?>; border-radius: <?php echo esc_attr($summary_border_radius); ?>px;">
						<?php if ($settings['show_summary_header'] ?? true): ?>
						<div class="summary-header" style="padding: <?php echo esc_attr($summary_padding['top'] . $summary_padding['unit']); ?> <?php echo esc_attr($summary_padding['right'] . $summary_padding['unit']); ?>; border-bottom: 1px solid <?php echo esc_attr($summary_border_color); ?>;">
							<h3 class="summary-title" style="font-size: <?php echo esc_attr($summary_title_font_size); ?>px; color: <?php echo esc_attr($summary_title_color); ?>;">
								<?php if ($settings['show_summary_icon'] ?? true): ?>
								<i class="fas fa-receipt" style="color: <?php echo esc_attr($summary_icon_color); ?>;"></i>
								<?php endif; ?>
								<?php echo esc_html($settings['summary_title_text'] ?? __('Order Summary', 'shopglut')); ?>
							</h3>
						</div>
						<?php endif; ?>
						<div class="summary-content" style="padding: <?php echo esc_attr($summary_padding['top'] . $summary_padding['unit']); ?> <?php echo esc_attr($summary_padding['right'] . $summary_padding['unit']); ?> <?php echo esc_attr($summary_padding['bottom'] . $summary_padding['unit']); ?> <?php echo esc_attr($summary_padding['left'] . $summary_padding['unit']); ?>;">
							<?php if ($settings['show_subtotal'] ?? true): ?>
							<div class="summary-row" style="padding: <?php echo esc_attr($row_spacing); ?>px 0; color: <?php echo esc_attr($row_label_color); ?>; font-size: <?php echo esc_attr($row_font_size); ?>px;">
								<span class="label"><?php echo esc_html__('Subtotal:', 'shopglut'); ?></span>
								<span class="value" style="color: <?php echo esc_attr($row_value_color); ?>;"><?php echo wp_kses_post(wc_price($cart->get_subtotal())); ?></span>
							</div>
							<?php endif; ?>

							<?php if ($settings['show_discount_row'] ?? true && $cart->get_discount_total() > 0): ?>
							<div class="summary-row" style="padding: <?php echo esc_attr($row_spacing); ?>px 0; color: <?php echo esc_attr($row_label_color); ?>; font-size: <?php echo esc_attr($row_font_size); ?>px;">
								<span class="label"><?php echo esc_html__('Discount:', 'shopglut'); ?></span>
								<span class="value discount" style="color: <?php echo esc_attr($row_value_color); ?>;">-<?php echo wp_kses_post(wc_price($cart->get_discount_total())); ?></span>
							</div>
							<?php endif; ?>

							<?php if ($settings['show_shipping'] ?? true): ?>
							<div class="summary-row" style="padding: <?php echo esc_attr($row_spacing); ?>px 0; color: <?php echo esc_attr($row_label_color); ?>; font-size: <?php echo esc_attr($row_font_size); ?>px;">
								<span class="label"><?php echo esc_html__('Shipping:', 'shopglut'); ?></span>
								<span class="value" style="color: <?php echo esc_attr($row_value_color); ?>;"><?php echo wp_kses_post(wc_price($cart->get_shipping_total())); ?></span>
							</div>
							<?php endif; ?>

							<?php if ($settings['show_tax'] ?? true && wc_tax_enabled()): ?>
							<div class="summary-row" style="padding: <?php echo esc_attr($row_spacing); ?>px 0; color: <?php echo esc_attr($row_label_color); ?>; font-size: <?php echo esc_attr($row_font_size); ?>px;">
								<span class="label"><?php echo esc_html__('Tax:', 'shopglut'); ?></span>
								<span class="value" style="color: <?php echo esc_attr($row_value_color); ?>;"><?php echo wp_kses_post(wc_price($cart->get_total_tax())); ?></span>
							</div>
							<?php endif; ?>

							<div class="summary-row total-row" style="padding: <?php echo esc_attr($row_spacing); ?>px 0 16px 0; color: <?php echo esc_attr($total_label_color); ?>; font-size: <?php echo esc_attr($total_font_size); ?>px; font-weight: <?php echo esc_attr($total_font_weight); ?>; border-bottom: <?php echo $total_row_separator ? '2px solid ' . esc_attr($total_separator_color) : 'none'; ?>;">
								<span class="label"><?php echo esc_html__('Total:', 'shopglut'); ?></span>
								<span class="value" style="color: <?php echo esc_attr($total_value_color); ?>;"><?php echo wp_kses_post(wc_price($cart->get_total(''))); ?></span>
							</div>

							<button class="checkout-btn" id="proceed-to-checkout" style="background: <?php echo esc_attr($checkout_button_background); ?>; color: <?php echo esc_attr($checkout_button_text_color); ?>; border-radius: <?php echo esc_attr($checkout_button_border_radius); ?>px; font-size: <?php echo esc_attr($checkout_button_font_size); ?>px; padding: <?php echo esc_attr($checkout_button_padding['top'] . $checkout_button_padding['unit']); ?> <?php echo esc_attr($checkout_button_padding['right'] . $checkout_button_padding['unit']); ?> <?php echo esc_attr($checkout_button_padding['bottom'] . $checkout_button_padding['unit']); ?> <?php echo esc_attr($checkout_button_padding['left'] . $checkout_button_padding['unit']); ?>;">
								<?php if ($settings['show_checkout_icon'] ?? true): ?>
								<i class="fas fa-lock"></i>
								<?php endif; ?>
								<?php echo esc_html($settings['checkout_button_text'] ?? __('Secure Checkout', 'shopglut')); ?>
							</button>

							<?php if ($settings['show_security_badges'] ?? true): ?>
							<div class="security-info" style="gap: <?php echo esc_attr($security_badge_spacing); ?>px; <?php echo $security_badges_layout === 'vertical' ? 'flex-direction: column;' : ''; ?> <?php echo $security_badges_layout === 'grid' ? 'display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));' : ''; ?>">
								<?php if ($settings['show_ssl_badge'] ?? true): ?>
								<div class="security-badge ssl-badge" style="font-size: <?php echo esc_attr($ssl_badge_font_size); ?>px;">
									<i class="<?php echo esc_attr($settings['ssl_badge_icon'] ?? 'fas fa-shield-alt'); ?>" style="color: <?php echo esc_attr($ssl_badge_icon_color); ?>;"></i>
									<span style="color: <?php echo esc_attr($ssl_badge_text_color); ?>;"><?php echo esc_html($settings['ssl_badge_text'] ?? __('SSL Secured', 'shopglut')); ?></span>
								</div>
								<?php endif; ?>
								<?php if ($settings['show_payment_badge'] ?? true): ?>
								<div class="security-badge payment-badge" style="font-size: <?php echo esc_attr($payment_badge_font_size); ?>px;">
									<i class="<?php echo esc_attr($settings['payment_badge_icon'] ?? 'fas fa-credit-card'); ?>" style="color: <?php echo esc_attr($payment_badge_icon_color); ?>;"></i>
									<span style="color: <?php echo esc_attr($payment_badge_text_color); ?>;"><?php echo esc_html($settings['payment_badge_text'] ?? __('Safe Payment', 'shopglut')); ?></span>
								</div>
								<?php endif; ?>
								<?php if ($settings['show_return_badge'] ?? true): ?>
								<div class="security-badge return-badge" style="font-size: <?php echo esc_attr($return_badge_font_size); ?>px;">
									<i class="<?php echo esc_attr($settings['return_badge_icon'] ?? 'fas fa-undo'); ?>" style="color: <?php echo esc_attr($return_badge_icon_color); ?>;"></i>
									<span style="color: <?php echo esc_attr($return_badge_text_color); ?>;"><?php echo esc_html($settings['return_badge_text'] ?? __('30-Day Return', 'shopglut')); ?></span>
								</div>
								<?php endif; ?>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php endif; ?>
			</div>

			<!-- Continue Shopping Link -->
			<?php if ($settings['show_continue_shopping'] ?? true): ?>
			<div class="continue-shopping">
				<a href="<?php echo esc_url($this->getContinueShoppingUrl($settings)); ?>" class="continue-link" style="color: <?php echo esc_attr($continue_link_color); ?>; font-size: <?php echo esc_attr($continue_link_font_size); ?>px;">
					<?php if ($settings['show_continue_icon'] ?? true): ?>
					<i class="fas fa-arrow-left"></i>
					<?php endif; ?>
					<?php echo esc_html($settings['continue_shopping_text'] ?? __('Continue Shopping', 'shopglut')); ?>
				</a>
			</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render demo cart for preview when cart is empty
	 */
	private function render_demo_cart($settings) {
		// Get the placeholder image URL
		$placeholder_url = SHOPGLUT_URL . 'global-assets/images/wc-placeholder.png';

		?>
		<div class="cart-content">
			<div class="cart-table-container">
				<table class="cart-table">
					<?php if ($settings['show_table_header']): ?>
					<thead>
						<tr>
							<th><?php echo esc_html__('Product', 'shopglut'); ?></th>
							<th><?php echo esc_html__('Price', 'shopglut'); ?></th>
							<th><?php echo esc_html__('Quantity', 'shopglut'); ?></th>
							<th><?php echo esc_html__('Total', 'shopglut'); ?></th>
							<th></th>
						</tr>
					</thead>
					<?php endif; ?>
					<tbody>
						<!-- Demo Product 1 -->
						<tr class="demo-item">
							<td>
								<div class="product-cell">
									<div class="product-image" style="width: <?php echo esc_attr($settings['product_image_size']['width'] ?? 60); ?>px; height: <?php echo esc_attr($settings['product_image_size']['height'] ?? 60); ?>px; background: <?php echo esc_attr($settings['image_background_color'] ?? '#f9fafb'); ?>; border-radius: <?php echo esc_attr($settings['image_border_radius'] ?? 8); ?>px; border: <?php echo esc_attr($settings['image_border_width'] ?? 1); ?>px solid <?php echo esc_attr($settings['image_border_color'] ?? '#e5e7eb'); ?>;">
										<img src="<?php echo esc_url($placeholder_url); ?>" alt="<?php echo esc_attr__('Sample Product', 'shopglut'); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: <?php echo esc_attr($settings['image_border_radius'] ?? 8); ?>px;" />
									</div>
									<div class="product-details">
										<?php if ($settings['show_product_link']): ?>
										<a href="#" class="product-name" style="color: <?php echo esc_attr($settings['product_title_color'] ?? '#111827'); ?>; font-size: <?php echo esc_attr($settings['product_title_font_size'] ?? 16); ?>px; font-weight: <?php echo esc_attr($settings['product_title_font_weight'] ?? '600'); ?>;"><?php echo esc_html__('Premium Wireless Headphones', 'shopglut'); ?></a>
										<?php else: ?>
										<div class="product-name" style="color: <?php echo esc_attr($settings['product_title_color'] ?? '#111827'); ?>; font-size: <?php echo esc_attr($settings['product_title_font_size'] ?? 16); ?>px; font-weight: <?php echo esc_attr($settings['product_title_font_weight'] ?? '600'); ?>;"><?php echo esc_html__('Premium Wireless Headphones', 'shopglut'); ?></div>
										<?php endif; ?>
										<?php if ($settings['show_product_meta']): ?>
										<div class="product-meta" style="color: <?php echo esc_attr($settings['product_meta_color'] ?? '#6b7280'); ?>; font-size: <?php echo esc_attr($settings['product_meta_font_size'] ?? 14); ?>px;">
											<?php echo esc_html__('High-quality audio with noise cancellation', 'shopglut'); ?>
											<?php if ($settings['show_product_badges']): ?>
											<span class="product-badge sale-badge" style="background: <?php echo esc_attr($settings['badge_background_color'] ?? '#3b82f6'); ?>; color: <?php echo esc_attr($settings['badge_text_color'] ?? '#ffffff'); ?>;"><?php echo esc_html__('Sale', 'shopglut'); ?></span>
											<?php endif; ?>
										</div>
										<?php endif; ?>
									</div>
								</div>
							</td>
							<td class="price-cell" style="color: <?php echo esc_attr($settings['price_color'] ?? '#111827'); ?>; font-size: <?php echo esc_attr($settings['price_font_size'] ?? 16); ?>px; font-weight: <?php echo esc_attr($settings['price_font_weight'] ?? '600'); ?>;">$129.99</td>
							<td class="quantity-cell">
								<div class="qty-control" style="border-color: <?php echo esc_attr($settings['quantity_input_border'] ?? '#d1d5db'); ?>; border-radius: <?php echo esc_attr($settings['quantity_control_border_radius'] ?? 6); ?>px; background: <?php echo esc_attr($settings['quantity_input_background'] ?? '#ffffff'); ?>;">
									<button class="qty-btn qty-decrease" disabled style="background: <?php echo esc_attr($settings['quantity_button_color'] ?? '#f3f4f6'); ?>; color: <?php echo esc_attr($settings['quantity_button_text_color'] ?? '#374151'); ?>;">−</button>
									<input type="number" value="1" min="1" class="qty-input" disabled style="color: <?php echo esc_attr($settings['quantity_button_text_color'] ?? '#374151'); ?>; background: <?php echo esc_attr($settings['quantity_input_background'] ?? '#ffffff'); ?>;">
									<button class="qty-btn qty-increase" disabled style="background: <?php echo esc_attr($settings['quantity_button_color'] ?? '#f3f4f6'); ?>; color: <?php echo esc_attr($settings['quantity_button_text_color'] ?? '#374151'); ?>;">+</button>
								</div>
							</td>
							<td class="price-cell item-total" style="color: <?php echo esc_attr($settings['total_price_highlight'] ? ($settings['total_price_color'] ?? '#059669') : ($settings['price_color'] ?? '#111827')); ?>; font-size: <?php echo esc_attr($settings['price_font_size'] ?? 16); ?>px; font-weight: <?php echo esc_attr($settings['price_font_weight'] ?? '600'); ?>;">$129.99</td>
							<td>
								<button class="remove-btn" disabled title="<?php echo esc_attr__('Remove item', 'shopglut'); ?>">
									<i class="fas fa-times"></i>
								</button>
							</td>
						</tr>

						<!-- Demo Product 2 -->
						<tr class="demo-item">
							<td>
								<div class="product-cell">
									<div class="product-image" style="width: <?php echo esc_attr($settings['product_image_size']['width'] ?? 60); ?>px; height: <?php echo esc_attr($settings['product_image_size']['height'] ?? 60); ?>px; background: <?php echo esc_attr($settings['image_background_color'] ?? '#f9fafb'); ?>; border-radius: <?php echo esc_attr($settings['image_border_radius'] ?? 8); ?>px; border: <?php echo esc_attr($settings['image_border_width'] ?? 1); ?>px solid <?php echo esc_attr($settings['image_border_color'] ?? '#e5e7eb'); ?>;">
										<img src="<?php echo esc_url($placeholder_url); ?>" alt="<?php echo esc_attr__('Sample Product', 'shopglut'); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: <?php echo esc_attr($settings['image_border_radius'] ?? 8); ?>px;" />
									</div>
									<div class="product-details">
										<?php if ($settings['show_product_link']): ?>
										<a href="#" class="product-name" style="color: <?php echo esc_attr($settings['product_title_color'] ?? '#111827'); ?>; font-size: <?php echo esc_attr($settings['product_title_font_size'] ?? 16); ?>px; font-weight: <?php echo esc_attr($settings['product_title_font_weight'] ?? '600'); ?>;"><?php echo esc_html__('Smartphone Case', 'shopglut'); ?></a>
										<?php else: ?>
										<div class="product-name" style="color: <?php echo esc_attr($settings['product_title_color'] ?? '#111827'); ?>; font-size: <?php echo esc_attr($settings['product_title_font_size'] ?? 16); ?>px; font-weight: <?php echo esc_attr($settings['product_title_font_weight'] ?? '600'); ?>;"><?php echo esc_html__('Smartphone Case', 'shopglut'); ?></div>
										<?php endif; ?>
										<?php if ($settings['show_product_meta']): ?>
										<div class="product-meta" style="color: <?php echo esc_attr($settings['product_meta_color'] ?? '#6b7280'); ?>; font-size: <?php echo esc_attr($settings['product_meta_font_size'] ?? 14); ?>px;">
											<?php echo esc_html__('Protective case with premium materials', 'shopglut'); ?>
											<?php if ($settings['show_product_badges']): ?>
											<span class="product-badge featured-badge" style="background: <?php echo esc_attr($settings['badge_background_color'] ?? '#3b82f6'); ?>; color: <?php echo esc_attr($settings['badge_text_color'] ?? '#ffffff'); ?>;"><?php echo esc_html__('Featured', 'shopglut'); ?></span>
											<?php endif; ?>
										</div>
										<?php endif; ?>
									</div>
								</div>
							</td>
							<td class="price-cell" style="color: <?php echo esc_attr($settings['price_color'] ?? '#111827'); ?>; font-size: <?php echo esc_attr($settings['price_font_size'] ?? 16); ?>px; font-weight: <?php echo esc_attr($settings['price_font_weight'] ?? '600'); ?>;">$24.99</td>
							<td class="quantity-cell">
								<div class="qty-control" style="border-color: <?php echo esc_attr($settings['quantity_input_border'] ?? '#d1d5db'); ?>; border-radius: <?php echo esc_attr($settings['quantity_control_border_radius'] ?? 6); ?>px; background: <?php echo esc_attr($settings['quantity_input_background'] ?? '#ffffff'); ?>;">
									<button class="qty-btn qty-decrease" disabled style="background: <?php echo esc_attr($settings['quantity_button_color'] ?? '#f3f4f6'); ?>; color: <?php echo esc_attr($settings['quantity_button_text_color'] ?? '#374151'); ?>;">−</button>
									<input type="number" value="2" min="1" class="qty-input" disabled style="color: <?php echo esc_attr($settings['quantity_button_text_color'] ?? '#374151'); ?>; background: <?php echo esc_attr($settings['quantity_input_background'] ?? '#ffffff'); ?>;">
									<button class="qty-btn qty-increase" disabled style="background: <?php echo esc_attr($settings['quantity_button_color'] ?? '#f3f4f6'); ?>; color: <?php echo esc_attr($settings['quantity_button_text_color'] ?? '#374151'); ?>;">+</button>
								</div>
							</td>
							<td class="price-cell item-total" style="color: <?php echo esc_attr($settings['total_price_highlight'] ? ($settings['total_price_color'] ?? '#059669') : ($settings['price_color'] ?? '#111827')); ?>; font-size: <?php echo esc_attr($settings['price_font_size'] ?? 16); ?>px; font-weight: <?php echo esc_attr($settings['price_font_weight'] ?? '600'); ?>;">$49.98</td>
							<td>
								<button class="remove-btn" disabled title="<?php echo esc_attr__('Remove item', 'shopglut'); ?>">
									<i class="fas fa-times"></i>
								</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="cart-footer">
				<div class="footer-grid">
					<?php if ($settings['show_discount_section'] ?? true): ?>
					<div class="footer-section" style="background: <?php echo esc_attr($settings['discount_section_background'] ?? '#ffffff'); ?>; border: 1px solid <?php echo esc_attr($settings['discount_section_border'] ?? '#e5e7eb'); ?>; padding: <?php echo esc_attr(($settings['discount_section_padding']['top'] ?? 20) . ($settings['discount_section_padding']['unit'] ?? 'px')); ?> <?php echo esc_attr(($settings['discount_section_padding']['right'] ?? 20) . ($settings['discount_section_padding']['unit'] ?? 'px')); ?> <?php echo esc_attr(($settings['discount_section_padding']['bottom'] ?? 20) . ($settings['discount_section_padding']['unit'] ?? 'px')); ?> <?php echo esc_attr(($settings['discount_section_padding']['left'] ?? 20) . ($settings['discount_section_padding']['unit'] ?? 'px')); ?>; border-radius: 8px;">
						<?php if ($settings['show_discount_title'] ?? true): ?>
						<h3 class="section-title" style="font-size: <?php echo esc_attr($settings['discount_title_font_size'] ?? 18); ?>px; color: <?php echo esc_attr($settings['discount_title_color'] ?? '#111827'); ?>;">
							<?php if ($settings['show_discount_icon'] ?? true): ?>
							<i class="fas fa-tag" style="color: <?php echo esc_attr($settings['discount_icon_color'] ?? '#3b82f6'); ?>;"></i>
							<?php endif; ?>
							<?php echo esc_html($settings['discount_title_text'] ?? __('Discount Code', 'shopglut')); ?>
						</h3>
						<?php endif; ?>
						<form class="coupon-form">
							<div class="input-group" style="border-color: <?php echo esc_attr($settings['coupon_input_border'] ?? '#d1d5db'); ?>; border-radius: <?php echo esc_attr($settings['coupon_input_border_radius'] ?? 6); ?>px;">
								<input type="text" placeholder="<?php echo esc_attr($settings['coupon_input_placeholder'] ?? __('Enter coupon code', 'shopglut')); ?>" class="coupon-input" disabled style="background: <?php echo esc_attr($settings['coupon_input_background'] ?? '#ffffff'); ?>; color: <?php echo esc_attr($settings['coupon_input_text_color'] ?? '#374151'); ?>; padding: <?php echo esc_attr(($settings['coupon_input_padding']['top'] ?? 12) . ($settings['coupon_input_padding']['unit'] ?? 'px')); ?> <?php echo esc_attr(($settings['coupon_input_padding']['right'] ?? 16) . ($settings['coupon_input_padding']['unit'] ?? 'px')); ?> <?php echo esc_attr(($settings['coupon_input_padding']['bottom'] ?? 12) . ($settings['coupon_input_padding']['unit'] ?? 'px')); ?> <?php echo esc_attr(($settings['coupon_input_padding']['left'] ?? 16) . ($settings['coupon_input_padding']['unit'] ?? 'px')); ?>;">
								<button type="button" class="apply-btn" disabled style="background: <?php echo esc_attr($settings['apply_button_background'] ?? '#3b82f6'); ?>; color: <?php echo esc_attr($settings['apply_button_text_color'] ?? '#ffffff'); ?>; border-radius: <?php echo esc_attr($settings['apply_button_border_radius'] ?? 6); ?>px; padding: <?php echo esc_attr(($settings['apply_button_padding']['top'] ?? 12) . ($settings['apply_button_padding']['unit'] ?? 'px')); ?> <?php echo esc_attr(($settings['apply_button_padding']['right'] ?? 20) . ($settings['apply_button_padding']['unit'] ?? 'px')); ?> <?php echo esc_attr(($settings['apply_button_padding']['bottom'] ?? 12) . ($settings['apply_button_padding']['unit'] ?? 'px')); ?> <?php echo esc_attr(($settings['apply_button_padding']['left'] ?? 20) . ($settings['apply_button_padding']['unit'] ?? 'px')); ?>;"><?php echo esc_html($settings['apply_button_text'] ?? __('Apply', 'shopglut')); ?></button>
							</div>
						</form>
					</div>
					<?php endif; ?>

					<?php if ($settings['show_summary_section'] ?? true): ?>
					<div class="footer-section">
						<div class="cart-summary" style="background: <?php echo esc_attr($settings['summary_background_color'] ?? '#f9fafb'); ?>; border: 2px solid <?php echo esc_attr($settings['summary_border_color'] ?? '#e5e7eb'); ?>; border-radius: <?php echo esc_attr($settings['summary_border_radius'] ?? 8); ?>px;">
							<?php if ($settings['show_summary_header'] ?? true): ?>
							<div class="summary-header" style="padding: <?php echo esc_attr($settings['summary_padding']['top'] ?? 24) . ($settings['summary_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['summary_padding']['right'] ?? 20) . ($settings['summary_padding']['unit'] ?? 'px'); ?>; border-bottom: 1px solid <?php echo esc_attr($settings['summary_border_color'] ?? '#e5e7eb'); ?>;">
								<h3 class="summary-title" style="font-size: <?php echo esc_attr($settings['summary_title_font_size'] ?? 20); ?>px; color: <?php echo esc_attr($settings['summary_title_color'] ?? '#111827'); ?>;">
									<?php if ($settings['show_summary_icon'] ?? true): ?>
									<i class="fas fa-receipt" style="color: <?php echo esc_attr($settings['summary_icon_color'] ?? '#3b82f6'); ?>;"></i>
									<?php endif; ?>
									<?php echo esc_html($settings['summary_title_text'] ?? __('Order Summary', 'shopglut')); ?>
								</h3>
							</div>
							<?php endif; ?>
							<div class="summary-content" style="padding: <?php echo esc_attr($settings['summary_padding']['top'] ?? 24) . ($settings['summary_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['summary_padding']['right'] ?? 20) . ($settings['summary_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['summary_padding']['bottom'] ?? 24) . ($settings['summary_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['summary_padding']['left'] ?? 20) . ($settings['summary_padding']['unit'] ?? 'px'); ?>;">
								<?php if ($settings['show_subtotal'] ?? true): ?>
								<div class="summary-row" style="padding: <?php echo esc_attr($settings['row_spacing'] ?? 12); ?>px 0; color: <?php echo esc_attr($settings['row_label_color'] ?? '#6b7280'); ?>; font-size: <?php echo esc_attr($settings['row_font_size'] ?? 14); ?>px;">
									<span class="label"><?php echo esc_html__('Subtotal:', 'shopglut'); ?></span>
									<span class="value" style="color: <?php echo esc_attr($settings['row_value_color'] ?? '#111827'); ?>;">$179.97</span>
								</div>
								<?php endif; ?>
								<?php if ($settings['show_shipping'] ?? true): ?>
								<div class="summary-row" style="padding: <?php echo esc_attr($settings['row_spacing'] ?? 12); ?>px 0; color: <?php echo esc_attr($settings['row_label_color'] ?? '#6b7280'); ?>; font-size: <?php echo esc_attr($settings['row_font_size'] ?? 14); ?>px;">
									<span class="label"><?php echo esc_html__('Shipping:', 'shopglut'); ?></span>
									<span class="value" style="color: <?php echo esc_attr($settings['row_value_color'] ?? '#111827'); ?>;">$9.99</span>
								</div>
								<?php endif; ?>
								<?php if ($settings['show_tax'] ?? true): ?>
								<div class="summary-row" style="padding: <?php echo esc_attr($settings['row_spacing'] ?? 12); ?>px 0; color: <?php echo esc_attr($settings['row_label_color'] ?? '#6b7280'); ?>; font-size: <?php echo esc_attr($settings['row_font_size'] ?? 14); ?>px;">
									<span class="label"><?php echo esc_html__('Tax:', 'shopglut'); ?></span>
									<span class="value" style="color: <?php echo esc_attr($settings['row_value_color'] ?? '#111827'); ?>;">$14.40</span>
								</div>
								<?php endif; ?>
								<div class="summary-row total-row" style="padding: <?php echo esc_attr($settings['row_spacing'] ?? 12); ?>px 0 16px 0; color: <?php echo esc_attr($settings['total_label_color'] ?? '#111827'); ?>; font-size: <?php echo esc_attr($settings['total_font_size'] ?? 18); ?>px; font-weight: <?php echo esc_attr($settings['total_font_weight'] ?? '700'); ?>; border-bottom: <?php echo ($settings['total_row_separator'] ?? true) ? '2px solid ' . esc_attr($settings['total_separator_color'] ?? '#e5e7eb') : 'none'; ?>;">
									<span class="label"><?php echo esc_html__('Total:', 'shopglut'); ?></span>
									<span class="value" style="color: <?php echo esc_attr($settings['total_value_color'] ?? '#059669'); ?>;">$204.36</span>
								</div>

								<button class="checkout-btn" disabled style="background: <?php echo esc_attr($settings['checkout_button_background'] ?? '#059669'); ?>; color: <?php echo esc_attr($settings['checkout_button_text_color'] ?? '#ffffff'); ?>; border-radius: <?php echo esc_attr($settings['checkout_button_border_radius'] ?? 8); ?>px; font-size: <?php echo esc_attr($settings['checkout_button_font_size'] ?? 16); ?>px; padding: <?php echo esc_attr(($settings['checkout_button_padding']['top'] ?? 16) . ($settings['checkout_button_padding']['unit'] ?? 'px')); ?> <?php echo esc_attr(($settings['checkout_button_padding']['right'] ?? 24) . ($settings['checkout_button_padding']['unit'] ?? 'px')); ?> <?php echo esc_attr(($settings['checkout_button_padding']['bottom'] ?? 16) . ($settings['checkout_button_padding']['unit'] ?? 'px')); ?> <?php echo esc_attr(($settings['checkout_button_padding']['left'] ?? 24) . ($settings['checkout_button_padding']['unit'] ?? 'px')); ?>;">
									<?php if ($settings['show_checkout_icon'] ?? true): ?>
									<i class="fas fa-lock"></i>
									<?php endif; ?>
									<?php echo esc_html($settings['checkout_button_text'] ?? __('Secure Checkout', 'shopglut')); ?>
								</button>

								<?php if ($settings['show_security_badges'] ?? true): ?>
								<div class="security-info" style="gap: <?php echo esc_attr($settings['security_badge_spacing'] ?? 8); ?>px; <?php echo ($settings['security_badges_layout'] ?? 'horizontal') === 'vertical' ? 'flex-direction: column;' : ''; ?> <?php echo ($settings['security_badges_layout'] ?? 'horizontal') === 'grid' ? 'display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));' : ''; ?>">
									<?php if ($settings['show_ssl_badge'] ?? true): ?>
									<div class="security-badge ssl-badge" style="font-size: <?php echo esc_attr($settings['ssl_badge_font_size'] ?? 12); ?>px;">
										<i class="<?php echo esc_attr($settings['ssl_badge_icon'] ?? 'fas fa-shield-alt'); ?>" style="color: <?php echo esc_attr($settings['ssl_badge_icon_color'] ?? '#059669'); ?>;"></i>
										<span style="color: <?php echo esc_attr($settings['ssl_badge_text_color'] ?? '#6b7280'); ?>;"><?php echo esc_html($settings['ssl_badge_text'] ?? __('SSL Secured', 'shopglut')); ?></span>
									</div>
									<?php endif; ?>
									<?php if ($settings['show_payment_badge'] ?? true): ?>
									<div class="security-badge payment-badge" style="font-size: <?php echo esc_attr($settings['payment_badge_font_size'] ?? 12); ?>px;">
										<i class="<?php echo esc_attr($settings['payment_badge_icon'] ?? 'fas fa-credit-card'); ?>" style="color: <?php echo esc_attr($settings['payment_badge_icon_color'] ?? '#3b82f6'); ?>;"></i>
										<span style="color: <?php echo esc_attr($settings['payment_badge_text_color'] ?? '#6b7280'); ?>;"><?php echo esc_html($settings['payment_badge_text'] ?? __('Safe Payment', 'shopglut')); ?></span>
									</div>
									<?php endif; ?>
									<?php if ($settings['show_return_badge'] ?? true): ?>
									<div class="security-badge return-badge" style="font-size: <?php echo esc_attr($settings['return_badge_font_size'] ?? 12); ?>px;">
										<i class="<?php echo esc_attr($settings['return_badge_icon'] ?? 'fas fa-undo'); ?>" style="color: <?php echo esc_attr($settings['return_badge_icon_color'] ?? '#f59e0b'); ?>;"></i>
										<span style="color: <?php echo esc_attr($settings['return_badge_text_color'] ?? '#6b7280'); ?>;"><?php echo esc_html($settings['return_badge_text'] ?? __('30-Day Return', 'shopglut')); ?></span>
									</div>
									<?php endif; ?>
								</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</div>

				<!-- Continue Shopping Link -->
				<?php if ($settings['show_continue_shopping'] ?? true): ?>
				<div class="continue-shopping">
					<a href="<?php echo esc_url($this->getContinueShoppingUrl($settings)); ?>" class="continue-link" style="color: <?php echo esc_attr($settings['continue_link_color'] ?? '#3b82f6'); ?>; font-size: <?php echo esc_attr($settings['continue_link_font_size'] ?? 14); ?>px;">
						<?php if ($settings['show_continue_icon'] ?? true): ?>
						<i class="fas fa-arrow-left"></i>
						<?php endif; ?>
						<?php echo esc_html($settings['continue_shopping_text'] ?? __('Continue Shopping', 'shopglut')); ?>
					</a>
				</div>
				<?php endif; ?>
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

		global $wpdb;
		$table_name = $wpdb->prefix . 'shopglut_cartpage_layouts';


		// Use caching for better performance
		$cache_key = "shopglut_cartpage_settings_{$layout_id}";
		$layout_data = wp_cache_get( $cache_key, 'shopglut_cartpage' );

		if ( false === $layout_data ) {
			// Use proper table name escaping with %i placeholder
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table operation
			$layout_data = $wpdb->get_row(
				$wpdb->prepare("SELECT layout_settings FROM {$wpdb->prefix}shopglut_cartpage_layouts WHERE id = %d", $layout_id)
			);

			// Cache the result for 30 minutes
			wp_cache_set( $cache_key, $layout_data, 'shopglut_cartpage', 30 * MINUTE_IN_SECONDS );
		}

		if ($layout_data && !empty($layout_data->layout_settings)) {
			$settings = maybe_unserialize($layout_data->layout_settings);

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