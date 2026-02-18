<?php

/**
 * Plugin Update Checker for Related Plugins
 * Public Version - No configuration needed by users
 *
 * The update server URL is hardcoded below.
 * Users don't need to configure anything!
 */

class ShopGlut_PluginUpdateChecker {

    // YOUR UPDATE SERVER - Change this to your domain
    private $update_server_url = 'https://updates.appglut.com/plugins';

    private $related_plugins = array(
        'wishglut' => array(
            'name' => 'WishGlut',
            'basename' => 'wishglut/wishglut.php',
            'icon' => '🎁',
            'description' => 'Advanced wishlist plugin for WooCommerce with multiple wishlist lists'
        ),
        'checkoutglut' => array(
            'name' => 'CheckoutGlut',
            'basename' => 'checkoutglut/checkoutglut.php',
            'icon' => '🛒',
            'description' => 'Customize WooCommerce checkout page with drag & drop builder'
        ),
        'shortcodeglut' => array(
            'name' => 'ShortcodeGlut',
            'basename' => 'shortcodeglut/shortcodeglut.php',
            'icon' => '⚡',
            'description' => 'Powerful shortcode builder for WordPress with visual editor'
        ),
        'product-details-glut' => array(
            'name' => 'ProductDetailsGlut',
            'basename' => 'product-details-glut/product-details-glut.php',
            'icon' => '📦',
            'description' => 'Beautiful WooCommerce single product page builder with 7+ templates'
        )
    );

    private $option_name = 'shopglut_related_plugins_versions';
    private $cache_time = DAY_IN_SECONDS; // Check once per day

    public function __construct() {
        // Allow override via wp-config.php (optional, for testing)
        if (defined('SHOPGLUT_UPDATE_SERVER_URL')) {
            $this->update_server_url = rtrim(SHOPGLUT_UPDATE_SERVER_URL, '/');
        } else {
            $this->update_server_url = rtrim($this->update_server_url, '/');
        }

        // Scheduled update check (runs daily via WordPress cron)
        add_action('shopglut_scheduled_plugin_updates', array($this, 'scheduled_check'));

        // Run an initial check if no data exists yet
        add_action('admin_init', array($this, 'maybe_initial_check'));

        // Admin notices and AJAX handlers
        add_action('admin_notices', array($this, 'show_update_notices'));
        add_action('wp_ajax_shopglut_dismiss_plugin_update', array($this, 'dismiss_update'));
        add_action('wp_ajax_shopglut_force_check_updates', array($this, 'ajax_force_check'));
        add_action('wp_ajax_shopglut_update_plugin', array($this, 'ajax_update_plugin'));
    }

    /**
     * Run initial check only if no version data exists yet
     */
    public function maybe_initial_check() {
        $versions = get_option($this->option_name, array());

        if (empty($versions)) {
            $this->check_for_updates();
        }
    }

    /**
     * Scheduled check - called by WordPress cron
     */
    public function scheduled_check() {
        $last_check = get_transient($this->option_name . '_last_check');

        if ($last_check && $last_check > time() - $this->cache_time) {
            return;
        }

        $this->check_for_updates();
    }

    /**
     * Check for updates from self-hosted server
     */
    public function check_for_updates() {
        $versions = get_option($this->option_name, array());

        $this->check_from_self_hosted($versions);

        update_option($this->option_name, $versions);
        set_transient($this->option_name . '_last_check', time(), $this->cache_time);
    }

    /**
     * Check for updates from self-hosted server (public access)
     * Reads version JSON files from appglutplugins folder
     */
    private function check_from_self_hosted(&$versions) {
        foreach ($this->related_plugins as $slug => $plugin) {
            $json_url = $this->update_server_url . '/' . $slug . '-version.json';

            $response = wp_remote_get($json_url, array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'User-Agent' => 'ShopGlut-Plugin-Checker/' . get_bloginfo('version')
                ),
                'timeout' => 15
            ));

            if (is_wp_error($response)) {
                continue;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (!isset($data['version'])) {
                continue;
            }

            $versions[$slug] = array(
                'name' => $data['name'] ?? $plugin['name'],
                'version' => $data['version'],
                'published_at' => $data['last_updated'] ?? '',
                'url' => $data['homepage'] ?? '',
                'icon' => $plugin['icon'],
                'zip_url' => $this->update_server_url . '/' . $slug . '.zip',
                'description' => $plugin['description']
            );
        }
    }

    /**
     * Show update notices in admin
     */
    public function show_update_notices() {
        if (!$this->is_shopglut_admin_page()) {
            return;
        }

        $versions = get_option($this->option_name, array());
        $dismissed = get_option($this->option_name . '_dismissed', array());

        if (empty($versions)) {
            return;
        }

        foreach ($versions as $slug => $plugin) {
            if (in_array($slug, $dismissed)) {
                continue;
            }

            $this->render_notice($plugin, $slug);
        }
    }

    /**
     * Render single notice
     */
    private function render_notice($plugin, $slug) {
        $notice_id = esc_attr($slug);
        $dismiss_nonce = wp_create_nonce('shopglut_dismiss_update');

        // Get current installed version if plugin is installed
        $plugin_basename = $this->related_plugins[$slug]['basename'] ?? '';
        $current_version = '';

        if ($plugin_basename && function_exists('get_plugins')) {
            $plugins = get_plugins();
            if (isset($plugins[$plugin_basename])) {
                $current_version = $plugins[$plugin_basename]['Version'];
            }
        }

        // Only show if update is available
        if ($current_version && version_compare($current_version, $plugin['version'], '>=')) {
            return;
        }

        ?>
        <div class="notice notice-info is-dismissible shopglut-plugin-update" data-plugin="<?php echo $notice_id; ?>">
            <p>
                <strong><?php echo esc_html($plugin['icon'] . ' ' . $plugin['name']); ?></strong>
                -
                <?php if ($current_version) : ?>
                    <?php
                    printf(
                        esc_html__('You have version %1$s installed. New version %2$s is available!', 'shopglut'),
                        esc_html($current_version),
                        esc_html($plugin['version'])
                    );
                    ?>
                <?php else : ?>
                    <?php
                    printf(
                        esc_html__('Version %s is now available!', 'shopglut'),
                        esc_html($plugin['version'])
                    );
                    ?>
                <?php endif; ?>
                <a href="<?php echo esc_url($plugin['url']); ?>" target="_blank" class="button button-small" style="margin-left: 10px;">
                    <?php esc_html_e('View Release', 'shopglut'); ?>
                </a>
            </p>
        </div>

        <script>
        jQuery(document).on('click', '.shopglut-plugin-update .notice-dismiss', function() {
            var plugin = jQuery(this).closest('.shopglut-plugin-update').data('plugin');
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'shopglut_dismiss_plugin_update',
                    plugin: plugin,
                    nonce: '<?php echo $dismiss_nonce; ?>'
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Handle AJAX dismiss request
     */
    public function dismiss_update() {
        check_ajax_referer('shopglut_dismiss_update', 'nonce');

        $plugin = isset($_POST['plugin']) ? sanitize_text_field($_POST['plugin']) : '';

        if (!$plugin || !isset($this->related_plugins[$plugin])) {
            wp_send_json_error();
        }

        $dismissed = get_option($this->option_name . '_dismissed', array());
        $dismissed[] = $plugin;
        update_option($this->option_name . '_dismissed', $dismissed);

        wp_send_json_success();
    }

    /**
     * Handle AJAX force check updates request
     */
    public function ajax_force_check() {
        check_ajax_referer('shopglut_check_updates', 'nonce');

        // Clear the cache
        delete_transient($this->option_name . '_last_check');

        // Clear dismissed list to show updates again
        delete_option($this->option_name . '_dismissed');

        // Force check
        $versions = get_option($this->option_name, array());
        $this->check_from_self_hosted($versions);

        update_option($this->option_name, $versions);
        set_transient($this->option_name . '_last_check', time(), $this->cache_time);

        wp_send_json_success(array(
            'count' => count($versions),
            'plugins' => array_keys($versions)
        ));
    }

    /**
     * Check if we're on a shopglut admin page
     */
    private function is_shopglut_admin_page() {
        $screen = get_current_screen();

        if (!$screen) {
            return false;
        }

        if (strpos($screen->id, 'shopglut') !== false || strpos($screen->id, 'shopglut-pro') !== false) {
            return true;
        }

        if (strpos($screen->id, 'woocommerce') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Force refresh check
     */
    public function force_check() {
        delete_transient($this->option_name . '_last_check');
        $this->check_for_updates();
    }

    /**
     * Get current versions for display
     */
    public function get_versions() {
        return get_option($this->option_name, array());
    }

    /**
     * Handle AJAX update plugin request
     */
    public function ajax_update_plugin() {
        $plugin = isset($_POST['plugin']) ? sanitize_text_field($_POST['plugin']) : '';
        $zip_url = isset($_POST['zip_url']) ? esc_url_raw($_POST['zip_url']) : '';
        $slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';

        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'shopglut_update_plugin_' . $slug)) {
            wp_send_json_error('Invalid nonce');
        }

        // Check permissions
        if (!current_user_can('update_plugins')) {
            wp_send_json_error('You do not have permission to update plugins');
        }

        // Validate plugin
        if (!$plugin || !$this->is_valid_plugin($plugin)) {
            wp_send_json_error('Invalid plugin');
        }

        // Download and update the plugin
        $result = $this->update_plugin_from_zip($plugin, $zip_url);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Clear version cache
        delete_option($this->option_name);
        delete_transient($this->option_name . '_last_check');
        delete_option($this->option_name . '_dismissed');
        wp_cache_flush();

        wp_send_json_success(array(
            'message' => 'Plugin updated successfully',
            'plugin' => $plugin
        ));
    }

    /**
     * Update plugin from ZIP URL
     */
    private function update_plugin_from_zip($plugin_basename, $zip_url) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skins.php';

        $was_active = is_plugin_active($plugin_basename);

        $skin = new WP_Ajax_Upgrader_Skin(array(
            'nonce' => 'shopglut_update_plugin',
            'url' => admin_url('admin-ajax.php?action=shopglut_update_plugin')
        ));

        $upgrader = new Plugin_Upgrader($skin);

        $result = $upgrader->install($zip_url, array(
            'overwrite_package' => true,
            'hook_extra' => array(
                'type' => 'plugin',
                'plugin' => $plugin_basename
            )
        ));

        if (is_wp_error($result)) {
            return $result;
        }

        if ($result === false) {
            return new WP_Error('update_failed', 'Plugin update failed');
        }

        if ($was_active) {
            $reactivate = activate_plugin($plugin_basename);
            if (is_wp_error($reactivate)) {
                error_log('ShopGlut: Failed to reactivate plugin after update: ' . $reactivate->get_error_message());
            }
        }

        wp_cache_flush();

        return true;
    }

    /**
     * Check if plugin is valid and installed
     */
    private function is_valid_plugin($plugin_basename) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugins = get_plugins();
        return isset($plugins[$plugin_basename]);
    }
}

// Initialize the update checker
new ShopGlut_PluginUpdateChecker();

// Schedule plugin update checks on activation
register_activation_hook(SHOPGLUT_FILE, 'shopglut_schedule_update_checks');
register_deactivation_hook(SHOPGLUT_FILE, 'shopglut_clear_update_checks');

function shopglut_schedule_update_checks() {
    if (!wp_next_scheduled('shopglut_scheduled_plugin_updates')) {
        wp_schedule_event(time(), 'daily', 'shopglut_scheduled_plugin_updates');
    }

    if (class_exists('ShopGlut_PluginUpdateChecker')) {
        $checker = new ShopGlut_PluginUpdateChecker();
        $checker->check_for_updates();
    }
}

function shopglut_clear_update_checks() {
    wp_clear_scheduled_hook('shopglut_scheduled_plugin_updates');
}
