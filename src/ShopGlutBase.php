<?php
namespace Shopglut;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Shopglut\layouts\AllLayouts;
// use Shopglut\layouts\cartPage\chooseTemplates as CartPageTemplates;
// use Shopglut\layouts\cartPage\dataManage as CartPageDataManage;

// use Shopglut\layouts\checkoutPage\CheckoutFieldsManager;
// use Shopglut\layouts\checkoutPage\CheckoutFieldsDisplay;
// use Shopglut\layouts\checkoutPage\BlockCheckoutFields;
// use Shopglut\layouts\checkoutPage\CheckoutFieldsInit;

// use Shopglut\layouts\orderCompletePage\chooseTemplates as orderCompleteTemplates;
// use Shopglut\layouts\orderCompletePage\dataManage as orderCompletedataManage;


// use Shopglut\layouts\accountPage\AccountPageChooseTemplates;
// use Shopglut\layouts\accountPage\AccountPageDataManage;


// use Shopglut\layouts\shopLayout\chooseTemplates as ShopLayoutTemplates;
// use Shopglut\layouts\shopLayout\dataManage as ShopLayoutDataManage;


// use Shopglut\enhancements\Filters\dataManage as FiltersDataManage;

// use Shopglut\enhancements\Swatches\Swatches;
// use Shopglut\enhancements\Swatches\dataManage as SwatchesdataManage;

// use Shopglut\enhancements\ProductBadges\BadgechooseTemplates;
// use Shopglut\enhancements\ProductBadges\BadgeDataManage;

// use Shopglut\enhancements\ProductComparison\ProductComparisonDataManage;
// use Shopglut\enhancements\ProductComparison\ComparisonchooseTemplates;


// use Shopglut\enhancements\ProductQuickView\QuickViewchooseTemplates;
// use Shopglut\enhancements\ProductQuickView\QuickViewDataManage;


// use Shopglut\tools\loginRegister\LoginRegister;
// use Shopglut\tools\productCustomField\ProductCustomFieldDataManage;
// use Shopglut\tools\productCustomField\ProductCustomFieldHandler;
// use Shopglut\tools\miniCart\MiniCart;
// use Shopglut\tools\WooThemes\WooThemes;

// use Shopglut\showcases\Sliders\SliderchooseTemplates;
// use Shopglut\showcases\Sliders\SliderDataManage;
// 
// use Shopglut\showcases\ShopBanner\ShopBannerchooseTemplates;
// use Shopglut\showcases\ShopBanner\ShopBannerDataManage;
// 
// use Shopglut\showcases\Gallery\GallerychooseTemplates;
// 
// use Shopglut\showcases\Tabs\TabchooseTemplates;
// use Shopglut\showcases\Tabs\TabDataManage;
// use Shopglut\showcases\Tabs\TabEntity;
// 
// use Shopglut\showcases\ShopBanner\ShopBannerFrontend;
// 
// use Shopglut\showcases\Accordions\AccordionchooseTemplates;
// use Shopglut\showcases\Accordions\AccordionDataManage;

use Shopglut\enhancements\AllEnhancements;

// use Shopglut\enhancements\ProductSwatches\chooseTemplates as SwatchesTemplates;
// use Shopglut\enhancements\ProductSwatches\dataManage as ProductSwatchesDataManage;
// use Shopglut\enhancements\ProductSwatches\AttributeSwatchesManager;


class ShopGlutBase {

	// Declare properties to fix PHP 8.2+ deprecation warnings
	public $menu_slug;

	public function __construct() {

		// Initialize core components
		ShopGlutDatabase::ShopGlut_initialize();
		ShopGlutDatabase::force_create_core_tables(); // Ensure core tables exist even if modules are disabled
		ShopGlutRegisterScripts::get_instance();
		ShopGlutRegisterMenu::get_instance();
		DataManage::get_instance();
		AllLayouts::get_instance();
		AllEnhancements::get_instance();

// 		// Only load embedded singleProduct module if ProductPageGlut is NOT active
// 		if ( ! $this->is_productpageglut_active() ) {
// 			if ( class_exists( 'Shopglut\\layouts\\singleProduct\\dataManage' ) ) {
// 				\Shopglut\layouts\singleProduct\dataManage::get_instance();
// 			}
// 			if ( class_exists( 'Shopglut\\layouts\\singleProduct\\chooseTemplates' ) ) {
// 				\Shopglut\layouts\singleProduct\chooseTemplates::get_instance();
// 			}
// 		}

			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 			// Only load embedded cartPage module if CartPageGlut is NOT active
// 			if ( !$this->is_cartpageglut_active() ) {
// 				CartPageTemplates::get_instance();
// 				CartPageDataManage::get_instance();
// 			}
		
		
		// ShopLayoutTemplates::get_instance();
		// ShopLayoutDataManage::get_instance();

		// CheckoutFieldsManager::get_instance();
		// CheckoutFieldsDisplay::get_instance();
		// CheckoutFieldsInit::get_instance();
		// BlockCheckoutFields::get_instance();

		// Only load embedded Filters module if ShopFilterGlut is NOT active
			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 		if ( ! $this->is_shopfilterglut_active() ) {
// 			FiltersDataManage::get_instance();
// 		}
// 
// 		orderCompletedataManage::get_instance();
// 		orderCompleteTemplates::get_instance();
// 
		// Only load embedded accountPage module if MyAccountGlut is NOT active
			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 		if ( ! $this->is_myaccountglut_active() ) {
// 			if ( class_exists( 'Shopglut\\layouts\\accountPage\\AccountPageDataManage' ) ) {
// 				\Shopglut\layouts\accountPage\AccountPageDataManage::get_instance();
// 			}
// 			if ( class_exists( 'Shopglut\\layouts\\accountPage\\AccountPageChooseTemplates' ) ) {
// 				\Shopglut\layouts\accountPage\AccountPageChooseTemplates::get_instance();
// 			}
// 		}
// 
// 		// Only load embedded ProductBadges module if ProductBadgesGlut is NOT active
// 		if ( ! $this->is_productbadgesglut_active() ) {
// 			BadgechooseTemplates::get_instance();
// 			BadgeDataManage::get_instance();
// 		}
// 
// 			// Only load embedded ProductComparison module if ProductComparisonGlut is NOT active
// 			if ( ! $this->is_productcomparisonglut_active() ) {
// 				ComparisonchooseTemplates::get_instance();
// 				ProductComparisonDataManage::get_instance();
// 			}
// 
// 			// Only load embedded ProductQuickView module if ProductQuickViewGlut is NOT active
// 			if ( ! $this->is_productquickviewglut_active() ) {
// 				QuickViewchooseTemplates::get_instance();
// 				QuickViewDataManage::get_instance();
// 			}
// 






		// SliderchooseTemplates::get_instance();
		// SliderDataManage::get_instance();

		// GalleryChooseTemplates::get_instance();

			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 		// Only load embedded ProductCustomField module if ProductCustomFieldGlut is NOT active
// 		if ( ! $this->is_productcustomfieldglut_active() ) {
// 			ProductCustomFieldDataManage::get_instance();
// 			ProductCustomFieldHandler::get_instance();
// 		}

			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 		// Only load embedded ProductSwatches module if ProductSwatchesGlut is NOT active
// 		if ( ! $this->is_productswatchesglut_active() ) {
// 			SwatchesTemplates::get_instance();
// 			ProductSwatchesDataManage::get_instance();
// 			AttributeSwatchesManager::get_instance();
// 		}

			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 		// Only load embedded LoginRegister module if LoginRegisterGlut is NOT active
// 		if ( ! $this->is_loginregisterglut_active() ) {
// 			LoginRegister::get_instance();
// 		}

			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 		// Only load embedded MiniCart module if MiniCartGlut is NOT active
// 		if ( ! $this->is_minicartglut_active() ) {
// 			MiniCart::get_instance();
// 		}

			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 		// Initialize WooThemes
// 		new WooThemes();
		
		// Initialize ShopBanner module
		// ShopBannerChooseTemplates::get_instance();
		// ShopBannerDataManage::get_instance();
		// ShopBannerFrontend::get_instance();

		// Initialize Accordion module
		// AccordionchooseTemplates::get_instance();
		// AccordionDataManage::get_instance();

		// Initialize Tabs module
		// TabchooseTemplates::get_instance();

		// SliderdataManage::get_instance();
		// TabDataManage::get_instance();
	


		// Add actions
		add_action( 'init', array( $this, 'shopglutInitialFunctions' ), 9 );
		add_filter( 'update_footer', array( $this, 'shopglut_admin_footer_version' ), 999 );

		// Hook the redirection function into admin_init
		add_action( 'admin_init', array( $this, 'shopglut_redirect_after_activation' ) );
	}

	public function shopglut_redirect_after_activation() {
		if ( ! get_option( 'shopglut_plugin_first_activation_redirect' ) ) {
			// Set the option to ensure this runs only once
			update_option( 'shopglut_plugin_first_activation_redirect', true );

			// Redirect to the welcome page after activation
			wp_safe_redirect( admin_url( 'admin.php?page=shopglut-welcome' ) );
			exit;
		}
	}

	public function shopglutInitialFunctions() {
		// Load required files
		require_once SHOPGLUT_PATH . 'src/ModuleManager.php';
		require_once SHOPGLUT_PATH . 'src/library/model/classes/setup.class.php';

// 		// Only load embedded singleProduct settings if ProductPageGlut is NOT active AND file exists
// 		$single_product_settings_file = SHOPGLUT_PATH . 'src/layouts/singleProduct/singleLayout-settings.php';
// 		if ( ! $this->is_productpageglut_active() && file_exists( $single_product_settings_file ) ) {
// 			require_once $single_product_settings_file;
// 		}

			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 			// Only load embedded cartPage template-settings if CartPageGlut is NOT active AND file exists
// 			$cart_template_settings_file = SHOPGLUT_PATH . 'src/layouts/cartPage/template-settings.php';
// 			if ( !$this->is_cartpageglut_active() && file_exists( $cart_template_settings_file ) ) {
// 				require_once $cart_template_settings_file;
// 			}
			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 		require_once SHOPGLUT_PATH . 'src/layouts/orderCompletePage/template-settings.php';

			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 		// Only load embedded accountPage settings if MyAccountGlut is NOT active AND file exists
// 		$account_page_settings_file = SHOPGLUT_PATH . 'src/layouts/accountPage/template-settings.php';
// 		if ( ! $this->is_myaccountglut_active() && file_exists( $account_page_settings_file ) ) {
// 			require_once $account_page_settings_file;
// 		}

			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 		// Only load embedded Filters settings if ShopFilterGlut is NOT active
// 		if ( ! $this->is_shopfilterglut_active() ) {
// 			require_once SHOPGLUT_PATH . 'src/enhancements/Filters/filters-config.php';
// 		}
// 		// Commented out - using new ProductSwatches module instead
// 		// require_once SHOPGLUT_PATH . 'src/enhancements/Swatches/product-swatches-settings.php';
// 		require_once SHOPGLUT_PATH . 'src/enhancements/ProductSwatches/productSwatches-settings.php';

			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 			// Only load embedded ProductBadges settings if ProductBadgesGlut is NOT active
// 			if ( ! $this->is_productbadgesglut_active() ) {
// 				require_once SHOPGLUT_PATH . 'src/enhancements/ProductBadges/product-badges-settings.php';
// 			}
// 			// Only load embedded ProductBadges settings if ProductBadgesGlut is NOT active
// 			if ( ! $this->is_productbadgesglut_active() ) {
// 				require_once SHOPGLUT_PATH . 'src/enhancements/ProductBadges/product-badges-settings.php';
// 			}
		require_once SHOPGLUT_PATH . 'src/shortcodeglut-integration-settings.php';
		// Only load embedded MiniCart settings if MiniCartGlut is NOT active
			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 		if ( ! $this->is_minicartglut_active() ) {
// 			require_once SHOPGLUT_PATH . 'src/tools/miniCart/mini-cart-settings.php';
// 		}
			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 			// Only load embedded ProductComparison settings if ProductComparisonGlut is NOT active
// 			if ( ! $this->is_productcomparisonglut_active() ) {
// 				require_once SHOPGLUT_PATH . 'src/enhancements/ProductComparison/template-settings.php';
// 			}
			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 			// Only load embedded ProductQuickView settings if ProductQuickViewGlut is NOT active
// 			if ( ! $this->is_productquickviewglut_active() ) {
// 				require_once SHOPGLUT_PATH . 'src/enhancements/ProductQuickView/template-settings.php';
// 			}


		//require_once SHOPGLUT_PATH . 'src/BusinessSolutions/PdfInvoices/pdf-invoices-settings.php';
			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 		// Only load embedded shopLayout settings if ShopArchivePageGlut is NOT active AND file exists
// 			$shop_layout_settings_file = SHOPGLUT_PATH . 'src/layouts/shopLayout/shopLayouts-config.php';
// 			if ( ! $this->is_shoparchivepageglut_active() && file_exists( $shop_layout_settings_file ) ) {
// 				require_once $shop_layout_settings_file;
// 			}
			// === COMMENTED OUT - MOVED TO STANDALONE PLUGIN ===
// 	     // Only load embedded ProductCustomField settings if ProductCustomFieldGlut is NOT active
// 	     if ( ! $this->is_productcustomfieldglut_active() ) {
// 	    	 require_once SHOPGLUT_PATH . 'src/tools/productCustomField/product-custom-field-settings.php';
// 	     }
		// require_once SHOPGLUT_PATH . 'src/showcases/Gallery/templates/template1/template1-settings.php';
		// require_once SHOPGLUT_PATH . 'src/showcases/ShopBanner/template-settings.php';
		// require_once SHOPGLUT_PATH . 'src/showcases/Sliders/template-settings.php';
	    // require_once SHOPGLUT_PATH . 'src/showcases/Tabs/template-settings.php';
	    // require_once SHOPGLUT_PATH . 'src/showcases/Accordions/template-settings.php';
	    // Load shortcodeShowcase with universal loader
	    // This loader automatically detects which plugin is loading and sets up compatibility

		// Load business solutions modules
		if ( file_exists( SHOPGLUT_PATH . 'src/business-solutions/index.php' ) ) {
			require_once SHOPGLUT_PATH . 'src/business-solutions/index.php';
		}
	}

	public function shopglut_admin_footer_version() {
		return '<span id="shopglut-footer-version" style="display: none;">ShopGlut ' . SHOPGLUT_VERSION . '</span>';
	}

	/**
	 * Check if ProductPageGlut plugin is active
	 *
	 * @return bool True if ProductPageGlut is active
	 */
	private function is_productpageglut_active() {
		// Check by active plugins list
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

		if ( is_multisite() ) {
			// Get network active plugins
			$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
		}

		// Check for productpage-glut/productpage-glut.php or productpage-glut/productpage-glut.php plugin
		foreach ( $active_plugins as $plugin ) {
			if ( $plugin === 'productpage-glut/productpage-glut.php'
				|| $plugin === 'productpage-glut/productpage-glut.php' ) {
				return true;
			}
		}

		// Also check if the main class exists (including productpageglut variant)
		return class_exists( 'ProductPageGlut\\ProductPageGlutBase' )
			|| class_exists( 'Productpageglut\\ProductpageglutBase' );
	}

	/**
	 * Check if CartPageGlut plugin is active
	 *
	 * @return bool True if CartPageGlut is active
	 */
	private function is_cartpageglut_active() {
		// Check by active plugins list
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

		if ( is_multisite() ) {
			// Get network active plugins
			$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
		}

		// Check for cartpage-glut/cartpage-glut.php plugin
		foreach ( $active_plugins as $plugin ) {
			if ( $plugin === 'cartpage-glut/cartpage-glut.php' ) {
				return true;
			}
		}

		// Also check if the main class exists
		return class_exists( 'Cartpageglut\\CartpageglutBase' );
	}

	/**
	 * Check if ProductDetailsGlut plugin is active (legacy support)
	 *
	 * @return bool True if ProductDetailsGlut is active
	 * @deprecated Use is_productpageglut_active() instead
	 */
	private function is_productdetailsglut_active() {
		// Check by active plugins list
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

		if ( is_multisite() ) {
			// Get network active plugins
			$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
		}

		// Check for both old (product-details-glut) and new (productpage-glut, productpage-glut) plugins
		foreach ( $active_plugins as $plugin ) {
			if ( $plugin === 'product-details-glut/product-details-glut.php'
				|| $plugin === 'productpage-glut/productpage-glut.php'
				|| $plugin === 'productpage-glut/productpage-glut.php' ) {
				return true;
			}
		}

		// Also check if the main class exists (check both old and new class names)
		return class_exists( 'ProductDetailsGlut\\ProductDetailsGlutBase' )
			|| class_exists( 'ProductPageGlut\\ProductPageGlutBase' )
			|| class_exists( 'Productpageglut\\ProductpageglutBase' );
	}

	/**
	 * Check if ShopArchivePageGlut plugin is active
	 *
	 * @return bool True if ShopArchivePageGlut is active
	 */
	private function is_shoparchivepageglut_active() {
		// Check by active plugins list
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

		if ( is_multisite() ) {
			// Get network active plugins
			$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
		}

		// Check for shop-archivepage-glut/shop-archivepage-glut.php plugin
		foreach ( $active_plugins as $plugin ) {
			if ( $plugin === 'shop-archivepage-glut/shop-archivepage-glut.php' ) {
				return true;
			}
		}

		// Also check if the main class exists
		return class_exists( 'Shoparchivepageglut\\ShoparchivepageglutBase' );
	}

	/**
	 * Check if MyAccountGlut plugin is active
	 *
	 * @return bool True if MyAccountGlut is active
	 */
	private function is_myaccountglut_active() {
		// Check by active plugins list
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

		if ( is_multisite() ) {
			// Get network active plugins
			$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
		}

		// Check for myaccount-glut/myaccount-glut.php plugin
		foreach ( $active_plugins as $plugin ) {
			if ( $plugin === 'myaccount-glut/myaccount-glut.php' ) {
				return true;
			}
		}

		// Also check if the main class exists
		return class_exists( 'Myaccountglut\\MyaccountglutBase' );
	}

	/**
	 * Check if ShopFilterGlut plugin is active
	 *
	 * @return bool True if ShopFilterGlut is active
	 */
	private function is_shopfilterglut_active() {
		// Check by active plugins list
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

		if ( is_multisite() ) {
			// Get network active plugins
			$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
		}

		// Check for shopfilter-glut/shopfilter-glut.php plugin
		foreach ( $active_plugins as $plugin ) {
			if ( $plugin === 'shopfilter-glut/shopfilter-glut.php' ) {
				return true;
			}
		}

		// Also check if the main class exists
		return class_exists( 'Shopfilterglut\\ShopfilterglutBase' );
	}

	/**
		 * Check if ProductBadgesGlut plugin is active
		 *
		 * @return bool True if ProductBadgesGlut is active
		 */
		private function is_productbadgesglut_active() {
			// Check by active plugins list
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

			if ( is_multisite() ) {
				// Get network active plugins
				$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
				$active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
			}

			// Check for productbadges-glut/productbadges-glut.php plugin
			foreach ( $active_plugins as $plugin ) {
				if ( $plugin === 'productbadges-glut/productbadges-glut.php' ) {
					return true;
				}
			}

			// Also check if the main class exists
			return class_exists( 'Productbadgesglut\\ProductbadgesglutBase' );
		}

		/**
		 * Check if ProductSwatchesGlut plugin is active
		 *
		 * @return bool True if ProductSwatchesGlut is active
		 */
		private function is_productswatchesglut_active() {
			// Check by active plugins list
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

			if ( is_multisite() ) {
				// Get network active plugins
				$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
				$active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
			}

			// Check for productswatches-glut/productswatches-glut.php plugin
			foreach ( $active_plugins as $plugin ) {
				if ( $plugin === 'productswatches-glut/productswatches-glut.php' ) {
					return true;
				}
			}

			// Also check if the main class exists
			return class_exists( 'Productswatchesglut\\ProductswatchesglutBase' );
		}
		/**
		 * Check if ProductComparisonGlut plugin is active
		 *
		 * @return bool True if ProductComparisonGlut is active
		 */
		private function is_productcomparisonglut_active() {
			// Check by active plugins list
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

			if ( is_multisite() ) {
				// Get network active plugins
				$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
				$active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
			}

			// Check for productcomparison-glut/productcomparison-glut.php plugin
			foreach ( $active_plugins as $plugin ) {
				if ( $plugin === 'productcomparison-glut/productcomparison-glut.php' ) {
					return true;
				}
			}

			// Also check if the main class exists
			return class_exists( 'Comparisonglut\\ComparisonglutBase' );
		}

		/**
		 * Check if ProductQuickViewGlut plugin is active
		 *
		 * @return bool True if ProductQuickViewGlut is active
		 */
		private function is_productquickviewglut_active() {
			// Check by active plugins list
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

			if ( is_multisite() ) {
				// Get network active plugins
				$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
				$active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
			}

			// Check for productquickview-glut/productquickview-glut.php plugin
			foreach ( $active_plugins as $plugin ) {
				if ( $plugin === 'productquickview-glut/productquickview-glut.php' ) {
					return true;
				}
			}

			// Also check if the main class exists
			return class_exists( 'Productquickviewglut\\ProductquickviewglutBase' );
		}

		/**
		 * Check if ProductCustomFieldGlut plugin is active
		 *
		 * @return bool True if ProductCustomFieldGlut is active
		 */
		private function is_productcustomfieldglut_active() {
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

			if ( is_multisite() ) {
				// Get network active plugins
				$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
				$active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
			}

			// Check for productcustomfield-glut/productcustomfield-glut.php plugin
			foreach ( $active_plugins as $plugin ) {
				if ( $plugin === 'productcustomfield-glut/productcustomfield-glut.php' ) {
					return true;
				}
			}

			// Also check if the main class exists
			return class_exists( 'Productcustomfieldglut\\ProductcustomfieldglutBase' );
		}

		/**
		 * Check if LoginRegisterGlut plugin is active
		 *
		 * @return bool True if LoginRegisterGlut is active
		 */
		private function is_loginregisterglut_active() {
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

			if ( is_multisite() ) {
				// Get network active plugins
				$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
				$active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
			}

			// Check for loginregister-glut/loginregister-glut.php plugin
			foreach ( $active_plugins as $plugin ) {
				if ( $plugin === 'loginregister-glut/loginregister-glut.php' ) {
					return true;
				}
			}

			// Also check if the main class exists
			return class_exists( 'Loginregisterglut\\LoginregisterglutBase' );
		}

		/**
		 * Check if MiniCartGlut plugin is active
		 *
		 * @return bool True if MiniCartGlut is active
		 */
		private function is_minicartglut_active() {
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

			if ( is_multisite() ) {
				// Get network active plugins
				$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
				$active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
			}

			// Check for minicart-glut/minicart-glut.php plugin
			foreach ( $active_plugins as $plugin ) {
				if ( $plugin === 'minicart-glut/minicart-glut.php' ) {
					return true;
				}
			}

			// Also check if the main class exists
			return class_exists( 'Minicartglut\\MinicartglutBase' );
		}

	public static function get_instance() {
		static $instance;

		if ( is_null( $instance ) ) {
			$instance = new self();
		}
		return $instance;
	}
}
