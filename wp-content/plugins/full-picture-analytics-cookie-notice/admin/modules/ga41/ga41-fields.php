<?php

$option_arr_id = 'fupi_ga41';
$ga41_data = get_option('fupi_ga41');

$sections = array(

	// INSTALLATION

	array(
		'section_id' => 'fupi_ga41_install',
		'section_title' => esc_html__( 'Installation', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'GTAG ID', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'id',
				'class'				=> 'fupi_required',
				'required'			=> true,
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[id]',
				'placeholder'		=> esc_html__( 'G-0000000 or GT-0000000', 'full-picture-analytics-cookie-notice'),
				'under field'		=> '<p>' . sprintf( esc_html__( '%1$sLearn where to find it%2$s', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/how-to-install-google-analytics-4/">', '</a>') . '</p>
					<p>' . esc_html__( 'Tip. To pass installation check, enable "Force load" in the "Loading" section. Disable it after the check.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type' 				=> 'toggle',
				'label' 			=> esc_html__( 'Try to avoid conflicts with other Google Analytics installations', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'cookie_prefix',
				'option_arr_id'		=> $option_arr_id,
				'popup2'			=> '<p>' . esc_html__( 'Enable this function if you installed another Google Analytics with a different plugin, the Custom Scripts module or a Google Tag Manager. It will change the cookie prefix of this installation to avoid conflicts.', 'full-picture-analytics-cookie-notice') . '</p>
				<p class="fupi_warning_text">' . esc_html__( 'Using multiple Google Analytics that are installed in different ways is highly discouraged and may cause unexpected tracking issues. Test before using in production.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
		),
	),

	// LOADING

	array(
		'section_id' => 'fupi_ga41_loading',
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
				'type'	 			=> 'r3',
				'label' 			=> esc_html__('Only track visitors from specific countries', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'limit_country',
				'must_have'			=> 'pro geo',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_load_opts',
				'is repeater'		=> false,
				'popup'				=> sprintf( esc_html__('Enter a list of 2-character %1$scountry codes%2$s separated by comas.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://www.iban.com/country-codes">', '</a>' ) . '<br>' . esc_html__('If visitor\'s country is not recognized GA will load normally.', 'full-picture-analytics-cookie-notice' ),
				'fields'			=> array(
					array(
						'type'				=> 'select',
						'field_id'			=> 'method',
						'options'			=> array(
							'excl'				=> esc_html__('All except','full-picture-analytics-cookie-notice'),
							'incl'				=> esc_html__('Only in','full-picture-analytics-cookie-notice'),
						),
						'class'				=> 'fupi_col_20',
					),
					array(
						'type'				=> 'text',
						'field_id'			=> 'countries',
						'placeholder'		=> 'e.g. GB, DE, FR, AU, etc.',
					),
				),
			),
		),
	),

	// Privacy settings

	array(
		'section_id' => 'fupi_ga41_basic',
		'section_title' => esc_html__( 'Data collection settings', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Enhanced Conversions', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'enh_conv',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'under field'		=> esc_html__( 'This has an effect only if you link your Google Analytics with your Google Ads', 'full-picture-analytics-cookie-notice' ),
				'popup'			=> '<p>' . esc_html__( 'Enhanced Conversions improves the accuracy of conversion tracking in Google Ads when it is linked with Google Analytics. This option sends to Google your visitors\' personal information, like their email address, first and last name and physical address. This information is later used by Google to better match the conversions with specific users.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'To implement Enhanced Conversions in Google Ads via Google Analytics follow the steps below.', 'full-picture-analytics-cookie-notice') . '</p>
					<ol>
						<li>' . esc_html__('Go to your Google Analytics panel > Admin (settings page) > Data Collection > and enable "User-provided data collection" ', 'full-picture-analytics-cookie-notice') . '</li>
						<li>' . esc_html__('Go back to the Admin page (settings page) > and link your GA with G Ads in Google Ads links section" ', 'full-picture-analytics-cookie-notice') . '</li>
						<li>' . esc_html__('Enable "Enhanced Conversions" via Google Tag in your Google Ads account. You will find it in "Goals" > "Conversions" > "Settings" > "Enhanced conversions" > and select "Google tag" from the dropdown. If you can\'t see these menu elements, please switch to the new menu using the "Appearance" switch in the top.', 'full-picture-analytics-cookie-notice') . '</li>
						<li>' . sprintf( esc_html__( 'Make sure you agree and comply with Google\'s %1$sCustomer Data policies%2$s, %3$sGoogle Ads Data Processing Terms%2$s and privacy law in your country.', 'full-picture-analytics-cookie-notice' ), '<a href="https://support.google.com/adspolicy/answer/7475709?sjid=6953114821919544275-EU">', '</a>', '<a href="https://business.safety.google/adsprocessorterms/">' ) . '</li>
						<li>' . esc_html__('Add information to your privacy policy that you send personal user data to Google.', 'full-picture-analytics-cookie-notice' ) . '</li>
					</ol>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Cross-device and cross-browser tracking', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'set_user_id',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . esc_html__( 'Identifies logged-in users across devices and browsers by their user WordPress user IDs.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . sprintf( esc_html__( 'You can learn more about it from %1$sGoogle\'s documentation%2$s.', 'full-picture-analytics-cookie-notice'), '<a href="https://support.google.com/analytics/answer/9213390?hl=en" target="_blank">', '</a>') . '</p>',
			),
		),
	),

	// EVENTS

	array(
		'section_id' => 'fupi_ga41_events',
		'section_title' => esc_html__( 'Tracking simple events', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'radio',
				'label' 			=> esc_html__( 'Track clicks on email and tel. links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_email_tel',
				'option_arr_id'		=> $option_arr_id,
				'options' 			=> array(
					''					=> esc_html__( 'Do not track', 'full-picture-analytics-cookie-notice'),
					'evt'				=> esc_html__( 'Track every link with a different event', 'full-picture-analytics-cookie-notice'),
					'params'			=> esc_html__( 'Track every link with the same event but different parameter (advanced)', 'full-picture-analytics-cookie-notice'),
				),
				'popup'				=> '<p>' . esc_html__( 'It will track the last 5 digits of the phone number and the part of the email address before the "@" symbol.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<h3>' . esc_html__( '"Track every link with a different event" option', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . esc_html__( 'When you choose this option, every time someone clicks a contact link WP FP will send to GA a different event. Events will be named according to the format "tel_clicked_[last 5 digits]" and "email_clicked_[email part before @]".', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'This option is recommended if you do not have many phone and email links on your website and/or you are not an advanced GA user.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<h3>' . esc_html__( '"Track as one event with different parameters" option', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . esc_html__( 'When you choose this option, WP FP will send to your GA events "email_link_click" and "tel_link_click".', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . sprintf ( esc_html__( 'To see information on what links were clicked, you need to %3$sregister a custom dimension in GA%4$s with event parameter %1$scontact_click%2$s and build a custom report.', 'full-picture-analytics-cookie-notice') , ' <span style="background: #fdf3ce;">', '</span>', '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-custom-definitions-in-google-analytics-4/?utm_source=fp_admin&utm_medium=fp_link">', '</a>' ) . '</p>
					<p>' . esc_html__( 'This option is recommended if you have many different contact links on the website and you are an advanced GA user.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
			array(
				'type'	 			=> 'radio',
				'label' 			=> esc_html__( 'Track clicks on affiliate links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_affil_method',
				'option_arr_id'		=> $option_arr_id,
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_affil_cond',
				'options' 			=> array(
					''					=> esc_html__( 'Do not track', 'full-picture-analytics-cookie-notice'),
					'evt'				=> esc_html__( 'Track every link with a different event', 'full-picture-analytics-cookie-notice'),
					'params'			=> esc_html__( 'Track every link with the same event but different parameter (advanced)', 'full-picture-analytics-cookie-notice'),
				),
				'popup'				=> '<p>' . esc_html__( 'Enable this function to track clicks on affiliate links.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<h3>' . esc_html__( '"Track every link with a different event" option', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . sprintf( esc_html__( 'When you choose this option, every time someone clicks an affiliate link specified in the fields below, WP FP will send to GA an event with a name specific for this link. The event names must follow %1$sthese naming rules%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://support.google.com/analytics/answer/13316687?hl=en#zippy=%2Cweb" target="_blank">', '</a>' ) . '</p>
					<p>' . esc_html__( 'This option is recommended if you do not intend to set many event names and/or you are not an advanced GA user.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<h3>' . esc_html__( '"Track as one event with different parameters" option', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . esc_html__( 'When you choose this option, every time someone clicks an affiliate link, WP FP will send to your GA event "affiliate_link_click".', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'Information about the names of the links will be sent to GA as event parameters.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . sprintf ( esc_html__( 'To see these parameters / names of clicked links in your GA reports, you need to %3$sregister a custom dimension in GA%4$s with event parameter %1$saffiliate_link_click%2$s and build a custom report.', 'full-picture-analytics-cookie-notice') , ' <span style="background: #fdf3ce;">', '</span>', '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-custom-definitions-in-google-analytics-4/?utm_source=fp_admin&utm_medium=fp_link">', '</a>' ) . '</p>
					<p>' . esc_html__( 'This option is recommended if you have many different affiliate links on the website and you are an advanced GA user.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
				array(
					'type'	 			=> 'r3',
					'label' 			=> esc_html__( 'Links', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_affiliate',
					'class'				=> 'fupi_simple_r3 fupi_sub fupi_affil_cond fupi_cond_val_evt fupi_cond_val_params fupi_disabled',
					'option_arr_id'		=> $option_arr_id,
					'is_repeater'		=> true,
					'btns_class'		=> 'fupi_push_right',
					'fields'			=> array(
						array(
							'placeholder'		=> esc_html__( 'Url part, e.g. /go/', 'full-picture-analytics-cookie-notice' ),
							'type'				=> 'text',
							'field_id'			=> 'sel',
							'class'				=> 'fupi_col_35_grow',
							'required'			=> true,
						),
						array(
							'placeholder'		=> esc_html__( 'Event name or link name', 'full-picture-analytics-cookie-notice' ),
							'type'				=> 'text',
							'field_id'			=> 'val',
							'class'				=> 'fupi_col_35_grow',
							'required'			=> true,
						),
					),
					'popup'				=> '<p>' . esc_html__( 'If you chose an option to track clicks with different parameters, then in the second field you can also use a placeholder [name]. It will be replaced with the first 20 characters of the text inside the clicked element. Make sure it has any.', 'full-picture-analytics-cookie-notice' ) . '</p>',
				),
			array(
				'type'	 			=> 'radio',
				'label' 			=> esc_html__( 'Track clicks on page elements', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_elems_method',
				'option_arr_id'		=> $option_arr_id,
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_elems_cond',
				'options' 			=> array(
					''					=> esc_html__( 'Do not track', 'full-picture-analytics-cookie-notice'),
					'evt'				=> esc_html__( 'Track every element with a different event', 'full-picture-analytics-cookie-notice'),
					'params'			=> esc_html__( 'Track every element with the same event but different parameter (advanced)', 'full-picture-analytics-cookie-notice'),
				),
				'popup'				=> '<p>' . esc_html__( 'Enable this function to track clicks on page elements.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<h3>' . esc_html__( '"Track every element with a different event" option', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . sprintf( esc_html__( 'When you choose this option, every time someone clicks a page element specified in the fields below, WP FP will send to GA an event with a name specific for this element. The event names must follow %1$sthese naming rules%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://support.google.com/analytics/answer/13316687?hl=en#zippy=%2Cweb" target="_blank">', '</a>' ) . '</p>
					<p>' . esc_html__( 'This option is recommended if you do not intend to set many event names and/or you are not an advanced GA user.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<h3>' . esc_html__( '"Track as one event with different parameters" option', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . esc_html__( 'When you choose this option, every time someone clicks an element, WP FP will send to your GA event "element_click".', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'Information about the names of the clicked elements will be sent to GA as event parameters.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . sprintf ( esc_html__( 'To see these parameters / names in your GA reports, you need to %3$sregister a custom dimension in GA%4$s with event parameter %1$selement_click%2$s and build a custom report.', 'full-picture-analytics-cookie-notice') , ' <span style="background: #fdf3ce;">', '</span>', '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-custom-definitions-in-google-analytics-4/?utm_source=fp_admin&utm_medium=fp_link">', '</a>' ) . '</p>
					<p>' . esc_html__( 'This option is recommended if you want to track clicks on many different elements on the website and you are an advanced GA user.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
				array(
					'type'	 			=> 'r3',
					'label' 			=> esc_html__( 'Page elements', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_elems',
					'option_arr_id'		=> $option_arr_id,
					'is_repeater'		=> true,
					'class'				=> 'fupi_simple_r3 fupi_sub fupi_elems_cond fupi_cond_val_evt fupi_cond_val_params fupi_disabled',
					'btns_class'		=> 'fupi_push_right',
					'popup2'			=> '<h3>' . esc_html__( 'How to fill in these fields', 'full-picture-analytics-cookie-notice' ) . '</h3>
						<ol>
							<li>' . esc_html__( 'You can enter more then 1 selector in a "CSS selector" field, e.g. .button, .different-button, .another-button.', 'full-picture-analytics-cookie-notice' ) . '</li>
							<li>' . esc_html__( 'If multiple selectors point at the same element only the first match will be tracked.', 'full-picture-analytics-cookie-notice' ) . '</li>
							<li>' . esc_html__( 'If you are tracking events with a single event name but different parameters, you can add in the "name" field a placeholder [name]. It will be replaced with the first 20 characters of the text inside the clicked element. Make sure it has any.', 'full-picture-analytics-cookie-notice' ) . '</li>
						</ol>
						<h3>' . esc_html__( 'Attention!', 'full-picture-analytics-cookie-notice') . '</h3>
						<p class="fupi_warning_text">' . esc_html__( 'To correctly track clicks in page elements OTHER than links (e.g. buttons), you need to provide CSS selectors of ALL clickable elements inside that element.', 'full-picture-analytics-cookie-notice' ) . '</p>
						<p>' . esc_html__( 'The easiest way to do it is to use the asterisk symbol "*". For example, to track clicks in buttons provide:', 'full-picture-analytics-cookie-notice' ) . ' <code>.my_button, .my_button *</code>.</p>
						<p><a href="https://wpfullpicture.com/support/documentation/how-to-track-clicks-in-page-page-elements/" target="_blank">' . esc_html__( 'Learn more about tracking clicks', 'full-picture-analytics-cookie-notice' ) . '</a></p>',
					'fields'			=> array(
						array(
							'placeholder'		=> esc_html__( 'CSS selector e.g. #sth img', 'full-picture-analytics-cookie-notice' ),
							'type'				=> 'text',
							'field_id'			=> 'sel',
							'class'				=> 'fupi_col_35_grow',
							'required'			=> true,
						),
						array(
							'placeholder'		=> esc_html__( 'Event name or element name', 'full-picture-analytics-cookie-notice' ),
							'type'				=> 'text',
							'field_id'			=> 'val',
							'class'				=> 'fupi_col_35_grow',
							'required'			=> true,
						),
					)
				),
			array(
				'type'	 			=> 'radio',
				'label' 			=> esc_html__( 'Track form submissions', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_forms_method',
				'option_arr_id'		=> $option_arr_id,
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_forms_cond',
				'options' 			=> array(
					''					=> esc_html__( 'Do not track', 'full-picture-analytics-cookie-notice'),
					'evt'				=> esc_html__( 'Track every form with a different event', 'full-picture-analytics-cookie-notice'),
					'params'			=> esc_html__( 'Track every form with the same event but different parameter (advanced)', 'full-picture-analytics-cookie-notice'),
				),
				'popup2_id'			=> 'fupi_track_forms_popup',
			),
				array(
					'type'	 			=> 'r3',
					'label' 			=> esc_html__( 'Forms', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_forms',
					'option_arr_id'		=> $option_arr_id,
					'is_repeater'		=> true,
					'class'				=> 'fupi_simple_r3 fupi_sub fupi_forms_cond fupi_cond_val_evt fupi_cond_val_params fupi_disabled',
					'btns_class'		=> 'fupi_push_right',
					'fields'			=> array(
						array(
							'placeholder'		=> esc_html__( 'CSS selector e.g. #form_id', 'full-picture-analytics-cookie-notice' ),
							'type'				=> 'text',
							'field_id'			=> 'sel',
							'class'				=> 'fupi_col_35_grow',
							'required'			=> true,
						),
						array(
							'placeholder'		=> esc_html__('Event name or form name','full-picture-analytics-cookie-notice'),
							'type'				=> 'text',
							'field_id'			=> 'val',
							'class'				=> 'fupi_col_35_grow',
							'required'			=> true,
						),
					)
				),
			array(
				'type'	 			=> 'radio',
				'label' 			=> esc_html__( 'Track when visitors see specific page elements', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_views_method',
				'option_arr_id'		=> $option_arr_id,
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_elemview_cond',
				'options' 			=> array(
					''					=> esc_html__( 'Do not track', 'full-picture-analytics-cookie-notice'),
					'evt'				=> esc_html__( 'Track every element with a different event', 'full-picture-analytics-cookie-notice'),
					'params'			=> esc_html__( 'Track every element with the same event but different parameter (advanced)', 'full-picture-analytics-cookie-notice'),
				),
				'popup2'			=> '<p class="fupi_warning_text">' . esc_html__( 'This function works only on elements which are present in the HTML at the moment of rendering the page. To track elements added later, enable the "DOM listener" function in the Shared tracking settings > Tracking improvements.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__( 'Enable this function to track when specific page elements are visible to the visitor.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'Elements are treated as "visible" when they are 200px inside the screen (you can change it on the "shared tracking settings" page). Each view is counted once per page view.', 'full-picture-analytics-cookie-notice') . '</p>
					<h3>' . esc_html__( '"Track every element with a different event" option', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . sprintf( esc_html__( 'When you choose this option, every time someone sees a page element you specify, WP FP will send to GA an event with a name for this element. The event names must follow %1$sthese naming rules%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://support.google.com/analytics/answer/13316687?hl=en#zippy=%2Cweb" target="_blank">', '</a>' ) . '</p>
					<p>' . esc_html__( 'This option is recommended if you do not intend to set many event names and/or you are not an advanced GA user.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<h3>' . esc_html__( '"Track as one event with different parameters" option', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . esc_html__( 'When you choose this option, every time someone sees a page elements, WP FP will send to your GA event "element_view".', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'Information about the names of the seen elements will be sent to GA as event parameters.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . sprintf ( esc_html__( 'To see these parameters / names in your GA reports, you need to %3$sregister a custom dimension in GA%4$s with event parameter %1$sviewed_element%2$s and build a custom report.', 'full-picture-analytics-cookie-notice') , ' <span style="background: #fdf3ce;">', '</span>', '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-custom-definitions-in-google-analytics-4/?utm_source=fp_admin&utm_medium=fp_link">', '</a>' ) . '</p>
					<p>' . esc_html__( 'This option is recommended if you want to track clicks on many different elements on the website and you are an advanced GA user.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
				array(
					'type'	 			=> 'r3',
					'label' 			=> esc_html__( 'Viewed elements', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_views',
					'option_arr_id'		=> $option_arr_id,
					'is_repeater'		=> true,
					'class'				=> 'fupi_simple_r3 fupi_sub fupi_elemview_cond fupi_cond_val_evt fupi_cond_val_params fupi_disabled',
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
							'placeholder'		=> esc_html__( 'Event name or element name (required)', 'full-picture-analytics-cookie-notice' ),
							'type'				=> 'text',
							'field_id'			=> 'val',
							'class'				=> 'fupi_col_35_grow',
							'required'			=> true,
						),
					)
				),
			array(
				'type'	 			=> 'radio',
				'label' 			=> esc_html__( 'Track scroll depths', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_scroll_method',
				'option_arr_id'		=> $option_arr_id,
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_scroll_cond',
				'options' 			=> array(
					''					=> esc_html__( 'Do not track', 'full-picture-analytics-cookie-notice'),
					'evt'				=> esc_html__( 'Track every depth level with a different event', 'full-picture-analytics-cookie-notice'),
					'params'			=> esc_html__( 'Track every depth level with one event with parameters', 'full-picture-analytics-cookie-notice'),
				),
				'popup'				=> '<p>' . esc_html__( 'Enable this function to track how deep people scroll your pages.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<h3>' . esc_html__( '"Track every depth level with a different event" option', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . esc_html__( 'When you choose this option, when a depth level is reached, WP FP will send to GA an event "scrolled_[depth]", e.g. scrolled_50 ', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'This option is recommended if you do not intend to set many event names and/or you are not an advanced GA user.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<h3>' . esc_html__( '"Track as one event with different parameters" option', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . esc_html__( 'When you choose this option, every time someone reaches a specified depthd, WP FP will send to your GA event "scroll".', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'Information about the depth will be sent to GA as event parameters.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . sprintf ( esc_html__( 'To see these parameters in your GA reports, you need to %3$sregister a custom dimension in GA%4$s with event parameter %1$spercent_scrolled%2$s and build a custom report.', 'full-picture-analytics-cookie-notice') , ' <span style="background: #fdf3ce;">', '</span>', '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-custom-definitions-in-google-analytics-4/?utm_source=fp_admin&utm_medium=fp_link">', '</a>' ) . '</p>
					<p>' . esc_html__( 'This option is recommended if you want to track clicks on many different elements on the website and you are an advanced GA user.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'Track when visitors scroll to:', 'full-picture-analytics-cookie-notice' ),
					'placeholder'		=> esc_html__( 'e.g. 25, 50, 75', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_scroll',
					'class'				=> 'fupi_sub fupi_scroll_cond fupi_cond_val_evt fupi_cond_val_params fupi_disabled',
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[track_scroll]',
					'after field'		=> esc_html__( '% of page height', 'full-picture-analytics-cookie-notice'),
				),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track how long the user was actively engaged with the content', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_engagement',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_engagement]',
				'popup2'			=> '<p class="fupi_warning_text">' . sprintf ( esc_html__( 'To have the timer data available in GA, you need to %3$sregister a custom metric in GA%4$s with event parameter %1$suser_engagement_time%2$s. Unit of measurement: seconds.', 'full-picture-analytics-cookie-notice') , ' <span style="background: #fdf3ce;">', '</span>', '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-custom-definitions-in-google-analytics-4/?utm_source=fp_admin&utm_medium=fp_link">', '</a>' ) . '
					<p>' . esc_html__ ( 'This feature lets you measure how much time users actively spend on your website (scrolling, reading, etc.).', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__ ( 'This does not, however, use Google Analytics\' method of tracking engagement time. The method used here is more precise but it requires the use of Calculated Metrics feature of GA, which requires certain knowledge and experience.', 'full-picture-analytics-cookie-notice') . '</p>
					<h3>' . esc_html__ ( 'How does it work?', 'full-picture-analytics-cookie-notice') . '</h3>
					<p>' . esc_html__ ( 'Time of engagement starts running when a user focuses a tab with page’s content and pauses whenever a tab loses focus. In other words the timer doesn’t run if a user is not looking at the content.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__ ( 'When a user stops scrolling or moving a mouse, a 15 second countdown starts. If during this time the user doesn’t move the mouse or scroll the window, the timer is paused. It resumes counting the time when the user makes an action.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__ ( 'The time info is sent to GA before the user closes his browser or changes a browser tab.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__ ( 'When a user returns to the page, the timer is cleared and starts counting from zero. This is important for the calculations - as described below.', 'full-picture-analytics-cookie-notice') . '</p>
					<h3>' . esc_html__ ( 'How to get the data', 'full-picture-analytics-cookie-notice') . '</h3>
					<p>' . esc_html__ ( 'All the timer events that WP FP sends to Google Analytics contain information about how long the user was engaged with the content since the last sent event. In other words, if a visitors changes tabs several times during one pageview, WP FP will send several timer events.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__ ( 'This means, that in order to learn how much time the user was engaged with the content, you need to add all these numbers together.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__ ( 'This can be done using Google\'s Calculated Metrics feature. Here\'s a guide with an explainer video showing how to do it. Please mind, that this is for advanced GA users.', 'full-picture-analytics-cookie-notice') . ' <a href="https://www.lovesdata.com/blog/calculated-metrics">' . esc_html__( 'Go to the tutorial', 'full-picture-analytics-cookie-notice' ) . '</a></p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track JavaScript errors', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'js_err_dimens',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'popup2'				=> '<p>' .  esc_html__( 'This will send to Google Analytics descriptions of JavaScript errors on your site. Use it with caution! If your site has many errors, the number of events may exceed Google Analytics\' limit. Events descriptions are limited to 100 characters (Google\'s limit).', 'full-picture-analytics-cookie-notice') . '</p><p>' . sprintf ( esc_html__( 'To see this data in reports you need to %3$sregister a custom dimension in GA%4$s with event parameter %1$sjs_error_details%2$s.', 'full-picture-analytics-cookie-notice') , ' <span style="background: #fdf3ce;">', '</span>', '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-custom-definitions-in-google-analytics-4/?utm_source=fp_admin&utm_medium=fp_link">', '</a>' ) . '</p>',
			),
		),
	),

	// CUSTOM EVENTS

	array(
		'section_id' => 'fupi_ga41_atrig',
		'section_title' => esc_html__( 'Tracking complex events', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track when specific conditions are met', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'custom_events',
				'class'				=> 'fupi_events_builder fupi_fullwidth_tr',
				'must_have'			=> 'pro atrig',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'fields'			=> array(
					array(
						'label'				=> esc_html__( 'When this happens', 'full-picture-analytics-cookie-notice' ),
						'type' 				=> 'atrig_select',
						'field_id'			=> 'atrig_id',
						'class'				=> 'fupi_col_30',
						'required'			=> true,
						'format'			=> 'key'
					),
					array(
						'type'	 			=> 'select',
						'label' 			=> esc_html__( '...for...', 'full-picture-analytics-cookie-notice' ),
						'field_id' 			=> 'repeat',
						'option_arr_id'		=> $option_arr_id,
						'class'				=> 'fupi_col_15',
						'options'			=> array(
							'no'				=> esc_html__( 'The first time', 'full-picture-analytics-cookie-notice' ),
							'yes'				=> esc_html__( 'Every time', 'full-picture-analytics-cookie-notice' ),
						),
					),
					array(
						'type'				=> 'text',
						'label'				=> esc_html__( 'Send to GA event', 'full-picture-analytics-cookie-notice' ),
						'placeholder'		=> esc_html__( 'event_name', 'full-picture-analytics-cookie-notice' ),
						'field_id'			=> 'evt_name',
						'el_class'			=> 'fupi_events_builder_evt',
						'required'			=> true,
						'class'				=> 'fupi_col_20',
					),
					array(
						'type'				=> 'number',
						'label'				=> esc_html__( 'Value (optional)', 'full-picture-analytics-cookie-notice' ),
						'field_id'			=> 'evt_val',
						'required'			=> true,
						'class'				=> 'fupi_col_20',
					),
				),
			)
		),
	),

	// EVENT PARAMETERS

	array(
		'section_id' => 'fupi_ga41_wpdata',
		'section_title' => esc_html__( 'Tracking event parameters', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track page type', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'page_type',
				'placeholder'		=> esc_html__( 'Parameter name, e.g. "page_type"', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[page_type]',
				'format'			=> 'key',
				'under field'		=> esc_html__( 'Only lowercase letters, digits and underscores. Parameter name cannot start with a digit.', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track page IDs', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'page_id',
				'placeholder'		=> esc_html__( 'Parameter name, e.g. "page_id"', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[page_id]',
				'format'			=> 'key',
				'under field'		=> esc_html__( 'Only lowercase letters, digits and underscores. Parameter name cannot start with a digit.', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track the current page number of categories, tags, etc.', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'page_number',
				'placeholder'		=> esc_html__( 'Parameter name, e.g. "page_number"', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[page_number]',
				'format'			=> 'key',
				'under field'		=> esc_html__( 'Only lowercase letters, digits and underscores. Parameter name cannot start with a digit.', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track post and page publish dates', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'post_date',
				'placeholder'		=> esc_html__( 'Parameter name, e.g. "post_date"', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[post_date]',
				'format'			=> 'key',
				'under field'		=> esc_html__( 'Only lowercase letters, digits and underscores. Parameter name cannot start with a digit.', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track user\'s login status and role', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'user_role',
				'placeholder'		=> esc_html__( 'Parameter name, e.g. "user_role"', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[user_role]',
				'format'			=> 'key',
				'under field'		=> esc_html__( 'Only lowercase letters, digits and underscores. Parameter name cannot start with a digit.', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track page language', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'page_lang',
				'placeholder'		=> esc_html__( 'Parameter name, e.g. "page_lang"', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[page_lang]',
				'format'			=> 'key',
				'under field'		=> esc_html__( 'Only lowercase letters, digits and underscores. Parameter name cannot start with a digit.', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track post\'s terms (categories, tags, etc.) ', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tax_terms',
				'placeholder'		=> esc_html__( 'Parameter name, e.g. "taxonomy_terms"', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[tax_terms]',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_tax_terms_opts',
				'format'			=> 'key',
				'under field'		=> esc_html__( 'Only lowercase letters, digits and underscores. Parameter name cannot start with a digit.', 'full-picture-analytics-cookie-notice' ),
				'popup'				=> '<p>' . esc_html__('This will track the categories, tags and formats of posts and pages. You can enable tracking other terms in the "Shared tracking settings" > "Default settings".','full-picture-analytics-cookie-notice') . '</p>',
			),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Add taxonomy slug to term name', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'add_tax_term_cat',
					'option_arr_id'		=> $option_arr_id,
					'class'				=> 'fupi_sub fupi_tax_terms_opts fupi_disabled',
					'popup'				=> '<p>' . esc_html__( 'Sometimes it can be difficult to say if a term, e.g. "european music" is a tag or a category. Enable this setting and this information will be sent to Google Analytics along with the term itself, e.g. "european music (tag)"', 'full-picture-analytics-cookie-notice') . '</p>',
				),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Send term names instead of term slugs', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'send_tax_terms_titles',
					'option_arr_id'		=> $option_arr_id,
					'class'				=> 'fupi_sub fupi_tax_terms_opts fupi_disabled',
					'popup'				=> '<p>' . esc_html__( 'Enable this to send term names (e.g. product category) instead of their slugs (e.g. product_category). Enabling this feature is not recommended since term names can sometimes be changed while slugs are changed only on very rare occasions.', 'full-picture-analytics-cookie-notice') . '</p>',
				),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track unmodified page titles', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'clean_page_title',
				'option_arr_id'		=> $option_arr_id,
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_seo_title',
				'popup'				=> '<p>' . esc_html__( 'By default, Google Analytics takes page titles from the "title" meta tag. This is not perfect since this tag can often change (e.g. when you tweak it with an SEO plugin). The result is that your Google Analytics can show you reports where one page can have multiple entries - under different titles.', 'full-picture-analytics-cookie-notice') . '</p><p>' . esc_html__( 'When you enable this option, WP Full Picture will send to Google Analytics the default title of your page as used on the page / post / product edit screen. This will make data analysis easier.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'Track SEO titles (displayed in Google\'s search results)', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'seo_title',
					'placeholder'		=> esc_html__( 'Parameter name, e.g. "seo_title"', 'full-picture-analytics-cookie-notice' ),
					'class'				=> 'fupi_sub fupi_seo_title fupi_disabled',
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[seo_title]',
					'format'			=> 'key',
					'under field'		=> esc_html__( 'Only lowercase letters, digits and underscores. Parameter name cannot start with a digit.', 'full-picture-analytics-cookie-notice' ),
				),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track author\'s display names', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'post_author',
				'placeholder'		=> esc_html__( 'Parameter name, e.g. "post_author"', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[post_author]',
				'format'			=> 'key',
				'under field'		=> esc_html__( 'Only lowercase letters, digits and underscores. Parameter name cannot start with a digit.', 'full-picture-analytics-cookie-notice' ),
				'popup2'			=> '<p class="fupi_warning_text">' . esc_html__( 'Tracking personally identifiable information is against Google\'s policy. Make sure that the displayed names are pseudonyms.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track authors IDs', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'author_id',
				'must_have'			=> 'field|fupi_track|show_author_id|exists|Enable_tracking_authors_IDs_in_Shared_Tracking_Settings',
				'placeholder'		=> esc_html__( 'Parameter name, e.g. "author_id"', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[author_id]',
				'format'			=> 'key',
				'under field'		=> esc_html__( 'Only lowercase letters, digits and underscores. Parameter name cannot start with a digit.', 'full-picture-analytics-cookie-notice' ),
				'popup3'			=> '<p style="color: red">' . esc_html__('Do not enable this option if authors of content of your site have administrator rights. This will expose their IDs making attacks easier. Don\'t help attackers hack your site.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track the number of search results', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'search_results_nr',
				'placeholder'		=> esc_html__( 'Parameter name, e.g. "search_results_nr"', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[search_results_nr]',
				'format'			=> 'key',
				'under field'		=> esc_html__( 'Only lowercase letters, digits and underscores. Parameter name cannot start with a digit.', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track metadata', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_cf',
				'must_have'			=> 'pro',
				'class'				=> 'fupi_metadata_tracker fupi_simple_r3',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'popup2'				=> '<p>' . esc_html__( 'This setting lets you track metadata (hidden and/or custom data of your content, users and post/page terms).', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'To track metadata you need to register it in the "Shared tracking settings" > "Extra tracking functions". After you do this, refresh this page and choose what you want to track.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p class="wupi_warning_text">' . esc_html__( 'To view data in your Google Analytics reports you need to register custom dimensions in Google Analytics\' panel using the same event parameter names as you entered in the fields.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'Event parameter names must contain only lowercase letters, digits and underscores and cannot begin with a digit.', 'full-picture-analytics-cookie-notice' ) . '</p>',
				'fields'			=> array(
					array(
						'type'				=> 'custom_meta_select',
						'field_id'			=> 'id',
						'required'			=> true,
					),
					array(
						'type'				=> 'text',
						'placeholder'		=> esc_html__( 'parameter_name' , 'full-picture-analytics-cookie-notice' ),
						'field_id'			=> 'dimname',
						'required'			=> true,
						'format' 			=> 'key',
					),
				),
			)
		),
	),

	// WOOCOMMERCE

	array(
		'section_id' => 'fupi_ga41_ecomm',
		'section_title' => esc_html__( 'WooCommerce tracking', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Measurement Protocol API secret key', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'mp_secret_key',
				'must_have'			=> 'pro woo',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[mp_secret_key]',
				'under field'		=> sprintf( esc_html__( 'Measurement Protocol is only used for WooCommerce Status-Based Order Tracking (see below). %1$sLearn where to find the MP key%2$s', 'full-picture-analytics-cookie-notice'), '<button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_mpapi_key_popup">', '</button>'),
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Status-Based Order Tracking with Measurement Protocol', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'adv_orders',
				'class'				=> 'fupi_sub',
				'must_have'			=> 'pro woo', // field|fupi_ga41|mp_secret_key|exists|' . esc_html__("Measurement_Protocol_Secret_Key")
				'option_arr_id'		=> $option_arr_id,
				'popup2'			=> '
					<p>' . esc_html__( 'Status-Based Order Tracking is an alternative method of tracking purchases. Instead of tracking them on order confirmation pages, orders are tracked when their status changes.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'This method of tracking is recommended for stores that use payment gateways, which do not redirect back to the order confirmation page.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . sprintf( esc_html__( 'In addition, SBOT allows for tracking returns and cancellations. (requires a %1$scustom "refunds" report %2$s).', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-make-a-refunds-report-in-google-analytics-4/">', '</a>' ) . '</p>
					<h3>' . esc_html__( 'Other information', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<ol>
						<li>' . esc_html__( 'Orders will be tracked when they get a status that is set in "WooCommerce Tracking" page > "Status-Based Order Tracking" section.', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'Purchases are attributed to users and sessions just like with standard tracking.', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'SBOT does not track orders added manually in the WooCommerce admin panel, since they cannot be attributed to any website users.', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li class="fupi_warning_text">' . esc_html__( 'Partial refunds are not tracked. Only full refunds are tracked when the order status changes to "Refunded".', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li class="fupi_warning_text">' . esc_html__( 'Most purchases tracked with SBOT will not be visible in the "realtime view" reports in GA. Google can process server-side purchase events for up to 48 hours.', 'full-picture-analytics-cookie-notice' ) . '</li>
					</ol>',
			),
		),
	)
);
?>
