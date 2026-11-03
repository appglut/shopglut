<?php
/**
 * Cart page template 3 (ported from CartPage Glut).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

class ShopGlut_Cart_Template3_Style {

    public function dynamicCss($layout_id = 0)
    {
        // Get settings for this layout
        $settings = $this->getLayoutSettings($layout_id);

        ?>
       <style> .clean-technical {
  --font-main: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  --color-bg: #f8fafc;
  --color-surface: #ffffff;
  --color-text: #0f172a;
  --color-text-secondary: #64748b;
  --color-text-muted: #94a3b8;
  --color-border: #e2e8f0;
  --color-border-light: #f1f5f9;
  --color-primary: #0ea5e9;
  --color-primary-dark: #0284c7;
  --color-success: #10b981;
  --color-danger: #ef4444;
}

.clean-technical {
  font-family: var(--font-main);
  color: var(--color-text);
  background: var(--color-bg);
  padding: 32px 24px;
  line-height: 1.5;
}

.clean-technical .technical-grid {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 24px;
}

.clean-technical .technical-header {
  margin-bottom: 24px;
}

.clean-technical .breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  margin-bottom: 16px;
}

.clean-technical .breadcrumb a {
  color: var(--color-text-secondary);
  text-decoration: none;
}

.clean-technical .breadcrumb a:hover {
  color: var(--color-primary);
}

.clean-technical .breadcrumb .separator {
  color: var(--color-text-muted);
}

.clean-technical .breadcrumb span:last-child {
  color: var(--color-text);
}

.clean-technical .technical-header h1 {
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.clean-technical .header-description {
  color: var(--color-text-secondary);
  margin: 0;
  font-size: 14px;
}

.clean-technical .technical-cart {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  overflow: hidden;
}

.clean-technical .cart-header-row {
  display: grid;
  grid-template-columns: 140px 1fr 100px 100px 50px;
  gap: 16px;
  padding: 16px 20px;
  background: var(--color-bg-light);
  border-bottom: 1px solid var(--color-border);
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--color-text-secondary);
}

.clean-technical .cart-items-rows {
  display: flex;
  flex-direction: column;
}

.clean-technical .cart-row-item {
  display: grid;
  grid-template-columns: 140px 1fr 100px 100px 50px;
  gap: 16px;
  padding: 20px;
  border-bottom: 1px solid var(--color-border-light);
  align-items: center;
}

.clean-technical .cart-row-item:last-child {
  border-bottom: none;
}

.clean-technical .row-product {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.clean-technical .product-thumb-small {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  overflow: hidden;
}

.clean-technical .thumb-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 9px;
  font-weight: 600;
  color: var(--color-text-muted);
  letter-spacing: 0.5px;
}

.clean-technical .product-sku {
  font-size: 11px;
  color: var(--color-text-muted);
  font-family: monospace;
}

.clean-technical .product-title {
  font-size: 15px;
  font-weight: 600;
  margin: 0 0 4px 0;
}

.clean-technical .product-attr {
  font-size: 13px;
  color: var(--color-text-secondary);
}

.clean-technical .qty-compact {
  display: flex;
  align-items: center;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  overflow: hidden;
  width: fit-content;
}

.clean-technical .qty-btn-sm {
  width: 28px;
  height: 32px;
  border: none;
  background: var(--color-bg-light);
  cursor: pointer;
  font-size: 14px;
  color: var(--color-text);
  transition: background 0.2s;
}

.clean-technical .qty-btn-sm:hover {
  background: var(--color-border);
}

.clean-technical .qty-input-sm {
  width: 36px;
  height: 32px;
  border: none;
  border-left: 1px solid var(--color-border);
  border-right: 1px solid var(--color-border);
  text-align: center;
  font-size: 14px;
  font-family: inherit;
}

.clean-technical .price-current {
  font-weight: 600;
  font-size: 15px;
}

.clean-technical .btn-remove-sm {
  width: 32px;
  height: 32px;
  border: none;
  background: none;
  color: var(--color-text-muted);
  cursor: pointer;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.clean-technical .btn-remove-sm:hover {
  background: #fef2f2;
  color: var(--color-danger);
}

.clean-technical .technical-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
  padding: 20px;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
}

.clean-technical .link-back {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--color-text-secondary);
  text-decoration: none;
  font-size: 14px;
}

.clean-technical .link-back:hover {
  color: var(--color-primary);
}

.clean-technical .btn-update {
  padding: 10px 20px;
  background: var(--color-bg-light);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.clean-technical .btn-update:hover {
  background: var(--color-border);
}

.clean-technical .technical-sidebar {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.clean-technical .summary-box {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 24px;
}

.clean-technical .summary-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--color-border-light);
}

.clean-technical .summary-header h2 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
}

.clean-technical .summary-count {
  font-size: 13px;
  color: var(--color-text-secondary);
  background: var(--color-bg-light);
  padding: 4px 12px;
  border-radius: 20px;
}

.clean-technical .summary-sections {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.clean-technical .summary-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
}

.clean-technical .line-label {
  color: var(--color-text-secondary);
}

.clean-technical .line-value {
  font-weight: 600;
}

.clean-technical .shipping-select select {
  padding: 6px 12px;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 13px;
  font-family: inherit;
  background: var(--color-surface);
  cursor: pointer;
}

.clean-technical .summary-total-section {
  border-top: 1px solid var(--color-border);
  padding-top: 16px;
  margin-top: 8px;
}

.clean-technical .total-line {
  font-size: 16px;
}

.clean-technical .total-label {
  font-weight: 600;
}

.clean-technical .total-value {
  font-size: 20px;
  font-weight: 700;
  color: var(--color-primary);
}

.clean-technical .btn-checkout-primary {
  width: 100%;
  padding: 14px 24px;
  margin-top: 24px;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.clean-technical .btn-checkout-primary:hover {
  background: var(--color-primary-dark);
}

.clean-technical .checkout-features {
  display: flex;
  gap: 20px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid var(--color-border-light);
}

.clean-technical .feature-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: var(--color-text-secondary);
}

.clean-technical .feature-item svg {
  color: var(--color-success);
  flex-shrink: 0;
}

.clean-technical .coupon-box {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 20px;
}

.clean-technical .coupon-box h3 {
  font-size: 14px;
  font-weight: 600;
  margin: 0 0 12px 0;
}

.clean-technical .coupon-input-group {
  display: flex;
  gap: 8px;
}

.clean-technical .coupon-input-group input {
  flex: 1;
  padding: 10px 14px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
}

.clean-technical .coupon-input-group button {
  padding: 10px 16px;
  background: var(--color-text);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: opacity 0.2s;
}

.clean-technical .coupon-input-group button:hover {
  opacity: 0.9;
}

@media (max-width: 1024px) {
  .clean-technical .technical-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .clean-technical .technical-cart {
    overflow-x: auto;
  }

  .clean-technical .cart-header-row,
  .clean-technical .cart-row-item {
    min-width: 600px;
  }

  .clean-technical .technical-footer {
    flex-direction: column;
    gap: 12px;
  }

  .clean-technical .btn-update {
    width: 100%;
  }
}</style>
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
     */
    private function flattenSettings($nested_settings) {
        $flat_settings = array();

        foreach ($nested_settings as $group_key => $group_values) {
            if (is_array($group_values)) {
                foreach ($group_values as $setting_key => $setting_value) {
                    // If value is an array, check if it's a nested fieldset or a slider field
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
            'continue_shopping_url' => '#',
            'continue_link_color' => '#3b82f6',
            'continue_link_hover_color' => '#2563eb',
            'continue_link_font_size' => 14,
            'show_continue_icon' => true,
        );
    }

}
