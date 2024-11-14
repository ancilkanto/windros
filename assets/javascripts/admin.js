(function($) {
    $(window).on('load', function(){
        
        $('#_enable_subscription').on('change', function(){
            if($(this).is(":checked")){
                $('.subscription-fields').css('display', 'block');
                
            }else{
                $('.subscription-fields').css('display', 'none');
            }
        });




        setTimeout(function(){
            $(".chosen-wraper select").chosen();
        },1000);


        $('.hide-fields-on-load').css('display', 'none');


        $(document).on('click', '.wc-block-components-quantity-selector__button--plus', function(e){   
            e.preventDefault();                         
            var currentQty = parseInt($('.wc-block-components-quantity-selector__input').val());
            $('.wc-block-components-quantity-selector__input').val(currentQty+1);
        });
        $(document).on('click', '.wc-block-components-quantity-selector__button--minus', function(e){ 
            e.preventDefault();                           
            var currentQty = parseInt($('.wc-block-components-quantity-selector__input').val());
            if(currentQty > 1){
                $('.wc-block-components-quantity-selector__input').val(currentQty-1);
            }
        });

        $(document).on('click', '#edit-subscription', function(e){
            e.preventDefault(); 
            $('.edit-subscription-wrapper').slideToggle('fast');
        });
        
    });
    
})(jQuery);