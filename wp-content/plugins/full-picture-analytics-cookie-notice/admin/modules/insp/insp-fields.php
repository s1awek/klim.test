<?php

$option_arr_id = 'fupi_insp';

// BASIC FIELDS

$basic_fields = array(
	array(
		'type'	 			=> 'text',
		'label' 			=> esc_html__( 'Site ID', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'id',
		'class'				=> 'fupi_required',
		'placeholder'		=> '000000000',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[id]',
		'under field'		=> '<button type="button" class="fupi_open_popup fupi_faux_link" data-popup="fupi_install_popup" >' . esc_html__( 'Where to find Site ID', 'full-picture-analytics-cookie-notice' ) . '</button>'
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Install a script for A/B tests', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'ab_test_script',
		'option_arr_id'		=> $option_arr_id,
	),
);

// LOADING

$loading_fields = array(
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Force load', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'force_load',
		'el_class'			=> 'fupi_condition fupi_condition_reverse',
		'el_data_target'	=> 'fupi_load_opts',
		'option_arr_id'		=> $option_arr_id,
		'popup3'			=> '<p style="color: red">' . esc_html__( 'Use only for installation verification or testing. It breaks GDPR and similar laws.', 'full-picture-analytics-cookie-notice' ) . '</p>
		<p>' . sprintf( esc_html__( 'This will load the tracking tool for all website visitors, including administrators, bots, excluded users, people browsing from excluded locations and people that didn\'t agree to tracking. %1$sLearn more%2$s.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/support/documentation/validation-mode/?utm_source=fp_admin&utm_medium=referral&utm_campaign=settings_link">', '</a>' ) . '</p>',
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Track without waiting for consent', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'disreg_cookies',
		'must_have'			=> 'cook',
		'option_arr_id'		=> $option_arr_id,
		'class'				=> 'fupi_load_opts',
		'popup3'			=> '<p style="color: red">' . esc_html__( 'Use only for installation verification or testing. It breaks GDPR and similar laws.', 'full-picture-analytics-cookie-notice' ) . '</p>
		<p>' . esc_html__( 'Visitors will still be able to turn off tracking by declining tracking / cookies.', 'full-picture-analytics-cookie-notice' ) . '</p>'
	),
	array(
		'type'	 			=> 'r3',
		'label' 			=> esc_html__('Only track visitors from specific countries', 'full-picture-analytics-cookie-notice'),
		'field_id' 			=> 'limit_country',
		'option_arr_id'		=> $option_arr_id,
		'class'				=> 'fupi_load_opts',
		'must_have'			=> 'pro geo',
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
				'placeholder'		=> esc_html__( 'e.g. GB, DE, FR, AU, etc.','full-picture-analytics-cookie-notice' ),
			),
		),
	),
);

// ALL TOGETHER

$sections = array(

	// INSTALLATION

	array(
		'section_id' => 'fupi_insp_install',
		'section_title' => esc_html__( 'Installation', 'full-picture-analytics-cookie-notice' ),
		'fields' => $basic_fields,
	),

	// LOADING

	array(
		'section_id' => 'fupi_insp_loading',
		'section_title' => esc_html__( 'Loading', 'full-picture-analytics-cookie-notice' ),
		'fields' => $loading_fields,
	),

	// SESSION TAGGING

	array(
		'section_id' => 'fupi_insp_tags',
		'section_title' => esc_html__( 'Tag recordings', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Tag with clicks on outbound links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_outbound',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__( 'Tags clicks on all links that lead to other domains. Attention! Affiliate links leading to other sites are also treated as outbound.', 'full-picture-analytics-cookie-notice'),
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Tag with clicks on affiliate links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_affiliate',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3',
				'btns_class'		=> 'fupi_push_right',
				'under field'		=> '<p>' . esc_html__( 'In the second field you can also use a placeholder [name]. It will be replaced with the first 20 characters of the text inside the clicked element. Make sure it has any.', 'full-picture-analytics-cookie-notice' ) . '</p>',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'Url part, e.g. /go/', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Tag (optional)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
					),
				)
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Tag with clicks on email and tel. links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_email_tel',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . esc_html__( 'It will track the last 5 digits of the phone number and the part of the email address before the "@" symbol.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Tag with clicks on file download links', 'full-picture-analytics-cookie-notice' ),
				'placeholder'		=> esc_html__( 'e.g. pdf, doc, docx, xls, xlsx, txt', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_file_downl',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[tag_file_downl]',
				'popup'				=> esc_html__( 'Enter coma separated list of file formats you want to track', 'full-picture-analytics-cookie-notice'),
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Tag with anchor clicks (links leading to elements on the same page)', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_anchor_clicks',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Tag with clicks on page elements', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_elems',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3',
				'btns_class'		=> 'fupi_push_right',
				'popup2'			=> '<h3>' . esc_html__( 'How to fill in these fields', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<ol>
						<li>' . esc_html__( 'You can enter more then 1 selector in a "CSS selector" field, e.g. .button, .different-button, .another-button.', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'If multiple selectors point at the same element only the first match will be tracked.', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'In the second field you can also use a placeholder [name]. It will be replaced with the first 20 characters of the text inside the clicked element. Make sure it has any.', 'full-picture-analytics-cookie-notice' ) . '</li>
					</ol>
					<h3>' . esc_html__( 'Attention!', 'full-picture-analytics-cookie-notice') . '</h3>
					<p style="color: #e47d00;">' . esc_html__( 'To correctly track clicks in page elements OTHER than links (e.g. buttons), you need to provide CSS selectors of ALL clickable elements inside that element.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'The easiest way to do it is to use the asterisk symbol "*". For example, to track clicks in buttons provide:', 'full-picture-analytics-cookie-notice' ) . ' <code>.my_button, .my_button *</code>.</p>
					<p><a href="https://wpfullpicture.com/support/documentation/how-to-track-clicks-in-page-page-elements/" target="_blank">' . esc_html__( 'Learn more about tracking clicks', 'full-picture-analytics-cookie-notice' ) . '</a></p>',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'CSS selector e.g. .side img', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Tag (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				)
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Tag with form submissions', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_forms',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3',
				'btns_class'		=> 'fupi_push_right',
				'popup2'			=> '<p style="color: #e47d00;">' . esc_html__( 'There are 4 methods of tracking form. Please choose the one that is best suited for your forms. Otherwise form tracking may not work correctly' , 'full-picture-analytics-cookie-notice' ) . '<p>
					<p><a class="button-secondary" target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-choose-the-best-way-to-track-form-submissions/">' . esc_html__( 'Choose correct method to track your forms.' , 'full-picture-analytics-cookie-notice' ) . '</a></p>',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'CSS selector e.g. #form_id', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Tag (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				)
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Tag when page elements show on screen', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_views',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3',
				'btns_class'		=> 'fupi_push_right',
				'popup2'				=> '<p>' . esc_html__( 'Elements are treated as "visible" when they are 200px inside the screen (you can change it in the General Settings). Each view is counted once per page view.', 'full-picture-analytics-cookie-notice') . '</p>
				<p style="color:#e47d00">' . esc_html__( 'This tracks only elements which are present in the HTML at the moment of rendering the page. To track elements added later, enable the "DOM listener" function in the General Settings.', 'full-picture-analytics-cookie-notice') . '</p>',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'CSS selector e.g. .side img', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Tag (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				)
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Tag with user\'s login status and role', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_user_role',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Tag with page type', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_pagetype',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Tag with page author', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_pageauthor',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Tag with referring site', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_referrer',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Tag with UTM campaign parameters', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_utm',
				'option_arr_id'		=> $option_arr_id,
			),
		),
	),

	// USER IDENTIF.

	array(
		'section_id' => 'fupi_insp_user',
		'section_title' => esc_html__( 'User identification', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Identify logged-in users', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'identif_users',
				'option_arr_id'		=> $option_arr_id,
				'must_have'			=> 'pro',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_sub_identif',
				'popup3'			=> '<p>' . esc_html__( 'User identification is used to associate recordings with people.', 'full-picture-analytics-cookie-notice') . '</p>
				<h3>' . esc_html__( 'Attention', 'full-picture-analytics-cookie-notice' ) . '</h3>
				<p>' . sprintf( esc_html__( 'To identify users you may have to %1$ssign Data Processing Agreement with Inspectlet%2$s and disclose in your privacy policy that you send this information to a 3rd party. Proceed according to privacy laws in your country.', 'full-picture-analytics-cookie-notice' ), '<a href="https://docs.inspectlet.com/hc/en-us/articles/360004212092-Inspectlet-Data-Processing-Agreement-under-GDPR" target="_blank">', '</a>' ) . '</p>',
			),
			array(
				'type'	 			=> 'select',
				'label' 			=> esc_html__( 'Identify logged-in users by:', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'identif_method',
				'class'				=> 'fupi_sub fupi_sub_identif fupi_disabled',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'options' 			=> array(
					'encode_user_id'	=> esc_html__( 'Encoded User ID', 'full-picture-analytics-cookie-notice'),
					'user_id'			=> esc_html__( 'User ID', 'full-picture-analytics-cookie-notice'),
					'email'				=> esc_html__( 'Email', 'full-picture-analytics-cookie-notice'),
					'username' 			=> esc_html__( 'Username', 'full-picture-analytics-cookie-notice'),
				),
				'default'			=> 'encode_user_id',
			),
		),
	),

	// WOOCOMMERCE

	array(
		'section_id' => 'fupi_insp_ecomm',
		'section_title' => esc_html__( 'Tag recordings with WooCommerce events', 'full-picture-analytics-cookie-notice' ),
	),
);

?>
