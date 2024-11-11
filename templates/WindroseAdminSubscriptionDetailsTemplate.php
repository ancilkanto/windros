<?php
namespace WindroseSubscription\Templates;
use WindroseSubscription\Includes\WindroseSkipSubscription;

defined( 'WINDROS_INIT' ) || exit;      // Exit if accessed directly.


class WindroseAdminSubscriptionDetailsTemplate {
    public function display_subscription_details($subscription_id) {
        global $wpdb;

        // Define your custom table name (considering WordPress table prefix)
        $subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;

        // Query to get all rows from the custom table
        // Prepare and execute the query to retrieve a single subscription
        $subscription = $wpdb->get_row( 
            $wpdb->prepare( "SELECT * FROM $subscription_table WHERE id = %d", $subscription_id )                
        );

        if ( $subscription ) {
            $product = wc_get_product( $subscription->product_id );
            $product_title = $product->get_name();
            // Get product thumbnail (this returns the URL)
            $product_thumbnail_url = get_the_post_thumbnail_url( $subscription->product_id, 'full' );
            // If no thumbnail exists, use WooCommerce placeholder image
            if ( !$product_thumbnail_url ) {
                $product_thumbnail_url = wc_placeholder_img_src(); // Placeholder image
            }
            // $subscription_schedule = $subscription->schedule;
            $subscription_qty = $subscription->quantity;
            $subscription_price = $product->get_price() * $subscription_qty;
            $formatted_price = wc_price( $subscription_price );
            $product_price_html = $formatted_price . '&nbsp;' . __('for', 'windros-subscription') . '&nbsp;' . WINDROS_FREQUENCY[$subscription->schedule];

            ob_start();
            ?>
                <div id="subscription_data" class="panel windrose-subscription-data">
                    <h2 class="windrose-subscription-data__heading">
                        Subscription <?php echo '#' . $subscription_id; ?> details				
                    </h2>
                    <p class="windrose-subscription-data__meta subscription_number">
                        Subscription created on: <?php echo date('F d, Y', strtotime($subscription->time_stamp)); ?>				
                    </p>
                    <div class="subscription_data_column_container">
                        <div class="subscription_data_column">
                            <div class="subsc-prdt-detail">
                                <div class="sub-prdt-thumb-full">
                                    <img src="<?php echo esc_url($product_thumbnail_url);?>" alt="<?php echo esc_attr($product_title); ?>">
                                </div>
                                <div class="sub-prdt-details">
                                    <h3 class="woocommerce-order-details__title"><?php echo esc_html($product_title); ?></h3>
                                    <h5 class="sub-prdt-price-schedule"><?php echo $product_price_html; ?></h5>
                                    <p><?php echo __('Quantity: ', 'windros-subscription') . $subscription_qty; ?></p>
                                    <p><?php echo __('WooCommerce Order ID:', 'windros-subscription') . '&nbsp;#' . $subscription->order_id; ?></p>
                                    <p class="subscription-status <?php echo esc_attr($subscription->status); ?>">
                                        <img src="<?php echo WINDROS_URL.'/assets/icons/'. esc_attr($subscription->status) .'.svg'; ?>" alt="<?php echo esc_attr($subscription->status); ?>">
                                        <?php
                                            echo WINDROS_SUBSCRIPTION_STATUS[$subscription->status];
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                
                <div class="panel-wrap windrose">
                    <?php $this->display_subscription_actions($subscription); ?>
                    <div class="clear"></div>                            
                </div>
            <?php

            echo ob_get_clean();
        } else {
            echo __('No subscription found.', 'windros-subscription');
        }
    }
    
    
    public function display_subscription_actions($subscription) {
        $product_name = $subscription->product_name;
        if(isset($_POST['edit-subscription'])){
            if (isset($_POST['edit_subscription_nonce']) && wp_verify_nonce($_POST['edit_subscription_nonce'], 'edit_subscription_action')) {
                $subscription_quantity = intval($_POST['subscription-quantity']);
                $subscription_schedule = intval($_POST['subscription-schedule']);
                $modify_upcoming = (sanitize_text_field($_POST['modify-upcoming-on-update']) == 'on') ? true : false;
                
                $this->update_subscription($subscription->id, $subscription_quantity, $subscription_schedule, $modify_upcoming );
            }
        }
        
        if(isset($_POST['subscription-action']) && $_POST['subscription-action'] == 'pause'){
            if (isset($_POST['pause_subscription_nonce']) && wp_verify_nonce($_POST['pause_subscription_nonce'], 'pause_subscription_action')) {
                
                $this->pause_subscription( $subscription->id );
            }
        }
        
        if(isset($_POST['subscription-action']) && $_POST['subscription-action'] == 'reactivate'){
            if (isset($_POST['reactivate_subscription_nonce']) && wp_verify_nonce($_POST['reactivate_subscription_nonce'], 'reactivate_subscription_action')) {
                
                $this->reactivate_subscription( $subscription->id );
            }
        }
        
        if(isset($_POST['subscription-action']) && $_POST['subscription-action'] == 'cancel'){
            if (isset($_POST['cancel_subscription_nonce']) && wp_verify_nonce($_POST['cancel_subscription_nonce'], 'cancel_subscription_action')) {
                
                $this->cancel_subscription( $subscription->id );
            }
        }

        ?>

            <div class="subscription-actions">
                <?php 
                    if($subscription->status == 'active' || $subscription->status == 'processing') {
                        ?>
                            <input type="button" id="edit-subscription" class="button action" value="<?php _e('Edit Subscription', 'windros-subscription' ); ?>">
                            
                        <?php 
                    }
                
                    if($subscription->status == 'active') {
                        ?>
                        <form method="post">
                            <?php wp_nonce_field('pause_subscription_action', 'pause_subscription_nonce'); ?>
                            <input type="hidden" name="subscription-id" value="<?php echo esc_attr( $subscription->id ); ?>">
                            <input type="hidden" name="subscription-action" value="pause">
                            <input type="submit" id="pause-subscription" class="button action" value="<?php _e('Pause Subscription', 'windros-subscription' ); ?>">
                        </form>
                        
                
                        <?php 
                    }

                    if($subscription->status == 'paused') {
                        ?>
                            
                            <form method="post">
                                <?php wp_nonce_field('reactivate_subscription_action', 'reactivate_subscription_nonce'); ?>
                                <input type="hidden" name="subscription-id" value="<?php echo esc_attr( $subscription->id ); ?>">
                                <input type="hidden" name="subscription-action" value="reactivate">
                                <input type="submit" id="reactivate-subscription" class="button action" value="<?php _e('Re-activate Subscription', 'windros-subscription' ); ?>">
                            </form>
                        <?php
                    }
                    
                    if($subscription->status == 'active' || $subscription->status == 'processing' || $subscription->status == 'paused') {
                        ?>
                            
                            <form method="post">
                                <?php wp_nonce_field('cancel_subscription_action', 'cancel_subscription_nonce'); ?>
                                <input type="hidden" name="subscription-id" value="<?php echo esc_attr( $subscription->id ); ?>">
                                <input type="hidden" name="subscription-action" value="cancel">
                                <input type="submit" id="cancel-subscription" class="button action" value="<?php _e('Cancel Subscription', 'windros-subscription' ); ?>">
                            </form>
                        <?php
                    }
                ?>
                
            </div>
            <div class="hidden edit-subscription-wrapper">
                <form method="post">
                    <?php wp_nonce_field('edit_subscription_action', 'edit_subscription_nonce'); ?>
                    <input type="hidden" name="edit-subscription" value="<?php echo esc_attr( $subscription->id ); ?>">
                    <div class="field-wrap">
                        <div class="field-label"><?php echo __('Quantity', 'windros-subscription'); ?></div>
                        <div class="field-item">
                            <div class="wc-block-components-quantity-selector">
                                <button aria-label="<?php echo __('Reduce quantity of', 'windros-subscription') . '&nbsp;' . esc_attr($product_name); ?>" class="wc-block-components-quantity-selector__button wc-block-components-quantity-selector__button--minus">－</button>
                                <input name="subscription-quantity" class="wc-block-components-quantity-selector__input" type="number" step="1" min="1" max="9999" aria-label=" <?php echo __('Quantity of', 'windros-subscription') . '&nbsp;' . esc_attr($product_name) . __('in your subscription.', 'windros-subscription') ;?> " value="<?php echo esc_attr($subscription->quantity); ?>">
                                <button aria-label="<?php echo __('Increase quantity of', 'windros-subscription') . '&nbsp;' . esc_attr($product_name); ?>" class="wc-block-components-quantity-selector__button wc-block-components-quantity-selector__button--plus">＋</button>
                            </div>
                        </div>
                    </div>
                    <div class="field-wrap">
                        <div class="field-label"><?php echo __('Schedule', 'windros-subscription'); ?></div>
                        <div class="field-item">
                            <div class="wc-block-components-quantity-selector">                                    
                                <select name="subscription-schedule" id="subscription-schedule">
                                    <?php
                                    
                                    $selected_fequencies = trim(get_post_meta( $subscription->product_id, '_subscription_frequencies', true ));
                                    if($selected_fequencies != ''){
                                        $selected_fequencies = explode(',', $selected_fequencies); 
                                        foreach($selected_fequencies as $frequency){
                                            $slected_value = (intval($frequency) === intval($subscription->schedule)) ? 'selected' : '';
                                            echo '<option value="'. $frequency .'" '.$slected_value.'>'. WINDROS_FREQUENCY[$frequency] .'</option>';
                                        }
                                    }
                                    
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="field-wrap">
                        <div class="field-item windrose-checkbox-field-wrap">
                            <input class="windrose-checkbox widrose-modify-upcoming" id="modify-upcoming-on-update" type="checkbox" name="modify-upcoming-on-update" checked>
                            <label for="modify-upcoming-on-update" class="windrose-label"><?php echo __('Update upcoming order? By check this, the very next order will also get modified.', 'windros-subscription'); ?></label>
                        </div>
                    </div>
                    <div class="field-wrap">
                        <div class="field-item">
                            <button type="submit" class="button action button-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>

        <?php
        
    }

    public function update_subscription($subscription_id, $quantity, $schedule, $modify_upcoming = true) { 

        global $wpdb;

        $current_URL = $_SERVER['REQUEST_URI'];
        $current_URL = str_replace('/wp-admin/','', $current_URL);

        // Define your custom table name (considering WordPress table prefix)
        $subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;
        $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;

        // Query to get all rows from the custom table
        // Prepare and execute the query to retrieve a single subscription
        $subscription = $wpdb->get_row( 
            $wpdb->prepare( "SELECT * FROM $subscription_table WHERE id = %d", $subscription_id)
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
                if($modify_upcoming){
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
                        $upcoming_order_data = $wpdb->get_row( 
                            $wpdb->prepare( "SELECT id FROM $subscription_order_table WHERE subscription_id = %d AND status = 'upcoming'", $subscription_id )
                        );

                        do_action('windrose_subscription_order_updated', $upcoming_order_data->id);

                        do_action('windrose_subscription_main_order_updated', $subscription_id);

                        set_transient('windrose_admin_action_notice', 'The subscription has been updated.', 30);
                        wp_safe_redirect(admin_url( $current_URL ));
                    }else{
                        $response['status'] = 'error';
                        $response['message'] = __('Subscription Not Updated!', 'windros-subscription');
                        wc_add_notice( __('Subscription Not Updated!', 'windros-subscription'), 'error');

                        set_transient('windrose_admin_action_notice', 'The subscription has not updated.', 30);
                        wp_safe_redirect(admin_url( $current_URL ));
                    }
                    
                }else{
                    wc_add_notice( __('Subscription Updated Successfully!', 'windros-subscription'), 'success');
                
                    $response['status'] = 'success';
                    $response['message'] = __('Subscription Updated Successfully!', 'windros-subscription');
                    
                    do_action('windrose_subscription_main_order_updated', $subscription_id);
                    set_transient('windrose_admin_action_notice', 'The subscription has been updated.', 30);
                    wp_safe_redirect(admin_url( $current_URL ));
                }
                
            }else{
                $response['status'] = 'error';
                $response['message'] = __('Subscription Not Updated!', 'windros-subscription');
                wc_add_notice( __('Subscription Not Updated!', 'windros-subscription'), 'error');

                set_transient('windrose_admin_action_notice', 'The subscription has not updated.', 30);
                wp_safe_redirect(admin_url( $current_URL ));
            }

            

        }else{
            $response['status'] = 'error';
            $response['message'] = __('Unauthorized Request!', 'windros-subscription');
            wc_add_notice( __('Unauthorized Request!', 'windros-subscription'), 'error');
        }
    }
    
    public function pause_subscription( $subscription_id ){
        
        global $wpdb;

        $current_URL = $_SERVER['REQUEST_URI'];
        $current_URL = str_replace('/wp-admin/','', $current_URL);

        // Define your custom table name (considering WordPress table prefix)
        $subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;
        $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;

        $data = array(
            'status' => 'paused'
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

            $order_pause_data = array(
                'status' => 'cancelled'
            );

            $order_pause_condition = array(
                'id' => $upcoming_order_data->id
            );

            $order_pause_format = array('%s');
            $order_pause_where_format = array('%d');

            $order_updated = $wpdb->update( $subscription_order_table, $order_pause_data, $order_pause_condition, $order_pause_format, $order_pause_where_format );

            do_action('windrose_subscription_order_cancelled', $upcoming_order_data->id);
            do_action('windrose_subscription_order_cancelled_on_pause', $upcoming_order_data->id);
            do_action('windrose_subscription_main_order_paused', $subscription_id);
            
            set_transient('windrose_admin_action_notice', 'The subscription has been paused.', 30);
            wp_safe_redirect(admin_url( $current_URL ));
        }else{
            set_transient('windrose_admin_action_notice', 'The subscription has not paused.', 30);
            wp_safe_redirect(admin_url( $current_URL ));
        }
    }


    public function reactivate_subscription($subscription_id) {
        global $wpdb;

        $current_URL = $_SERVER['REQUEST_URI'];
        $current_URL = str_replace('/wp-admin/','', $current_URL);

        // Define your custom table name (considering WordPress table prefix)
        $subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;
        $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;

        $data = array(
            'status' => 'active'
        );

        $condition = array(
            'id' => $subscription_id,
        );

        $format = array('%s');  
        $where_format = array('%d'); 

        $updated = $wpdb->update( $subscription_table, $data, $condition, $format, $where_format );

        if($updated){
            do_action('windrose_subscription_main_order_activated', $subscription_id);
            
            set_transient('windrose_admin_action_notice', 'The subscription has been activated.', 30);
            wp_safe_redirect(admin_url( $current_URL ));

        }else{
            set_transient('windrose_admin_action_notice', 'The subscription has not activated.', 30);
            wp_safe_redirect(admin_url( $current_URL ));
        }
    }

    public function cancel_subscription($subscription_id) {
        global $wpdb;

        $current_URL = $_SERVER['REQUEST_URI'];
        $current_URL = str_replace('/wp-admin/','', $current_URL);

        // Define your custom table name (considering WordPress table prefix)
        $subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;
        $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;

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
            do_action('windrose_subscription_main_order_cancelled', $subscription_id);

            set_transient('windrose_admin_action_notice', 'The subscription has been cancelled.', 30);
            wp_safe_redirect(admin_url( $current_URL ));
        }else{
            set_transient('windrose_admin_action_notice', 'The subscription has not cancelled.', 30);
            wp_safe_redirect(admin_url( $current_URL ));
        }
    }

    public function display_subscription_upcoming_delivery($subscription_id) {
        global $wpdb;
        $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;
        $subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;

        $upcoming_order_data = $wpdb->get_row( 
            $wpdb->prepare( "SELECT * FROM $subscription_order_table WHERE subscription_id = %d AND status = 'upcoming'", $subscription_id )                
        );

        

        if(!empty($upcoming_order_data)){
            $sequence = windrose_get_day_with_suffix($upcoming_order_data->sequence + 1);
            $timestamp = $upcoming_order_data->time_stamp;
            $date = date('F d, Y', $timestamp);

            $this->upcoming_delivery_markup($sequence, $date, true, $subscription_id, $upcoming_order_data->id);
        }else{                
            $subscription = $wpdb->get_row( 
                $wpdb->prepare( "SELECT * FROM $subscription_table WHERE id = %d", $subscription_id )                
            );

            if ( $subscription ) {
                if($subscription->status == 'processing') {
                    $this->upcoming_delivery_markup(windrose_get_day_with_suffix(1), 'Processing', false, 0, 0);
                }
            }

        }

        

    }

    public function upcoming_delivery_markup($sequence, $date, $action = true, $subscription_id = 0, $order_id = 0 ) {

        if(isset($_POST['subscription-action']) && $_POST['subscription-action'] == 'skip'){
            if (isset($_POST['skip_subscription_order_nonce']) && wp_verify_nonce($_POST['skip_subscription_order_nonce'], 'skip_subscription_order_action')) {
                $skip_subscription = new WindroseSkipSubscription();
                $skip_subscription->skip_subscription_action( $subscription_id, 'admin' );

                set_transient('windrose_admin_action_notice', 'The subscription has skipped its next delivery.', 30);
                $current_URL = $_SERVER['REQUEST_URI'];
                $current_URL = str_replace('/wp-admin/','', $current_URL);
                wp_safe_redirect(admin_url( $current_URL ));

            }
        }

        ob_start();

        ?>
            <div class="upcoming-delivery">
                <h3 class="woocommerce-order-details__title"><?php echo __('Upcoming Order', 'windros-subscription'); ?></h3>
                <div class="upcoming-wrapper">
                    <div class="upcoming-inner">
                        <div class="icon-wrapper">
                            <img src="<?php echo WINDROS_URL.'/assets/icons/calendar_month.svg'; ?>" alt="Calendar">
                        </div>
                        <div class="upcoming-details">
                            <h3 class="woocommerce-order-details__title"><?php echo esc_html($sequence) . '&nbsp;' . __('Delivery', 'windros-subscription') ; ?></h3>
                            <p class="delivery-date"><?php echo esc_html($date); ?></p>
                        </div>
                    </div>
                    <?php
                    if( $action ){
                        ?>
                        <div class="upcomming-actions">
                            <form method="post">
                                <?php wp_nonce_field('skip_subscription_order_action', 'skip_subscription_order_nonce'); ?>
                                <input type="hidden" name="subscription-action" value="skip">
                                <input type="hidden" name="skip-subscription" value="<?php echo esc_attr( $subscription_id  ); ?>">
                                <button type="submit" class="button action button-primary"><?php _e('Skip Delivery', 'windros-subscription' ); ?></button>
                            </form>
                        </div>
                        <?php 
                    }
                    ?>
                </div>
            </div>

        <?php

        echo ob_get_clean();
    }
    

    public function display_subscription_previous_deliveries($subscription_id) {
        global $wpdb;
        
        $subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;
        $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;

        $past_orders_query = $wpdb->prepare( "SELECT * FROM $subscription_order_table WHERE subscription_id = %d AND status != 'upcoming' ORDER BY sequence DESC", $subscription_id );
        $past_orders = $wpdb->get_results( $past_orders_query );

        $subscription = $wpdb->get_row( 
            $wpdb->prepare( "SELECT * FROM $subscription_table WHERE id = %d", $subscription_id )                
        );
        
        if($subscription->status != 'processing'){
            ob_start();
            ?>

                <div class="past-deliveries">
                    <h3 class="woocommerce-order-details__title"><?php echo __('Previous Orders', 'windros-subscription'); ?></h3>
                    <ul class="past-delivery-list">
                        <?php 
                            if(!empty($past_orders)){ 
                                foreach($past_orders as $past_order){
                                    $past_sequence = windrose_get_day_with_suffix(($past_order->sequence) + 1);
                                    $past_timestamp = $past_order->time_stamp;
                                    $past_date = date('F d, Y', $past_timestamp);
                                    ?>
                                    <li class="prev-order-status-<?php echo esc_attr($past_order->status); ?>">
                                        <h3 class="woocommerce-order-details__title">
                                            <?php echo esc_html($past_sequence) . '&nbsp;' . __('Delivery', 'windros-subscription') ; ?>
                                            <?php 
                                                if($past_order->status != 'past'){
                                                    echo '<sup>' . esc_html(WINDROS_SUBSCRIPTION_ORDER_STATUS[$past_order->status]) . '</sup>';
                                                }
                                            ?>
                                        </h3>
                                        <p class="delivery-date"><?php echo esc_html($past_date); ?></p>
                                    </li>
                                    <?php
                                }
                            }

                            if($subscription->active_date != ''){
                                $subscription_activated_date = strtotime($subscription->active_date);
                                $subscription_active_date = date('F d, Y', $subscription_activated_date);
                                ?>
                                <li class="prev-order-status-past>">
                                    <h3 class="woocommerce-order-details__title">
                                        <?php echo esc_html(windrose_get_day_with_suffix(1)) . '&nbsp;' . __('Delivery', 'windros-subscription') ; ?>
                                    </h3>
                                    <p class="delivery-date"><?php echo esc_html($subscription_active_date); ?></p>
                                </li>
                                <?php
                            }
                        ?>                                                                                        
                    </ul>
                </div>

            <?php
            if(!empty($past_orders) || $subscription->active_date != ''){
                echo ob_get_clean();
            }
        }
    }
}

