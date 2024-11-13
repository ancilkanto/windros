<?php
namespace WindroseSubscription\Includes;

defined( 'WINDROS_INIT' ) || exit;  




class WindroseUpdateSubscription {
    public function __construct() {
        add_action( 'wp_ajax_windrose_update_subscription', [$this, 'update_subscription'] );
        add_action( 'wp_ajax_nopriv_windrose_update_subscription', [$this, 'update_subscription'] );
    }


    public function update_subscription() {
        // Verify the nonce for security
        if (!isset($_POST['update_subscription_nonce']) || !wp_verify_nonce($_POST['update_subscription_nonce'], 'update_subscription_action')) {
            wp_send_json_error(__('Invalid submission.', 'windros-subscription'));
            wp_die();
        }

        // Nonce is valid, process form data            
        $subscription_id = intval($_POST['subscription_id']);
        $quantity = intval($_POST['quantity']);
        $schedule = intval($_POST['schedule']);
        $modify_upcoming = sanitize_text_field($_POST['modifyUpcoming']);

        $current_user_id = get_current_user_id();

        global $wpdb;

        // Define your custom table name (considering WordPress table prefix)
        $subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;
        $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;

        // Query to get all rows from the custom table
        // Prepare and execute the query to retrieve a single subscription
        $subscription = $wpdb->get_row( 
            $wpdb->prepare( "SELECT * FROM $subscription_table WHERE id = %d AND user_id = %s", $subscription_id, $current_user_id )
        );
        

        $response = array();


        if ( $subscription ) {
            $update_data = array(
                'quantity' => $quantity,
                'schedule' => $schedule, 
            );

            $update_condition = array(
                'id' => $subscription_id,
            );

            $format = array('%d');  
            $where_format = array('%d');  

            // Execute the update query
            $updated = $wpdb->update( $subscription_table, $update_data, $update_condition, $format, $where_format );

            if($updated){
                if($modify_upcoming == 'true'){
                    $order_update_data = array(
                        'quantity' => $quantity
                    );
        
                    $order_update_condition = array(
                        'subscription_id' => $subscription_id,
                        'status' => 'upcoming'
                    );
        
                    $order_update_format = array('%d');  
                    $order_update_where_format = array('%d', '%s');  
        
                    // Execute the update query
                    $order_updated = $wpdb->update( $subscription_order_table, $order_update_data, $order_update_condition, $order_update_format, $order_update_where_format );
                    
                    if($order_updated){

                        wc_add_notice( __('Subscription and Upcoming Order Updated Successfully!', 'windros-subscription'), 'success');
                    
                        $response['status'] = 'success';
                        $response['message'] = __('Subscription Updated Successfully!', 'windros-subscription');
                        $upcoming_order_data = $wpdb->get_row( 
                            $wpdb->prepare( "SELECT id FROM $subscription_order_table WHERE subscription_id = %d AND status = 'upcoming'", $subscription_id )
                        );

                        do_action('windrose_subscription_order_updated', $upcoming_order_data->id);

                        do_action('windrose_subscription_main_order_updated', $subscription_id);
                    }else{
                        $response['status'] = 'error';
                        $response['message'] = __('Subscription Not Updated!', 'windros-subscription');
                        wc_add_notice( __('Subscription Not Updated!', 'windros-subscription'), 'error');
                        
                    }
                    
                }else{
                    wc_add_notice( __('Subscription Updated Successfully!', 'windros-subscription'), 'success');
                
                    $response['status'] = 'success';
                    $response['message'] = __('Subscription Updated Successfully!', 'windros-subscription');
                    
                    do_action('windrose_subscription_main_order_updated', $subscription_id);
                }
                
            }else{
                $response['status'] = 'error';
                $response['message'] = __('Subscription Not Updated!', 'windros-subscription');
                wc_add_notice( __('Subscription Not Updated!', 'windros-subscription'), 'error');
            }

            

        }else{
            $response['status'] = 'error';
            $response['message'] = __('Unauthorized Request!', 'windros-subscription');
            wc_add_notice( __('Unauthorized Request!', 'windros-subscription'), 'error');
        }



        // Return the user ID in the AJAX response
        wp_send_json_success($response);

        wp_die(); // Required to end the AJAX request
        
    }
}