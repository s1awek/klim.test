<?php

$option_arr_id = 'fupi_posthog';

$sections = array(

	// INSTALLATION

	array(
		'section_id' => 'fupi_posthog_install',
		'section_title' => esc_html__( 'Installation', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Project API key', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'api_key',
				'class'				=> 'fupi_required',
				'required'			=> true,
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[api_key]',
				'under field'		=> esc_html__( 'Paste the API Key from PostHog dashboard > "Project Settings".', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Is the tracked data hosted in the EU?', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'data_in_eu',
				'class'				=> 'fupi_sub',
				'option_arr_id'		=> $option_arr_id,
				'popup3'			=> '<p style="color: red;">' . esc_html__( 'It is very important to choose the right option here. If you make a wrong choice, PostHog tracking will not work.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . esc_html__( 'If you don\'t know if your PostHog data is kept on EU servers or not, please log in to your PostHog account and see if its address starts with eu.posthog.com.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
		),
	),

	// LOADING

	array(
		'section_id' => 'fupi_posthog_loading',
		'section_title' => esc_html__( 'Loading', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Force load', 'full-picture-analytics-cookie-notice' ),
				'el_class'			=> 'fupi_condition fupi_condition_reverse',
				'el_data_target'	=> 'fupi_load_opts',
				'field_id' 			=> 'force_load',
				'option_arr_id'		=> $option_arr_id,
				'popup3'			=> '<p style="color: red">' . esc_html__( 'Use only for installation verification or testing. It breaks GDPR and similar laws.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . sprintf( esc_html__( 'This will load the tracking tool for all website visitors, including administrators, bots, excluded users, people browsing from excluded locations and people that didn\'t agree to tracking. %1$sLearn more%2$s.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/support/documentation/validation-mode/?utm_source=fp_admin&utm_medium=referral&utm_campaign=settings_link">', '</a>' ) . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track without waiting for consent', 'full-picture-analytics-cookie-notice' ),
				'class'				=> 'fupi_load_opts',
				'must_have'			=> 'cook',
				'field_id' 			=> 'disreg_cookies',
				'option_arr_id'		=> $option_arr_id,
				'popup3'			=> '<p style="color: red">' . esc_html__( 'Use only for installation verification or testing. It breaks GDPR and similar laws.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . esc_html__( 'Visitors will still be able to turn off tracking by declining tracking / cookies.', 'full-picture-analytics-cookie-notice' ) . '</p>'
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__('Only track visitors from specific countries', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'limit_country',
				'must_have'			=> 'pro geo',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_load_opts',
				'is repeater'		=> false,
				'fields'			=> array(
					array(
						'type'				=> 'select',
						'field_id'			=> 'method',
						'options'			=> array(
							'excl'				=> esc_html__('All except', 'full-picture-analytics-cookie-notice'),
							'incl'				=> esc_html__('Only in', 'full-picture-analytics-cookie-notice'),
						),
						'class'		=> 'fupi_col_20',
					),
					array(
						'type'				=> 'text',
						'field_id'			=> 'countries',
						'placeholder'		=> esc_html__('e.g. GB, DE, FR, AU, etc.', 'full-picture-analytics-cookie-notice'),
					),
				),
				'popup'				=> sprintf( esc_html__('Enter a list of 2-character %1$scountry codes%2$s separated by comas.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://www.iban.com/country-codes">', '</a>' ) . '. ' . esc_html__('If visitor\'s country is not recognized, PostHog will load normally.', 'full-picture-analytics-cookie-notice' ),
			),
		),
	),
);

?>
