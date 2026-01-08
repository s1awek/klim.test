<?php

$option_arr_id = 'fupi_fbp1';

$sections = array(

	// BASIC SETUP

	array(
		'section_id' => 'fupi_fbp1_install',
		'section_title' => esc_html__( 'Installation', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Pixel ID / Dataset ID', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'pixel_id',
				'class'				=> 'fupi_required',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[pixel_id]',
				'under field'		=> '<p><a href="https://wpfullpicture.com/support/documentation/how-to-install-meta-pixel/">' . esc_html__('See where to find it', 'full-picture-analytics-cookie-notice') . '</a></p>' ,
			),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'Conversion API token (for server-side tracking)', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'capi_token',
					'class'				=> 'fupi_sub',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[capi_token]',
					'popup'				=> '<p>' . esc_html__( 'After you enable Conversion API, the data you track, will be sent to Meta by both, the tracking pixel and your server. This way, you will be able to track visitors who use ad blockers (they block tracking pixels). Enabling Conversion API will put a strain on your server, requiring it to do additional work. Do not enable Conversion API on hosting that is at its limits.', 'full-picture-analytics-cookie-notice' ) . '</p>
						<p><a href="https://wpfullpicture.com/support/documentation/3-ways-to-test-and-debug-meta-pixel-integration/">' . esc_html__('See the installation guide', 'full-picture-analytics-cookie-notice') . '</a></p>',
				),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'Test event code', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'test_code',
					'class'				=> 'fupi_sub',
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[test_code]',
					'popup' 			=> esc_html__( 'Use this option if you want to test the events in the events manager. You can find your test code in the "Facebook Events Manager" > "Test events" section. All the events that happen, will be visible on the "Test events" page for 24 hours after creation. Remember to remove this code before going live.', 'full-picture-analytics-cookie-notice' ),
				),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Install 2nd pixel (optional)', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'extra_install',
				'must_have'			=> 'pro',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_2nd_opts',
				'option_arr_id'		=> $option_arr_id,
				'after field'		=> esc_html__( 'Both pixels will track the same data and use the same settings.', 'full-picture-analytics-cookie-notice' ),
			),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'Pixel ID / Dataset ID #2', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'pixel_id_2',
					'class'				=> 'fupi_sub fupi_2nd_opts fupi_disabled',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[pixel_id_2]',
				),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'Conversion API token #2', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'capi_token_2',
					'class'				=> 'fupi_sub fupi_2nd_opts fupi_disabled',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[capi_token_2]',
				),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'Test event code #2', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'test_code_2',
					'class'				=> 'fupi_sub fupi_2nd_opts fupi_disabled',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[test_code_2]',
				),
		),
	),

	// LOADING SETUP

	array(
		'section_id' => 'fupi_fbp1_loading',
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
				<p>' . sprintf( esc_html__( 'This will load the tracking script for administrators, bots, excluded users, people browsing from excluded locations and people who didn\'t agree to tracking. %1$sLearn more%2$s.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/support/documentation/validation-mode/?utm_source=fp_admin&utm_medium=fp_link">', '</a>' ) . '</p>',
				),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track without waiting for consent', 'full-picture-analytics-cookie-notice' ),
				'class'				=> 'fupi_load_opts fupi_adv',
				'field_id' 			=> 'disreg_cookies',
				'must_have'			=> 'cook',
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
				'is repeater'		=> false,
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

	// BASIC SETTINGS

	array(
		'section_id' => 'fupi_fbp1_basic',
		'section_title' => esc_html__( 'Data collection', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Advanced matching', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'adv_match',
				'must_have'			=> 'pro',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_adv_match_opts',
				'option_arr_id'		=> $option_arr_id,
				'popup' 			=> '<p>' . esc_html__( 'Advanced matching improves the accuracy of conversion tracking by sending to Meta personally identifiable information about your users.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'When you enable Advanced Matching, WP Full Picture will start sending to Meta encrypted addresses, email addresses, phone numbers and user identifiers of your visitors.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'The data will be sent on all pages where your visitor is logged in and on the WooCommerce order confirmation page. The type and amount of sent data depends on what is known about the visitors at a given moment.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__('Advanced External ID', 'full-picture-analytics-cookie-notice'),
					'under field'		=> esc_html__('Provide comma-separated CSS selectors of form fields, where users are the most likely to leave the same email address as they used to register to this website.', 'full-picture-analytics-cookie-notice'),
					'field_id' 			=> 'user_email_fields',
					'class'				=> 'fupi_adv fupi_adv_match_opts fupi_sub fupi_disabled',
					'option_arr_id'		=> $option_arr_id,
					'popup' 			=> '<p>' . esc_html__('This function improves cross-browser conversion attribution and event match quality score.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__('It generates External IDs for users who are not logged-in, based on email addresses they provide in form fields on your site.', 'full-picture-analytics-cookie-notice') . '</p>
					<p><a href="https://wpfullpicture.com/support/documentation/what-is-advanced-external-id/" target="_blank">' . esc_html__('Learn more', 'full-picture-analytics-cookie-notice') . '</a></p>',
				),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Enable "Limited Data Use" for visitors from the US', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'limit_data_use',
				'after field'		=> esc_html__( 'required for compliance with privacy laws in some US states', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . sprintf( esc_html__( 'WP Full Picture will modify Pixel code to comply with the data processing regulations in Colorado, Connecticut and California (US). This will negatively impact data accuracy, campaign performance and retargeting done on the visitors from these states. Users from other states and countries will not be affected. %1$sLearn more%2$s.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://developers.facebook.com/docs/meta-pixel/implementation/data-processing-options">', '</a>' ) . '</p>',
			),
		),
	),

	// EVENTS

	array(
		'section_id' => 'fupi_fbp1_events',
		'section_title' => esc_html__( 'Simple events', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track clicks on outbound links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_outbound',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . esc_html__( 'This will track clicks on all the links that lead to other domains. Attention! Affiliate links leading to other sites are also treated as outbound.', 'full-picture-analytics-cookie-notice') . '</p><p>' . esc_html__( 'Tracked as a parameter "url" of the "outbound" event', 'full-picture-analytics-cookie-notice' ) . '</p>'
			),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Also track with Conversion API', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_outbound_capi',
					'class'				=> 'fupi_sub fupi_adv',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'popup_id'			=> 'fupi_servertrack_info_popup'
				),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track clicks on affiliate links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_affiliate',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3 fupi_adv',
				'btns_class'		=> 'fupi_push_right',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'URL part, e.g. /go/', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Name (optional)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
					),
				),
				'popup' 			=> '<p>' . esc_html__( 'In the second field you can also use a placeholder [name]. It will be replaced with the first 20 characters of the text inside the clicked element. Make sure it has any.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'These clicks will be tracked as a parameter "target" of the "affiliate" event', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Also track with Conversion API', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_affiliate_capi',
					'class'				=> 'fupi_sub fupi_adv',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'popup_id'			=> 'fupi_servertrack_info_popup'
				),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track clicks on file download links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_file_downl',
				'class'				=> 'fupi_adv',
				'placeholder'		=> 'pdf, doc, docx, xls, xlsx, txt',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_file_downl]',
				'popup'				=> '<p>' . esc_html__( 'Enter coma separated list of file formats (extensions) you want to track', 'full-picture-analytics-cookie-notice') . '</p><p>' . esc_html__( 'Tracked as a parameter "file" of the "file download" event', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Also track with Conversion API', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_file_downl_capi',
					'class'				=> 'fupi_sub fupi_adv',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'popup_id'			=> 'fupi_servertrack_info_popup'
				),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track clicks on email and tel. links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_email_tel',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup'		 		=> '<p>' . esc_html__( 'It will track the last 5 digits of the phone number and the part of the email address before the "@" symbol.', 'full-picture-analytics-cookie-notice' ) . '</p><p>' . esc_html__( 'Tracked as parameters "target" and "type" of the "Contact" event', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Also track with Conversion API', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_email_tel_capi',
					'class'				=> 'fupi_sub fupi_adv',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'popup_id'			=> 'fupi_servertrack_info_popup'
				),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track clicks on page elements', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_elems',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3 fupi_adv',
				'btns_class'		=> 'fupi_push_right',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'CSS selector e.g. #sth img', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Element name (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				),
				'popup2'			=> '<p>' . esc_html__( 'These clicks will be tracked as a parameter "name" of the "click on element" event', 'full-picture-analytics-cookie-notice' ) . '</p>
				<h3>' . esc_html__( 'How to fill in these fields', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<ol>
						<li>' . esc_html__( 'You can enter more then 1 selector in a "CSS selector" field, e.g. .button, .different-button, .another-button.', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'If multiple selectors point at the same element only the first match will be tracked.', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'In the second field you can also use a placeholder [name]. It will be replaced with the first 20 characters of the text inside the clicked element. Make sure it has any.', 'full-picture-analytics-cookie-notice' ) . '</li>
					</ol>
					<h3>' . esc_html__( 'Attention!', 'full-picture-analytics-cookie-notice') . '</h3>
					<p class="fupi_warning_text">' . esc_html__( 'To correctly track clicks in page elements OTHER than links (e.g. buttons), you need to provide CSS selectors of ALL clickable elements inside that element.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'The easiest way to do it is to use the asterisk symbol "*". For example, to track clicks in buttons provide:', 'full-picture-analytics-cookie-notice' ) . ' <code>.my_button, .my_button *</code>.</p>
					<p><a href="https://wpfullpicture.com/support/documentation/how-to-track-clicks-in-page-page-elements/" target="_blank">' . esc_html__( 'Learn more about tracking clicks', 'full-picture-analytics-cookie-notice' ) . '</a></p>',
			),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Also track with Conversion API', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_elems_capi',
					'class'				=> 'fupi_sub fupi_adv',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'popup_id'			=> 'fupi_servertrack_info_popup',
				),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track form submissions', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_forms',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3 fupi_adv',
				'btns_class'		=> 'fupi_push_right',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'CSS selector e.g. #form_id', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Form name (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				),
				'popup2'			=> '<p class="fupi_warning_text">' . esc_html__( 'There are 4 methods of tracking form. Please choose the one that is best suited for your forms. Otherwise form tracking may not work correctly' , 'full-picture-analytics-cookie-notice' ) . '<p>
					<p><a class="button-secondary" target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-choose-the-best-way-to-track-form-submissions/">' . esc_html__( 'Choose correct method to track your forms.' , 'full-picture-analytics-cookie-notice' ) . '</a></p>
					<p>' . esc_html__( 'Tracked as a parameter "form" of the "form submit" event', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Also track with Conversion API', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_forms_capi',
					'class'				=> 'fupi_sub fupi_adv',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'popup_id'			=> 'fupi_servertrack_info_popup'
				),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track when page elements show on screen', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_views',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3 fupi_adv',
				'btns_class'		=> 'fupi_push_right',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'CSS selector e.g. .side img', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'				=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Element name (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'				=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				),
				'popup2'				=> '<p class="fupi_warning_text">' . esc_html__( 'This function works only on elements which are present in the HTML at the moment of rendering the page. To track elements added later, enable the "DOM listener" function in the Shared tracking settings > Tracking improvements.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__( 'Elements are treated as "visible" when they are 200px inside the screen (you can change it on the "shared tracking settings" page). Each view is counted once per page view.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__( 'Tracked as a parameter "element" of the "user viewed" event', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Also track with Conversion API', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_views_capi',
					'class'				=> 'fupi_sub fupi_adv',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'popup_id'			=> 'fupi_servertrack_info_popup'
				),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track when visitors scroll to:', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_scroll',
				'class'				=> 'fupi_adv',
				'placeholder'		=> esc_html__( 'e.g. 25, 50, 75', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_scroll]',
				'after field'		=> esc_html__( '% of page height', 'full-picture-analytics-cookie-notice'),
				'popup'				=> '<p>' . esc_html__( 'Separate multiple values with comas. Do not use "%" symbol.', 'full-picture-analytics-cookie-notice')  . '</p>
				<p>' . esc_html__( 'Tracked as a parameter "scroll height" of the "scroll" event', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Also track with Conversion API', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_scroll_capi',
					'class'				=> 'fupi_sub fupi_adv',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'popup2'			=> '<p class="fupi_warning_text">' . esc_html__( 'Tracking scroll with server can greatly decrease its performance. It is NOT recommended to enable this function on slow hosting.', 'full-picture-analytics-cookie-notice') . '</p>'
				),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track how long the user was actively engaged with the content', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_engagement',
				'class'				=> 'fupi_adv',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__( 'Tracked as a parameter "seconds" of the "user engagement time" event', 'full-picture-analytics-cookie-notice' ),
			),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Also track with Conversion API', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_engagement_capi',
					'class'				=> 'fupi_sub fupi_adv',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'popup2'			=> '<p class="fupi_warning_text">' . esc_html__( 'Tracking engagement time with server will decrease its performance. It is NOT recommended to enable this function on slow hosting.', 'full-picture-analytics-cookie-notice') . '</p>'
				),
		),
	),

	// CUSTOM EVENTS

	array(
		'section_id' => 'fupi_fbp1_atrig',
		'section_title' => esc_html__( 'Custom events', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track when specific conditions are met', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'custom_events',
				'must_have'			=> 'pro atrig',
				'class'				=> 'fupi_events_builder fupi_fullwidth_tr fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'fields'			=> array(
					array(
						'label'				=> esc_html__( 'When this happens...', 'full-picture-analytics-cookie-notice' ),
						'type' 				=> 'atrig_select',
						'field_id'			=> 'atrig_id',
						'class'				=> 'fupi_col_30',
						'required'			=> true,
					),
					array(
						'type'	 			=> 'select',
						'label' 			=> esc_html__( '...for...', 'full-picture-analytics-cookie-notice' ),
						'field_id' 			=> 'repeat',
						'option_arr_id'		=> $option_arr_id,
						'class'				=> 'fupi_col_20',
						'options'			=> array(
							'no'				=> esc_html__( 'The first time', 'full-picture-analytics-cookie-notice' ),
							'yes'				=> esc_html__( 'Every time', 'full-picture-analytics-cookie-notice' ),
						),
					),
					array(
						'type'	 			=> 'select',
						'label' 			=> esc_html__( '...send', 'full-picture-analytics-cookie-notice' ),
						'field_id' 			=> 'evt_type',
						'option_arr_id'		=> $option_arr_id,
						'class'				=> 'fupi_col_25',
						'options'			=> array(
							'custom'				=> esc_html__( 'Custom event', 'full-picture-analytics-cookie-notice' ),
							'standard'				=> esc_html__( 'Meta standard event', 'full-picture-analytics-cookie-notice' ),
						),
					),
					array(
						'type'				=> 'text',
						'label'				=> esc_html__( '...with event name', 'full-picture-analytics-cookie-notice' ),
						'placeholder'		=> esc_html__( 'event_name', 'full-picture-analytics-cookie-notice' ),
						'field_id'			=> 'evt_name',
						'el_class'			=> 'fupi_events_builder_evt',
						'required'			=> true,
						'class'				=> 'fupi_col_25',
					),
					array(
						'type'				=> 'r3',
						'label'				=> esc_html__( 'Event parameters (optional)', 'full-picture-analytics-cookie-notice' ),
						'field_id'			=> 'params',
                        'is_repeater'		=> true,
						'class'		        => 'fupi_col_100 fupi_simple_r3',
						'fields'			=> array(
                            array(
                                'placeholder'		=> esc_html__( 'Parameter name', 'full-picture-analytics-cookie-notice' ),
                                'type'				=> 'text',
                                'field_id'			=> 'name',
                                'class'		        => 'fupi_col_30',
                            ),
							array(
								'type'	 			=> 'select',
								'field_id' 			=> 'type',
								'option_arr_id'		=> $option_arr_id,
								'class'				=> 'fupi_col_20',
								'options'			=> array(
									''					=> esc_html__( 'Value type', 'full-picture-analytics-cookie-notice' ),
									'string'			=> esc_html__( 'Text', 'full-picture-analytics-cookie-notice' ),
									'number'			=> esc_html__( 'Number', 'full-picture-analytics-cookie-notice' ),
									'bool'				=> esc_html__( 'true/false', 'full-picture-analytics-cookie-notice' ),
									'path'			=> esc_html__( 'Path to a JS value', 'full-picture-analytics-cookie-notice' ),
								),
							),
							array(
                                'placeholder'		=> esc_html__( 'Parameter value', 'full-picture-analytics-cookie-notice' ),
                                'type'				=> 'text',
                                'field_id'			=> 'val',
                                'class'		        => 'fupi_col_30',
                            ),
						)
					),
					array(
						'type'	 			=> 'toggle',
						'label' 			=> esc_html__( 'Also track with CAPI', 'full-picture-analytics-cookie-notice' ),
						'field_id' 			=> 'capi',
						'option_arr_id'		=> $option_arr_id,
						'class'				=> 'fupi_col_20',
					),
				),
			)
		),
	),

	// EVENT PARAMS

	array(
		'section_id' => 'fupi_fbp1_wpdata',
		'section_title' => esc_html__( 'Event parameters', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track page type', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_pagetype',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track page title', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_pagetitle',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track archive and page numbers', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_pagenum',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track page language', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'page_lang',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track post and page publish dates', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_pobdate',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track page id', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_pageid',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track post\'s terms (categories, tags, etc.) ', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_terms',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_adv',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_track_terms_opts',
				'popup'				=> '<p>' . esc_html__('By default WP Full Picture tracks categories, tags and formats of posts and pages. You can enable tracking other terms in the "Shared tracking settings" > "Default settings".','full-picture-analytics-cookie-notice') . '</p>',
			),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Add taxonomy slug to term name', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'add_tax_term_cat',
					'option_arr_id'		=> $option_arr_id,
					'class'				=> 'fupi_sub fupi_track_terms_opts fupi_disabled fupi_adv',
					'popup'				=> '<p>' . esc_html__( 'This will add information about a taxonomy, to the term information sent to Meta, e.g. "european music (tag)"', 'full-picture-analytics-cookie-notice') . '</p>',
				),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Send term names instead of term slugs', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'send_tax_terms_titles',
					'option_arr_id'		=> $option_arr_id,
					'class'				=> 'fupi_sub fupi_track_terms_opts fupi_disabled fupi_adv',
					'popup'				=> '<p>' . esc_html__( 'This will send term names (e.g. The best of european music) instead of term slugs (e.g. eu_music). Doing this is not recommended since term names can sometimes be changed while slugs are changed only on rare occasions.', 'full-picture-analytics-cookie-notice') . '</p>',
				),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track author\'s display names', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_author',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track searched phrases', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_search',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track user\'s login status and role', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_user_role',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__( 'Tracked as a parameter "user_type" of the "PageView" event', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track visitor\'s browser language', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_lang',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__( 'Tracked as a parameter "browser_lang" of the "PageView" event', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track metadata', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_cf',
				'must_have'			=> 'pro',
				'class'				=> 'fupi_metadata_tracker fupi_simple_r3 fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'fields'			=> array(
					array(
						'type'				=> 'custom_meta_select',
						'field_id'			=> 'id',
						'required'			=> true,
					),
				),
				'popup2'				=> '<p>' . esc_html__( 'This setting lets you track metadata (hidden and/or custom data of your content, users and post/page terms).', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . esc_html__( 'To track metadata you need to register it in the "Shared tracking settings" > "Extra tracking functions". After you do this, refresh this page and choose what you want to track.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
		)
	),

	// WOO

	array(
		'section_id' => 'fupi_fbp1_ecomm',
		'section_title' => esc_html__( 'Tracking WooCommerce', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Status-Based Order Tracking with Conversion API', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'adv_orders',
				'must_have'			=> 'pro woo', //field|fupi_fbp1|capi_token|exists|' . esc_html__("Conversion_API_token")
				'option_arr_id'		=> $option_arr_id,
				'under field'		=> esc_html__( 'This feature requires the use of Conversion API. Please check if you entered its key in the "Installation" section.', 'full-picture-analytics-cookie-notice' ),
				'popup'				=> '<p>' . esc_html__( 'Status-Based Order Tracking is an alternative method of tracking purchases. Instead of tracking them on order confirmation pages, orders are tracked when their status changes.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . sprintf( esc_html__( 'You can learn how it works %1$son this page%2$s.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/support/documentation/what-you-need-to-know-about-status-based-order-tracking/">', '</a>' ) . '</p>',
			),
		),
	),
);

?>
