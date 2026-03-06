<?php

$option_arr_id = 'fupi_simpl';

$sections = array(

	array(
		'section_id' => 'fupi_simpl_install',
		'section_title' => esc_html__( 'Installation & Loading', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Use server-side tracking INSTEAD of browser tracking (deprecated by SA)', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'server_side',
				'must_have'			=> 'pro',
				'class'				=> 'fupi_adv',
				'el_class'			=> 'fupi_condition fupi_condition_reverse',
				'el_data_target'	=> 'fupi_serverside_cond',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[server_side]',
				'popup3'			=> '<p style="color: red">' . esc_html__( 'Server-side tracking has been deprecated by Simple Analytics. Use this feature at your own risk. It may be removed at any moment.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p><p>' . sprintf( esc_html__( 'When this option is enabled, data about your visitors will be sent to Simple Analytics via server, and not their browser. This will bypass ad-blockers and increase accuracy of your data. However most of the options below will become unavailable. %1$sLearn more%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://docs.simpleanalytics.com/script#developers">', '</a>' ) . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Custom domain for bypassing ad-blockers', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'src',
				'class'				=> 'fupi_sub fupi_serverside_cond fupi_adv',
				'required'			=> true,
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[src]',
				'placeholder'		=> 'https://simple.example.com/',
				'popup'				=> '<p>' . esc_html__( 'Here you can paste the custom URL that you configured to bypass ad-blockers. If you are not sure what it means, go to your Simple Analytics dashboard > "Settings" > "Bypass Ad-blockers" section and see if any custom domain was entered there.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . sprintf( esc_html__( 'Enter https:// at the beginning of your address and put "/" at the end, for example %1$shttps://simple.example.com/%2$s', 'full-picture-analytics-cookie-notice' ), '<b>', '</b>' ) . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Join traffic data from multiple websites under one domain', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'join_traffic',
				'class'				=> 'fupi_sub fupi_serverside_cond fupi_adv',
				'required'			=> true,
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[join_traffic]',
				'placeholder'		=> 'example.com',
				'popup'				=> '<p>' . esc_html__( 'Enable this option if you have Simple Analytics installed on multiple domains and your visitors freely move between them. A common situation is when you have multiple language versions of the same site in different subdomains e.g. examples.com, de.example.com, fr.example.com.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . sprintf( esc_html__( 'Enter the domain name in the field above (without the "https://" part) that you want all traffic from the other domains to be joined in. %1$sLearn more%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://docs.simpleanalytics.com/overwrite-domain-name">', '</a>' ) . '. ' . esc_html__('Remember, that Simple Analytics needs to be installed on all of those websites and the domain that you enter above needs to be registered in the Simple Analytics dashboard.','full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Run on localhost (for web developers)', 'full-picture-analytics-cookie-notice' ),
				'class'				=> 'fupi_sub fupi_serverside_cond fupi_adv',
				'field_id' 			=> 'localhost',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[localhost]',
				'popup'				=> '<p>' . sprintf( esc_html__( 'Enable this, if you are running Simple Analytics on a localhost dev environment. %1$sRead more%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://docs.simpleanalytics.com/script#developers">', '</a>' ) . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track the same page with different hashes as different pages', 'full-picture-analytics-cookie-notice' ),
				'class'				=> 'fupi_sub fupi_serverside_cond fupi_adv',
				'field_id' 			=> 'hashes',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[hashes]',
				'popup'				=> '<p>' . sprintf( esc_html__( 'Enable this, if you are running Simple Analytics on a single-page website and clicks in navigation scroll page to different sections on the page. %1$sRead more%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://docs.simpleanalytics.com/hash-mode">', '</a>' ) . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Force load', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'force_load',
				'el_class'			=> 'fupi_condition fupi_condition_reverse',
				'el_data_target'	=> 'fupi_load_opts',
				'option_arr_id'		=> $option_arr_id,
				'popup2'			=> sprintf( esc_html__( 'When this setting is enabled, Simple Analytics will load for all visitors - even for bots, logged in administrators and excluded users and locations (with the geolocation module). %1$sLearn more%2$s.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/support/documentation/validation-mode/">', '</a>' ),
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__('Only track visitors from specific countries', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'limit_country',
				'option_arr_id'		=> $option_arr_id,
				'must_have'			=> 'pro geo',
				'class'				=> 'fupi_load_opts fupi_adv',
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
				'popup'				=> sprintf( esc_html__('Enter a list of 2-character %1$scountry codes%2$s separated by commas.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://www.iban.com/country-codes">', '</a>' ) . '. ' . esc_html__('If visitor\'s country is not recognized, Simple Analytics will load as usual.', 'full-picture-analytics-cookie-notice' ),
			)
		),
	),
);

?>
