<?php

$option_arr_id = 'fupi_gads';

$sections = array(

	// INSTALLATION

	array(
		'section_id' => 'fupi_gads_install',
		'section_title' => esc_html__( 'Installation', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'GTAG ID', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'id',
				'class'				=> 'fupi_required',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[id]',
				'placeholder'		=> 'XX-00000000',
				'under field'		=> '
					<p>' . sprintf ( esc_html__( '%1$sHow to get GTAG ID and Conversion ID and install Google Ads%2$s.', 'full-picture-analytics-cookie-notice') , '<a href="https://wpfullpicture.com/support/documentation/how-to-install-google-ads/">', '</a>' ) . '</p>
					<p>' . esc_html__( 'Attention. Google cannot properly detect if multiple Google Analytics and Ads tools are installed. If you click "test instalation" in your Google account, disregard its result.', 'full-picture-analytics-cookie-notice' ) . '</p>'
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Conversion ID', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'id2',
				'class'				=> 'fupi_join',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[id2]',
				'placeholder'		=> 'AW-00000000',
				'under field'		=> '<p>' . esc_html__( 'Provide Conversion ID if it is different from GTAG ID.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
		),
	),

	// LOADING

	array(
		'section_id' => 'fupi_gads_loading',
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

	// Privacy settings

	array(
		'section_id' => 'fupi_gads_basic',
		'section_title' => esc_html__( 'Data collection', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Enhanced Conversions', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'enh_conv',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . esc_html__( 'Enhanced Conversion improves the accuracy of conversion tracking by sending to Google your visitors\' personal information, like their email address, first and last name and physical address. This information is later used by Google to better match the conversions with specific users.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'To implement Enhanced Conversions follow the steps below.', 'full-picture-analytics-cookie-notice') . '</p>
					<ol>
						<li>' . esc_html__('Enable "Enhanced Conversions" via Google Tag in your Google Ads account. You will find it in "Goals" > "Conversions" > "Settings" > "Enhanced conversions" > and select "Google tag" from the dropdown. If you can\'t see these menu elements, please switch to the new menu using the "Appearance" switch in the top.', 'full-picture-analytics-cookie-notice') . '</li>
						<li>' . sprintf( esc_html__( 'Make sure you agree and comply with Google\'s %1$sCustomer Data policies%2$s, %3$sGoogle Ads Data Processing Terms%2$s and privacy law in your country.', 'full-picture-analytics-cookie-notice' ), '<a href="https://support.google.com/adspolicy/answer/7475709?sjid=6953114821919544275-EU">', '</a>', '<a href="https://business.safety.google/adsprocessorterms/">' ) . '</li>
						<li>' . esc_html__('Add information to your privacy policy that you send personal user data to Google.', 'full-picture-analytics-cookie-notice' ) . '</li>
					</ol>',
			)
		),
	),

	// EVENT TRACKING

	array(
		'section_id' => 'fupi_gads_events',
		'section_title' => esc_html__( 'Simple events', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track clicks on all email links as conversions', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_email',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'placeholder'		=> esc_html__( 'Conversion label (required)', 'full-picture-analytics-cookie-notice' ),
				'popup'				=> '<p>' . esc_html__('This will track clicks in all email links on your website. If you want to track only specific links, please use the option to "track clicks on page elements [...]" below.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track clicks on all tel. links as conversions', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_tel',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'placeholder'		=> esc_html__( 'Conversion label (required)', 'full-picture-analytics-cookie-notice' ),
				'popup'				=> '<p>' . esc_html__('This will track clicks in all telephone links on your website. If you want to track only specific links, please use the option to "track clicks on page elements [...]" below.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track clicks on affiliate links as conversions', 'full-picture-analytics-cookie-notice' ),
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
						'placeholder'		=> esc_html__( 'Conversion label (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				)
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track clicks on page elements as conversions', 'full-picture-analytics-cookie-notice' ),
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
						'placeholder'		=> esc_html__( 'Conversion label (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'				=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				)
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track form submissions as conversions', 'full-picture-analytics-cookie-notice' ),
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
						'class'				=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Conversion label (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'				=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				)
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track as conversions when page elements show on screen', 'full-picture-analytics-cookie-notice' ),
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
						'class'				=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Conversion label (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'				=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				)
			),
		),
	),
);

$adv_triggers_section = array(
	array(
		'section_id' => 'fupi_gads_custom_events',
		'section_title' => esc_html__( 'Custom events', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track conversions when specific conditions are met', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'custom_events',
				'class'				=> 'fupi_adv fupi_events_builder fupi_fullwidth_tr',
				'must_have'			=> 'pro atrig',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'fields'			=> array(
					array(
						'label'				=> esc_html__( 'When this happens', 'full-picture-analytics-cookie-notice' ),
						'type' 				=> 'atrig_select',
						'field_id'			=> 'atrig_id',
						'class'				=> 'fupi_col_40',
						'required'			=> true,
						'format'			=> 'key'
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
						'type'				=> 'text',
						'label'				=> esc_html__( 'Send an event with a conversion label', 'full-picture-analytics-cookie-notice' ),
						'placeholder'		=> esc_html__( 'Conversion label', 'full-picture-analytics-cookie-notice' ),
						'field_id'			=> 'conv_label',
						'el_class'			=> 'fupi_events_builder_evt',
						'required'			=> true,
						'class'				=> 'fupi_col_40',
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
				),
			)
		),
	),
);

$sections = array_merge( $sections, $adv_triggers_section );

// E-COMMERCE
$woo_section = array(
	array(
		'section_id' => 'fupi_gads_ecomm',
		'section_title' => esc_html__( 'WooCommerce tracking', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track purchases as conversions', 'full-picture-analytics-cookie-notice' ),
				'placeholder'		=> esc_html__( 'Conversion label (required)', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'woo_conv_id',
				'must_have'			=> 'woo',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[woo_conv_id]',
				'popup'				=> '<p>' . esc_html__( 'Conversion event will be sent with cart value and IDs of the purchased items.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . sprintf ( esc_html__( 'To get the conversion label, please follow this %1$sshort video tutorial%2$s.', 'full-picture-analytics-cookie-notice') , '<a href="https://wpfullpicture.com/support/documentation/how-to-get-google-ads-tag-id-conversion-id/">', '</a>' ) . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track checkouts as conversions', 'full-picture-analytics-cookie-notice' ),
				'placeholder'		=> esc_html__( 'Conversion label (required)', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'woo_checkout_conv_id',
				'must_have'			=> 'woo',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[woo_checkout_conv_id]',
				'popup'				=> '<p>' . esc_html__( 'Conversion event will be sent with cart value and IDs of the purchased items.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__('Attention. By default, this event will be sent every time the user visits the checkout page - even if it happens multiple times in one session (except page refreshes). You can disable it by setting this conversion as a single-time conversion in the Google Ads panel (while registering a new conversion type).', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . sprintf ( esc_html__( 'To get the conversion label, please follow this %1$sshort video tutorial%2$s.', 'full-picture-analytics-cookie-notice') , '<a href="https://wpfullpicture.com/support/documentation/how-to-get-google-ads-tag-id-conversion-id/">', '</a>' ) . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track additions to cart as conversions', 'full-picture-analytics-cookie-notice' ),
				'placeholder'		=> esc_html__( 'Conversion label (required)', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'woo_add_to_cart_conv_id',
				'must_have'			=> 'woo',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[woo_add_to_cart_conv_id]',
				'popup'				=> '<p>' . esc_html__( 'Conversion event will be sent with the IDs of the items added to cart and their total value.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . sprintf ( esc_html__( 'To get the conversion label, please follow this %1$sshort video tutorial%2$s.', 'full-picture-analytics-cookie-notice') , '<a href="https://wpfullpicture.com/support/documentation/how-to-get-google-ads-tag-id-conversion-id/">', '</a>' ) . '</p>',
			),
			array(
				'type'	 			=> 'select',
				'label' 			=> esc_html__( 'Your business type', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'business_type',
				'must_have'			=> 'woo',
				'option_arr_id'		=> $option_arr_id,
				'options' 			=> array(
					'retail'			=> esc_html__( 'Retail', 'full-picture-analytics-cookie-notice' ),
					'local'				=> esc_html__( 'Local business', 'full-picture-analytics-cookie-notice' ),
					'real_estate'		=> esc_html__( 'Real estate', 'full-picture-analytics-cookie-notice' ),
				),
				'default'			=> 'retail',
			),
		),
	),
);

$sections = array_merge( $sections, $woo_section );


?>
