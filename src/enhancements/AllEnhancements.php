<?php
namespace Shopglut\enhancements;

if ( ! defined( 'ABSPATH' ) ) exit;

use Shopglut\enhancements\Filters\FilterListTable;

use Shopglut\enhancements\Filters\FiltersEntity;

use  Shopglut\enhancements\Filters\FiltersSettingsPage;



use Shopglut\enhancements\ProductBadges\BadgeListTable;

use Shopglut\enhancements\ProductBadges\BadgeEntity;


use Shopglut\enhancements\ProductBadges\BadgesSettingsPage;

use Shopglut\enhancements\ProductBadges\BadgeTemplates;

use Shopglut\enhancements\ProductBadges\BadgechooseTemplates;



use Shopglut\enhancements\ProductQuickView\QuickViewListTable;

use Shopglut\enhancements\ProductQuickView\QuickViewEntity;

use Shopglut\enhancements\ProductQuickView\QuickViewSettingsPage;

use Shopglut\enhancements\ProductQuickView\QuickViewchooseTemplates as QuickViewTemplates;

use Shopglut\enhancements\ProductComparison\ProductComparisonListTable;

use Shopglut\enhancements\ProductComparison\ProductComparisonEntity;

use Shopglut\enhancements\ProductComparison\SettingsPage as ComparisonSettings;

use Shopglut\enhancements\ProductComparison\ComparisonchooseTemplates as ComparisonTemplates;

use Shopglut\enhancements\ProductSwatches\ProductSwatchesListTable;

use Shopglut\enhancements\ProductSwatches\ProductSwatchesEntity;

use Shopglut\enhancements\ProductSwatches\SettingsPage as ProductSwatchesSettings;

use Shopglut\enhancements\ProductSwatches\chooseTemplates as SwatchesChooseTemplates;

use Shopglut\enhancements\ProductSwatches\AttributeSwatchesManager;


class AllEnhancements {

	public $not_implemented;

	public function __construct() {

		add_filter( 'admin_body_class', array( $this, 'shopglutBodyClass' ) );
		add_action( 'admin_post_create_badge', array( $this, 'handleCreateBadge' ) );

        $this->not_implemented = true;

	}

	public function shopglutBodyClass( $classes ) {
        $current_screen = get_current_screen();

        if ( empty( $current_screen ) ) {
            return $classes;
        }
        
        if ( false !== strpos( $current_screen->id, 'shopglut_' ) ) {
            $classes .= ' shopglut-admin';
        }

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for body class only
		if ( isset( $_GET['page'] ) && 'shopglut_enhancements' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) && isset( $_GET['editor'] ) ) {
			$classes .= '-shopglut-editor-collapse ';
		}


        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for CSS class addition only
        if ( isset( $_GET['page'] ) && 'shopglut_enhancements' === sanitize_text_field( wp_unslash($_GET['page'] )) ) {
            
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for CSS class addition only
            if ( isset( $_GET['editor'] ) ) {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for CSS class addition only
                $editor = sanitize_text_field( wp_unslash($_GET['editor']) );
                
                switch ( $editor ) {
                    case 'filters':
                        $classes .= '-shopglut-filters-editor ';
                        $classes .= ' shopglut-fullwidth-editor ';
                        break;
                    case 'product_badges':
                        $classes .= '-shopglut-badges-editor ';
                        $classes .= ' shopglut-fullwidth-editor ';
                        break;
                }
            }
        }

        return $classes;
   }

	public function renderenhancementsPages() {

		// Check user permissions first
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shopglut' ) );
		}


			// === COMMENTED OUT ===
			// 					$filter_settings = new FiltersSettingsPage();
			// 		$badge_settings = new BadgesSettingsPage();
			// 	    $comparison_settings = new ComparisonSettings();
			// $quickview_settings = new QuickViewSettingsPage();


		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for routing only
		if ( ! isset( $_GET['page'] ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'shopglut' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for routing only
		$page = sanitize_text_field( wp_unslash( $_GET['page'] ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for routing only
		$editor = isset( $_GET['editor'] ) ? sanitize_text_field( wp_unslash( $_GET['editor'] ) ) : '';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for routing only
		$view = isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : '';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for routing only
		$layout_id = isset( $_GET['layout_id'] ) ? sanitize_text_field( wp_unslash( $_GET['layout_id'] ) ) : '';// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for routing only

	     // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for routing only
		$filter_id = isset( $_GET['filter_id'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_id'] ) ) : '';

	     // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for routing only
		$badge_id = isset( $_GET['badge_id'] ) ? sanitize_text_field( wp_unslash( $_GET['badge_id'] ) ) : '';


		// Handle shopglut_enhancements page
		if ( 'shopglut_enhancements' === $page ) {

			
			// Editor routes with layout_id
			if ( ! empty( $editor ) && (! empty( $layout_id ) || (!empty($filter_id)) || (!empty($badge_id)))) {
				switch ( $editor ) {
					case 'filters':
						$filter_settings->FilterSettings();
						break;
					case 'product_badges':
						$badge_settings->BadgeEditor();
						break;
					case 'product_swatches':
						$swatches_settings = new ProductSwatchesSettings();
						$swatches_settings->render();
						break;
					case 'product_comparison':
						$comparison_settings->loadProductComparisonEditor();
						break;
					case 'product_quickview':
						$quickview_settings->loadProductQuickviewEditor();
						break;

					case 'product_quickview':
						$quickview_settings->loadAcfEditor();
						break;

					default:
						wp_die( esc_html__( 'Invalid editor type.', 'shopglut' ) );
				}
			}
			// View routes with parameters
			elseif ( ! empty( $view ) && isset( $_GET['attribute'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for routing only
				$attribute = sanitize_text_field( wp_unslash( $_GET['attribute'] ) );

				switch ( $view ) {
					case 'product_swatches_templates':
						$this->renderSwatchesTemplates( $attribute );
						break;
					default:
						break;
				}
			}
			// Template view routes
			elseif ( ! empty( $view ) ) {
				switch ( $view ) {
					
					case 'product_badges':
						$this->renderProductBadges();
						break;
					case 'product_badge_templates':
						$this->renderBadgeTemplates();
						break;
					case 'product_comparisons':
						$this->renderProductComparison();
						break;
					case 'product_quickviews':
						$this->renderQuickView();
						break;
					case 'product_comparison_templates':
						$this->renderProductComparisonTemplates();
						break;
					case 'product_quick_view_templates':
						$this->renderQuickViewTemplates();
						break;
					case 'wishlist':
						$this->renderWishlist();
						break;
					case 'shop_filters':
						$this->renderShopFilters();
						break;
					case 'filter_another':
						$this->renderFiltersTable();
						break;
					case 'product_swatches':
						$this->renderProductSwatches();
						break;
					case 'product_swatches_templates':
						$this->renderSwatchesTemplates();
						break;
					case 'attribute_swatches':
						$this->renderAttributeSwatches();
						break;
					default:
						// Default case - you can uncomment if needed
						// $this->renderenhancementsTable();
						break;
				}
			}
			// Default shopglut_enhancements page
			else {
				$this->renderWooCommerceEnhancements();
			}
		}
		else {
			wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'shopglut' ) );
		}
   }

	public function settingsPageHeader( $active_menu ) {
		$logo_url = SHOPGLUT_URL . 'global-assets/images/header-logo.svg';
		?>
		<div class="shopglut-page-header">
			<div class="shopglut-page-header-wrap">
				<div class="shopglut-page-header-banner shopglut-pro shopglut-no-submenu">
					<div class="shopglut-page-header-banner__logo">
						<img src="<?php echo esc_url( $logo_url );// phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>" alt="">
					</div>
					<div class="shopglut-page-header-banner__helplinks">
						<span><a rel="noopener"
								href="https://documentation.appglut.com/?utm_source=shoglutplugin-admin&utm_medium=referral&utm_campaign=adminmenu"
								target="_blank">
								<span class="dashicons dashicons-admin-page"></span>
								<?php echo esc_html__( 'Documentation', 'shopglut' ); ?>
							</a></span>
						<span><a class="shopglut-active" rel="noopener"
								href="https://www.appglut.com/plugin/shopglut/?utm_source=shoglutplugin-admin&utm_medium=referral&utm_campaign=upgrade"
								target="_blank">
								<span class="dashicons dashicons-unlock"></span>
								<?php echo esc_html__( 'Unlock Pro Edition', 'shopglut' ); ?>
							</a></span>
						<span><a rel="noopener"
								href="https://www.appglut.com/support/?utm_source=shoglutplugin-admin&utm_medium=referral&utm_campaign=support"
								target="_blank">
								<span class="dashicons dashicons-share-alt"></span>
								<?php echo esc_html__( 'Support', 'shopglut' ); ?>
							</a></span>
					</div>
					<div class="clear"></div>
					<?php $this->settingsPageHeaderMenus( $active_menu ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	public function renderNotImplementedMessage() {
		?>
		<div style="padding: 50px 30px; background: #fff; margin: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-radius: 6px;">
			<div style="text-align: center;">
				<div style="font-size: 48px; margin-bottom: 20px; opacity: 0.6;">
					🚧
				</div>
				<h1 style="color: #dc3232; font-size: 24px; font-weight: 500; margin: 0 0 20px 0; line-height: 1.4;">
					<?php echo esc_html__( 'Feature Not Available', 'shopglut' ); ?>
				</h1>
				<p style="color: #666; font-size: 16px; margin: 0 0 25px 0; line-height: 1.6; max-width: 500px; margin-left: auto; margin-right: auto;">
					<?php echo esc_html__( 'This feature is currently under development. We are working to bring you enhanced functionality and customization options.', 'shopglut' ); ?>
				</p>
				<div style="padding: 15px; background: #f0f6fc; border: 1px solid #c3d9ed; border-radius: 4px; margin: 20px auto; max-width: 400px;">
					<p style="color: #0073aa; font-size: 14px; margin: 0; font-weight: 500;">
						<?php echo esc_html__( 'Please check back for updates in future releases.', 'shopglut' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	public function settingsPageHeaderMenus( $active_menu ) {

		$menus = $this->headerMenuTabs();

		if ( count( $menus ) < 2 ) {
			return;
		}

		?>
		<div class="shopglut-header-menus">
			<nav class="shopglut-nav-tab-wrapper nav-tab-wrapper">
				<?php foreach ( $menus as $menu ) : ?>
					<?php $id = $menu['id'];
					$url = esc_url_raw( ! empty( $menu['url'] ) ? $menu['url'] : '' );
					?>
					<a href="<?php echo esc_url( remove_query_arg( wp_removable_query_args(), $url ) ); ?>"
						class="shopglut-nav-tab nav-tab<?php echo esc_attr( $id ) == esc_attr( $active_menu ) ? ' shopglut-nav-active' : ''; ?>">
						<?php echo esc_html( $menu['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
		</div>
		<?php
	}

	public function defaultHeaderMenu() {
		return 'all_enhancements';
	}

	public function headerMenuTabs() {
			$tabs = [
				1 => [ 'id' => 'all_enhancements', 'url' => admin_url( 'admin.php?page=shopglut_enhancements' ), 'label' => '✨ ' . esc_html__( 'All Enhancements', 'shopglut' ) ],
				5 => [ 'id' => 'wishlist', 'url' => admin_url( 'admin.php?page=shopglut_enhancements&view=wishlist' ), 'label' => '❤️ ' . esc_html__( 'Wishlist', 'shopglut' ) ],
				10 => [ 'id' => 'shop_filters', 'url' => admin_url( 'admin.php?page=shopglut_enhancements&view=shop_filters' ), 'label' => '🔍 ' . esc_html__( 'Shop Filters', 'shopglut' ) ],
				15 => [ 'id' => 'product_swatches', 'url' => admin_url( 'admin.php?page=shopglut_enhancements&view=product_swatches' ), 'label' => '🎨 ' . esc_html__( 'Product Swatches', 'shopglut' ) ],
				20 => [ 'id' => 'product_badges', 'url' => admin_url( 'admin.php?page=shopglut_enhancements&view=product_badges' ), 'label' => '🏷️ ' . esc_html__( 'Product Badges', 'shopglut' ) ],
				25 => [ 'id' => 'product_comparisons', 'url' => admin_url( 'admin.php?page=shopglut_enhancements&view=product_comparisons' ), 'label' => '⚖️ ' . esc_html__( 'Product Comparison', 'shopglut' ) ],
				30 => [ 'id' => 'product_quickviews', 'url' => admin_url( 'admin.php?page=shopglut_enhancements&view=product_quickviews' ), 'label' => '⚡ ' . esc_html__( 'Quick View', 'shopglut' ) ],
			];

			ksort( $tabs );

			return $tabs;
	}

	public function activeMenuTab() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for menu display only
			if ( ! isset( $_GET['page'] ) ) {
				return false;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for menu display only
			$page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
			
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for menu display only
			$nonce_check = isset( $_GET['url_nonce_check'] ) ? sanitize_text_field( wp_unslash( $_GET['url_nonce_check'] ) ) : '';
			
			if ( ( ! wp_verify_nonce( $nonce_check, 'url_nonce_value' ) ) && ( strpos( $page, 'shopglut' ) !== false ) ) {
				// If no view parameter, we're on the main landing page (all_enhancements)
				if ( !isset( $_GET['view'] ) && isset( $_GET['page'] ) && $_GET['page'] === 'shopglut_enhancements' ) {
					return 'all_enhancements';
				}
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for menu display only
				return isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : $this->defaultHeaderMenu();
			}

			return false;
	}

	public function renderenhancementsTable() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shopglut' ) );
		}

		// Handle individual delete action
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification handled below
		if ( isset( $_GET['action'] ) && 'delete' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) && isset( $_GET['layout_id'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification handled below
			$layout_id = absint( wp_unslash( $_GET['layout_id'] ) );

			// Verify nonce
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This IS the nonce verification
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'shopglut_delete_layout_' . $layout_id ) ) {
				// Delete the layout
				LayoutEntity::delete_layout( $layout_id );

				// Redirect to avoid resubmission
				wp_safe_redirect( admin_url( 'admin.php?page=shopglut_enhancements&deleted=true' ) );
				exit;
			} else {
				wp_die( esc_html__( 'Security check failed.', 'shopglut' ) );
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin page parameter check for success message display only
		if ( isset( $_GET['deleted'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['deleted'] ) ) ) {
			echo '<div class="updated notice"><p>' . esc_html__( 'Layout deleted successfully.', 'shopglut' ) . '</p></div>';
		}
		
		$enhancements_table = new ShopListTable();
		$enhancements_table->prepare_items();
		$active_menu = $this->activeMenuTab();
		$this->settingsPageHeader( $active_menu );
		?>
		<div class="wrap shopglut-admin-contents">
			<h2><?php echo esc_html__( 'enhancements', 'shopglut' ); ?><a
					href="<?php echo esc_url( admin_url( 'admin.php?page=shopglut_enhancements&view=shop_templates' ) ); ?>"><span
						class="add-new-h2"><?php echo esc_html__( 'Add New Layout', 'shopglut' ); ?></span></a></h2>
			<form method="post">
				<?php $enhancements_table->display(); ?>
			</form>
		</div>
		<?php
	}
	
	public function renderWooCommerceEnhancements() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shopglut' ) );
		}

		$active_menu = $this->activeMenuTab();
		$this->settingsPageHeader( $active_menu );
		?>
		<div class="wrap shopglut-admin-contents">
			<h2 style="text-align: center; font-weight: bold;"><?php echo esc_html__( 'WooCommerce Enhancements', 'shopglut' ); ?></h2>
			<p class="subheading" style="text-align: center;">
				<?php echo esc_html__( 'Configure and manage your WooCommerce store enhancements', 'shopglut' ); ?>
			</p>

			<div class="shopglut-enhancements-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">

				<!-- Wishlist -->
				<div class="shopglut-option-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
					<div class="option-header" style="display: flex; align-items: center; margin-bottom: 15px;">
						<i class="fas fa-heart" style="font-size: 24px; color: #667eea; margin-right: 12px;"></i>
						<h3 style="margin: 0; color: #333;"><?php echo esc_html__( 'Wishlist', 'shopglut' ); ?></h3>
					</div>
					<p style="color: #666; margin-bottom: 15px;"><?php echo esc_html__( 'Enable and configure wishlist functionality for your customers.', 'shopglut' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=shopglut_enhancements&view=wishlist' ) ); ?>" class="button button-primary"><?php echo esc_html__( 'Manage Wishlist', 'shopglut' ); ?></a>
				</div>

				<!-- Shop Filters -->
				<div class="shopglut-option-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
					<div class="option-header" style="display: flex; align-items: center; margin-bottom: 15px;">
						<i class="fas fa-filter" style="font-size: 24px; color: #667eea; margin-right: 12px;"></i>
						<h3 style="margin: 0; color: #333;"><?php echo esc_html__( 'Shop Filters', 'shopglut' ); ?></h3>
					</div>
					<p style="color: #666; margin-bottom: 15px;"><?php echo esc_html__( 'Configure advanced filtering enhancements for your shop pages.', 'shopglut' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=shopglut_enhancements&view=shop_filters' ) ); ?>" class="button button-primary"><?php echo esc_html__( 'Setup Filters', 'shopglut' ); ?></a>
				</div>

				<!-- Product Swatches -->
				<div class="shopglut-option-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
					<div class="option-header" style="display: flex; align-items: center; margin-bottom: 15px;">
						<i class="fas fa-palette" style="font-size: 24px; color: #667eea; margin-right: 12px;"></i>
						<h3 style="margin: 0; color: #333;"><?php echo esc_html__( 'Product Swatches', 'shopglut' ); ?></h3>
					</div>
					<p style="color: #666; margin-bottom: 15px;"><?php echo esc_html__( 'Create color and image swatches for product variations.', 'shopglut' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=shopglut_enhancements&view=product_swatches' ) ); ?>" class="button button-primary"><?php echo esc_html__( 'Configure Swatches', 'shopglut' ); ?></a>
				</div>

				<!-- Product Badges -->
				<div class="shopglut-option-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
					<div class="option-header" style="display: flex; align-items: center; margin-bottom: 15px;">
						<i class="fas fa-tags" style="font-size: 24px; color: #667eea; margin-right: 12px;"></i>
						<h3 style="margin: 0; color: #333;"><?php echo esc_html__( 'Product Badges', 'shopglut' ); ?></h3>
					</div>
					<p style="color: #666; margin-bottom: 15px;"><?php echo esc_html__( 'Create and manage promotional badges for your products.', 'shopglut' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=shopglut_enhancements&view=product_badges' ) ); ?>" class="button button-primary"><?php echo esc_html__( 'Manage Badges', 'shopglut' ); ?></a>
				</div>

				<!-- Product Comparison -->
				<div class="shopglut-option-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
					<div class="option-header" style="display: flex; align-items: center; margin-bottom: 15px;">
						<i class="fas fa-balance-scale" style="font-size: 24px; color: #667eea; margin-right: 12px;"></i>
						<h3 style="margin: 0; color: #333;"><?php echo esc_html__( 'Product Comparison', 'shopglut' ); ?></h3>
					</div>
					<p style="color: #666; margin-bottom: 15px;"><?php echo esc_html__( 'Allow customers to compare products side by side.', 'shopglut' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=shopglut_enhancements&view=product_comparisons' ) ); ?>" class="button button-primary"><?php echo esc_html__( 'Setup Comparison', 'shopglut' ); ?></a>
				</div>

				<!-- Quick View -->
				<div class="shopglut-option-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
					<div class="option-header" style="display: flex; align-items: center; margin-bottom: 15px;">
						<i class="fa-solid fa-forward-fast" style="font-size: 24px; color: #667eea; margin-right: 12px;"></i>
						<h3 style="margin: 0; color: #333;"><?php echo esc_html__( 'Quick View', 'shopglut' ); ?></h3>
					</div>
					<p style="color: #666; margin-bottom: 15px;"><?php echo esc_html__( 'Enable quick view popups for products on shop pages.', 'shopglut' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=shopglut_enhancements&view=product_quickviews' ) ); ?>" class="button button-primary"><?php echo esc_html__( 'Configure Quick View', 'shopglut' ); ?></a>
				</div>

			</div>
		</div>
		<?php
	}

			public function renderProductBadges() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shopglut' ) );
			}

			$active_menu = $this->activeMenuTab();
			$this->settingsPageHeader( $active_menu );
			?>
			<div class="wrap shopglut-admin-contents">
				<h2><?php echo esc_html__( 'Product Badges', 'shopglut' ); ?></h2>
				<div class="shopglut-feature-not-added" style="max-width: 700px; margin: 40px auto;">
					<div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #d63638; padding: 40px; text-align: center; border-radius: 8px;">
						<div style="font-size: 64px; margin-bottom: 20px;">
							<span class="dashicons dashicons-tag" style="color: #d63638; font-size: 64px; width: 64px; height: 64px;"></span>
						</div>
						<h2 style="color: #1d2327; font-size: 28px; margin: 0 0 15px 0;">
							<?php esc_html_e( 'Feature Not Added', 'shopglut' ); ?>
						</h2>
						<p style="color: #50575e; font-size: 16px; margin: 0 0 20px 0; max-width: 500px; margin-left: auto; margin-right: auto;">
							<?php esc_html_e( 'The product badges feature has not been added yet. This feature will be available in a future update.', 'shopglut' ); ?>
						</p>
					</div>
				</div>
			</div>
			<?php
			return;
			}

	public function renderWishlist() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shopglut' ) );
		}

		$active_menu = $this->activeMenuTab();
		$this->settingsPageHeader( $active_menu );
		?>
		<div class="wrap shopglut-admin-contents">
			<h2><?php echo esc_html__( 'Wishlist', 'shopglut' ); ?></h2>
			<div class="shopglut-feature-not-added" style="max-width: 700px; margin: 40px auto;">
				<div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #d63638; padding: 40px; text-align: center; border-radius: 8px;">
					<div style="font-size: 64px; margin-bottom: 20px;">
						<span class="dashicons dashicons-heart" style="color: #d63638; font-size: 64px; width: 64px; height: 64px;"></span>
					</div>
					<h2 style="color: #1d2327; font-size: 28px; margin: 0 0 15px 0;">
						<?php esc_html_e( 'Feature Not Added', 'shopglut' ); ?>
					</h2>
					<p style="color: #50575e; font-size: 16px; margin: 0 0 20px 0; max-width: 500px; margin-left: auto; margin-right: auto;">
						<?php esc_html_e( 'The wishlist feature has not been added yet. This feature will be available in a future update.', 'shopglut' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
		return;
	}

			public function renderShopFilters() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shopglut' ) );
			}

			$active_menu = $this->activeMenuTab();
			$this->settingsPageHeader( $active_menu );
			?>
			<div class="wrap shopglut-admin-contents">
				<h2><?php echo esc_html__( 'Shop Filters', 'shopglut' ); ?></h2>
				<div class="shopglut-feature-not-added" style="max-width: 700px; margin: 40px auto;">
					<div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #d63638; padding: 40px; text-align: center; border-radius: 8px;">
						<div style="font-size: 64px; margin-bottom: 20px;">
							<span class="dashicons dashicons-filter" style="color: #d63638; font-size: 64px; width: 64px; height: 64px;"></span>
						</div>
						<h2 style="color: #1d2327; font-size: 28px; margin: 0 0 15px 0;">
							<?php esc_html_e( 'Feature Not Added', 'shopglut' ); ?>
						</h2>
						<p style="color: #50575e; font-size: 16px; margin: 0 0 20px 0; max-width: 500px; margin-left: auto; margin-right: auto;">
							<?php esc_html_e( 'The shop filters feature has not been added yet. This feature will be available in a future update.', 'shopglut' ); ?>
						</p>
					</div>
				</div>
			</div>
			<?php
			return;
			}

	public function renderFiltersTable() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shopglut' ) );
		}

		// Handle individual delete action
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['filter_id'] ) ) {
			$filter_id = absint( $_GET['filter_id'] );

			// Verify nonce
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'shopglut_delete_filter_' . $filter_id ) ) {
				// Delete the layout
				FiltersEntity1::delete_layout( $filter_id );

				// Redirect to avoid resubmission
				wp_safe_redirect( admin_url( 'admin.php?page=shopglut_enhancements&deleted=true' ) );
				exit;
			} else {
				wp_die( esc_html__( 'Security check failed.', 'shopglut' ) );
			}
		}

		if ( isset( $_GET['deleted'] ) && $_GET['deleted'] === 'true' ) {
			echo '<div class="updated notice"><p>' . esc_html__( 'Filter deleted successfully.', 'shopglut' ) . '</p></div>';
		}

		$filters_table = new FilterListTable1();
		$filters_table->prepare_items();
		$active_menu = $this->activeMenuTab();
		$this->settingsPageHeader( $active_menu );
		?>
		<div class="wrap shopglut-admin-contents">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="create_filter1">
				<?php global $wpdb;
				$table_name = $wpdb->prefix . 'shopglut_enhancements_filters';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table query, table name is safe (uses $wpdb->prefix)
				$filter_id = intval( $wpdb->get_var( "SELECT MAX(id) FROM {$table_name}" ) );
				$filter_id = $filter_id ? $filter_id + 1 : 1;
				?>
				<input type="hidden" name="filter_id" value="<?php echo esc_attr( $filter_id ); ?>">
				<?php wp_nonce_field( 'create_filter1_nonce', 'create_filter1_nonce' ); ?>
				<div class="wrap">
					<h2><?php echo esc_html__( 'Shop Filters ', 'shopglut' ); ?><input class="add-new-h2" type="submit"
							name="publish" id="publish" value="<?php echo esc_html__( "Add New Filter", 'shopglut' ); ?>" />
					</h2>
				</div>

			</form>

			<form method="post">
				<?php $filters_table->display(); ?>
			</form>

		</div>
		<?php
	}

			public function renderProductSwatches() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shopglut' ) );
			}

			$active_menu = $this->activeMenuTab();
			$this->settingsPageHeader( $active_menu );
			?>
			<div class="wrap shopglut-admin-contents">
				<h2><?php echo esc_html__( 'Product Swatches', 'shopglut' ); ?></h2>
				<div class="shopglut-feature-not-added" style="max-width: 700px; margin: 40px auto;">
					<div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #d63638; padding: 40px; text-align: center; border-radius: 8px;">
						<div style="font-size: 64px; margin-bottom: 20px;">
							<span class="dashicons dashicons-art" style="color: #d63638; font-size: 64px; width: 64px; height: 64px;"></span>
						</div>
						<h2 style="color: #1d2327; font-size: 28px; margin: 0 0 15px 0;">
							<?php esc_html_e( 'Feature Not Added', 'shopglut' ); ?>
						</h2>
						<p style="color: #50575e; font-size: 16px; margin: 0 0 20px 0; max-width: 500px; margin-left: auto; margin-right: auto;">
							<?php esc_html_e( 'The product swatches feature has not been added yet. This feature will be available in a future update.', 'shopglut' ); ?>
						</p>
					</div>
				</div>
			</div>
			<?php
			return;
			}

	public function renderSwatchesTemplates( $attribute = '' ) {
		$active_menu = 'product_swatches';
		$this->settingsPageHeader( $active_menu );

		// Get attribute label if attribute is provided
		$attribute_label = '';
		if ( ! empty( $attribute ) ) {
			// Get attribute taxonomies
			$attribute_taxonomies = wc_get_attribute_taxonomies();

			// Extract attribute name from taxonomy (remove 'pa_' prefix)
			$attr_name = str_replace( 'pa_', '', $attribute );

			// Find matching attribute
			foreach ( $attribute_taxonomies as $attr ) {
				if ( $attr->attribute_name === $attr_name ) {
					$attribute_label = $attr->attribute_label;
					break;
				}
			}

			// Fallback to taxonomy name if label not found
			if ( empty( $attribute_label ) ) {
				$attribute_label = ucwords( str_replace( '_', ' ', $attr_name ) );
			}
		}

		$swatches_templates = new SwatchesChooseTemplates();
		?>
		<div class="wrap shopglut-admin-contents shoplayouts-templates">
			<div class="shopglut-templates-header">
				<h1>
					<?php
					if ( ! empty( $attribute_label ) ) {
						printf( esc_html__( 'Choose Template for: %s', 'shopglut' ), esc_html( $attribute_label ) );
					} else {
						esc_html_e( 'PreBuilt Swatches Templates', 'shopglut' );
					}
					?>
				</h1>
				<?php if ( ! empty( $attribute_label ) ) : ?>
					<p class="subheading">
						<?php
						printf( esc_html__( 'Select a template to customize the %s attribute display', 'shopglut' ), esc_html( $attribute_label ) );
						?>
					</p>
				<?php else : ?>
					<p class="subheading"><?php esc_html_e( 'Choose your desired template to customize', 'shopglut' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php $swatches_templates->loadProductSwatchesTemplates( $attribute );
	}

	public function renderAttributeSwatches() {
		$active_menu = 'product_swatches';
		$this->settingsPageHeader( $active_menu );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shopglut' ) );
		}

		// Check if product_swatches module is enabled
		$module_manager = \Shopglut\ModuleManager::get_instance();
		if ( ! $module_manager->is_module_enabled( 'product_swatches' ) ) {
			$module_manager->render_disabled_module_message( 'product_swatches' );
			return;
		}

		// Initialize and render the attribute swatches manager
		$attribute_manager = AttributeSwatchesManager::get_instance();
		$attribute_manager->render_manager_page();
	}

			public function renderProductComparison() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shopglut' ) );
			}

			$active_menu = $this->activeMenuTab();
			$this->settingsPageHeader( $active_menu );
			?>
			<div class="wrap shopglut-admin-contents">
				<h2><?php echo esc_html__( 'Product Comparison', 'shopglut' ); ?></h2>
				<div class="shopglut-feature-not-added" style="max-width: 700px; margin: 40px auto;">
					<div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #d63638; padding: 40px; text-align: center; border-radius: 8px;">
						<div style="font-size: 64px; margin-bottom: 20px;">
							<span class="dashicons dashicons-list-view" style="color: #d63638; font-size: 64px; width: 64px; height: 64px;"></span>
						</div>
						<h2 style="color: #1d2327; font-size: 28px; margin: 0 0 15px 0;">
							<?php esc_html_e( 'Feature Not Added', 'shopglut' ); ?>
						</h2>
						<p style="color: #50575e; font-size: 16px; margin: 0 0 20px 0; max-width: 500px; margin-left: auto; margin-right: auto;">
							<?php esc_html_e( 'The product comparison feature has not been added yet. This feature will be available in a future update.', 'shopglut' ); ?>
						</p>
					</div>
				</div>
			</div>
			<?php
			return;
			}

			public function renderQuickView() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shopglut' ) );
			}

			$active_menu = $this->activeMenuTab();
			$this->settingsPageHeader( $active_menu );
			?>
			<div class="wrap shopglut-admin-contents">
				<h2><?php echo esc_html__( 'Quick View', 'shopglut' ); ?></h2>
				<div class="shopglut-feature-not-added" style="max-width: 700px; margin: 40px auto;">
					<div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #d63638; padding: 40px; text-align: center; border-radius: 8px;">
						<div style="font-size: 64px; margin-bottom: 20px;">
							<span class="dashicons dashicons-visibility" style="color: #d63638; font-size: 64px; width: 64px; height: 64px;"></span>
						</div>
						<h2 style="color: #1d2327; font-size: 28px; margin: 0 0 15px 0;">
							<?php esc_html_e( 'Feature Not Added', 'shopglut' ); ?>
						</h2>
						<p style="color: #50575e; font-size: 16px; margin: 0 0 20px 0; max-width: 500px; margin-left: auto; margin-right: auto;">
							<?php esc_html_e( 'The quick view feature has not been added yet. This feature will be available in a future update.', 'shopglut' ); ?>
						</p>
					</div>
				</div>
			</div>
			<?php
			return;
			}

	public function renderProductComparisonTemplates() {
		$active_menu = 'product_comparisons';
		$this->settingsPageHeader( $active_menu );
		$comparisonLayout_templates = new ComparisonTemplates();
		?>
		<div class="wrap shopglut-admin-contents shoplayouts-templates">
			<h1><?php echo esc_html__( 'PreBuilt Comparison Layout Templates', 'shopglut' ); ?></h1>
			<p class="subheading"><?php echo esc_html__( 'Choose your desired template to customize', 'shopglut' ); ?></p>
		</div>
		<?php $comparisonLayout_templates->loadProductComparisonTemplates();
	}

	public function renderQuickViewTemplates() {
		$active_menu = 'product_quickviews';
		$this->settingsPageHeader( $active_menu );
		$quickviewLayout_templates = new QuickViewTemplates();
		?>
		<div class="wrap shopglut-admin-contents shoplayouts-templates">
			<h1><?php echo esc_html__( 'PreBuilt Quick View Layout Templates', 'shopglut' ); ?></h1>
			<p class="subheading"><?php echo esc_html__( 'Choose your desired template to customize', 'shopglut' ); ?></p>
		</div>
		<?php $quickviewLayout_templates->loadProductQuickviewTemplates();
	}

	public function renderBadgeTemplates() {
		$active_menu = 'product_badges';
		$this->settingsPageHeader( $active_menu );
		$badge_templates = new BadgechooseTemplates();
		?>
		<div class="wrap shopglut-admin-contents shoplayouts-templates">
			<h1><?php echo esc_html__( 'PreBuilt Badge Templates', 'shopglut' ); ?></h1>
			<p class="subheading"><?php echo esc_html__( 'Choose your desired template to customize', 'shopglut' ); ?></p>
		</div>
		<?php $badge_templates->loadProductBadgeTemplates();
	}

	public function handleCreateBadge() {
		if (
			isset( $_POST['create_badge_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['create_badge_nonce'] ) ), 'create_badge_nonce' ) &&
			current_user_can( 'manage_options' )
		) {
			// Get next available ID
			global $wpdb;
			$table_name = $wpdb->prefix . 'shopglut_product_badge_layouts';
			$max_id_sql = $wpdb->prepare( "SELECT MAX(id) FROM `%s`", $table_name );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name prepared, no user input
			$max_id = intval( $wpdb->get_var( $max_id_sql ) );
			$badge_id = $max_id ? $max_id + 1 : 1;

			$badge_name = sanitize_text_field( 'Badge(#' . $badge_id . ')' );

			// Create default badge data
			$default_badge_data = json_encode(array(
				'text' => 'New Badge',
				'style' => array(
					'background_color' => '#ff0000',
					'text_color' => '#fff',
					'font_size' => 12,
					'border_radius' => 3,
					'padding' => '5px 10px',
					'position' => 'top-left'
				)
			));

			// Create badge using direct database query
			global $wpdb;
			$table_name = \Shopglut\ShopGlutDatabase::table_product_badges();

			$badge_settings = array(
				'shopg_product_badge_settings' => $default_badge_data
			);

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table operation
			$result = $wpdb->insert(
				$table_name,
				array(
					'layout_name' => $badge_name,
					'layout_template' => 'template1',
					'layout_settings' => serialize($badge_settings),
					'created_at' => current_time('mysql')
				),
				array('%s', '%s', '%s', '%s')
			);

			$new_badge_id = $result ? $wpdb->insert_id : false;

			if ( $new_badge_id ) {
				$redirect_url = admin_url( 'admin.php?page=shopglut_enhancements&editor=product_badges&badge_id=' . $new_badge_id );
				wp_safe_redirect( $redirect_url );
				exit;
			} else {
				wp_die( esc_html__( 'Database insertion error', 'shopglut' ) );
			}
		} else {
			wp_die( esc_html__( 'Security check failed.', 'shopglut' ) );
		}
	}


	public static function get_instance() {
		static $instance;

		if ( is_null( $instance ) ) {
			$instance = new self();
		}
		return $instance;
	}
}