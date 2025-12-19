<?php

$option_arr_id = 'fupi_pla';

// ALL TOGETHER

$sections = array(

	// INSTALLATION

	array(
		'section_id' => 'fupi_pla_install',
		'section_title' => esc_html__( 'Installation', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'radio',
				'label' 			=> esc_html__( 'Use this module to', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'pla_use',
				'option_arr_id'		=> $option_arr_id,
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_pla_use_cond',
				'options' 			=> array(
					'install'			=> esc_html__( 'Install Plausible and track user actions and WP data', 'full-picture-analytics-cookie-notice'),
					'extend'			=> sprintf( esc_html__( 'Extend the %1$sPlausible plugin%2$s with extra tracking features', 'full-picture-analytics-cookie-notice'), '<a href="https://wordpress.org/plugins/plausible-analytics/" target="_blank">', '</a>' ),
				),
				'default'			=> 'install',
				'under field'		=> '<button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_installation_modes_popup">' . esc_html__( 'How do these modes differ', 'full-picture-analytics-cookie-notice' ) . '</button>',
				'popup_id'			=> 'fupi_installation_modes_popup',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Your website domain', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'domain',
				'class'				=> 'fupi_sub fupi_pla_use_cond fupi_cond_val_install fupi_disabled',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[domain]',
				'placeholder'		=> $_SERVER['HTTP_HOST'],
				'default'			=> $_SERVER['HTTP_HOST'],
				'popup'				=> '<p>' . esc_html__('This field was automatically filled with the domain of your site. Change it if it is incorrect for any reason.', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Script source domain', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'custom_domain',
				'class'				=> 'fupi_sub fupi_pla_use_cond fupi_cond_val_install fupi_disabled',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[custom_domain]',
				'default'			=> 'plausible.io',
				'popup'				=> '<p>'. esc_html__('This field should contain the domain where the Plausible script is hosted. It can be:','full-picture-analytics-cookie-notice' ) . '</p>
					<ol>
						<li>' . esc_html__('Plausible\'s own domain (plausible.io - default)','full-picture-analytics-cookie-notice') . '</li>
						<li>' . sprintf( esc_html__('or the domain of your self-hosted plausible installation. %1$sLearn more (advanced)%2$s','full-picture-analytics-cookie-notice'), '<a href="https://github.com/plausible/community-edition/" target="_blank">', '</a>' ) . '</li>
					</ol>',
				'under field'		=> esc_html__('Default:','full-picture-analytics-cookie-notice') . ' plausible.io',
			),
		),
	),

	// LOADING

	array(
		'section_id' => 'fupi_pla_loading',
		'section_title' => esc_html__( 'Loading', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Force load', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'force_load',
				'el_class'			=> 'fupi_condition fupi_condition_reverse',
				'el_data_target'	=> 'fupi_load_opts',
				'option_arr_id'		=> $option_arr_id,
				'popup2'			=> sprintf( esc_html__( 'When this option is enabled, it will load Plausible\'s tracking script for all visitors - even for administrators, bots, excluded users and locations. %1$sLearn more%2$s.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/support/documentation/validation-mode/?utm_source=fp_admin&utm_medium=fp_link">', '</a>' ),
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__('Only track visitors from specific countries', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'limit_country',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_load_opts fupi_adv',
				'must_have'			=> 'pro geo',
				'is_repeater'		=> false,
				'popup'				=> sprintf( esc_html__('Enter a list of 2-character %1$scountry codes%2$s separated by comas.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://www.iban.com/country-codes">', '</a>' ) . '<br><br>' . esc_html__('If visitor\'s country is not recognized Plausible will load normally. Location is checked using the method chosen in the settings of the Geolocation module.', 'full-picture-analytics-cookie-notice' ),
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
	
	// EVENT TRACKING

	array(
		'section_id' => 'fupi_pla_events_2',
		'section_title' => esc_html__( 'Simple events', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track clicks in contact links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_contact_links',
				'class'				=> 'fupi_adv',
				'placeholder'		=> esc_html__('Name, e.g. "Contact link clicks"','full-picture-analytics-cookie-notice'),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_contact_links]',
				'popup2'			=> '
					<h3>' . esc_html__( 'What you need to know', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<ol class="fupi_warning_text">
						<li>' . esc_html__( 'In order to use this option, you need to use Plausible Business plan. This is because clicks in contact links are tracked as goals with parameters, which can be viewed only in the Business plan.', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'If you do not use a Plausible Business plan, and you still want to track clicks in contact links, then set up tracking clicks in page elements (see options below).', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'When you enable this option, WP FP will track the last 5 digits of the phone number and the part of the email address before the "@" symbol. Tracking full numbers and addresses is against Plausible\'s Terms of service.', 'full-picture-analytics-cookie-notice' ) . '</li>
					</ol>',
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track affiliate links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_affiliate_2',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'class'				=> 'fupi_simple_r3 fupi_adv',
				'btns_class'		=> 'fupi_push_right',
				'popup2' 			=> '<h3>' . esc_html__( 'An extra feature', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . esc_html__( 'You can leave the "Link name" field empty or use a placeholder [name].', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'When you leave it empty, then link\'s URL will become its name.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'If you use [name] placeholder, then it will be replaced with the first 20 characters of the text inside the clicked element (if it has any).', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p class="fupi_warning_text">' . esc_html__( 'Attention. using any of these 2 options will create unique names. Use them only if you send them as parameters (in the option below) or if you have very few links to track.', 'full-picture-analytics-cookie-notice' ) . '</p>',

					
				'fields'			=> array(
					array(
						'placeholder'		=> esc_html__( 'URL part, e.g. /go/', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'sel',
						'class'		=> 'fupi_col_35_grow',
						'required'			=> true,
					),
					array(
						'placeholder'		=> esc_html__( 'Link name', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'val',
						'class'		=> 'fupi_col_35_grow',
					),
				)
			),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'Track as an event with properties (not recommended)', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_affiliate_goalname',
					'class'				=> 'fupi_sub fupi_adv',
					'placeholder'		=> esc_html__('Name, e.g. "Affiliate link clicks"','full-picture-analytics-cookie-notice'),
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[track_affiliate_goalname]',
				),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track clicks on page elements', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_elems_2',
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
					'label' 			=> esc_html__( 'Track as an event with properties (not recommended)', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_elems_goalname',
					'class'				=> 'fupi_sub fupi_adv',
					'placeholder'		=> esc_html__('Name, e.g. "Clicks in page elements"','full-picture-analytics-cookie-notice'),
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[track_elems_goalname]',
				),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track form submissions', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_forms_2',
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
						<p><a class="button-secondary" target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-choose-the-best-way-to-track-form-submissions/">' . esc_html__( 'Choose correct method to track your forms.' , 'full-picture-analytics-cookie-notice' ) . '</a></p>',
			),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'Track as an event with properties (not recommended)', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_forms_goalname',
					'class'				=> 'fupi_sub fupi_adv',
					'placeholder'		=> esc_html__('Name, e.g. "Form submissions"','full-picture-analytics-cookie-notice'),
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[track_forms_goalname]',
				),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track clicks in file download links', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_file_downl_goalname',
				'class'				=> 'fupi_adv',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_downl_subs',
				'placeholder'		=> esc_html__('Name, e.g. "File downloads"', 'full-picture-analytics-cookie-notice'),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_file_downl_goalname]',
				'popup2'			=> '
					<h3>' . esc_html__( 'What you need to know', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<ol class="fupi_warning_text">
						<li>' . esc_html__( 'In order to use this option, you need to use Plausible Business plan. This is because downloads are tracked as goals with parameters, which can be viewed only in the Business plan.', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'If you do not use a Plausible Business plan, and you still want to track clicks in download links, then set up tracking clicks in page elements (see options below).', 'full-picture-analytics-cookie-notice' ) . '</li>
					</ol>',
			),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'Tracked file formats', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_file_downl',
					'placeholder'		=> esc_html__('e.g. pdf, doc, docx, xls, xlsx, txt','full-picture-analytics-cookie-notice'),
					'class'				=> 'fupi_sub fupi_downl_subs fupi_disabled fupi_adv',
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[track_file_downl]',
					'under field'		=> esc_html__( 'Enter coma separated list of file formats (extensions) you want to track', 'full-picture-analytics-cookie-notice'),
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
					'label' 			=> esc_html__( 'Track as an event with properties (not recommended)', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_visib_goalname',
					'class'				=> 'fupi_sub fupi_adv',
					'placeholder'		=> esc_html__('Name, e.g. "Viewed page elements"', 'full-picture-analytics-cookie-notice'),
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[track_visib_goalname]',
				),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track how many people decline cookies', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_cookie_decline_2',
				'class'				=> 'fupi_adv',
				'placeholder'		=> esc_html__('Name, e.g. "Cookies declined"','full-picture-analytics-cookie-notice'),
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_cookie_decline_2]',
			),
		),
	),

	array(
		'section_id' => 'fupi_pla_cond',
		'section_title' => esc_html__( 'Custom events', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track when specific conditions are met', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'custom_events',
				'class'				=> 'fupi_events_builder fupi_fullwidth_tr fupi_adv',
				'must_have'			=> 'pro atrig',
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
						'label'				=> esc_html__( 'Send', 'full-picture-analytics-cookie-notice' ),
						'placeholder'		=> esc_html__( 'Name', 'full-picture-analytics-cookie-notice' ),
						'field_id'			=> 'evt_name',
						'el_class'			=> 'fupi_events_builder_evt',
						'required'			=> true,
						'class'		=> 'fupi_col_20',
					),
				),
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track as an event with properties (not recommended)', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'custom_track_goalname',
				'must_have'			=> 'pro atrig',
				'class'				=> 'fupi_sub fupi_adv',
				'placeholder'		=> esc_html__('Name, e.g. "Custom events"', 'full-picture-analytics-cookie-notice'),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[custom_track_goalname]',
			),
		)
	),

	// EVENT PROPERTIES

	array(
		'section_id' => 'fupi_pla_wpdata',
		'section_title' => esc_html__( 'Properties of the pageview event', 'full-picture-analytics-cookie-notice' ),
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
				'label' 			=> esc_html__( 'Track page id', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_pageid',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
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
				'label' 			=> esc_html__( 'Track user\'s login status and role', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_user_role',
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
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Track metadata', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_cf',
				'class'				=> 'fupi_metadata_tracker fupi_simple_r3 fupi_adv',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'fields'			=> array(
					array(
						'type'				=> 'custom_meta_select',
						'field_id'			=> 'id',
						'required'			=> true,
					),
					array(
						'type'				=> 'text',
						'field_id'			=> 'param_name',
						'required'			=> true,
						'format'			=> 'key',
						'placeholder'		=> esc_html__('parameter name','full-picture-analytics-cookie-notice'),
					),
				),
				'popup3'			=> '<p>' . esc_html__( 'This setting lets you track metadata (hidden and/or custom data of your content, users and post/page terms).', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . esc_html__( 'To track metadata you need to register it in the "Shared tracking settings" > "Extra tracking functions". After you do this, refresh this page and choose what you want to track.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'The tracked values will be sent to Plausible as properties.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p style="color: red">' . esc_html__( 'Attention. Do not track information that can be used to identify users. If you do this, Plausible will no longer comply with GDPR and other privacy regulations because then it will have to be loaded after visitors give consent to tracking (and this function is unavailable for Plausible).', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
		),
	),

	// WOOCOMMERCE

	array(
		'section_id' => 'fupi_pla_ecomm',
		'section_title' => esc_html__( 'WooCommerce tracking', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track purchases', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_woo_purchases',
				'must_have'			=> 'woo',
				'placeholder'		=> esc_html__('Name, e.g. "Purchase"', 'full-picture-analytics-cookie-notice'),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_purchases]',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_track_woo_purchases',
			),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'Track purchased items', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_woo_purchased_items',
					'must_have'			=> 'woo',
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[track_purchased_items]',
					'placeholder'		=> esc_html__('Name, e.g. "Purchased item"', 'full-picture-analytics-cookie-notice'),
					'class'				=> 'fupi_sub fupi_track_woo_purchases fupi_disabled',
				),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track when a customer begins checkout', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_woo_checkouts',
				'must_have'			=> 'woo',
				'placeholder'		=> esc_html__('Name, e.g. "Started checkout"', 'full-picture-analytics-cookie-notice'),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_checkouts]',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_track_woo_checkouts',
			),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'Track items in cart during checkout', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'track_woo_checkout_items',
					'must_have'			=> 'woo',
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[track_checkout_items]',
					'placeholder'		=> esc_html__('Name, e.g. "Checkout item"', 'full-picture-analytics-cookie-notice'),
					'class'				=> 'fupi_sub fupi_track_woo_checkouts fupi_disabled',
					'under field'		=> esc_html__( 'Attention! Each item in cart is tracked with a separate event!', 'full-picture-analytics-cookie-notice'),
				),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track adding products to cart', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_woo_addtocart',
				'must_have'			=> 'woo',
				'placeholder'		=> esc_html__('Name, e.g. "Added to cart"', 'full-picture-analytics-cookie-notice'),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_addtocart]',
				'under field'		=> esc_html__( 'Attention! If a grouped product is added to cart, each item in the group will be tracked with a separate event!', 'full-picture-analytics-cookie-notice'),
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Track adding products to a wishlist', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_woo_addtowishlist',
				'must_have'			=> 'woo',
				'placeholder'		=> esc_html__('Name, e.g. "Added to wishlist"','full-picture-analytics-cookie-notice'),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_addtowishlist]',
			),
		),
	),

	// STATISTICS IN ADMIN

	array(
		'section_id' => 'fupi_pla_stats',
		'section_title' => esc_html__( 'Statistics in WP admin', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Shared link URL', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'shared_link_url',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[shared_link_url]',
				'placeholder'		=> esc_html__('Your shared URL', 'full-picture-analytics-cookie-notice'),
				'under field'		=> '<p>' . sprintf( esc_html__( '%1$sGet your shared link URL%2$s', 'full-picture-analytics-cookie-notice'), '<a href="https://plausible.io/docs/shared-links" target="_blank">', '</a>' ) . '</p>'
			),
			array(
				'type' 				=> 'user_search',
				'field_id' 			=> 'selected_users',
				'label' 			=> esc_html__( 'Let specific users enter the "Reports" page', 'full-picture-analytics-cookie-notice' ),
				'must_have'			=> 'pro admin',
				'option_arr_id' 	=> $option_arr_id,
				'popup2'			=> '<p>' . esc_html__( 'By default, only administrators can access the "Reports" page.', 'full-picture-analytics-cookie-notice') . '</p>
				<p class="fupi_warning_text">' . esc_html__( 'As a security measure, the "Reports" page will NOT be accessible to users without the right to edit posts.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
		),
	),
);

?>
