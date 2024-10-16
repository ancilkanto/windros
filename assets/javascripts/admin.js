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


        $('.hide-fields-on-load').css('display', 'none')
        
    });
    
})(jQuery);