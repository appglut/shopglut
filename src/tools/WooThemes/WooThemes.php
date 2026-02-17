<?php
namespace Shopglut\tools\WooThemes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WooThemes {

	public function __construct() {
		add_action( 'wp_ajax_activate_woo_theme', [ $this, 'activateTheme' ] );
		add_action( 'wp_ajax_deactivate_woo_theme', [ $this, 'deactivateTheme' ] );
		add_action( 'wp_ajax_save_woo_theme_settings', [ $this, 'saveThemeSettings' ] );
		add_action( 'wp_ajax_get_theme_preview_content', [ $this, 'getThemePreviewContent' ] );
		add_action( 'wp_ajax_nopriv_activate_woo_theme', [ $this, 'activateTheme' ] );
		add_action( 'wp_ajax_nopriv_deactivate_woo_theme', [ $this, 'deactivateTheme' ] );
		add_action( 'wp_ajax_nopriv_get_theme_preview_content', [ $this, 'getThemePreviewContent' ] );
		add_filter( 'body_class', [ $this, 'addThemeBodyClass' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueueThemeStyles' ] );
	}

	public function renderThemesList() {
		$themes = $this->getAvailableThemes();
		$active_theme = get_option( 'shopglut_active_woo_theme', '' );

		?>
		<div class="wrap">
			<div style="text-align: center; margin-bottom: 30px;">
				<h1 style="margin: 0 0 10px 0;"><?php echo esc_html__( 'Woo Themes', 'shopglut' ); ?></h1>
				<div class="notice notice-info" style="display: inline-block; text-align: left;">
					<p><strong><?php echo esc_html__( 'Important:', 'shopglut' ); ?></strong> <?php echo esc_html__( 'These themes customize your WooCommerce store\'s header and footer elements. They work with your existing WordPress theme and do not replace it.', 'shopglut' ); ?></p>
				</div>
				<p style="margin-top: 15px;"><?php echo esc_html__( 'Choose a theme to customize your store\'s appearance. Activate a theme to apply it to your store.', 'shopglut' ); ?></p>
			</div>

			<div class="themes-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px; margin-top: 30px;">
				<?php foreach ( $themes as $theme_id => $theme ) : ?>
					<div class="theme-card"
						 data-theme-id="<?php echo esc_attr( $theme_id ); ?>"
						 style="border: 2px solid <?php echo $active_theme === $theme_id ? '#2271b1' : '#e0e0e0' ?>; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer;">

						<!-- CSS-based Theme Preview -->
						<div class="theme-preview" style="position: relative; height: 220px; background: linear-gradient(135deg, <?php echo esc_attr( $theme['gradient'] ); ?>); padding: 20px;">
							<!-- Header Preview -->
							<div class="preview-header" style="background: rgba(255,255,255,0.95); border-radius: 8px; padding: 12px 15px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
								<div style="display: flex; align-items: center; justify-content: space-between;">
									<div style="display: flex; align-items: center; gap: 10px;">
										<div style="width: 30px; height: 30px; background: <?php echo esc_attr( $theme['accent'] ); ?>; border-radius: 6px;"></div>
										<div style="width: 80px; height: 8px; background: #e0e0e0; border-radius: 4px;"></div>
									</div>
									<div style="display: flex; gap: 8px;">
										<div style="width: 24px; height: 24px; background: #f5f5f5; border-radius: 4px;"></div>
										<div style="width: 24px; height: 24px; background: #f5f5f5; border-radius: 4px;"></div>
									</div>
								</div>
							</div>
							<!-- Content Preview -->
							<div class="preview-content" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
								<div style="background: rgba(255,255,255,0.9); border-radius: 6px; height: 50px;"></div>
								<div style="background: rgba(255,255,255,0.9); border-radius: 6px; height: 50px;"></div>
								<div style="background: rgba(255,255,255,0.9); border-radius: 6px; height: 50px;"></div>
							</div>
							<!-- Footer Preview -->
							<div class="preview-footer" style="position: absolute; bottom: 20px; left: 20px; right: 20px; background: <?php echo esc_attr( $theme['footer_bg'] ); ?>; border-radius: 6px; padding: 10px 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
								<div style="height: 6px; background: rgba(255,255,255,0.3); border-radius: 3px; width: 60%;"></div>
							</div>

							<?php if ( $active_theme === $theme_id ) : ?>
								<div class="active-badge" style="position: absolute; top: 15px; right: 15px; background: #00a32a; color: white; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; box-shadow: 0 2px 8px rgba(0,163,42,0.3);">
									<span style="display: flex; align-items: center; gap: 5px;">
										<span style="font-size: 16px;">✓</span>
										<?php echo esc_html__( 'Active', 'shopglut' ); ?>
									</span>
								</div>
							<?php endif; ?>
						</div>

						<div class="theme-info" style="padding: 24px;">
							<h3 style="margin: 0 0 8px 0; color: #1d2327; font-size: 20px; font-weight: 600;"><?php echo esc_html( $theme['name'] ); ?></h3>
							<p style="color: #646970; margin: 0 0 20px 0; font-size: 14px; line-height: 1.6;"><?php echo esc_html( $theme['description'] ); ?></p>

							<div class="theme-features" style="margin-bottom: 20px;">
								<?php foreach ( $theme['features'] as $feature ) : ?>
									<span style="display: inline-block; background: #f0f6fc; color: #2271b1; padding: 4px 10px; border-radius: 12px; font-size: 12px; margin-right: 5px; margin-bottom: 5px;">
										<?php echo esc_html( $feature ); ?>
									</span>
								<?php endforeach; ?>
							</div>

							<div class="theme-actions">
								<div style="display: flex; gap: 10px;">
									<button class="button preview-theme-btn" data-theme-id="<?php echo esc_attr( $theme_id ); ?>" style="flex: 1; height: 42px;">
										<?php echo esc_html__( 'Preview', 'shopglut' ); ?>
									</button>
									<?php if ( $active_theme === $theme_id ) : ?>
										<button class="button deactivate-theme-btn" data-theme-id="<?php echo esc_attr( $theme_id ); ?>" style="flex: 1; height: 42px; background: #d63638; border-color: #d63638; color: white;">
											<?php echo esc_html__( 'Deactivate', 'shopglut' ); ?>
										</button>
									<?php else : ?>
										<button class="button button-primary activate-theme-btn" data-theme-id="<?php echo esc_attr( $theme_id ); ?>" style="flex: 1; height: 42px;">
											<?php echo esc_html__( 'Activate', 'shopglut' ); ?>
										</button>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<style>
			.theme-card:hover {
				transform: translateY(-4px);
				box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
			}
			.theme-card:hover .theme-preview {
				transform: scale(1.02);
			}
		</style>

		<script>
		jQuery(document).ready(function($) {
			// Activate theme
			$('.activate-theme-btn').on('click', function(e) {
				e.stopPropagation();
				var themeId = $(this).data('theme-id');
				var button = $(this);
				var card = button.closest('.theme-card');

				button.prop('disabled', true).text('<?php echo esc_js( __( 'Activating...', 'shopglut' ) ); ?>');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'activate_woo_theme',
						theme_id: themeId,
						nonce: '<?php echo esc_attr( wp_create_nonce( 'shopglut_theme_nonce' ) ); ?>'
					},
					success: function(response) {
						if (response.success) {
							location.reload();
						} else {
							alert(response.data || '<?php echo esc_js( __( 'Error activating theme', 'shopglut' ) ); ?>');
							button.prop('disabled', false).text('<?php echo esc_js( __( 'Activate', 'shopglut' ) ); ?>');
						}
					},
					error: function() {
						alert('<?php echo esc_js( __( 'Error activating theme', 'shopglut' ) ); ?>');
						button.prop('disabled', false).text('<?php echo esc_js( __( 'Activate', 'shopglut' ) ); ?>');
					}
				});
			});

			// Deactivate theme
			$('.deactivate-theme-btn').on('click', function(e) {
				e.stopPropagation();
				var themeId = $(this).data('theme-id');
				var button = $(this);

				button.prop('disabled', true).text('<?php echo esc_js( __( 'Deactivating...', 'shopglut' ) ); ?>');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'deactivate_woo_theme',
						theme_id: themeId,
						nonce: '<?php echo esc_attr( wp_create_nonce( 'shopglut_theme_nonce' ) ); ?>'
					},
					success: function(response) {
						if (response.success) {
							location.reload();
						} else {
							alert(response.data || '<?php echo esc_js( __( 'Error deactivating theme', 'shopglut' ) ); ?>');
							button.prop('disabled', false).text('<?php echo esc_js( __( 'Deactivate', 'shopglut' ) ); ?>');
						}
					},
					error: function() {
						alert('<?php echo esc_js( __( 'Error deactivating theme', 'shopglut' ) ); ?>');
						button.prop('disabled', false).text('<?php echo esc_js( __( 'Deactivate', 'shopglut' ) ); ?>');
					}
				});
			});

			// Preview button click - open modal
			$('.preview-theme-btn').on('click', function(e) {
				e.stopPropagation();
				var themeId = $(this).data('theme-id');
				openPreviewModal(themeId);
			});

			// Card click - go to customizer (but not when clicking buttons)
			$('.theme-card').on('click', function(e) {
				if ($(e.target).closest('button').length === 0) {
					var themeId = $(this).data('theme-id');
					window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=shopglut_tools&view=woo_themes&customize=' ) ); ?>' + themeId;
				}
			});

			// Preview Modal Functions
			var currentThemeId = '';
			var currentPage = 'home';

			function openPreviewModal(themeId) {
				currentThemeId = themeId;
				currentPage = 'home';
				$('.shopglut-preview-modal').remove();
				createPreviewModal();
				loadPreviewContent(themeId, 'home');
			}

			function createPreviewModal() {
				var modalHtml = `
					<div class="shopglut-preview-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100000; display: flex; align-items: center; justify-content: center;">
						<div class="preview-modal-content" style="background: #fff; border-radius: 12px; width: 90%; max-width: 1200px; height: 85vh; display: flex; flex-direction: column; overflow: hidden;">
							<div class="preview-modal-header" style="display: flex; align-items: center; justify-content: space-between; padding: 20px 25px; border-bottom: 1px solid #e0e0e0; background: #f9f9f9;">
								<h2 style="margin: 0; font-size: 20px;">Theme Preview</h2>
								<button class="close-preview-modal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #646970; padding: 5px;">×</button>
							</div>
							<div class="preview-modal-nav" style="display: flex; padding: 15px 25px; gap: 10px; border-bottom: 1px solid #e0e0e0; background: #fff;">
								<button class="preview-nav-btn active" data-page="home" style="padding: 10px 20px; border: 2px solid #2271b1; background: #2271b1; color: white; border-radius: 6px; cursor: pointer; font-weight: 500;">Home</button>
								<button class="preview-nav-btn" data-page="shop" style="padding: 10px 20px; border: 2px solid #e0e0e0; background: #fff; color: #333; border-radius: 6px; cursor: pointer; font-weight: 500;">Shop</button>
								<button class="preview-nav-btn" data-page="product" style="padding: 10px 20px; border: 2px solid #e0e0e0; background: #fff; color: #333; border-radius: 6px; cursor: pointer; font-weight: 500;">Product</button>
								<button class="preview-nav-btn" data-page="cart" style="padding: 10px 20px; border: 2px solid #e0e0e0; background: #fff; color: #333; border-radius: 6px; cursor: pointer; font-weight: 500;">Cart</button>
								<button class="preview-nav-btn" data-page="checkout" style="padding: 10px 20px; border: 2px solid #e0e0e0; background: #fff; color: #333; border-radius: 6px; cursor: pointer; font-weight: 500;">Checkout</button>
							</div>
							<div class="preview-modal-body" style="flex: 1; overflow: auto; padding: 0; background: #f5f5f5;">
								<iframe class="preview-iframe" style="width: 100%; height: 100%; border: none;"></iframe>
							</div>
						</div>
					</div>
				`;
				$('body').append(modalHtml);

				$('.close-preview-modal').on('click', function() {
					$('.shopglut-preview-modal').remove();
				});

				$('.preview-nav-btn').on('click', function() {
					var page = $(this).data('page');
					$('.preview-nav-btn').removeClass('active').css({
						'background': '#fff',
						'color': '#333',
						'border-color': '#e0e0e0'
					});
					$(this).addClass('active').css({
						'background': '#2271b1',
						'color': 'white',
						'border-color': '#2271b1'
					});
					loadPreviewContent(currentThemeId, page);
				});

				$(document).on('keydown', function(e) {
					if (e.key === 'Escape') {
						$('.shopglut-preview-modal').remove();
					}
				});
			}

			function loadPreviewContent(themeId, page) {
				currentPage = page;
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'get_theme_preview_content',
						theme_id: themeId,
						page: page,
						nonce: '<?php echo esc_attr( wp_create_nonce( 'shopglut_preview_nonce' ) ); ?>'
					},
					success: function(response) {
						if (response.success) {
							var iframe = $('.preview-iframe');
							var iframeDoc = iframe[0].contentDocument || iframe[0].contentWindow.document;
							iframeDoc.open();
							iframeDoc.write(response.data);
							iframeDoc.close();
						}
					}
				});
			}
		});
		</script>
		<?php
	}

	public function renderCustomizer() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for theme customizer display
		$theme_id = isset( $_GET['customize'] ) ? sanitize_text_field( wp_unslash( $_GET['customize'] ) ) : '';
		$themes = $this->getAvailableThemes();

		if ( ! isset( $themes[$theme_id] ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid theme ID', 'shopglut' ) . '</p></div>';
			return;
		}

		$theme = $themes[$theme_id];
		$active_theme = get_option( 'shopglut_active_woo_theme', '' );
		$is_active = ( $active_theme === $theme_id );

		// Get current theme settings
		$settings = $this->getThemeSettings( $theme_id );

		?>
		<div class="wrap">
			<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
				<div>
					<h1 style="margin: 0;"><?php echo esc_html__( 'Customize Theme:', 'shopglut' ); ?> <?php echo esc_html( $theme['name'] ); ?></h1>
					<p style="margin: 5px 0 0 0; color: #646970;"><?php echo esc_html__( 'Customize header and footer elements for this theme.', 'shopglut' ); ?></p>
				</div>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=shopglut_tools&view=woo_themes' ) ); ?>" class="button">
					← <?php echo esc_html__( 'Back to Themes', 'shopglut' ); ?>
				</a>
			</div>

			<div class="theme-customizer" style="display: grid; grid-template-columns: 350px 1fr; gap: 30px; margin-top: 20px;">
				<div class="customizer-controls" style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #e0e0e0;">
					<h2 style="margin: 0 0 20px 0; font-size: 18px;"><?php echo esc_html__( 'Customization Options', 'shopglut' ); ?></h2>

					<!-- Theme Status -->
					<div class="control-group" style="margin-bottom: 25px; padding: 15px; background: <?php echo $is_active ? '#edfaef' : '#f6f7f7'; ?>; border-radius: 6px;">
						<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
							<span style="font-size: 20px;"><?php echo $is_active ? '✓' : '○'; ?></span>
							<strong style="color: <?php echo $is_active ? '#00a32a' : '#646970'; ?>;">
								<?php echo $is_active ? esc_html__( 'Theme Active', 'shopglut' ) : esc_html__( 'Theme Inactive', 'shopglut' ); ?>
							</strong>
						</div>
						<?php if ( ! $is_active ) : ?>
							<button class="button button-primary activate-theme-btn" data-theme-id="<?php echo esc_attr( $theme_id ); ?>" style="width: 100%;">
								<?php echo esc_html__( 'Activate This Theme', 'shopglut' ); ?>
							</button>
						<?php else : ?>
							<button class="button deactivate-theme-btn" data-theme-id="<?php echo esc_attr( $theme_id ); ?>" style="width: 100%; background: #d63638; border-color: #d63638; color: white;">
								<?php echo esc_html__( 'Deactivate Theme', 'shopglut' ); ?>
							</button>
						<?php endif; ?>
					</div>

					<!-- Header Settings -->
					<div class="control-group" style="margin-bottom: 25px;">
						<label style="display: block; font-weight: 600; margin-bottom: 10px; color: #1d2327;">
							<?php echo esc_html__( 'Header Settings', 'shopglut' ); ?>
						</label>
						<p style="margin-bottom: 10px;">
							<label style="display: flex; align-items: center; gap: 8px;">
								<input type="checkbox" id="enable_header" <?php checked( $settings['enable_header'], true ); ?> style="width: 18px; height: 18px;">
								<span><?php echo esc_html__( 'Enable Custom Header', 'shopglut' ); ?></span>
							</label>
						</p>
						<p>
							<label style="display: block; margin-bottom: 5px; font-size: 13px; color: #646970;"><?php echo esc_html__( 'Header Background:', 'shopglut' ); ?></label>
							<input type="text" id="header_bg" value="<?php echo esc_attr( $settings['header_bg'] ); ?>" class="regular-text" placeholder="#ffffff">
							<input type="color" id="header_bg_color" value="<?php echo esc_attr( $settings['header_bg'] ); ?>" style="width: 50px; height: 35px; vertical-align: middle;">
						</p>
						<p>
							<label style="display: block; margin-bottom: 5px; font-size: 13px; color: #646970;"><?php echo esc_html__( 'Header Text Color:', 'shopglut' ); ?></label>
							<input type="text" id="header_text_color" value="<?php echo esc_attr( $settings['header_text_color'] ); ?>" class="regular-text" placeholder="#333333">
							<input type="color" id="header_text_color_picker" value="<?php echo esc_attr( $settings['header_text_color'] ); ?>" style="width: 50px; height: 35px; vertical-align: middle;">
						</p>
					</div>

					<!-- Footer Settings -->
					<div class="control-group" style="margin-bottom: 25px;">
						<label style="display: block; font-weight: 600; margin-bottom: 10px; color: #1d2327;">
							<?php echo esc_html__( 'Footer Settings', 'shopglut' ); ?>
						</label>
						<p style="margin-bottom: 10px;">
							<label style="display: flex; align-items: center; gap: 8px;">
								<input type="checkbox" id="enable_footer" <?php checked( $settings['enable_footer'], true ); ?> style="width: 18px; height: 18px;">
								<span><?php echo esc_html__( 'Enable Custom Footer', 'shopglut' ); ?></span>
							</label>
						</p>
						<p>
							<label style="display: block; margin-bottom: 5px; font-size: 13px; color: #646970;"><?php echo esc_html__( 'Footer Background:', 'shopglut' ); ?></label>
							<input type="text" id="footer_bg" value="<?php echo esc_attr( $settings['footer_bg'] ); ?>" class="regular-text" placeholder="#333333">
							<input type="color" id="footer_bg_color" value="<?php echo esc_attr( $settings['footer_bg'] ); ?>" style="width: 50px; height: 35px; vertical-align: middle;">
						</p>
						<p>
							<label style="display: block; margin-bottom: 5px; font-size: 13px; color: #646970;"><?php echo esc_html__( 'Footer Text Color:', 'shopglut' ); ?></label>
							<input type="text" id="footer_text_color" value="<?php echo esc_attr( $settings['footer_text_color'] ); ?>" class="regular-text" placeholder="#ffffff">
							<input type="color" id="footer_text_color_picker" value="<?php echo esc_attr( $settings['footer_text_color'] ); ?>" style="width: 50px; height: 35px; vertical-align: middle;">
						</p>
					</div>

					<!-- Save Button -->
					<div style="display: flex; gap: 10px;">
						<button class="button preview-customizer-btn" data-theme-id="<?php echo esc_attr( $theme_id ); ?>" style="flex: 1; height: 45px; font-size: 16px;">
							<?php echo esc_html__( 'Preview', 'shopglut' ); ?>
						</button>
						<button class="button button-primary" id="save_customizations" data-theme-id="<?php echo esc_attr( $theme_id ); ?>" style="flex: 1; height: 45px; font-size: 16px;">
							<?php echo esc_html__( 'Save Changes', 'shopglut' ); ?>
						</button>
					</div>
				</div>

				<div class="theme-preview" style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #e0e0e0;">
					<h2 style="margin: 0 0 20px 0; font-size: 18px;"><?php echo esc_html__( 'Live Preview', 'shopglut' ); ?></h2>

					<div class="preview-frame" style="border: 1px solid #e0e0e0; background: linear-gradient(135deg, <?php echo esc_attr( $theme['gradient'] ); ?>); border-radius: 8px; min-height: 500px; padding: 20px; position: relative;">
						<!-- Header Preview -->
						<div class="preview-header-section" style="background: <?php echo esc_attr( $settings['header_bg'] ); ?>; color: <?php echo esc_attr( $settings['header_text_color'] ); ?>; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
							<div style="display: flex; align-items: center; justify-content: space-between;">
								<div style="display: flex; align-items: center; gap: 15px;">
									<div style="width: 40px; height: 40px; background: <?php echo esc_attr( $theme['accent'] ); ?>; border-radius: 8px;"></div>
									<span style="font-size: 18px; font-weight: 600;">Your Store Logo</span>
								</div>
								<div style="display: flex; gap: 15px;">
									<span style="font-size: 14px;">Home</span>
									<span style="font-size: 14px;">Shop</span>
									<span style="font-size: 14px;">Cart</span>
								</div>
							</div>
						</div>

						<!-- Content Area -->
						<div class="preview-content-section" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
							<div style="background: rgba(255,255,255,0.9); border-radius: 8px; height: 80px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
								<div style="width: 50px; height: 50px; background: #e0e0e0; border-radius: 6px; margin-bottom: 10px;"></div>
								<div style="height: 8px; background: #e0e0e0; border-radius: 4px; width: 60%;"></div>
							</div>
							<div style="background: rgba(255,255,255,0.9); border-radius: 8px; height: 80px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
								<div style="width: 50px; height: 50px; background: #e0e0e0; border-radius: 6px; margin-bottom: 10px;"></div>
								<div style="height: 8px; background: #e0e0e0; border-radius: 4px; width: 60%;"></div>
							</div>
							<div style="background: rgba(255,255,255,0.9); border-radius: 8px; height: 80px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
								<div style="width: 50px; height: 50px; background: #e0e0e0; border-radius: 6px; margin-bottom: 10px;"></div>
								<div style="height: 8px; background: #e0e0e0; border-radius: 4px; width: 60%;"></div>
							</div>
						</div>

						<!-- Footer Preview -->
						<div class="preview-footer-section" style="background: <?php echo esc_attr( $settings['footer_bg'] ); ?>; color: <?php echo esc_attr( $settings['footer_text_color'] ); ?>; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: absolute; bottom: 20px; left: 20px; right: 20px;">
							<div style="text-align: center;">
								<p style="margin: 0 0 10px 0; font-size: 14px;">© 2025 Your Store. All rights reserved.</p>
								<div style="height: 4px; background: rgba(255,255,255,0.2); border-radius: 2px; width: 100px; margin: 0 auto;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			// Color picker sync
			function syncColorPicker(textInput, colorInput) {
				$(textInput).on('change', function() {
					$(colorInput).val($(this).val());
				});
				$(colorInput).on('change', function() {
					$(textInput).val($(this).val());
					updatePreview();
				});
			}

			syncColorPicker('#header_bg', '#header_bg_color');
			syncColorPicker('#header_text_color', '#header_text_color_picker');
			syncColorPicker('#footer_bg', '#footer_bg_color');
			syncColorPicker('#footer_text_color', '#footer_text_color_picker');

			// Update preview on change
			function updatePreview() {
				var headerBg = $('#header_bg').val();
				var headerText = $('#header_text_color').val();
				var footerBg = $('#footer_bg').val();
				var footerText = $('#footer_text_color').val();

				$('.preview-header-section').css({
					'background': headerBg,
					'color': headerText
				});

				$('.preview-footer-section').css({
					'background': footerBg,
					'color': footerText
				});
			}

			$('#header_bg, #header_text_color, #footer_bg, #footer_text_color').on('change', updatePreview);

			// Preview Modal Functions
			var currentThemeId = '';
			var currentPage = 'home';

			$('.preview-customizer-btn').on('click', function() {
				var themeId = $(this).data('theme-id');
				currentThemeId = themeId;
				currentPage = 'home';
				$('.shopglut-preview-modal').remove();
				createPreviewModal();
				loadPreviewContent(themeId, 'home');
			});

			function createPreviewModal() {
				var modalHtml = `
					<div class="shopglut-preview-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100000; display: flex; align-items: center; justify-content: center;">
						<div class="preview-modal-content" style="background: #fff; border-radius: 12px; width: 90%; max-width: 1200px; height: 85vh; display: flex; flex-direction: column; overflow: hidden;">
							<div class="preview-modal-header" style="display: flex; align-items: center; justify-content: space-between; padding: 20px 25px; border-bottom: 1px solid #e0e0e0; background: #f9f9f9;">
								<h2 style="margin: 0; font-size: 20px;">Theme Preview</h2>
								<button class="close-preview-modal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #646970; padding: 5px;">×</button>
							</div>
							<div class="preview-modal-nav" style="display: flex; padding: 15px 25px; gap: 10px; border-bottom: 1px solid #e0e0e0; background: #fff;">
								<button class="preview-nav-btn active" data-page="home" style="padding: 10px 20px; border: 2px solid #2271b1; background: #2271b1; color: white; border-radius: 6px; cursor: pointer; font-weight: 500;">Home</button>
								<button class="preview-nav-btn" data-page="shop" style="padding: 10px 20px; border: 2px solid #e0e0e0; background: #fff; color: #333; border-radius: 6px; cursor: pointer; font-weight: 500;">Shop</button>
								<button class="preview-nav-btn" data-page="product" style="padding: 10px 20px; border: 2px solid #e0e0e0; background: #fff; color: #333; border-radius: 6px; cursor: pointer; font-weight: 500;">Product</button>
								<button class="preview-nav-btn" data-page="cart" style="padding: 10px 20px; border: 2px solid #e0e0e0; background: #fff; color: #333; border-radius: 6px; cursor: pointer; font-weight: 500;">Cart</button>
								<button class="preview-nav-btn" data-page="checkout" style="padding: 10px 20px; border: 2px solid #e0e0e0; background: #fff; color: #333; border-radius: 6px; cursor: pointer; font-weight: 500;">Checkout</button>
							</div>
							<div class="preview-modal-body" style="flex: 1; overflow: auto; padding: 0; background: #f5f5f5;">
								<iframe class="preview-iframe" style="width: 100%; height: 100%; border: none;"></iframe>
							</div>
						</div>
					</div>
				`;
				$('body').append(modalHtml);

				$('.close-preview-modal').on('click', function() {
					$('.shopglut-preview-modal').remove();
				});

				$('.preview-nav-btn').on('click', function() {
					var page = $(this).data('page');
					$('.preview-nav-btn').removeClass('active').css({
						'background': '#fff',
						'color': '#333',
						'border-color': '#e0e0e0'
					});
					$(this).addClass('active').css({
						'background': '#2271b1',
						'color': 'white',
						'border-color': '#2271b1'
					});
					loadPreviewContent(currentThemeId, page);
				});

				$(document).on('keydown', function(e) {
					if (e.key === 'Escape') {
						$('.shopglut-preview-modal').remove();
					}
				});
			}

			function loadPreviewContent(themeId, page) {
				currentPage = page;
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'get_theme_preview_content',
						theme_id: themeId,
						page: page,
						nonce: '<?php echo esc_attr( wp_create_nonce( 'shopglut_preview_nonce' ) ); ?>'
					},
					success: function(response) {
						if (response.success) {
							var iframe = $('.preview-iframe');
							var iframeDoc = iframe[0].contentDocument || iframe[0].contentWindow.document;
							iframeDoc.open();
							iframeDoc.write(response.data);
							iframeDoc.close();
						}
					}
				});
			}

			// Save customizations
			$('#save_customizations').on('click', function() {
				var button = $(this);
				var themeId = button.data('theme-id');

				button.prop('disabled', true).text('<?php echo esc_js( __( 'Saving...', 'shopglut' ) ); ?>');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'save_woo_theme_settings',
						theme_id: themeId,
						enable_header: $('#enable_header').is(':checked') ? '1' : '0',
						header_bg: $('#header_bg').val(),
						header_text_color: $('#header_text_color').val(),
						enable_footer: $('#enable_footer').is(':checked') ? '1' : '0',
						footer_bg: $('#footer_bg').val(),
						footer_text_color: $('#footer_text_color').val(),
						nonce: '<?php echo esc_attr( wp_create_nonce( 'shopglut_theme_settings_nonce' ) ); ?>'
					},
					success: function(response) {
						if (response.success) {
							button.text('<?php echo esc_js( __( 'Saved!', 'shopglut' ) ); ?>');
							setTimeout(function() {
								button.prop('disabled', false).text('<?php echo esc_js( __( 'Save Changes', 'shopglut' ) ); ?>');
							}, 2000);
						} else {
							alert(response.data || '<?php echo esc_js( __( 'Error saving settings', 'shopglut' ) ); ?>');
							button.prop('disabled', false).text('<?php echo esc_js( __( 'Save Changes', 'shopglut' ) ); ?>');
						}
					},
					error: function() {
						alert('<?php echo esc_js( __( 'Error saving settings', 'shopglut' ) ); ?>');
						button.prop('disabled', false).text('<?php echo esc_js( __( 'Save Changes', 'shopglut' ) ); ?>');
					}
				});
			});

			// Activate/Deactivate theme in customizer
			$('.activate-theme-btn, .deactivate-theme-btn').on('click', function() {
				var button = $(this);
				var themeId = button.data('theme-id');
				var isActivate = button.hasClass('activate-theme-btn');

				button.prop('disabled', true).text(isActivate ? '<?php echo esc_js( __( 'Activating...', 'shopglut' ) ); ?>' : '<?php echo esc_js( __( 'Deactivating...', 'shopglut' ) ); ?>');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: isActivate ? 'activate_woo_theme' : 'deactivate_woo_theme',
						theme_id: themeId,
						nonce: '<?php echo esc_attr( wp_create_nonce( 'shopglut_theme_nonce' ) ); ?>'
					},
					success: function(response) {
						if (response.success) {
							location.reload();
						} else {
							alert(response.data || '<?php echo esc_js( __( 'Error', 'shopglut' ) ); ?>');
							button.prop('disabled', false);
						}
					},
					error: function() {
						alert('<?php echo esc_js( __( 'Error', 'shopglut' ) ); ?>');
						button.prop('disabled', false);
					}
				});
			});
		});
		</script>
		<?php
	}

	public function activateTheme() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'shopglut_theme_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		$theme_id = isset( $_POST['theme_id'] ) ? sanitize_text_field( wp_unslash( $_POST['theme_id'] ) ) : '';
		$themes = $this->getAvailableThemes();

		if ( ! isset( $themes[$theme_id] ) ) {
			wp_send_json_error( 'Invalid theme ID' );
		}

		// Update active theme option
		update_option( 'shopglut_active_woo_theme', $theme_id );

		wp_send_json_success( 'Theme activated successfully' );
	}

	public function deactivateTheme() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'shopglut_theme_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		$theme_id = isset( $_POST['theme_id'] ) ? sanitize_text_field( wp_unslash( $_POST['theme_id'] ) ) : '';
		$active_theme = get_option( 'shopglut_active_woo_theme', '' );

		if ( $active_theme !== $theme_id ) {
			wp_send_json_error( 'Theme is not active' );
		}

		// Remove active theme
		update_option( 'shopglut_active_woo_theme', '' );

		wp_send_json_success( 'Theme deactivated successfully' );
	}

	public function saveThemeSettings() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'shopglut_theme_settings_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		$theme_id = isset( $_POST['theme_id'] ) ? sanitize_text_field( wp_unslash( $_POST['theme_id'] ) ) : '';
		$themes = $this->getAvailableThemes();

		if ( ! isset( $themes[$theme_id] ) ) {
			wp_send_json_error( 'Invalid theme ID' );
		}

		// Save theme settings
		$settings = [
			'enable_header' => isset( $_POST['enable_header'] ) && '1' === $_POST['enable_header'],
			'header_bg' => isset( $_POST['header_bg'] ) ? sanitize_hex_color( wp_unslash( $_POST['header_bg'] ) ) : '#ffffff',
			'header_text_color' => isset( $_POST['header_text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['header_text_color'] ) ) : '#333333',
			'enable_footer' => isset( $_POST['enable_footer'] ) && '1' === $_POST['enable_footer'],
			'footer_bg' => isset( $_POST['footer_bg'] ) ? sanitize_hex_color( wp_unslash( $_POST['footer_bg'] ) ) : '#333333',
			'footer_text_color' => isset( $_POST['footer_text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['footer_text_color'] ) ) : '#ffffff',
		];

		update_option( 'shopglut_theme_settings_' . $theme_id, $settings );

		wp_send_json_success( 'Settings saved successfully' );
	}

	public function addThemeBodyClass( $classes ) {
		if ( ! is_admin() ) {
			$active_theme = get_option( 'shopglut_active_woo_theme', '' );
			if ( ! empty( $active_theme ) ) {
				$classes[] = 'shopglut-woo-theme-' . $active_theme;
				$classes[] = 'shopglut-woo-theme-active';
			}
		}
		return $classes;
	}

	public function getThemePreviewContent() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'shopglut_preview_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		$theme_id = isset( $_POST['theme_id'] ) ? sanitize_text_field( wp_unslash( $_POST['theme_id'] ) ) : '';
		$page = isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : 'home';

		$themes = $this->getAvailableThemes();
		if ( ! isset( $themes[$theme_id] ) ) {
			wp_send_json_error( 'Invalid theme ID' );
		}

		$theme = $themes[$theme_id];
		$settings = $this->getThemeSettings( $theme_id );

		// Generate preview HTML based on page type
		$html = '';
		switch ( $page ) {
			case 'home':
				$html = $this->getHomePageContent( $theme, $settings );
				break;
			case 'shop':
				$html = $this->getShopPageContent( $theme, $settings );
				break;
			case 'product':
				$html = $this->getProductPageContent( $theme, $settings );
				break;
			case 'cart':
				$html = $this->getCartPageContent( $theme, $settings );
				break;
			case 'checkout':
				$html = $this->getCheckoutPageContent( $theme, $settings );
				break;
			default:
				$html = $this->getHomePageContent( $theme, $settings );
		}

		wp_send_json_success( $html );
	}

	private function getHomePageContent( $theme, $settings ) {
		$css = $this->generatePreviewCSS( $theme, $settings );
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Home Preview - <?php echo esc_html( $theme['name'] ); ?></title>
			<style>
				* { margin: 0; padding: 0; box-sizing: border-box; }
				body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
				<?php echo $css; ?>
			</style>
		</head>
		<body>
			<div class="shopglut-preview-wrapper">
				<header class="shopglut-header" style="background: <?php echo esc_attr( $settings['header_bg'] ); ?>; color: <?php echo esc_attr( $settings['header_text_color'] ); ?>; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
					<div style="display: flex; align-items: center; justify-content: space-between;">
						<div style="display: flex; align-items: center; gap: 15px;">
							<div style="width: 45px; height: 45px; background: <?php echo esc_attr( $theme['accent'] ); ?>; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px;">S</div>
							<span style="font-size: 22px; font-weight: 700;">Your Store</span>
						</div>
						<nav style="display: flex; gap: 30px;">
							<a href="#" style="color: inherit; text-decoration: none; font-weight: 500; font-size: 15px;">Home</a>
							<a href="#" style="color: inherit; text-decoration: none; font-weight: 500; font-size: 15px;">Shop</a>
							<a href="#" style="color: inherit; text-decoration: none; font-weight: 500; font-size: 15px;">About</a>
							<a href="#" style="color: inherit; text-decoration: none; font-weight: 500; font-size: 15px;">Contact</a>
						</nav>
						<div style="display: flex; gap: 15px; align-items: center;">
							<span style="font-size: 20px;">🔍</span>
							<span style="font-size: 20px;">🛒</span>
							<span style="font-size: 20px;">👤</span>
						</div>
					</div>
				</header>

				<main style="min-height: 400px; padding: 60px 40px;">
					<div class="hero-section" style="text-align: center; margin-bottom: 60px;">
						<h1 style="font-size: 48px; font-weight: 800; margin-bottom: 20px; background: linear-gradient(135deg, <?php echo esc_attr( $theme['gradient'] ); ?>); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Welcome to Our Store</h1>
						<p style="font-size: 18px; color: #666; margin-bottom: 30px;">Discover amazing products at great prices</p>
						<button style="background: <?php echo esc_attr( $theme['accent'] ); ?>; color: white; border: none; padding: 15px 40px; font-size: 16px; font-weight: 600; border-radius: 8px; cursor: pointer;">Shop Now</button>
					</div>

					<div class="featured-products" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px;">
						<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
							<div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
								<div style="height: 180px; background: linear-gradient(135deg, <?php echo esc_attr( $theme['gradient'] ); ?>); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px;">📦</div>
								<div style="padding: 20px;">
									<h3 style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Product <?php echo $i; ?></h3>
									<p style="color: #666; font-size: 14px; margin-bottom: 12px;">Description text here</p>
									<div style="display: flex; align-items: center; justify-content: space-between;">
										<span style="font-weight: 700; font-size: 18px; color: <?php echo esc_attr( $theme['accent'] ); ?>;">$<?php echo ( $i * 29 ); ?>.99</span>
										<button style="background: <?php echo esc_attr( $theme['accent'] ); ?>; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px;">Add to Cart</button>
									</div>
								</div>
							</div>
						<?php endfor; ?>
					</div>
				</main>

				<footer class="shopglut-footer" style="background: <?php echo esc_attr( $settings['footer_bg'] ); ?>; color: <?php echo esc_attr( $settings['footer_text_color'] ); ?>; padding: 40px; text-align: center;">
					<p style="margin-bottom: 10px;">© 2025 Your Store. All rights reserved.</p>
					<div style="display: flex; justify-content: center; gap: 20px; font-size: 14px; opacity: 0.8;">
						<a href="#" style="color: inherit; text-decoration: none;">Privacy Policy</a>
						<a href="#" style="color: inherit; text-decoration: none;">Terms of Service</a>
						<a href="#" style="color: inherit; text-decoration: none;">Contact</a>
					</div>
				</footer>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	private function getShopPageContent( $theme, $settings ) {
		$css = $this->generatePreviewCSS( $theme, $settings );
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Shop Preview - <?php echo esc_html( $theme['name'] ); ?></title>
			<style>
				* { margin: 0; padding: 0; box-sizing: border-box; }
				body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
				<?php echo $css; ?>
			</style>
		</head>
		<body>
			<div class="shopglut-preview-wrapper">
				<header class="shopglut-header" style="background: <?php echo esc_attr( $settings['header_bg'] ); ?>; color: <?php echo esc_attr( $settings['header_text_color'] ); ?>; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
					<div style="display: flex; align-items: center; justify-content: space-between;">
						<div style="display: flex; align-items: center; gap: 15px;">
							<div style="width: 45px; height: 45px; background: <?php echo esc_attr( $theme['accent'] ); ?>; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px;">S</div>
							<span style="font-size: 22px; font-weight: 700;">Your Store</span>
						</div>
						<nav style="display: flex; gap: 30px;">
							<a href="#" style="color: inherit; text-decoration: none; font-weight: 500; font-size: 15px;">Home</a>
							<a href="#" style="color: inherit; text-decoration: none; font-weight: 500; font-size: 15px;">Shop</a>
							<a href="#" style="color: inherit; text-decoration: none; font-weight: 500; font-size: 15px;">About</a>
						</nav>
						<div style="display: flex; gap: 15px; align-items: center;">
							<span style="font-size: 20px;">🔍</span>
							<span style="font-size: 20px;">🛒(3)</span>
						</div>
					</div>
				</header>

				<main style="min-height: 400px; padding: 40px;">
					<div style="display: flex; gap: 30px;">
						<aside style="width: 250px; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
							<h3 style="margin-bottom: 20px; font-size: 18px;">Filters</h3>
							<div style="margin-bottom: 20px;">
								<h4 style="margin-bottom: 10px; font-size: 14px; font-weight: 600;">Categories</h4>
								<label style="display: block; margin-bottom: 8px; font-size: 14px;"><input type="checkbox"> Electronics</label>
								<label style="display: block; margin-bottom: 8px; font-size: 14px;"><input type="checkbox"> Clothing</label>
								<label style="display: block; margin-bottom: 8px; font-size: 14px;"><input type="checkbox"> Accessories</label>
							</div>
							<div>
								<h4 style="margin-bottom: 10px; font-size: 14px; font-weight: 600;">Price Range</h4>
								<input type="range" style="width: 100%;">
								<div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 5px;">
									<span>$0</span>
									<span>$500</span>
								</div>
							</div>
						</aside>

						<div style="flex: 1;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
								<h2 style="font-size: 28px;">All Products</h2>
								<select style="padding: 10px 15px; border-radius: 6px; border: 1px solid #e0e0e0;">
									<option>Sort by: Featured</option>
									<option>Price: Low to High</option>
									<option>Price: High to Low</option>
								</select>
							</div>

							<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px;">
								<?php for ( $i = 1; $i <= 6; $i++ ) : ?>
									<div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
										<div style="height: 160px; background: linear-gradient(135deg, <?php echo esc_attr( $theme['gradient'] ); ?>); display: flex; align-items: center; justify-content: center; color: white; font-size: 40px;">📦</div>
										<div style="padding: 15px;">
											<h3 style="font-size: 15px; font-weight: 600; margin-bottom: 6px;">Product <?php echo $i; ?></h3>
											<p style="color: #666; font-size: 13px; margin-bottom: 10px;">Short description</p>
											<div style="display: flex; align-items: center; justify-content: space-between;">
												<span style="font-weight: 700; font-size: 16px; color: <?php echo esc_attr( $theme['accent'] ); ?>;">$<?php echo ( $i * 19 ); ?>.99</span>
												<button style="background: <?php echo esc_attr( $theme['accent'] ); ?>; color: white; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 13px;">Add</button>
											</div>
										</div>
									</div>
								<?php endfor; ?>
							</div>

							<div style="display: flex; justify-content: center; gap: 10px; margin-top: 40px;">
								<button style="padding: 10px 20px; border: 1px solid #e0e0e0; background: white; border-radius: 6px; cursor: pointer;">← Prev</button>
								<button style="padding: 10px 20px; background: <?php echo esc_attr( $theme['accent'] ); ?>; color: white; border: none; border-radius: 6px; cursor: pointer;">1</button>
								<button style="padding: 10px 20px; border: 1px solid #e0e0e0; background: white; border-radius: 6px; cursor: pointer;">2</button>
								<button style="padding: 10px 20px; border: 1px solid #e0e0e0; background: white; border-radius: 6px; cursor: pointer;">3</button>
								<button style="padding: 10px 20px; border: 1px solid #e0e0e0; background: white; border-radius: 6px; cursor: pointer;">Next →</button>
							</div>
						</div>
					</div>
				</main>

				<footer class="shopglut-footer" style="background: <?php echo esc_attr( $settings['footer_bg'] ); ?>; color: <?php echo esc_attr( $settings['footer_text_color'] ); ?>; padding: 40px; text-align: center;">
					<p style="margin-bottom: 10px;">© 2025 Your Store. All rights reserved.</p>
				</footer>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	private function getProductPageContent( $theme, $settings ) {
		$css = $this->generatePreviewCSS( $theme, $settings );
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Product Preview - <?php echo esc_html( $theme['name'] ); ?></title>
			<style>
				* { margin: 0; padding: 0; box-sizing: border-box; }
				body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
				<?php echo $css; ?>
			</style>
		</head>
		<body>
			<div class="shopglut-preview-wrapper">
				<header class="shopglut-header" style="background: <?php echo esc_attr( $settings['header_bg'] ); ?>; color: <?php echo esc_attr( $settings['header_text_color'] ); ?>; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
					<div style="display: flex; align-items: center; justify-content: space-between;">
						<div style="display: flex; align-items: center; gap: 15px;">
							<div style="width: 45px; height: 45px; background: <?php echo esc_attr( $theme['accent'] ); ?>; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px;">S</div>
							<span style="font-size: 22px; font-weight: 700;">Your Store</span>
						</div>
						<nav style="display: flex; gap: 30px;">
							<a href="#" style="color: inherit; text-decoration: none; font-weight: 500;">Home</a>
							<a href="#" style="color: inherit; text-decoration: none; font-weight: 500;">Shop</a>
						</nav>
						<div style="display: flex; gap: 15px; align-items: center;">
							<span style="font-size: 20px;">🛒</span>
						</div>
					</div>
				</header>

				<main style="min-height: 400px; padding: 60px 40px;">
					<div style="max-width: 1000px; margin: 0 auto;">
						<p style="color: #666; margin-bottom: 20px;">Shop / Products / Premium Product</p>

						<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px;">
							<div>
								<div style="background: linear-gradient(135deg, <?php echo esc_attr( $theme['gradient'] ); ?>); border-radius: 16px; height: 400px; display: flex; align-items: center; justify-content: center; color: white; font-size: 80px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">📦</div>
								<div style="display: flex; gap: 15px; margin-top: 20px;">
									<div style="width: 80px; height: 80px; background: #f5f5f5; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 30px;">📦</div>
									<div style="width: 80px; height: 80px; background: #f5f5f5; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 30px;">📦</div>
									<div style="width: 80px; height: 80px; background: #f5f5f5; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 30px;">📦</div>
									<div style="width: 80px; height: 80px; background: #f5f5f5; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 30px;">📦</div>
								</div>
							</div>

							<div>
								<h1 style="font-size: 36px; font-weight: 700; margin-bottom: 15px;">Premium Product</h1>
								<div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
									<div style="color: #f59e0b; font-size: 20px;">★★★★★</div>
									<span style="color: #666;">(128 reviews)</span>
								</div>
								<div style="font-size: 32px; font-weight: 700; color: <?php echo esc_attr( $theme['accent'] ); ?>; margin-bottom: 25px;">$89.99</div>

								<p style="color: #666; line-height: 1.8; margin-bottom: 30px;">This is a premium quality product with excellent features. Perfect for everyday use with durable materials and modern design.</p>

								<div style="margin-bottom: 25px;">
									<label style="display: block; font-weight: 600; margin-bottom: 10px;">Color:</label>
									<div style="display: flex; gap: 10px;">
										<div style="width: 35px; height: 35px; background: #333; border-radius: 50%; cursor: pointer; border: 3px solid <?php echo esc_attr( $theme['accent'] ); ?>;"></div>
										<div style="width: 35px; height: 35px; background: #fff; border: 2px solid #e0e0e0; border-radius: 50%; cursor: pointer;"></div>
										<div style="width: 35px; height: 35px; background: #22c55e; border-radius: 50%; cursor: pointer;"></div>
										<div style="width: 35px; height: 35px; background: #3b82f6; border-radius: 50%; cursor: pointer;"></div>
									</div>
								</div>

								<div style="margin-bottom: 30px;">
									<label style="display: block; font-weight: 600; margin-bottom: 10px;">Quantity:</label>
									<div style="display: flex; align-items: center; gap: 10px;">
										<button style="width: 40px; height: 40px; border: 1px solid #e0e0e0; background: white; border-radius: 6px; cursor: pointer; font-size: 18px;">-</button>
										<input type="number" value="1" style="width: 60px; height: 40px; text-align: center; border: 1px solid #e0e0e0; border-radius: 6px;">
										<button style="width: 40px; height: 40px; border: 1px solid #e0e0e0; background: white; border-radius: 6px; cursor: pointer; font-size: 18px;">+</button>
									</div>
								</div>

								<div style="display: flex; gap: 15px;">
									<button style="flex: 1; background: <?php echo esc_attr( $theme['accent'] ); ?>; color: white; border: none; padding: 18px; font-size: 16px; font-weight: 600; border-radius: 8px; cursor: pointer;">Add to Cart</button>
									<button style="width: 60px; height: 60px; border: 2px solid #e0e0e0; background: white; border-radius: 8px; cursor: pointer; font-size: 24px;">♡</button>
								</div>
							</div>
						</div>
					</div>
				</main>

				<footer class="shopglut-footer" style="background: <?php echo esc_attr( $settings['footer_bg'] ); ?>; color: <?php echo esc_attr( $settings['footer_text_color'] ); ?>; padding: 40px; text-align: center;">
					<p style="margin-bottom: 10px;">© 2025 Your Store. All rights reserved.</p>
				</footer>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	private function getCartPageContent( $theme, $settings ) {
		$css = $this->generatePreviewCSS( $theme, $settings );
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Cart Preview - <?php echo esc_html( $theme['name'] ); ?></title>
			<style>
				* { margin: 0; padding: 0; box-sizing: border-box; }
				body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
				<?php echo $css; ?>
			</style>
		</head>
		<body>
			<div class="shopglut-preview-wrapper">
				<header class="shopglut-header" style="background: <?php echo esc_attr( $settings['header_bg'] ); ?>; color: <?php echo esc_attr( $settings['header_text_color'] ); ?>; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
					<div style="display: flex; align-items: center; justify-content: space-between;">
						<div style="display: flex; align-items: center; gap: 15px;">
							<div style="width: 45px; height: 45px; background: <?php echo esc_attr( $theme['accent'] ); ?>; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px;">S</div>
							<span style="font-size: 22px; font-weight: 700;">Your Store</span>
						</div>
						<nav style="display: flex; gap: 30px;">
							<a href="#" style="color: inherit; text-decoration: none; font-weight: 500;">Home</a>
							<a href="#" style="color: inherit; text-decoration: none; font-weight: 500;">Shop</a>
						</nav>
						<div style="display: flex; gap: 15px; align-items: center;">
							<span style="font-size: 20px;">🛒(3)</span>
						</div>
					</div>
				</header>

				<main style="min-height: 400px; padding: 40px;">
					<div style="max-width: 1100px; margin: 0 auto;">
						<h1 style="font-size: 32px; font-weight: 700; margin-bottom: 30px;">Shopping Cart</h1>

						<div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
							<div>
								<div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
									<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
										<div style="display: flex; gap: 20px; padding: 25px; border-bottom: 1px solid #f0f0f0;">
											<div style="width: 100px; height: 100px; background: linear-gradient(135deg, <?php echo esc_attr( $theme['gradient'] ); ?>); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 36px;">📦</div>
											<div style="flex: 1;">
												<h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">Product <?php echo $i; ?></h3>
												<p style="color: #666; font-size: 14px; margin-bottom: 12px;">Color: Black | Size: Medium</p>
												<div style="display: flex; align-items: center; gap: 15px;">
													<div style="display: flex; align-items: center; gap: 8px; border: 1px solid #e0e0e0; border-radius: 6px; padding: 5px 10px;">
														<button style="background: none; border: none; cursor: pointer; font-size: 16px;">-</button>
														<span style="font-weight: 600;">1</span>
														<button style="background: none; border: none; cursor: pointer; font-size: 16px;">+</button>
													</div>
													<button style="color: #d63638; background: none; border: none; cursor: pointer; font-size: 14px;">Remove</button>
												</div>
											</div>
											<div style="text-align: right;">
												<div style="font-weight: 700; font-size: 20px; color: <?php echo esc_attr( $theme['accent'] ); ?>;">$<?php echo ( $i * 29 ); ?>.99</div>
											</div>
										</div>
									<?php endfor; ?>
								</div>
							</div>

							<div>
								<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 20px;">
									<h3 style="font-size: 20px; font-weight: 600; margin-bottom: 20px;">Cart Summary</h3>

									<div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px;">
										<span>Subtotal</span>
										<span>$269.97</span>
									</div>
									<div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px;">
										<span>Shipping</span>
										<span>$9.99</span>
									</div>
									<div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 15px;">
										<span>Tax</span>
										<span>$21.60</span>
									</div>

									<div style="border-top: 2px solid #f0f0f0; padding-top: 20px; margin-bottom: 20px;">
										<div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: 700;">
											<span>Total</span>
											<span style="color: <?php echo esc_attr( $theme['accent'] ); ?>;">$301.56</span>
										</div>
									</div>

									<button style="width: 100%; background: <?php echo esc_attr( $theme['accent'] ); ?>; color: white; border: none; padding: 16px; font-size: 16px; font-weight: 600; border-radius: 8px; cursor: pointer; margin-bottom: 15px;">Proceed to Checkout</button>
									<button style="width: 100%; background: white; color: #333; border: 2px solid #e0e0e0; padding: 16px; font-size: 16px; font-weight: 600; border-radius: 8px; cursor: pointer;">Continue Shopping</button>
								</div>
							</div>
						</div>
					</div>
				</main>

				<footer class="shopglut-footer" style="background: <?php echo esc_attr( $settings['footer_bg'] ); ?>; color: <?php echo esc_attr( $settings['footer_text_color'] ); ?>; padding: 40px; text-align: center;">
					<p style="margin-bottom: 10px;">© 2025 Your Store. All rights reserved.</p>
				</footer>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	private function getCheckoutPageContent( $theme, $settings ) {
		$css = $this->generatePreviewCSS( $theme, $settings );
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Checkout Preview - <?php echo esc_html( $theme['name'] ); ?></title>
			<style>
				* { margin: 0; padding: 0; box-sizing: border-box; }
				body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
				<?php echo $css; ?>
			</style>
		</head>
		<body>
			<div class="shopglut-preview-wrapper">
				<header class="shopglut-header" style="background: <?php echo esc_attr( $settings['header_bg'] ); ?>; color: <?php echo esc_attr( $settings['header_text_color'] ); ?>; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
					<div style="display: flex; align-items: center; justify-content: space-between;">
						<div style="display: flex; align-items: center; gap: 15px;">
							<div style="width: 45px; height: 45px; background: <?php echo esc_attr( $theme['accent'] ); ?>; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px;">S</div>
							<span style="font-size: 22px; font-weight: 700;">Your Store</span>
						</div>
						<nav style="display: flex; gap: 30px;">
							<a href="#" style="color: inherit; text-decoration: none; font-weight: 500;">Home</a>
							<a href="#" style="color: inherit; text-decoration: none; font-weight: 500;">Shop</a>
						</nav>
						<div style="display: flex; gap: 15px; align-items: center;">
							<span style="font-size: 20px;">🛒</span>
						</div>
					</div>
				</header>

				<main style="min-height: 400px; padding: 40px;">
					<div style="max-width: 1100px; margin: 0 auto;">
						<h1 style="font-size: 32px; font-weight: 700; margin-bottom: 30px;">Checkout</h1>

						<div style="display: grid; grid-template-columns: 1fr 380px; gap: 30px;">
							<div>
								<!-- Billing Details -->
								<div style="background: white; border-radius: 12px; padding: 30px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
									<h3 style="font-size: 20px; font-weight: 600; margin-bottom: 25px;">Billing Details</h3>

									<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
										<div>
											<label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 14px;">First Name</label>
											<input type="text" placeholder="John" style="width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px;">
										</div>
										<div>
											<label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 14px;">Last Name</label>
											<input type="text" placeholder="Doe" style="width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px;">
										</div>
										<div style="grid-column: 1 / -1;">
											<label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 14px;">Email Address</label>
											<input type="email" placeholder="john@example.com" style="width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px;">
										</div>
										<div style="grid-column: 1 / -1;">
											<label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 14px;">Street Address</label>
											<input type="text" placeholder="123 Main Street" style="width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px;">
										</div>
										<div>
											<label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 14px;">City</label>
											<input type="text" placeholder="New York" style="width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px;">
										</div>
										<div>
											<label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 14px;">ZIP Code</label>
											<input type="text" placeholder="10001" style="width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px;">
										</div>
									</div>
								</div>

								<!-- Payment Method -->
								<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
									<h3 style="font-size: 20px; font-weight: 600; margin-bottom: 25px;">Payment Method</h3>

									<div style="margin-bottom: 20px;">
										<label style="display: flex; align-items: center; gap: 12px; padding: 15px; border: 2px solid <?php echo esc_attr( $theme['accent'] ); ?>; border-radius: 8px; cursor: pointer; background: <?php echo esc_attr( $theme['accent'] ); ?>10;">
											<input type="radio" name="payment" checked style="width: 20px; height: 20px;">
											<div>
												<div style="font-weight: 600;">Credit Card</div>
												<div style="font-size: 13px; color: #666;">Pay with your credit card</div>
											</div>
											<div style="margin-left: auto; font-size: 24px;">💳</div>
										</label>
									</div>
									<div style="margin-bottom: 20px;">
										<label style="display: flex; align-items: center; gap: 12px; padding: 15px; border: 1px solid #e0e0e0; border-radius: 8px; cursor: pointer;">
											<input type="radio" name="payment" style="width: 20px; height: 20px;">
											<div>
												<div style="font-weight: 600;">PayPal</div>
												<div style="font-size: 13px; color: #666;">Pay with PayPal</div>
											</div>
											<div style="margin-left: auto; font-size: 24px;">🅿️</div>
										</label>
									</div>
									<div>
										<label style="display: flex; align-items: center; gap: 12px; padding: 15px; border: 1px solid #e0e0e0; border-radius: 8px; cursor: pointer;">
											<input type="radio" name="payment" style="width: 20px; height: 20px;">
											<div>
												<div style="font-weight: 600;">Cash on Delivery</div>
												<div style="font-size: 13px; color: #666;">Pay when delivered</div>
											</div>
											<div style="margin-left: auto; font-size: 24px;">📦</div>
										</label>
									</div>
								</div>
							</div>

							<!-- Order Summary -->
							<div>
								<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 20px;">
									<h3 style="font-size: 20px; font-weight: 600; margin-bottom: 20px;">Your Order</h3>

									<div style="margin-bottom: 20px;">
										<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
											<div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px;">
												<div>
													<div style="font-weight: 500;">Product <?php echo $i; ?></div>
													<div style="color: #666;">× 1</div>
												</div>
												<div style="font-weight: 600;">$<?php echo ( $i * 29 ); ?>.99</div>
											</div>
										<?php endfor; ?>
									</div>

									<div style="border-top: 2px solid #f0f0f0; padding-top: 20px;">
										<div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px;">
											<span>Subtotal</span>
											<span>$269.97</span>
										</div>
										<div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px;">
											<span>Shipping</span>
											<span>$9.99</span>
										</div>
										<div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 15px;">
											<span>Tax</span>
											<span>$21.60</span>
										</div>
										<div style="border-top: 2px solid #f0f0f0; padding-top: 20px; margin-bottom: 20px;">
											<div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: 700;">
												<span>Total</span>
												<span style="color: <?php echo esc_attr( $theme['accent'] ); ?>;">$301.56</span>
											</div>
										</div>
									</div>

									<button style="width: 100%; background: <?php echo esc_attr( $theme['accent'] ); ?>; color: white; border: none; padding: 18px; font-size: 16px; font-weight: 600; border-radius: 8px; cursor: pointer;">Place Order</button>
								</div>
							</div>
						</div>
					</div>
				</main>

				<footer class="shopglut-footer" style="background: <?php echo esc_attr( $settings['footer_bg'] ); ?>; color: <?php echo esc_attr( $settings['footer_text_color'] ); ?>; padding: 40px; text-align: center;">
					<p style="margin-bottom: 10px;">© 2025 Your Store. All rights reserved.</p>
				</footer>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	private function generatePreviewCSS( $theme, $settings ) {
		return "
			.shopglut-preview-wrapper {
				min-height: 100vh;
				display: flex;
				flex-direction: column;
			}
			.shopglut-header a {
				transition: opacity 0.2s;
			}
			.shopglut-header a:hover {
				opacity: 0.7;
			}
			.shopglut-footer a {
				transition: opacity 0.2s;
			}
			.shopglut-footer a:hover {
				opacity: 0.7;
			}
			button {
				transition: all 0.2s;
			}
			button:hover {
				transform: translateY(-1px);
				box-shadow: 0 4px 12px rgba(0,0,0,0.15);
			}
		";
	}

	public function enqueueThemeStyles() {
		if ( is_admin() ) {
			return;
		}

		$active_theme = get_option( 'shopglut_active_woo_theme', '' );
		if ( empty( $active_theme ) ) {
			return;
		}

		$themes = $this->getAvailableThemes();
		if ( ! isset( $themes[$active_theme] ) ) {
			return;
		}

		$theme = $themes[$active_theme];
		$settings = $this->getThemeSettings( $active_theme );

		// Generate and enqueue dynamic CSS
		$css = $this->generateThemeCSS( $theme, $settings, $active_theme );
		wp_register_style( 'shopglut-woo-theme', false );
		wp_enqueue_style( 'shopglut-woo-theme' );
		wp_add_inline_style( 'shopglut-woo-theme', $css );
	}

	private function generateThemeCSS( $theme, $settings, $active_theme = '' ) {
		$css = '';

		// Custom Header Styles
		if ( $settings['enable_header'] ) {
			$css .= "
				body.shopglut-woo-theme-active .site-header,
				body.shopglut-woo-theme-{$active_theme} header {
					background-color: {$settings['header_bg']} !important;
					color: {$settings['header_text_color']} !important;
				}
				body.shopglut-woo-theme-active .site-header a,
				body.shopglut-woo-theme-{$active_theme} header a {
					color: {$settings['header_text_color']} !important;
				}
			";
		}

		// Custom Footer Styles
		if ( $settings['enable_footer'] ) {
			$css .= "
				body.shopglut-woo-theme-active .site-footer,
				body.shopglut-woo-theme-{$active_theme} footer {
					background-color: {$settings['footer_bg']} !important;
					color: {$settings['footer_text_color']} !important;
				}
				body.shopglut-woo-theme-active .site-footer a,
				body.shopglut-woo-theme-{$active_theme} footer a {
					color: {$settings['footer_text_color']} !important;
				}
			";
		}

		// Accent Color Styles
		$css .= "
			body.shopglut-woo-theme-active .button,
			body.shopglut-woo-theme-active .wc-forward,
			body.shopglut-woo-theme-{$active_theme} .button,
			body.shopglut-woo-theme-{$active_theme} .wc-forward {
				background-color: {$theme['accent']} !important;
				border-color: {$theme['accent']} !important;
				color: #ffffff !important;
			}
			body.shopglut-woo-theme-active .price,
			body.shopglut-woo-theme-{$active_theme} .price {
				color: {$theme['accent']} !important;
			}
		";

		return $css;
	}

	private function getThemeSettings( $theme_id ) {
		$defaults = [
			'enable_header' => true,
			'header_bg' => '#ffffff',
			'header_text_color' => '#333333',
			'enable_footer' => true,
			'footer_bg' => '#333333',
			'footer_text_color' => '#ffffff',
		];

		$saved = get_option( 'shopglut_theme_settings_' . $theme_id, [] );
		return wp_parse_args( $saved, $defaults );
	}

	private function getAvailableThemes() {
		return [
			'simple' => [
				'name' => esc_html__( 'Simple Theme', 'shopglut' ),
				'description' => esc_html__( 'A clean, minimal design with focus on content. Perfect for stores that want a straightforward, professional look.', 'shopglut' ),
				'gradient' => '#667eea, #764ba2',
				'accent' => '#667eea',
				'footer_bg' => '#2c3e50',
				'features' => [
					esc_html__( 'Clean Design', 'shopglut' ),
					esc_html__( 'Fast Loading', 'shopglut' ),
					esc_html__( 'Mobile Friendly', 'shopglut' ),
				],
			],
			'sample' => [
				'name' => esc_html__( 'Sample Theme', 'shopglut' ),
				'description' => esc_html__( 'A beautiful sample theme showcasing various customization options for your WooCommerce store.', 'shopglut' ),
				'gradient' => '#f093fb, #f5576c',
				'accent' => '#f5576c',
				'footer_bg' => '#1a1a2e',
				'features' => [
					esc_html__( 'Modern Design', 'shopglut' ),
					esc_html__( 'Color Options', 'shopglut' ),
					esc_html__( 'Customizable', 'shopglut' ),
				],
			],
		];
	}
}
