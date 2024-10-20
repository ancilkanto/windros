<?php
defined( 'WINDROS_INIT' ) || exit;      // Exit if accessed directly.

if ( ! class_exists( 'Windrose_Subscription_List_Template' ) ) {
    class Windrose_Subscription_List_Template {

		
		public function subscription_list() {

			echo '<h3>' . __( 'Subscriptions', 'windros-subscription' ) . '</h3>';
            


			wp_enqueue_style('windrose-myaccount', WINDROS_URL .'assets/stylesheets/my-account.css'); 

			$customer = wp_get_current_user();

			global $wpdb;

			// Define your custom table name (considering WordPress table prefix)
			$subscription_table = $wpdb->prefix . WINDROS_SUBSCRIPTION_MAIN_TABLE;

			// Query to get all rows from the custom table
			$subscription_query = $wpdb->prepare( "SELECT * FROM $subscription_table WHERE user_id = %d", $customer->ID );
			$subscription_list = $wpdb->get_results( $subscription_query );
			
			if ( !empty($subscription_list) ) {
				echo '<p>' . __( 'Here is the list of all your subscriptions!', 'windros-subscription' ) . '</p>';
				ob_start();
			?>
					<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
						<thead>
							<tr>
								<th scope="col" style="width: 50%;" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span
										class="nobr">Subscription</span></th>
								<th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date"><span
										class="nobr">Upcoming</span></th>
								<th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span
										class="nobr">Status</span></th>
								<th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-actions">
									<span class="nobr">Actions</span></th>
							</tr>
						</thead>

						<tbody>
							<?php
								foreach ( $subscription_list as $subscription_item ) {
									
									// Get the product object
									$product = wc_get_product( $subscription_item->product_id );
									$product_title = $product->get_name();
									// Get product thumbnail (this returns the URL)
    								$product_thumbnail_url = get_the_post_thumbnail_url( $subscription_item->product_id, 'thumbnail' );
									// If no thumbnail exists, use WooCommerce placeholder image
									if ( !$product_thumbnail_url ) {
										$product_thumbnail_url = wc_placeholder_img_src(); // Placeholder image
									}
									// $subscription_schedule = $subscription_item->schedule;
									$subscription_qty = $subscription_item->quantity;
									$subscription_price = $product->get_price() * $subscription_qty;
									$formatted_price = wc_price( $subscription_price );
									$product_price_html = $formatted_price . '&nbsp;' . __('for', 'windros-subscription') . '&nbsp;' . WINDROS_FREQUENCY[$subscription_item->schedule];
									?>
										<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
											<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="Order"
												scope="row">
												<div class="subsc-prdt-detail">
													<div class="sub-prdt-thumb">
														<img src="<?php echo esc_url($product_thumbnail_url);?>" alt="<?php echo esc_attr($product_title); ?>">
													</div>
													<div class="sub-prdt-details">
														<h3 class="sub-prdt-name"><?php echo esc_html($product_title); ?></h3>
														<h6 class="sub-prdt-price-schedule"><?php echo $product_price_html; ?></h6>
													</div>
												</div>												
											</th>
											<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date" data-title="Date">

												<time datetime="2024-10-18T07:41:33+00:00">October 18, 2024</time>
												
											</td>
											<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" data-title="Status">
												<span class="subscription-status subscription-status_<?php echo esc_attr($subscription_item->status); ?>">
													<?php
														echo WINDROS_SUBSCRIPTION_STATUS[$subscription_item->status];
													?>
												</span>
											</td>
											<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions"
												data-title="Actions">

												<a data-href="http://windros/my-account/view-order/296/" href="#" class="woocommerce-button button view"
													aria-label="View order number 296">View</a>
											</td>
										</tr>
									<?php
								}
							?>
						</tbody>
					</table>
				<?php

				echo ob_get_clean();
				
			} else {
				echo '<p>' . __( 'Sorry! No Subscriptions Available.', 'windros-subscription' ) . '</p>';
			}

			
			
		}
	}
}

?>
