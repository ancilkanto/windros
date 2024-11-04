<?php
defined( 'WINDROS_INIT' ) || exit;  



if ( ! class_exists( 'Windros_Skip_Subscription' ) ) {
    class Windros_Skip_Subscription {
        public function __construct() {
            add_action( 'wp_ajax_windrose_skip_subscription', [$this, 'skip_subscription'] );
            add_action( 'wp_ajax_nopriv_windrose_skip_subscription', [$this, 'skip_subscription'] );
        }

        public function skip_subscription() {
            // Verify the nonce for security
            if (!isset($_POST['skip_subscription_nonce']) || !wp_verify_nonce($_POST['skip_subscription_nonce'], 'skip_subscription_action')) {
                wp_send_json_error(__('Invalid submission.', 'windros-subscription'));
                wp_die();
            }

            // Nonce is valid, process form data            
            
            $response = $this->skip_subscription_action(intval($_POST['subscription_id']), 'ajax');

            
            // Return the user ID in the AJAX response
            wp_send_json_success($response);

            wp_die(); // Required to end the AJAX request
        }

        public function skip_subscription_action($subscription_id, $action) {
            $response = array();
            $user_condition = '';
            $admin_action = false;

            if($action === 'ajax'){
                $current_user_id = get_current_user_id();
                $user_condition = sprintf('AND user_id = %s', $current_user_id);
                
            }

            if($action === 'admin'){
                if (current_user_can('administrator') || current_user_can('shop_manager')) {
                    $admin_action = true;
                }
            }

            global $wpdb;

            // Define your custom table name (considering WordPress table prefix)
            $subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;
            $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;

            // Query to get all rows from the custom table
            // Prepare and execute the query to retrieve a single subscription
            $subscription = $wpdb->get_row( 
                $wpdb->prepare( "SELECT * FROM $subscription_table WHERE id = %d $user_condition", $subscription_id )
            );


            $response = array();

            if ( $subscription || $admin_action ) {

                    
                $upcoming_order_data = $wpdb->get_row( 
                    $wpdb->prepare( "SELECT id, time_stamp FROM $subscription_order_table WHERE subscription_id = %d AND status = 'upcoming'", $subscription_id )
                );

                $order_skip_data = array(
                    'status' => 'skipped'
                );

                $order_skip_condition = array(
                    'id' => $upcoming_order_data->id
                );

                $order_skip_format = array('%s');
                $order_skip_where_format = array('%d');

                $order_updated = $wpdb->update( $subscription_order_table, $order_skip_data, $order_skip_condition, $order_skip_format, $order_skip_where_format );

                $created_new_order = $this->create_alternate_subscription_order($subscription, $upcoming_order_data);

                if($created_new_order){

                    $data = array(
                        'total_orders' => intval($subscription->total_orders)+1
                    );
        
                    $condition = array(
                        'id' => $subscription_id,
                    );
        
                    $format = array('%d');  
                    $where_format = array('%d'); 

                    $updated = $wpdb->update( $subscription_table, $data, $condition, $format, $where_format );

                    do_action('windrose_subscription_order_skipped', $upcoming_order_data->id);
                    
                    if($action != 'admin'){
                        wc_add_notice( __('The subscription order has been skipped!', 'windros-subscription'), 'success');
                    }
                    
                    $response['status'] = 'success';
                    $response['message'] = __('The subscription order has been skipped!', 'windros-subscription');
                }else{
                    $response['status'] = 'error';
                    $response['message'] = __('Subscription Order Not Skipped!', 'windros-subscription');
                    if($action != 'admin'){
                        wc_add_notice( __('Subscription Order Not Skipped!', 'windros-subscription'), 'error');
                    }
                }
                    
                    
                    
                
            }else{
                $response['status'] = 'error';
                $response['message'] = __('Unauthorized Request!', 'windros-subscription');
                if($action != 'admin'){
                    wc_add_notice( __('Unauthorized Request!', 'windros-subscription'), 'error');
                }
            }

            return $response;
        }


        public function create_alternate_subscription_order($subscription, $skipped_order_data) {
            global $wpdb;            
            $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;

            $sequence = intval($subscription->total_orders) + 1;

            // Get the timezone setting from WordPress
            $timezone = get_option('timezone_string');  // e.g., 'America/New_York'

            // If 'timezone_string' is empty, fall back to 'gmt_offset'
            if (empty($timezone)) {
                $gmt_offset = get_option('gmt_offset'); // e.g., -5 for UTC-5
                $timezone = sprintf('Etc/GMT%+d', $gmt_offset); // e.g., 'Etc/GMT-5'
            }

            // Set the PHP timezone to match WordPress
            date_default_timezone_set($timezone);

            $date = date("Y-m-d H:i:s");

            $skipped_timestamp = $skipped_order_data->time_stamp;
            $skipped_date = date('Y-m-d H:i:s', $skipped_timestamp);

            $timestamp = strtotime($skipped_date . ' +'.esc_html($subscription->schedule).' days');

            return $wpdb->insert($subscription_order_table, array(
                'subscription_id' => $subscription->id,
                'main_order_id' => $subscription->order_id,
                'user_id' => $subscription->user_id, 
                'product_id' => $subscription->product_id, 
                'quantity' => $subscription->quantity, 
                'payment_token' => $subscription->payment_token, 
                'attempts' => 0, 
                'status' => 'upcoming', 
                'sequence' => $sequence,
                'time_stamp' => $timestamp, 
                'created_at' => $date, 
            ));

        }
    }
}

new Windros_Skip_Subscription();