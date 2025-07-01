<?php

$option_arr_id = 'fupi_privex';

$sections = array(

	// IFRAME BLOCKING

	array(
		'section_id' => 'fupi_privex_main',
		'section_title' => esc_html__( 'Privacy Policy Extras', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__('Add more tracking tools to the list', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'extra_tools',
				'class'				=> 'fupi_simple_r3',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'fields'			=> array(
					array(
						'type'				=> 'text',
						'field_id'			=> 'name',
						'placeholder'		=> 'Tool (required)',
						'required'			=> true,
					),
					array(
						'type'				=> 'url',
						'field_id'			=> 'url',
						'placeholder'		=> 'Privacy policy link (optional)',
					),
				),
				'popup'				=> '<p>' . esc_html__('Fill in these fields to add tracking tools that are installed with Google Tag Manager or other plugins (when they are not managed with the Tracking Tools Manager module).', 'full-picture-analytics-cookie-notice') . '</p>',
			),
		)
	),
);

?>
