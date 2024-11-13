<?php
namespace WindroseSubscription\Templates;

defined( 'WINDROS_INIT' ) || exit;      // Exit if accessed directly.


class WindrosePauseSubscriptionTemplate {

    public function pause_subscription($subscription_data){                                    

        ob_start();
        ?>
            <h3 class="woocommerce-order-details__title"><?php echo __('Pause Subscription', 'windros-subscription'); ?></h3>
            <div class="windrose-frontdrop-content-block">
                <div class="field-form-wrap pause-subscription-form">
                    <div class="field-wrap">
                        <div class="field-item"><?php echo __('Do you really want to pause the subscription? If yes, please confirm.', 'windros-subscription'); ?></div>                            
                    </div>                        
                </div>
                <div class="foredrop-footer">
                    <?php wp_nonce_field('pause_subscription_action', 'pause_subscription_nonce'); ?>
                    <a href="#pause-subscription" class="woocommerce-button button confirm-pause-subscription" data-subscription-id="<?php echo esc_attr($subscription_data->id); ?>">
                        <?php echo __('Confirm', 'windros-subscription'); ?>
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