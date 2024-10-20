<?php
defined( 'WINDROS_INIT' ) || exit;  



if ( ! class_exists( 'Windros_Create_Subscription' ) ) {
    class Windros_Create_Subscription {
        public function __construct() {
            // Save the custom field value to the order
            add_action( 'windrose_on_subscription_created', [$this, 'initiate_subscription'], 10, 4 );

            // Hook into WooCommerce after the order meta is updated (after the order is created)
            add_action('woocommerce_thankyou', [$this, 'update_subscription_order_data_after_payment'], 10, 1);
        }


        public function initiate_subscription( $order_id, $product_id, $subscription_schedule, $quantity ) {
            // $order_id = $order->get_id();
            $order = wc_get_order($order_id);
            $customer_id = $order->get_user_id(); 
            $status = 'processing';

            // Get the timezone setting from WordPress
            $timezone = get_option('timezone_string');  // e.g., 'America/New_York'

            // If 'timezone_string' is empty, fall back to 'gmt_offset'
            if (empty($timezone)) {
                $gmt_offset = get_option('gmt_offset'); // e.g., -5 for UTC-5
                $timezone = sprintf('Etc/GMT%+d', $gmt_offset); // e.g., 'Etc/GMT-5'
            }

            // Set the PHP timezone to match WordPress
            date_default_timezone_set($timezone);


            global $wpdb;
            $subscription_main_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;
            

            $wpdb->insert($subscription_main_table, array(
                'order_id' => $order_id,
                'product_id' => $product_id,
                'user_id' => $customer_id, 
                'payment_token' => '', 
                'schedule' => $subscription_schedule, 
                'quantity' => $quantity, 
                'status' => $status, 
                'time_stamp' => date("Y-m-d H:i:s"), 
            ));
            
        }

        

        public function update_subscription_order_data_after_payment($order_id) {

            // Get the order object using the order ID
            $order = wc_get_order($order_id);
            // Get the user/customer ID (returns 0 for guest checkouts)
            $user_id = $order->get_user_id();

            global $wpdb;
            $subscription_main_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;
            
            $data = array(
                'user_id' => $user_id,
                'payment_token' => 'PAYMENT-TOKEN-HERE', 
            );

            $where = array(
                'order_id' => $order_id,
            );

            $format = array('%s');  
            $where_format = array('%s');  

            // Execute the update query
            $updated = $wpdb->update( $subscription_main_table, $data, $where, $format, $where_format );

        }
    }
}

new Windros_Create_Subscription();