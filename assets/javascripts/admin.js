(function($) {
    $(window).on('load', function(){

        $('#_enable_subscription').on('change', function(){
            if($(this).is(":checked")){
                $('.subscription-fields').css('display', 'block');
            }else{
                $('.subscription-fields').css('display', 'none');
            }
        });

        $(".chosen-wraper select").chosen();
        
    });
    
})(jQuery);