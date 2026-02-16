<?php

/**
 * Plugin Update Widget
 * Shows sticky notification widget on right side when related plugins have updates
 */

class ShopGlut_PluginUpdateWidget {

    private $related_plugins = array(
        'wishglut' => array(
            'name' => 'WishGlut',
            'repo' => 'appglut/wishglut',
            'url' => 'https://github.com/appglut/wishglut',
            'icon' => '🎁',
            'basename' => 'wishglut/wishglut.php'
        ),
        'checkoutglut' => array(
            'name' => 'CheckoutGlut',
            'repo' => 'appglut/checkoutglut',
            'url' => 'https://github.com/appglut/checkoutglut',
            'icon' => '🛒',
            'basename' => 'checkoutglut/checkoutglut.php'
        ),
        'shortcodeglut' => array(
            'name' => 'ShortcodeGlut',
            'repo' => 'appglut/shortcodeglut',
            'url' => 'https://github.com/appglut/shortcodeglut',
            'icon' => '⚡',
            'basename' => 'shortcodeglut/shortcodeglut.php'
        )
    );

    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_footer', array($this, 'render_widget'));
        add_action('wp_ajax_shopglut_dismiss_widget_update', array($this, 'dismiss_update'));
        add_action('wp_ajax_shopglut_widget_check_updates', array($this, 'check_updates'));
    }

    public function enqueue_scripts() {
        // Only show on ShopGlut pages
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'shopglut') === false) {
            return;
        }

        wp_enqueue_script('shopglut-update-widget', plugins_url('shopglut.js', __FILE__), array('jquery'), SHOPGLUT_VERSION, true);
    }

    public function render_widget() {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'shopglut') === false) {
            return;
        }

        $versions = get_option('shopglut_related_plugins_versions', array());
        $dismissed = get_option('shopglut_widget_dismissed', array());

        // Filter out dismissed updates
        $available_updates = array();
        foreach ($versions as $slug => $plugin) {
            if (!in_array($slug, $dismissed)) {
                // Check if plugin is installed
                $is_installed = $this->is_plugin_installed($plugin['name']);
                $current_version = $is_installed ? $this->get_plugin_version($plugin['name']) : null;
                $latest_version = $plugin['version'];

                // Only show if installed AND has update
                if ($is_installed && $current_version && version_compare($current_version, $latest_version, '<')) {
                    $available_updates[$slug] = array(
                        'name' => $plugin['name'],
                        'icon' => $plugin['icon'],
                        'current_version' => $current_version,
                        'latest_version' => $latest_version,
                        'url' => $plugin['url'],
                        'zip_url' => $plugin['zip_url'],
                        'basename' => $plugin['basename']
                    );
                }
            }
        }

        if (empty($available_updates)) {
            return;
        }

        ?>
        <div id="shopglut-update-widget" class="shopglut-update-widget">
            <div class="shopglut-widget-header">
                <span class="shopglut-widget-icon">🔔</span>
                <span class="shopglut-widget-badge"><?php echo count($available_updates); ?></span>
            </div>

            <div class="shopglut-widget-content" style="display: none;">
                <div class="shopglut-widget-title">Plugin Updates Available</div>

                <?php foreach ($available_updates as $slug => $plugin): ?>
                    <div class="shopglut-widget-item" data-plugin="<?php echo esc_attr($slug); ?>">
                        <div class="shopglut-widget-plugin">
                            <span class="shopglut-widget-plugin-icon"><?php echo esc_html($plugin['icon']); ?></span>
                            <div class="shopglut-widget-plugin-info">
                                <div class="shopglut-widget-plugin-name"><?php echo esc_html($plugin['name']); ?></div>
                                <div class="shopglut-widget-plugin-versions">
                                    <span class="current">v<?php echo esc_html($plugin['current_version']); ?></span>
                                    <span class="arrow">→</span>
                                    <span class="latest">v<?php echo esc_html($plugin['latest_version']); ?></span>
                                </div>
                            </div>
                            <button class="shopglut-widget-dismiss" data-plugin="<?php echo esc_attr($slug); ?>">✕</button>
                        </div>
                        <div class="shopglut-widget-actions">
                            <a href="<?php echo esc_url($plugin['url']); ?>" target="_blank" class="shopglut-widget-view">
                                <span class="dashicons dashicons-external"></span> View
                            </a>
                            <a href="<?php echo esc_url($plugin['zip_url']); ?>" target="_blank" class="shopglut-widget-download">
                                <span class="dashicons dashicons-download"></span> Download
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="shopglut-widget-footer">
                    <button type="button" class="shopglut-widget-refresh" id="shopglut-widget-refresh-btn">
                        <span class="dashicons dashicons-update-alt"></span> Check Updates
                    </button>
                    <span class="spinner" style="display: none;"></span>
                </div>
            </div>
        </div>

        <style>
        .shopglut-update-widget {
            position: fixed;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            z-index: 99999;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
        }

        .shopglut-widget-header {
            position: relative;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .shopglut-widget-header:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.5);
        }

        .shopglut-widget-icon {
            font-size: 24px;
        }

        .shopglut-widget-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            font-size: 11px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 10px;
            border: 2px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .shopglut-widget-content {
            position: absolute;
            right: 60px;
            top: 0;
            width: 280px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .shopglut-widget-content.show {
            opacity: 1;
            visibility: visible;
        }

        .shopglut-widget-title {
            padding: 15px 15px 10px;
            font-size: 14px;
            font-weight: 600;
            color: #1d2327;
            border-bottom: 1px solid #f0f0f1;
        }

        .shopglut-widget-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f1;
            transition: background 0.2s ease;
        }

        .shopglut-widget-item:last-child {
            border-bottom: none;
        }

        .shopglut-widget-item:hover {
            background: #f8f9fa;
        }

        .shopglut-widget-plugin {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .shopglut-widget-plugin-icon {
            font-size: 24px;
        }

        .shopglut-widget-plugin-info {
            flex: 1;
        }

        .shopglut-widget-plugin-name {
            font-size: 13px;
            font-weight: 600;
            color: #1d2327;
        }

        .shopglut-widget-plugin-versions {
            font-size: 12px;
            color: #646970;
            margin-top: 2px;
        }

        .shopglut-widget-plugin-versions .current {
            color: #646970;
        }

        .shopglut-widget-plugin-versions .latest {
            color: #00a32a;
            font-weight: 600;
        }

        .shopglut-widget-plugin-versions .arrow {
            margin: 0 4px;
            color: #2271b1;
        }

        .shopglut-widget-dismiss {
            background: none;
            border: none;
            color: #646970;
            cursor: pointer;
            padding: 5px;
            font-size: 16px;
            line-height: 1;
            transition: color 0.2s ease;
        }

        .shopglut-widget-dismiss:hover {
            color: #d63638;
        }

        .shopglut-widget-actions {
            display: flex;
            gap: 8px;
            margin-top: 5px;
        }

        .shopglut-widget-actions a {
            text-decoration: none;
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s ease;
        }

        .shopglut-widget-view {
            background: #f0f0f1;
            color: #1d2327;
        }

        .shopglut-widget-view:hover {
            background: #e0e0e1;
        }

        .shopglut-widget-download {
            background: #2271b1;
            color: white;
        }

        .shopglut-widget-download:hover {
            background: #135e96;
        }

        .shopglut-widget-footer {
            padding: 10px 15px;
            background: #f8f9fa;
            border-top: 1px solid #f0f0f1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .shopglut-widget-refresh {
            background: #fff;
            border: 1px solid #ddd;
            color: #1d2327;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }

        .shopglut-widget-refresh:hover {
            background: #f0f0f1;
            border-color: #ccc;
        }

        .shopglut-widget-refresh .dashicons {
            font-size: 14px;
        }

        .shopglut-widget-refresh:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .shopglut-widget-footer .spinner {
            float: none;
            margin: 0;
        }

        @media (max-width: 768px) {
            .shopglut-update-widget {
                top: auto;
                bottom: 20px;
                right: 20px;
                transform: none;
            }

            .shopglut-widget-content {
                top: auto;
                bottom: 60px;
                right: 0;
            }
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            var $widget = $('#shopglut-update-widget');
            var $header = $widget.find('.shopglut-widget-header');
            var $content = $widget.find('.shopglut-widget-content');

            // Toggle widget content
            $header.on('click', function(e) {
                e.stopPropagation();
                $content.toggleClass('show');
            });

            // Close when clicking outside
            $(document).on('click', function(e) {
                if (!$widget.is(e.target) && !$widget.has(e.target).length) {
                    $content.removeClass('show');
                }
            });

            // Dismiss individual update
            $('.shopglut-widget-dismiss').on('click', function(e) {
                e.stopPropagation();
                var $item = $(this).closest('.shopglut-widget-item');
                var plugin = $(this).data('plugin');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'shopglut_dismiss_widget_update',
                        plugin: plugin,
                        nonce: '<?php echo wp_create_nonce('shopglut_widget_dismiss'); ?>'
                    },
                    success: function() {
                        $item.fadeOut(300, function() {
                            $(this).remove();
                            // Update badge count
                            var count = $('.shopglut-widget-item').length;
                            if (count === 1) {
                                // Last item being removed
                                $('.shopglut-widget-badge').text('0');
                                setTimeout(function() {
                                    $widget.fadeOut(300);
                                }, 500);
                            } else {
                                $('.shopglut-widget-badge').text(count - 1);
                            }
                        });
                    }
                });
            });

            // Check for updates
            $('#shopglut-widget-refresh-btn').on('click', function() {
                var $btn = $(this);
                var $spinner = $btn.next('.spinner');

                $btn.prop('disabled', true);
                $spinner.css('display', 'inline-block');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'shopglut_widget_check_updates',
                        nonce: '<?php echo wp_create_nonce('shopglut_widget_check'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $spinner.css('display', 'none');
                    }
                });
            });
        });
        </script>
        <?php
    }

    public function dismiss_update() {
        check_ajax_referer('shopglut_widget_dismiss', 'nonce');

        $plugin = isset($_POST['plugin']) ? sanitize_text_field($_POST['plugin']) : '';

        if (!$plugin || !isset($this->related_plugins[$plugin])) {
            wp_send_json_error();
        }

        $dismissed = get_option('shopglut_widget_dismissed', array());
        $dismissed[] = $plugin;
        update_option('shopglut_widget_dismissed', $dismissed);

        wp_send_json_success();
    }

    public function check_updates() {
        check_ajax_referer('shopglut_widget_check', 'nonce');

        // Clear dismissed list
        delete_option('shopglut_widget_dismissed');

        // Force check updates
        if (class_exists('ShopGlut_PluginUpdateChecker')) {
            $checker = new ShopGlut_PluginUpdateChecker();
            // Force check by clearing cache
            delete_transient('shopglut_related_plugins_versions_last_check');
        }

        wp_send_json_success();
    }

    private function is_plugin_installed($plugin_name) {
        $plugin_map = array(
            'WishGlut' => 'wishglut/wishglut.php',
            'CheckoutGlut' => 'checkoutglut/checkoutglut.php',
            'ShortcodeGlut' => 'shortcodeglut/shortcodeglut.php'
        );

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();
        $basename = isset($plugin_map[$plugin_name]) ? $plugin_map[$plugin_name] : '';

        return $basename && isset($plugins[$basename]);
    }

    private function get_plugin_version($plugin_name) {
        $plugin_map = array(
            'WishGlut' => 'wishglut/wishglut.php',
            'CheckoutGlut' => 'checkoutglut/checkoutglut.php',
            'ShortcodeGlut' => 'shortcodeglut/shortcodeglut.php'
        );

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();
        $basename = isset($plugin_map[$plugin_name]) ? $plugin_map[$plugin_name] : '';

        if ($basename && isset($plugins[$basename])) {
            return $plugins[$basename]['Version'];
        }

        return null;
    }
}

// Initialize the widget
new ShopGlut_PluginUpdateWidget();
