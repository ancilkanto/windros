<?php
defined( 'WINDROS_INIT' ) || exit;      // Exit if accessed directly.

if ( ! class_exists( 'Windrose_Subscription_Details_Template' ) ) {
    class Windrose_Subscription_Details_Template {

		
		public function subscription_details($subscription_id) {

			echo '<h2 class="woocommerce-order-details__title">' . __( 'Subscription Details', 'windros-subscription' ) . '</h2>';
            

			wp_enqueue_style('windrose-myaccount', WINDROS_URL .'assets/stylesheets/my-account.css'); 

			$customer = wp_get_current_user();

			global $wpdb;

			// Define your custom table name (considering WordPress table prefix)
			$subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;

			// Query to get all rows from the custom table
			// Prepare and execute the query to retrieve a single subscription
            $subscription = $wpdb->get_row( 
                $wpdb->prepare( "SELECT * FROM $subscription_table WHERE id = %d", $subscription_id ), 
                ARRAY_A // Return the result as an associative array
            );

            if ( $subscription ) {
                if($subscription['user_id'] == $customer->ID){
                    $product = wc_get_product( $subscription['product_id'] );
                    $product_title = $product->get_name();
                    // Get product thumbnail (this returns the URL)
                    $product_thumbnail_url = get_the_post_thumbnail_url( $subscription['product_id'], 'full' );
                    // If no thumbnail exists, use WooCommerce placeholder image
                    if ( !$product_thumbnail_url ) {
                        $product_thumbnail_url = wc_placeholder_img_src(); // Placeholder image
                    }
                    // $subscription_schedule = $subscription->schedule;
                    $subscription_qty = $subscription['quantity'];
                    $subscription_price = $product->get_price() * $subscription_qty;
                    $formatted_price = wc_price( $subscription_price );
                    $product_price_html = $formatted_price . '&nbsp;' . __('for', 'windros-subscription') . '&nbsp;' . WINDROS_FREQUENCY[$subscription['schedule']];

                    ob_start();
                    ?>
                        <div class="subsc-detail-page">
                            <div class="subsc-prdt-detail">
                                <div class="sub-prdt-thumb-full">
                                    <img src="<?php echo esc_url($product_thumbnail_url);?>" alt="<?php echo esc_attr($product_title); ?>">
                                </div>
                                <div class="sub-prdt-details">
                                    <h3 class="woocommerce-order-details__title"><?php echo esc_html($product_title); ?></h3>
                                    <h5 class="sub-prdt-price-schedule"><?php echo $product_price_html; ?></h5>
                                    <p class="subscription-status <?php echo esc_attr($subscription['status']); ?>">
                                        <img src="<?php echo WINDROS_URL.'/assets/icons/'. esc_attr($subscription['status']) .'.svg'; ?>" alt="<?php echo esc_attr($subscription['status']); ?>">
                                        <?php
                                            echo WINDROS_SUBSCRIPTION_STATUS[$subscription['status']];
                                        ?>
                                    </p>
                                </div>
                            </div>
                            <div class="subscription-actions">
                                <a href="#<?php echo esc_url( $subscription['id'] ); ?>" class="woocommerce-button edit-subscription"
                                aria-label="Edit subscription <?php echo esc_attr( $subscription['id'] );?>"><?php _e('Edit Subscription', 'windros-subscription' ); ?></a>
                                
                                <a href="#<?php echo esc_url( $subscription['id'] ); ?>" class="woocommerce-button pause-subscription"
                                aria-label="Pause subscription <?php echo esc_attr( $subscription['id'] );?>"><?php _e('Pause Subscription', 'windros-subscription' ); ?></a>
                                
                                <a href="#<?php echo esc_url( $subscription['id'] ); ?>" class="woocommerce-button cancel-subscription"
                                aria-label="Cancel subscription <?php echo esc_attr( $subscription['id'] );?>"><?php _e('Cancel Subscription', 'windros-subscription' ); ?></a>
                            </div>

                            <div class="upcoming-delivery">
                                <h3 class="woocommerce-order-details__title"><?php echo __('Upcoming', 'windros-subscription'); ?></h3>
                                <div class="upcoming-wrapper">
                                    <div class="upcoming-inner">
                                        <div class="icon-wrapper">
                                            <img src="<?php echo WINDROS_URL.'/assets/icons/calendar_month.svg'; ?>" alt="Calendar">
                                        </div>
                                        <div class="upcoming-details">
                                            <h5 class="woocommerce-order-details__title">4th Delivery</h5>
                                            <p class="delivery-date">October 18, 2024</p>
                                        </div>
                                    </div>
                                    <div class="upcomming-actions">
                                        <a href="#" class="woocommerce-button skip-delivery"
                                        aria-label="Skip subscription delivery"><?php _e('Skip Delivery', 'windros-subscription' ); ?></a>
                                    </div>
                                </div>
                            </div>

                            <div class="past-deliveries">
                                <h3 class="woocommerce-order-details__title"><?php echo __('Previous Deliveries', 'windros-subscription'); ?></h3>
                                <ul class="past-delivery-list">
                                    <li>
                                        <h5 class="woocommerce-order-details__title">3rd Delivery</h5>
                                        <p class="delivery-date">October 18, 2024</p>
                                    </li>
                                    <li>
                                        <h5 class="woocommerce-order-details__title">2nd Delivery</h5>
                                        <p class="delivery-date">October 18, 2024</p>
                                    </li>
                                    <li>
                                        <h5 class="woocommerce-order-details__title">1st Delivery</h5>
                                        <p class="delivery-date">October 18, 2024</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    <?php

                    echo ob_get_clean();
                    
                }else{
                    // The subscription is not subscribed by the current user.
                    echo __('Illegal Access!', 'windros-subscription');
                }
                
            } else {
                echo 'No subscription found.';
            }
			
			

			
			
		}
	}
}

?>
