<?php

if ( ! class_exists( 'Admin_Product_Subscription_Options' ) ) {
    class Admin_Product_Subscription_Options {
        public function __construct() {
            // Enqueue admin scripts
            add_action('admin_enqueue_scripts', [$this, 'admin_scripts'], 100);

            // Add Subscription tab for Product
            add_filter( 'woocommerce_product_data_tabs', [$this, 'add_subscription_settings_tab'], 10, 1 );

            // Set Icon for Subscription Tab
            add_action( 'admin_head', [$this, 'subscription_settings_tab_icon'] );

            // add product subscription options to subscription tab
            add_filter( 'woocommerce_product_data_panels', [$this, 'subscription_fields_for_products'] );

            // Save Subscription options
            add_action('woocommerce_process_product_meta', [$this, 'save_subscription_fields_data'] );
            
        }

        public function admin_scripts() {
            wp_enqueue_script('windros-admin', 
                WINDROS_URL .'assets/javascripts/admin.js',   //
                array ('jquery'),					//depends on these, however, they are registered by core already, so no need to enqueue them.
                false, false);
            // wp_enqueue_script('windros-admin');
        }

        public function add_subscription_settings_tab( $tabs ) {
            $tabs = array_merge(
                $tabs,
                array(
                    'windros-subscription-settings' => array(
                        'label'    => __( 'Subscription settings', 'windros-subscription' ),
                        'target'   => 'windros_subscription_settings',
                        'class'    => array(),
                        'priority' => 11,
                    ),
                )
            );
            return $tabs;
        }
        
        // Add CSS - icon
        public function subscription_settings_tab_icon() {
            echo '<style>
                #woocommerce-product-data ul.wc-tabs li.windros-subscription-settings_options a::before {
                    content: "\f515";
                } 
            </style>';
        }


        // Add subscription options for product

        public function subscription_fields_for_products() {
            global $thepostid;
        
            $product = wc_get_product( $thepostid );
            
            ?>
        
            <div id="windros_subscription_settings" class="panel woocommerce_options_panel">
                <h4 style="padding-left: 10px;">Subscription Settings</h4>
                <div class="options_group">
                    <?php
                        woocommerce_wp_checkbox( array(
                            'id'            => '_enable_subscription',
                            'wrapper_class' => 'show_if_simple',
                            'label'         => __( 'Enable Subscription' ),
                            'description'   => __( 'check if you want to enable product subscription' )
                        ) );
                    ?>
                </div>
                <?php
                    $enable_subscription_fields = false;
                    $enable_subscription = get_post_meta( $thepostid, '_enable_subscription', true ); // Get the data - Checbox 1
                    if( ! empty( $enable_subscription ) && $enable_subscription == 'yes' ){
                        $enable_subscription_fields = true;
                    }
                    
                ?>
                <!-- <div class="subscription-fields" <?php echo !$enable_subscription_fields ? ' style="display: none;"' : ''; ?> >            
                    <div class="options_group">
                        <?php
                            // Custom field for "Manufacturer"
                            woocommerce_wp_text_input( 
                                array( 
                                    'id'          => '_manufacturer_name_2', 
                                    'label'       => __('Manufacturer Name', 'woocommerce'), 
                                    'desc_tip'    => 'true',
                                    'description' => __('Enter the manufacturer name for this product.', 'woocommerce')
                                )
                            );
                        ?>
                    </div>
                </div> -->
            </div>
        
            <?php
        
        }

        public function save_subscription_fields_data($post_id) {
            
            $custom_field_value = isset($_POST['_enable_subscription']) ? sanitize_text_field($_POST['_enable_subscription']) : '';
            update_post_meta($post_id, '_enable_subscription', $custom_field_value);
            
            $custom_field_value = isset($_POST['_manufacturer_name_2']) ? sanitize_text_field($_POST['_manufacturer_name_2']) : '';
            update_post_meta($post_id, '_manufacturer_name_2', $custom_field_value);
        }
    }
}
new Admin_Product_Subscription_Options();
