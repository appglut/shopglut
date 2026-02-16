/**
 * Shopglut Cart Template1 Functionality
 * Handles AJAX cart operations for template1 layout
 */

(function($) {
    'use strict';

    class ShopglutCartTemplate1 {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.setupLoadingStates();
        }

        bindEvents() {
            // Quantity controls
            $(document).on('click', '.shopglut-cart.template1 .qty-decrease', this.decreaseQuantity.bind(this));
            $(document).on('click', '.shopglut-cart.template1 .qty-increase', this.increaseQuantity.bind(this));
            $(document).on('change', '.shopglut-cart.template1 .qty-input', this.updateQuantity.bind(this));

            // Remove item
            $(document).on('click', '.shopglut-cart.template1 .remove-btn', this.removeItem.bind(this));

            // Coupon functionality
            $(document).on('submit', '.shopglut-cart.template1 #shopglut-coupon-form', this.applyCoupon.bind(this));
            $(document).on('click', '.shopglut-cart.template1 .remove-coupon', this.removeCoupon.bind(this));

            // Checkout
            $(document).on('click', '.shopglut-cart.template1 #proceed-to-checkout', this.proceedToCheckout.bind(this));

            // Update cart fragments (WooCommerce native)
            $(document.body).on('updated_wc_div', this.onCartUpdated.bind(this));
        }

        setupLoadingStates() {
            this.loadingClass = 'shopglut-loading';
            this.disabledClass = 'shopglut-disabled';
        }

        /**
         * Decrease quantity
         */
        decreaseQuantity(e) {
            e.preventDefault();
            const button = $(e.currentTarget);
            const cartKey = button.data('cart-key');
            const qtyControl = button.closest('.qty-control');
            const qtyInput = qtyControl.find('.qty-input');
            const currentQty = parseInt(qtyInput.val()) || 1;
            const maxQty = parseInt(qtyInput.attr('max')) || 999;

            if (currentQty > 1) {
                // Update input immediately for visual feedback
                qtyInput.val(currentQty - 1);
                this.updateCartQuantity(cartKey, currentQty - 1, qtyInput);
            }
        }

        /**
         * Increase quantity
         */
        increaseQuantity(e) {
            e.preventDefault();
            const button = $(e.currentTarget);
            const cartKey = button.data('cart-key');
            const qtyControl = button.closest('.qty-control');
            const qtyInput = qtyControl.find('.qty-input');
            const currentQty = parseInt(qtyInput.val()) || 1;
            const maxQty = parseInt(qtyInput.attr('max')) || 999;

            if (currentQty < maxQty) {
                // Update input immediately for visual feedback
                qtyInput.val(currentQty + 1);
                this.updateCartQuantity(cartKey, currentQty + 1, qtyInput);
            }
        }

        /**
         * Update quantity from input
         */
        updateQuantity(e) {
            const qtyInput = $(e.currentTarget);
            const cartKey = qtyInput.data('cart-key');
            const newQty = parseInt(qtyInput.val());

            const minQty = parseInt(qtyInput.attr('min')) || 1;
            const maxQty = parseInt(qtyInput.attr('max')) || 999;

            // Validate quantity
            if (newQty < minQty) {
                qtyInput.val(minQty);
                return;
            }
            if (newQty > maxQty) {
                qtyInput.val(maxQty);
                return;
            }

            // Store original value for rollback
            if (!qtyInput.data('original-value')) {
                qtyInput.data('original-value', newQty);
            }

            this.updateCartQuantity(cartKey, newQty, qtyInput);
        }

        /**
         * Update cart quantity via AJAX
         */
        updateCartQuantity(cartKey, quantity, qtyInput) {
            const cartItem = qtyInput.closest('.cart-item');
            const originalQty = qtyInput.data('original-value') || qtyInput.val();

            this.setLoading(cartItem, true);

            $.ajax({
                url: shopglut_cart_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'shopglut_update_cart_quantity',
                    cart_item_key: cartKey,
                    quantity: quantity,
                    nonce: shopglut_cart_ajax.nonce
                },
                timeout: 30000,
                success: (response) => {
                    if (response.success) {
                        // Update cart fragments if provided
                        if (response.data && response.data.fragments) {
                            this.updateCartFragments(response.data.fragments);
                        }

                        // Show success message
                        const message = response.data && response.data.message
                            ? response.data.message
                            : shopglut_cart_ajax.i18n.updated || 'Cart updated';
                        this.showMessage(message, 'success');

                        // Update original value
                        qtyInput.data('original-value', quantity);
                    } else {
                        // Rollback to original quantity
                        qtyInput.val(originalQty);
                        const errorMessage = response.data && response.data.message
                            ? response.data.message
                            : shopglut_cart_ajax.i18n.error || 'Error updating cart';
                        this.showMessage(errorMessage, 'error');
                    }
                },
                error: () => {
                    // Rollback to original quantity
                    qtyInput.val(originalQty);
                    this.showMessage(shopglut_cart_ajax.i18n.error || 'Error updating cart', 'error');
                },
                complete: () => {
                    this.setLoading(cartItem, false);
                }
            });
        }

        /**
         * Remove item from cart
         */
        removeItem(e) {
            e.preventDefault();
            const button = $(e.currentTarget);
            const cartKey = button.data('cart-key');
            const cartItem = button.closest('.cart-item');

            if (!confirm(shopglut_cart_ajax.i18n.confirm_remove || 'Are you sure you want to remove this item?')) {
                return;
            }

            this.setLoading(cartItem, true);

            $.ajax({
                url: shopglut_cart_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'shopglut_remove_cart_item',
                    cart_item_key: cartKey,
                    nonce: shopglut_cart_ajax.nonce
                },
                timeout: 30000,
                success: (response) => {
                    if (response.success) {
                        // Remove only this specific row
                        cartItem.fadeOut(300, function() {
                            $(this).remove();
                        });

                        // Show success message
                        const message = (response.data && response.data.message) ?
                            response.data.message : shopglut_cart_ajax.i18n.removing || 'Item removed';

                        this.showMessage(message, 'success');

                        // Check if cart is empty and reload page
                        if ($('.cart-item').length === 0) {
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                    } else {
                        const errorMessage = (response.data && response.data.message) ?
                            response.data.message : shopglut_cart_ajax.i18n.error || 'Error removing item';
                        this.showMessage(errorMessage, 'error');
                    }
                },
                error: () => {
                    this.showMessage(shopglut_cart_ajax.i18n.error || 'Error removing item', 'error');
                },
                complete: () => {
                    this.setLoading(cartItem, false);
                }
            });
        }

        /**
         * Apply coupon
         */
        applyCoupon(e) {
            e.preventDefault();
            const form = $(e.currentTarget);
            const couponCode = form.find('#couponCode').val().trim();
            const messageContainer = form.find('#couponMessage');

            if (!couponCode) {
                this.showCouponMessage(messageContainer, 'Please enter a coupon code.', 'error');
                return;
            }

            const submitButton = form.find('.apply-btn');
            const originalText = submitButton.text();

            submitButton.text(shopglut_cart_ajax.i18n.updating || 'Updating...').prop('disabled', true);

            $.ajax({
                url: shopglut_cart_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'shopglut_apply_coupon',
                    coupon_code: couponCode,
                    nonce: shopglut_cart_ajax.nonce
                },
                timeout: 30000,
                success: (response) => {
                    if (response.success) {
                        // Update cart fragments if provided
                        if (response.data && response.data.fragments) {
                            this.updateCartFragments(response.data.fragments);
                        }

                        const message = response.data && response.data.message
                            ? response.data.message
                            : shopglut_cart_ajax.i18n.coupon_applied || 'Coupon applied';
                        this.showMessage(message, 'success');
                        form.find('#couponCode').val('');

                        // Reload cart to show applied coupon
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        const errorMessage = response.data && response.data.message
                            ? response.data.message
                            : shopglut_cart_ajax.i18n.invalid_coupon || 'Invalid coupon';
                        this.showMessage(errorMessage, 'error');
                    }
                },
                error: () => {
                    this.showCouponMessage(messageContainer, shopglut_cart_ajax.i18n.error || 'Error applying coupon', 'error');
                },
                complete: () => {
                    submitButton.text(originalText).prop('disabled', false);
                }
            });
        }

        /**
         * Remove coupon
         */
        removeCoupon(e) {
            e.preventDefault();
            const button = $(e.currentTarget);
            const couponCode = button.data('coupon');

            if (!couponCode) {
                return;
            }

            const couponContainer = button.closest('.applied-coupons');
            if (!couponContainer.length) {
                return;
            }

            this.setLoading(couponContainer, true);

            $.ajax({
                url: shopglut_cart_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'shopglut_remove_coupon',
                    coupon_code: couponCode,
                    nonce: shopglut_cart_ajax.nonce
                },
                timeout: 30000,
                success: (response) => {
                    if (response.success) {
                        const message = response.data && response.data.message
                            ? response.data.message
                            : shopglut_cart_ajax.i18n.coupon_removed || 'Coupon removed';
                        this.showMessage(message, 'success');

                        // Reload page to update cart display
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        const errorMessage = response.data && response.data.message
                            ? response.data.message
                            : shopglut_cart_ajax.i18n.error || 'Error removing coupon';
                        this.showMessage(errorMessage, 'error');
                    }
                },
                error: () => {
                    this.showMessage(shopglut_cart_ajax.i18n.error || 'Error removing coupon', 'error');
                },
                complete: () => {
                    this.setLoading(couponContainer, false);
                }
            });
        }

        /**
         * Proceed to checkout
         */
        proceedToCheckout(e) {
            e.preventDefault();
            window.location.href = shopglut_cart_ajax.checkout_url;
        }

        /**
         * Show loading state - using fixed overlay
         */
        setLoading(element, isLoading) {
            // Get or create a loader overlay
            let $overlay = $('.shopglut-loader-overlay');

            if ($overlay.length === 0) {
                $overlay = $(`
                    <div class="shopglut-loader-overlay">
                        <div class="shopglut-loader-container">
                            <div class="shopglut-loader-spinner"></div>
                            <div class="shopglut-loader-dash-circle"></div>
                        </div>
                    </div>
                `);
                $('body').append($overlay);
            }

            if (isLoading) {
                // Position overlay over specific element using fixed positioning
                const offset = element.offset();
                const scrollTop = $(window).scrollTop();
                const scrollLeft = $(window).scrollLeft();

                // Use fixed position relative to viewport
                $overlay.css({
                    position: 'fixed',
                    top: (offset.top - scrollTop) + 'px',
                    left: (offset.left - scrollLeft) + 'px',
                    width: element.outerWidth(),
                    height: element.outerHeight(),
                    backgroundColor: 'rgba(255, 255, 255, 0.7)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    zIndex: 999999
                }).addClass('active');

                // Disable buttons and inputs
                element.find('button, input').addClass(this.disabledClass).prop('disabled', true);
            } else {
                // Hide overlay immediately
                $overlay.removeClass('active').css('display', 'none');

                // Re-enable buttons and inputs
                element.find('button, input').removeClass(this.disabledClass).prop('disabled', false);
            }
        }

        /**
         * Show message notification
         */
        showMessage(message, type) {
            // Remove any existing notifications
            $('.shopglut-notification').remove();

            // Create notification element
            const notification = $(`
                <div class="shopglut-notification shopglut-notification-${type}">
                    <span>${message}</span>
                    <button class="notification-close">×</button>
                </div>
            `);

            $('body').append(notification);

            // Auto hide after 5 seconds
            setTimeout(() => {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);

            // Close button handler
            notification.find('.notification-close').on('click', function() {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            });
        }

        /**
         * Show coupon message
         */
        showCouponMessage(container, message, type) {
            // Decode HTML entities in message before displaying
            const decodedMessage = $('<div/>').html(message).text();

            container
                .removeClass('success error')
                .addClass(type)
                .text(decodedMessage)
                .show();

            // Auto hide after 5 seconds
            setTimeout(() => {
                container.fadeOut();
            }, 5000);
        }

        /**
         * Update cart fragments
         */
        updateCartFragments(fragments) {
            $.each(fragments, function(key, value) {
                $(key).replaceWith(value);
            });

            // Trigger cart updated event
            $(document.body).trigger('wc_fragment_refresh');
        }

        /**
         * Handle cart updated event
         */
        onCartUpdated() {
            // Cart has been updated - any additional logic can go here
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        const cartTemplate = new ShopglutCartTemplate1();
        cartTemplate.init();

        // Store original quantities for all inputs
        $('.shopglut-cart.template1 .qty-input').each(function() {
            const input = $(this);
            input.data('original-value', input.val());
        });
    });

})(jQuery);
