<?php
namespace WindroseSubscription\Templates;

defined( 'WINDROS_INIT' ) || exit;      // Exit if accessed directly.


class WindroseUpdateSubscriptionTemplate {

    public function update_subscription($subscription_data){
        
        
        $product_name = $subscription_data->product_name;

        ob_start();
        ?>
            <h3 class="woocommerce-order-details__title"><?php echo __('Update Subscription', 'windros-subscription'); ?></h3>
            <div class="windrose-frontdrop-content-block">
                <div class="field-form-wrap update-subscription-form">
                    <div class="field-wrap">
                        <div class="field-label"><?php echo __('Quantity', 'windros-subscription'); ?></div>
                        <div class="field-item">
                            <div class="wc-block-components-quantity-selector">
                                <input class="wc-block-components-quantity-selector__input" type="number" step="1" min="1" max="9999" aria-label=" <?php echo __('Quantity of', 'windros-subscription') . '&nbsp;' . esc_attr($product_name) . __('in your subscription.', 'windros-subscription') ;?> " value="<?php echo esc_attr($subscription_data->quantity); ?>">
                                <button aria-label="<?php echo __('Reduce quantity of', 'windros-subscription') . '&nbsp;' . esc_attr($product_name); ?>" class="wc-block-components-quantity-selector__button wc-block-components-quantity-selector__button--minus">－</button>
                                <button aria-label="<?php echo __('Increase quantity of', 'windros-subscription') . '&nbsp;' . esc_attr($product_name); ?>" class="wc-block-components-quantity-selector__button wc-block-components-quantity-selector__button--plus">＋</button>
                            </div>
                        </div>
                    </div>
                    <div class="field-wrap">
                        <div class="field-label"><?php echo __('Schedule', 'windros-subscription'); ?></div>
                        <div class="field-item">
                            <div class="wc-block-components-quantity-selector">                                    
                                <select name="subscription-schedule" id="subscription-schedule">
                                    <?php
                                    
                                    $selected_fequencies = trim(get_post_meta( $subscription_data->product_id, '_subscription_frequencies', true ));
                                    if($selected_fequencies != ''){
                                        $selected_fequencies = explode(',', $selected_fequencies); 
                                        foreach($selected_fequencies as $frequency){
                                            $slected_value = (intval($frequency) === intval($subscription_data->schedule)) ? 'selected' : '';
                                            echo '<option value="'. $frequency .'" '.$slected_value.'>'. WINDROS_FREQUENCY[$frequency] .'</option>';
                                        }
                                    }
                                    
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="field-wrap">
                        <div class="field-item windrose-checkbox-field-wrap">
                            <input class="windrose-checkbox widrose-modify-upcoming" id="modify-upcoming-on-update" type="checkbox" name="modify-upcoming-on-update" checked>
                            <label for="modify-upcoming-on-update" class="windrose-label"><?php echo __('Update upcoming order? By check this, the very next order will also get modified.', 'windros-subscription'); ?></label>
                        </div>
                    </div>
                </div>
                <div class="foredrop-footer">
                    <?php wp_nonce_field('update_subscription_action', 'update_subscription_nonce'); ?>
                    <a href="#update-subscription" class="woocommerce-button button confirm-edit-subscription" data-subscription-id="<?php echo esc_attr($subscription_data->id); ?>">
                        <?php echo __('Update', 'windros-subscription'); ?>
                    </a>
                    <a href="#close" class="woocommerce-button button windrose-close-popup">
                        <?php echo __('Close', 'windros-subscription'); ?>
                    </a>
                </div>
            </div>
            
        <?php
        echo ob_get_clean();
    }
}
