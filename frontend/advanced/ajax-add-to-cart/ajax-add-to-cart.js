jQuery(document).ready(function() {
    jQuery('form.cart').on('submit', function(e) {
        e.preventDefault();

        if ( ! window.sppcfw_ajax_add_to_cart || ! sppcfw_ajax_add_to_cart.ajaxurl ) {
            return;
        }

        let form = jQuery(this);
        let submitBtn = form.find('button[type=submit], input[type=submit]').first();
        let item_input = submitBtn.attr('name');
        let item_value = submitBtn.attr('value');

        if ( typeof form.block === 'function' ) {
            form.block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });
        }

        if ( item_input && typeof item_value !== 'undefined' && item_value !== '' ) {
            if ( form.find('input[name="' + item_input + '"]').length === 0 ) {
                form.append(
                    jQuery("<input type='hidden'>").attr({
                        name: item_input,
                        value: item_value
                    })
                );
            }
        }

        form.find('input[name=action]').remove();
        form.append(
            jQuery("<input type='hidden'>").attr({
                name: 'action',
                value: 'sppcfw_ajax_add_to_cart'
            })
        );

        if ( sppcfw_ajax_add_to_cart.nonce ) {
            form.find('input[name=nonce]').remove();
            form.append(
                jQuery("<input type='hidden'>").attr({
                    name: 'nonce',
                    value: sppcfw_ajax_add_to_cart.nonce
                })
            );
        }

        let formData = form.serialize();
        let urlForm = sppcfw_ajax_add_to_cart.ajaxurl;

        jQuery.ajax({
            type: 'POST',
            url: urlForm,
            data: formData,
            success: function(response) {
                jQuery("body").trigger("update_checkout");
                jQuery(document.body).trigger('wc_fragment_refresh');

                if ( typeof form.unblock === 'function' ) {
                    form.unblock();
                }

                jQuery('.woocommerce-notices-wrapper').html('').html(response);
            },
            error: function() {
                if ( typeof form.unblock === 'function' ) {
                    form.unblock();
                }
                jQuery('.woocommerce-notices-wrapper').html('').html(
                    '<div class="woocommerce-error">Unable to add to cart. Please try again.</div>'
                );
            }
        });

    });
});