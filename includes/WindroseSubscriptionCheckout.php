<?php
namespace WindroseSubscription\Includes; 

defined( 'WINDROS_INIT' ) || exit;  

class WindroseSubscriptionCheckout {
    public function __construct() {
        // Save the custom field value to the order
        add_action( 'woocommerce_checkout_create_order_line_item', [$this, 'save_subscription_schedule_order_meta'], 100, 4 );
    }


    public function save_subscription_schedule_order_meta( $item, $cart_item_key, $values, $order ) {

        
        if ( ! empty( $values['subscription-schedule'] ) ) {
            
            $item->update_meta_data('Subscription Schedule', WINDROS_FREQUENCY[$values['subscription-schedule']]);
            $order->update_meta_data( '_subscription_main_order', 'yes' );
            $order->update_meta_data( '_subscription_product', $item->get_product()->get_id() );
            $order->update_meta_data( '_subscription_schedule', $values['subscription-schedule'] );

            $order_id = $order->save();
            do_action('windrose_subscription_main_order_created', $order_id, $item->get_product()->get_id(), $values['subscription-schedule'], $values['quantity']);
            
        }
    }
}