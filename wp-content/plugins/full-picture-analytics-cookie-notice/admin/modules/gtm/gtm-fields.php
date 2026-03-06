<?php

$option_arr_id = 'fupi_gtm';

$sections = array(

	// INSTALLATION

	array(
		'section_id' => 'fupi_gtm_main',
		'section_title' => esc_html__( 'Installation', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Container ID', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'id',
				'class'				=> 'fupi_required',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[id]',
				'placeholder'		=> 'GTM-0000000',
				'popup'				=> '<p>' . sprintf ( esc_html__('To install Google Tag Manager\'s container on this site, please paste the Container ID in the form. Please %1$sfollow this guide%2$s if you do not know where to find this ID.', 'full-picture-analytics-cookie-notice' ), '<a href="https://www.optimizesmart.com/how-to-get-google-tag-manager-container-id/" target="_blank">','</a>' ) . '</p>',
			),
			array(
				'type'	 			=> 'radio',
				'label' 			=> esc_html__( 'DataLayer name', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'datalayer',
				'options'			=> array(
					'fupi_datalayer'	=> esc_html__( 'fupi_dataLayer (recommended)', 'full-picture-analytics-cookie-notice' ),
					'default'			=> esc_html__( 'dataLayer', 'full-picture-analytics-cookie-notice' ),
				),
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . esc_html__( 'Google Analytics, Ads and Tag Manager use the same datalayer which may lead to duplicate events. Prevent it by renaming it to fupi_datalayer.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
		),
	),

	// LOADING & PRIVACY

	array(
		'section_id' => 'fupi_gtm_loading',
		'section_title' => esc_html__( 'Loading & privacy', 'full-picture-analytics-cookie-notice' ),
	),
	
	// SIMPLE EVENTS

	array(
		'section_id' => 'fupi_gtm_events',
		'section_title' => esc_html__( 'Simple events', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track clicks on outbound links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_outbound',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__( 'Send to the dataLayer clicks on all links that lead to other domains. Attention! Affiliate links leading to other sites are also treated as outbound.', 'full-picture-analytics-cookie-notice'),
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track clicks on email and tel. links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_email_tel',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track clicks on affiliate links', 'full-picture-analytics-cookie-notice' ),
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
				'label' 			=> esc_html__( 'Track clicks on file download links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_file_downl',
				'placeholder'		=> 'e.g. pdf, doc, docx, xls, xlsx, txt',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_file_downl]',
				'under field'		=> esc_html__( 'Enter comma separated list of file formats (extensions) you want to track', 'full-picture-analytics-cookie-notice'),
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track clicks on page elements', 'full-picture-analytics-cookie-notice' ),
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
					<p class="fupi_warning_text">' . esc_html__( 'To correctly track clicks in page elements OTHER than links (e.g. buttons), you need to provide CSS selectors of ALL clickable elements inside that element.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'The easiest way to do it is to use the asterisk symbol "*". For example, to track clicks in buttons provide:', 'full-picture-analytics-cookie-notice' ) . ' <code>.my_button, .my_button *</code>.</p>
					<p><a href="https://wpfullpicture.com/support/documentation/how-to-track-clicks-in-page-page-elements/" target="_blank">' . esc_html__( 'Learn more about tracking clicks', 'full-picture-analytics-cookie-notice' ) . '</a></p>',
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track form submissions', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_forms',
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
				'label' 			=> esc_html__( 'Track when the window visibility state changes', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_focus',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__( 'This will send an event every time the window gets "focus" or "blur" visibility state, e.g. when visitor starts viewing a webpage (focus) or moves to a different browser tab (blur).', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track when page elements show on screen', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_views',
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
						'placeholder'		=> esc_html__( 'Element name (required)', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'				=> 'fupi_col_35_grow',
						'required'			=> true,
					),
				)
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track when visitors scroll to:', 'full-picture-analytics-cookie-notice' ),
				'placeholder'		=> esc_html__( 'e.g. 25, 50, 75', 'full-picture-analytics-cookie-notice'),
				'after field'		=> esc_html__( '% of page height', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'track_scroll',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_scroll]',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track AJAX-triggered URL changes', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_dynamic_urls',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track clicks on anchors', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_anchor_clicks',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> esc_html__( 'Tracks clicks in links that lead to different sections on the same page.', 'full-picture-analytics-cookie-notice'),
			),
		),
	),

	// COMPLEX EVENTS

	array(
		'section_id' => 'fupi_gtm_atrig',
		'section_title' => esc_html__( 'Custom events', 'full-picture-analytics-cookie-notice' ),
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
						'class'				=> 'fupi_col_50',
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
						'label'				=> esc_html__( 'Send dataLayer event', 'full-picture-analytics-cookie-notice' ),
						'placeholder'		=> esc_html__( 'event_name', 'full-picture-analytics-cookie-notice' ),
						'field_id'			=> 'evt_name',
						'el_class'			=> 'fupi_events_builder_evt',
						'required'			=> true,
						'class'				=> 'fupi_col_30',
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
									'path'			=> esc_html__( 'Path to a JS variable', 'full-picture-analytics-cookie-notice' ),
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

	// PARAMETERS

	array(
		'section_id' => 'fupi_gtm_wpdata',
		'section_title' => esc_html__( 'Event parameters', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track page language', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'page_lang',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track page titles', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'page_title',
				'option_arr_id'		=> $option_arr_id,
				'after field'		=> esc_html__( 'Sends both, default post/page titles and meta titles (SEO titles).', 'full-picture-analytics-cookie-notice'),
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track page type', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'page_type',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track page IDs', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'page_id',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track page numbers and archive page numbers', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'page_num',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track post\'s terms (categories, tags, etc.) ', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'post_date',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . esc_html__('This will track the categories, tags and formats of posts and pagesYou can enable tracking other terms in the "Shared tracking settings" > "Default settings".','full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track categories, tags and other terms', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'terms',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . esc_html__('By default WP Full Picture tracks categories, tags and formats of posts and pages. You can enable tracking other terms in the "Shared tracking settings" > "Default settings".','full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track search terms', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'search_terms',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track the number of search results', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'search_results',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track author\'s display names', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'author',
				'option_arr_id'		=> $option_arr_id,
				'popup2'			=> '<p>' . esc_html__( 'Please make sure that the author\'s display names that you will be tracking are not their real names and cannot be used for their identification (it is against Google\'s policy)', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track authors IDs', 'full-picture-analytics-cookie-notice' ),
				'must_have'			=> 'field|fupi_track|show_author_id|exists|Enable_tracking_authors_IDs_in_Shared_Tracking_Settings',
				'field_id' 			=> 'author_id',
				'option_arr_id'		=> $option_arr_id,
				'popup3'			=> '<p style="color: red">' . esc_html__('Do not enable this option if authors of content of your site have administrator rights. This will expose their IDs making attacks easier. Don\'t help attackers hack your site.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track metadata', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_cf',
				'must_have'			=> 'pro',
				'class'				=> 'fupi_metadata_tracker fupi_simple_r3',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'popup'				=> '<p>' . esc_html__( 'This setting lets you track metadata (hidden and/or custom data of your content, users and post/page terms).', 'full-picture-analytics-cookie-notice' ) . '</p>
						<p>' . esc_html__( 'To track metadata you need to register it in the "Shared tracking settings" > "Extra tracking functions". After you do this, refresh this page and choose what you want to track.', 'full-picture-analytics-cookie-notice' ) . '</p>',
				'fields'			=> array(
					array(
						'type'				=> 'custom_meta_select',
						'field_id'			=> 'id',
						'class'		=> 'fupi_col_50_grow',
						'required'			=> true,
					),
				),
			),
		),
	),

	// USER DATA TRACKING

	array(
		'section_id' => 'fupi_gtm_users',
		'section_title' => esc_html__( 'Tracking user info', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track what country and region (optional) the visitor is from', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_user_country',
				'must_have'			=> 'pro geo',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track how long visitors are actively engaged with the website\'s content', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_engagement',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track user ID of logged-in users', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'user_id',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'after field'		=> esc_html__( 'User ID is sent in an encoded format','full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track user\'s login status and role', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'user_role',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track visitor\'s browser language', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'browser_lang',
				'option_arr_id'		=> $option_arr_id,
			),
		),
	),

	// WOO

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
				'after field'		=> sprintf( esc_html__( 'Not recommended. %1$sLearn more%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/why-full-picture-clears-product-arrays-in-the-datalayer-before-pushing-new-ones/" target="_blank">', '</a>' ),
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track customer\'s name and surname', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'user_realname',
				'must_have'			=> 'pro woo',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track customer\'s email address', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'user_email',
				'must_have'			=> 'pro woo',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track customer\'s phone number', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'user_phone',
				'must_have'			=> 'pro woo',
				'option_arr_id'		=> $option_arr_id,
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Track customer\'s physical address', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'user_address',
				'must_have'			=> 'pro woo',
				'option_arr_id'		=> $option_arr_id,
			),
		),
	),
);

?>
