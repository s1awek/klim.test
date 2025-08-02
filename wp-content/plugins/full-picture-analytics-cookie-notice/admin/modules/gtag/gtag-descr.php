<?php

$ret_text = '';

switch( $section_id ){
	case 'fupi_gtag_general':
		$ret_text = '<p>' . esc_html__( 'These settings are common for Google Analytics and Google Ads. Do not change them if you are not sure how they work.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;
};

?>
