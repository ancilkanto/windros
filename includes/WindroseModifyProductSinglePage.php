<?php
namespace WindroseSubscription\Includes; 

defined( 'WINDROS_INIT' ) || exit;  


class WindroseModifyProductSinglePage {
    public function __construct() {
        // Add the custom select field before the quantity input
        add_action( 'woocommerce_before_add_to_cart_button', [$this, 'add_subscription_schedule_field'] );

        

        

        // add_action( 'woocommerce_checkout_create_order', [$this, 'add_custom_meta_to_order'], 1, 2 );
    }

    public function add_subscription_schedule_field() {
        global $product;
    
        $enable_subscription_fields = false;
        $enable_subscription = get_post_meta( $product->get_id(), '_enable_subscription', true ); // Get the data - Checbox 1
        if( ! empty( $enable_subscription ) && $enable_subscription == 'yes' ){
            $enable_subscription_fields = true;
        }
        if(!$enable_subscription_fields){
            return;
        }
    
        $selected_fequencies = trim(get_post_meta( $product->get_id(), '_subscription_frequencies', true ));
        
    
        ob_start();
        ?>
    
        <div class="subscription-schedule-field">
            <label for="subscription-schedule">
                <?php
                    echo __('Choose Subscription Schedule', 'windros-subscription');
                ?>
            </label>
            <br>
            <select name="subscription-schedule" id="subscription-schedule">
                <?php
                if($selected_fequencies == ''){
                    $selected_fequencies = WINDROS_FREQUENCY;
                    foreach($selected_fequencies as $index => $frequency){
                        echo '<option value="'. $index .'">'. $frequency .'</option>';
                    }
                }else{
                    $selected_fequencies = explode(',', $selected_fequencies); 
                    foreach($selected_fequencies as $frequency){
                        echo '<option value="'.$frequency.'">'.WINDROS_FREQUENCY[$frequency].'</option>';
                    }
                }
                
                ?>
            </select>
            <p>
                <?php
                    echo __('You can adjust, pause or cancel at any time', 'windros-subscription');
                ?>
            </p>
        </div>
    
        <?php 
        echo ob_get_clean();
        
    }

    


    

    
    
}