<?php

$siteURL = get_bloginfo('url') . '/';
$main_opts = get_option('fupi_main');
$magic_keyword = ! empty ( $main_opts['magic_keyword'] ) ? $main_opts['magic_keyword'] : 'tracking';
$option_arr_id = 'fupi_main';

// OTHER SETTINGS

$other_settings = array();

if ( current_user_can('manage_options') ){
	$other_settings = array( 
		array(
			'type' 				=> 'user_search',
			'field_id' 			=> 'extra_users_2',
			'label' 			=> esc_html__( 'Let specific users view and save WP Full Picture\'s settings', 'full-picture-analytics-cookie-notice' ),
			'must_have'			=> 'pro',
			'option_arr_id' 	=> $option_arr_id,
			'popup2'			=> '<p style="color: #e47d00">' . esc_html__( 'As a security measure, the edit rights cannot be given to users without the right to at least edit posts.', 'full-picture-analytics-cookie-notice' ) . '</p>',
		),
	);
}

$other_settings = array_merge( $other_settings, array(
	array(
		'type' 				=> 'email',
		'label' 			=> esc_html__( 'Send email notification when the plugin is deactivated', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'deactiv_email',
		'option_arr_id'		=> $option_arr_id,
		'placeholder'		=> esc_html__( 'e.g. john@example.com, peter@example.com, etc.', 'full-picture-analytics-cookie-notice' ),
		'under field' 		=> esc_html__('Enter a coma separated list of email addresses where you want to send the email.','full-picture-analytics-cookie-notice'),
	),
	array(
		'type' 				=> 'toggle',
		'label' 			=> esc_html__( 'Enable debug mode', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'debug',
		'option_arr_id'		=> $option_arr_id,
		'after field' 		=> sprintf(esc_html__(' %1$sLearn more%2$s.', 'full-picture-analytics-cookie-notice'), ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/?utm_source=fp_admin&utm_medium=referral&utm_campaign=settings_link" target="_blank">', '</a>'),
	),
	array(
		'type' 				=> 'text',
		'label' 			=> esc_html__( 'Change "WP Full Picture" menu item text to:', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'custom_menu_title',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[custom_menu_title]',
	),
	array(
		'type' 				=> 'toggle',
		'label' 			=> esc_html__( 'Remove all WP Full Picture\'s settings on deactivation', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'clean_all',
		'option_arr_id'		=> $option_arr_id,
	),
) );

// SECTIONS

$sections = array(

	// TRAFFIC EXCLUSION

	array(
		'section_id' => 'fupi_main_no_track',
		'section_title' => esc_html__( 'Tracking exclusions', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'roles multi checkbox',
				'label' 			=> esc_html__( 'Do not track logged-in users with these roles:', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'disable_for_roles',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[disable_for_roles]',
				'default'			=> 'administrator',
				'under field'		=> esc_html__( 'Site administrator is always excluded from tracking. If you want to test a tracking tool while being logged in as an administrator, please enable the "Force load" option in the settings of a module of a tracking tool you want to test.', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Do not track visitors who enter the site from this link:', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'magic_keyword',
				'el_class'			=> 'fupi_narrow_text_field',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[magic_keyword]',
				'before field'		=> $siteURL . '?',
				'after field'		=> '=off',
				'placeholder'		=> 'tracking',
				'format'			=> 'key',
				'default'			=> 'tracking',
				'under field'		=> '<p><a href="' . get_bloginfo('url') . '/?' . $magic_keyword . '=off"><strong>' . get_bloginfo('url') . '/?' . $magic_keyword . '=off</strong></a></p>',
				'popup' 			=> '<p>' . esc_html__( 'When someone clicks this link they will no longer be tracked no matter whether they are logged in or not. This state will be remembered by their browser until they:','full-picture-analytics-cookie-notice') . '</p>
				<ol>
					<li>' . esc_html__( 'visit the site from address that ends with "','full-picture-analytics-cookie-notice') . '?' . $magic_keyword . '=on",</li>
					<li>' . esc_html__( 'click an "eye" icon in the bottom-left corner of the screen (on the visitor-facing website),','full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'clear cookies in their browser.','full-picture-analytics-cookie-notice') . '</li>
				</ol>
				<p><strong>' . $magic_keyword . '=on</strong> or <strong>' . $magic_keyword . '=reset</strong>: ' . esc_html__( 'resets visitor\'s tracking preferences','full-picture-analytics-cookie-notice') . '</p>
				',
			),
		),
	),

	// DEFAULT SETTINGS

	array(
		'section_id' => 'fupi_main_default',
		'section_title' => esc_html__( 'Default tracking settings', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('Delay page redirect after clicking links', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'link_click_delay',
				'option_arr_id'		=> $option_arr_id,
				'popup2' 			=> '<p>' . esc_html__('This setting pauses page redirects until all tools finish tracking link clicks.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__('This can be useful when you track clicks on many elements, with many tracking tools and/or when your website is super fast.', 'full-picture-analytics-cookie-notice') . '</p>
					<p style="color: #e47d00;">' . esc_html__('Attention! Test before using in production. On rare occasions enabling this setting can cause issues with clicks on dynamic page elements like galleries or sliders.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'number',
				'label' 			=> esc_html__('Do not track form submissions if they happen within:', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'formsubm_trackdelay',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[formsubm_trackdelay]',
				'default'			=> '3',
				'after field' 		=> esc_html__('seconds after the page loads', 'full-picture-analytics-cookie-notice' ),
				'popup' 			=> '<p>' . esc_html__('Humans don\'t usually send forms right after they open a page. Set this value to a minimum of 3 seconds to prevent tracking form submittions done by bots and accidental clicks. Enter 0 or leave empty to disable. Default is 3 seconds.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'number',
				'label' 			=> esc_html__('Prevent tracking multi-clicks that happen within:', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'notrack_dblclck',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[notrack_dblclck]',
				'default'			=> '300',
				'after field' 		=> esc_html__('miliseconds', 'full-picture-analytics-cookie-notice' ),
				'popup' 			=> '<p>' . esc_html__('When your visitors click the same elements multiple times in a very short time (like "double clicks" or "rage clicks") you should track only the first one. This will make your click tracking statistics more accurate since they will not be inflated with extra clicks. Enter time between clicks that will be considered a multi click. Only the first click will be tracked. Enter 0 or leave empty to disable. Default is 300ms.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'number',
				'label' 			=> esc_html__( 'Prevent tracking page scroll depth if visitor scrolled less than:', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_scroll_min',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_scroll_min]',
				'default'			=> '200',
				'after field'		=> esc_html__( 'pixels.', 'full-picture-analytics-cookie-notice'),
				'popup'				=> '<p>' . esc_html__( 'This setting prevents tracking scrolls on short pages, when even a very small scroll can reach the bottom of the page. Default is 200px.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'number',
				'label' 			=> esc_html__( 'Prevent tracking page scroll depth if it happened in the first:', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_scroll_time',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_scroll_time]',
				'default'			=> '5',
				'after field'		=> esc_html__( 'seconds', 'full-picture-analytics-cookie-notice' ),
				'popup'				=> '<p>' . esc_html__( 'This setting prevents tracking "exploratory" scrolls - quick scrolls performed by visitors who want to see the contents of the page before devoting more time to read it.','full-picture-analytics-cookie-notice') . '</p><p>' . esc_html__('After the set time has passed page\'s scroll depth will be tracked normally.','full-picture-analytics-cookie-notice') . '</p><p>' . esc_html__('Default is 5 seconds.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('DOM Listener', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'use_mutation_observer',
				'option_arr_id'		=> $option_arr_id,
				'popup2' 			=> '
					<p>' . esc_html__('DOM Listener "listens" for changes in the HTML of your site after it has loaded. At the moment, it is used only to track when visitors see on screen popups, ads or other dynamically added page elements.', 'full-picture-analytics-cookie-notice') . '</p>
					<p style="color: #e47d00;">' . esc_html__('Attention! Test on weaker devices before using in production. This function uses DOM Mutation Observer, which can be taxing for weak processors.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'select',
				'label' 			=> esc_html__( 'Bot detection list', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'bot_list',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[bot_list]',
				'el_class'			=> 'fupi_condition fupi_condition_reverse',
				'el_data_target'	=> 'fupi_bots_opts',
				'options'				=> array(
					'basic'			=> esc_html__( 'Basic list (fast, only checks for the most common bots)', 'full-picture-analytics-cookie-notice' ),
					'big'			=> esc_html__( 'Big list (slower but much more accurate and blocks some AI bots)', 'full-picture-analytics-cookie-notice' ),
					'none'			=> esc_html__( 'None', 'full-picture-analytics-cookie-notice' ),
				),
				'default'			=> 'basic',
				'popup'				=> '<p>' . esc_html__( 'Bot detection is used to prevent bots from:','full-picture-analytics-cookie-notice' ) . '</p>
					<ol>
						<li>' . esc_html__( 'triggering server events (for tools supporting server-side tracking)','full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'making unnecessary geolocation checks (when the geolocation module is enabled),','full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'saving cookie consents','full-picture-analytics-cookie-notice' ) . '</li>
					</ol>
					<p>' . sprintf( esc_html__( 'The Big list is based on the %1$slist of crawler user agents%2$s by bentsi.','full-picture-analytics-cookie-notice' ), '<a href="https://github.com/monperrus/crawler-user-agents/blob/master/crawler-user-agents.json">', '</a>' ) . '</p>',
			),
			array(
				'type'	 			=> 'select',
				'label' 			=> esc_html__( 'Method of communication with the server', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'server_method',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[server_method]',
				'options'				=> array(
					'rest'			=> esc_html__( 'Rest API (default)', 'full-picture-analytics-cookie-notice' ),
					'ajax'			=> esc_html__( 'AJAX', 'full-picture-analytics-cookie-notice' ),
				),
				'popup'				=> '<p>' . esc_html__( 'The method you choose here will be used to send data from the visitors\'s browser to your server. This is used for server-side tracking and for sending visitor consents.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'Rest API is generally faster and less resource-intensive.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Set when elements are considered to be visible on screen', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'intersections',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[intersections]',
				'placeholder'		=> 'Top Right Bottom Left',
				'under field'		=> esc_html__( 'Leave empty to use default values', 'full-picture-analytics-cookie-notice' ) . ' -200px 0px -200px 0px',
				'popup'				=> '<p>' . esc_html__( 'By default this is set to "-200px 0px -200px 0px", which means, that elements will be considered as visible, when their top or bottom edge is 200 pixels inside the visible area of the screen.', 'full-picture-analytics-cookie-notice' ) . '</p>',	
			),
			array(
				'type'	 			=> 'taxonomies multi checkbox',
				'label' 			=> esc_html__( 'Allow tracking terms of these taxonomies:', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tracked_taxonomies',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[tracked_taxonomies]',
				'popup'		 		=> '<p>' . esc_html__('By default, WP Full Picture lets you track tags, categories and formats of your posts and pages. Use this setting to mark other term taxonomies that you may want to track.', 'full-picture-analytics-cookie-notice') . '</p>'
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('Reset engagement time counter after clicks in anchors', 'full-picture-analytics-cookie-notice'),
				'must_have'			=> 'pro',
				'field_id' 			=> 'reset_timer_on_anchor',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p><a href="https://wpfullpicture.com/support/documentation/how-engagement-time-is-calculated/">'. esc_html__('Learn more about engagement time', 'full-picture-analytics-cookie-notice') . '</a></p>
					<p>'. esc_html__('Enable this option if your website is a single page and you use anchors to navigate between its sections. Engagement time will be sent to compatible tracking tools (Google Analytics, Meta Pixel) when users click navigation anchors. If this is disabled, engagement time will be calculated normally.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('Allow tracking IDs of content authors', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'show_author_id',
				'option_arr_id'		=> $option_arr_id,
				'popup2'			=> '<p>' . esc_html__('This option adds tracking ID of content authors to Google Analytics, Matomo and GTM. After you enable it a new tracking option will show up on their settings pages. Turn it on to start tracking author IDs.','full-picture-analytics-cookie-notice') . '</p>
				<p style="color: #e47d00;">' . esc_html__('Do not enable this option if authors of content of your site have administrator rights. This will expose their IDs making attacks easier. Don\'t help attackers hack your site.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
		),
	),

	// ACCURACY TWEAKS

	array(
		'section_id' => 'fupi_main_ref',
		'section_title' => esc_html__( 'Tracking accuracy tweaks', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'multi checkbox',
				'label' 			=> esc_html__( 'Make tracking tools correctly recognize traffic from Android applications', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'auto_track_non_http',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'options' 			=> array(
					'google'				=> 'Google Search Bar',
					'facebook'				=> 'Facebook App',
					'pinterest'				=> 'Pinterest App',
					'telegram'				=> 'Telegram App',
					'slack'					=> 'Slack App',
					'tinder'		 		=> 'Tinder App',
					'linkedin'		 		=> 'LinkedIn App',
					'youtube'				=> 'YouTube App',
					'line'					=> 'Line Communicator App (JP)',
				),
				'popup'				=> '<p>' . esc_html__('This function fixes incorrect traffic source recognition by Google Analytics and other tracking tools.' , 'full-picture-analytics-cookie-notice') . '</p>
				<p>' . esc_html__('When you enable these options, traffic from chosen applications will no longer be labelled as "Direct" but will be recognized as coming from their websites, e.g. Google Search Bar > Google.com, Facebook App > Facebook.com, etc.' , 'full-picture-analytics-cookie-notice') . '</p>
				<p>' . esc_html__('Traffic from Line app will be marked as coming from https://line-android-app.jp/.' , 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Make tracking tools correctly recognize traffic from other Android applications', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_non_http',
				'class'				=> 'fupi_simple_r3 fupi_r3_fullwidth',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'fields'			=> array(
					array(
						'type'				=> 'select',
						'label'				=> esc_html__('When traffic source','full-picture-analytics-cookie-notice'),
						'field_id'			=> 'compare',
						'options'			=> array(
							'start'				=> esc_html__('Starts with','full-picture-analytics-cookie-notice'),
							'eq'				=> esc_html__('Equals','full-picture-analytics-cookie-notice'),
							'incl'				=> esc_html__('Includes','full-picture-analytics-cookie-notice'),
						),
						'class'		=> 'fupi_col_20',
					),
					array(
						'type'				=> 'text',
						'label'				=> '',
						'placeholder'		=> 'e.g. android-app://com.google.android.googlequicksearchbox/',
						'field_id'			=> 'search',
						'required'			=> true,
					),
					array(
						'type'				=> 'text',
						'label'				=> esc_html__('Report as traffic from this address','full-picture-analytics-cookie-notice'),
						'placeholder'		=> esc_html__('e.g. android-g-search-box.com','full-picture-analytics-cookie-notice'),
						'field_id'			=> 'replace',
						'required'			=> true,
					),
				),
				'popup2' 			=> '<p><a class="button-secondary" href="https://wpfullpicture.com/support/documentation/how-to-get-better-traffic-sources-information/">' . esc_html__( 'How to set it up', 'full-picture-analytics-cookie-notice' ) . '</a></p>',
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Prevent tracking conversions from payment gateways', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'switch_ref',
				'class'				=> 'fupi_simple_r3 fupi_r3_fullwidth',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'fields'			=> array(
					array(
						'type'				=> 'text',
						'label'				=> esc_html__('When this page is visited','full-picture-analytics-cookie-notice'),
						'placeholder'		=> esc_html__('e.g. Order confirmation page URL','full-picture-analytics-cookie-notice'),
						'field_id'			=> 'search',
						'required'			=> true,
					),
					array(
						'type'				=> 'text',
						'label'				=> esc_html__('Always report as traffic from','full-picture-analytics-cookie-notice'),
						'placeholder'		=> esc_html__('e.g. Checkout page URL (has to start with https://)','full-picture-analytics-cookie-notice'),
						'field_id'			=> 'replace',
						'required'			=> true,
					),
				),
				'popup'				=> '<p>' . esc_html__('This will make sure that your conversions, will be attributed to the real sources of conversion, rather than the payment gateways.' , 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type' 				=> 'multi checkbox',
				'label' 			=> esc_html__( 'Combine similar URLs of referring domains', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'join_ref',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'options'			=> array(
					'fb'		=> 'Facebook',
					'insta'		=> 'Instagram',
					'pin'		=> 'Pinterest',
				),
				'popup'				=> '<p>' . esc_html__( 'Facebook, Instagram and Pinterest use multiple URLs that redirect traffic to your site, e.g. l.facebook.com, lm.facebook.com, etc. Enable this function to combine them and analyse traffic sources more easily.', 'full-picture-analytics-cookie-notice') . ' <a target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-get-better-traffic-sources-information/?utm_source=fp_admin&utm_medium=referral&utm_campaign=documentation_link">' . esc_html__('Learn more', 'full-picture-analytics-cookie-notice') . '</a>.</p>',
			),
		),
	),

	// PERFORMANCE
	array(
		'section_id' => 'fupi_main_perf',
		'section_title' => esc_html__( 'Performance', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type' 				=> 'toggle',
				'label' 			=> esc_html__( 'Defer non-critical scripts', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'async_scripts',
				'option_arr_id'		=> $option_arr_id,
				'popup2'		 	=> '<p>'. esc_html__('Slightly improves page-loading speed.', 'full-picture-analytics-cookie-notice') . '</p>
				<p class="fupi_warning_text">'. esc_html__('Do not defer WP FP\'s scripts using a different plugin or solution. Not all WP FP\'s scripts can be safely deferred.', 'full-picture-analytics-cookie-notice') . '</p>' 
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('Save main JS functions in a file (beta)', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'save_settings_file',
				'option_arr_id'		=> $option_arr_id,
				'popup2'			=> '<p>'. esc_html__('If you enable this option, WP FP will save some of its main JavaScript functions in a file, instead of printing them directly in the HTML.', 'full-picture-analytics-cookie-notice') . '</p>
				<p>'. esc_html__('This is done to slightly improve performance and avoid issues with some caching configurations.', 'full-picture-analytics-cookie-notice') . '</p>
				<p>'. esc_html__('The files will be placed in a folder at "wp-content/uploads/sites(for WP Multisite)/site_number(for WP Multisite)/wpfp/js/"', 'full-picture-analytics-cookie-notice') . '</p>
				<p class="fupi_warning_text">'. sprintf( esc_html__('This feature is in beta. Please report issues in %1$sthe support forum%2$s.', 'full-picture-analytics-cookie-notice'),'<a href="https://wordpress.org/support/plugin/full-picture-analytics-cookie-notice/" target="_blank">', '</a>' ). '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('Save Custom Scripts in files (beta)', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'save_cscr_file',
				'option_arr_id'		=> $option_arr_id,
				'popup2'			=> '<p>'. esc_html__('If you enable this option, WP FP will put scripts from the Custom Scripts module in files, instead of printing them directly in the HTML.', 'full-picture-analytics-cookie-notice') . '</p>
				<p>'. esc_html__('This is done to slightly improve performance and avoid issues with some caching configurations.', 'full-picture-analytics-cookie-notice') . '</p>
				<p>'. esc_html__('The files will be placed in a folder at "wp-content/uploads/sites(for WP Multisite)/site_number(for WP Multisite)/wpfp/js/"', 'full-picture-analytics-cookie-notice') . '</p>
				<p class="fupi_warning_text">'. sprintf( esc_html__('This feature is in beta. Please report issues in %1$sthe support forum%2$s.', 'full-picture-analytics-cookie-notice'),'<a href="https://wordpress.org/support/plugin/full-picture-analytics-cookie-notice/" target="_blank">', '</a>' ). '</p>',
			),
		),
	),

	// IMPORT/EXPORT SETTINGS
	array(
		'section_id' => 'fupi_main_importexport',
		'section_title' => esc_html__( 'Backups', 'full-picture-analytics-cookie-notice' ),
	),

	// OTHER SETTINGS
	
	array(
		'section_id' => 'fupi_main_other',
		'section_title' => esc_html__( 'Other', 'full-picture-analytics-cookie-notice' ),
		'fields' => $other_settings
	),
);

?>
