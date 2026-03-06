<?php

$option_arr_id = 'fupi_woo';

$sections = array(

	// BASIC SETTINGS

	array(
		'section_id' => 'fupi_woo_main',
		'section_title' => esc_html__( 'Basic settings', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type' 				=> 'toggle',
				'label' 			=> esc_html__('Use SKU as a product id','full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'sku_is_id',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__('If SKU is not set, a default product id will be used. This value must be the same as the one in your product feeds, e.g. Merchant Center feed or Facebook catalog (if you use it).','full-picture-analytics-cookie-notice'),
			),
			array(
				'type' 				=> 'toggle',
				'label' 			=> esc_html__('Track prices with tax','full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'incl_tax_in_price',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__('This also applies to the shipping cost','full-picture-analytics-cookie-notice'),
			),
			array(
				'type' 				=> 'toggle',
				'label' 			=> esc_html__('Include shipping costs in order total','full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'incl_shipping_in_total',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__('This setting only applies to the total value of the purchase. If a tracking tool allows for tracking shipping cost as a separate value, then it will be tracked no matter whether this option is turned on or off.','full-picture-analytics-cookie-notice'),
			),
			array(
				'type' 				=> 'radio',
				'label' 			=> esc_html__('Method of tracking variable products','full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'variable_tracking_method',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_var_track_cond',
				'option_arr_id'		=> $option_arr_id,
				'options' 			=> array(
					'default' 			=> esc_html__('Standard','full-picture-analytics-cookie-notice'),
					'track_def_var'		=> esc_html__('Standard + track default variants','full-picture-analytics-cookie-notice'),
					'track_parents'		=> esc_html__('Track parent products instead of variants','full-picture-analytics-cookie-notice'),
				),
				'default' 			=> 'default',
				'popup'			=> '<h3>' . esc_html__('Standard','full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . esc_html__('Parent products are tracked in product lists and when a visitor views a product page. Variants are tracked when they are added to cart and in checkout.','full-picture-analytics-cookie-notice' ) . '</p>
					<h3>' . esc_html__('Standard + track default variants','full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . esc_html__('The same as "Standard" but when a visitor views a product page, WP FP sends two "product view" events - one for the parent product and another one for the active variant (if chosen in the product settings).','full-picture-analytics-cookie-notice' ) . '</p>
					<h3>' . esc_html__('Track parent products instead of variants','full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . esc_html__('Variants are not tracked. Parent products are tracked throughout the conversion path.','full-picture-analytics-cookie-notice' ) . '</p>',
			),
			array(
				'type' 				=> 'toggle',
				'label' 			=> esc_html__('Track views of product variants', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'track_variant_views',
				'class'				=> 'fupi_sub fupi_var_track_cond fupi_cond_val_track_def_var fupi_cond_val_default fupi_hidden fupi_disabled',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . esc_html__('This will send "product view" events when visitors switch between product variants.','full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type' 				=> 'toggle',
				'label' 			=> esc_html__('Do not track product views after a page is refreshed','full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'refresh_no_track_views',
				'option_arr_id'		=> $option_arr_id,
				'after field'		=> esc_html__('Recommended if your product pages are refreshed after adding products to cart','full-picture-analytics-cookie-notice'),
				'popup'				=> '<p>' . esc_html__('This setting applies to views of products on lists and product pages.','full-picture-analytics-cookie-notice') . '</p>
					<h3>' . esc_html__('Exception - product variants','full-picture-analytics-cookie-notice') . '</h3>
					<p>' . esc_html__('If you enabled an option to track views of product variants (see option above), then they will still be tracked after refresh.','full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type' 				=> 'toggle',
				'label' 			=> esc_html__('Show tracking information on order details pages','full-picture-analytics-cookie-notice'),
				'must_have'			=> 'pro',
				'field_id' 			=> 'order_stats',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . sprintf( esc_html__('Enable this function to view %1$sorder tracking information%2$s in the "edit order" pages in the WP admin. Please mind, that not all information will be available for orders that were made before you updated to WP FP 9.1.','full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/how-to-understand-information-in-the-woocommerce-tracking-information-box/">', '</a>' ) . '</p>',
			),
		),
	),

	// PRIVACY

	array(
		'section_id' => 'fupi_woo_priv',
		'section_title' => esc_html__( 'Privacy', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type' 				=> 'toggle',
				'label' 			=> esc_html__('Block WooCommerce Order Attribution function until consent','full-picture-analytics-cookie-notice'),
				'under field'		=> esc_html__('Recommended. Required in 60+ countries, including EU','full-picture-analytics-cookie-notice'),
				'must_have'			=> 'cook',
				'field_id' 			=> 'block_sbjs',
				'option_arr_id'		=> $option_arr_id,
				'popup2'			=> '<p>' . sprintf( esc_html__('We highly recommend disabling Order Attribution function on %1$sthis page%2$s', 'full-picture-analytics-cookie-notice'), '<a href="/wp-admin/admin.php?page=wc-settings&tab=advanced&section=features" target="_blank">', '</a>' ) . '</p>
				<p>' . esc_html__('WooComerce uses last-click order attribution model which gives a skewed picture on the effectiveness of traffic sources and ad campaigns. This means, it gives 100% of the credit for the purchase to the last source that drove traffic to your website, omitting the role of other sources.','full-picture-analytics-cookie-notice') . '</p>',
			),
		),
	),

	// STATUS-BASED ORDER TRACKING

	array(
		'section_id' => 'fupi_woo_adv',
		'section_title' => esc_html__( 'Status-based order tracking', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type' 				=> 'woo_order_statuses',
				'label' 			=> esc_html__( 'Track new orders when they get these statuses', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'server_track_on_statuses',
				'must_have'			=> 'pro',
				'default'			=> array( 'wc-processing', 'wc-on-hold' ),
				'option_arr_id'		=> $option_arr_id,
				'under field'		=> '<p>' . esc_html__('Recommended statuses - Processing (for orders paid via payment gateways) and On Hold (for orders paid with bank transfers).', 'full-picture-analytics-cookie-notice') . '</p>',
				'popup'		 		=> '<p>' . esc_html__('You can enter here multiple order statuses indicating that an order has been made. The "purchase" event will be sent only the first time an order gets a matching status.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type' 				=> 'woo_order_statuses',
				'label' 			=> esc_html__( 'Track order cancellations when they get these statuses', 'full-picture-analytics-cookie-notice' ),
				'must_have'			=> 'pro',
				'class'				=> 'fupi_join',
				'field_id' 			=> 'server_cancel_on_statuses',
				'default'			=> array( 'wc-cancelled', 'wc-refunded' ),
				'option_arr_id'		=> $option_arr_id,
				'under field'		=> '<p>' . esc_html__('Recommended statuses - Cancelled and Refunded.', 'full-picture-analytics-cookie-notice') . '</p>',
				'popup'		 		=> '<p>' . esc_html__('You can enter here multiple order statuses indicating that the order has been cancelled or refunded. The "refund" event will be sent only the first time an order gets a matching status.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type' 				=> 'toggle',
				'label' 			=> esc_html__('Debug Status-Based Order Tracking','full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'debug_server_calls',
				'must_have'			=> 'pro',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . esc_html__('This will add a meta box in the order details/edit page. The box will contain the data that WP FP sent to tracking tools with SBOT. The box will not show any results for orders created when this option was not active.','full-picture-analytics-cookie-notice') . '</p>',
			),
		),
	),

	// CUSTOM ADJUSTEMENTS

	array(
		'section_id' => 'fupi_woo_custom',
		'section_title' => esc_html__( 'Custom adjustments', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type' 				=> 'radio',
				'label' 			=> esc_html__('Track additions to cart','full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'where_track_addtocart',
				'option_arr_id'		=> $option_arr_id,
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_add_to_cart_mode',
				'options'			=> array(
					'default'			=> esc_html__( '(default) After clicks in "add to cart" buttons', 'full-picture-analytics-cookie-notice' ),
					'in_cart'			=> esc_html__( 'On the cart page', 'full-picture-analytics-cookie-notice' ),
				),
				'default'			=> 'default',
				'popup2'			=> '<p>' . sprintf( esc_html__('Use this function if your products are not tracked correctly when they are added to cart (you can check it with the Setup Helper). This can happen, if they are highly modified or do not use %1$sstandard WooCommerce elements%2$s.', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . '</p>
					<p class="fupi_warning_text">' . esc_html__( 'If you choose to track "add to cart" events on the cart page, redirect users to this page after they click "add to cart" buttons. You can find this option in WooCommerce settings.', 'full-picture-analytics-cookie-notice' ) . '</p>'
			),
			array(
				'type'				=> 'multi checkbox',
				'label' 			=> esc_html__( 'Disable tracking additions to cart', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'disable_woo_events',
				'class'				=> 'fupi_sub fupi_adv fupi_add_to_cart_mode fupi_hidden fupi_cond_val_default',
				'option_arr_id'		=> $option_arr_id,
				'options'			=> array(
					'add_to_cart_full'		=> esc_html__( 'For main products (on product pages)', 'full-picture-analytics-cookie-notice' ),
					'add_to_cart_teaser'	=> esc_html__( 'For product teasers (in lists)', 'full-picture-analytics-cookie-notice' ),
				),
				'popup2'		 		=> '<p>' . esc_html__('Use this function if additions to cart are not tracked correctly. This function will prevent WP FP from sending "add to cart" events after visitors click "add to cart" buttons.', 'full-picture-analytics-cookie-notice') . '</p>
				<p class="fupi_warning_text">' . sprintf( esc_html__('To re-enable tracking, you will have to %1$swrite custom "add to cart" event code%2$s', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/how-to-send-custom-add-to-cart-events-for-woocommerce/">', '</a>' ) . '</p>',
			),
			array(
				'label' 			=> esc_html__('Send "List item view" event instead of "Product view" on these pages','full-picture-analytics-cookie-notice'),
				'type'	 			=> 'r3',
				'is_repeater'		=> true,
				'option_arr_id'		=> $option_arr_id,
				'field_id' 			=> 'force_item_view_on_url',
				'class'				=> 'fupi_simple_r3 fupi_adv',
				'fields'			=> array(
					array(
						'type'	 			=> 'text',
						'placeholder'		=> esc_html__( 'URL or its part', 'full-picture-analytics-cookie-notice' ),
						'field_id' 			=> 'url_part',
					),
				),
				'popup2'		 	=> '<p>' . esc_html__('This function is useful for stores that display multiple full products on a single page (not to confuse with product teasers used in lists).', 'full-picture-analytics-cookie-notice') . '</p>
				<p>' . esc_html__('By default, "big" products are tracked with "view item" events, however this may not be always what you want. Use this function to track views of such products with "list item view" event, istead of "product view" event.', 'full-picture-analytics-cookie-notice') . '</p>
				<p class="fupi_warning_text">' . esc_html__('This function only affects tools that allow tracking views of products in lists, like Google Analytics, Ads, GTM or MS Advertising.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'CSS selector of "Add to wishlist" button', 'full-picture-analytics-cookie-notice' ),
				'under field'		=> esc_html__( 'Enter CSS class or ID', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'wishlist_btn_sel',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[wishlist_btn_sel]',
				'placeholder'		=> esc_html__('e.g. .my-add-to-wishlist', 'full-picture-analytics-cookie-notice' ),
				'popup2'	 		=> '<p>' . esc_html__('Enter CSS selector of an "add to wishlist" button used on your site (if it uses any).', 'full-picture-analytics-cookie-notice') . '</p>
				<p class="fupi_warning_text">' . esc_html__('The wishlist button needs to be positioned in your HTML right after the "Add to cart" button to work. Other placements may not work correctly. Tracking wishlisted products doesn\'t work with grouped products.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'CSS selectors of custom product teasers', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'teaser_wrapper_sel',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[teaser_wrapper_sel]',
				'placeholder'		=> esc_html__('e.g. .product', 'full-picture-analytics-cookie-notice' ),
				'under field'		=> esc_html__('You can provide a comma separated list of CSS selectors.', 'full-picture-analytics-cookie-notice' ),
				'popup2'	 		=> '<p>' . esc_html__('By default, all product teasers (products in lists) in WooCommerce are wrapped in &lt;li&gt;elements. However, some themes or plugins may use a different tag. If this happens, tracking clicks in the "add to cart" buttons and views of teasers may not work correctly.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__('You can fix it here. Provide CSS selector of an HTML element which wraps around product teasers on your site (often this is an element with a class "product").', 'full-picture-analytics-cookie-notice') . '</p>
					<p class="fupi_warning_text">' . esc_html__('Attention. Tracking will only work if there is an HTML &lt;i&gt; element with class "fupi_prod_data" inside the product teaser\'s HTML. If it is missing, then it means that the teaser does not use standard WooCommerce hooks to display its content, and WP Full Picture will not be able to track it.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
		),
	),
);

?>
