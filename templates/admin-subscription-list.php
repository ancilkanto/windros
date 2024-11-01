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
                'subscription'  => 'Product',
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
                '<input type="checkbox" name="custom_data[]" value="%s" />', $item['id']
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
            return sprintf(
                '<a href="%s" target="_blank"><strong>%s</strong></a>', 
                get_edit_post_link($item['product_id']),
                $product->get_name() 
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
            $date = date('F d, Y', strtotime($timestamp));
            return sprintf(
                '<span>%s</span>', 
                $date
            );
            
        }


        // Define bulk actions
        public function get_bulk_actions() {
            return [
                'pause' => __('Pause Subscription', 'windros-subscription'),
                'cancel' => __('Cancel Subscription', 'windros-subscription'),
                'skip' =>__('Skip Upcoming Delivery', 'windros-subscription'),
            ];
        }

        // Process bulk actions
        public function process_bulk_action() {

            if ('pause' === $this->current_action()) {

                error_log('Pause Subscription');
                // Process pause action
                // Implement deletion logic for selected items
            } elseif ('cancel' === $this->current_action()) {
                // Process cancel action
                // Implement activation logic for selected items
            } elseif ('skip' === $this->current_action()) {
                // Process deactivate action
                // Implement deactivation logic for selected items
            }
        }
        



        // Add sample data
        function get_data($search = '', $status = 'all', $product_filter = 'all', $customer_filter = 'all') {
            global $wpdb;
            global $wp;
            
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
                $condition = 'WHERE '.implode(' OR ', $condition_params);
            }
            

            $subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE; 
            $query = "SELECT * FROM $subscription_table $condition $order_by $order";
            
            return $wpdb->get_results($query, ARRAY_A);
        }

        // Prepare items for the table
        function prepare_items() {
            
            echo '<form method="post">';
            $this->process_bulk_action();
            echo '</form>';
            
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