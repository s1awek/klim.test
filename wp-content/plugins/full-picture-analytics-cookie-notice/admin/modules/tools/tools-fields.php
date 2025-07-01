<?php

$ga42_cond_info_text = '';
$option_arr_id = 'fupi_tools';
// TOOLS
$tools_fields = array(
    // Crazy Egg
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/crazyegg_fav.png" aria-hidden="true">Crazy Egg</div>',
        'field_id'      => 'cegg',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'tests heat surveys woo',
        'el_data_name'  => 'Crazy Egg',
        'popup'         => '<p>' . esc_html__( 'Crazy Egg let\'s you:', 'full-picture-analytics-cookie-notice' ) . '</p>
		<ol>
			<li>' . esc_html__( 'record how your users interact with your website,', 'full-picture-analytics-cookie-notice' ) . '</li>
			<li>' . esc_html__( 'see where they click the most and the least,', 'full-picture-analytics-cookie-notice' ) . '</li>
			<li>' . esc_html__( 'and test multiple versions of your content (to see which performs better).', 'full-picture-analytics-cookie-notice' ) . '</li>
		</ol>
		<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/crazy-egg/" class="button-primary">', '</a>' ) . '</p>
		<p>' . sprintf( esc_html__( '%1$sVisit Crazy Egg website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://www.crazyegg.com" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // Google Ads
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/gads_fav.png" aria-hidden="true">Google Ads</div>',
        'field_id'      => 'gads',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'ads woo',
        'el_data_name'  => 'Google Ads',
        'popup'         => '<p>' . esc_html__( 'Google Ads integration allows you to track conversions from your Google advertising campaigns.', 'full-picture-analytics-cookie-notice' ) . '</p>
		<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/google-ads/" class="button-primary">', '</a>' ) . '</p>
		<p>' . sprintf( esc_html__( '%1$sVisit Google Ads website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://ads.google.com/intl/en/home/" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // GA 4
    array(
        'type'           => 'toggle',
        'label'          => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/analytics_fav.png" aria-hidden="true">Google Analytics</div>',
        'field_id'       => 'ga41',
        'option_arr_id'  => $option_arr_id,
        'tags'           => 'ads stats woo',
        'el_class'       => 'fupi_condition',
        'el_data_target' => 'fupi_ga4_subs',
        'el_data_name'   => 'Google Analytics',
        'popup3'         => '<p>' . esc_html__( 'Google Analytics is the most popular free website analytics software.', 'full-picture-analytics-cookie-notice' ) . '</p>
 			<p style="color: red;">' . sprintf( esc_html__( 'Google Analytics is illegal in Austria, the Netherlands, France and %1$ssome other countries%2$s. We advise you to enable the Geolocation module and exclude GA from loading in those countries.', 'full-picture-analytics-cookie-notice' ), '<a href="https://www.simpleanalytics.com/google-analytics-is-illegal-in-these-countries">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/google-analytics-integration-for-wordpress/" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit Google Analytics website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://marketingplatform.google.com/about/analytics/" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // GA 4 #2
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/analytics_fav.png" aria-hidden="true">Google Analytics #2</div>',
        'field_id'      => 'ga42',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'ads stats woo',
        'class'         => 'fupi_sub fupi_do_not_hide fupi_ga4_subs',
        'must_have'     => 'pro_round',
        'under field'   => $ga42_cond_info_text,
        'el_data_name'  => 'Google Analytics #2',
        'popup'         => '<p>' . esc_html__( 'This module adds a 2nd integration of Google Analytics.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/google-analytics-integration-for-wordpress/" class="button-primary">', '</a>' ) . '</p>',
    ),
    // Hotjar
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/hotjar_fav.png" aria-hidden="true">Hotjar</div>',
        'field_id'      => 'hotj',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'heat surveys nocook woo',
        'el_data_name'  => 'Hotjar',
        'popup'         => '<p>' . esc_html__( 'Hotjar is a business-oriented tool that lets you:', 'full-picture-analytics-cookie-notice' ) . '</p>
			<ol>
				<li>' . esc_html__( 'record how your users interact with your website,', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'see where they click the most and the least,', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'and collect user feedback.', 'full-picture-analytics-cookie-notice' ) . '</li>
			</ol>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/hotjar/" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit Hotjar website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://www.hotjar.com" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // Inspectlet
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/inspectlet_fav.png" aria-hidden="true">Inspectlet</div>',
        'field_id'      => 'insp',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'heat surveys tests woo',
        'el_data_name'  => 'Inspectlet',
        'popup'         => '<p>' . esc_html__( 'Inspectlet allows you to:', 'full-picture-analytics-cookie-notice' ) . '</p>
			<ol>
				<li>' . esc_html__( 'record how your users interact with your website,', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'see where they click the most and the least,', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'collect user feedback,', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'and test multiple versions of your content to see which performs better.', 'full-picture-analytics-cookie-notice' ) . '</li>
			</ol>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit Inspectlet website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://www.inspectlet.com" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // LinkedIn
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/linkedin-fav.png" aria-hidden="true">LinkedIn Insight</div>',
        'field_id'      => 'linkd',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'ads woo',
        'el_data_name'  => 'LinkedIn',
        'popup'         => '<p>' . esc_html__( 'LinkedIn integration allows you to:', 'full-picture-analytics-cookie-notice' ) . '</p>
			<ol>
				<li>' . esc_html__( 'build lists of visitors who may be interested in your offer (to show them ads on Linked In)', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'and measure the conversions of those ads.', 'full-picture-analytics-cookie-notice' ) . '</li>
			</ol>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/inspectlet/" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit LinkedIn Insight website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://business.linkedin.com/marketing-solutions/cx/21/10/insight-tag" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // Matomo
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/matomo_fav.png" aria-hidden="true">Matomo</div>',
        'field_id'      => 'mato',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'stats nocook proxy woo',
        'el_data_name'  => 'Matomo (basic)',
        'popup'         => '<p>' . esc_html__( 'This module lets you install Matomo on your website. It supports Matomo on-premise and cloud versions.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . esc_html__( 'Matomo is a powerful Google Analytics alternative. It is similar to Google Analytics UA but it is slightly easier to use. Matomo can import GA UA\'s data and it can work as its replacement.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/matomo/" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit Matomo website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://matomo.org" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // Meta Pixel
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/meta_fav.png" aria-hidden="true">Meta Pixel</div>',
        'field_id'      => 'fbp1',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'ads woo server',
        'el_data_name'  => 'Meta Pixel (Facebook / Instagram)',
        'popup'         => '<p>' . esc_html__( 'Meta Pixel lets you:', 'full-picture-analytics-cookie-notice' ) . '</p>
			<ol>
				<li>' . esc_html__( 'build lists of visitors who may be interested in your offer (for Facebook and Instagram ads)', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'and measure the conversions from Facebook and Instagram advertising campaigns.', 'full-picture-analytics-cookie-notice' ) . '</li>
			</ol>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/meta-pixel/" class="button-primary">', '</a>' ) . '</p>',
    ),
    // Microsoft Advertising
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/microsoft_fav.png" aria-hidden="true">Microsoft Advertising</div>',
        'field_id'      => 'mads',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'ads woo',
        'el_data_name'  => 'MS Ads',
        'popup'         => '<p>' . esc_html__( 'Microsoft Advertising integration allows you to:', 'full-picture-analytics-cookie-notice' ) . '</p>
			<ol>
				<li>' . esc_html__( 'build lists of visitors who may be interested in your offer - to show them ads on Bing, Yahoo and other search engines', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'and measure the conversions of those ads.', 'full-picture-analytics-cookie-notice' ) . '</li>
			</ol>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/microsoft-advertising/" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit Microsoft Advertising website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://ads.microsoft.com/" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // Microsoft Clarity
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/microsoft_fav.png" aria-hidden="true">Microsoft Clarity</div>',
        'field_id'      => 'clar',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'stats heat nocook woo',
        'el_data_name'  => 'MS Clarity',
        'popup'         => '<p>' . esc_html__( 'Microsoft Clarity lets you:', 'full-picture-analytics-cookie-notice' ) . '</p>
			<ol>
				<li>' . esc_html__( 'collect basic traffic statistics,', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'record how your users interact with your website (see where they click the most and the least).', 'full-picture-analytics-cookie-notice' ) . '</li>
			</ol>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/microsoft-clarity/" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit Microsoft Clarity website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://clarity.microsoft.com" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // Pinterest
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/pinterest_fav.png" aria-hidden="true">Pinterest Tag</div>',
        'field_id'      => 'pin',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'ads woo',
        'el_data_name'  => 'Pinterest',
        'popup'         => '<p>' . esc_html__( 'Pinterest Tag integration allows you to:', 'full-picture-analytics-cookie-notice' ) . '</p>
			<ol>
				<li>' . esc_html__( 'build lists of visitors who may be interested in your offer (to show them ads on Pinterest)', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'and measure the conversions of those ads.', 'full-picture-analytics-cookie-notice' ) . '</li>
			</ol>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/pinterest-ads/" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit Pinterest Analytics website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://analytics.pinterest.com" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // Plausible
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/plausible_fav.png" aria-hidden="true">Plausible</div>',
        'field_id'      => 'pla',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'nocook woo admin_stats proxy',
        'el_data_name'  => 'Plausible',
        'popup'         => '<p>' . esc_html__( 'Plausible is a basic tracking tool that:', 'full-picture-analytics-cookie-notice' ) . '</p>
			<ol>
				<li>' . esc_html__( 'can import data from Google Analytics UA (Universal Analytics),', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'can be configured to bypass adblockers,', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'does not use cookies (and so it does not require a consent banner),', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'lets you measure how much traffic is not tracked by other tools,', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'lets you view your website statistics directly in your Wordpress admin panel.', 'full-picture-analytics-cookie-notice' ) . '</li>
			</ol>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/plausible-analytics/" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit Plausible Analytics website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://plausible.io" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // TikTok Pixel
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/tiktok_fav.png" aria-hidden="true">TikTok Pixel</div>',
        'field_id'      => 'tik',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'ads woo',
        'el_data_name'  => 'TikTok Pixel',
        'popup'         => '<p>' . esc_html__( 'TikTok Pixel allows you to:', 'full-picture-analytics-cookie-notice' ) . '</p>
			<ol>
				<li>' . esc_html__( 'build lists of visitors who may be interested in your offer - to show them ads on TikTok', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'and measure the conversions of those ads.', 'full-picture-analytics-cookie-notice' ) . '</li>
			</ol>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/tiktok-pixel/" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit TikTok for Business website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://getstarted.tiktok.com/eu-go-tiktok?lang=en" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // X / Twitter
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/x_ico.png" aria-hidden="true">X Ads (Twitter Ads)</div>',
        'field_id'      => 'twit',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'ads woo',
        'el_data_name'  => 'X Ads',
        'popup'         => '<p>' . esc_html__( 'X Ads (previously Twitter Ads) integration allows you to:', 'full-picture-analytics-cookie-notice' ) . '</p>
			<ol>
				<li>' . esc_html__( 'build lists of visitors who may be interested in your offer (to show them ads on X)', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'and measure the conversions of those ads.', 'full-picture-analytics-cookie-notice' ) . '</li>
			</ol>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/x-ads-twitter-ads/" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit X for Business website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://business.twitter.com" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // PostHog
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/posthog_fav.png" aria-hidden="true">PostHog</div>',
        'field_id'      => 'posthog',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'stats heat surveys',
        'class'         => 'fupi_basic',
        'el_data_name'  => 'Posthog',
        'popup'         => '<p>' . esc_html__( 'PostHog is a product analytics tool. It is best suited to analyze web applications and platforms. PostHog lets you collect traffic statistics, automatically capture events, record user sessions, do A/B tests and more.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/posthog/" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit PostHog website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://posthog.com" class="button-secondary">', '</a>' ) . '</p>',
    ),
    // Simple Analytics
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/simple_analytics-fav.png" aria-hidden="true">Simple Analytics</div>',
        'field_id'      => 'simpl',
        'option_arr_id' => $option_arr_id,
        'tags'          => 'stats nocook server proxy',
        'el_data_name'  => 'Simple Analytics',
        'popup'         => '<p>' . esc_html__( 'Simple Analytics is a basic tracking tool that:', 'full-picture-analytics-cookie-notice' ) . '</p>
			<ol>
				<li>' . esc_html__( 'can import data from Google Analytics UA (Universal Analytics),', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'can be configured to bypass adblockers,', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'does not use cookies (and so it does not require a consent banner),', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'lets you measure how much traffic is not tracked by other tools,', 'full-picture-analytics-cookie-notice' ) . '</li>
			</ol>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/simple-analytics/" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit Simple Analytics website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://www.simpleanalytics.com" class="button-secondary">', '</a>' ) . '</p>',
    ),
);
$extra_tools_fields = apply_filters( 'fupi_add_integr_module_switch', [] );
// ! ADDON
$tools_fields = array_merge( $tools_fields, $extra_tools_fields );
// array_push( $tools_fields, array(
// 	'type'	 			=> 'button',
// 	'label' 			=> '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/addons_ico.png" aria-hidden="true">' . esc_html__( 'Add more', 'full-picture-analytics-cookie-notice' ) . '</div>',
// 	'button_text' 		=> esc_html__( 'View add-ons', 'full-picture-analytics-cookie-notice' ),
// 	'href'				=> 'https://wpfullpicture.com/addons/',
// 	'target'			=> '_blank',
// 	'class'				=> 'fp_button_wrap',
// 	'el_class'			=> 'button button-secondary',
// 	'field_id' 			=> 'customize_notice_btn',
// 	'option_arr_id'		=> $option_arr_id,
// ) );
$tagmanagers_fields = array(
    // Custom scripts
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/custom_scr_ico.png" aria-hidden="true">' . esc_html__( 'Custom scripts', 'full-picture-analytics-cookie-notice' ) . '</div>',
        'field_id'      => 'cscr',
        'option_arr_id' => $option_arr_id,
        'el_data_name'  => 'Custom scripts',
        'popup'         => '<p>' . esc_html__( 'Custom scripts module is an easy way to install other tracking tools to your website. Out of the box it works with the consent banner module, geolocation and other modules and functions.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/custom-scripts/" class="button-primary">', '</a>' ) . '</p>',
    ),
    // GTM
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/gtm_fav.png" aria-hidden="true">Google Tag Manager</div>',
        'field_id'      => 'gtm',
        'option_arr_id' => $option_arr_id,
        'el_data_name'  => 'GTM',
        'popup2'        => '<p>' . esc_html__( 'GTM is an advanced tool for installing tracking tools and adding extra features to the ones that are already installed.', 'full-picture-analytics-cookie-notice' ) . '<p>
			<p style="color: #e47d00">' . esc_html__( 'Unlike tools installed with the "Custom scripts" module, tools installed with GTM require extra effort to work with the Consent Banner and the Geolocation modules.', 'full-picture-analytics-cookie-notice' ) . '<p>
			<p>' . esc_html__( 'If you only want to install something, then using the "Custom scripts" module will be much easier.', 'full-picture-analytics-cookie-notice' ) . '<p>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/google-tag-manager/" class="button-primary">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sVisit Google Tag Manager website%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://marketingplatform.google.com/about/tag-manager/" class="button-secondary">', '</a>' ) . '</p>',
    ),
);
$tagman_tools_fields = apply_filters( 'fupi_add_tagmanager_module_switch', [] );
// ! ADDON
$tagmanagers_fields = array_merge( $tagmanagers_fields, $tagman_tools_fields );
// array_push( $tools_fields, array(
// 	'type'	 			=> 'button',
// 	'label' 			=> '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/addons_ico.png" aria-hidden="true">' . esc_html__( 'Add more', 'full-picture-analytics-cookie-notice' ) . '</div>',
// 	'button_text' 		=> esc_html__( 'View add-ons', 'full-picture-analytics-cookie-notice' ),
// 	'href'				=> 'https://wpfullpicture.com/addons/',
// 	'target'			=> '_blank',
// 	'class'				=> 'fp_button_wrap',
// 	'el_class'			=> 'button button-secondary',
// 	'field_id' 			=> 'customize_notice_btn',
// 	'option_arr_id'		=> $option_arr_id,
// ) );
$privacy_fields = array(
    array(
        'type'           => 'toggle',
        'label'          => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/cookie_icon.png" aria-hidden="true">' . esc_html__( 'Consent Banner', 'full-picture-analytics-cookie-notice' ) . '</div>',
        'field_id'       => 'cook',
        'el_class'       => 'fupi_condition',
        'el_data_target' => 'fupi_cook_cond',
        'option_arr_id'  => $option_arr_id,
        'popup'          => '<p>' . esc_html__( 'This module helps you track your visitors according to privacy regulations and save their tracking choices in a cloud database.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . esc_html__( 'Read this article if you do not know if you need it', 'full-picture-analytics-cookie-notice' ) . ' <a href="https://wpfullpicture.com/support/documentation/countries-that-require-opt-in-or-opt-out-to-cookies/" target="_blank" class="button-secondary">' . esc_html__( 'Read now', 'full-picture-analytics-cookie-notice' ) . '</a></p>',
    ),
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/iframeblock_ico.png" aria-hidden="true">' . esc_html__( 'Iframes Manager', 'full-picture-analytics-cookie-notice' ) . '</div>',
        'field_id'      => 'iframeblock',
        'option_arr_id' => $option_arr_id,
        'class'         => 'fupi_sub fupi_do_not_hide fupi_cook_cond fupi_disabled',
        'under field'   => esc_html__( 'Enable consent manager', 'full-picture-analytics-cookie-notice' ),
        'popup'         => '<p>' . esc_html__( 'This module prevents loading content from other websites (videos, maps, forms, etc.) before visitors agree to their privacy policies. You should use it if both of these are true:', 'full-picture-analytics-cookie-notice' ) . '</p>
				<ol>
					<li>' . esc_html__( 'the content you want to embed on your website tracks your visitors personal data and/or identification data,', 'full-picture-analytics-cookie-notice' ) . '</li>
					<li>' . esc_html__( 'and your visitors come from countries that require consent to tracking.', 'full-picture-analytics-cookie-notice' ) . '</li>
				</ol>
				<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/iframes-manager/" class="button-primary">', '</a>' ) . '</p>',
    ),
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/blockscr_ico.png" aria-hidden="true">' . esc_html__( 'Tracking Tools Manager', 'full-picture-analytics-cookie-notice' ) . '</div>',
        'field_id'      => 'blockscr',
        'option_arr_id' => $option_arr_id,
        'popup'         => '<p>' . esc_html__( 'With this module WP Full Picture can control loading of tracking tools installed with other plugins or added directly to the HTML of your site. Controlled tools:', 'full-picture-analytics-cookie-notice' ) . '</p>
		<ol>
			<li>' . esc_html__( 'load according to the settings in the Consent Banner module', 'full-picture-analytics-cookie-notice' ) . '</li>
			<li>' . esc_html__( 'load only in specific countries (Geolocation module must be enabled)', 'full-picture-analytics-cookie-notice' ) . '</li>
			<li>' . esc_html__( 'do not track users specified in the General Settings page', 'full-picture-analytics-cookie-notice' ) . ' <button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_track_excl_popup">' . esc_html__( "Learn more", 'full-picture-analytics-cookie-notice' ) . '</button></li>
			<li>' . esc_html__( 'do not track pages that are not viewed, e.g. opened in tabs that were never opened', 'full-picture-analytics-cookie-notice' ) . '</li>
		</ol>',
    ),
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/fonts_ico.png" aria-hidden="true">' . esc_html__( 'Safe Fonts', 'full-picture-analytics-cookie-notice' ) . '</div>',
        'field_id'      => 'safefonts',
        'option_arr_id' => $option_arr_id,
        'popup2'        => '<p>' . esc_html__( 'This module has no settings page.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( 'Once enabled, it will replace links to Google Fonts (which share IP addresses of visitor\'s devices with other Google services and partners) with links to the same fonts hosted by GDPR compliant %1$sBunny Fonts%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://fonts.bunny.net" target="_blank">', '</a>' ) . '</p>
			<p>' . sprintf( esc_html__( 'If you are unsure if your website uses any Google Fonts, you can %1$scheck it here%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://fontsplugin.com/google-fonts-checker/" target="_blank">', '</a>' ) . '</p>
			<p>' . esc_html__( 'As a preventive measure, we recommend that you always keep this module enabled - even if you are not using Google Fonts at the moment.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p style="color: #e47d00;">' . esc_html__( 'This function works for links to Google fonts that are available in HTML at the moment of loading the page. Links to fonts loaded later will not be replaced.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p style="color: #e47d00;">' . esc_html__( 'Although it is unclear, it is possible that in some countries you may have to disclose in your privacy policy that you use fonts from Bunny Fonts.', 'full-picture-analytics-cookie-notice' ) . '</p>',
    ),
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/privex_ico.png" aria-hidden="true">' . esc_html__( 'Privacy Policy Extras', 'full-picture-analytics-cookie-notice' ) . '</div>',
        'field_id'      => 'privex',
        'option_arr_id' => $option_arr_id,
        'popup'         => '<p>' . esc_html__( 'This module lets you add to your privacy policy a list of all tracking tools that you use. The list is always up-to-date so that you don\'t need to change it every time you enable or disable a tracking tool.', 'full-picture-analytics-cookie-notice' ) . '</p>',
    )
);
$priv_tools_fields = apply_filters( 'fupi_add_privacy_module_switch', [] );
// ! ADDON
$privacy_fields = array_merge( $privacy_fields, $priv_tools_fields );
// array_push( $privacy_fields, array(
// 	'type'	 			=> 'button',
// 	'label' 			=> '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/addons_ico.png" aria-hidden="true">' . esc_html__( 'Add more', 'full-picture-analytics-cookie-notice' ) . '</div>',
// 	'button_text' 		=> esc_html__( 'View add-ons', 'full-picture-analytics-cookie-notice' ),
// 	'href'				=> 'https://wpfullpicture.com/addons/',
// 	'target'			=> '_blank',
// 	'class'				=> 'fp_button_wrap',
// 	'el_class'			=> 'button button-secondary',
// 	'field_id' 			=> 'customize_notice_btn',
// 	'option_arr_id'		=> $option_arr_id,
// ) );
$woo_disabled_class = ( function_exists( 'WC' ) ? '' : ' fupi_disabled fupi_do_not_hide' );
$woo_disabled_text = ( function_exists( 'WC' ) ? false : esc_html__( 'Requires: WooCommerce plugin', 'full-picture-analytics-cookie-notice' ) );
$extensions_fields = array(
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/reports_ico.png" aria-hidden="true">' . esc_html__( 'Reports & Statistics', 'full-picture-analytics-cookie-notice' ) . '</div>',
        'field_id'      => 'reports',
        'option_arr_id' => $option_arr_id,
        'popup'         => '<p>' . esc_html__( 'This extension lets you add to your WP admin, analytics & marketing dashboards created with Google Looker Studio, Databox or similar platforms.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/analytics-dashboards/" class="button-primary">', '</a>' ) . '</p>',
    ),
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/woo_fav.png" aria-hidden="true">' . esc_html__( 'WooCommerce Tracking', 'full-picture-analytics-cookie-notice' ) . '</div>',
        'field_id'      => 'woo',
        'option_arr_id' => $option_arr_id,
        'class'         => $woo_disabled_class,
        'under field'   => $woo_disabled_text,
        'popup'         => '<p>' . esc_html__( 'This extension lets you track WooCommerce events and product data with your installed tracking tools (all extended integrations and the GTM module support it).', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/woocommerce-tracking/" class="button-primary">', '</a>' ) . '</p>',
    ),
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/adv_triggers_ico.png" aria-hidden="true">' . esc_html__( 'Advanced Triggers', 'full-picture-analytics-cookie-notice' ) . '</div>',
        'field_id'      => 'atrig',
        'option_arr_id' => $option_arr_id,
        'class'         => 'fupi_do_not_hide',
        'must_have'     => 'pro_round',
        'popup'         => '<p>' . esc_html__( 'Here you can define custom events and set up lead scoring rules. You can use it for:', 'full-picture-analytics-cookie-notice' ) . '</p>
			<ol>
				<li>' . esc_html__( 'measuring the quality of traffic sources and ad campaigns,', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'counting how many users are on different stages of customer journeys,', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'creating ultra-specific custom audiences for retargeting campaigns,', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'and many other.', 'full-picture-analytics-cookie-notice' ) . '</li>
			</ol>
			<p>' . esc_html__( 'The module works with Google Analytics, Google Ads, Google Tag Manager, Meta Pixel, Plausible and Matomo.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/advanced-triggers/" class="button-primary">', '</a>' ) . '</p>',
    ),
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/ext_ico.png" aria-hidden="true">' . esc_html__( 'Metadata Tracking', 'full-picture-analytics-cookie-notice' ) . '</div>',
        'field_id'      => 'trackmeta',
        'option_arr_id' => $option_arr_id,
        'class'         => 'fupi_do_not_hide',
        'must_have'     => 'pro_round',
        'popup'         => '<p>' . esc_html__( 'With this module you can track metadata of users, posts, pages, products (e.g. WooCommerce), custom post types, tags, categories or terms of any taxonomy.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . esc_html__( 'You can track default WP data as well as data added by other plugins and frameworks like ACF or Meta Box.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . esc_html__( 'This module is compatible with Google Analytics, Matomo, Facebook Pixel, GTM, Plausible and Microsoft Clarity modules.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/metadata-tracking/" class="button-primary">', '</a>' ) . '</p>',
    ),
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/brokenlink_ico.png" aria-hidden="true">' . esc_html__( 'Broken Links Tracking', 'full-picture-analytics-cookie-notice' ) . '</div>',
        'field_id'      => 'track404',
        'option_arr_id' => $option_arr_id,
        'popup'         => '<p>' . esc_html__( 'This extension lets you find links that lead to non-existent pages on your website and redirect them to a custom 404 page.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . esc_html__( 'To track broken links you need to use a tracking tool which lets you view full addresses of the visited pages, e.g. Google Analytics or Matomo.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/broken-links-tracking/" class="button-primary">', '</a>' ) . '</p>',
    ),
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/pagelabel_ico.png" aria-hidden="true">' . esc_html__( 'Page Labels', 'full-picture-analytics-cookie-notice' ) . '</div>',
        'field_id'      => 'labelpages',
        'option_arr_id' => $option_arr_id,
        'class'         => 'fupi_do_not_hide',
        'must_have'     => 'pro_round',
        'popup'         => '<p>' . esc_html__( 'This extension has no settings page. When activated, it will add a "page label" field to page edit screens.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( 'Use it, to label pages according to their type, e.g. landing page, contact page, etc. This information can be sent to tracking and marketing tools for analysis or be used by developers. %1$sLearn more%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/what-is-page-labeling-and-how-to-use-it/?utm_source=fp_admin&utm_medium=referral&utm_campaign=tools_link" target="_blank">', '</a>' ) . '</p>
			<p>' . esc_html__( 'At the moment this extension works with Google Analytics, Facebook Pixel and Google Tag Manager.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/page-labels/" class="button-primary">', '</a>' ) . '</p>',
    ),
    array(
        'type'          => 'toggle',
        'label'         => '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/geo_ico.png" aria-hidden="true">' . esc_html__( 'Geolocation', 'full-picture-analytics-cookie-notice' ) . '</div>',
        'field_id'      => 'geo',
        'option_arr_id' => $option_arr_id,
        'class'         => 'fupi_do_not_hide',
        'must_have'     => 'pro_round',
        'popup'         => '<p>' . esc_html__( 'With the Geolocation module you can:', 'full-picture-analytics-cookie-notice' ) . '</p>
			<ol>
				<li>' . esc_html__( 'use geolocation-based options of the consent banner (useful when your visitors come from different countries, with different privacy rules),', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . esc_html__( 'save on premium tracking tools (by loading them only in countries where you do business),', 'full-picture-analytics-cookie-notice' ) . '</li>
				<li>' . sprintf( esc_html__( 'prevent Google Analytics from loading in %1$scountries where it is illegal%2$s,', 'full-picture-analytics-cookie-notice' ), '<a href="https://www.simpleanalytics.com/google-analytics-is-illegal-in-these-countries">', '</a>' ) . '</li>
				<li>' . esc_html__( 'load 3rd party scripts only in countries where you need them (e.g. load live chat only in countries where you do business)', 'full-picture-analytics-cookie-notice' ) . '</li>
			</ol>
			<p>' . esc_html__( '[Technical] This module saves JavaScript variables fpdata.country and fpdata.region which developers can use in their scripts.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/module/geolocation/" class="button-primary">', '</a>' ) . '</p>',
    )
);
$ext_tools_fields = apply_filters( 'fupi_add_ext_module_switch', [] );
// ! ADDON
$extensions_fields = array_merge( $extensions_fields, $ext_tools_fields );
// array_push( $extensions_fields, array(
// 	'type'	 			=> 'button',
// 	'label' 			=> '<div class="fupi_field_title_wrap"><img class="fupi_label_icon" src="' . FUPI_URL . 'admin/assets/img/addons_ico.png" aria-hidden="true">' . esc_html__( 'Add more', 'full-picture-analytics-cookie-notice' ) . '</div>',
// 	'button_text' 		=> esc_html__( 'View add-ons', 'full-picture-analytics-cookie-notice' ),
// 	'href'				=> 'https://wpfullpicture.com/addons/',
// 	'target'			=> '_blank',
// 	'class'				=> 'fp_button_wrap',
// 	'el_class'			=> 'button button-secondary',
// 	'field_id' 			=> 'customize_notice_btn',
// 	'option_arr_id'		=> $option_arr_id,
// ) );
// ALL TOGETHER
$sections = array(
    // INTEGRATIONS
    array(
        'section_id'    => 'fupi_tools_integrations',
        'section_title' => esc_html__( 'Ready-to-use integrations', 'full-picture-analytics-cookie-notice' ),
        'fields'        => $tools_fields,
    ),
    // TAG MANAGERS
    array(
        'section_id'    => 'fupi_tools_tagmanagers',
        'section_title' => esc_html__( 'Manual integrations', 'full-picture-analytics-cookie-notice' ),
        'fields'        => $tagmanagers_fields,
    ),
    // PRIVACY
    array(
        'section_id'    => 'fupi_tools_privacy',
        'section_title' => esc_html__( 'Privacy solutions', 'full-picture-analytics-cookie-notice' ),
        'fields'        => $privacy_fields,
    ),
    // EXTENSIONS
    array(
        'section_id'    => 'fupi_tools_ext',
        'section_title' => esc_html__( 'Extensions', 'full-picture-analytics-cookie-notice' ),
        'fields'        => $extensions_fields,
    ),
);