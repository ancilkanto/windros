<?php

defined( 'WINDROS_INIT' ) || exit;      // Exit if accessed directly.

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
            // Scripts
            wp_register_script('chosen', 
                WINDROS_URL .'assets/javascripts/chosen.jquery.min.js',
                array ('jquery'),					
                false, false);
            wp_register_script('windros-admin', 
                WINDROS_URL .'assets/javascripts/admin.js', 
                array ('jquery', 'chosen'),					
                false, false);

            // Styles

            wp_register_style('chosen', 
                WINDROS_URL .'assets/stylesheets/chosen.min.css'
            );
            
            
        }

        public function add_subscription_settings_tab( $tabs ) {
            wp_enqueue_script('windros-admin');
            wp_enqueue_style('chosen');

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
                    content: "\f508";
                } 
                .chosen-wraper .chosen-container{
                    min-width: 300px;
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
                            'label'         => __( 'Enable Subscription', 'windros-subscription' ),
                            'description'   => __( 'check if you want to enable product subscription', 'windros-subscription' )
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
                <div class="subscription-fields <?php echo !$enable_subscription_fields ? ' hide-fields-on-load' : ''; ?>" >            
                    <div class="options_group">
                        <?php
                            $selected_fequencies = trim(get_post_meta( get_the_ID(), '_subscription_frequencies', true ));
                            
                            if($selected_fequencies == ''){
                                $selected_fequencies = array(strval(array_key_first(WINDROS_FREQUENCY)));
                            }else{
                                $selected_fequencies = explode(',', $selected_fequencies);
                            }

                            woocommerce_wp_select( array(
                                'id'          => '_subscription_frequencies[]',
                                'label'       => __( 'Set Frquencies', 'windros-subscription' ),
                                'options'     => WINDROS_FREQUENCY,
                                'wrapper_class' => 'chosen-wraper',
                                'value'       => $selected_fequencies,
                                'custom_attributes' => array(
                                    'multiple' => 'multiple',   // Enabling multi-select                                    
                                )
                            ));
                        ?>
                    </div>                    
                </div>
            </div>
        
            <?php
        
        }

        public function save_subscription_fields_data($post_id) {
            
            $custom_field_value = isset($_POST['_enable_subscription']) ? sanitize_text_field($_POST['_enable_subscription']) : '';
            update_post_meta($post_id, '_enable_subscription', $custom_field_value);

            
            $selected_fequencies = isset($_POST['_subscription_frequencies']) ? $_POST['_subscription_frequencies'] : array();
            
            if(empty($selected_fequencies)){
                $selected_fequencies = array(strval(array_key_first(WINDROS_FREQUENCY)));
            }
            $selected_fequencies = sanitize_text_field(implode(',', $selected_fequencies));
            
            update_post_meta($post_id, '_subscription_frequencies', $selected_fequencies);
        }
    }
}
new Admin_Product_Subscription_Options();
