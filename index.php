<?php
/* Plugin Name: Windros Subscription
* Plugin URI: https://github.com/ancilkanto/windros-subscription
 * Description: <code><strong>Windros Subscription</strong></code> allows enabling automatic recurring payments on your products. Once you buy a subscription-based product, the plugin will renew the payment automatically based on your own settings.
 * Version: 1.0
 * Author: Ancil
 * Author URI: https://ancil.dev/
 * Text Domain: windros-subscription
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) || exit;

$windros_subscription_frequencies = array(
    '7' => __('Every 1 Week', 'windros-subscription'),
    '14' => __('Every 2 Weeks', 'windros-subscription'),
    '21' => __('Every 3 Weeks', 'windros-subscription'),
    '28' => __('Every 4 Weeks', 'windros-subscription')
);

! defined( 'WINDROS_DIR' ) && define( 'WINDROS_DIR', plugin_dir_path( __FILE__ ) );
! defined( 'WINDROS_URL' ) && define( 'WINDROS_URL', plugin_dir_url(__FILE__) );
! defined( 'WINDROS_INIT' ) && define( 'WINDROS_INIT', plugin_basename( __FILE__ ) );
! defined( 'WINDROS_INC' ) && define( 'WINDROS_INC', WINDROS_DIR . 'includes/' );
! defined( 'WINDROS_FREQUENCY' ) && define( 'WINDROS_FREQUENCY', $windros_subscription_frequencies );




require_once WINDROS_INC.'admin/class-product-subscription-options.php';
require_once WINDROS_INC.'admin/class-register-shortcodes.php';
require_once WINDROS_INC.'admin/class-modify-product-loop-data.php';
require_once WINDROS_INC.'admin/class-modify-product-single-page.php';
require_once WINDROS_INC.'admin/class-subscription-cart.php';
require_once WINDROS_INC.'admin/class-subscription-checkout.php';





// Add a custom label next to the product title in WooCommerce admin products list
add_action('manage_product_posts_custom_column', 'show_custom_label_in_product_column', 100, 2);

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

