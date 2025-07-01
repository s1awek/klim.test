<?php

$option_arr_id = 'fupi_gtm';

// BASIC FIELDS

$basic_fields = array(
	array(
		'type'	 			=> 'text',
		'label' 			=> esc_html__( 'Container ID', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'id',
		'class'				=> 'fupi_required',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[id]',
		'placeholder'		=> 'GTM-0000000',
		'popup_id'			=> 'fupi_install_popup',
	),	
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Protect dataLayer', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'datalayer',
		'option_arr_id'		=> $option_arr_id,
		'popup'				=> '<p>' . esc_html__( 'Google Analytics, Ads and Tag Manager share the same dataLayer. If you install GA or GAds with WP FP\'s modules or other plugins then all events they track will populate the same datalayer that GTM uses which can lead to unexpected problems.', 'full-picture-analytics-cookie-notice') . '</p>
			<p>' . esc_html__( 'When you enable this function, GTM will use a separate dataLayer which will not be filled with events tracked by GA and GAds, however, you will also not be able to use GTM to extend the GA and GAds installed with WP Full Picture\'s modules or other plugins.', 'full-picture-analytics-cookie-notice') . '</p>'
	),
);

// EVENTS FIELDS

$events_fields = array(
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL clicks on outbound links', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_outbound',
		'option_arr_id'		=> $option_arr_id,
		'popup'				=> esc_html__( 'Send to the dataLayer clicks on all links that lead to other domains. Attention! Affiliate links leading to other sites are also treated as outbound.', 'full-picture-analytics-cookie-notice'),
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL clicks on email and tel. links', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_email_tel',
		'option_arr_id'		=> $option_arr_id,
	),
	array(
		'type'	 			=> 'r3',
		'label' 			=> esc_html__( 'Send to the DL clicks on affiliate links', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_affiliate',
		'option_arr_id'		=> $option_arr_id,
		'is_repeater'		=> true,
		'class'				=> 'fupi_simple_r3',
		'btns_class'		=> 'fupi_push_right',
		'popup'		 		=> '<p>' . esc_html__( 'In the second field you can also use a placeholder [name]. It will be replaced with the first 20 characters of the text inside the clicked element. Make sure it has any.', 'full-picture-analytics-cookie-notice' ) . '</p>',
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
		)
	),
	array(
		'type'	 			=> 'text',
		'label' 			=> esc_html__( 'Send to the DL clicks on file download links', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_file_downl',
		'placeholder'		=> 'e.g. pdf, doc, docx, xls, xlsx, txt',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[track_file_downl]',
		'under field'		=> esc_html__( 'Enter coma separated list of file formats (extensions) you want to track', 'full-picture-analytics-cookie-notice'),
	),
	array(
		'type'	 			=> 'r3',
		'label' 			=> esc_html__( 'Send to the DL clicks on page elements', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_elems',
		'option_arr_id'		=> $option_arr_id,
		'is_repeater'		=> true,
		'class'				=> 'fupi_simple_r3',
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
			<p style="color: #e47d00;">' . esc_html__( 'To correctly track clicks in page elements OTHER than links (e.g. buttons), you need to provide CSS selectors of ALL clickable elements inside that element.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . esc_html__( 'The easiest way to do it is to use the asterisk symbol "*". For example, to track clicks in buttons provide:', 'full-picture-analytics-cookie-notice' ) . ' <code>.my_button, .my_button *</code>.</p>
			<p><a href="https://wpfullpicture.com/support/documentation/how-to-track-clicks-in-page-page-elements/" target="_blank">' . esc_html__( 'Learn more about tracking clicks', 'full-picture-analytics-cookie-notice' ) . '</a></p>',
	),
	array(
		'type'	 			=> 'r3',
		'label' 			=> esc_html__( 'Send to the DL form submissions', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_forms',
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
				'placeholder'		=> esc_html__( 'Form name (required)', 'full-picture-analytics-cookie-notice' ),
				'type'				=> 'text',
				'field_id'			=> 'val',
				'class'		=> 'fupi_col_35_grow',
				'required'			=> true,
			),
		)
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL when the window visibility state changes', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_focus',
		'option_arr_id'		=> $option_arr_id,
		'popup'				=> esc_html__( 'This will send an event every time the window gets "focus" or "blur" visibility state, e.g. when visitor starts viewing a webpage (focus) or moves to a different browser tab (blur).', 'full-picture-analytics-cookie-notice' ),
	),
	array(
		'type'	 			=> 'r3',
		'label' 			=> esc_html__( 'Send to the DL when page elements show on screen', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_views',
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
				'placeholder'		=> esc_html__( 'Element name (required)', 'full-picture-analytics-cookie-notice' ),
				'type'				=> 'text',
				'field_id'			=> 'val',
				'class'		=> 'fupi_col_35_grow',
				'required'			=> true,
			),
		)
	),
	array(
		'type'	 			=> 'text',
		'label' 			=> esc_html__( 'Send to the DL an event when visitors scroll to:', 'full-picture-analytics-cookie-notice' ),
		'placeholder'		=> esc_html__( 'e.g. 25, 50, 75', 'full-picture-analytics-cookie-notice'),
		'after field'		=> esc_html__( '% of page height', 'full-picture-analytics-cookie-notice'),
		'field_id' 			=> 'track_scroll',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[track_scroll]',
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL AJAX-triggered URL changes', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_dynamic_urls',
		'option_arr_id'		=> $option_arr_id,
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL clicks on anchors', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_anchor_clicks',
		'option_arr_id'		=> $option_arr_id,
		'popup'				=> esc_html__( 'Tracks clicks in links that lead to different sections on the same page.', 'full-picture-analytics-cookie-notice'),
	),
);

// USER DATA FIELDS

$user_fields = array();


$user_fields = array_merge( $user_fields, array(
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL what country and region (optional) the visitor is from', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_user_country',
		'must_have'			=> 'pro geo',
		'option_arr_id'		=> $option_arr_id,
	),
));

$user_fields = array_merge( $user_fields, array(
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL how long visitors are actively engaged with the website\'s content', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_engagement',
		'must_have'			=> 'pro',
		'option_arr_id'		=> $option_arr_id,
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL user ID of logged-in users', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'user_id',
		'must_have'			=> 'pro',
		'option_arr_id'		=> $option_arr_id,
		'after field'		=> esc_html__( 'User ID is sent in an encoded format','full-picture-analytics-cookie-notice' ),
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL user\'s login status and role', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'user_role',
		'option_arr_id'		=> $option_arr_id,
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL visitor\'s browser language', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'browser_lang',
		'option_arr_id'		=> $option_arr_id,
	),
));


// WP DATA FIELDS

$wpdata_fields = array(
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL page language', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'page_lang',
		'option_arr_id'		=> $option_arr_id,
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL page titles', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'page_title',
		'option_arr_id'		=> $option_arr_id,
		'after field'		=> esc_html__( 'Sends both, default post/page titles and meta titles (SEO titles).', 'full-picture-analytics-cookie-notice'),
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL page type', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'page_type',
		'option_arr_id'		=> $option_arr_id,
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL page IDs', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'page_id',
		'option_arr_id'		=> $option_arr_id,
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL page numbers and archive page numbers', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'page_num',
		'option_arr_id'		=> $option_arr_id,
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL post\'s terms (categories, tags, etc.) ', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'post_date',
		'option_arr_id'		=> $option_arr_id,
		'popup'				=> '<p>' . esc_html__('This will track what categories, tags and formats are associated with posts and pages. You can enable tracking other terms and / or post types in the "General Settings" > "Default Tracking Settings".','full-picture-analytics-cookie-notice') . '</p>',
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL categories, tags and other terms', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'terms',
		'option_arr_id'		=> $option_arr_id,
		'popup'				=> '<p>' . esc_html__('By default WP Full Picture tracks categories, tags and formats that are attached to posts and pages. You can enable tracking other terms in the "General Settings" > "Default Tracking Settings".','full-picture-analytics-cookie-notice') . '</p>',
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL search terms', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'search_terms',
		'option_arr_id'		=> $option_arr_id,
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL the number of search results', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'search_results',
		'option_arr_id'		=> $option_arr_id,
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Send to the DL author\'s display names', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'author',
		'option_arr_id'		=> $option_arr_id,
		'popup2'			=> '<p>' . esc_html__( 'Please make sure that the author\'s display names that you will be tracking are not their real names and cannot be used for their identification (it is against Google\'s policy)', 'full-picture-analytics-cookie-notice' ) . '</p>',
	),
);


if ( isset( $this->main['show_author_id'] ) ){
	$auth_id_field = array(
		array(
			'type'	 			=> 'toggle',
			'label' 			=> esc_html__( 'Send to the DL authors IDs', 'full-picture-analytics-cookie-notice' ),
			'field_id' 			=> 'author_id',
			'option_arr_id'		=> $option_arr_id,
		),
	);
	
	$wpdata_fields = array_merge( $wpdata_fields, $auth_id_field );
}


$custom_data_ids_fields = array(
	array(
		'type'	 			=> 'r3',
		'label' 			=> esc_html__( 'Send to the DL custom metadata', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_cf',
		'must_have'			=> 'pro trackmeta',
		'class'				=> 'fupi_metadata_tracker fupi_simple_r3',
		'option_arr_id'		=> $option_arr_id,
		'is_repeater'		=> true,
		'popup'				=> '<p>' . esc_html__( 'This setting lets you track custom metadata that was previously registerd in the Metadata Tracking page.', 'full-picture-analytics-cookie-notice' ) . '</p>',
		'fields'			=> array(
			array(
				'type'				=> 'custom_meta_select',
				'field_id'			=> 'id',
				'class'		=> 'fupi_col_50_grow',
				'required'			=> true,
			),
		),
	),
);

$wpdata_fields = array_merge( $wpdata_fields, $custom_data_ids_fields );

// ALL TOGETHER

$sections = array(

	// BASIC SETUP

	array(
		'section_id' => 'fupi_gtm_main',
		'section_title' => esc_html__( 'Installation', 'full-picture-analytics-cookie-notice' ),
		'fields' => $basic_fields,
	),
	
	// EVENT TRACKING

	array(
		'section_id' => 'fupi_gtm_events',
		'section_title' => esc_html__( 'Tracking events', 'full-picture-analytics-cookie-notice' ),
		'fields' => $events_fields,
	),

	// USER DATA TRACKING

	array(
		'section_id' => 'fupi_gtm_users',
		'section_title' => esc_html__( 'Tracking user info', 'full-picture-analytics-cookie-notice' ),
		'fields' => $user_fields,
	),

	// WP DATA

	array(
		'section_id' => 'fupi_gtm_wpdata',
		'section_title' => esc_html__( 'Tracking WP data', 'full-picture-analytics-cookie-notice' ),
		'fields' => $wpdata_fields,
	),
);


// CUSTOM EVENTS BUILDER


$adv_triggers_section = array(
	array(
		'section_id' => 'fupi_gtm_atrig',
		'section_title' => esc_html__( 'Tracking lead scores and custom events', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track when specific conditions are met', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'custom_events',
				'must_have'			=> 'pro atrig',
				'class'				=> 'fupi_events_builder fupi_fullwidth_tr',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'fields'			=> array(
					array(
						'label'				=> esc_html__( 'When this happens', 'full-picture-analytics-cookie-notice' ),
						'type' 				=> 'atrig_select',
						'field_id'			=> 'atrig_id',
						'class'		=> 'fupi_col_30',
						'required'			=> true,
						'format'			=> 'key'
					),
					array(
						'type'	 			=> 'select',
						'label' 			=> esc_html__( '...for...', 'full-picture-analytics-cookie-notice' ),
						'field_id' 			=> 'repeat',
						'option_arr_id'		=> $option_arr_id,
						'class'		=> 'fupi_col_15',
						'options'			=> array(
							'no'				=> esc_html__( 'The first time', 'full-picture-analytics-cookie-notice' ),
							'yes'				=> esc_html__( 'Every time', 'full-picture-analytics-cookie-notice' ),
						),
					),
					array(
						'type'				=> 'text',
						'label'				=> esc_html__( 'Send dataLayer event', 'full-picture-analytics-cookie-notice' ),
						'placeholder'		=> esc_html__( 'event_name', 'full-picture-analytics-cookie-notice' ),
						'field_id'			=> 'evt_name',
						'el_class'			=> 'fupi_events_builder_evt',
						'required'			=> true,
						'class'		=> 'fupi_col_20',
					),
					array(
						'type'				=> 'number',
						'label'				=> esc_html__( 'Value (optional)', 'full-picture-analytics-cookie-notice' ),
						'field_id'			=> 'evt_val',
						'required'			=> true,
						'class'		=> 'fupi_col_20',
					),
				),
			)
		),
	),
);

$sections = array_merge( $sections, $adv_triggers_section );

// WOOCOMMERCE

$woo_section = array(
	array(
		'section_id' => 'fupi_gtm_ecomm',
		'section_title' => esc_html__( 'WooCommerce tracking', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Do NOT clear the ecommerce object in the dataLayer before each push', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'clear_woo_data',
				'must_have'			=> 'woo',
				'option_arr_id'		=> $option_arr_id,
				'after field'		=> 'Not recommended. <a href="https://wpfullpicture.com/support/documentation/why-full-picture-clears-product-arrays-in-the-datalayer-before-pushing-new-ones/?utm_source=fp_admin&utm_medium=referral&utm_campaign=settings_link" target="_blank">Learn more</a>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Send to the DL customer\'s name and surname', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'user_realname',
				'must_have'			=> 'pro woo',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Send to the DL customer\'s email address', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'user_email',
				'must_have'			=> 'pro woo',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Send to the DL customer\'s phone number', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'user_phone',
				'must_have'			=> 'pro woo',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Send to the DL customer\'s physical address', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'user_address',
				'must_have'			=> 'pro woo',
				'option_arr_id'		=> $option_arr_id,
			),
		),
	),
);

$sections = array_merge( $sections, $woo_section );

?>
