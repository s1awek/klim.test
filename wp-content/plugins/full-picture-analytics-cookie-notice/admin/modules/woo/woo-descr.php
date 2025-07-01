<?php

$ret_text = '';

switch( $section_id ){

	// DEFAULT

	case 'fupi_woo_main':
		$ret_text = '<p>' . esc_html__('These settings apply to all tools which support WooCommerce Tracking module (have the "WooCommerce tracking" section in their settings pages).', 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;

	case 'fupi_woo_adv':
		$ret_text = '<p>' . esc_html__( 'Status-Based Order Tracking is an alternative method of tracking purchases. At the moment it is only available in Meta Pixel and Google Analytics and needs to be enabled in their settings.', 'full-picture-analytics-cookie-notice' ) . ' <button type="button" class="fupi_open_popup button button-secondary" data-popup="fupi_adv_tracking_popup">' . esc_html__('Learn more', 'full-picture-analytics-cookie-notice' ) . '</button></p>';
	break;

	case 'fupi_woo_custom':
		$ret_text = '
		<p>' . sprintf( esc_html__( 'Here you can adjust tracking to work with non-standard store elements (ones that do not use %1$srequired WooCommerce functions and HTML%2$s).', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . '</p>';
	break;
};

?>
