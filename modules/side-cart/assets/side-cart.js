/**
 * ShopGlut Mini Cart JavaScript
 * @package ShopGlut
 */

(function($) {
    'use strict';

    const ShopGlutMiniCart = {
        undoTimeout: null,
        mouseOverTimer: null,

        init: function() {
            this.bindEvents();
            this.initAutoClose();
            this.initAutoOpen();
            this.initMouseOverTrigger();
            this.initCustomTrigger();
            this.initToastContainer();
            this.checkFloatingCartVisibility();
        },

        bindEvents: function() {
            // Open cart on floating icon click
            $(document).on('click', '.shopglut-floating-cart', this.openCart.bind(this));

            // Open cart on menu icon click
            $(document).on('click', '.shopglut-menu-cart', this.openCart.bind(this));

            // Close cart
            $(document).on('click', '.shopglut-cart-close', this.closeCart.bind(this));
            $(document).on('click', '.shopglut-mini-cart-overlay', this.closeCart.bind(this));

            // Quantity controls
            $(document).on('click', '.shopglut-quantity-btn.plus', this.increaseQuantity.bind(this));
            $(document).on('click', '.shopglut-quantity-btn.minus', this.decreaseQuantity.bind(this));
            $(document).on('change', '.shopglut-quantity-input', this.updateQuantity.bind(this));

            // Remove item
            $(document).on('click', '.shopglut-cart-item-remove', this.removeItem.bind(this));

            // Undo removal
            $(document).on('click', '.shopglut-undo-button', this.undoRemove.bind(this));

            // Empty cart
            $(document).on('click', '.shopglut-empty-cart-button', this.emptyCart.bind(this));

            // Coupon functionality
            $(document).on('click', '.shopglut-apply-coupon-btn', this.applyCoupon.bind(this));
            $(document).on('click', '.shopglut-remove-coupon', this.removeCoupon.bind(this));
            $(document).on('click', '.shopglut-coupon-toggle', this.toggleCouponSection.bind(this));
            $(document).on('keypress', '.shopglut-coupon-input', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    ShopGlutMiniCart.applyCoupon(e);
                }
            });

            // Order summary toggle
            $(document).on('click', '.shopglut-order-summary-toggle', this.toggleOrderSummary.bind(this));

            // Cart share
            $(document).on('click', '.shopglut-cart-share-button', this.toggleShareForm.bind(this));
            $(document).on('click', '.shopglut-cart-share-send', this.sendCartEmail.bind(this));

            // Continue shopping
            $(document).on('click', '.shopglut-continue-shopping', this.closeCart.bind(this));

            // Listen for WooCommerce add to cart events
            $(document.body).on('added_to_cart', this.onProductAdded.bind(this));

            // Block themes: WooCommerce blocks (Product Collection, Add to Cart button) fire a DOM event instead of the jQuery one.
            document.body.addEventListener('wc-blocks_added_to_cart', () => this.onProductAdded());
            document.body.addEventListener('wc-blocks_removed_from_cart', () => this.refreshCart());
        },

        /**
         * Initialize mouse over trigger
         */
        initMouseOverTrigger: function() {
            if (!shopglut_side_cart.trigger_mouse_over) {
                return;
            }

            const self = this;
            const delay = shopglut_side_cart.mouse_over_delay || 300;

            // Mouse over on floating cart
            $(document).on('mouseenter', '.shopglut-floating-cart', function() {
                self.mouseOverTimer = setTimeout(function() {
                    self.openCart();
                }, delay);
            });

            $(document).on('mouseleave', '.shopglut-floating-cart', function() {
                if (self.mouseOverTimer) {
                    clearTimeout(self.mouseOverTimer);
                }
            });

            // Mouse over on menu cart
            $(document).on('mouseenter', '.shopglut-menu-cart', function() {
                self.mouseOverTimer = setTimeout(function() {
                    self.openCart();
                }, delay);
            });

            $(document).on('mouseleave', '.shopglut-menu-cart', function() {
                if (self.mouseOverTimer) {
                    clearTimeout(self.mouseOverTimer);
                }
            });

            // Don't close on mouse leave if it was opened by hover
            $(document).on('mouseleave', '.shopglut-mini-cart-sidebar', function() {
                // Optional: close when mouse leaves sidebar
            });

            $(document).on('mouseenter', '.shopglut-mini-cart-sidebar', function() {
                if (self.mouseOverTimer) {
                    clearTimeout(self.mouseOverTimer);
                }
            });
        },

        /**
         * Initialize custom trigger class
         */
        initCustomTrigger: function() {
            if (!shopglut_side_cart.custom_trigger_class) {
                return;
            }

            const triggerClass = shopglut_side_cart.custom_trigger_class.trim();
            if (!triggerClass) {
                return;
            }

            const self = this;
            $(document).on('click', '.' + triggerClass, function(e) {
                e.preventDefault();
                self.openCart();
            });
        },

        /**
         * Initialize auto open on first visit if cart has items
         */
        initAutoOpen: function() {
            if (!shopglut_side_cart.auto_open_toggle) {
                return;
            }

            // Check if we've already shown auto open
            if (sessionStorage.getItem('shopglut_sc_auto_opened')) {
                return;
            }

            // Get cart count from the DOM
            const cartCount = parseInt($('.shopglut-cart-count').text()) || 0;

            if (cartCount > 0) {
                // Mark as opened
                sessionStorage.setItem('shopglut_sc_auto_opened', 'true');

                // Open cart after a short delay
                setTimeout(function() {
                    ShopGlutMiniCart.openCart();
                }, 500);
            }
        },

        /**
         * Check and update floating cart visibility based on cart contents
         */
        checkFloatingCartVisibility: function() {
            if (!shopglut_side_cart.hide_toggle_empty) {
                return;
            }

            const cartCount = parseInt($('.shopglut-cart-count').text()) || 0;
            const $floatingCart = $('.shopglut-floating-cart');

            if (cartCount === 0) {
                $floatingCart.addClass('shopglut-hidden');
            } else {
                $floatingCart.removeClass('shopglut-hidden');
            }
        },

        openCart: function(e) {
            if (e) {
                e.preventDefault();
            }

            $('.shopglut-mini-cart-overlay').addClass('active');
            $('.shopglut-mini-cart-sidebar').addClass('active');
            $('body').css('overflow', 'hidden');

            // Reset auto-close timer
            this.initAutoClose();
        },

        closeCart: function(e) {
            if (e) {
                e.preventDefault();
            }

            $('.shopglut-mini-cart-overlay').removeClass('active');
            $('.shopglut-mini-cart-sidebar').removeClass('active');
            $('body').css('overflow', '');

            // Clear auto-close timer
            this.clearAutoClose();
        },

        increaseQuantity: function(e) {
            e.preventDefault();
            const $input = $(e.currentTarget).siblings('.shopglut-quantity-input');
            const currentVal = parseInt($input.val()) || 1;
            $input.val(currentVal + 1).trigger('change');
        },

        decreaseQuantity: function(e) {
            e.preventDefault();
            const $input = $(e.currentTarget).siblings('.shopglut-quantity-input');
            const currentVal = parseInt($input.val()) || 1;
            if (currentVal > 1) {
                $input.val(currentVal - 1).trigger('change');
            }
        },

        updateQuantity: function(e) {
            const $input = $(e.currentTarget);
            const cartKey = $input.data('cart-key');
            const quantity = parseInt($input.val()) || 1;

            if (quantity < 1) {
                $input.val(1);
                return;
            }

            this.updateCartItem(cartKey, quantity);
        },

        updateCartItem: function(cartKey, quantity) {
            const $cartItem = $('[data-cart-key="' + cartKey + '"]').closest('.shopglut-cart-item');

            $cartItem.addClass('shopglut-cart-loading');

            $.ajax({
                url: shopglut_side_cart.ajax_url,
                type: 'POST',
                data: {
                    action: 'shopglut_sc_minicart_update_quantity',
                    cart_key: cartKey,
                    quantity: quantity,
                    nonce: shopglut_side_cart.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.refreshCart();
                    } else {
                        // Check if this is a max quantity error
                        if (response.data && response.data.max_quantity_message) {
                            this.showNotification(response.data.message || shopglut_side_cart.i18n.max_quantity, 'error');
                            // Reset quantity to previous value
                            const $input = $('[data-cart-key="' + cartKey + '"]');
                            const previousQty = parseInt($input.attr('data-previous-qty')) || 1;
                            $input.val(previousQty);
                        } else {
                            this.showNotification(response.data.message || 'Failed to update cart', 'error');
                        }
                        $cartItem.removeClass('shopglut-cart-loading');
                    }
                },
                error: () => {
                    this.showNotification('Failed to update cart', 'error');
                    $cartItem.removeClass('shopglut-cart-loading');
                }
            });
        },

        removeItem: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const cartKey = $button.data('cart-key');
            const $cartItem = $button.closest('.shopglut-cart-item');

            if (!confirm(shopglut_side_cart.i18n.remove_confirm || 'Remove this item from cart?')) {
                return;
            }

            $cartItem.addClass('shopglut-cart-loading');

            // First, get a fresh nonce and session token
            $.ajax({
                url: shopglut_side_cart.ajax_url,
                type: 'POST',
                data: {
                    action: 'shopglut_sc_get_nonce',
                    cart_key: cartKey
                },
                success: (response) => {
                    if (response.success && response.data.nonce) {
                        shopglut_side_cart.nonce = response.data.nonce;
                        // Use fresh nonce and session token for the actual delete request
                        this.removeItemWithNonce(cartKey, response.data.nonce, response.data.session_token, $cartItem);
                    } else {
                        this.showNotification('Failed to get security token', 'error');
                        $cartItem.removeClass('shopglut-cart-loading');
                    }
                },
                error: () => {
                    this.showNotification('Failed to get security token', 'error');
                    $cartItem.removeClass('shopglut-cart-loading');
                }
            });
        },

        removeItemWithNonce: function(cartKey, nonce, sessionToken, $cartItem) {
            const ajaxData = {
                action: 'shopglut_sc_minicart_remove_item',
                cart_key: cartKey,
                nonce: nonce
            };

            // Add session token if available
            if (sessionToken) {
                ajaxData.session_token = sessionToken;
            }

            $.ajax({
                url: shopglut_side_cart.ajax_url,
                type: 'POST',
                data: ajaxData,
                success: (response) => {
                    if (response.success) {
                        // Check if undo is available
                        if (response.data.can_undo && response.data.undo_key && shopglut_side_cart.enable_undo_removal) {
                            this.showUndoNotification(response.data.undo_key);
                        } else {
                            this.showNotification(shopglut_side_cart.i18n.item_removed || 'Item removed from cart', 'success');
                        }
                        this.refreshCart();
                    } else {
                        this.showNotification(response.data.message || 'Failed to remove item', 'error');
                        $cartItem.removeClass('shopglut-cart-loading');
                    }
                },
                error: () => {
                    this.showNotification('Failed to remove item', 'error');
                    $cartItem.removeClass('shopglut-cart-loading');
                }
            });
        },

        showUndoNotification: function(undoKey) {
            // Clear any existing undo timeout
            if (this.undoTimeout) {
                clearTimeout(this.undoTimeout);
            }

            const undoText = shopglut_side_cart.i18n.undo || 'Undo';
            const itemRemovedText = shopglut_side_cart.i18n.item_removed || 'Item removed from cart';

            const $notification = $(
                '<div class="shopglut-cart-notification undo" data-undo-key="' + undoKey + '">' +
                    '<span>' + itemRemovedText + '</span> ' +
                    '<button class="shopglut-undo-button">' + undoText + '</button>' +
                '</div>'
            );

            $('body').append($notification);

            // Set timeout to hide undo option
            const timeout = shopglut_side_cart.undo_timeout || 10000;
            this.undoTimeout = setTimeout(() => {
                $notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, timeout);
        },

        undoRemove: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const $notification = $button.closest('.shopglut-cart-notification');
            const undoKey = $notification.data('undo-key');

            if (!undoKey) {
                return;
            }

            // Clear the timeout
            if (this.undoTimeout) {
                clearTimeout(this.undoTimeout);
                this.undoTimeout = null;
            }

            $.ajax({
                url: shopglut_side_cart.ajax_url,
                type: 'POST',
                data: {
                    action: 'shopglut_sc_undo_remove_item',
                    undo_key: undoKey,
                    nonce: shopglut_side_cart.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.refreshCart();
                        this.showNotification('Item restored to cart', 'success');
                    } else {
                        this.showNotification(response.data.message || 'Failed to restore item', 'error');
                    }
                    $notification.remove();
                },
                error: () => {
                    this.showNotification('Failed to restore item', 'error');
                    $notification.remove();
                }
            });
        },

        emptyCart: function(e) {
            e.preventDefault();

            if (!confirm(shopglut_side_cart.i18n.empty_cart_confirm || 'Are you sure you want to empty your cart?')) {
                return;
            }

            const $button = $(e.currentTarget);
            $button.prop('disabled', true).text('Emptying...');

            $.ajax({
                url: shopglut_side_cart.ajax_url,
                type: 'POST',
                data: {
                    action: 'shopglut_sc_empty_cart',
                    nonce: shopglut_side_cart.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.refreshCart();
                        this.showNotification(shopglut_side_cart.i18n.cart_emptied || 'Cart has been emptied', 'success');
                    } else {
                        this.showNotification(response.data.message || 'Failed to empty cart', 'error');
                    }
                    $button.prop('disabled', false).text('Empty Cart');
                },
                error: () => {
                    this.showNotification('Failed to empty cart', 'error');
                    $button.prop('disabled', false).text('Empty Cart');
                }
            });
        },

        /**
         * Apply coupon
         */
        applyCoupon: function(e) {
            e.preventDefault();

            const $input = $(e.currentTarget).closest('.shopglut-coupon-form').find('.shopglut-coupon-input');
            const couponCode = $input.val().trim();

            if (!couponCode) {
                this.showCouponMessage(shopglut_side_cart.i18n.enter_coupon_code || 'Please enter a coupon code.', 'error');
                return;
            }

            const $button = $(e.currentTarget).hasClass('shopglut-apply-coupon-btn')
                ? $(e.currentTarget)
                : $(e.currentTarget).closest('.shopglut-coupon-form').find('.shopglut-apply-coupon-btn');

            $button.prop('disabled', true).text('Applying...');

            $.ajax({
                url: shopglut_side_cart.ajax_url,
                type: 'POST',
                data: {
                    action: 'shopglut_sc_apply_coupon',
                    coupon_code: couponCode,
                    nonce: shopglut_side_cart.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.refreshCart();
                        this.showNotification(response.data.message || shopglut_side_cart.i18n.coupon_applied, 'success');
                        $input.val('');
                    } else {
                        this.showCouponMessage(response.data.message || shopglut_side_cart.i18n.coupon_error, 'error');
                    }
                    $button.prop('disabled', false).text(shopglut_side_cart.i18n.apply_coupon || 'Apply');
                },
                error: () => {
                    this.showCouponMessage(shopglut_side_cart.i18n.coupon_error || 'Failed to apply coupon', 'error');
                    $button.prop('disabled', false).text(shopglut_side_cart.i18n.apply_coupon || 'Apply');
                }
            });
        },

        /**
         * Remove coupon
         */
        removeCoupon: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $couponElement = $(e.currentTarget).closest('.shopglut-applied-coupon');
            const couponCode = $couponElement.data('coupon-code');

            if (!couponCode) {
                return;
            }

            const $button = $(e.currentTarget);
            $button.prop('disabled', true);

            $.ajax({
                url: shopglut_side_cart.ajax_url,
                type: 'POST',
                data: {
                    action: 'shopglut_sc_remove_coupon',
                    coupon_code: couponCode,
                    nonce: shopglut_side_cart.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.refreshCart();
                        this.showNotification(response.data.message || shopglut_side_cart.i18n.coupon_removed, 'success');
                    } else {
                        this.showCouponMessage(response.data.message || 'Failed to remove coupon', 'error');
                    }
                },
                error: () => {
                    this.showCouponMessage('Failed to remove coupon', 'error');
                    $button.prop('disabled', false);
                }
            });
        },

        /**
         * Toggle coupon section (collapsible)
         */
        toggleCouponSection: function(e) {
            e.preventDefault();
            const $section = $(e.currentTarget).closest('.shopglut-coupon-section');
            const $content = $section.find('.shopglut-coupon-content');
            const $icon = $(e.currentTarget).find('.shopglut-coupon-toggle-icon');

            $section.toggleClass('collapsed');
            $content.slideToggle(300);
            $icon.toggleClass('is-open');
        },

        /**
         * Show coupon message in the coupon section
         */
        showCouponMessage: function(message, type) {
            const $messageEl = $('.shopglut-coupon-message');
            if ($messageEl.length) {
                $messageEl.removeClass('success error').addClass(type).text(message).fadeIn();

                // Auto hide after 3 seconds
                setTimeout(() => {
                    $messageEl.fadeOut();
                }, 3000);
            } else {
                // Fallback to regular notification
                this.showNotification(message, type);
            }
        },

        /**
         * Toggle order summary section
         */
        toggleOrderSummary: function(e) {
            e.preventDefault();
            const $summary = $(e.currentTarget).closest('.shopglut-order-summary');
            const $content = $summary.find('.shopglut-order-summary-content');
            const $icon = $(e.currentTarget).find('.shopglut-ic');

            $content.slideToggle(300);
            $icon.toggleClass('is-open');
            $summary.toggleClass('collapsed');
        },

        refreshCart: function() {
            $.ajax({
                url: shopglut_side_cart.ajax_url,
                type: 'POST',
                data: {
                    action: 'shopglut_sc_get_mini_cart',
                    nonce: shopglut_side_cart.nonce
                },
                success: (response) => {
                    if (response.success) {
                        $('.shopglut-cart-items').html(response.data.items_html);
                        $('.shopglut-cart-footer').html(response.data.footer_html).toggle((response.data.cart_count || 0) > 0);

                        // Update cart count badges
                        const count = response.data.cart_count || 0;
                        $('.shopglut-floating-cart-badge, .shopglut-menu-cart-count').text(count);

                        if (count === 0) {
                            $('.shopglut-floating-cart-badge, .shopglut-menu-cart-count').hide();
                        } else {
                            $('.shopglut-floating-cart-badge, .shopglut-menu-cart-count').show();
                        }

                        // Check floating cart visibility
                        this.checkFloatingCartVisibility();
                    }
                }
            });
        },

        onProductAdded: function(e, fragments, cart_hash, $button) {
            // Refresh cart content
            this.refreshCart();

            // Auto-open cart if enabled
            if (shopglut_side_cart.auto_open === '1') {
                setTimeout(() => {
                    this.openCart();
                }, 300);
            }

            // Show notification
            this.showNotification(shopglut_side_cart.i18n.added_to_cart || 'Product added to cart', 'success');
        },

        toggleShareForm: function(e) {
            e.preventDefault();
            // This method is no longer needed - using inline JavaScript
            console.log('Legacy toggleShareForm called - using inline JavaScript instead');
        },

        sendCartEmail: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const $form = $button.closest('.shopglut-cart-share-form');

            const senderName = $form.find('input[name="sender_name"]').val();
            const senderEmail = $form.find('input[name="sender_email"]').val();
            const recipientEmail = $form.find('input[name="recipient_email"]').val();
            const message = $form.find('textarea[name="message"]').val();

            if (!senderName || !senderEmail || !recipientEmail) {
                this.showNotification(shopglut_side_cart.i18n.fill_required || 'Please fill all required fields', 'error');
                return;
            }

            $button.prop('disabled', true).text(shopglut_side_cart.i18n.sending || 'Sending...');

            $.ajax({
                url: shopglut_side_cart.ajax_url,
                type: 'POST',
                data: {
                    action: 'shopglut_sc_send_cart_email',
                    sender_name: senderName,
                    sender_email: senderEmail,
                    recipient_email: recipientEmail,
                    message: message,
                    nonce: shopglut_side_cart.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotification(shopglut_side_cart.i18n.cart_sent || 'Cart shared successfully!', 'success');
                        $form.removeClass('active');
                        $form.find('input, textarea').val('');
                    } else {
                        this.showNotification(response.data || 'Failed to send email', 'error');
                    }
                    $button.prop('disabled', false).text(shopglut_side_cart.i18n.send_email || 'Send Email');
                },
                error: () => {
                    this.showNotification('Failed to send email', 'error');
                    $button.prop('disabled', false).text(shopglut_side_cart.i18n.send_email || 'Send Email');
                }
            });
        },

        initAutoClose: function() {
            this.clearAutoClose();

            const autoCloseTime = parseInt(shopglut_side_cart.auto_close_time || 0);

            if (autoCloseTime > 0) {
                this.autoCloseTimer = setTimeout(() => {
                    this.closeCart();
                }, autoCloseTime * 1000);
            }
        },

        clearAutoClose: function() {
            if (this.autoCloseTimer) {
                clearTimeout(this.autoCloseTimer);
                this.autoCloseTimer = null;
            }
        },

        showNotification: function(message, type) {
            // Check if toast notifications are enabled
            const enableToasts = shopglut_side_cart.enable_toast_notifications !== undefined
                ? shopglut_side_cart.enable_toast_notifications
                : true;

            if (!enableToasts) {
                // Fallback to old notification style if toasts are disabled
                const $notification = $('<div class="shopglut-cart-notification ' + type + '">' + message + '</div>');
                $('body').append($notification);
                setTimeout(() => {
                    $notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
                return;
            }

            // Use toast notification system
            this.showToast(message, type);
        },

        showToast: function(message, type) {
            type = type || 'info';
            const position = shopglut_side_cart.notification_position || 'top-right';
            const duration = 4000; // 4 seconds

            // Get or create toast container
            let $container = $('.shopglut-toast-container.position-' + position);
            if ($container.length === 0) {
                $container = $('<div class="shopglut-toast-container position-' + position + '"></div>');
                $('body').append($container);
            }

            // Create toast element
            const $toast = $(`
                <div class="shopglut-toast ${type}">
                    <div class="shopglut-toast-icon"></div>
                    <div class="shopglut-toast-content">${message}</div>
                    <button class="shopglut-toast-close">&times;</button>
                    <div class="shopglut-toast-progress" style="animation-duration: ${duration}ms;"></div>
                </div>
            `);

            // Add close button functionality
            $toast.find('.shopglut-toast-close').on('click', function() {
                $toast.addClass('removing');
                setTimeout(function() {
                    $toast.remove();
                }, 300);
            });

            // Add toast to container
            $container.append($toast);

            // Auto-remove after duration
            setTimeout(function() {
                $toast.addClass('removing');
                setTimeout(function() {
                    $toast.remove();
                }, 300);
            }, duration);
        },

        initToastContainer: function() {
            // Create initial toast container based on settings
            const position = shopglut_side_cart.notification_position || 'top-right';
            if ($('.shopglut-toast-container').length === 0) {
                $('<div class="shopglut-toast-container position-' + position + '"></div>').appendTo('body');
            }
        },
    };

    // Initialize on document ready
    $(document).ready(function() {
        console.log('ShopGlut Mini Cart initializing...');
        ShopGlutMiniCart.init();
        console.log('ShopGlut Mini Cart initialized.');
    });

    // Make it globally available
    window.ShopGlutMiniCart = ShopGlutMiniCart;

})(jQuery);
