<?php
/* Plugin Name: Windrose Subscription
* Plugin URI: https://github.com/ancilkanto/windros-subscription
 * Description: <code><strong>Windrose Subscription</strong></code> allows enabling automatic recurring payments on your products. Once you buy a subscription-based product, the plugin will renew the payment automatically based on your own settings.
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
! defined( 'WINDROS_SUBSCRIPTION_STATUS' ) && define( 'WINDROS_SUBSCRIPTION_STATUS', array(
    'processing' => __('Processing', 'windros-subscription'),
    'active' => __('Active', 'windros-subscription'),
    'paused' => __('Paused', 'windros-subscription'),
    'cancel' => __('Cancelled', 'windros-subscription'),
    'expired' => __('Expired', 'windros-subscription')
));
! defined( 'WINDROS_SUBSCRIPTION_ORDER_STATUS' ) && define( 'WINDROS_SUBSCRIPTION_ORDER_STATUS', array(
    'upcoming' => __('Upcoming', 'windros-subscription'),
    'past' => __('Past', 'windros-subscription'),
    'skipped' => __('Skipped', 'windros-subscription'),
    'cancelled' => __('Cancelled', 'windros-subscription')
));
! defined( 'WINDROS_SUBSCRIPTION_MAIN_TABLE' ) && define( 'WINDROS_SUBSCRIPTION_MAIN_TABLE', 'windrose_subscriptions' );
! defined( 'WINDROS_SUBSCRIPTION_ORDER_TABLE' ) && define( 'WINDROS_SUBSCRIPTION_ORDER_TABLE', 'windrose_subscription_orders' );
! defined( 'WINDROS_DROP_TABLES' ) && define( 'WINDROS_DROP_TABLES', true );






// Register the activation hook
register_activation_hook(__FILE__, 'windrose_plugin_activate');
require_once WINDROS_DIR.'install-plugin.php';

// Register the deactivation hook
register_uninstall_hook( __FILE__, 'windrose_plugin_uninstall' );
require_once WINDROS_DIR.'uninstall-plugin.php';

require_once 'vendor/autoload.php';



class MainWindroseClass {
    public function __construct() {
        $this->load_includes();
        
    }

    public function load_includes() {
        new WindroseSubscription\Includes\AdminProductSubscriptionOptions();  
        new WindroseSubscription\Includes\WindroseRegisterShortcodes();  
        new WindroseSubscription\Includes\WindroseModifyProductLoopData();
        new WindroseSubscription\Includes\WindroseModifyProductListingLoop();
        new WindroseSubscription\Includes\WindroseModifyProductSinglePage();
        new WindroseSubscription\Includes\WindroseSubscriptionCart();
        new WindroseSubscription\Includes\WindroseSubscriptionCheckout();
        new WindroseSubscription\Includes\WindroseCreateSubscription();
        new WindroseSubscription\Includes\WindroseMyAccountInit();
        new WindroseSubscription\Includes\WindroseActivateSubscription();
        new WindroseSubscription\Includes\WindroseCreateSubscriptionOrder();
        new WindroseSubscription\Includes\WindroseUpdateSubscription();
        new WindroseSubscription\Includes\WindrosePauseSubscription();
        new WindroseSubscription\Includes\WindroseCancelSubscription();
        new WindroseSubscription\Includes\WindroseSkipSubscription();
        new WindroseSubscription\Includes\WindroseReactivateSubscription();
        // admin side
        new WindroseSubscription\Includes\WindroseAdminSubscriptionList();
        new WindroseSubscription\Includes\WindroseAdminSubscriptionDetailView();
    }

    
}

new MainWindroseClass();



// require_once WINDROS_INC.'class-product-subscription-options.php';
// require_once WINDROS_INC.'class-register-shortcodes.php';
// require_once WINDROS_INC.'class-modify-product-loop-data.php';
// require_once WINDROS_INC.'class-modify-product-listing-loop.php';
// require_once WINDROS_INC.'class-modify-product-single-page.php';
// require_once WINDROS_INC.'class-subscription-cart.php';
// require_once WINDROS_INC.'class-subscription-checkout.php';
// require_once WINDROS_INC.'class-create-subscription.php';
// require_once WINDROS_INC.'class-subscription-my-account.php';
// require_once WINDROS_INC.'class-activate-subscription.php';
// require_once WINDROS_INC.'class-create-subscription-order.php';
// require_once WINDROS_INC.'class-update-subscription.php';
// require_once WINDROS_INC.'class-pause-subscription.php';
// require_once WINDROS_INC.'class-cancel-subscription.php';
// require_once WINDROS_INC.'class-skip-subscription.php';
// require_once WINDROS_INC.'class-reactivate-subscription.php';

// require_once WINDROS_INC.'class-admin-subscription-list.php';
// require_once WINDROS_INC.'class-admin-subscription-details.php';


// // Load templates
// require_once WINDROS_DIR.'templates/my-account-subscription-list.php';
// require_once WINDROS_DIR.'templates/my-account-subscription-details.php';
// require_once WINDROS_DIR.'templates/my-account-update-subscription.php';
// require_once WINDROS_DIR.'templates/my-account-pause-subscription.php';
// require_once WINDROS_DIR.'templates/my-account-cancel-subscription.php';
// require_once WINDROS_DIR.'templates/my-account-skip-subscription.php';

// // Load admin templates
// require_once WINDROS_DIR.'templates/admin-subscription-list.php';
// require_once WINDROS_DIR.'templates/admin-subscription-details.php';





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


function windrose_get_day_with_suffix($day) {
    if (!in_array(($day % 100), array(11, 12, 13))) {
        switch ($day % 10) {
            case 1: return $day . __('st', 'windros-subscription');
            case 2: return $day . __('nd', 'windros-subscription');
            case 3: return $day . __('rd', 'windros-subscription');
        }
    }
    return $day . __('th', 'windros-subscription');
}

function windrose_get_subscrption_products() {
    $subscription_products = array();
    $products = new WP_Query( array (
        'post_type'         => 'product',
        'post_status'       => 'publish',
        'posts_per_page'    => '-1',
        'meta_query'        => array(
            'relation'  => 'AND',
            array(
                'key'       => '_enable_subscription',
                'value'     => 'yes',
                'compare'   => '='
            )
        )
    ));

    if ( $products->have_posts() ) {
        while ( $products->have_posts() ) : $products->the_post(); 
            $subscription_products[] = (object) array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
            );
        endwhile;
    }

    return (object) $subscription_products;
}

function windrose_get_customers() {
    // Define the query to get all users with the customer role
    $args = [
        'role'    => 'customer',
        'orderby' => 'ID',
        'order'   => 'ASC',
        'fields'  => 'all', // Get full user data
    ];

    $user_query = new WP_User_Query($args);
    $customers = [];

    if (!empty($user_query->get_results())) {
        foreach ($user_query->get_results() as $user) {
            $user_id = $user->ID;
            $billing_first_name = get_user_meta($user_id, 'billing_first_name', true);
            $billing_last_name = get_user_meta($user_id, 'billing_last_name', true);

            // Add customer data to the array
            $customers[] = (object) array(
                'user_id'           => $user_id,
                'customer_name'     => $billing_first_name.' '.$billing_last_name
            );
        }
    }

    return (object) $customers;
}