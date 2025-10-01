<?php

$option_arr_id = 'fupi_tik';

$sections = array(

	// INSTALLATION

	array(
		'section_id' => 'fupi_tik_install',
		'section_title' => esc_html__( 'Installation', 'full-picture-analytics-cookie-notice' ),
		'fields' =>  array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'TikTok Pixel ID', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'id',
				'class'				=> 'fupi_required',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[id]',
				'under field'		=> '<p>' . sprintf( esc_html__('Learn %1$swhere to get pixel ID from%2$s.','full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/how-to-install-tiktok-pixel/?utm_source=fp_admin&utm_medium=fp_link" target="_blank">', '</a>') . '</p>',
			),
		),
	),

	// LOADING

	array(
		'section_id' => 'fupi_tik_loading',
		'section_title' => esc_html__( 'Loading', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Force load', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'force_load',
				'el_class'			=> 'fupi_condition fupi_condition_reverse',
				'el_data_target'	=> 'fupi_load_opts',
				'option_arr_id'		=> $option_arr_id,
				'popup3'			=> '<p style="color: red">' . esc_html__( 'Use only for installation verification or testing. It breaks GDPR and similar laws.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . sprintf( esc_html__( 'This will load the tracking script for all website visitors, including administrators, bots, excluded users, people browsing from excluded locations and people that didn\'t agree to tracking. %1$sLearn more%2$s.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/support/documentation/validation-mode/?utm_source=fp_admin&utm_medium=fp_link">', '</a>' ) . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track without waiting for consent', 'full-picture-analytics-cookie-notice' ),
				'class'				=> 'fupi_load_opts fupi_adv',
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
				'class'				=> 'fupi_load_opts fupi_adv',
				'is_repeater'		=> false,
				'popup'				=> '<p>' . sprintf( esc_html__('Enter a list of 2-character %1$scountry codes%2$s separated by comas.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://www.iban.com/country-codes">', '</a>' ) . '</p><p>'. esc_html__('Location is checked using the method chosen in the settings of the Geolocation module.', 'full-picture-analytics-cookie-notice' ) . '</p>',
				'fields'			=> array(
					array(
						'type'				=> 'select',
						'field_id'			=> 'method',
						'options'			=> array(
							'excl'				=> esc_html__('All except','full-picture-analytics-cookie-notice'),
							'incl'				=> esc_html__('Only in','full-picture-analytics-cookie-notice'),
						),
						'class'		=> 'fupi_col_20',
					),
					array(
						'type'				=> 'text',
						'field_id'			=> 'countries',
						'placeholder'		=> esc_html__('e.g. GB, DE, FR, AU, etc.','full-picture-analytics-cookie-notice'),
					),
				),
			),
		),
	),

	// EVENT TRACKING

	array(
		'section_id' => 'fupi_tik_events',
		'section_title' => esc_html__( 'Tracking simple events', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track clicks on email and tel. links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_email_tel',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup2'			=> '<p class="fupi_warning_text">' . esc_html__( 'When this option is enabled, all clicks on email and phone links will be tracked and sent to TikTok. However, due to TikToks tracking limitations, the event will NOT contain information WHICH link was clicked.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track clicks on file download links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_file_downl',
				'class'				=> 'fupi_adv',
				'placeholder'		=> 'pdf, doc, docx, xls, xlsx, txt',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_file_downl]',
				'under field'		=> esc_html__( 'Enter coma separated list of file formats you want to track', 'full-picture-analytics-cookie-notice'),
				'popup2'			=> '<p class="fupi_warning_text">' . esc_html__( 'When this option is enabled, all downloads of files in the provided formats will be tracked and sent to TikTok. However, due to TikToks tracking limitations, the event will NOT contain information WHICH file was downloaded.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track clicks on page elements', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_elems',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3 fupi_adv',
				'btns_class'		=> 'fupi_push_right',
				'popup2'			=> '<h3>' . esc_html__( 'How to fill in these fields', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<ol>
						<li>' . esc_html__( 'You can enter more then 1 selector in a "CSS selector" field, e.g. .button, .different-button, .another-button.', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'If multiple selectors point at the same element only the first match will be tracked.', 'full-picture-analytics-cookie-notice' ) . '</li>
					</ol>
					<h3>' . esc_html__( 'Attention!', 'full-picture-analytics-cookie-notice') . '</h3>
					<p class="fupi_warning_text">' . esc_html__( 'To correctly track clicks in page elements OTHER than links (e.g. buttons), you need to provide CSS selectors of ALL clickable elements inside that element.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'The easiest way to do it is to use the asterisk symbol "*". For example, to track clicks in buttons provide:', 'full-picture-analytics-cookie-notice' ) . ' <code>.my_button, .my_button *</code>.</p>
					<p><a href="https://wpfullpicture.com/support/documentation/how-to-track-clicks-in-page-page-elements/" target="_blank">' . esc_html__( 'Learn more about tracking clicks', 'full-picture-analytics-cookie-notice' ) . '</a></p>
					<h3>' . esc_html__( 'How TikTok will track it', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p class="fupi_warning_text">' . esc_html__( 'All clicks on page elements will be tracked and sent to TikTok as a single event "ClickButton".', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'However, this event will NOT contain context WHICH page element was clicked. To do this, you need to send to TikTok Pixel a custom event.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p><a href="https://wpfullpicture.com/support/documentation/tracking-events-with-tiktok-pixel/" class="button-secondary" target="_blank">' . esc_html__( 'How to create custom events in WP FP', 'full-picture-analytics-cookie-notice' ) . '</a></p>',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'CSS selector e.g. #sth img', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'your_custom_event_name (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				)
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track form submissions', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_forms',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3 fupi_adv',
				'btns_class'		=> 'fupi_push_right',
				'popup2'			=> '<p class="fupi_warning_text">' . esc_html__( 'There are 4 methods of tracking form. Please choose the one that is best suited for your forms. Otherwise form tracking may not work correctly' , 'full-picture-analytics-cookie-notice' ) . '<p>
					<p><a class="button-secondary" target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-choose-the-best-way-to-track-form-submissions/">' . esc_html__( 'Choose correct method to track your forms.' , 'full-picture-analytics-cookie-notice' ) . '</a></p>
					<p>' . esc_html__( 'When this option is enabled, all form submissions will be tracked and sent to TikTok as a single event "SubmitForm".', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'However, this event will NOT contain context WHICH form was submitted. To do this, you need to send to TikTok Pixel a custom event.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p><a href="https://wpfullpicture.com/support/documentation/tracking-events-with-tiktok-pixel/" class="button-secondary" target="_blank">How to create custom events in WP FP</a></p>',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'CSS selector e.g. #form_id', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'your_custom_event_name (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				)
			),
		),
	),
	
	// WOOCOMMERCE

	array(
		'section_id' => 'fupi_tik_ecomm',
		'section_title' => esc_html__( 'WooCommerce tracking', 'full-picture-analytics-cookie-notice' ),
	)
);

?>
