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
        
        $(document).on('click', '.skip-delivery', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            $('.windrose-backdrop').addClass('visible');
            $('.skip-subscription-wrapper').addClass('visible');
        });

        $(document).on('click', '.windrose-backdrop', function(){
            $(this).removeClass('visible');
            $('.windrose-foredrop').removeClass('visible');
        });
        $(document).on('click', '.windrose-close-popup', function(e){
            e.preventDefault();
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
                url: woocommerce_params.ajax_url, // WooCommerce AJAX URL
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

        $(document).on('click', '.confirm-pause-subscription', function(e) {
            e.preventDefault();
                                    
            let ajaxData = {
                action: 'windrose_pause_subscription',
                pause_subscription_nonce: $('#pause_subscription_nonce').val(),
                subscription_id: parseInt($(this).attr('data-subscription-id'))                
            };
                        
            // AJAX call to pause subscription
            $.ajax({
                type: 'POST',
                url: woocommerce_params.ajax_url, // WooCommerce AJAX URL
                data: ajaxData,
                beforeSend: function(response) {
                    $('.windrose-foredrop').addClass('loading');
                },
                success: function(response) {
                    location.reload();                                        
                }
            });
        });
        
        $(document).on('click', '.confirm-cancel-subscription', function(e) {
            e.preventDefault();
                                    
            let ajaxData = {
                action: 'windrose_cancel_subscription',
                cancel_subscription_nonce: $('#cancel_subscription_nonce').val(),
                subscription_id: parseInt($(this).attr('data-subscription-id'))                
            };
                        
            // AJAX call to cancel subscription
            $.ajax({
                type: 'POST',
                url: woocommerce_params.ajax_url, // WooCommerce AJAX URL
                data: ajaxData,
                beforeSend: function(response) {
                    $('.windrose-foredrop').addClass('loading');
                },
                success: function(response) {
                    location.reload();                                        
                }
            });
        });
        
        $(document).on('click', '.confirm-skip-subscription', function(e) {
            e.preventDefault();
                                    
            let ajaxData = {
                action: 'windrose_skip_subscription',
                skip_subscription_nonce: $('#skip_subscription_nonce').val(),
                subscription_id: parseInt($(this).attr('data-subscription-id'))                
            };
                        
            // AJAX call to skip subscription
            $.ajax({
                type: 'POST',
                url: woocommerce_params.ajax_url, // WooCommerce AJAX URL
                data: ajaxData,
                beforeSend: function(response) {
                    $('.windrose-foredrop').addClass('loading');
                },
                success: function(response) {
                    location.reload();                                        
                }
            });
        });

        $(document).on('click', '.reactivate-subscription', function(e) {
            e.preventDefault();
            var _this = $(this);                                    
            let ajaxData = {
                action: 'windrose_reactivate_subscription',
                reactivate_subscription_nonce: $('#reactivate_subscription_nonce').val(),
                subscription_id: parseInt($(this).attr('data-subscription-id'))                
            };
                        
            // AJAX call to reactivate subscription
            $.ajax({
                type: 'POST',
                url: woocommerce_params.ajax_url, // WooCommerce AJAX URL
                data: ajaxData,
                beforeSend: function(response) {
                    _this.addClass('loading');
                },
                success: function(response) {
                    location.reload();                                        
                }
            });
        });
    });