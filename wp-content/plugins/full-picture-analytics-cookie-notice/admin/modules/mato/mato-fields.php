<?php

$option_arr_id = 'fupi_mato';

// BASIC SECTION FIELDS

$basic_fields = array(
	array(
		'type'	 			=> 'number',
		'label' 			=> esc_html__( 'Site ID', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'id',
		'class'				=> 'fupi_required',
		'required'			=> true,
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[id]',
		'under field'		=> '<p><a href="https://wpfullpicture.com/support/documentation/how-to-install-matomo-on-premise-and-cloud/">' . esc_html__( 'How to install Matomo', 'full-picture-analytics-cookie-notice' ) . '</a></p>',
	),
	array(
		'type'	 			=> 'url',
		'label' 			=> esc_html__( 'Matomo dashboard URL', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'url',
		'class'				=> 'fupi_sub fupi_required',
		'required'			=> true,
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[url]',
		'placeholder'		=> 'https://....com/',
	),
	array(
		'type'	 			=> 'text',
		'label' 			=> esc_html__( 'Tracking script\'s URL (only Matomo Cloud)', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'src',
		'required'			=> true,
		'class'				=> 'fupi_sub',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[src]',
		'placeholder'		=> '//cdn.matomo.../matomo.js',
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Track subdomains', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_subdomains',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[track_subdomains]',
		'popup'				=> esc_html__( 'Use this to track users across the main domain and any of its subdomains, e.g. store.example.com. For this to work, you need to also install this Matomo script (with the same siteID) on all of the subdomains you want to track and have this option enabled.', 'full-picture-analytics-cookie-notice' ),
	),
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Track logged-in users across devices and browsers', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'set_user_id',
		'must_have'			=> 'pro',
		'option_arr_id'		=> $option_arr_id,
		'popup3'			=> '<p>' . esc_html__( 'WP Full Picture will send encoded User IDs of your logged in visitors. It will allow Matomo to recognize users when they log-in to your site on different browsers and devices.', 'full-picture-analytics-cookie-notice') . '<p>
			<h3>' . esc_html__( 'GDPR compliance', 'full-picture-analytics-cookie-notice') . '</h3>
			<p style="color: red;">' . esc_html__('If your visitors come from a country where they have to consent before they are tracked, then you should enable consent banner in optin or automatic mode and add to your privacy policy information about sending User IDs to Matomo.', 'full-picture-analytics-cookie-notice') . '</p>
			<p style="color: red;">' . esc_html__('User ID is treated as personal information that can be used to identify a person so it won\'t be tracked if visitors decline using their personal data for statistics.', 'full-picture-analytics-cookie-notice') . '</p>',
	),
);

// LOADING

$loading_fields = array(
	array(
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Privacy mode', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'no_cookies',
		'option_arr_id'		=> $option_arr_id,
		'popup_id'			=> 'fupi_privacy_mode_popup',
	),
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
		'class'				=> 'fupi_load_opts',
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
		'class'				=> 'fupi_load_opts',
		'must_have'			=> 'pro geo',
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
		'popup'				=> '<p>' . sprintf( esc_html__('Enter a list of 2-character %1$scountry codes%2$s separated by comas.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://www.iban.com/country-codes">', '</a>' ) . '</p><p>'. esc_html__('Location is checked using the method chosen in the settings of the Geolocation module.', 'full-picture-analytics-cookie-notice' ) . '</p>',
	),
);

// WP DATA FIELDS

$wpdata_fields = array(
	array( // ok
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Track unmodified page titles', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'clean_page_title',
		'el_class'			=> 'fupi_condition',
		'el_data_target'	=> 'fupi_page_title_cond',
		'option_arr_id'		=> $option_arr_id,
		'popup'				=> '<p>' . esc_html__( 'By default, Matomo shows in reports page titles copied from the "title" meta tag. This is not perfect since this tag can often change (e.g. when you tweak it with an SEO plugin). The result is that your Matomo can show you reports where one page can have multiple entries, under different titles.', 'full-picture-analytics-cookie-notice') . '</p><p>' . esc_html__( 'When you enable this option, WP Full Picture will send to Matomo the default title of your page as used on the page / post / product edit screen. This will make data analysis easier.', 'full-picture-analytics-cookie-notice') . '</p>',
	),
	array( // ok
		'type'	 			=> 'number',
		'label' 			=> esc_html__( 'Track SEO titles', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'seo_title_dimens',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[seo_title_dimens]',
		'class'				=> 'fupi_sub fupi_page_title_cond fupi_disabled',
		'popup'				=> esc_html__( 'SEO titles will be tracked in a separate dimension, which makes their analysis simpler. Enter the index number of the custom dimension (with "Action" scope). 0 or empty to disable.', 'full-picture-analytics-cookie-notice'),
	),
	array( // ok
		'type'	 			=> 'number',
		'label' 			=> esc_html__( 'Track page IDs', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'page_id_dimens',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[page_id_dimens]',
		'popup'				=> esc_html__( 'Enter the index number of the custom dimension (with "Action" scope). 0 or empty to disable.', 'full-picture-analytics-cookie-notice'),
	),
	array( // ok
		'type'	 			=> 'number',
		'label' 			=> esc_html__( 'Track page types', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'page_type_dimens',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[page_type_dimens]',
		'popup'				=> esc_html__( 'Enter the index number of the custom dimension (with "Action" scope). 0 or empty to disable.', 'full-picture-analytics-cookie-notice'),
	),
	array( // ok
		'type'	 			=> 'number',
		'label' 			=> esc_html__( 'Track author\'s display names', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'author_dimens',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[author_dimens]',
		'popup'				=> esc_html__( 'Enter the index number of the custom dimension (with "Action" scope). 0 or empty to disable.', 'full-picture-analytics-cookie-notice'),
	),
	array( // ok
		'type'	 			=> 'number',
		'label' 			=> esc_html__( 'Track user\'s login status and role', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'user_role_dimens',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[user_role_dimens]',
		'popup'				=> esc_html__( 'Enter the index number of the custom dimension (with "Visit" scope). 0 or empty to disable.', 'full-picture-analytics-cookie-notice'),
	),
	array( // ok
		'type'	 			=> 'number',
		'label' 			=> esc_html__( 'Track page language', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'page_lang_dimens',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[page_lang_dimens]',
		'popup'				=> esc_html__( 'Enter the index number of the custom dimension (with "Action" scope). 0 or empty to disable.', 'full-picture-analytics-cookie-notice'),
	),
	array( // ok
		'type'	 			=> 'number',
		'label' 			=> esc_html__( 'Track post\'s terms (categories, tags, etc.) ', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'tax_terms_dimens',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[tax_terms_dimens]',
		'el_class'			=> 'fupi_condition',
		'el_data_target'	=> 'fupi_tax_terms_opts',
		'popup'				=> '<p>' . esc_html__('This will track what categories, tags and formats are associated with posts and pages. You can enable tracking other terms and / or post types in the "General Settings" > "Default Tracking Settings". Enter the index number of the custom dimension (with "Action" scope). 0 or empty to disable.','full-picture-analytics-cookie-notice') . '</p>',
	),
		array( // ok
			'type'	 			=> 'toggle',
			'label' 			=> esc_html__( 'Add taxonomy slug to term name', 'full-picture-analytics-cookie-notice' ),
			'field_id' 			=> 'add_tax_term_cat',
			'option_arr_id'		=> $option_arr_id,
			'class'				=> 'fupi_sub fupi_tax_terms_opts fupi_disabled',
			'popup'				=> esc_html__('Enable to see which taxonomy a given term belongs to. Term data in your GA reports will be then displayd like e.g. "term1 (category), term2 (tag)"' ,'full-picture-analytics-cookie-notice' ),
		),
		array( // ok
			'type'	 			=> 'toggle',
			'label' 			=> esc_html__( 'Track term names instead of term slugs', 'full-picture-analytics-cookie-notice' ),
			'field_id' 			=> 'send_tax_terms_titles',
			'option_arr_id'		=> $option_arr_id,
			'class'				=> 'fupi_sub fupi_tax_terms_opts fupi_disabled',
			'under field'		=> esc_html__('Send term titles (e.g. product category) instead of their slugs (e.g. product_category). Enabling this feature is not recommended since term names are changed more often then slugs.', 'full-picture-analytics-cookie-notice'),
		),
);

if ( isset( $this->main['show_author_id'] ) ){
	$auth_id_field = array(
		array(
			'type'	 			=> 'number',
			'label' 			=> esc_html__( 'Track authors IDs', 'full-picture-analytics-cookie-notice' ),
			'field_id' 			=> 'author_id_dimens',
			'option_arr_id'		=> $option_arr_id,
			'label_for' 		=> $option_arr_id . '[author_id_dimens]',
			'popup'				=> esc_html__( 'Enter the index number of the custom dimension (with "Action" scope). 0 or empty to disable.', 'full-picture-analytics-cookie-notice'),
		),
	);

	$wpdata_fields = array_merge( $wpdata_fields, $auth_id_field );
};


$custom_data_ids_fields = array(
	array(
		'type'	 			=> 'r3',
		'label' 			=> esc_html__( 'Track custom metadata', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_cf',
		'class'				=> 'fupi_metadata_tracker fupi_simple_r3',
		'el_class'			=> 'fupi_ga_cf_ids',
		'must_have'			=> 'pro trackmeta',
		'option_arr_id'		=> $option_arr_id,
		'is_repeater'		=> true,
		'popup'				=> '<p>' . esc_html__( 'This setting lets you track custom metadata that was previously registerd in the Metadata Tracking page.', 'full-picture-analytics-cookie-notice' ) . '</p>',
		'fields'			=> array(
			array(
				'type'				=> 'custom_meta_select',
				'field_id'			=> 'id',
				'class'		=> 'fupi_col_50',
				'required'			=> true,
			),
			array(
				'type'				=> 'number',
				'field_id'			=> 'dim',
				'class'		=> 'fupi_col_30_grow',
				'required'			=> true,
			),
		),
	),
);

$wpdata_fields = array_merge( $wpdata_fields, $custom_data_ids_fields );

// EVENT TRACKING

$click_fields = array(
	array( // ok
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Accurately measure the time users spend on each page', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'enable_hearbeat',
		'option_arr_id'		=> $option_arr_id,
		'popup'				=> '<p>' . esc_html__( 'When this feature is enabled, Matomo will count the actual time spent in the visit, as long as the user is actively viewing the page (i.e. when the tab is active and in focus). The time of visitor\'s engagement is sent to Matomo after:', 'full-picture-analytics-cookie-notice') . '</p>
		<ol>
			<li>' . esc_html__( 'switching to another browser tab after the current tab was active for at least 15 seconds.', 'full-picture-analytics-cookie-notice') . '</li>
			<li>' . esc_html__( 'navigating to another page within the same tab.', 'full-picture-analytics-cookie-notice') . '</li>
			<li>' . esc_html__( 'closing the tab.', 'full-picture-analytics-cookie-notice') . '</li>
		</ol>
		<p>' . esc_html__( 'Attention! This feature does not use WP FUll Picture\'s method of tracking visitor\'s engagement time because Matomo does not have a built in feature to calculate metrics.', 'full-picture-analytics-cookie-notice') . '</p>',
	),
	array( // ok
		'type'	 			=> 'toggle',
		'label' 			=> esc_html__( 'Track clicks on email and tel. links', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_email_tel',
		'option_arr_id'		=> $option_arr_id,
		'popup'				=> '<p>' . esc_html__( 'It will track the last 5 digits of the phone number and the part of the email address before the "@" symbol.', 'full-picture-analytics-cookie-notice' ) . '</p>',
	),
	array( // ok
		'type'	 			=> 'r3',
		'label' 			=> esc_html__( 'Track clicks on affiliate links', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_affiliate',
		'option_arr_id'		=> $option_arr_id,
		'is_repeater'		=> true,
		'class'				=> 'fupi_simple_r3',
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
				'placeholder'		=> esc_html__('Element(s) name', 'full-picture-analytics-cookie-notice' ),
				'type'				=> 'text',
				'field_id'			=> 'val',
				'class'		=> 'fupi_col_35_grow',
			),
		),
		'popup' 			=> '<p>' . esc_html__( 'In the second field you can also use a placeholder [name]. It will be replaced with the first 20 characters of the text inside the clicked element. Make sure it has any.', 'full-picture-analytics-cookie-notice' ) . '</p>',
	),
	array( // ok
		'type'	 			=> 'r3',
		'label' 			=> esc_html__( 'Track clicks on page elements', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_elems',
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
				'placeholder'		=> esc_html__('CSS selector, e.g. .menu a', 'full-picture-analytics-cookie-notice' ),
				'type'				=> 'text',
				'field_id'			=> 'sel',
				'class'		=> 'fupi_col_35_grow',
				'required'			=> true,
			),
			array(
				'placeholder'		=> esc_html__('Element(s) name', 'full-picture-analytics-cookie-notice' ),
				'type'				=> 'text',
				'field_id'			=> 'val',
				'class'		=> 'fupi_col_35_grow',
				'required'			=> true,
			),
		)
	),
	array( // ok
		'type'	 			=> 'r3',
		'label' 			=> esc_html__( 'Track form submissions', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_forms',
		'option_arr_id'		=> $option_arr_id,
		'is_repeater'		=> true,
		'class'				=> 'fupi_simple_r3',
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
				'placeholder'		=> esc_html__('Form(s) name', 'full-picture-analytics-cookie-notice'),
				'type'				=> 'text',
				'field_id'			=> 'val',
				'class'		=> 'fupi_col_35_grow',
				'required'			=> true,
			),
		),
		'popup2_id'			=> 'fupi_track_forms_popup',
	),
	array( // ok
		'type'	 			=> 'r3',
		'label' 			=> esc_html__( 'Track when page elements show on screen', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_views',
		'option_arr_id'		=> $option_arr_id,
		'is_repeater'		=> true,
		'class'				=> 'fupi_simple_r3',
		'btns_class'		=> 'fupi_push_right',
		'fields'			=> array(
			array(
				'placeholder'		=> esc_html__( 'CSS selector e.g. .side img', 'full-picture-analytics-cookie-notice' ),
				'type'				=> 'text',
				'field_id'			=> 'sel',
				'class'		=> 'fupi_col_35_grow',
				'required'			=> true,
			),
			array(
				'placeholder'		=> esc_html__( 'Element(s) name', 'full-picture-analytics-cookie-notice' ),
				'type'				=> 'text',
				'field_id'			=> 'val',
				'class'		=> 'fupi_col_35_grow',
				'required'			=> true,
			),
		),
		'popup2'				=> '<p>' . esc_html__( 'Elements are treated as "visible" when they are 200px inside the screen (you can change it in the General Settings). Each view is counted once per page view.', 'full-picture-analytics-cookie-notice') . '</p>
				<p style="color:#e47d00">' . esc_html__( 'This tracks only elements which are present in the HTML at the moment of rendering the page. To track elements added later, enable the "DOM listener" function in the General Settings.', 'full-picture-analytics-cookie-notice') . '</p>
				<p>' . sprintf( esc_html__( 'This feature uses WP Full Picture\'s method of tracking content elements instead of Matomo\'s Content Tracking. The advantage is that it does NOT require you to manually modify website\'s HTML.', 'full-picture-analytics-cookie-notice'), '<a href="https://matomo.org/guide/reports/content-tracking/">', '</a>' ). 
			'</p>',
	),
	array( // ok
		'type'	 			=> 'text',
		'label' 			=> esc_html__( 'Track when visitors scroll to:', 'full-picture-analytics-cookie-notice' ),
		'placeholder'		=> esc_html__( 'e.g. 25, 50, 75', 'full-picture-analytics-cookie-notice' ),
		'after field'		=> esc_html__( '% of page height', 'full-picture-analytics-cookie-notice'),
		'field_id' 			=> 'track_scroll',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[track_scroll]',
		'under field'		=> esc_html__( 'Separate multiple values with comas. Do not use "%" symbol.', 'full-picture-analytics-cookie-notice'),
	),
	array( // ok
		'type'	 			=> 'text',
		'label' 			=> esc_html__( 'Change what file downloads Matomo should track', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'track_downl_file_formats',
		'placeholder'		=> esc_html__('e.g. pdf, doc, docx, xls, xlsx, txt', 'full-picture-analytics-cookie-notice'),
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[track_downl_file_formats]',
		'popup2'			=> '<p>' . sprintf( esc_html__( 'By default, Matomo tracks downloads of %1$sthese file formats%2$s. Use this function to change it. Enter coma-separated list of formats that you want to track instead.', 'full-picture-analytics-cookie-notice' ), '<a href="https://matomo.org/faq/new-to-piwik/faq_47/">', '</a>' ) . '</p>
			<p style="color: #e47d00">' . esc_html__( 'Attention! Download events will not be visible in the browser console (after you enable WP FP\'s debug mode) since they are tracked by Matomo and not by WP Full Picture. ', 'full-picture-analytics-cookie-notice' ) . '</p>',
	),
);

// EVERYTHING TOGETHER

$sections = array(

	array(
		'section_id' => 'fupi_mato_install',
		'section_title' => esc_html__( 'Installation', 'full-picture-analytics-cookie-notice' ),
		'fields' => $basic_fields,
	),

	array(
		'section_id' => 'fupi_mato_loading',
		'section_title' => esc_html__( 'Loading', 'full-picture-analytics-cookie-notice' ),
		'fields' => $loading_fields,
	),

	array(
		'section_id' => 'fupi_mato_wpdata',
		'section_title' => esc_html__( 'Tracking WP data', 'full-picture-analytics-cookie-notice' ),
		'fields' => $wpdata_fields,
	),

	array(
		'section_id' => 'fupi_mato_events',
		'section_title' => esc_html__( 'Tracking events', 'full-picture-analytics-cookie-notice' ),
		'fields' => $click_fields,
	),
);

// CUSTOM EVENTS BUILDER

$adv_triggers_section = array(
	array(
		'section_id' => 'fupi_mato_atrig',
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
						'label'				=> esc_html__( 'Send Matomo event', 'full-picture-analytics-cookie-notice' ),
						'placeholder'		=> esc_html__( 'Event name', 'full-picture-analytics-cookie-notice' ),
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
		'section_id' => 'fupi_mato_ecomm',
		'section_title' => esc_html__( 'WooCommerce tracking', 'full-picture-analytics-cookie-notice' ),
	),
);

$sections = array_merge( $sections, $woo_section );

?>
