<?php
/**
 * Cart page template 2 (ported from CartPage Glut).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

class ShopGlut_Cart_Template2_Style {

    public function dynamicCss($layout_id = 0)
    {
        // Get settings for this layout
        $settings = $this->getLayoutSettings($layout_id);

        ?>
        <style> 
        
.wide-rows {
  --font-display: 'Playfair Display', serif;
  --font-body: 'Source Sans Pro', sans-serif;
  --dark: #1a1a1a;
  --light: #f5f5f5;
  --gray: #888;
  --accent: #c9a55c;
}

.wide-rows {
  font-family: var(--font-body);
  background: #fafafa;
  color: var(--dark);
  padding: 0;
}

.wide-rows .wide-wrapper {
  max-width: 1200px;
  margin: 0 auto;
}

.wide-rows .top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 0;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 30px;
}

.wide-rows .bar-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.wide-rows .bar-title {
  font-family: var(--font-display);
  font-size: 28px;
  font-weight: 600;
}

.wide-rows .bar-separator {
  color: #ddd;
}

.wide-rows .bar-count {
  color: var(--gray);
  font-size: 14px;
}

.wide-rows .bar-link {
  color: var(--dark);
  text-decoration: none;
  font-weight: 600;
  border-bottom: 1px solid var(--dark);
  padding-bottom: 2px;
  transition: all 0.2s;
}

.wide-rows .bar-link:hover {
  color: var(--accent);
  border-color: var(--accent);
}

.wide-rows .bar-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.wide-rows .clear-all-btn {
  padding: 10px 20px;
  background: #fff0f0;
  color: #e74c3c;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
}

.wide-rows .clear-all-btn:hover {
  background: #ffe0e0;
  transform: translateY(-1px);
}

.wide-rows .wide-items {
  display: flex;
  flex-direction: column;
  gap: 1px;
  background: #e0e0e0;
}

.wide-rows .wide-row {
  background: white;
  transition: all 0.3s ease;
}

.wide-rows .wide-row:hover {
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.wide-rows .wide-row-layout {
  display: grid;
  grid-template-columns: 200px 1fr;
  gap: 0;
}

.wide-rows .wide-image {
  padding: 0;
  position: relative;
}

.wide-rows .wide-img-delete {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 36px;
  height: 36px;
  border: none;
  background: rgba(255,255,255,0.95);
  border-radius: 50%;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  display: flex;
  align-items: center;
  justify-content: center;
}

.wide-rows .wide-img-delete:hover {
  background: #e74c3c;
  color: white;
  transform: scale(1.1);
}

.wide-rows .wide-img-box {
  height: 100%;
  min-height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.wide-rows .wide-img-text {
  color: rgba(255,255,255,0.3);
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 2px;
  text-transform: uppercase;
}

.wide-rows .wide-info {
  padding: 30px 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.wide-rows .wide-info-main {
  margin-bottom: 20px;
}

.wide-rows .wide-title {
  font-family: var(--font-display);
  font-size: 22px;
  font-weight: 600;
  margin: 0 0 6px 0;
}

.wide-rows .wide-sku {
  font-size: 12px;
  color: var(--gray);
  margin: 0 0 8px 0;
  font-weight: 400;
  letter-spacing: 0.5px;
}

.wide-rows .wide-meta {
  color: var(--gray);
  margin: 0 0 12px 0;
  font-size: 14px;
}

.wide-rows .wide-colors {
  display: flex;
  align-items: center;
  gap: 10px;
}

.wide-rows .color-label {
  font-size: 13px;
  color: var(--gray);
  font-weight: 500;
}

.wide-rows .color-dot {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s;
  border: 2px solid transparent;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.wide-rows .color-dot:hover {
  transform: scale(1.2);
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.wide-rows .color-active {
  border-color: var(--dark);
  box-shadow: 0 0 0 2px white, 0 0 0 4px var(--accent);
}

.wide-rows .wide-info-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.wide-rows .wide-qty-group {
  display: flex;
  align-items: center;
  gap: 16px;
}

.wide-rows .qty-label {
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: var(--gray);
}

.wide-rows .wide-qty-control {
  display: flex;
  align-items: center;
  border: 1px solid #ddd;
  border-radius: 4px;
  overflow: hidden;
}

.wide-rows .wide-qty-btn {
  width: 40px;
  height: 40px;
  border: none;
  background: var(--light);
  cursor: pointer;
  font-size: 18px;
  transition: all 0.2s;
}

.wide-rows .wide-qty-btn:hover {
  background: var(--accent);
  color: white;
  transform: scale(1.05);
}

.wide-rows .wide-qty-val {
  width: 50px;
  text-align: center;
  font-weight: 600;
  font-size: 16px;
}

.wide-rows .wide-price-section {
  text-align: right;
}

.wide-rows .wide-price {
  font-family: var(--font-display);
  font-size: 24px;
  font-weight: 600;
  display: block;
  margin-bottom: 8px;
}

.wide-rows .wide-remove {
  background: none;
  border: none;
  color: var(--gray);
  font-size: 13px;
  cursor: pointer;
  text-decoration: underline;
  transition: color 0.2s;
}

.wide-rows .wide-remove:hover {
  color: #e74c3c;
}

.wide-rows .bottom-panel {
  position: sticky;
  bottom: 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 40px;
  margin-top: 30px;
  background: white;
  border-top: 2px solid var(--accent);
  box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
}

.wide-rows .panel-note {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #27ae60;
  font-size: 14px;
}

.wide-rows .panel-right {
  display: flex;
  align-items: center;
  gap: 32px;
}

.wide-rows .panel-totals {
  text-align: right;
}

.wide-rows .panel-totals span:first-child {
  display: block;
  font-size: 13px;
  color: var(--gray);
  text-transform: uppercase;
  letter-spacing: 1px;
}

.wide-rows .panel-total {
  font-family: var(--font-display);
  font-size: 32px;
  font-weight: 600;
}

.wide-rows .panel-checkout {
  padding: 16px 48px;
  background: var(--dark);
  color: white;
  border: none;
  font-size: 15px;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.2s;
}

.wide-rows .panel-checkout:hover {
  background: var(--accent);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(201, 165, 92, 0.4);
}

@media (max-width: 768px) {
  .wide-rows .bar-right {
    flex-direction: column;
    gap: 10px;
    align-items: flex-end;
  }

  .wide-rows .wide-row-layout {
    grid-template-columns: 100px 1fr;
  }

  .wide-rows .wide-info {
    padding: 20px;
  }

  .wide-rows .wide-title {
    font-size: 18px;
  }

  .wide-rows .wide-colors {
    flex-wrap: wrap;
  }

  .wide-rows .wide-info-actions {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .wide-rows .wide-price-section {
    text-align: left;
  }

  .wide-rows .bottom-panel {
    flex-direction: column;
    gap: 20px;
    padding: 20px;
  }

  .wide-rows .panel-right {
    flex-direction: column;
    width: 100%;
    gap: 16px;
  }

  .wide-rows .panel-checkout {
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
