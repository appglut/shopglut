<?php

/**
 * Plugin Update Checker for Related Plugins
 * Checks GitHub releases for wishglut, checkoutglut, shortcodeglut
 */

class ShopGlut_PluginUpdateChecker {

    private $related_plugins = array(
        'wishglut' => array(
            'name' => 'WishGlut',
            'repo' => 'appglut/wishglut',
            'url' => 'https://github.com/appglut/wishglut',
            'icon' => '🎁'
        ),
        'checkoutglut' => array(
            'name' => 'CheckoutGlut',
            'repo' => 'appglut/checkoutglut',
            'url' => 'https://github.com/appglut/checkoutglut',
            'icon' => '🛒'
        ),
        'shortcodeglut' => array(
            'name' => 'ShortcodeGlut',
            'repo' => 'appglut/shortcodeglut',
            'url' => 'https://github.com/appglut/shortcodeglut',
            'icon' => '⚡'
        )
    );

    private $option_name = 'shopglut_related_plugins_versions';
    private $cache_time = DAY_IN_SECONDS; // Check once per day

    public function __construct() {
        add_action('admin_init', array($this, 'check_for_updates'));
        add_action('admin_notices', array($this, 'show_update_notices'));
        add_action('wp_ajax_shopglut_dismiss_plugin_update', array($this, 'dismiss_update'));
        add_action('wp_ajax_shopglut_force_check_updates', array($this, 'ajax_force_check'));
        add_action('wp_ajax_shopglut_update_plugin', array($this, 'ajax_update_plugin'));
    }

    /**
     * Check for updates from GitHub API
     */
    public function check_for_updates() {
        // Only check on the main shopglut admin page
        if (!$this->is_shopglut_admin_page()) {
            return;
        }

        $last_check = get_transient($this->option_name . '_last_check');

        if ($last_check && $last_check > time() - $this->cache_time) {
            return; // Already checked recently
        }

        $versions = get_option($this->option_name, array());

        foreach ($this->related_plugins as $slug => $plugin) {
            $release_info = $this->get_github_release($plugin['repo']);

            if ($release_info && !isset($release_info['message'])) {
                // Use tag_name as version (e.g., "v1.0.0" or "latest")
                $tag_name = isset($release_info['tag_name']) ? $release_info['tag_name'] : 'latest';

                // If tag is "latest", use date as fallback version
                if ($tag_name === 'latest') {
                    $published_at = $release_info['published_at'];
                    $version = $this->format_version_date($published_at);
                } else {
                    // Remove 'v' prefix if present
                    $version = ltrim($tag_name, 'v');
                }

                $versions[$slug] = array(
                    'name' => $plugin['name'],
                    'version' => $version,
                    'tag_name' => $tag_name,
                    'published_at' => $release_info['published_at'],
                    'url' => $plugin['url'] . '/releases/tag/' . $tag_name,
                    'icon' => $plugin['icon'],
                    'zip_url' => $this->get_zip_url($release_info)
                );
            }
        }

        update_option($this->option_name, $versions);
        set_transient($this->option_name . '_last_check', time(), $this->cache_time);
    }

    /**
     * Get release info from GitHub API
     */
    private function get_github_release($repo) {
        $url = "https://api.github.com/repos/{$repo}/releases/latest";

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'ShopGlut-Plugin-Checker'
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    /**
     * Get ZIP download URL from release
     */
    private function get_zip_url($release) {
        if (isset($release['assets'][0]['browser_download_url'])) {
            return $release['assets'][0]['browser_download_url'];
        }
        return $release['html_url'];
    }

    /**
     * Format published date as version
     */
    private function format_version_date($date) {
        $timestamp = strtotime($date);
        return date('Y.m.d', $timestamp);
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
        $plugin_basename = $this->get_plugin_basename($slug);
        $current_version = '';

        if ($plugin_basename && function_exists('get_plugins')) {
            $plugins = get_plugins();
            if (isset($plugins[$plugin_basename])) {
                $current_version = $plugins[$plugin_basename]['Version'];
            }
        }

        ?>
        <div class="notice notice-info is-dismissible shopglut-plugin-update" data-plugin="<?php echo $notice_id; ?>">
            <p>
                <strong><?php echo esc_html($plugin['icon'] . ' ' . $plugin['name']); ?></strong>
                -
                <?php if ($current_version) : ?>
                    <?php
                    printf(
                        /* translators: 1: current version, 2: new version */
                        esc_html__('You have version %1$s installed. New version %2$s is available!', 'shopglut'),
                        esc_html($current_version),
                        esc_html($plugin['version'])
                    );
                    ?>
                <?php else : ?>
                    <?php
                    printf(
                        /* translators: %s: version number */
                        esc_html__('Version %s is now available!', 'shopglut'),
                        esc_html($plugin['version'])
                    );
                    ?>
                <?php endif; ?>
                <a href="<?php echo esc_url($plugin['url']); ?>" target="_blank" class="button button-small" style="margin-left: 10px;">
                    <?php esc_html_e('View Release', 'shopglut'); ?>
                </a>
                <a href="<?php echo esc_url($plugin['zip_url']); ?>" target="_blank" class="button button-primary button-small" style="margin-left: 5px;">
                    <?php esc_html_e('Download', 'shopglut'); ?>
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

        foreach ($this->related_plugins as $slug => $plugin) {
            $release_info = $this->get_github_release($plugin['repo']);

            if ($release_info && !isset($release_info['message'])) {
                // Use tag_name as version (e.g., "v1.0.0" or "latest")
                $tag_name = isset($release_info['tag_name']) ? $release_info['tag_name'] : 'latest';

                // If tag is "latest", use date as fallback version
                if ($tag_name === 'latest') {
                    $published_at = $release_info['published_at'];
                    $version = $this->format_version_date($published_at);
                } else {
                    // Remove 'v' prefix if present
                    $version = ltrim($tag_name, 'v');
                }

                $versions[$slug] = array(
                    'name' => $plugin['name'],
                    'version' => $version,
                    'tag_name' => $tag_name,
                    'published_at' => $release_info['published_at'],
                    'url' => $plugin['url'] . '/releases/tag/' . $tag_name,
                    'icon' => $plugin['icon'],
                    'zip_url' => $this->get_zip_url($release_info)
                );
            }
        }

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

        // Check for shopglut admin pages
        if (strpos($screen->id, 'shopglut') !== false || strpos($screen->id, 'shopglut-pro') !== false) {
            return true;
        }

        // Also check for WooCommerce pages (since this is a WC plugin)
        if (strpos($screen->id, 'woocommerce') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Force refresh check (call from settings page)
     */
    public function force_check() {
        delete_transient($this->option_name . '_last_check');
        $this->check_for_updates();
    }

    /**
     * Get current versions for display in settings
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

        // Get plugin directory
        $plugin_dir = dirname(WP_PLUGIN_DIR . '/' . $plugin_basename);

        // Create a custom upgrader skin
        $skin = new ShopGlut_Quiet_Upgrader_Skin();

        // Create upgrader instance
        $upgrader = new Plugin_Upgrader($skin);

        // Download the ZIP file
        $download_url = $zip_url;

        // Get the plugin slug from basename
        $plugin_slug = dirname($plugin_basename);

        // Use WordPress upgrade API
        $result = $upgrader->upgrade($plugin_basename, $download_url);

        if (is_wp_error($result)) {
            return $result;
        }

        if ($result === false) {
            return new WP_Error('update_failed', 'Plugin update failed');
        }

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

    /**
     * Get plugin basename from slug
     */
    private function get_plugin_basename($slug) {
        $basenames = array(
            'wishglut' => 'wishglut/wishglut.php',
            'checkoutglut' => 'checkoutglut/checkoutglut.php',
            'shortcodeglut' => 'shortcodeglut/shortcodeglut.php'
        );
        return isset($basenames[$slug]) ? $basenames[$slug] : '';
    }
}

/**
 * Custom upgrader skin for silent updates
 */
class ShopGlut_Quiet_Upgrader_Skin extends WP_Upgrader_Skin {
    public function feedback($string, ...$args) {
        // Silence feedback
    }

    public function header() {
        // No header
    }

    public function footer() {
        // No footer
    }

    public function error($errors) {
        // Handle errors silently
    }
}

// Initialize the update checker
new ShopGlut_PluginUpdateChecker();
