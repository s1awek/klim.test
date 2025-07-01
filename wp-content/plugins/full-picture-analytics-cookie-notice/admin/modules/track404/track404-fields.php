<?php

$option_arr_id = 'fupi_track404';

$sections = array(

	array(
		'section_id' => 'fupi_track404_main',
		'section_title' => esc_html__( 'Track broken links', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type' 				=> 'text',
				'label' 			=> esc_html__( 'Use custom 404 page:', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'redirect_404',
				'placeholder'		=> 'e.g. https://example.com/my-custom-404=page/',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[redirect_404]',
				'popup_id'			=> 'fupi_redirect404_popup',
			),
		),
	),
);

?>
