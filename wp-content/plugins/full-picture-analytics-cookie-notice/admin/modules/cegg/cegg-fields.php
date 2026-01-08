<?php

$option_arr_id = 'fupi_cegg';

// ALL TOGETHER

$sections = array(

	// INSTALLATION

	array(
		'section_id' => 'fupi_cegg_install',
		'section_title' => esc_html__( 'Installation', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Crazy Egg\'s script URL', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'script_src',
				'class'				=> 'fupi_required',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[script_src]',
				'placeholder'		=> '//script.crazyegg.com/pages/scripts/XXXX/YYYY.js',
				'under field'		=> '<p>' . sprintf( esc_html__( '%1$sWhere to find script URL and correctly finish installation%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-install-crazy-egg/">', '</a>' ) . '</p>',
			),
		),
	),

	// LOADING

	array(
		'section_id' => 'fupi_cegg_loading',
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
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_load_opts fupi_adv',
				'must_have'			=> 'pro geo',
				'is repeater'		=> false,
				'popup'				=> '<p>' . sprintf( esc_html__('Enter a list of 2-character %1$scountry codes%2$s separated by comas.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://www.iban.com/country-codes">', '</a>' ) . '</p><p>'. esc_html__('Location is checked using the method chosen in the settings of the Geolocation module.', 'full-picture-analytics-cookie-notice' ) . '</p>',
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
			),
		),
	),

	// TAGGING RECORDINGS

	array(
		'section_id' => 'fupi_cegg_tags',
		'section_title' => esc_html__( 'Tagging recordings', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Tag with clicks on outbound links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_outbound',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__( 'Tags sessions with clicks on all links that lead to other domains. Attention! Affiliate links leading to other sites are also treated as outbound.', 'full-picture-analytics-cookie-notice'),
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Tag with clicks on affiliate links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_affiliate',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3 fupi_adv',
				'btns_class'		=> 'fupi_push_right',
				'popup' 			=> '<p>' . esc_html__( 'In the second field you can also use a placeholder [name]. It will be replaced with the first 20 characters of the text inside the clicked element. Make sure it has any.', 'full-picture-analytics-cookie-notice' ) . '</p>',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'URL part, e.g. /go/', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Tag name (optional)', 'full-picture-analytics-cookie-notice' ),
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
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . esc_html__( 'It will track the last 5 digits of the phone number and the part of the email address before the "@" symbol.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Tag with clicks on file download links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_file_downl',
				'class'				=> 'fupi_adv',
				'placeholder'		=> 'pdf, doc, docx, xls, xlsx, txt',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[tag_file_downl]',
				'under field'		=> esc_html__( 'Enter coma separated list of file formats you want to track', 'full-picture-analytics-cookie-notice'),
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Tag with anchor clicks (links that lead to elements on the same page)', 'full-picture-analytics-cookie-notice' ),
				'class'				=> 'fupi_adv',
				'field_id' 			=> 'tag_anchor_clicks',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Tag with clicks on page elements', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_elems',
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
				'popup2'			=> '<h3>' . esc_html__( 'How to fill in these fields', 'full-picture-analytics-cookie-notice' ) . '</h3>
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
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Tag with form submissions', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_forms',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3 fupi_adv',
				'btns_class'		=> 'fupi_push_right',
				'popup2'			=> '<p class="fupi_warning_text">' . esc_html__( 'There are 4 methods of tracking form. Please choose the one that is best suited for your forms. Otherwise form tracking may not work correctly' , 'full-picture-analytics-cookie-notice' ) . '<p>
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
						'placeholder'		=> esc_html__('Form(s) name','full-picture-analytics-cookie-notice'),
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
				'class'				=> 'fupi_simple_r3 fupi_adv',
				'btns_class'		=> 'fupi_push_right',
				'popup2'			=> '<p class="fupi_warning_text">' . esc_html__( 'This function works only on elements which are present in the HTML at the moment of rendering the page. To track elements added later, enable the "DOM listener" function in the Shared tracking settings > Tracking improvements.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__( 'Elements are treated as "visible" when they are 200px inside the screen (you can change it on the "shared tracking settings" page). Each view is counted once per page view.', 'full-picture-analytics-cookie-notice') . '</p>',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'CSS selector e.g. #side img', 'full-picture-analytics-cookie-notice' ),
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
				)
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Tag with page types', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_pagetype',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
			),
		),
	),

	// USER IDENTIFICATION

	array(
		'section_id' => 'fupi_cegg_user',
		'section_title' => esc_html__( 'User tracking', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'label' 			=> esc_html__( 'Set Custom User Variable 1 to:', 'full-picture-analytics-cookie-notice' ),
				'type'	 			=> 'select',
				'class'				=> 'fupi_adv',
				'field_id' 			=> 'uservar_1',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[uservar_1]',
				'options' 			=> array(
					'0' 				=> esc_html__('none','full-picture-analytics-cookie-notice'),
					'status_n_role'		=> esc_html__('User\'s login status and role', 'full-picture-analytics-cookie-notice'),
				),
				'default'			=> '0',
				'popup'				=> sprintf( esc_html__( 'User variables are used in "confetti" overlay views of your reports or recordings. %1$sLearn more%2$s', 'full-picture-analytics-cookie-notice'), '<a href="https://support.crazyegg.com/hc/en-us/articles/360054584474-Custom-User-Variables">', '</a>' ),
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Identify logged-in users by User ID', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'identif_users',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_adv',
				'must_have'			=> 'pro',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_encode_userid',
				'popup3'			=> '<p>' . sprintf( esc_html__('Identify users to associate them with session recordings. This can be used to improve your customer support or finding problems that specific users encountered on the site. %1$sLearn more%2$s', 'full-picture-analytics-cookie-notice'), '<a href="https://support.crazyegg.com/hc/en-us/articles/1500001716641-Visitor-Identifier">', '</a>' ) . '</p>
					<p style="color: red;">' . esc_html__( 'Attention. You need to disclose in your privacy policy that you send user IDs to Crazy Egg.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
				array(
					'type'	 			=> 'toggle',
					'label' 			=> esc_html__( 'Encode User ID before sending to Crazy Egg', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'encode_userid',
					'option_arr_id'		=> $option_arr_id,
					'class'				=> 'fupi_adv fupi_sub fupi_encode_userid fupi_disabled fupi_pro',
				)
		),
	),

	// WOOCOMMERCE

	array(
		'section_id' => 'fupi_cegg_ecomm',
		'section_title' => esc_html__( 'WooCommerce tagging', 'full-picture-analytics-cookie-notice' ),
	),
);
?>
