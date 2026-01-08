<?php

$option_arr_id = 'fupi_main';

$siteURL = get_bloginfo('url') . '/';
$main_opts = get_option('fupi_main');
$magic_keyword = ! empty ( $main_opts['magic_keyword'] ) ? $main_opts['magic_keyword'] : 'tracking';

// MAIN SETTINGS

$other_settings = array(
	array(
		'type' 				=> 'email',
		'label' 			=> esc_html__( 'Send email notification when the plugin is deactivated', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'deactiv_email',
		'option_arr_id'		=> $option_arr_id,
		'placeholder'		=> esc_html__( 'e.g. john@example.com, peter@example.com, etc.', 'full-picture-analytics-cookie-notice' ),
		'under field' 		=> esc_html__('Enter a coma separated list of email addresses where you want to send the email.','full-picture-analytics-cookie-notice'),
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
);

if ( current_user_can('manage_options') ){
	array_push( $other_settings, 
		array(
			'type' 				=> 'user_search',
			'field_id' 			=> 'extra_users_2',
			'label' 			=> esc_html__( 'Let non-admin users view and save WP Full Picture\'s settings', 'full-picture-analytics-cookie-notice' ),
			'must_have'			=> 'pro',
			'option_arr_id' 	=> $option_arr_id,
			'popup2'			=> '<p class="fupi_warning_text">' . esc_html__( 'As a security measure, the edit rights cannot be given to users without the right to at least edit posts.', 'full-picture-analytics-cookie-notice' ) . '</p>',
		),
	);
}

array_push( $other_settings, 
	array(
		'type'	 			=> 'select',
		'label' 			=> esc_html__( 'Bot detection list', 'full-picture-analytics-cookie-notice' ),
		'field_id' 			=> 'bot_list',
		'class'				=> 'fupi_adv',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[bot_list]',
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
		'class'				=> 'fupi_adv',
		'el_class'			=> 'fupi_condition',
		'el_data_target'	=> 'fupi_restpi_opts',
		'option_arr_id'		=> $option_arr_id,
		'label_for' 		=> $option_arr_id . '[server_method]',
		'options'				=> array(
			'rest'			=> esc_html__( 'Rest API (default)', 'full-picture-analytics-cookie-notice' ),
			'ajax'			=> esc_html__( 'AJAX', 'full-picture-analytics-cookie-notice' ),
		),
		'popup'				=> '<p>' . esc_html__( 'The method you choose here will be used to send data from the visitors\'s browser to your server. This is used for server-side tracking and for sending visitor consents.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . esc_html__( 'Rest API is generally faster and less resource-intensive.', 'full-picture-analytics-cookie-notice' ) . '</p>',
	)
);

// SECTIONS

$sections = array(

	// TRACKING EXCLUSIONS
	
	array(
		'section_id' => 'fupi_main_no_track',
		'section_title' => esc_html__( 'Tracking exclusions', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
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
			array(
				'type'	 			=> 'roles multi checkbox',
				'label' 			=> esc_html__( 'Do not track logged-in users with these roles:', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'disable_for_roles',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[disable_for_roles]',
				'default'			=> 'administrator',
				'under field'		=> esc_html__( 'Site administrators are never tracked. To make them temporarily trackable, please enable the Setup Helper in the top menu and enable testing in the front-end of your site.', 'full-picture-analytics-cookie-notice' ),
			),
		)
	),

	// META TAGS

	array(
		'section_id' => 'fupi_main_meta',
		'section_title' => esc_html__( 'Meta tags', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__('Meta tags','full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'meta_tags',
				'class'				=> 'fupi_simple_r3',
				'is_repeater'		=> true,
				'option_arr_id'		=> $option_arr_id,
				'under field'		=> '<p>' . esc_html__('One tag per line. For security reasons, meta tags can only have parameters: "name", "content", "property", "charset" and "http-equiv".', 'full-picture-analytics-cookie-notice' ) . '</p>',
				'fields'			=> array(
					array(
						'type'				=> 'text',
						'field_id'			=> 'name',
						'class'				=> 'fupi_col_20_grow',
						'placeholder'		=> esc_html__('Name (for your information)', 'full-picture-analytics-cookie-notice'),
						'required'			=> true,
					),
					array(
						'type'				=> 'text',
						'field_id'			=> 'tag',
						'placeholder'		=> esc_html__('Tag <meta....>', 'full-picture-analytics-cookie-notice'),
						'required'			=> true,
					),
				),
			),
		),
	),

	// GEOLOCATION
	
	array(
		'section_id' => 'fupi_main_geo',
		'section_title' => esc_html__( 'Geolocation', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'select',
				'label' 			=> esc_html__( 'Geolocation provider', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'geo',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_adv',
				'must_have'			=> 'pro',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_geo_cond',
				'options' 			=> array(
					''					=> esc_html__( 'Disabled', 'full-picture-analytics-cookie-notice'),
					'cf_non_user'		=> esc_html__( 'Cloudflare (for not-registered users)', 'full-picture-analytics-cookie-notice'),
					'cf_default'		=> esc_html__( 'Cloudflare (for registered users)', 'full-picture-analytics-cookie-notice'),
					'cf_worker'			=> esc_html__( 'Cloudflare (Worker method)', 'full-picture-analytics-cookie-notice'),
					'ip_api'			=> 'ipapi',
					'ipdata'			=> 'ipdata',
					'db_ip'				=> 'DB-IP',
					'cloudways'			=> 'Cloudways Geo IP',
				),
				'default'			=> 'none',
				'under field'		=> '<p>' . esc_html__( 'All providers on this list offer free services but some of them require registration or extra setup.','full-picture-analytics-cookie-notice') . '</p>
				<ul>
					<li><a href="https://www.cloudflare.com/en-gb/">Cloudflare</a> ' . esc_html__( '(default) - Geolocation of visitor\'s country, free, registration not required', 'full-picture-analytics-cookie-notice') . '</li>
					<li><a href="https://www.cloudflare.com/en-gb/">Cloudflare</a> '. esc_html__( '(for regisered users) - Geolocation of visitor\'s country, free, registration required', 'full-picture-analytics-cookie-notice') . '</li>
					<li><a href="https://www.cloudflare.com/en-gb/">Cloudflare</a> '. esc_html__( '(Worker method) - Geolocation of visitor\'s country, free, registration and extra setup required', 'full-picture-analytics-cookie-notice') . '</li>
					<li><a href="https://ipapi.co/">ipapi</a> - '. esc_html__( 'Geolocation of visitor\'s country & region, free and paid, free is limited to 1000 checks/day, registration not required', 'full-picture-analytics-cookie-notice') . '</li>
					<li><a href="http://ipdata.co/">ipdata</a> - '. esc_html__( 'Geolocation of visitor\'s country & region, free and paid, registration is always required, free is limited to 1500 checks/day and non-commercial use', 'full-picture-analytics-cookie-notice') . '</li>
					<li><a href="https://db-ip.com/">DB_IP</a> - '. esc_html__( 'Geolocation of visitor\'s country, does not require registration (paid plans are not yet supported)', 'full-picture-analytics-cookie-notice') . '</li>
					<li><a href="https://www.cloudways.com/en/">Cloudways</a> - '. esc_html__( 'Geolocation of visitor\'s country. Only for Cloudways customers. Extra setup required. Attention! Some caching configurations may cache geo lookup result!', 'full-picture-analytics-cookie-notice') . '</li>
				</ul>',
			),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'Cloudflare Worker URL', 'full-picture-analytics-cookie-notice' ),
					'class'				=> 'fupi_sub fupi_adv fupi_geo_cond fupi_cond_val_cf_worker fupi_disabled',
					'must_have'			=> 'pro',
					'field_id' 			=> 'cf_worker_url',
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[cf_worker_url]',
					'placeholder'		=> '',
					'under field' 		=> sprintf(esc_html__('Enter worker script URL. %1s Learn how to find it%2s.', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/geolocation/?utm_source=fp_admin&utm_medium=fp_link" target="_blank">', '</a>'),
				),
				array(
					'type'	 			=> 'text',
					'label' 			=> esc_html__( 'ipdata API key', 'full-picture-analytics-cookie-notice' ),
					'class'				=> 'fupi_sub fupi_geo_cond fupi_cond_val_ipdata fupi_disabled fupi_adv',
					'must_have'			=> 'pro',
					'field_id' 			=> 'ipdata_api_key',
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[ipdata_api_key]',
				),
			array(
				'type'	 			=> 'number',
				'label' 			=> esc_html__( 'Remember visitor\'s location for', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'remember_geo',
				'class'				=> 'fupi_adv',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[show_to_countries]',
				'default'			=> '30',
				'after field'		=> 'days',
				'popup' 			=> '<p>' . esc_html__( 'Default is 30 days. Set to a lower number if your visitors often browse from different countries. If set to 0, location will be checked at the begining of each session. If a location wasn\'t found it will be checked again at the begining of the next session and remembered for the specified number of days.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
		)
	),

	// IMPORT/EXPORT SETTINGS
	array(
		'section_id' => 'fupi_main_importexport',
		'section_title' => esc_html__( 'Backups', 'full-picture-analytics-cookie-notice' ),
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
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup2'		 	=> '<p class="fupi_warning_text">'. esc_html__('Do not defer WP FP\'s scripts using a different plugin or solution. Not all WP FP\'s scripts can be safely deferred.', 'full-picture-analytics-cookie-notice') . '</p>' 
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('Save main JS functions in a file', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'save_settings_file',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>'. esc_html__('If you enable this option, WP FP will save some of its main JavaScript functions in a file, instead of printing them directly in the HTML.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('Save Custom Scripts in a file (beta)', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'save_cscr_file',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup3'			=> '<p>'. esc_html__('If you enable this option, WP FP will save scripts from the Custom Scripts module in a file, instead of printing them directly in the HTML.', 'full-picture-analytics-cookie-notice') . '</p>
				<p class="fupi_warning_text">'. sprintf( esc_html__('Attention. All scripts in a file will be wrapped in a function. This means that all variables that should be accessible via "window" object, will not be accessible this way unless you change them from e.g. "var variable_name" to "window.variable_name".', 'full-picture-analytics-cookie-notice'),'<a href="https://wordpress.org/support/plugin/full-picture-analytics-cookie-notice/" target="_blank">', '</a>' ). '</p>
				<p>'. sprintf( esc_html__('This feature is in beta. Please report issues in %1$sthe support forum%2$s.', 'full-picture-analytics-cookie-notice'),'<a href="https://wordpress.org/support/plugin/full-picture-analytics-cookie-notice/" target="_blank">', '</a>' ). '</p>',
			),
			array(
				'type' 				=> 'toggle',
				'label' 			=> esc_html__( 'Enable WP Rocket compatibility settings', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'wprocket_compat',
				'option_arr_id'		=> $option_arr_id,
				'popup2'		 	=> '<p>'. esc_html__('WP Full Picture only loads scripts that are used by the enabled modules. These scripts are loaded in a specific order and place. Unfortunately, some of WP Rocket\'s functions change it, which breaks tracking. Enable this option to prevent it.', 'full-picture-analytics-cookie-notice') . '</p>
				<p>'. sprintf( esc_html__('If, for any reasons, you experience issues, please let us know and %1$sset up exclusions manually%2$s.', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/how-to-fix-issues-with-caching/" target="_blank">', '</a>') . '</p>',
			),
		),
	),

	// OTHER SETTINGS
	array(
		'section_id' => 'fupi_main_other',
		'section_title' => esc_html__( 'Other settings', 'full-picture-analytics-cookie-notice' ),
		'fields' => $other_settings
	),

	// FUPI SHORTCODE

	array(
		'section_id' => 'fupi_main_shortcode',
		'section_title' => esc_html__( '[fp_info] shortcode', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__('Add more tracking tools to the list', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'extra_tools',
				'class'				=> 'fupi_simple_r3',
				'option_arr_id'		=> $option_arr_id,
				'is_repeater'		=> true,
				'fields'			=> array(
					array(
						'type'				=> 'text',
						'field_id'			=> 'name',
						'placeholder'		=> 'Tool (required)',
						'required'			=> true,
					),
					array(
						'type'				=> 'url',
						'field_id'			=> 'url',
						'placeholder'		=> 'Privacy policy link (optional)',
					),
				),
			),
		)
	),
);

?>
