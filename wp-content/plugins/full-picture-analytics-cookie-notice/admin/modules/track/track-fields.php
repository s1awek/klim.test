<?php

$siteURL = get_bloginfo('url') . '/';
$track_opts = get_option('fupi_track');
$magic_keyword = ! empty ( $track_opts['magic_keyword'] ) ? $track_opts['magic_keyword'] : 'tracking';
$option_arr_id = 'fupi_track';

$sections = array(

	// OPTIONAL TRAKING FUNCTIONS

	array(
		'section_id' => 'fupi_track_opt',
		'section_title' => esc_html__( 'Extra tracking functions', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__('Enable metadata tracking', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'custom_data_ids',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_fullwidth_tr fupi_adv',
				'is_repeater'		=> true,
				'fields'			=> array(
					array(
						'label'				=> esc_html__( 'Descriptive name*', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'field_id'			=> 'name',
						'el_class'			=> 'fupi_internal_title',
						'class'				=> 'fupi_col_100',
					),
					array(
						'type'				=> 'select',
						'field_id'			=> 'meta',
						'options'			=> array(
							'post'				=> esc_html__('Post meta', 'full-picture-analytics-cookie-notice'),
							'user'				=> esc_html__('User meta', 'full-picture-analytics-cookie-notice'),
							'term'				=> esc_html__('Term meta', 'full-picture-analytics-cookie-notice'),
						),
						'class'				=> 'fupi_col_20',
					),
					array(
						'type'				=> 'text',
						'field_id'			=> 'id',
						'placeholder'		=> esc_html__('Metadata ID', 'full-picture-analytics-cookie-notice'),
						'required'			=> true,
					),
					array(
						'type'				=> 'select',
						'field_id'			=> 'type',
						'options'			=> array(
							''					=> esc_html__('Select data type', 'full-picture-analytics-cookie-notice'),
							'str'				=> esc_html__('String', 'full-picture-analytics-cookie-notice'),
							'bool'				=> esc_html__('Boolean', 'full-picture-analytics-cookie-notice'),
							'int'				=> esc_html__('Integer', 'full-picture-analytics-cookie-notice'),
							'float'				=> esc_html__('Float num', 'full-picture-analytics-cookie-notice'),
						),
						'required'			=> true,
					),
				),
				'popup'					=> '<p class="fupi_warning_text">' . esc_html__( 'This is intended for advanced users', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . esc_html__( 'Many tracking tools in WP FP let you track data associated with your posts, pages and users, like page types, categories, user roles, etc.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . esc_html__( 'Metadata tracking lets you track other data - even data added by other plugins.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . esc_html__( 'To track this data, follow these steps:', 'full-picture-analytics-cookie-notice' ) . '</p>
					<h3>' . esc_html__( 'Step 1. Find IDs of metadata you want to track', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<ol>
						<li>' . esc_html__( 'Enable the Setup Helper in the top menu.', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'Visit a page on your website.', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'Open your browser console and type "fp_usermeta", "fp_postmeta" or "fp_termmeta". You need to be logged in as an administrator while doing this.', 'full-picture-analytics-cookie-notice' ) . '</li>
						<li>' . esc_html__( 'Copy the IDs of the data that you want to track and paste it in the form on this page.', 'full-picture-analytics-cookie-notice' ) . '</li>
					</ol>
					<h3>' . esc_html__( 'Step 2. Set up metadata tracking in your tracking tools', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . esc_html__( 'To track metadata in your tracking tools, simply look for the "Track metadata" fields in their settings pages and follow instructions you find there.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('Use page labels for tracking page types','full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'page_labels',
				'class'				=> 'fupi_adv',
				'must_have'			=> 'pro',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . esc_html__('This will add a "page label" field to page "edit" screens.','full-picture-analytics-cookie-notice') . '</p>
					<p>' . sprintf( esc_html__('Use it, to label pages according to their type, e.g. landing page, contact page, etc. This information can be sent to tracking and marketing tools for analysis or be used by developers. %1$sLearn more%2$s.', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/what-is-page-labeling-and-how-to-use-it/" target="_blank">', '</a>') . '</p>
					<p>' . esc_html__('At the moment this extension works with Google Analytics, Meta Pixel and Google Tag Manager.','full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('Allow tracking IDs of content authors', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'show_author_id',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup3'			=> '<p>' . esc_html__('This enables the option to track IDs of content authors with Google Analytics, Matomo and GTM.','full-picture-analytics-cookie-notice') . '</p>
				<p style="color: red">' . esc_html__('Do not enable this option if authors of content of your site have administrator rights. This will expose their IDs making attacks easier. Don\'t help attackers hack your site.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('Track locations of broken links', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'track_404',
				'class'				=> 'fupi_adv',
				'el_class'			=> 'fupi_condition',
				'el_data_target'	=> 'fupi_redirect_404_opt',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>' . esc_html__( 'When this is enabled, every time someone tries to visit a non-existent page on your website, WP Full Picture will add extra parameters to the address of the 404 page they visit, like this: ','full-picture-analytics-cookie-notice') . '</p>
					<p style="font-family: courier; background: #efefef; padding: 5px; word-wrap: break-word;">' . get_bloginfo('url') . '/my_404?broken_link_location=facebook.com&broken_link=abot_us</p>
					<p>' . esc_html__( 'These parameters contain information which will let you find broken links on your site or other websites and fix them.','full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__( 'You can view all links containing "broken links location" information in Google Analytics, Matomo and other web analytics tools. Simply search for page views that contain the phrase "broken_link_location" in their URLs.','full-picture-analytics-cookie-notice') . '</p>',
			),
				array(
					'type' 				=> 'text',
					'label' 			=> esc_html__( 'Use custom 404 page:', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'redirect_404',
					'class'				=> 'fupi_sub fupi_disabled fupi_redirect_404_opt fupi_adv',
					'placeholder'		=> 'URL of a custom 404 page',
					'must_have'			=> 'pro',
					'option_arr_id'		=> $option_arr_id,
					'label_for' 		=> $option_arr_id . '[redirect_404]',
					'popup2'			=> '<p>' . esc_html__('Remember to noindex your custom 404 page from Google search results with your SEO plugin.','full-picture-analytics-cookie-notice') . '</li>
						</ol>',
				),
			array(
				'type'	 			=> 'taxonomies multi checkbox',
				'label' 			=> esc_html__( 'Allow tracking terms of these taxonomies:', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'tracked_taxonomies',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[tracked_taxonomies]',
				'popup2'	 		=> '<p>' . esc_html__('By default, WP Full Picture lets you track categories, product categories (in WooCommerce) and tags that a page/post/product belongs to. Use this function, to track additional taxonomy terms.', 'full-picture-analytics-cookie-notice') . '</p>
				<p>' . esc_html__('This will apply to blog posts, pages and single custom post types (even WooCommerce products*).', 'full-picture-analytics-cookie-notice') . '</p>
				<p class="fupi_warning_text">* ' . esc_html__('Product categories that WooCommerce products belong to are tracked automatically but they are sent in store-related events, like purchases. Use this function to also track them in other events, like pageviews.', 'full-picture-analytics-cookie-notice') . '</p>'
			),
		)
	),

	// ACCURACY TWEAKS

	array(
		'section_id' => 'fupi_track_ref',
		'section_title' => esc_html__( 'Tracking improvements', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('Wait for tracking to finish before redirecting to another page', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'link_click_delay',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup2' 			=> '<p>' . esc_html__('Using this option is recommended for websites that load very quickly and use multiple tracking tools that track link clicks.', 'full-picture-analytics-cookie-notice') . '</p>
				<p>' . esc_html__('When a visitor clicks a link, WP FP will pause the page redirect until all the tracking tools finish tracking the click.', 'full-picture-analytics-cookie-notice') . '</p>
					<p class="fupi_warning_text">' . esc_html__('Attention! Test before using in production. On rare occasions enabling this setting can cause issues with clicks on dynamic page elements like galleries or sliders.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('Use DOM Listener to track when dynamic page elements show on screen', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'use_mutation_observer',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'popup2' 			=> '<p>' . esc_html__('This function extends the "element visibility tracking" function, available in some tracking tools modules.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>' . esc_html__('Use it to track when popups and other content loaded dynamically (after the page has loaded) shows on screen.', 'full-picture-analytics-cookie-notice') . '</p>
					<p class="fupi_warning_text">' . esc_html__('Attention! If your website loads a lot of content dynamically, using this option can make your website feel less responsive while this content is being loaded.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
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
				<p>' . sprintf( esc_html__('Traffic from Line app will be marked as coming from %1$s.' , 'full-picture-analytics-cookie-notice'), 'https://line-android-app.jp/' ) . '</p>',
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Make tracking tools correctly recognize traffic from other Android applications', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_non_http',
				'class'				=> 'fupi_simple_r3 fupi_r3_fullwidth fupi_adv',
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
				'popup2' 				=> '<p>' . esc_html__( 'To set it up you need to use Google Analytics with at least 1 month of data or any other tool that can track real referral addresses.', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p><a href="https://wpfullpicture.com/support/documentation/how-to-get-better-traffic-sources-information/">' . esc_html__( 'How to set it up', 'full-picture-analytics-cookie-notice' ) . '</a></p>',
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Prevent attributing conversions to payment gateways', 'full-picture-analytics-cookie-notice' ),
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
						'placeholder'		=> sprintf( esc_html__('e.g. Checkout page URL (has to start with %1$s)','full-picture-analytics-cookie-notice'), 'https://' ),
						'field_id'			=> 'replace',
						'required'			=> true,
					),
				),
				'popup'				=> '<p>' . esc_html__('This will make sure that your conversions are not attributed to payment gateways (as last source of traffic before visiting order confirmation page).' , 'full-picture-analytics-cookie-notice') . '</p>',
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
				'popup'				=> '<p>' . esc_html__( 'Facebook, Instagram and Pinterest use multiple URLs that redirect traffic to your site, e.g. l.facebook.com, lm.facebook.com, etc. Enable this function to combine them and analyse traffic sources more easily.', 'full-picture-analytics-cookie-notice') . ' <a target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-get-better-traffic-sources-information/">' . esc_html__('Learn more', 'full-picture-analytics-cookie-notice') . '</a>.</p>',
			),
		),
	),

	// DEFAULT SETTINGS

	array(
		'section_id' => 'fupi_track_default',
		'section_title' => esc_html__( 'Default settings', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'number',
				'label' 			=> esc_html__('Do not track form submissions if they happen within:', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'formsubm_trackdelay',
				'class'				=> 'fupi_adv',
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
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[notrack_dblclck]',
				'default'			=> '300',
				'after field' 		=> esc_html__('miliseconds', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type'	 			=> 'number',
				'label' 			=> esc_html__( 'Prevent tracking page scroll depth if visitor scrolled less than:', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'track_scroll_min',
				'class'				=> 'fupi_adv',
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
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[track_scroll_time]',
				'default'			=> '5',
				'after field'		=> esc_html__( 'seconds', 'full-picture-analytics-cookie-notice' ),
				'popup'				=> '<p>' . esc_html__( 'This setting prevents tracking "exploratory" scrolls - quick scrolls performed by visitors who want to see the contents of the page before devoting more time to read it.','full-picture-analytics-cookie-notice') . '</p><p>' . esc_html__('After the set time has passed page\'s scroll depth will be tracked normally.','full-picture-analytics-cookie-notice') . '</p><p>' . esc_html__('Default is 5 seconds.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Set when elements are considered to be visible on screen', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'intersections',
				'class'				=> 'fupi_adv',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[intersections]',
				'placeholder'		=> 'Top Right Bottom Left',
				'under field'		=> esc_html__( 'Leave empty to use default values', 'full-picture-analytics-cookie-notice' ) . ' -200px 0px -200px 0px',
				'popup'				=> '<p>' . esc_html__( 'By default this is set to "-200px 0px -200px 0px", which means, that elements will be considered as visible, when their top or bottom edge is 200 pixels inside the visible area of the screen.', 'full-picture-analytics-cookie-notice' ) . '</p>',	
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__('Reset engagement time counter after clicks in anchors', 'full-picture-analytics-cookie-notice'),
				'must_have'			=> 'pro',
				'class'				=> 'fupi_adv',
				'field_id' 			=> 'reset_timer_on_anchor',
				'option_arr_id'		=> $option_arr_id,
				'popup'				=> '<p>'. esc_html__('This function modifies the setting "Track how long the user was actively engaged with the content" in Google Analytics and Meta Pixel modules.', 'full-picture-analytics-cookie-notice') . '</p>
					<p>'. esc_html__('Enable this option if your website only consists of a single page and you use anchors to navigate between its sections. Engagement time will be sent to compatible tracking tools (Google Analytics, Meta Pixel) when users click navigation anchors. If this remains disabled, engagement time will be calculated for the whole page.', 'full-picture-analytics-cookie-notice') . '</p>
					<p><a href="https://wpfullpicture.com/support/documentation/how-engagement-time-is-calculated/">'. esc_html__('Learn more about engagement time', 'full-picture-analytics-cookie-notice') . '</a></p>',
			),
		),
	),
);

?>
