jQuery(document).ready(function($) {

    // Handle module toggle switches
    $('.shopglut-module-toggle').on('change', function() {
        const $toggle = $(this);
        const $moduleCard = $toggle.closest('.shopglut-module-card, .shopglut-module-card-new');
        const module = $toggle.data('module');
        const enabled = $toggle.is(':checked');
        const isNewCard = $moduleCard.hasClass('shopglut-module-card-new');

        // Show loading state using overlay for new cards
        if (isNewCard) {
            $moduleCard.addClass('loading');
        } else {
            // For old cards, use existing method
            const originalWidth = $moduleCard.outerWidth();
            const originalHeight = $moduleCard.outerHeight();

            $moduleCard.css({
                'width': originalWidth + 'px',
                'height': originalHeight + 'px',
                'min-width': originalWidth + 'px',
                'min-height': originalHeight + 'px'
            });

            $moduleCard.addClass('shopglut-preserve-dimensions');
            $moduleCard.addClass('shopglut-loading');
        }

        $toggle.prop('disabled', true);

        $.ajax({
            url: shopglut_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'toggle_shopglut_module',
                module: module,
                enabled: enabled,
                nonce: shopglut_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showNotification(response.data.message, 'success');

                    // Check if we're on the WooCommerce Builder Modules page
                    if (window.location.href.indexOf('shopg_woocommerce_builder') !== -1) {
                        // Reload the page after a short delay to show the success message
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        // Update UI for new card style
                        if (isNewCard) {
                            const $link = $moduleCard.find('.module-link');
                            if (enabled) {
                                $moduleCard.removeClass('disabled');
                                $link.removeClass('disable-link disabled-link');
                            } else {
                                $moduleCard.addClass('disabled');
                                $link.addClass('disable-link disabled-link');
                            }
                        }
                    }
                } else {
                    // Revert toggle state on error
                    $toggle.prop('checked', !enabled);
                    showNotification(response.data.message || 'Unknown error occurred.', 'error');
                }
            },
            error: function(xhr, status, error) {
                // Revert toggle state on error
                $toggle.prop('checked', !enabled);
                showNotification('An error occurred while toggling the module.', 'error');
            },
            complete: function() {
                // Remove loading state
                $toggle.prop('disabled', false);

                if (isNewCard) {
                    $moduleCard.removeClass('loading');
                } else {
                    $moduleCard.removeClass('shopglut-loading');
                    $moduleCard.removeClass('shopglut-preserve-dimensions');

                    // Clear inline dimension styles after loading
                    $moduleCard.css({
                        'width': '',
                        'height': '',
                        'min-width': '',
                        'min-height': ''
                    });
                }
            }
        });
    });


    // Show notification function - uses centralized ShopGlutNotification utility
    function showNotification(message, type) {
        if (typeof ShopGlutNotification !== 'undefined') {
            ShopGlutNotification.show(message, type, { duration: 3000 });
        } else {
            // Fallback if centralized utility not loaded
            const $notification = $('<div class="shopglut-notification shopglut-notification-' + type + '">' + message + '</div>');
            $('body').append($notification);
            setTimeout(function() { $notification.addClass('show'); }, 100);
            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() { $notification.remove(); }, 300);
            }, 3000);
        }
    }
});