<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;


// Define the function to run during plugin activation
function windrose_plugin_activate() {
    error_log( 'Activating Windrose' );

    // Create a custom database table for subscription
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $subscription_main_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;
    $create_main_table_query = "CREATE TABLE $subscription_main_table (
        id bigint(9) NOT NULL AUTO_INCREMENT,
        order_id text NOT NULL,
        product_id text NOT NULL,
        user_id text NOT NULL,
        payment_token text NULL,
        schedule mediumint(9) NOT NULL,
        quantity mediumint(9) NOT NULL,
        status text NOT NULL,
        total_orders int NOT NULL,
        time_stamp timestamp NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($create_main_table_query);


    $subscription_order_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_ORDER_TABLE;

    $create_main_table_query = "CREATE TABLE $subscription_order_table (
        id bigint(9) NOT NULL AUTO_INCREMENT,
        subscription_id int NOT NULL,
        main_order_id int NOT NULL,
        user_id int NOT NULL,
        product_id int NOT NULL,
        quantity int NOT NULL,
        payment_token text NOT NULL,
        attempts int NOT NULL,
        status text NOT NULL,
        sequence int NOT NULL,
        time_stamp bigint(20) NOT NULL,
        created_at datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP
        PRIMARY KEY  (id)
      ) $charset_collate;";

    flush_rewrite_rules();
    
}

?>