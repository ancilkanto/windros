<?php
namespace WindroseSubscription\Includes;

defined( 'WINDROS_INIT' ) || exit;  

class WindroseCancelSubscription {
    public function __construct() {
        add_action( 'wp_ajax_windrose_cancel_subscription', [$this, 'cancel_subscription'] );
        add_action( 'wp_ajax_nopriv_windrose_cancel_subscription', [$this, 'cancel_subscription'] );
    }

    public function cancel_subscription() {
        // Verify the nonce for security
        if (!isset($_POST['cancel_subscription_nonce']) || !wp_verify_nonce($_POST['cancel_subscription_nonce'], 'cancel_subscription_action')) {
            wp_send_json_error(__('Invalid submission.', 'windros-subscription'));
            wp_die();
        }

        // Nonce is valid, process form data            
        $response = $this->cancel_subscription_action( intval($_POST['subscription_id']), 'ajax');
        
        // Return the user ID in the AJAX response
        wp_send_json_success($response);

        wp_die(); // Required to end the AJAX request
    }

    public function cancel_subscription_action($subscription_id, $action) {
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
        
        if ( $subscription || $admin_action ) {
            $data = array(
                'status' => 'cancel'
            );

            $condition = array(
                'id' => $subscription_id,
            );

            $format = array('%s');  
            $where_format = array('%d');  

            // Execute the update query
            $updated = $wpdb->update( $subscription_table, $data, $condition, $format, $where_format );

            if($updated){
                
                $upcoming_order_data = $wpdb->get_row( 
                    $wpdb->prepare( "SELECT id FROM $subscription_order_table WHERE subscription_id = %d AND status = 'upcoming'", $subscription_id )
                );

                $order_cancel_data = array(
                    'status' => 'cancelled'
                );

                $order_cancel_condition = array(
                    'id' => $upcoming_order_data->id
                );

                $order_cancel_format = array('%s');
                $order_cancel_where_format = array('%d');

                $order_updated = $wpdb->update( $subscription_order_table, $order_cancel_data, $order_cancel_condition, $order_cancel_format, $order_cancel_where_format );

                do_action('windrose_subscription_order_cancelled', $upcoming_order_data->id);
                do_action('windrose_subscription_order_cancelled_on_cancel', $upcoming_order_data->id);

                if($action != 'admin'){
                    wc_add_notice( __('The subscription has been cancelled!', 'windros-subscription'), 'success');
                }
                
                $response['status'] = 'success';
                $response['message'] = __('The subscription has been cancelled!', 'windros-subscription');
                
                do_action('windrose_subscription_main_order_cancelled', $subscription_id);
            }else{
                $response['status'] = 'error';
                $response['message'] = __('Subscription Not Canceld!', 'windros-subscription');
                if($action != 'admin'){
                    wc_add_notice( __('Subscription Not Canceld!', 'windros-subscription'), 'error');
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
}
