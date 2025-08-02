<?php

$option_arr_id = 'fupi_hotj';

// ALL TOGETHER

$sections = array(

	// INSTALLATION

	array(
		'section_id' => 'fupi_hotj_install',
		'section_title' => esc_html__( 'Installation', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Site ID', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'id',
				'class'				=> 'fupi_required',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[id]',
				'under field'		=> '<p>' . sprintf( esc_html__( '%1$sHow to install Hotjar step-by-step%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-install-hotjar/">', '</a>' ) . '</p>
					<p>' . esc_html__( 'Tip. After you save the ID, enable "Force load" (see "Loading" section) to pass validation. Disable it after your site is validated.', 'full-picture-analytics-cookie-notice') . '</p>'
			),
		),
	),

	// INSTALLATION

	array(
		'section_id' => 'fupi_hotj_loading',
		'section_title' => esc_html__( 'Loading', 'full-picture-analytics-cookie-notice' ),
		'fields' => 		array(
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
				'field_id' 			=> 'disreg_cookies',
				'must_have'			=> 'cook',
				'class'				=> 'fupi_load_opts',
				'option_arr_id'		=> $option_arr_id,
				'popup3'			=> '<p style="color: red">' . esc_html__( 'Use only for installation verification or testing. It breaks GDPR and similar laws.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . esc_html__( 'Visitors will still be able to turn off tracking by declining tracking / cookies.', 'full-picture-analytics-cookie-notice' ) . '</p>'
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__('Use only in specific countries', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'limit_country',
				'must_have'			=> 'pro geo',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_load_opts',
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

	// DATA COLLECTION

	array(
		'section_id' => 'fupi_hotj_basic',
		'section_title' => esc_html__( 'Data collection settings', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Data supression', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'data_suppression',
				'el_class'			=> 'fupi_condition fupi_condition_reverse',
				'el_data_target'	=> 'fupi_hotj_priv',
				'option_arr_id'		=> $option_arr_id,
				'popup3'			=> '
				<h3 class="fupi_warning_text">' . esc_html__( 'Read it carefully', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . esc_html__( 'By enabling data supression, you confirm that:', 'full-picture-analytics-cookie-notice' ) . '</p>
					<ol>
						<li>' . esc_html__( 'You understand, that Hotjar will load and start tracking website visitors without asking for consent,', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'You understand, that by using this function, WP FP will disable the user identification feature and stop sending WooCommerce order IDs to Hotjar,', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . sprintf( esc_html__( 'You confirm, that you disabled tracking personally identifiable information in Hotjar\'s dashboard %1$sLearn how%2$s,', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-use-hotjar-without-asking-for-consent/" target="_blank">', '</a>' ) . '</li>
						<li>' . esc_html__( 'You understand, that enabling this function does not stop Hotjar from storing tracking cookies on the devices of your visitors,', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'You consulted with a legal professional, who confirmed that employing these limitations are enough to use Hotjar without asking visitors for consent,', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'In the "Loading" tab you chose countries, in which Hotjar can safely load without breaking the privacy laws.', 'full-picture-analytics-cookie-notice' ) . '</li>
					</ol>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Enable user identification', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'identif_users',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_hotj_priv',
				'popup2'			=> '<p>' . esc_html__( 'With user identification you will be able to target specific users with polls, widgets and easily search their session recordings in the Hotjar panel.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__( 'Identification is done with User ID (for logged-in users) or Hotjar ID (for other visitors)', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__( 'To be able to identify users you need to:', 'full-picture-analytics-cookie-notice') . '</p>
					<ol>
						<li class="fupi_warning_text">' . esc_html__( 'If your visitors come from a country where they have to consent before they are tracked, then you should enable consent banner in optin or an automatic mode and add to your privacy policy information about sending user information to Hotjar.', 'full-picture-analytics-cookie-notice') . '</li>
						<li>' . esc_html__( 'Visit', 'full-picture-analytics-cookie-notice') . ' <a href="https://insights.hotjar.com/settings/user-attributes">' . esc_html__( 'this page', 'full-picture-analytics-cookie-notice') . '</a> ' . esc_html__( 'in your Hotjar dashboard.', 'full-picture-analytics-cookie-notice') . '</li>
						<li>' . esc_html__( 'Select a website where you want to start tracking users (Not available on all plans!)', 'full-picture-analytics-cookie-notice') . '</li>
						<li>' . esc_html__( 'Return to this settings page', 'full-picture-analytics-cookie-notice') . '</li>
						<li>' . esc_html__( 'Set up options in the "User attributes" section', 'full-picture-analytics-cookie-notice') . '</li>
					</ol>',
			),
			array(
				'type'	 			=> 'multi checkbox',
				'label' 			=> esc_html__( 'Associate users with:', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'user_attr',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_sub fupi_hotj_priv',
				'must_have'			=> 'pro',
				'options' 			=> array(
					'role' => esc_html__( 'User role', 'full-picture-analytics-cookie-notice' ),
					'email'	=> esc_html__( 'User email', 'full-picture-analytics-cookie-notice' ),
				),
			),
		),
	),

	// SESSION TAGGING

	array(
		'section_id' => 'fupi_hotj_tags',
		'section_title' => esc_html__( 'Tagging recordings', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track clicks on outbound links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_outbound',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__( 'Tracks clicks on all links that lead to other domains. Affiliate links leading to other sites are also treated as outbound. Attention! This WILL greatly increase the number of events associated with your site!', 'full-picture-analytics-cookie-notice'),
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track clicks on affiliate links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_affiliate',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3',
				'btns_class'		=> 'fupi_push_right',
				'popup'				=> '<p>' . esc_html__( 'In the second field you can also use a placeholder [name]. It will be replaced with the first 20 characters of the text inside the clicked element. Make sure it has any.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'Attention! Depending on the number of affiliate links you are using, this may greatly increase the number of events associated with your site!', 'full-picture-analytics-cookie-notice') . '</p>',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'URL part, e.g. /go/', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Event name (optional)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
					),
				)
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track clicks on email and tel. links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_email_tel',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . esc_html__( 'It will track the last 5 digits of the phone number and the part of the email address before the "@" symbol.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track clicks on file download links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_file_downl',
				'placeholder'		=> esc_html__('e.g. pdf, doc, docx, xls, xlsx, txt', 'full-picture-analytics-cookie-notice'),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[tag_file_downl]',
				'under field'		=> esc_html__( 'Enter a coma-separated list of formats of files that you want to track.', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track anchor clicks (links leading to elements on the same page)', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_anchor_clicks',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__( 'Enable this to track clicks in links that lead to different sections on the same page. Attention! If you use many anchors this may greatly increase the number of events associated with your site!', 'full-picture-analytics-cookie-notice'),
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track clicks on page elements', 'full-picture-analytics-cookie-notice' ),
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
						'placeholder'		=> esc_html__( 'Event name (required)', 'full-picture-analytics-cookie-notice' ),
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
				'field_id' 			=> 'tag_forms',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3',
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
						'placeholder'		=> esc_html__( 'Event name (required)', 'full-picture-analytics-cookie-notice' ),
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
				'field_id' 			=> 'tag_views',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3',
				'btns_class'		=> 'fupi_push_right',
				'popup2'			=> '<p class="fupi_warning_text">' . esc_html__( 'This function works only on elements which are present in the HTML at the moment of rendering the page. To track elements added later, enable the "DOM listener" function in the Shared tracking settings > Tracking improvements.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__( 'Elements are treated as "visible" when they are 200px inside the screen (you can change it on the "shared tracking settings" page). Each view is counted once per page view.', 'full-picture-analytics-cookie-notice') . '</p>',
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'CSS selector e.g. .side img', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'				=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Name of tracked element (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'				=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				)
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track user\'s login status and role', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_user_role',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track page type', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_pagetype',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track page author', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_pageauthor',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track UTM campaign parameters', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_utm',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__( 'Attention! If you conduct many advertising campaigns this may greatly increase the number of events associated with your site!', 'full-picture-analytics-cookie-notice'),
			),
		),
	),

	// WOOCOMMERCE

	array(
		'section_id' => 'fupi_hotj_ecomm',
		'section_title' => esc_html__( 'WooCommerce tracking', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track purchases', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_woo_purchases',
				'must_have'			=> 'woo',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'tag_woo_purchases_cond',
				'option_arr_id'		=> $option_arr_id,
				'under field'		=> esc_html__( 'When enabled, WP Full Picture will send to Hotjar "Woo purchase" event', 'full-picture-analytics-cookie-notice'),
			),
				array(
					'type'	 			=> 'multi checkbox',
					'label' 			=> esc_html__( 'Send extra events:', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'tag_woo_purchases_data',
					'class'				=> 'tag_woo_purchases_cond fupi_sub fupi_disabled',
					'must_have'			=> 'woo',
					'option_arr_id'		=> $option_arr_id,
					'options' 			=> array(
						'id'				=> '<span style="color: red">' . esc_html__( 'Order ID (Click "i" icon to learn more.)', 'full-picture-analytics-cookie-notice' ) . '</span>',
						'p_id'				=> esc_html__( 'Product IDs (one event for each product)', 'full-picture-analytics-cookie-notice' ),
						'p_name'			=> esc_html__( 'Product names (one event for each product)', 'full-picture-analytics-cookie-notice' ),
					),
					'popup2'		=> '<p class="fupi_warning_text">' . esc_html__('Order ID is treated as personal information. It will not be tracked if you enabled the privacy mode in the "Loading" section or if visitors decline using their personal data for statistics.', 'full-picture-analytics-cookie-notice') . '</p>',
						'under field'		=> esc_html__('Each selected option will trigger a single or multiple events. This may greatly increase the number of events associated with your site!', 'full-picture-analytics-cookie-notice'),
				),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track checkouts', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_woo_checkouts',
				'must_have'			=> 'woo',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'tag_woo_checkouts_cond',
				'option_arr_id'		=> $option_arr_id,
				'under field'		=> esc_html__( 'When enabled, WP Full Picture will send to Hotjar "Woo checkout" event', 'full-picture-analytics-cookie-notice'),
			),
				array(
					'type'	 			=> 'multi checkbox',
					'label' 			=> esc_html__( 'Send extra events:', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'tag_woo_checkouts_data',
					'must_have'			=> 'woo',
					'option_arr_id'		=> $option_arr_id,
					'class'				=> 'tag_woo_checkouts_cond fupi_sub fupi_disabled',
					'options' 			=> array(
						'p_id'				=> esc_html__( 'Product IDs (one event for each product)', 'full-picture-analytics-cookie-notice' ),
						'p_name'			=> esc_html__( 'Product names (one event for each product)', 'full-picture-analytics-cookie-notice' ),
					),
					'under field'		=> esc_html__('Each selected option will trigger a single or multiple events. This may greatly increase the number of events associated with your site!', 'full-picture-analytics-cookie-notice'),
				),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track when products are added to cart', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_woo_addtocart',
				'must_have'			=> 'woo',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'tag_woo_addtocart_cond',
				'option_arr_id'		=> $option_arr_id,
				'under field'		=> esc_html__( 'When enabled, WP Full Picture will send to Hotjar "Woo add to cart" event', 'full-picture-analytics-cookie-notice'),
			),
				array(
					'type'	 			=> 'multi checkbox',
					'label' 			=> esc_html__( 'Send extra events', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'tag_woo_addtocart_data',
					'must_have'			=> 'woo',
					'option_arr_id'		=> $option_arr_id,
					'class'				=> 'tag_woo_addtocart_cond fupi_sub fupi_disabled',
					'options' 			=> array(
						'p_id'				=> esc_html__( 'Product IDs (one event for each product)', 'full-picture-analytics-cookie-notice' ),
						'p_name'			=> esc_html__( 'Product names (one event for each product)', 'full-picture-analytics-cookie-notice' ),
					),
					'under field'		=> esc_html__('Each selected option will trigger a single or multiple events. This may greatly increase the number of events associated with your site!', 'full-picture-analytics-cookie-notice'),
				),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track when products are removed from cart', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_woo_removefromcart',
				'must_have'			=> 'woo',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'tag_woo_removefromcart_cond',
				'option_arr_id'		=> $option_arr_id,
				'under field'		=> esc_html__( 'When enabled, WP Full Picture will send to Hotjar "Woo remove from cart" taeventg', 'full-picture-analytics-cookie-notice'),
			),
				array(
					'type'	 			=> 'multi checkbox',
					'label' 			=> esc_html__( 'Send extra events', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'tag_woo_removefromcart_data',
					'must_have'			=> 'woo',
					'option_arr_id'		=> $option_arr_id,
					'class'				=> 'tag_woo_removefromcart_cond fupi_sub fupi_disabled',
					'options' 			=> array(
						'p_id'				=> esc_html__( 'Product IDs (one event for each product)', 'full-picture-analytics-cookie-notice' ),
						'p_name'			=> esc_html__( 'Product names (one event for each product)', 'full-picture-analytics-cookie-notice' ),
					),
					'under field'		=> esc_html__('Each selected option will trigger a single or multiple events. This may greatly increase the number of events associated with your site!', 'full-picture-analytics-cookie-notice'),
				),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track when products are added to a wishlist', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tag_woo_addtowishlist',
				'must_have'			=> 'woo',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'tag_woo_addtowishlist_cond',
				'option_arr_id'		=> $option_arr_id,
				'under field'		=> esc_html__( 'When enabled, WP Full Picture will send to Hotjar "Woo add to wishlist" event', 'full-picture-analytics-cookie-notice'),
			),
				array(
					'type'	 			=> 'multi checkbox',
					'label' 			=> esc_html__( 'Send extra events', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'tag_woo_addtowishlist_data',
					'must_have'			=> 'woo',
					'option_arr_id'		=> $option_arr_id,
					'class'				=> 'tag_woo_addtowishlist_cond fupi_sub fupi_disabled',
					'options' 			=> array(
						'p_id'				=> esc_html__( 'Product IDs (one event for each product)', 'full-picture-analytics-cookie-notice' ),
						'p_name'			=> esc_html__( 'Product names (one event for each product)', 'full-picture-analytics-cookie-notice' ),
					),
					'under field'		=> esc_html__('Each selected option will trigger a single or multiple events. This may greatly increase the number of events associated with your site!', 'full-picture-analytics-cookie-notice'),
				),
		),
	),
);


?>
