<?php
defined( 'WINDROS_INIT' ) || exit;      // Exit if accessed directly.

if ( ! class_exists( 'Windros_Admin_Subscription_Details_Template' ) ) {
    class Windros_Admin_Subscription_Details_Template {
        public function display_subscription_details($subscription_id) {
            ob_start();
            ?>
                <div id="subscription_data" class="panel windrose-subscription-data">
                    <h2 class="windrose-subscription-data__heading">
                        Subscription <?php echo '#' . $subscription_id; ?> details				
                    </h2>
                    <p class="windrose-subscription-data__meta subscription_number">
                        Payment via Cash on delivery. Customer IP:				
                    </p>
                    <div class="subscription_data_column_container">
                        <div class="subscription_data_column">
                            <h3>General</h3>
                        </div>
                    </div>
                </div>                

            <?php

            echo ob_get_clean();
            
        }
        
        
        public function display_subscription_actions($subscription_id) {
            
        }
        

        public function display_subscription_upcoming_delivery($subscription_id) {
            
        }
        

        public function display_subscription_previous_deliveries($subscription_id) {
            
        }
    }
}
