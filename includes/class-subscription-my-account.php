<?php
defined( 'WINDROS_INIT' ) || exit;      // Exit if accessed directly.

if ( ! class_exists( 'Windrose_My_Account_Init' ) ) {
    class Windrose_My_Account_Init {
        public function __construct() {
            // Add new tab to My Account
            add_filter( 'woocommerce_account_menu_items', [$this, 'windrose_subscription_add_my_account_tab'], 10, 1 );

            // Add content for new tab
            add_action( 'woocommerce_account_subscriptions_endpoint', [$this, 'windrose_subscription_add_my_account_tab_content'] );

            add_action( 'woocommerce_account_view-subscription_endpoint', [$this, 'subscription_detail_content'] );

            // Register the custom endpoint for the new tab
            add_action( 'init', [$this, 'windrose_add_my_account_endpoint' ]);

            // Make the subscription tab active when viewing an subscription detail
            add_filter( 'woocommerce_account_menu_item_classes', [$this, 'subscriptions_set_active_menu_item'], 10, 2 );
                
        }

        public function windrose_subscription_add_my_account_tab( $items ) {
            
            $new_items = array_slice($items, 0, 2, true);
            $new_items['subscriptions'] = __( 'Subscriptions', 'windros-subscription' );
            $new_items += array_slice($items, 2, null, true);
            return $new_items;
        }

        public function windrose_add_my_account_endpoint() {
            add_rewrite_endpoint( 'subscriptions', EP_ROOT | EP_PAGES );
            add_rewrite_endpoint( 'view-subscription', EP_ROOT | EP_PAGES );
            // Flush rewrite rules upon plugin activation is in "install-plugin.php"
        }

        public function windrose_subscription_add_my_account_tab_content() {
            
            $subscription_template = new Windrose_Subscription_List_Template();
            $subscription_template->subscription_list();
        }

        

        // Add content for the Item Detail view
        public function subscription_detail_content() {
            // Get the item ID from the URL (replace with your custom query)
            $subscription_id = get_query_var( 'view-subscription' );

            

            $subscription_template = new Windrose_Subscription_Details_Template();
            $subscription_template->subscription_details($subscription_id);
            
        }


        public function subscriptions_set_active_menu_item( $classes, $endpoint  ) {
            global $wp;
                    
            // Check if we're on the subscription-detail page
            if ( isset( $wp->query_vars['view-subscription'] ) ) {
                // Add the 'is-active' class to the 'custom-items' menu
                if ( $endpoint == 'subscriptions' ) {
                    $classes[] = 'is-active';
                }
            }

            return $classes;            
        }            

    }
}

new Windrose_My_Account_Init();

















?>