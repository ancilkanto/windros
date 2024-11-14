<?php
namespace WindroseSubscription\Includes;
use WP_CLI;
use WP_CLI_Command;
use WC_Order_Item_Product;

defined( 'WINDROS_INIT' ) || exit;  


class WindroseCLI extends WP_CLI_Command {

    public function create_subscription_order($args, $associative_args){
        WP_CLI::log("Started Subscription Order Creation");

        global $wpdb;
        $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;

        $current_timestamp = windrose_get_timestamp_object(0)->timestamp;
        
        $upcoming_order_data = $wpdb->get_results( 
            $wpdb->prepare( "SELECT * FROM $subscription_order_table WHERE time_stamp <= $current_timestamp AND status = 'upcoming'" ),
            ARRAY_A
        );
        $task_status = array(
            'success' => array(),
            'error' => array()
        );
        if(!empty($upcoming_order_data)) {
            // Create order with the data
            $task_status = $this->create_WC_order($upcoming_order_data, $task_status);

            if(empty($task_status['error'])){
                $success_count = count($task_status['success']);
                WP_CLI::success($success_count . " Subscription Orders Created Successfully.");
            }else{
                WP_CLI::error("Subscription Orders Not Created.");
            }
        }else{
            WP_CLI::success("No subscriptions were found for today.");
        }
    }

    public function create_WC_order($subscription_order_data, $task_status){

        if(!empty($subscription_order_data)){

            // Extract first item from the array
            $subscription_order = (object) reset($subscription_order_data);

            WP_CLI::log("Creating order for subscription #".$subscription_order->subscription_id);

            $order = wc_create_order();

            
            $product_id = intval($subscription_order->product_id);
            $quantity = intval($subscription_order->quantity);     // Define the quantity
            $product = wc_get_product( $product_id );

            if ( $product ) {
                $item = new WC_Order_Item_Product();
                $item->set_product( $product );    // Set the product
                $item->set_quantity( $quantity );  // Set the quantity
                $item->set_subtotal( $product->get_price() * $quantity );   // Set the line item subtotal
                $item->set_total( $product->get_price() * $quantity );  // Set the line item total
                $order->add_item( $item );         // Add item to the order
            }

            // Step 3: Set order billing and shipping details
            $address = array(
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'company'    => 'Company Name',
                'email'      => 'john.doe@example.com',
                'phone'      => '123-456-7890',
                'address_1'  => '123 Main St',
                'address_2'  => '',
                'city'       => 'Anytown',
                'state'      => 'CA',
                'postcode'   => '12345',
                'country'    => 'US',
            );
            $order->set_address( $address, 'billing' );
            $order->set_address( $address, 'shipping' );

            // Step 4: Set payment method
            $order->set_payment_method('cod'); // Replace with your desired payment method ID
            $order->set_payment_method_title('Cash on delivery');

            // $order->update_meta_data( '_wc_order_attribution_utm_source', 'Subscription Automation' );
            
            // $order->set_status('processing'); // Set to "processing" or "completed" as required

            
            // Step 7: Save the order
            $order->save();
            
            // Step 6: Calculate and set totals
            $order->calculate_totals();

            $this->update_subscription_order_status($subscription_order->id, 'past');

            do_action('windrose_subscription_order_executed_successfully', $subscription_order->subscription_id);


            $task_status['success'][] = array(
                'order_id' => $order->get_id(),
                'subscription_id' => $subscription_order->id
            );

            WP_CLI::log("New order #".$order->get_id()." has been created for subscription #".$subscription_order->subscription_id);

            // Recursive function call after removing the first item from the array

            $this->create_WC_order(array_slice($subscription_order_data,1), $task_status);

        }
        
        
        return $task_status;

    }


    public function update_subscription_order_status($subscription_order_id, $status){
        global $wpdb;

        $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;

        $order_status_data = array(
            'status' => $status
        );

        $order_status_condition = array(
            'id' => $subscription_order_id
        );

        $order_status_format = array('%s');
        $order_status_where_format = array('%d');

        $order_updated = $wpdb->update( $subscription_order_table, $order_status_data, $order_status_condition, $order_status_format, $order_status_where_format );
    }

}

