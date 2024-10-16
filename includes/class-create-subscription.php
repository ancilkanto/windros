<?php
defined( 'WINDROS_INIT' ) || exit;  



if ( ! class_exists( 'Windros_Create_Subscription' ) ) {
    class Windros_Create_Subscription {
        public function __construct() {
            // Save the custom field value to the order
            add_action( 'windrose_on_subscription_created', [$this, 'initiate_subscription'], 10, 3 );
        }


        public function initiate_subscription( $order, $product_id, $subscription_schedule ) {

            
        }
    }
}

new Windros_Create_Subscription();