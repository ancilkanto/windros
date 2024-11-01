<?php
defined( 'WINDROS_INIT' ) || exit;  



if ( ! class_exists( 'Windros_Admin_Subscription_List' ) ) {
    class Windros_Admin_Subscription_List {
        public function __construct() {
            add_action('admin_menu', [$this, 'subscription_listing_menu']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_select2_assets']);
            add_action('admin_footer', [$this, 'initialize_select2_filters']);
        }


        public function subscription_listing_menu() {
            add_submenu_page(
                'woocommerce', 
                'Subscriptions',
                'Subscriptions',
                'manage_woocommerce',
                'windrose-subscriptions',
                [$this, 'subscription_list_view'],
            );
        }
        
        public function subscription_list_view() {
            echo '<div class="wrap"><h1>'. __('Subscriptions', 'windros-subscription') .'</h1>';
            if ( class_exists( 'Windros_Admin_Subscription_List_Template' ) ) {
                
                $subscription_list = new Windros_Admin_Subscription_List_Template();
                $subscription_list->prepare_items();
                
                $subscription_list->display();
                
                              
            }
            echo '</div>';
        }

        public function enqueue_select2_assets() {
            // Ensure WooCommerce is active to use its assets
            if (class_exists('WooCommerce')) {
                wp_enqueue_style('select2-css', WC()->plugin_url() . '/assets/css/select2.css');
                wp_enqueue_script('select2-js', WC()->plugin_url() . '/assets/js/select2/select2.full.min.js', array('jquery'), WC_VERSION, true);
            }
        }

        public function initialize_select2_filters() {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    // $('#filter_status').select2({
                    //     placeholder: 'Select a Status',
                    //     allowClear: true,
                    //     minimumResultsForSearch: Infinity // hides the search box for small lists
                    // });
                    // $('#product_filter').select2({
                    //     // placeholder: 'Select a Product',
                    //     // allowClear: false,
                    //     // minimumResultsForSearch: Infinity
                    // });
                });
            </script>
            <?php
        }
        
    }
}

new Windros_Admin_Subscription_List();