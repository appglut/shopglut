<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.
/**
 *
 * Field: related_plugins
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'AGSHOPGLUT_related_plugins' ) ) {
	class AGSHOPGLUT_related_plugins extends AGSHOPGLUTP {

		public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
			parent::__construct( $field, $value, $unique, $where, $parent );
		}

		public function render() {
			echo wp_kses_post( $this->field_before() );

			$plugins = $this->get_related_plugins();
			$versions = get_option( 'shopglut_related_plugins_versions', array() );
			$dismissed = get_option( 'shopglut_related_plugins_versions_dismissed', array() );

			?>
			<div class="shopglut-related-plugins-wrapper">
				<div class="shopglut-plugins-header">
					<h3><?php esc_html_e( 'More Plugins by AppGlut', 'shopglut' ); ?></h3>
					<p class="description"><?php esc_html_e( 'Check out our other WooCommerce plugins to enhance your store.', 'shopglut' ); ?></p>
				</div>

				<div class="shopglut-plugins-grid">
					<?php foreach ( $plugins as $slug => $plugin ) :
						$is_installed = $this->is_plugin_installed( $plugin['basename'] );
						$is_active = $this->is_plugin_active( $plugin['basename'] );
						$has_update = isset( $versions[ $slug ] ) && ! in_array( $slug, $dismissed );
						$version_info = isset( $versions[ $slug ] ) ? $versions[ $slug ] : null;
						?>

						<div class="shopglut-plugin-card <?php echo $is_active ? 'active' : ''; ?> <?php echo $has_update ? 'has-update' : ''; ?>">
							<div class="plugin-icon"><?php echo esc_html( $plugin['icon'] ); ?></div>
							<div class="plugin-info">
								<h4 class="plugin-name"><?php echo esc_html( $plugin['name'] ); ?></h4>
								<p class="plugin-description"><?php echo esc_html( $plugin['description'] ); ?></p>

								<?php if ( $has_update && $version_info ) : ?>
									<div class="plugin-update-badge">
										<span class="update-badge">
											<?php
											printf(
												/* translators: %s: version date */
												esc_html__( 'New: %s', 'shopglut' ),
												esc_html( $version_info['version'] )
											);
											?>
										</span>
									</div>
								<?php endif; ?>

								<div class="plugin-status">
									<?php if ( $is_active ) : ?>
										<span class="status-badge active"><?php esc_html_e( 'Active', 'shopglut' ); ?></span>
									<?php elseif ( $is_installed ) : ?>
										<span class="status-badge installed"><?php esc_html_e( 'Installed', 'shopglut' ); ?></span>
									<?php else : ?>
										<span class="status-badge not-installed"><?php esc_html_e( 'Not Installed', 'shopglut' ); ?></span>
									<?php endif; ?>
								</div>

								<div class="plugin-actions">
									<?php if ( $has_update && $version_info ) : ?>
										<a href="<?php echo esc_url( $version_info['url'] ); ?>" target="_blank" class="button button-small">
											<?php esc_html_e( 'View Release', 'shopglut' ); ?>
										</a>
										<a href="<?php echo esc_url( $version_info['zip_url'] ); ?>" target="_blank" class="button button-primary button-small">
											<?php esc_html_e( 'Download', 'shopglut' ); ?>
										</a>
									<?php else : ?>
										<a href="<?php echo esc_url( $plugin['url'] ); ?>" target="_blank" class="button button-small">
											<?php esc_html_e( 'Learn More', 'shopglut' ); ?>
										</a>
									<?php endif; ?>
								</div>
							</div>
						</div>

					<?php endforeach; ?>
				</div>

				<div class="shopglut-plugins-footer">
					<button type="button" class="button shopglut-check-updates" id="shopglut-check-updates-btn">
						<span class="dashicons dashicons-update-alt"></span>
						<?php esc_html_e( 'Check for Updates', 'shopglut' ); ?>
					</button>
					<span class="spinner" style="display:none;"></span>
				</div>
			</div>

			<style>
				.shopglut-related-plugins-wrapper {
					background: #f9f9f9;
					border: 1px solid #ddd;
					border-radius: 8px;
					padding: 20px;
					margin: 15px 0;
				}

				.shopglut-plugins-header h3 {
					margin: 0 0 5px 0;
					font-size: 16px;
					color: #1d2327;
				}

				.shopglut-plugins-header .description {
					margin: 0 0 15px 0;
					color: #646970;
					font-size: 13px;
				}

				.shopglut-plugins-grid {
					display: grid;
					grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
					gap: 15px;
					margin-bottom: 15px;
				}

				.shopglut-plugin-card {
					background: #fff;
					border: 1px solid #ddd;
					border-radius: 6px;
					padding: 15px;
					display: flex;
					gap: 12px;
					transition: all 0.2s ease;
				}

				.shopglut-plugin-card:hover {
					box-shadow: 0 2px 8px rgba(0,0,0,0.1);
					border-color: #2271b1;
				}

				.shopglut-plugin-card.has-update {
					border-color: #72aee6;
					background: #f0f6fc;
				}

				.plugin-icon {
					font-size: 32px;
					line-height: 1;
					flex-shrink: 0;
				}

				.plugin-info {
					flex: 1;
				}

				.plugin-info .plugin-name {
					margin: 0 0 5px 0;
					font-size: 14px;
					font-weight: 600;
					color: #1d2327;
				}

				.plugin-info .plugin-description {
					margin: 0 0 8px 0;
					font-size: 12px;
					color: #646970;
					line-height: 1.4;
				}

				.plugin-update-badge {
					margin-bottom: 8px;
				}

				.plugin-update-badge .update-badge {
					display: inline-block;
					background: #72aee6;
					color: #fff;
					padding: 2px 8px;
					border-radius: 3px;
					font-size: 11px;
					font-weight: 500;
				}

				.plugin-status {
					margin-bottom: 8px;
				}

				.status-badge {
					display: inline-block;
					padding: 2px 8px;
					border-radius: 3px;
					font-size: 11px;
					font-weight: 500;
				}

				.status-badge.active {
					background: #00a32a;
					color: #fff;
				}

				.status-badge.installed {
					background: #646970;
					color: #fff;
				}

				.status-badge.not-installed {
					background: #d63638;
					color: #fff;
				}

				.plugin-actions {
					display: flex;
					gap: 8px;
				}

				.plugin-actions .button {
					padding: 4px 10px;
					font-size: 12px;
					height: auto;
					line-height: 1.5;
				}

				.shopglut-plugins-footer {
					display: flex;
					align-items: center;
					gap: 10px;
					padding-top: 15px;
					border-top: 1px solid #ddd;
				}

				.shopglut-check-updates {
					display: flex;
					align-items: center;
					gap: 5px;
				}

				.shopglut-check-updates .dashicons {
					font-size: 16px;
				}

				.shopglut-plugins-footer .spinner {
					float: none;
					margin: 0;
				}

				@media (max-width: 600px) {
					.shopglut-plugins-grid {
						grid-template-columns: 1fr;
					}
				}
			</style>

			<script>
			jQuery(document).ready(function($) {
				$('#shopglut-check-updates-btn').on('click', function() {
					var $btn = $(this);
					var $spinner = $btn.next('.spinner');

					$btn.prop('disabled', true);
					$spinner.css('display', 'inline-block');

					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'shopglut_force_check_updates',
							nonce: '<?php echo wp_create_nonce('shopglut_check_updates'); ?>'
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
			echo wp_kses_post( $this->field_after() );
		}

		private function get_related_plugins() {
			return array(
				'wishglut' => array(
					'name' => 'WishGlut',
					'description' => 'Advanced wishlist plugin for WooCommerce with multiple wishlist lists',
					'icon' => '🎁',
					'url' => 'https://github.com/appglut/wishglut',
					'basename' => 'wishglut/wishglut.php'
				),
				'checkoutglut' => array(
					'name' => 'CheckoutGlut',
					'description' => 'Customize WooCommerce checkout page with drag & drop builder',
					'icon' => '🛒',
					'url' => 'https://github.com/appglut/checkoutglut',
					'basename' => 'checkoutglut/checkoutglut.php'
				),
				'shortcodeglut' => array(
					'name' => 'ShortcodeGlut',
					'description' => 'Powerful shortcode builder for WordPress with visual editor',
					'icon' => '⚡',
					'url' => 'https://github.com/appglut/shortcodeglut',
					'basename' => 'shortcodeglut/shortcodeglut.php'
				)
			);
		}

		private function is_plugin_installed( $basename ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugins = get_plugins();
			return isset( $plugins[ $basename ] );
		}

		private function is_plugin_active( $basename ) {
			return is_plugin_active( $basename );
		}
	}
}
