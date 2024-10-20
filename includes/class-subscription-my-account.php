<?php
defined( 'WINDROS_INIT' ) || exit;      // Exit if accessed directly.

if ( ! class_exists( 'Windrose_My_Account_Init' ) ) {
    class Windrose_My_Account_Init {
        public function __construct() {
            // Add new tab to My Account
            add_filter( 'woocommerce_account_menu_items', [$this, 'windrose_subscription_add_my_account_tab'],1 );

            // Add content for new tab
            add_action( 'woocommerce_account_subscriptions_endpoint', [$this, 'windrose_subscription_add_my_account_tab_content'] );

            // Register the custom endpoint for the new tab
            add_action( 'init', [$this, 'windrose_add_my_account_endpoint' ]);
                
        }

        public function windrose_subscription_add_my_account_tab( $items ) {
            
            $new_items = array_slice($items, 0, 2, true);
            $new_items['subscriptions'] = __( 'Subscriptions', 'windros-subscription' );
            $new_items += array_slice($items, 2, null, true);
            return $new_items;
        }

        public function windrose_subscription_add_my_account_tab_content() {
            
            $subscription_template = new Windrose_Subscription_List_Template();
            $subscription_template->subscription_list();
        }

        public function windrose_add_my_account_endpoint() {
            add_rewrite_endpoint( 'subscriptions', EP_ROOT | EP_PAGES );
            // Flush rewrite rules upon plugin activation is in "install-plugin.php"
        }


    }
}

new Windrose_My_Account_Init();

















?>