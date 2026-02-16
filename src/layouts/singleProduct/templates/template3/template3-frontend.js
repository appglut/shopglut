jQuery(document).ready(function($) {
    'use strict';

    // Quantity selector functionality - Single unified handler for both simple and variable products
    $(document).on('click', '.qty-decrease, .qty-increase, .quantity-btn', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent event bubbling

        var $button = $(this);
        var isIncrease = $button.hasClass('qty-increase');
        var isDecrease = $button.hasClass('qty-decrease');

        // Find the quantity input - try multiple selectors to support both custom and WooCommerce inputs
        var $input = $button.siblings('.qty-input, input[name="quantity"], .qty, .input-text');

        // If sibling approach doesn't work, try finding within the same parent or nearby
        if ($input.length === 0) {
            $input = $button.closest('.quantity-selector, .quantity').find('.qty-input, input[name="quantity"], .qty, .input-text');
        }

        // For WooCommerce variable products, try finding in the closest quantity wrapper
        if ($input.length === 0) {
            $input = $button.closest('.quantity-cart-wrapper, .woocommerce-variation-add-to-cart').find('input[name="quantity"], .qty, .input-text');
        }

        if ($input.length > 0) {
            var currentValue = parseInt($input.val()) || 1;

            if (isIncrease) {
                var maxValue = parseInt($input.attr('max')) || 9999;
                // Handle WooCommerce's -1 (no limit) case
                if (maxValue === -1) {
                    maxValue = 9999;
                }

                if (currentValue < maxValue) {
                    var newValue = currentValue + 1;
                    $input.val(newValue).trigger('change').trigger('input');
                }
            } else if (isDecrease) {
                var minValue = parseInt($input.attr('min')) || 1;

                if (currentValue > minValue) {
                    var newValue = currentValue - 1;
                    $input.val(newValue).trigger('change').trigger('input');
                }
            }
        }
    });

    // Validate quantity input for both custom and WooCommerce inputs
    $(document).on('change keyup', '.qty-input, input[name="quantity"], .qty', function() {
        var $input = $(this);
        var value = parseInt($input.val()) || 1;
        var minValue = parseInt($input.attr('min')) || 1;
        var maxValue = parseInt($input.attr('max')) || 9999;

        // Handle WooCommerce's -1 (no limit) case
        if (maxValue === -1) {
            maxValue = 9999;
        }

        if (value < minValue) {
            $input.val(minValue);
        } else if (value > maxValue) {
            $input.val(maxValue);
        }
    });

    // DISABLED: Handle variable product AJAX submission using improved approach
    $(document).on('submit', 'form.shopglut-variations-form.DISABLED', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        var $form = $(this);
        var $button = $form.find('.shopglut-variable-add-to-cart');

        // Prevent any other handlers from firing or duplicate processing
        if ($button.hasClass('loading') || $button.prop('disabled') || $form.data('processing')) {
            return false;
        }

        // Check if variation is selected by looking for the variation_id
        var variation_id = $form.find('input[name="variation_id"]').val();
        if (!variation_id || variation_id == '0' || variation_id == '') {
            showNotification('Please select product options before adding to cart.', 'error');
            return false;
        }

        // Mark as processing to prevent duplicate calls
        $form.data('processing', true);

        // Show loading state
        var originalText = $button.text();
        $button.text('Adding...').prop('disabled', true).addClass('loading');

        // Get all form data
        var formData = $form.serializeArray();
        var data = {};

        // Convert form data to object
        $.each(formData, function(i, field) {
            data[field.name] = field.value;
        });

        // Ensure we have the product ID (add-to-cart parameter)
        if (!data['add-to-cart']) {
            var product_id = $form.find('input[name="add-to-cart"]').val() ||
                           $form.find('button[name="add-to-cart"]').val() ||
                           $form.data('product_id');
            if (product_id) {
                data['add-to-cart'] = product_id;
            }
        }

        // Add AJAX action and nonce for template3 handler
        data.action = 'shopglut_template3_add_variable_to_cart';
        data.nonce = shopglut_frontend_vars.nonce;

        $.ajax({
            type: 'POST',
            url: shopglut_frontend_vars.ajax_url,
            data: data,
            dataType: 'json',
            beforeSend: function() {
                $button.removeClass('added').addClass('loading');
            },
            complete: function() {
                $button.removeClass('loading');
            },
            success: function(response) {

                if (response.success === false) {
                    // Handle error from our custom handler
                    var errorMsg = response.data && response.data.error ? response.data.error : 'Failed to add product to cart';
                    showNotification('Error: ' + errorMsg, 'error');
                    $button.text(originalText).prop('disabled', false);
                    return;
                }

                if (response.error && response.product_url) {
                    window.location = response.product_url;
                    return;
                }

                // Check for errors in response
                if (response.error) {
                    showNotification('Error: ' + response.error, 'error');
                    $button.text(originalText).prop('disabled', false);
                    return;
                }

                if (response.fragments) {
                    // Update cart fragments
                    $.each(response.fragments, function(key, value) {
                        $(key).replaceWith(value);
                    });
                }

                // Show success state
                $button.addClass('added').text('Added to Cart');

                // Enhanced success message with quantity feedback
                var successMessage = 'Product added to cart successfully!';
                if (response.item_quantity && response.was_merged) {
                    successMessage = 'Quantity updated to ' + response.item_quantity + ' in cart';
                } else if (response.item_quantity) {
                    successMessage = 'Added to cart (quantity: ' + response.item_quantity + ')';
                }
                showNotification(successMessage, 'success');

                // Reset quantity input to 1 for better UX (optional)
                var $qtyInput = $form.find('.qty-input');
                if ($qtyInput.length > 0) {
                    $qtyInput.val(1);
                }

                // Trigger WooCommerce events
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);

                // Reset button after 2 seconds
                setTimeout(function() {
                    $button.text(originalText).removeClass('added').prop('disabled', false);
                }, 2000);
            },
            error: function(xhr, status, error) {

                // Try to parse error response
                try {
                    var errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.data && errorResponse.data.error) {
                        showNotification('Error: ' + errorResponse.data.error, 'error');
                    } else {
                        showNotification('Failed to add product to cart. Please try again.', 'error');
                    }
                } catch (e) {
                    showNotification('Failed to add product to cart. Please try again.', 'error');
                }

                $button.text(originalText).prop('disabled', false);
                $form.data('processing', false);
            }
        });

        return false;
    });

    // DISABLED: Prevent WooCommerce's native form submission for our custom forms
    $(document).on('submit', 'form.variations_form[data-shopglut-handled="true"].DISABLED', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        return false;
    });

    // WORKING VERSION: Just add a simple notification after page loads
    $(document).ready(function() {
        // Check if we just added something to cart (look for URL parameter or session)
        if (window.location.search.includes('add-to-cart=') || sessionStorage.getItem('shopglut_just_added')) {
            sessionStorage.removeItem('shopglut_just_added');
        }

        // Set flag before form submits
        $(document).on('submit', 'form.shopglut-variations-form', function() {
            sessionStorage.setItem('shopglut_just_added', 'true');
        });
    });

    // Backup: Enable WooCommerce AJAX for variable products
    $(document).ready(function() {

        // Enable WooCommerce AJAX add to cart for variable products
        $('body').on('adding_to_cart', function(event, $button, data) {
            $button.addClass('loading');
        });

        $('body').on('added_to_cart', function(event, fragments, cart_hash, $button) {
            $button.removeClass('loading').addClass('added');

            // Show success message - try multiple methods
            if (typeof showNotification === 'function') {
                showNotification('Product added to cart successfully!', 'success');
            } else {
                // Fallback notification methods

                // Show a simple alert as fallback
                var $notice = $('<div class="woocommerce-message" style="position: fixed; bottom: 20px; right: 20px; background: #28a745; color: white; padding: 15px 20px; border-radius: 4px; z-index: 9999; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">Product added to cart successfully!</div>');
                $('body').append($notice);

                // Remove notice after 3 seconds
                setTimeout(function() {
                    $notice.fadeOut(function() {
                        $notice.remove();
                    });
                }, 3000);
            }

            // Reset quantity input
            var $form = $button.closest('form');
            var $qtyInput = $form.find('.qty-input');
            if ($qtyInput.length > 0) {
                $qtyInput.val(1);
            }

            // Reset button text after 2 seconds
            setTimeout(function() {
                $button.removeClass('added').text($button.data('original-text') || 'Add to cart');
            }, 2000);
        });

        $('body').on('wc_add_to_cart_error', function(event, error_message) {
            if (typeof showNotification === 'function') {
                showNotification('Error: ' + error_message, 'error');
            } else {
                // Fallback error notification
                var $errorNotice = $('<div class="woocommerce-error" style="position: fixed; top: 20px; right: 20px; background: #dc3545; color: white; padding: 15px 20px; border-radius: 4px; z-index: 9999; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">Error: ' + error_message + '</div>');
                $('body').append($errorNotice);

                // Remove notice after 5 seconds
                setTimeout(function() {
                    $errorNotice.fadeOut(function() {
                        $errorNotice.remove();
                    });
                }, 5000);
            }
        });
    });

    // Listen for variation found events to update our form data
    $(document).on('found_variation', 'form.shopglut-variations-form', function(event, variation) {
        var $form = $(this);

        // Store variation data for our AJAX submission
        $form.data('current_variation', variation);

        // Update hidden inputs if they exist
        var $variation_id_input = $form.find('input[name="variation_id"]');
        if ($variation_id_input.length === 0) {
            $form.append('<input type="hidden" name="variation_id" value="' + variation.variation_id + '">');
        } else {
            $variation_id_input.val(variation.variation_id);
        }
    });

    // Handle variation reset
    $(document).on('reset_data', 'form.shopglut-variations-form', function(event) {
        var $form = $(this);
        $form.removeData('current_variation');
        $form.find('input[name="variation_id"]').val('');
    });

    // Add to Cart functionality for simple products only
    $(document).on('click', '.add-to-cart-btn:not(.single_add_to_cart_button)', function(e) {
        e.preventDefault();

        var $button = $(this);
        var $container = $button.closest('.shopglut-single-product-container');
        var $groupedForm = $button.closest('form.grouped_form');

        // Handle grouped product forms
        if ($groupedForm.length > 0) {
            // Check if at least one grouped product quantity is greater than 0
            var hasQuantity = false;
            $groupedForm.find('.qty-input').each(function() {
                if (parseInt($(this).val()) > 0) {
                    hasQuantity = true;
                    return false; // Break the loop
                }
            });

            if (!hasQuantity) {
                showNotification('Please select at least one product to add to cart.', 'error');
                return;
            }

            // Let the form submit naturally for grouped products
            return true;
        }

        // Handle external/affiliate products
        if ($button.hasClass('external-product-btn')) {
            // External products are handled by their link - no additional logic needed
            return true;
        }

        // Handle simple products with custom AJAX
        var $qtyInput = $container.find('.qty-input');
        var quantity = parseInt($qtyInput.val()) || 1;

        // Get product ID from current page
        var productId = getProductIdFromCurrentPage();

        if (!productId) {
            showNotification('Product ID not found', 'error');
            return;
        }

        // Show loading state
        var originalText = $button.text();
        $button.text('Adding...').prop('disabled', true);

        // Prepare data for WooCommerce AJAX add to cart
        var data = {
            action: 'woocommerce_add_to_cart',
            product_id: productId,
            quantity: quantity,
            product_sku: '',
            variation_id: 0,
            variation: {}
        };

        $.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.ajax_url,
            data: data,
            beforeSend: function() {
                $button.removeClass('added').addClass('loading');
            },
            complete: function() {
                $button.removeClass('loading');
            },
            success: function(response) {
                if (response.error && response.product_url) {
                    window.location = response.product_url;
                    return;
                }

                if (response.fragments) {
                    // Update cart fragments
                    $.each(response.fragments, function(key, value) {
                        $(key).replaceWith(value);
                    });
                }

                // Show success state
                $button.addClass('added').text('Added to Cart');
                showNotification('Product added to cart successfully!', 'success');

                // Trigger WooCommerce events
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);

                // Reset button after 2 seconds
                setTimeout(function() {
                    $button.text(originalText).removeClass('added').prop('disabled', false);
                }, 2000);
            },
            error: function(xhr, status, error) {
                showNotification('Failed to add product to cart. Please try again.', 'error');
                $button.text(originalText).prop('disabled', false);
            }
        });
    });

    // Handle grouped product form submissions (variable products are handled in the click handler above)
    $(document).on('submit', 'form.grouped_form', function(e) {
        var $form = $(this);
        var $button = $form.find('.add-to-cart-btn');

        // Show loading state for grouped products
        var originalText = $button.text();
        $button.text('Adding...').prop('disabled', true);

        // Reset button state after form processes
        setTimeout(function() {
            $button.text(originalText).prop('disabled', false);
        }, 3000);
    });

    // Quick Add functionality for related products
    $(document).on('click', '.quick-add-btn', function(e) {
        e.preventDefault();

        var $button = $(this);
        var productId = $button.data('product-id');

        if (!productId) {
            showNotification('Product ID not found', 'error');
            return;
        }

        // Show loading state
        var originalText = $button.text();
        $button.text('Adding...').prop('disabled', true);

        var data = {
            action: 'woocommerce_add_to_cart',
            product_id: productId,
            quantity: 1
        };

        $.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.ajax_url,
            data: data,
            beforeSend: function() {
                $button.removeClass('added').addClass('loading');
            },
            complete: function() {
                $button.removeClass('loading');
            },
            success: function(response) {
                if (response.error && response.product_url) {
                    window.location = response.product_url;
                    return;
                }

                if (response.fragments) {
                    // Update cart fragments
                    $.each(response.fragments, function(key, value) {
                        $(key).replaceWith(value);
                    });
                }

                // Show success state
                $button.addClass('added').text('Added!');
                showNotification('Product added to cart successfully!', 'success');

                // Trigger WooCommerce events
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);

                // Reset button after 2 seconds
                setTimeout(function() {
                    $button.text(originalText).removeClass('added').prop('disabled', false);
                }, 2000);
            },
            error: function(xhr, status, error) {
                showNotification('Failed to add product to cart. Please try again.', 'error');
                $button.text(originalText).prop('disabled', false);
            }
        });
    });

    // Thumbnail gallery functionality
    $(document).on('click', '.thumbnail-item', function(e) {
        e.preventDefault();

        var $thumbnail = $(this);
        var $gallery = $thumbnail.closest('.product-gallery-section');
        var $mainImage = $gallery.find('.main-product-image');
        var newImageSrc = $thumbnail.find('.thumbnail-image').attr('src');
        var newImageAlt = $thumbnail.find('.thumbnail-image').attr('alt');

        // Update active thumbnail
        $gallery.find('.thumbnail-item').removeClass('active');
        $thumbnail.addClass('active');

        // Update main image with fade effect
        $mainImage.fadeOut(200, function() {
            $(this).attr('src', newImageSrc).attr('alt', newImageAlt).fadeIn(200);
        });
    });

    // Attribute selection functionality (for color swatches, sizes, etc.)
    $(document).on('click', '.color-swatch, .size-button, .attribute-value', function(e) {
        e.preventDefault();

        var $item = $(this);
        var $group = $item.closest('.attribute-group');

        // Remove active class from siblings and add to clicked item
        $group.find('.color-swatch, .size-button, .attribute-value').removeClass('active selected');
        $item.addClass('active selected');
    });

    // Wishlist functionality (basic implementation)
    $(document).on('click', '.wishlist-btn', function(e) {
        e.preventDefault();

        var $button = $(this);
        var productId = getProductIdFromCurrentPage();

        if (!productId) {
            showNotification('Product ID not found', 'error');
            return;
        }

        // Toggle wishlist state
        if ($button.hasClass('in-wishlist')) {
            $button.removeClass('in-wishlist').find('i').removeClass('fas').addClass('far');
            showNotification('Removed from wishlist', 'success');
        } else {
            $button.addClass('in-wishlist').find('i').removeClass('far').addClass('fas');
            showNotification('Added to wishlist', 'success');
        }
    });

    // Compare functionality (basic implementation)
    $(document).on('click', '.compare-btn', function(e) {
        e.preventDefault();

        var $button = $(this);
        var productId = getProductIdFromCurrentPage();

        if (!productId) {
            showNotification('Product ID not found', 'error');
            return;
        }

        // Toggle compare state
        if ($button.hasClass('in-compare')) {
            $button.removeClass('in-compare');
            showNotification('Removed from compare', 'success');
        } else {
            $button.addClass('in-compare');
            showNotification('Added to compare', 'success');
        }
    });

    // Helper function to get product ID from current page
    function getProductIdFromCurrentPage() {
        // Try to get from URL parameters first
        var urlParams = new URLSearchParams(window.location.search);
        var productId = urlParams.get('product_id') || urlParams.get('p');

        if (productId) {
            return productId;
        }

        // Try to get from WordPress body classes
        var bodyClasses = $('body').attr('class') || '';
        var postIdMatch = bodyClasses.match(/postid-(\d+)/);
        if (postIdMatch) {
            return postIdMatch[1];
        }

        // Try to get from data attributes on the product container
        var $container = $('.shopglut-single-product-container');
        productId = $container.data('product-id');
        if (productId) {
            return productId;
        }

        // Try to get from WooCommerce variables if available
        if (typeof wc_single_product_params !== 'undefined' && wc_single_product_params.post_id) {
            return wc_single_product_params.post_id;
        }

        // Try to get from global WordPress variables
        if (typeof shopglut_vars !== 'undefined' && shopglut_vars.product_id) {
            return shopglut_vars.product_id;
        }

        return null;
    }

    // Notification function - uses centralized ShopGlutNotification utility
    function showNotification(message, type) {
        if (typeof ShopGlutNotification !== 'undefined') {
            ShopGlutNotification.show(message, type, { position: 'top-right', duration: 3000 });
        } else {
            // Fallback if centralized utility not loaded
            $('.shopglut-frontend-notification').remove();
            var notification = $('<div class="shopglut-frontend-notification shopglut-notification-' + type + '">' +
                               '<span>' + message + '</span>' +
                               '<button class="notification-close">×</button>' +
                               '</div>');
            $('body').append(notification);
            setTimeout(function() {
                notification.fadeOut(300, function() { $(this).remove(); });
            }, 3000);
            notification.find('.notification-close').on('click', function() {
                notification.fadeOut(300, function() { $(this).remove(); });
            });
        }
    }

    // Removed duplicate alternative handler to prevent double-firing

    // Initialize on page load
    initializeShopglutTemplate();

    function initializeShopglutTemplate() {
        // Set initial active states
        $('.thumbnail-gallery .thumbnail-item:first').addClass('active');
        $('.color-swatches .color-swatch:first').addClass('active');
        $('.size-buttons .size-button:first').addClass('active');

        // Initialize WooCommerce tabs
        initializeWooCommerceTabs();

        // Ensure quantity input has proper min value
        $('.qty-input').each(function() {
            var $input = $(this);
            if (!$input.attr('min')) {
                $input.attr('min', '1');
            }
            if (!$input.val() || parseInt($input.val()) < 1) {
                $input.val(1);
            }
        });

        // Initialize quantity selector elements
    }

    // Initialize WooCommerce tabs functionality
    function initializeWooCommerceTabs() {
        var $tabsContainer = $('.shopglut-single-product-container .woocommerce-tabs');
        if ($tabsContainer.length === 0) {
            return;
        }

        var $tabs = $tabsContainer.find('.wc-tabs li');
        var $panels = $tabsContainer.find('.woocommerce-Tabs-panel');

        // Set first tab as active by default
        if ($tabs.length > 0 && !$tabs.hasClass('active')) {
            $tabs.first().addClass('active');
            $panels.first().addClass('active');
        }

        // Handle tab clicks
        $tabs.find('a').on('click', function(e) {
            e.preventDefault();

            var $link = $(this);
            var $tab = $link.closest('li');
            var targetId = $link.attr('href');
            var $targetPanel = $tabsContainer.find(targetId);

            if ($targetPanel.length === 0) {
                return;
            }

            // Remove active classes
            $tabs.removeClass('active');
            $panels.removeClass('active');

            // Add active classes to clicked tab and corresponding panel
            $tab.addClass('active');
            $targetPanel.addClass('active');

            // No automatic scroll - let user control scrolling
        });

        // Handle hash changes in URL
        function handleHashChange() {
            var hash = window.location.hash;
            if (hash && hash.indexOf('#tab-') === 0) {
                var $targetTab = $tabsContainer.find('a[href="' + hash + '"]');
                if ($targetTab.length > 0) {
                    $targetTab.trigger('click');
                }
            }
        }

        // Check hash on load
        handleHashChange();

        // Listen for hash changes
        $(window).on('hashchange', handleHashChange);
    }

    // ==================== REVIEW FORM FUNCTIONALITY ====================
    // Initialize star rating system for review forms
    function initializeReviewStarRating() {
        // Handle star rating clicks in review form
        $(document).on('click', '.star-rating-input i', function(e) {
            e.preventDefault();

            var $star = $(this);
            var $ratingContainer = $star.closest('.star-rating-input');
            var $ratingInput = $ratingContainer.find('input[name="rating"]');
            var rating = parseInt($star.attr('data-rating'));

            // Update hidden input
            $ratingInput.val(rating).trigger('change');

            // Update star visual state
            var $stars = $ratingContainer.find('i');
            $stars.each(function(index) {
                var starValue = parseInt($(this).attr('data-rating'));
                if (starValue <= rating) {
                    $(this).removeClass('far fa-star').addClass('fas fa-star active');
                } else {
                    $(this).removeClass('fas fa-star active').addClass('far fa-star');
                }
            });

            // Add visual feedback animation
            $star.addClass('star-selected');
            setTimeout(function() {
                $star.removeClass('star-selected');
            }, 300);
        });

        // Handle star hover effects
        $(document).on('mouseenter', '.star-rating-input i', function() {
            var $star = $(this);
            var $ratingContainer = $star.closest('.star-rating-input');
            var rating = parseInt($star.attr('data-rating'));
            var $stars = $ratingContainer.find('i');

            $stars.each(function(index) {
                var starValue = parseInt($(this).attr('data-rating'));
                if (starValue <= rating) {
                    $(this).addClass('hover');
                } else {
                    $(this).removeClass('hover');
                }
            });
        });

        $(document).on('mouseleave', '.star-rating-input', function() {
            $(this).find('i').removeClass('hover');
        });
    }

    // Handle review form submission
    function handleReviewFormSubmission() {
        $(document).on('submit', '.comment-form', function(e) {
            var $form = $(this);
            var $ratingInput = $form.find('input[name="rating"]');
            var rating = parseInt($ratingInput.val() || 0);

            // Validate rating is selected
            if (rating === 0) {
                e.preventDefault();
                showNotification('Please select a rating before submitting your review.', 'error');

                // Scroll to rating section
                $ratingInput.closest('.star-rating-input')[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                // Highlight the rating section
                $ratingInput.closest('.star-rating-input').addClass('rating-error');
                setTimeout(function() {
                    $ratingInput.closest('.star-rating-input').removeClass('rating-error');
                }, 2000);

                return false;
            }

            // Validate required fields
            var $author = $form.find('#author');
            var $email = $form.find('#email');
            var $comment = $form.find('#comment');

            if ($author.length && $author.prop('required') && !$author.val().trim()) {
                e.preventDefault();
                showNotification('Please enter your name.', 'error');
                $author.focus();
                return false;
            }

            if ($email.length && $email.prop('required') && !$email.val().trim()) {
                e.preventDefault();
                showNotification('Please enter your email address.', 'error');
                $email.focus();
                return false;
            }

            if ($comment.length && $comment.prop('required') && !$comment.val().trim()) {
                e.preventDefault();
                showNotification('Please enter your review.', 'error');
                $comment.focus();
                return false;
            }

            // Show loading state
            var $submitBtn = $form.find('.submit-review-btn');
            var originalText = $submitBtn.html();
            $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Submitting...').prop('disabled', true);

            // Let the form submit normally to WordPress
            // The form will be redirected after submission by WordPress
        });
    }

    // Initialize review functionality
    initializeReviewStarRating();
    handleReviewFormSubmission();
});

// Add CSS styles for interactions
// Notification styles are now centralized in shopglut-notification.css
jQuery(document).ready(function($) {
    if (!$('#shopglut-frontend-styles').length) {
        $('head').append(`
            <style id="shopglut-frontend-styles">
                /* Button states */
                .add-to-cart-btn.loading,
                .quick-add-btn.loading {
                    opacity: 0.7;
                    cursor: not-allowed;
                }

                .add-to-cart-btn.added,
                .quick-add-btn.added {
                    background-color: #28a745 !important;
                }

                /* WooCommerce View Cart button styling for Related Products */
                .related-product-info .added_to_cart,
                .related-products-section .added_to_cart,
                .shopglut-single-product-container .related-product-card .added_to_cart {
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    min-height: 44px !important;
                    padding: 12px 20px !important;
                    background: #28a745 !important;
                    color: white !important;
                    border: none !important;
                    border-radius: 8px !important;
                    font-size: 14px !important;
                    font-weight: 600 !important;
                    text-decoration: none !important;
                    width: 100% !important;
                    margin-top: 10px !important;
                }

                .related-product-info .added_to_cart:hover,
                .related-products-section .added_to_cart:hover,
                .shopglut-single-product-container .related-product-card .added_to_cart:hover {
                    background: #218838 !important;
                }

                /* Thumbnail interactions */
                .thumbnail-item {
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .thumbnail-item:hover {
                    opacity: 0.8;
                }

                .thumbnail-item.active {
                    border: 2px solid #667eea;
                }

                /* Attribute interactions */
                .color-swatch,
                .size-button,
                .attribute-value {
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .color-swatch:hover,
                .size-button:hover,
                .attribute-value:hover {
                    transform: scale(1.05);
                }

                .color-swatch.active,
                .size-button.active,
                .attribute-value.active {
                    transform: scale(1.1);
                    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                }

                /* Quantity selector improvements */
                .qty-decrease,
                .qty-increase {
                    cursor: pointer;
                    user-select: none;
                    transition: all 0.2s ease;
                }

                .qty-decrease:hover,
                .qty-increase:hover {
                    background-color: #f8f9fa;
                }

                /* Wishlist and Compare states */
                .wishlist-btn.in-wishlist,
                .compare-btn.in-compare {
                    color: #dc3545;
                }

                /* Star Rating Input Styles */
                .star-rating-input {
                    display: inline-flex;
                    gap: 4px;
                    cursor: pointer;
                }

                .star-rating-input i {
                    font-size: 24px;
                    color: #d1d5db;
                    transition: all 0.2s ease;
                    cursor: pointer;
                }

                .star-rating-input i.fas.fa-star {
                    color: #fbbf24;
                }

                .star-rating-input i.hover {
                    transform: scale(1.1);
                    color: #fbbf24;
                }

                .star-rating-input i.active {
                    color: #fbbf24;
                }

                .star-rating-input i.star-selected {
                    animation: starPulse 0.3s ease;
                }

                @keyframes starPulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.3); }
                    100% { transform: scale(1); }
                }

                .star-rating-input.rating-error {
                    animation: ratingShake 0.5s ease;
                }

                @keyframes ratingShake {
                    0%, 100% { transform: translateX(0); }
                    25% { transform: translateX(-5px); }
                    75% { transform: translateX(5px); }
                }

                /* Review Form Styles */
                .review-form-container .form-group {
                    margin-bottom: 16px;
                }

                .review-form-container .form-label {
                    display: block;
                    margin-bottom: 8px;
                    font-weight: 500;
                }

                .review-form-container .form-input,
                .review-form-container .form-textarea {
                    width: 100%;
                    padding: 12px 16px;
                    border: 1px solid #d1d5db;
                    border-radius: 6px;
                    font-size: 14px;
                    transition: border-color 0.2s ease;
                }

                .review-form-container .form-input:focus,
                .review-form-container .form-textarea:focus {
                    outline: none;
                    border-color: #667eea;
                    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                }

                .review-form-container .form-textarea {
                    min-height: 120px;
                    resize: vertical;
                }

                .review-form-container .submit-review-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    padding: 12px 24px;
                    background-color: #667eea;
                    color: #ffffff;
                    border: none;
                    border-radius: 6px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .review-form-container .submit-review-btn:hover {
                    background-color: #5a67d8;
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
                }

                .review-form-container .submit-review-btn:disabled {
                    opacity: 0.6;
                    cursor: not-allowed;
                    transform: none;
                }
            </style>
        `);
    }
});