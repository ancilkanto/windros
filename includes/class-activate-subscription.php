<?php
defined( 'WINDROS_INIT' ) || exit;  



if ( ! class_exists( 'Windros_Activate_Subscription' ) ) {
    class Windros_Activate_Subscription {
        public function __construct() {
            // Save the custom field value to the order
            // add_action( 'woocommerce_order_status_completed', [$this, 'activate_subscription'], 10, 1 );

            add_action('woocommerce_order_action_activate_subscription', [$this, 'activate_subscription'], 10, 1 );

            // Add custom action to order actions dropdown in the order edit page
            add_filter('woocommerce_order_actions', [$this, 'add_subscription_order_action']);
            
        }

        public function activate_subscription($order) {
            error_log( 'Activating Subscription' );
            // Get the order object
            // $order = wc_get_order($order_id);
            $order_id = $order->get_id();
        
            // Perform actions when the order is completed
            global $wpdb;


            // Get the timezone setting from WordPress
            $timezone = get_option('timezone_string');  // e.g., 'America/New_York'

            // If 'timezone_string' is empty, fall back to 'gmt_offset'
            if (empty($timezone)) {
                $gmt_offset = get_option('gmt_offset'); // e.g., -5 for UTC-5
                $timezone = sprintf('Etc/GMT%+d', $gmt_offset); // e.g., 'Etc/GMT-5'
            }

            // Set the PHP timezone to match WordPress
            date_default_timezone_set($timezone);

			// Define your custom table name (considering WordPress table prefix)
			$subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;

			// Query to get all rows from the custom table
			$subscription_query = $wpdb->prepare( "SELECT id FROM $subscription_table WHERE order_id = %d", $order_id );
			$subscription_list = $wpdb->get_results( $subscription_query );

            $subscription_ids = array();
            foreach($subscription_list as $subscription_item){
                $subscription_ids[] = $subscription_item->id;
            }

            error_log( 'Subscription List: ' . json_encode($subscription_ids) );
			
			if ( !empty($subscription_ids) ) {
                
                $status = 'active';

                // Generate placeholders for each ID to use in a prepared statement
                $placeholders = implode(',', array_fill(0, count($subscription_ids), '%d'));
                $date = date("Y-m-d H:i:s");

                $update_status_query = "
                    UPDATE $subscription_table 
                    SET status = %s, active_date = '$date'
                    WHERE id IN ($placeholders)
                ";

                // Merge the status and IDs for the prepared statement
                $params = array_merge([$status], $subscription_ids);

                // Execute the query
                $wpdb->query($wpdb->prepare($update_status_query, ...$params));                                
                
                // Hook to invoke on 
                foreach($subscription_ids as $subscription_id){                    
                    do_action('windrose_subscription_main_order_activated', $subscription_id);
                }
            }
        }

        public function add_subscription_order_action($actions) {
            $order_id = $_GET['id'];           
            $order = wc_get_order($order_id);
            $is_subscription_main_order = $order->get_meta('_subscription_main_order');
            if($is_subscription_main_order == 'yes'){
                $actions['activate_subscription'] = __('Activate Subscription', 'windros-subscription');
            }

            return $actions;
        }
    }
}

new Windros_Activate_Subscription();


// function handle_custom_order_action($order) {
//     // Get order ID
//     $order_id = $order->get_id();

//     // Add a custom order note (or other custom actions)
//     $order->add_order_note('Custom email sent to customer.', true);

//     // Example: Send an email to the customer
//     $to = $order->get_billing_email();
//     $subject = 'Important Update on Your Order';
//     $message = 'Hello, we wanted to let you know about an update regarding your order #' . $order_id;
//     wp_mail($to, $subject, $message);
// }