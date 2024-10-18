<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;



function windrose_plugin_uninstall() {

    error_log( 'Uninstalling Windrose' );
    // Global database object
    global $wpdb;

    if(WINDROS_DROP_TABLES){
        // Define table names (replace with your actual custom table names)
        $subscription_main_table = $wpdb->prefix . 'windrose_subscription';
        // $table_name_2 = $wpdb->prefix . 'custom_table_2';

        // Prepare the SQL queries to drop the tables
        // $sql = "DROP TABLE IF EXISTS {$table_name_1}, {$table_name_2};";
        $sql = "DROP TABLE IF EXISTS {$subscription_main_table};";

        // Execute the queries
        $wpdb->query( $sql );

        error_log( 'Table '.$subscription_main_table. ' Removed' );

    }

    
}
?>