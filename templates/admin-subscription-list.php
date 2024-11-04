<?php
defined( 'WINDROS_INIT' ) || exit;      // Exit if accessed directly.

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'Windros_Admin_Subscription_List_Template' ) ) {
    class Windros_Admin_Subscription_List_Template extends WP_List_Table {
        public function __construct() {
            parent::__construct([
                'singular' => 'subscription',
                'plural'   => 'subscriptions',
                'ajax'     => false
            ]);
        }

        // Adding filter options in the table navigation
        protected function extra_tablenav($which) {
            if ($which === 'top') {
                $selected_status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
                $selected_product = isset($_REQUEST['product_filter']) ? $_REQUEST['product_filter'] : '';
                $selected_customer = isset($_REQUEST['customer_filter']) ? $_REQUEST['customer_filter'] : '';
                $selected_search = isset($_REQUEST['s']) ? $_REQUEST['s'] : '';
                
                echo '<div class="alignleft actions">';
                
                echo '<form method="get">';
                echo '<input type="hidden" name="page" value="windrose-subscriptions">';
                // Status filter dropdown
                echo '<select name="status" id="filter_status">';
                echo '<option value="all">All Statuses</option>';
                foreach(WINDROS_SUBSCRIPTION_STATUS as $key => $status){
                    echo '<option value="'.$key.'" ' . selected($selected_status, $key, false) . '>'.$status.'</option>';
                }                
                echo '</select>';

                $subscription_products = windrose_get_subscrption_products();
                // Product filter dropdown
                echo '<select name="product_filter" id="product_filter">';
                echo '<option value="all">All Products</option>';
                foreach($subscription_products as $product){
                    echo '<option value="'.$product->id.'" ' . selected($selected_product, $product->id, false) . '>'.$product->title.'</option>';
                }                
                echo '</select>';

                $subscribers = windrose_get_customers();
                // Customer filter dropdown
                echo '<select name="customer_filter" id="customer_filter">';
                echo '<option value="all">All Customers</option>';
                foreach($subscribers as $subscriber){
                    echo '<option value="'.$subscriber->user_id.'" ' . selected($selected_customer, $subscriber->user_id, false) . '>'.$subscriber->customer_name.'</option>';
                }                
                echo '</select>';
                echo '<input type="text" name="s" id="search_id" placeholder="Search ID" value="'.esc_attr( $selected_search ).'">';
                // Submit button for the filters
                submit_button(__('Filter'), 'apply', 'filter_action', false);
                echo '</form>';   
                echo '</div>';
            }
        }

        // Define table columns
        function get_columns() {
            $columns = [
                'cb'            => '<input type="checkbox" />',
                'id'            => 'ID',
                'subscription'  => 'Subscription',
                'schedule'      => 'Delivery Schedule',
                'upcoming'      => 'Upcoming Delivery',
                'order_id'      => 'Order ID',
                'customer'      => 'Subscriber',
                'status'        => 'Status',
                'created'       => 'Created Date',
            ];
            return $columns;
        }

        // Set sortable columns
        function get_sortable_columns() {
            return [
                'id'      => ['id', false]
            ];
        }

        // Define column defaults
        function column_default($item, $column_name) {
            return $item[$column_name];
        }

        // Checkbox column
        function column_cb($item) {
            return sprintf(
                '<input type="checkbox" class="subscription-bulk-select" name="_subscriptions[]" value="%s" />', $item['id']
            );
        }

        function column_id($item) {
            return sprintf(
                '%d', $item['id']
            );
        }
        
        function column_order_id($item) {
            return sprintf(
                '<a href="%s" target="_blank">%s</a>', 
                get_edit_post_link($item['order_id']), 
                '#'.$item['order_id'] 
            );
        }
        
        function column_subscription($item) {
            $product = wc_get_product( $item['product_id'] );
            
            $subscription_url = get_admin_url(null, 'admin.php?page=windrose-subscription-detail&id='.$item['id']);

            return sprintf(
                '<a href="%s"><strong>%s</strong> <br> Quantity: %s</a>', 
                $subscription_url,
                $product->get_name(),
                $item['quantity']
            );
            
        }

        function column_schedule($item) {
            return sprintf(
                '%s', 
                WINDROS_FREQUENCY[$item['schedule']]
            );
            
        }
        function column_upcoming($item) {
            global $wpdb;
            $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;
            $upcoming_order_data = $wpdb->get_row( 
                $wpdb->prepare( "SELECT time_stamp FROM $subscription_order_table WHERE subscription_id = %d AND status = 'upcoming'", $item['id'] )
            );
            $date = '--';
            if($upcoming_order_data){										
                $timestamp = $upcoming_order_data->time_stamp;
                $date = date('M d, Y', $timestamp);                
            }
            return sprintf(
                '%s', 
                $date
            );
            
        }
        
        
        function column_customer($item) {
            
            $billing_first_name = get_user_meta($item['user_id'], 'billing_first_name', true);
            $billing_last_name = get_user_meta($item['user_id'], 'billing_last_name', true);
            $customer_name = $billing_first_name . ' '. $billing_last_name;
            return sprintf(
                '<span>%s</span>', 
                $customer_name
            );
            
        }

        function column_status($item) {
            
            return sprintf(
                '<mark class="subscription-status status-%s tips"><span>%s</span></mark>', 
                $item['status'],
                WINDROS_SUBSCRIPTION_STATUS[$item['status']]
            );
            
        }
        
        function column_created($item) {
            
            $timestamp = $item['time_stamp'];
            $date = date('M d, Y', strtotime($timestamp));
            return sprintf(
                '<span>%s</span>', 
                $date
            );
            
        }


        // Define bulk actions
        public function get_bulk_actions() {
            return [
                'activate' => __('Activate Subscription', 'windros-subscription'),
                'pause' => __('Pause Subscription', 'windros-subscription'),
                'cancel' => __('Cancel Subscription', 'windros-subscription'),
                'skip' =>__('Skip Upcoming Delivery', 'windros-subscription'),
            ];
        }

        

        public function process_bulk_action() {
            
            // Get the list of selected items from the bulk action
            if (isset($_POST['subscriptions']) && !empty($_POST['subscriptions'])) {
                
                // $selected_ids = array_map('intval', $_POST['subscriptions']);
                $selected_ids = explode(',', $_POST['subscriptions']);
                
                // Perform action based on the selected bulk action
                $current_action = ($this->current_action() != NULL) ? $this->current_action() : $_POST['action'];

                $current_URL = $_SERVER['REQUEST_URI'];
                $current_URL = str_replace('/wp-admin/','', $current_URL);

                switch ($current_action) {
                    case 'pause':
                        
                        foreach ($selected_ids as $id) {
                           
                            $pause_subscription = new Windros_Pause_Subscription();
                            $pause_subscription->pause_subscription_action($id, 'admin');

                        }
                        // Show an admin notice for paused items
                        // Set a transient for the admin notice
                        set_transient('windrose_bulk_action_notice', count($selected_ids) . ' subscriptions has been paused.', 30);
                        wp_safe_redirect(admin_url( $current_URL ));
                        exit;

                        break;

                    case 'activate':
                        foreach ($selected_ids as $id) {
                            $activate_subscription = new Windros_Reactivate_Subscription();
                            $activate_subscription->reactivate_subscription_action($id, 'admin');
                        }
                        // Show an admin notice for activated items
                        // Set a transient for the admin notice
                        set_transient('windrose_bulk_action_notice', count($selected_ids) . ' subscriptions has been activated.', 30);
                        
                        wp_safe_redirect(admin_url( $current_URL ));
                        exit;
                        break;
        
                    case 'cancel':
                        foreach ($selected_ids as $id) {
                            $cancel_subscription = new Windros_Cancel_Subscription();
                            $cancel_subscription->cancel_subscription_action($id, 'admin');
                        }
                        // Show an admin notice for activated items
                        // Set a transient for the admin notice
                        set_transient('windrose_bulk_action_notice', count($selected_ids) . ' subscriptions has been cancelled.', 30);
                        wp_safe_redirect(admin_url( $current_URL ));
                        exit;
                        break;
        
                    case 'skip':
                        
                        foreach ($selected_ids as $id) {
                            $skip_subscription = new Windros_Skip_Subscription();
                            $skip_subscription->skip_subscription_action($id, 'admin');
                        }
                        
                        // Show an admin notice for skipped items
                        // Set a transient for the admin notice
                        set_transient('windrose_bulk_action_notice', count($selected_ids) . ' subscriptions has been skipped next deliveries.', 30);
                        wp_safe_redirect(admin_url( $current_URL ));
                        exit;
                        break;
                }
            }
        }

                


        // Add sample data
        function get_data($search = '', $status = 'all', $product_filter = 'all', $customer_filter = 'all') {
            global $wpdb;
            
            
            $order_by = isset( $_GET['orderby'] ) ? 'ORDER BY '. $_GET['orderby'] : 'ORDER BY id';
            $order = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';

            $condition = '';
            $condition_params = array();

            // Search
            if (!empty($search)) {
                $condition_params [] = sprintf("id = '%s'", $search);                
                $condition_params [] = sprintf("order_id = '%s'", $search);                
            }
            
            // Status filter
            if (!empty($status) && $status != 'all') {
                $condition_params[] = sprintf("status = '%s'", $status);
            }
            
            // Product filter
            if (!empty($product_filter) && $product_filter != 'all') {
                $condition_params[] = sprintf("product_id = '%s'", $product_filter);
            }
            
            // Customer filter
            if (!empty($customer_filter) && $customer_filter != 'all') {
                $condition_params[] = sprintf("user_id = '%s'", $customer_filter);
            }

            if(!empty($condition_params)){
                $condition = 'WHERE '.implode(' AND ', $condition_params);
            }
            

            $subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE; 
            $query = "SELECT * FROM $subscription_table $condition $order_by $order";
            
            return $wpdb->get_results($query, ARRAY_A);
        }

        // Prepare items for the table
        function prepare_items() {
            
            
            $this->process_bulk_action();
            
            
            $columns = $this->get_columns();
            $hidden = [];
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = [$columns, $hidden, $sortable];

            // Handle search query
            $search = isset($_REQUEST['s']) ? esc_sql($_REQUEST['s']) : '';
            $status = isset($_REQUEST['status']) ? esc_sql($_REQUEST['status']) : '';
            $product_filter = isset($_REQUEST['product_filter']) ? esc_sql($_REQUEST['product_filter']) : '';
            $customer_filter = isset($_REQUEST['customer_filter']) ? esc_sql($_REQUEST['customer_filter']) : '';

            // Fetch data and set pagination
            $data = $this->get_data($search, $status, $product_filter, $customer_filter);
            $currentPage = $this->get_pagenum();
            $perPage = 10;
            $totalItems = count($data);
            $this->set_pagination_args([
                'total_items' => $totalItems,
                'per_page'    => $perPage
            ]);
            $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
            $this->items = $data;
        }

        
    }
}