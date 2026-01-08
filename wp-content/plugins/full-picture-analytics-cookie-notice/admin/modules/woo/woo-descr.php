<?php

$ret_text = '';

switch( $section_id ){

	// DEFAULT

	case 'fupi_woo_main':
		$ret_text = '<p>' . esc_html__('These settings apply to all tools which support WooCommerce Tracking module (have the "WooCommerce tracking" section in their settings pages). ', 'full-picture-analytics-cookie-notice' ) . '</p>';
		// <div>
		// 	<p class="fupi_warning_text">' . esc_html__('Make sure that the settings for taxes and product IDs are consistent with the product catalog/feed you send to Meta, Google Merchant Center, and other platforms. ', 'full-picture-analytics-cookie-notice' ) . '</p>
		// </div>';
	break;

	case 'fupi_woo_adv':
		$ret_text = '<div>
			<p>' . esc_html__( 'Status-Based Order Tracking is an alternative method of tracking purchases that does not require clients to see the order confirmation page. In Google Analytics it also tracks refunds and cancellations.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__('At the moment it is only supported in Google Analytics and Meta Pixel. You can learn how it works %1$son this page%2$s.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/support/documentation/what-you-need-to-know-about-status-based-order-tracking/">', '</a>' ) . '</p>
		</div>';
	break;

	case 'fupi_woo_priv':
		$ret_text = '<p>' . esc_html__('Make WooCommerce Order Attribution follow GDPR', 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;

	case 'fupi_woo_custom':
		$ret_text = '
		<p>' . sprintf( esc_html__( 'Here you can adjust tracking to work with non-standard store elements (ones that do not use %1$srequired WooCommerce functions and HTML%2$s).', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . '</p>';
	break;
};

?>
