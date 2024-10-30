jQuery(function($) {
                        // Trigger when Add to Cart button is clicked
                        
                        
        $(document).on('click', '.edit-subscription', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            $('.windrose-backdrop').addClass('visible');
            $('.update-subscription-wrapper').addClass('visible');
        });

        
        $(document).on('click', '.pause-subscription', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            $('.windrose-backdrop').addClass('visible');
            $('.pause-subscription-wrapper').addClass('visible');
        });

        $(document).on('click', '.cancel-subscription', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            $('.windrose-backdrop').addClass('visible');
            $('.cancel-subscription-wrapper').addClass('visible');
        });

        $(document).on('click', '.windrose-backdrop', function(){
            $(this).removeClass('visible');
            $('.windrose-foredrop').removeClass('visible');
        });
        $(document).on('click', '.windrose-close-popup', function(){
            $('.windrose-backdrop, .windrose-foredrop').removeClass('visible');                            
        });

        $(document).on('click', '.wc-block-components-quantity-selector__button--plus', function(){                            
            var currentQty = parseInt($('.wc-block-components-quantity-selector__input').val());
            $('.wc-block-components-quantity-selector__input').val(currentQty+1);
        });
        $(document).on('click', '.wc-block-components-quantity-selector__button--minus', function(){                            
            var currentQty = parseInt($('.wc-block-components-quantity-selector__input').val());
            if(currentQty > 1){
                $('.wc-block-components-quantity-selector__input').val(currentQty-1);
            }
        });

        $(document).on('click', '.confirm-edit-subscription', function(e) {
            e.preventDefault();
                                    
            let ajaxData = {
                action: 'windrose_update_subscription',
                subscription_id: parseInt($(this).attr('data-subscription-id')),
                update_subscription_nonce: $('#update_subscription_nonce').val(),
                quantity: parseInt($('.wc-block-components-quantity-selector__input').val()),
                schedule: parseInt($('#subscription-schedule').val()),
                modifyUpcoming: $('#modify-upcoming-on-update').prop('checked') ? true : false
            };
            
            

            // AJAX call to update subscription
            $.ajax({
                type: 'POST',
                url: wc_add_to_cart_params.ajax_url, // WooCommerce AJAX URL
                data: ajaxData,
                beforeSend: function(response) {
                    $('.windrose-foredrop').addClass('loading');
                },
                success: function(response) {
                    location.reload();
                    
                    // if(response.data.status === 'success'){
                    //     location.reload();
                    // }else{
                    //     console.log(response.data.message);
                        
                    // }
                }
            });
        });
    });