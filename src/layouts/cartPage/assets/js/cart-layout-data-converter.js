// Fixed JavaScript for Cart Page Layout with proper security and consistency
jQuery(document).ready(function($) {

    // Wait for everything to fully load including images, styles, and scripts
    $(window).on('load', function() {
        // Add a small delay to ensure everything is rendered
        setTimeout(function() {
            $(".loader-overlay").css({"display": "none", "opacity": "0"});
            $(".loader-container").hide();
        }, 500); // 500ms delay to ensure complete loading
    });

    // Fallback: Hide loader after maximum 10 seconds even if something doesn't load
    setTimeout(function() {
        $(".loader-overlay").css({"display": "none", "opacity": "0"});
        $(".loader-container").hide();
    }, 10000);

    // Prevent form submission - handle via AJAX instead
    $("#shopglut_shop_layouts").on("submit", function (e) {
        e.preventDefault();
        return false;
    });

    // Reset settings functionality for Cart Page
    $("#reset-settings-button").on("click", function (e) {
        e.preventDefault();

        // Show confirmation dialog
        if (!confirm('Are you sure you want to reset all settings? This action cannot be undone.')) {
            return;
        }

        // Show loading
        $(".loader-overlay").css({"display": "flex", "opacity": "1"});
        $(".loader-container").show();

        var postid = $("#shopg_shop_layoutid").val();

        // Fallback: try different layout ID sources
        if (!postid) {
            postid = $("input[name='shopg_shop_layoutid']").val();
        }
        if (!postid) {
            postid = shopglut_admin_vars.layout_id;
        }
        if (!postid) {
            postid = $("input[name='layout_id']").val();
        }

        var nonce = $("input[name='shopg_cartpage_layouts_nonce']").val();

        // Fallback: try different nonce field names
        if (!nonce) {
            nonce = $("#shopg_cartpage_layouts_nonce").val();
        }
        if (!nonce) {
            nonce = $("input[name='_wpnonce']").val();
        }
        if (!nonce) {
            nonce = shopglut_admin_vars.nonce;
        }

        if (!nonce) {
            showNotification('Security error: nonce not found', 'error');
            $(".loader-overlay").css({"display": "none", "opacity": "0"});
            $(".loader-container").hide();
            return;
        }

        if (!postid) {
            showNotification('Error: Layout ID not found', 'error');
            $(".loader-overlay").css({"display": "none", "opacity": "0"});
            $(".loader-container").hide();
            return;
        }

        // AJAX request to reset settings
        $.ajax({
            type: "POST",
            url: shopglut_admin_vars.ajax_url || shopglut_admin_vars.ajaxurl || ajaxurl,
            data: {
                action: 'reset_shopg_cartlayout_settings',
                nonce: nonce,
                layout_id: postid
            },
            timeout: 30000,
            success: function (response) {
                // Always hide loader first
                $(".loader-overlay").css({"display": "none", "opacity": "0"});
                $(".loader-container").hide();

                if (response.success) {
                    // Reload the page to show reset settings
                   window.location.reload();
                } else {
                    var errorMsg = response.data ? response.data : 'Unknown error occurred';
                    showNotification('Failed to reset: ' + errorMsg, 'error');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $(".loader-overlay").css({"display": "none", "opacity": "0"});
                $(".loader-container").hide();

                showNotification('Reset failed: ' + textStatus, 'error');
            }
        });
    });

    // Main form submission for Cart Page Layout
    $("#cartLayout-publishing-action #publish").on("click", function (e) {
        e.preventDefault();

        // Validate required fields before processing
        var layoutName = $("#layout_name").val();
        var layoutTemplate = $("#layout_template").val();

        if (!layoutName || !layoutTemplate) {
            alert('Please fill in all required fields (Layout Name and Template).');
            return;
        }

        // Check if nonce exists
        var nonce = $('input[name="shopg_cartpage_layouts_nonce"]').val();

        $(".loader-overlay").css({"display": "flex", "opacity": "1"});
        $(".loader-container").show();

        var postid = $("#shopg_shop_layoutid").val();

        var shopgCartSettings = {};

        function setNestedProperty(obj, keys, value) {
            var lastKey = keys.pop();
            var nestedObj = keys.reduce((o, key) => (o[key] = o[key] || {}), obj);
            nestedObj[lastKey] = value;
        }

        function setNestedArrayProperty(obj, keys, value) {
            var lastKey = keys.pop();
            var nestedObj = keys.reduce((o, key) => (o[key] = o[key] || {}), obj);

            if (!nestedObj[lastKey]) {
                nestedObj[lastKey] = [];
            }

            if (Array.isArray(nestedObj[lastKey]) && !nestedObj[lastKey].includes(value)) {
                nestedObj[lastKey].push(value);
            }
        }

        // Process form inputs
        $("#shopg-cart-layout-settings :input, #shopg-cart-layout-settings select").each(function () {
            var input = $(this);
            var name = input.attr("name");
            var value;

            // Skip invalid fields and WordPress internal fields
            if (!name ||
                name.startsWith('___shopg_cartpage_settings') ||
                name === '_pseudo' ||
                name.startsWith('agl_metabox_nonce') ||
                name.startsWith('_wp_') ||
                name === 'shopg_cartpage_layouts_nonce' ||
                name === 'shopg_shop_layoutid' ||
                name === 'layout_name' ||
                name === 'layout_template' ||
                name === 'action' ||
                name.startsWith('_ajax_') ||
                name.startsWith('closedpostboxesnonce') ||
                name.startsWith('meta-box-order-nonce')) {
                return;
            }

            // Handle different input types
            if (input.is("select")) {
                if (input.prop('multiple')) {
                    var selectedValues = input.val();
                    if (selectedValues && selectedValues.length > 0) {
                        var keys = name.replace('[]', '').split("[").map((k) => k.replace("]", ""));
                        var lastKey = keys.pop();
                        var nestedObj = keys.reduce((o, key) => (o[key] = o[key] || {}), shopgCartSettings);
                        nestedObj[lastKey] = selectedValues;
                    }
                    return;
                } else {
                    value = input.val();
                }
            }
            else if (input.is(":checkbox")) {
                if (input.is(":checked")) {
                    value = input.val();

                    if (name.endsWith('[]')) {
                        var keys = name.replace('[]', '').split("[").map((k) => k.replace("]", ""));
                        setNestedArrayProperty(shopgCartSettings, keys, value);
                        return;
                    }
                } else {
                    return;
                }
            }
            else if (input.is(":radio")) {
                if (input.is(":checked")) {
                    value = input.val();
                } else {
                    return;
                }
            }
            else {
                value = input.val();
            }

            if (name && !name.endsWith('[]')) {
                var keys = name.split("[").map((k) => k.replace("]", ""));
                setNestedProperty(shopgCartSettings, keys, value);
            }
        });


        // AJAX request with improved error handling
        $.ajax({
            type: "POST",
            url: shopglut_admin_vars.ajax_url || shopglut_admin_vars.ajaxurl || ajaxurl,
            data: {
                action: 'save_shopg_cartlayoutdata',
                shopg_cartpage_layouts_nonce: nonce,
                shopg_cart_layoutid: postid,
                layout_name: layoutName,
                layout_template: layoutTemplate,
                shopg_cartpage_settings_template1: JSON.stringify(shopgCartSettings)
            },
            timeout: 30000, // 30 second timeout
            success: function (response) {
                // Always hide loader first
                $(".loader-overlay").css({"display": "none", "opacity": "0"});
                $(".loader-container").hide();

                console.log('Cart save response:', response);
                console.log('Response success:', response.success);
                console.log('Response data:', response.data);

                if (response.success) {
                    // Check if server requested a page reload
                    if (response.data && response.data.reload) {
                        // Reload the page instantly
                        window.location.reload();
                    } else {
                        // Show success message - cart layout doesn't need reload
                        showNotification('Layout saved successfully!', 'success');
                    }
                } else {
                    // Handle different error response formats
                    var errorMsg = 'Unknown error occurred';
                    if (typeof response.data === 'string') {
                        errorMsg = response.data;
                    } else if (response.data && response.data.message) {
                        errorMsg = response.data.message;
                    } else if (response.message) {
                        errorMsg = response.message;
                    }
                    console.error("Failed to save data:", errorMsg);
                    console.error("Full response:", response);
                    showNotification('Failed to save: ' + errorMsg, 'error');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $(".loader-overlay").css({"display": "none", "opacity": "0"});
                $(".loader-container").hide();

                var errorMsg = 'AJAX request failed';
                if (textStatus === 'timeout') {
                    errorMsg = 'Request timed out. Please try again.';
                } else if (jqXHR.status === 403) {
                    errorMsg = 'Access denied. Please refresh the page and try again.';
                } else if (jqXHR.status === 500) {
                    errorMsg = 'Server error. Please check server logs.';
                }


                showNotification(errorMsg, 'error');
            }
        });
    });


    // Utility function for notifications - uses centralized ShopGlutNotification utility
    function showNotification(message, type) {
        if (typeof ShopGlutNotification !== 'undefined') {
            ShopGlutNotification.show(message, type, { duration: 5000 });
        } else {
            // Fallback if centralized utility not loaded
            $('.shopglut-notification').remove();
            var notification = $('<div class="shopglut-notification shopglut-notification-' + type + '">' +
                               '<span>' + message + '</span>' +
                               '<button class="notification-close">×</button>' +
                               '</div>');
            $('body').append(notification);
            setTimeout(function() {
                notification.fadeOut(300, function() { $(this).remove(); });
            }, 5000);
            notification.find('.notification-close').on('click', function() {
                notification.fadeOut(300, function() { $(this).remove(); });
            });
        }
    }
});
