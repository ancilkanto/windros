<?php
/* Plugin Name: Windros Subscription
* Plugin URI: https://ancil.dev/windros-subscrtiption
 * Description: <code><strong>Windros Subscription</strong></code> allows enabling automatic recurring payments on your products. Once you buy a subscription-based product, the plugin will renew the payment automatically based on your own settings.
 * Version: 1.0
 * Author: Ancil
 * Author URI: https://ancil.dev/
 * Text Domain: windros-subscription
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) || exit;

! defined( 'WINDROS_DIR' ) && define( 'WINDROS_DIR', plugin_dir_path( __FILE__ ) );
! defined( 'WINDROS_URL' ) && define( 'WINDROS_URL', plugin_dir_url(__FILE__) );
! defined( 'WINDROS_INIT' ) && define( 'WINDROS_INIT', plugin_basename( __FILE__ ) );
! defined( 'WINDROS_INC' ) && define( 'WINDROS_INC', WINDROS_DIR . 'includes/' );



require_once WINDROS_INC.'admin/class-product-subscription-options.php';




// Add a custom label next to the product title in WooCommerce admin products list
add_filter('manage_edit-product_columns', 'add_custom_product_label_column');
add_action('manage_product_posts_custom_column', 'show_custom_label_in_product_column', 100, 2);

// Add a custom column header (if needed)
function add_custom_product_label_column($columns) {
    // You can add a new column here if you want to show labels in a separate column
    // For example: $columns['custom_label'] = __('Custom Label', 'your-textdomain');
    return $columns;
}

// Display custom label next to the product title in WooCommerce admin
function show_custom_label_in_product_column($column, $post_id) {
    if ($column === 'name') { // 'name' is the product title column
        // Get the product object
        $product = wc_get_product($post_id);

        // Define your custom label
        $custom_label = '';
        $enable_subscription = get_post_meta( $post_id, '_enable_subscription', true ); // Get the data - Checbox 1
        if( ! empty( $enable_subscription ) && $enable_subscription == 'yes' ){
            $custom_label = '<strong> - <span class="post-state">Subscription Product</span></strong>';
        }

        // Display the product title with the custom label appended
        echo $custom_label;
    }
}














?>