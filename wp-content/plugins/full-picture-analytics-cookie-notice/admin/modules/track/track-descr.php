<?php

$ret_text = '';

switch( $section_id ){
	
	// EXTEND TRACKING FEATURES

	case 'fupi_track_opt':
		$ret_text = '<p>' . esc_html__( 'These settings will help you track more data with your tracking tools', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// CHANGE DEFAULT SETTINGS

	case 'fupi_track_default':
		$ret_text = '<p>' . esc_html__( 'Here you can change the default tracking settings of tools installed with WP FP. Usually, there is no need to change them.' , 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;

	// TRACKING ACCURACY

	case 'fupi_track_ref':
		$ret_text = '<p>' . esc_html__( 'These functions can increase the amount and improve the accuracy of tracked data but sometimes they may give unexpected results. Click the "i" icons below for more information.' , 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;
};

?>
