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
                $published_at = $release_info['published_at'];
                $version_date = $this->format_version_date($published_at);

                $versions[$slug] = array(
                    'name' => $plugin['name'],
                    'version' => $version_date,
                    'published_at' => $published_at,
                    'url' => $plugin['url'],
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

        ?>
        <div class="notice notice-info is-dismissible shopglut-plugin-update" data-plugin="<?php echo $notice_id; ?>">
            <p>
                <strong><?php echo esc_html($plugin['icon'] . ' ' . $plugin['name']); ?></strong>
                - A new version is available! (Updated: <?php echo esc_html($plugin['version']); ?>)
                <a href="<?php echo esc_url($plugin['url']); ?>" target="_blank" class="button button-small" style="margin-left: 10px;">
                    View Release
                </a>
                <a href="<?php echo esc_url($plugin['zip_url']); ?>" target="_blank" class="button button-primary button-small" style="margin-left: 5px;">
                    Download
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
                $published_at = $release_info['published_at'];
                $version_date = $this->format_version_date($published_at);

                $versions[$slug] = array(
                    'name' => $plugin['name'],
                    'version' => $version_date,
                    'published_at' => $published_at,
                    'url' => $plugin['url'],
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
}

// Initialize the update checker
new ShopGlut_PluginUpdateChecker();
