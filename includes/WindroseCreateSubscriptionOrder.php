<?php
namespace WindroseSubscription\Includes;

defined( 'WINDROS_INIT' ) || exit;  

class WindroseCreateSubscriptionOrder {
    public function __construct() {
        add_action( 'windrose_subscription_main_order_activated', [$this, 'create_subscription_order'], 10, 1 );
        add_action( 'windrose_subscription_order_executed_successfully', [$this, 'create_subscription_order'], 10, 1 );
    }

    public function create_subscription_order($subscription_id){
        global $wpdb;

        
        $subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;
        $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;

        // Query to get all rows from the custom table
        // Prepare and execute the query to retrieve a single subscription
        $subscription = $wpdb->get_row( 
            $wpdb->prepare( "SELECT * FROM $subscription_table WHERE id = %d AND status = 'active'", $subscription_id ), 
            ARRAY_A // Return the result as an associative array
        );

        $schedule = $subscription['schedule'];

        // // Get the timezone setting from WordPress
        // $timezone = get_option('timezone_string');  // e.g., 'America/New_York'

        // // If 'timezone_string' is empty, fall back to 'gmt_offset'
        // if (empty($timezone)) {
        //     $gmt_offset = get_option('gmt_offset'); // e.g., -5 for UTC-5
        //     $timezone = sprintf('Etc/GMT%+d', $gmt_offset); // e.g., 'Etc/GMT-5'
        // }

        // // Set the PHP timezone to match WordPress
        // date_default_timezone_set($timezone);

        // $date = date("Y-m-d H:i:s");

        $timestamp_obj = windrose_get_timestamp_object($schedule);

        $sequence = $subscription['total_orders'] + 1;

        if ( !empty($subscription) ) {
            $wpdb->insert($subscription_order_table, array(
                'subscription_id' => $subscription_id,
                'main_order_id' => $subscription['order_id'],
                'user_id' => $subscription['user_id'], 
                'product_id' => $subscription['product_id'], 
                'quantity' => $subscription['quantity'], 
                'payment_token' => $subscription['payment_token'], 
                'attempts' => 0, 
                'status' => 'upcoming', 
                'sequence' => $sequence,
                'time_stamp' => $timestamp_obj->timestamp, 
                'created_at' => $timestamp_obj->date, 
            ));


            $update_data = array(
                'total_orders' => $sequence, 
            );

            $where = array(
                'id' => $subscription_id,
            );

            $format = array('%d');  
            $where_format = array('%d');  

            // Execute the update query
            $subscription_main_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;
            $wpdb->update( $subscription_main_table, $update_data, $where, $format, $where_format );
        }


    }
}
