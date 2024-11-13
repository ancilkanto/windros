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
        
        $upcoming_order_data = $wpdb->get_row( 
            $wpdb->prepare( "SELECT * FROM $subscription_order_table WHERE subscription_id = 8 AND status = 'upcoming'" )
        );
        $order_id = $this->create_WC_order($upcoming_order_data);
        WP_CLI::success("Subscription Order #".$order_id." Created Successfully.");
        // WP_CLI::error("Subscription Orders Not Created.");
    }

    public function create_WC_order($subscription_order){

        
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

        

        

        // Output the order ID for reference
        // echo 'Order created with ID: ' . $order->get_id();
        return $order->get_id();

    }

}