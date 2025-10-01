<?php

$option_arr_id = 'fupi_linkd';

// SECTIONS

$sections = array(

	// INSTALLATION

	array(
		'section_id' => 'fupi_linkd_install',
		'section_title' => esc_html__( 'Installation', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'LinkedIn Partner ID', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'id',
				'class'				=> 'fupi_required',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[id]',
				'under field'		=> '<p>' . sprintf( esc_html__( '%1$sHow to get it%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://www.linkedin.com/help/lms/answer/a417869/access-your-linkedin-partner-id?lang=en" target="_blank">', '</a>' ) . '</p>',
			),
		),
	),

	// INSTALLATION

	array(
		'section_id' => 'fupi_linkd_loading',
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
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_load_opts fupi_adv',
				'must_have'			=> 'pro geo',
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
						'placeholder'		=> 'e.g. GB, DE, FR, AU, etc.',
					),
				),
			),
		),
	),

	// EVENT TRACKING

	array(
		'section_id' => 'fupi_linkd_events',
		'section_title' => esc_html__( 'Tracking simple events', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track clicks on all email links as conversions', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_email',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'placeholder'		=> esc_html__( 'Conversion ID (required)', 'full-picture-analytics-cookie-notice' ),
				'popup'				=> '<p>' . esc_html__( 'Enable this function to track clicks on links with email addresses. It will track the part of the email address before the "@" symbol.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__('This will track clicks in all email links on your website. If you want to track only specific ones, please use the option to "track clicks on page elements [...]" below.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track clicks on all tel. links as conversions', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_tel',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_adv',
				'placeholder'		=> esc_html__( 'Conversion label (required)', 'full-picture-analytics-cookie-notice' ),
				'popup'				=> '<p>' . esc_html__( 'Enable this function to track clicks on links with phone numbers. It will track the last 5 digits of the number.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__('This will track clicks in all phone numbers on your website. If you want to track only specific ones, please use the option to "track clicks on page elements [...]" below.', 'full-picture-analytics-cookie-notice' ) . '</p>',
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
						'placeholder'		=> esc_html__( 'Conversion ID (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				)
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
						<li>' . esc_html__( 'In the second field you can also use a placeholder [name]. It will be replaced with the first 20 characters of the text inside the clicked element. Make sure it has any.', 'full-picture-analytics-cookie-notice' ) . '</li>
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
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Conversion ID (required)', 'full-picture-analytics-cookie-notice' ),
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
						'placeholder'		=> esc_html__( 'Conversion ID (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				)
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track when page elements show on screen', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_views',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3 fupi_adv',
				'btns_class'		=> 'fupi_push_right',
				'popup2'			=> '<p class="fupi_warning_text">' . esc_html__( 'This function works only on elements which are present in the HTML at the moment of rendering the page. To track elements added later, enable the "DOM listener" function in the Shared tracking settings > Tracking improvements.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__( 'Elements are treated as "visible" when they are 200px inside the screen (you can change it on the "shared tracking settings" page). Each view is counted once per page view.', 'full-picture-analytics-cookie-notice') . '</p>',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'CSS selector e.g. .side img', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Conversion ID (required)', 'full-picture-analytics-cookie-notice' ),
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
		'section_id' => 'fupi_linkd_ecomm',
		'section_title' => esc_html__( 'WooCommerce conversions tracking', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( '"Add to cart" Conversion ID', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'woo_add_to_cart_id',
				'must_have'			=> 'woo',
				'placeholder'		=> 'Conversion ID',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[add_to_cart_id]',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( '"Checkout started" Conversion ID', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'woo_checkout_start_id',
				'must_have'			=> 'woo',
				'placeholder'		=> 'Conversion ID',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[checkout_start_id]',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( '"Purchase" Conversion ID', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'woo_purchase_id',
				'must_have'			=> 'woo',
				'placeholder'		=> 'Conversion ID',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[purchase_id]',
			),
		),
	),
);

?>
