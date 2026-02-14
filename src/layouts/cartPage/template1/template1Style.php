<?php
namespace Shopglut\layouts\cartPage\template1;

class template1Style {

    public function dynamicCss($layout_id = 0)
    {
        // Get settings for this layout
        $settings = $this->getLayoutSettings($layout_id);

        ?>
        <style>
            .shopglut-cart.template1 .cart-container {
                max-width: 1200px;
                margin: 10px auto;
                padding: 0 10px;
            }

            .shopglut-cart.template1 .cart-header {
                background: white;
                border-radius: 16px 16px 0 0;
                padding: 40px;
                border-bottom: 1px solid #e2e8f0;
                text-align: center;
            }

            .shopglut-cart.template1 .cart-header h1 {
                font-size: 2.5rem;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 8px;
                letter-spacing: -0.02em;
            }

            .shopglut-cart.template1 .cart-header .subtitle {
                font-size: 1.1rem;
                color: #64748b;
                font-weight: 400;
            }

            .shopglut-cart.template1 .cart-header .cart-count {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: #f1f5f9;
                padding: 8px 16px;
                border-radius: 25px;
                font-size: 0.9rem;
                color: #475569;
                margin-top: 16px;
                font-weight: 500;
            }

            .shopglut-cart.template1 .cart-content {
                background: <?php echo esc_attr($settings['table_background_color'] ?? '#ffffff'); ?>;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
                border-radius: 0 0 16px 16px;
            }

            .shopglut-cart.template1 .cart-table-container {
                overflow-x: auto;
                padding: 40px;
                padding-bottom: 20px;
            }

            .shopglut-cart.template1 .cart-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                border-top-left-radius: 15px;
                border-top-right-radius: 15px;
            }

            <?php if ($settings['show_table_header'] ?? true): ?>
            .shopglut-cart.template1 .cart-table th {
                background: <?php echo esc_attr($settings['header_background_color'] ?? '#f3f4f6'); ?>;
                padding: <?php echo esc_attr($settings['header_padding']['top'] ?? '16') . ($settings['header_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['header_padding']['right'] ?? '12') . ($settings['header_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['header_padding']['bottom'] ?? '16') . ($settings['header_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['header_padding']['left'] ?? '12') . ($settings['header_padding']['unit'] ?? 'px'); ?>;
                text-align: left;
                font-weight: <?php echo esc_attr($settings['header_font_weight'] ?? '600'); ?>;
                color: <?php echo esc_attr($settings['header_text_color'] ?? '#374151'); ?>;
                font-size: 0.875rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                border-bottom: <?php echo esc_attr($settings['table_border_width'] ?? '1') . 'px solid ' . ($settings['table_border_color'] ?? '#e5e7eb'); ?>;
                white-space: nowrap;
            }
            <?php else: ?>
            .shopglut-cart.template1 .cart-table th {
                display: none;
            }
            <?php endif; ?>

            .shopglut-cart.template1 .cart-table th:first-child {
                border-top-left-radius: 12px;
            }

            .shopglut-cart.template1 .cart-table th:last-child {
                border-top-right-radius: 12px;
            }

            .shopglut-cart.template1 .cart-table td {
                padding: <?php echo esc_attr($settings['row_padding']['top'] ?? '16') . ($settings['row_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['row_padding']['right'] ?? '12') . ($settings['row_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['row_padding']['bottom'] ?? '16') . ($settings['row_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['row_padding']['left'] ?? '12') . ($settings['row_padding']['unit'] ?? 'px'); ?>;
                border-bottom: <?php echo esc_attr($settings['table_border_width'] ?? '1') . 'px solid ' . ($settings['table_border_color'] ?? '#e5e7eb'); ?>;
            }

            <?php if ($settings['row_hover_effect'] ?? true): ?>
            .shopglut-cart.template1 .cart-table tbody tr:hover {
                background: <?php echo esc_attr($settings['row_hover_color'] ?? '#f8fafc'); ?>;
            }
            <?php endif; ?>

            .shopglut-cart.template1 .product-cell {
                display: flex;
                align-items: center;
                gap: <?php echo esc_attr($settings['product_image_size']['width'] ?? '60') . 'px'; ?>;
            }

            .shopglut-cart.template1 .product-image {
                width: <?php echo esc_attr($settings['product_image_size']['width'] ?? '60') . 'px'; ?>;
                height: <?php echo esc_attr($settings['product_image_size']['height'] ?? '60') . 'px'; ?>;
                background: <?php echo esc_attr($settings['image_background_color'] ?? '#f9fafb'); ?>;
                border-radius: <?php echo esc_attr($settings['image_border_radius'] ?? '8') . 'px'; ?>;
                border: <?php echo esc_attr($settings['image_border_width'] ?? '1') . 'px solid ' . ($settings['image_border_color'] ?? '#e5e7eb'); ?>;
                object-fit: cover;
            }

            .shopglut-cart.template1 .product-title {
                color: <?php echo esc_attr($settings['product_title_color'] ?? '#111827'); ?>;
                font-size: <?php echo esc_attr($settings['product_title_font_size'] ?? '16') . 'px'; ?>;
                font-weight: <?php echo esc_attr($settings['product_title_font_weight'] ?? '600'); ?>;
                margin-bottom: 4px;
            }

            <?php if ($settings['show_product_link'] ?? true): ?>
            .shopglut-cart.template1 .product-title a {
                color: inherit;
                text-decoration: none;
            }
            .shopglut-cart.template1 .product-title a:hover {
                color: <?php echo esc_attr($settings['product_title_color'] ?? '#111827'); ?>;
                text-decoration: underline;
            }
            <?php endif; ?>

            .shopglut-cart.template1 .product-meta {
                color: <?php echo esc_attr($settings['product_meta_color'] ?? '#6b7280'); ?>;
                font-size: <?php echo esc_attr($settings['product_meta_font_size'] ?? '14') . 'px'; ?>;
            }

            .shopglut-cart.template1 .quantity-control {
                display: flex;
                align-items: center;
                gap: 4px;
            }

            .shopglut-cart.template1 .quantity-btn {
                width: 32px;
                height: 32px;
                background: <?php echo esc_attr($settings['quantity_button_color'] ?? '#f3f4f6'); ?>;
                color: <?php echo esc_attr($settings['quantity_button_text_color'] ?? '#374151'); ?>;
                border: none;
                border-radius: <?php echo esc_attr($settings['quantity_control_border_radius'] ?? '6') . 'px'; ?>;
                cursor: pointer;
                font-size: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s;
            }

            .shopglut-cart.template1 .quantity-btn:hover {
                background: <?php echo esc_attr($settings['quantity_button_hover_color'] ?? '#e5e7eb'); ?>;
            }

            .shopglut-cart.template1 .quantity-input {
                width: 50px;
                height: 32px;
                background: <?php echo esc_attr($settings['quantity_input_background'] ?? '#ffffff'); ?>;
                border: 1px solid <?php echo esc_attr($settings['quantity_input_border'] ?? '#d1d5db'); ?>;
                border-radius: <?php echo esc_attr($settings['quantity_control_border_radius'] ?? '6') . 'px'; ?>;
                text-align: center;
                font-size: 14px;
            }

            .shopglut-cart.template1 .price-cell {
                text-align: right;
            }

            .shopglut-cart.template1 .price {
                color: <?php echo esc_attr($settings['price_color'] ?? '#111827'); ?>;
                font-size: <?php echo esc_attr($settings['price_font_size'] ?? '16') . 'px'; ?>;
                font-weight: <?php echo esc_attr($settings['price_font_weight'] ?? '600'); ?>;
            }

            <?php if ($settings['total_price_highlight'] ?? true): ?>
            .shopglut-cart.template1 .total-price {
                color: <?php echo esc_attr($settings['total_price_color'] ?? '#059669'); ?>;
                font-weight: 700;
            }
            <?php endif; ?>

            .shopglut-cart.template1 .cart-footer {
                display: flex;
                justify-content: flex-end;
                gap: 20px;
                margin-top: 40px;
            }

            <?php if ($settings['show_summary_section'] ?? true): ?>
            .shopglut-cart.template1 .cart-summary {
                background: <?php echo esc_attr($settings['summary_background_color'] ?? '#f9fafb'); ?>;
                border: 1px solid <?php echo esc_attr($settings['summary_border_color'] ?? '#e5e7eb'); ?>;
                border-radius: <?php echo esc_attr($settings['summary_border_radius'] ?? '8') . 'px'; ?>;
                padding: <?php echo esc_attr($settings['summary_padding']['top'] ?? '24') . ($settings['summary_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['summary_padding']['right'] ?? '20') . ($settings['summary_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['summary_padding']['bottom'] ?? '24') . ($settings['summary_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['summary_padding']['left'] ?? '20') . ($settings['summary_padding']['unit'] ?? 'px'); ?>;
                min-width: 320px;
            }

            <?php if ($settings['show_summary_header'] ?? true): ?>
            .shopglut-cart.template1 .summary-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: <?php echo esc_attr($settings['row_spacing'] ?? '12') . 'px'; ?>;
                padding-bottom: <?php echo esc_attr($settings['row_spacing'] ?? '12') . 'px'; ?>;
                border-bottom: <?php echo esc_attr($settings['total_row_separator'] ?? '1') . 'px solid ' . ($settings['total_separator_color'] ?? '#e5e7eb'); ?>;
            }

            .shopglut-cart.template1 .summary-title {
                color: <?php echo esc_attr($settings['summary_title_color'] ?? '#111827'); ?>;
                font-size: <?php echo esc_attr($settings['summary_title_font_size'] ?? '20') . 'px'; ?>;
                font-weight: 600;
                flex-grow: 1;
            }

            <?php if ($settings['show_summary_icon'] ?? true): ?>
            .shopglut-cart.template1 .summary-icon {
                color: <?php echo esc_attr($settings['summary_icon_color'] ?? '#3b82f6'); ?>;
                font-size: 24px;
            }
            <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>

            .shopglut-cart.template1 .summary-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: <?php echo esc_attr($settings['row_spacing'] ?? '12') . 'px'; ?> 0;
            }

            .shopglut-cart.template1 .summary-label {
                color: <?php echo esc_attr($settings['row_label_color'] ?? '#6b7280'); ?>;
                font-size: <?php echo esc_attr($settings['row_font_size'] ?? '14') . 'px'; ?>;
            }

            .shopglut-cart.template1 .summary-value {
                color: <?php echo esc_attr($settings['row_value_color'] ?? '#111827'); ?>;
                font-size: <?php echo esc_attr($settings['row_font_size'] ?? '14') . 'px'; ?>;
                font-weight: 500;
            }

            .shopglut-cart.template1 .summary-total {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding-top: <?php echo esc_attr($settings['row_spacing'] ?? '12') . 'px'; ?>;
                margin-top: <?php echo esc_attr($settings['row_spacing'] ?? '12') . 'px'; ?>;
                border-top: <?php echo esc_attr($settings['total_row_separator'] ?? '1') . 'px solid ' . ($settings['total_separator_color'] ?? '#e5e7eb'); ?>;
            }

            .shopglut-cart.template1 .total-label {
                color: <?php echo esc_attr($settings['total_label_color'] ?? '#111827'); ?>;
                font-size: <?php echo esc_attr($settings['total_font_size'] ?? '18') . 'px'; ?>;
                font-weight: <?php echo esc_attr($settings['total_font_weight'] ?? '700'); ?>;
            }

            .shopglut-cart.template1 .total-value {
                color: <?php echo esc_attr($settings['total_value_color'] ?? '#059669'); ?>;
                font-size: <?php echo esc_attr($settings['total_font_size'] ?? '18') . 'px'; ?>;
                font-weight: <?php echo esc_attr($settings['total_font_weight'] ?? '700'); ?>;
            }

            .shopglut-cart.template1 .checkout-button {
                width: 100%;
                background: <?php echo esc_attr($settings['checkout_button_background'] ?? '#059669'); ?>;
                color: <?php echo esc_attr($settings['checkout_button_text_color'] ?? '#ffffff'); ?>;
                padding: <?php echo esc_attr($settings['checkout_button_padding']['top'] ?? '16') . ($settings['checkout_button_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['checkout_button_padding']['right'] ?? '24') . ($settings['checkout_button_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['checkout_button_padding']['bottom'] ?? '16') . ($settings['checkout_button_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['checkout_button_padding']['left'] ?? '24') . ($settings['checkout_button_padding']['unit'] ?? 'px'); ?>;
                border: none;
                border-radius: <?php echo esc_attr($settings['checkout_button_border_radius'] ?? '8') . 'px'; ?>;
                font-size: <?php echo esc_attr($settings['checkout_button_font_size'] ?? '16') . 'px'; ?>;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.2s;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            .shopglut-cart.template1 .checkout-button:hover {
                background: <?php echo esc_attr($settings['checkout_button_hover_background'] ?? '#047857'); ?>;
            }

            <?php if ($settings['show_security_badges'] ?? true): ?>
            .shopglut-cart.template1 .security-badges {
                display: flex;
                flex-direction: <?php echo esc_attr($settings['security_badges_layout'] ?? 'horizontal'); ?>;
                gap: <?php echo esc_attr($settings['security_badge_spacing'] ?? '8') . 'px'; ?>;
                margin-top: 20px;
                justify-content: center;
            }

            .shopglut-cart.template1 .security-badge {
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 12px;
            }

            <?php if ($settings['show_ssl_badge'] ?? true): ?>
            .shopglut-cart.template1 .ssl-badge {
                color: <?php echo esc_attr($settings['ssl_badge_text_color'] ?? '#6b7280'); ?>;
            }
            .shopglut-cart.template1 .ssl-badge i {
                color: <?php echo esc_attr($settings['ssl_badge_icon_color'] ?? '#059669'); ?>;
            }
            <?php endif; ?>

            <?php if ($settings['show_payment_badge'] ?? true): ?>
            .shopglut-cart.template1 .payment-badge {
                color: <?php echo esc_attr($settings['payment_badge_text_color'] ?? '#6b7280'); ?>;
            }
            .shopglut-cart.template1 .payment-badge i {
                color: <?php echo esc_attr($settings['payment_badge_icon_color'] ?? '#3b82f6'); ?>;
            }
            <?php endif; ?>

            <?php if ($settings['show_return_badge'] ?? true): ?>
            .shopglut-cart.template1 .return-badge {
                color: <?php echo esc_attr($settings['return_badge_text_color'] ?? '#6b7280'); ?>;
            }
            .shopglut-cart.template1 .return-badge i {
                color: <?php echo esc_attr($settings['return_badge_icon_color'] ?? '#f59e0b'); ?>;
            }
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($settings['show_discount_section'] ?? true): ?>
            .shopglut-cart.template1 .discount-section {
                background: <?php echo esc_attr($settings['discount_section_background'] ?? '#ffffff'); ?>;
                border: 1px solid <?php echo esc_attr($settings['discount_section_border'] ?? '#e5e7eb'); ?>;
                border-radius: 8px;
                padding: <?php echo esc_attr($settings['discount_section_padding']['top'] ?? '20') . ($settings['discount_section_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['discount_section_padding']['right'] ?? '20') . ($settings['discount_section_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['discount_section_padding']['bottom'] ?? '20') . ($settings['discount_section_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['discount_section_padding']['left'] ?? '20') . ($settings['discount_section_padding']['unit'] ?? 'px'); ?>;
                margin-bottom: 20px;
            }

            <?php if ($settings['show_discount_title'] ?? true): ?>
            .shopglut-cart.template1 .discount-title {
                color: <?php echo esc_attr($settings['discount_title_color'] ?? '#111827'); ?>;
                font-size: <?php echo esc_attr($settings['discount_title_font_size'] ?? '18') . 'px'; ?>;
                font-weight: 600;
                margin-bottom: 16px;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            <?php if ($settings['show_discount_icon'] ?? true): ?>
            .shopglut-cart.template1 .discount-title i {
                color: <?php echo esc_attr($settings['discount_icon_color'] ?? '#3b82f6'); ?>;
            }
            <?php endif; ?>
            <?php endif; ?>

            .shopglut-cart.template1 .coupon-form {
                display: flex;
                gap: 8px;
            }

            .shopglut-cart.template1 .coupon-input {
                flex: 1;
                padding: <?php echo esc_attr($settings['coupon_input_padding']['top'] ?? '12') . ($settings['coupon_input_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['coupon_input_padding']['right'] ?? '16') . ($settings['coupon_input_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['coupon_input_padding']['bottom'] ?? '12') . ($settings['coupon_input_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['coupon_input_padding']['left'] ?? '16') . ($settings['coupon_input_padding']['unit'] ?? 'px'); ?>;
                background: <?php echo esc_attr($settings['coupon_input_background'] ?? '#ffffff'); ?>;
                border: 1px solid <?php echo esc_attr($settings['coupon_input_border'] ?? '#d1d5db'); ?>;
                border-radius: <?php echo esc_attr($settings['coupon_input_border_radius'] ?? '6') . 'px'; ?>;
                font-size: 14px;
                color: <?php echo esc_attr($settings['coupon_input_text_color'] ?? '#374151'); ?>;
            }

            .shopglut-cart.template1 .apply-button {
                padding: <?php echo esc_attr($settings['apply_button_padding']['top'] ?? '12') . ($settings['apply_button_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['apply_button_padding']['right'] ?? '20') . ($settings['apply_button_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['apply_button_padding']['bottom'] ?? '12') . ($settings['apply_button_padding']['unit'] ?? 'px'); ?> <?php echo esc_attr($settings['apply_button_padding']['left'] ?? '20') . ($settings['apply_button_padding']['unit'] ?? 'px'); ?>;
                background: <?php echo esc_attr($settings['apply_button_background'] ?? '#3b82f6'); ?>;
                color: <?php echo esc_attr($settings['apply_button_text_color'] ?? '#ffffff'); ?>;
                border: none;
                border-radius: <?php echo esc_attr($settings['apply_button_border_radius'] ?? '6') . 'px'; ?>;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.2s;
            }

            .shopglut-cart.template1 .apply-button:hover {
                background: <?php echo esc_attr($settings['apply_button_hover_background'] ?? '#2563eb'); ?>;
            }

            <?php if ($settings['show_coupon_messages'] ?? true): ?>
            .shopglut-cart.template1 .coupon-message {
                margin-top: 12px;
                padding: 12px;
                border-radius: 6px;
                font-size: <?php echo esc_attr($settings['message_font_size'] ?? '14') . 'px'; ?>;
            }

            .shopglut-cart.template1 .coupon-message.success {
                background: #dcfce7;
                color: <?php echo esc_attr($settings['success_message_color'] ?? '#059669'); ?>;
            }

            .shopglut-cart.template1 .coupon-message.error {
                background: #fee2e2;
                color: <?php echo esc_attr($settings['error_message_color'] ?? '#dc2626'); ?>;
            }
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($settings['show_continue_shopping'] ?? true): ?>
            .shopglut-cart.template1 .continue-shopping {
                text-align: center;
                margin-top: 24px;
            }

            .shopglut-cart.template1 .continue-shopping a {
                color: <?php echo esc_attr($settings['continue_link_color'] ?? '#3b82f6'); ?>;
                font-size: <?php echo esc_attr($settings['continue_link_font_size'] ?? '14') . 'px'; ?>;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-weight: 500;
            }

            .shopglut-cart.template1 .continue-shopping a:hover {
                color: <?php echo esc_attr($settings['continue_link_hover_color'] ?? '#2563eb'); ?>;
                text-decoration: underline;
            }
            <?php endif; ?>

            @media (max-width: 768px) {
                .shopglut-cart.template1 .cart-footer {
                    flex-direction: column;
                }

                .shopglut-cart.template1 .cart-summary {
                    width: 100%;
                    min-width: auto;
                }

                .shopglut-cart.template1 .product-image {
                    width: 50px;
                    height: 50px;
                }

                .shopglut-cart.template1 .product-cell {
                    min-width: 120px;
                    gap: 12px;
                }
            }

            @media (max-width: 480px) {
                .shopglut-cart.template1 .cart-container {
                    margin: 5px;
                    padding: 0 8px;
                }

                .shopglut-cart.template1 .cart-header {
                    padding: 20px 12px;
                    border-radius: 8px 8px 0;
                }

                .shopglut-cart.template1 .cart-header h1 {
                    font-size: 1.5rem;
                }

                .shopglut-cart.template1 .product-image {
                    width: 40px;
                    height: 40px;
                }

                .shopglut-cart.template1 .product-cell {
                    min-width: 100px;
                    gap: 10px;
                }

                .shopglut-cart.template1 .security-badges {
                    flex-direction: column;
                    align-items: center;
                }
            }
        </style>
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

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $layout_data = $wpdb->get_row(
            $wpdb->prepare("SELECT layout_settings FROM {$wpdb->prefix}shopglut_cartpage_layouts WHERE id = %d", $layout_id)
        );

        if ($layout_data && !empty($layout_data->layout_settings)) {
            $settings = maybe_unserialize($layout_data->layout_settings);

            // Handle different data structures for compatibility
            if (isset($settings['shopg_cartpage_settings_template1'])) {
                $template_data = $settings['shopg_cartpage_settings_template1'];

                // Check if cart-page-settings is nested inside (tabbed field structure)
                if (isset($template_data['cart-page-settings'])) {
                    return $this->flattenSettings($template_data['cart-page-settings']);
                }

                // Otherwise, data is already flat or in a different structure
                return $this->flattenSettings($template_data);
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
                    // If value is an array, check if it's a nested fieldset or a slider field
                    if (is_array($setting_value)) {
                        // Check if it's a slider field with self-referencing key
                        if (isset($setting_value[$setting_key])) {
                            $flat_settings[$setting_key] = $setting_value[$setting_key];
                        } elseif (isset($setting_value['value']) && isset($setting_value['unit'])) {
                            // Handle slider fields with value/unit structure
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
            'ssl_badge_icon' => 'fas fa-shield-alt',
            'ssl_badge_text_color' => '#6b7280',
            'ssl_badge_icon_color' => '#059669',
            'ssl_badge_font_size' => 12,
            'show_payment_badge' => true,
            'payment_badge_text' => 'Safe Payment',
            'payment_badge_icon' => 'fas fa-credit-card',
            'payment_badge_text_color' => '#6b7280',
            'payment_badge_icon_color' => '#3b82f6',
            'payment_badge_font_size' => 12,
            'show_return_badge' => true,
            'return_badge_text' => '30-Day Return',
            'return_badge_icon' => 'fas fa-undo',
            'return_badge_text_color' => '#6b7280',
            'return_badge_icon_color' => '#f59e0b',
            'return_badge_font_size' => 12,

            // Discount Section
            'show_discount_section' => true,
            'discount_section_background' => '#ffffff',
            'discount_section_border' => '#e5e7eb',
            'discount_section_padding' => array('top' => '20', 'right' => '20', 'bottom' => '20', 'left' => '20', 'unit' => 'px'),
            'show_discount_title' => true,
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
            'show_continue_icon' => true,
            'continue_link_color' => '#3b82f6',
            'continue_link_hover_color' => '#2563eb',
            'continue_link_font_size' => 14,
        );
    }
}
