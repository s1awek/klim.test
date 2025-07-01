<?php

$option_arr_id = 'fupi_cook';
$field_disabled = 'fupi_disabled';
$must_have_html = '<div class="fupi_must_have_pro_ico_round fupi_tooltip"><span class="dashicons dashicons-lock"></span><span class="fupi_tooltiptext">' . esc_html__( 'Requires Pro licence', 'full-picture-analytics-cookie-notice' ) . '</span></div>';
$priv_policy_url = get_privacy_policy_url();
$priv_policy_url_text = ( empty( $priv_policy_url ) ? '<p style="color: red;">' . esc_html__( 'Please, set a privacy policy page in "Settings > Privacy" and make sure that it is published', 'full-picture-analytics-cookie-notice' ) . '</p>' : '' );
$install_id_text = '';
$current_theme = wp_get_theme();
$is_oceanWP_theme = $current_theme->get( 'Name' ) == 'OceanWP';
$modes_selector = '<div id="fupi_cookie_notice_mode_selector">
	<div id="fupi_cookie_optin" class="fupi_cn_mode">
		<p class="fupi_title"><strong>' . esc_html__( 'Opt-in mode', 'full-picture-analytics-cookie-notice' ) . '</strong></p>
		<p class="fupi_descr">' . esc_html__( 'Tracking starts when visitors agree to it.', 'full-picture-analytics-cookie-notice' ) . '<br><br><strong>' . esc_html__( 'This mode is accepted in all countries.', 'full-picture-analytics-cookie-notice' ) . '</strong> <button type="button" class="fupi_open_popup fupi_faux_link" data-popup="fupi_all_optin_popup" >' . esc_html__( 'Learn more', 'full-picture-analytics-cookie-notice' ) . '</button></p>
		<div id="fupi_all_optin_popup" class="fupi_popup_content fupi_do_not_create_popup_icon">
			<p>' . esc_html__( 'When you choose this mode, tools that collect personal information will not be allowed to track your users until they agree to tracking them. Tools that do not use cookies or are running in consent mode / privacy mode, will track basic data until visitors agree to full tracking.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( 'Please %1$sread this article%2$s to see in what countries you have to use this mode.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/countries-that-require-opt-in-or-opt-out-to-cookies/?utm_source=fp_admin&utm_medium=referral&utm_campaign=documentation_link">', '</a>' ) . '</p>
		</div>
		<div class="fupi_switch"><span class="fupi_switch_slider" data-mode="optin"></span></div>
	</div>
	<div id="fupi_cookie_optout" class="fupi_cn_mode">
		<p class="fupi_title"><strong>' . esc_html__( 'Opt-out mode', 'full-picture-analytics-cookie-notice' ) . '</strong></p>
		<p class="fupi_descr">' . esc_html__( 'Visitors are tracked until they decline tracking.', 'full-picture-analytics-cookie-notice' ) . '<br><br><strong>' . esc_html__( 'This mode is against the law in the EU and 30+ other countries.', 'full-picture-analytics-cookie-notice' ) . '</strong> <button type="button" class="fupi_open_popup fupi_faux_link" data-popup="fupi_all_optout_popup" >' . esc_html__( 'Learn more', 'full-picture-analytics-cookie-notice' ) . '</button></p>
		<div id="fupi_all_optout_popup" class="fupi_popup_content fupi_do_not_create_popup_icon">
			<p>' . esc_html__( 'When you choose this mode, tools that collect personal information will track your users from the moment they visit your website until they decline tracking in the consent banner. Tools that do not use cookies or those that can run in consent mode / privacy mode, will then only track basic data. All other tools will be disabled.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( 'Please %1$sread this article%2$s to see in what countries you can use this mode.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/countries-that-require-opt-in-or-opt-out-to-cookies/?utm_source=fp_admin&utm_medium=referral&utm_campaign=documentation_link">', '</a>' ) . '</p>
		</div>
		<div class="fupi_switch"><span class="fupi_switch_slider" data-mode="optout"></span></div>
	</div>
	<div id="fupi_cookie_notify" class="fupi_cn_mode">
		<p class="fupi_title"><strong>' . esc_html__( 'Notify-only mode', 'full-picture-analytics-cookie-notice' ) . '</strong></p>
		<p class="fupi_descr">' . esc_html__( 'Visitors from every country are informed that they are tracked but cannot decline it.', 'full-picture-analytics-cookie-notice' ) . '<br><br><strong>' . esc_html__( 'This mode is against the law in the EU and 30+ other countries.', 'full-picture-analytics-cookie-notice' ) . '</strong> <button type="button" class="fupi_open_popup fupi_faux_link" data-popup="fupi_all_inform_popup" >' . esc_html__( 'Learn more', 'full-picture-analytics-cookie-notice' ) . '</button></p>
		<div id="fupi_all_inform_popup" class="fupi_popup_content fupi_do_not_create_popup_icon">
			<p>' . esc_html__( 'When you choose this mode, all tracking tools installed on your website will track your visitors. People will not be able to decline tracking. They will only be informed that they are tracked.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( 'Please %1$sread this article%2$s to see in what countries you can use this mode.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/countries-that-require-opt-in-or-opt-out-to-cookies/?utm_source=fp_admin&utm_medium=referral&utm_campaign=documentation_link">', '</a>' ) . '</p>
		</div>
		<div class="fupi_switch"><span class="fupi_switch_slider" data-mode="notify"></span></div>
	</div>
	<div id="fupi_cookie_auto_strict" class="fupi_cn_mode ' . $field_disabled . '">
		' . $must_have_html . '
		<p class="fupi_title"><strong>' . esc_html__( 'Automatic mode', 'full-picture-analytics-cookie-notice' ) . '</strong> (strict)</p>
		<p class="fupi_descr">' . esc_html__( 'Automatically apply opt-in, opt-out or notification mode, depending on the location of the visitor.', 'full-picture-analytics-cookie-notice' ) . '<br><br><strong>' . esc_html__( 'This mode is accepted in all countries.', 'full-picture-analytics-cookie-notice' ) . '</strong><br><br> (' . esc_html__( 'The Strict mode is for sites that use visitors\' data for marketing or collect sensitive data.', 'full-picture-analytics-cookie-notice' ) . ') <button type="button" class="fupi_open_popup fupi_faux_link" data-popup="fupi_auto_strict_popup">' . esc_html__( 'Learn more', 'full-picture-analytics-cookie-notice' ) . '</button></p>
		<div id="fupi_auto_strict_popup" class="fupi_popup_content fupi_do_not_create_popup_icon">
			<p>' . esc_html__( 'When you choose this mode, WP Full Picture will check the location of your visitors and automatically choose the tracking mode (opt-in, opt-out or inform) that complies with privacy regulations in their countries.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( 'Use this mode if your website uses visitor\'s data for marketing purposes or collect %1$ssensitive data%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://commission.europa.eu/law/law-topic/data-protection/reform/rules-business-and-organisations/legal-grounds-processing-data/sensitive-data/what-personal-data-considered-sensitive_en">', '</a>' ) . '.</p>
			<p>' . sprintf( esc_html__( 'You can learn more about these modes %1$sfrom this article%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/countries-that-require-opt-in-or-opt-out-to-cookies/?utm_source=fp_admin&utm_medium=referral&utm_campaign=documentation_link">', '</a>' ) . '.</p>
		</div>	
		<div class="fupi_switch"><span class="fupi_switch_slider" data-mode="auto_strict"></span></div>
	</div>
	<div id="fupi_cookie_auto_lax" class="fupi_cn_mode ' . $field_disabled . '">
		' . $must_have_html . '
		<p class="fupi_title"><strong>' . esc_html__( 'Automatic mode', 'full-picture-analytics-cookie-notice' ) . '</strong> (lax)</p>
		<p class="fupi_descr">' . esc_html__( 'Automatically apply opt-in, opt-out or notification mode, depending on the location of the visitor.', 'full-picture-analytics-cookie-notice' ) . '<br><br><strong>' . esc_html__( 'This mode is accepted in all countries.', 'full-picture-analytics-cookie-notice' ) . '</strong><br><br>(' . esc_html__( 'The Lax mode is for sites that don\'t use visitors\' data for marketing or collect sensitive data.', 'full-picture-analytics-cookie-notice' ) . ') <button type="button" class="fupi_open_popup fupi_faux_link" data-popup="fupi_auto_strict_popup">' . esc_html__( 'Learn more', 'full-picture-analytics-cookie-notice' ) . '</button></p>
		<div id="fupi_auto_lax_popup" class="fupi_popup_content fupi_do_not_create_popup_icon">
			<p>' . esc_html__( 'When you choose this mode, WP Full Picture will check the location of your visitors and automatically choose the tracking mode (opt-in, opt-out or inform) that complies with privacy regulations in their countries.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__( 'Use this mode if your website doesn\'t use visitor\'s data for marketing purposes nor collect %1$ssensitive data%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://commission.europa.eu/law/law-topic/data-protection/reform/rules-business-and-organisations/legal-grounds-processing-data/sensitive-data/what-personal-data-considered-sensitive_en">', '</a>' ) . '.</p>
			<p>' . sprintf( esc_html__( 'You can learn more about these modes %1$sfrom this article%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/countries-that-require-opt-in-or-opt-out-to-cookies/?utm_source=fp_admin&utm_medium=referral&utm_campaign=documentation_link">', '</a>' ) . '.</p>
		</div>
		<div class="fupi_switch"><span class="fupi_switch_slider" data-mode="auto_lax"></span></div>
	</div>
	<div id="fupi_cookie_manual" class="fupi_cn_mode ' . $field_disabled . '">
		' . $must_have_html . '
		<p class="fupi_title"><strong>' . esc_html__( 'Manual mode', 'full-picture-analytics-cookie-notice' ) . '</strong></p>
		<p class="fupi_descr">' . esc_html__( 'Manually choose tracking settings for different countries.', 'full-picture-analytics-cookie-notice' ) . ' <button type="button" class="fupi_open_popup fupi_faux_link" data-popup="fupi_manual_popup">' . esc_html__( 'Learn more', 'full-picture-analytics-cookie-notice' ) . '</button></p>
		<div id="fupi_manual_popup" class="fupi_popup_content fupi_do_not_create_popup_icon">
			<p>' . esc_html__( 'When you choose this mode, WP Full Picture will check the location of your visitors and will adjust tracking and the consent banner\'s elements according to the settings that you manually chose. You will be able to choose different settings for each country.', 'full-picture-analytics-cookie-notice' ) . '</p>
		</div>
		<div class="fupi_switch"><span class="fupi_switch_slider" data-mode="manual"></span></div>
	</div>
</div>';
$cook_fields = array(array(
    'type'          => 'radio',
    'label'         => esc_html__( 'How the consent banner should behave', 'full-picture-analytics-cookie-notice' ),
    'field_id'      => 'enable_scripts_after',
    'class'         => 'fupi_cookie_notice_modes fupi_fullwidth_tr fupi_hide_manual_mode',
    'option_arr_id' => $option_arr_id,
    'options'       => array(
        'optin'  => esc_html__( 'Start tracking when people agree to it (GDPR compliant)', 'full-picture-analytics-cookie-notice' ),
        'optout' => esc_html__( 'Start tracking when people get to your website but let them opt-out', 'full-picture-analytics-cookie-notice' ),
        'notify' => esc_html__( 'Start tracking when people get to your website and DON\'T let them opt-out', 'full-picture-analytics-cookie-notice' ),
    ),
    'default'       => 'optin',
    'under field'   => $modes_selector,
));
$theme_compat_notice = '';
if ( $is_oceanWP_theme ) {
    $theme_compat_notice = '<p style="color: red;">' . sprintf( esc_html__( 'Attention. OceanWP theme breaks the controls for styling the consent banner. They will not be available when using OceanWP. %1$sLearn what to do about it%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-go-around-the-incompatibility-issues-with-oceanwp-theme/" target="_blank">', '</a>' ) . '</p>';
}
$cook_fields = array_merge( $cook_fields, array(
    array(
        'type'          => 'button',
        'label'         => esc_html__( 'Customize the banner', 'full-picture-analytics-cookie-notice' ),
        'button_text'   => esc_html__( 'Customize the banner', 'full-picture-analytics-cookie-notice' ),
        'icon'          => 'dashicons dashicons-admin-appearance',
        'href'          => wp_customize_url() . '?autofocus[panel]=fupi_notice',
        'target'        => '_blank',
        'class'         => 'fupi_customizer_link_wrap',
        'el_class'      => 'button button-secondary fupi_customize_notice_btn',
        'field_id'      => 'customize_notice_btn',
        'option_arr_id' => $option_arr_id,
        'after field'   => $theme_compat_notice,
        'under field'   => '<p>' . esc_html__( 'You must save changes before you can start customizing.', 'full-picture-analytics-cookie-notice' ) . '</p>',
    ),
    array(
        'type'           => 'toggle',
        'label'          => esc_html__( 'Enable Advanced Consent Mode v2 for Google Analytics and Google Ads', 'full-picture-analytics-cookie-notice' ),
        'field_id'       => 'gtag_no_cookie_mode',
        'el_class'       => 'fupi_condition',
        'el_data_target' => 'fupi_passthr_cond',
        'option_arr_id'  => $option_arr_id,
        'popup2'         => '<p>' . sprintf( esc_html__( 'When this option is %1$sdisabled%2$s, Google Analytics and Google Ads will use the basic consent mode v2.', 'full-picture-analytics-cookie-notice' ), '<strong>', '</strong>' ) . '</p>
			<p style="color: #e47d00">' . esc_html__( 'Advanced Consent Mode only benefits websites with traffic greater then 1000 consenting users/day or 700 ad clicks/day.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<a class="button-secondary" target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-enable-consent-mode-for-google-ads-analytics-and-gtm/">' . esc_html__( 'Everything you need to know about it', 'full-picture-analytics-cookie-notice' ) . '</a>
			<h3>' . esc_html__( 'For users of Google Tag Manager', 'full-picture-analytics-cookie-notice' ) . '</h3>
			<p>' . esc_html__( 'If you use the Google Tag Manager module, WP Full Picture automatically sends to GTM\'s dataLayer information on user consents in the format required by Google.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . esc_html__( 'Tag "Google Tag" automatically recognizes consent information sent by WP FP and starts Google Analytics and Ads based on the provided consents. Other tags need to be manually configured to respect consents.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . esc_html__( 'To do this, open the settings of a tag in GTM and click "Advanced Settings" > "Consent Settings (beta)" > "Require additional consents" and choose the consents you need.', 'full-picture-analytics-cookie-notice' ) . '</p>',
    ),
    array(
        'type'          => 'toggle',
        'label'         => esc_html__( 'Use link decoration to improve conversion tracking', 'full-picture-analytics-cookie-notice' ),
        'field_id'      => 'url_passthrough',
        'class'         => 'fupi_sub fupi_disabled fupi_passthr_cond',
        'option_arr_id' => $option_arr_id,
        'popup3'        => '<p>' . esc_html__( 'This will enable Google\'s "url_passthrough" feature for link decoration. It will add a Google\'s advertising identifier to all links on your website which will improve conversion tracking.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p style="color: #d50000">' . esc_html__( 'Attention! Using link decoration is a legal grey area and may be illegal in countries where consent before tracking is necessary (opt-in). Use at your own risk.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p style="color: #d50000">' . esc_html__( 'Attention! Link decoration may, in very rare cases, cause problems on a website. To test it, visit your website from a Google advertisement and finish the whole conversion path.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p style="color: #d50000">' . esc_html__( 'Attention! Link decoration will only be used in countries where consent banner lets users decline tracking.', 'full-picture-analytics-cookie-notice' ) . '</p>',
    ),
    array(
        'type'          => 'toggle',
        'label'         => esc_html__( 'Ask visitors for consent again, when new tracking tools get enabled or when privacy policy is updated', 'full-picture-analytics-cookie-notice' ),
        'field_id'      => 'ask_for_consent_again',
        'option_arr_id' => $option_arr_id,
        'popup2'        => '<p style="color: #e47d00">' . esc_html__( 'Attention. WP Full Picture is not aware of what tools the GTM installs. When new tools get installed inside GTM\'s container, visitors will not be asked for consent again.', 'full-picture-analytics-cookie-notice' ) . '</p>',
        'under field'   => '<p>' . esc_html__( 'Highly recommended. Required in most countries.', 'full-picture-analytics-cookie-notice' ) . '</p>' . $priv_policy_url_text,
    ),
    array(
        'type'          => 'text',
        'label'         => esc_html__( 'Show consent banner when visitors click this page element', 'full-picture-analytics-cookie-notice' ),
        'field_id'      => 'toggle_selector',
        'option_arr_id' => $option_arr_id,
        'label_for'     => $option_arr_id . '[toggle_selector]',
        'placeholder'   => esc_html__( 'e.g. #some_button', 'full-picture-analytics-cookie-notice' ),
        'under field'   => esc_html__( 'Leave empty to use the default .fp_show_cookie_notice', 'full-picture-analytics-cookie-notice' ),
        'popup'         => '<p>' . esc_html__( 'We recommend that you point at a link or a button in your privacy policy. This is required in most countries that require consent banners.', 'full-picture-analytics-cookie-notice' ) . '</p>',
    ),
    array(
        'type'          => 'page_search',
        'field_id'      => 'hide_on_pages',
        'label'         => esc_html__( 'Hide consent banner on these pages', 'full-picture-analytics-cookie-notice' ),
        'option_arr_id' => $option_arr_id,
        'popup'         => '<p>' . esc_html__( 'By default, WP Full Picture hides consent banner on the privacy policy page. Select other pages, where the banner should be hidden.', 'full-picture-analytics-cookie-notice' ) . '</p>
		<p>' . esc_html__( 'Hiding the banner only hides it visually. It does not automatically give consent to tracking.', 'full-picture-analytics-cookie-notice' ) . '</p>',
    )
) );
$sections = array(
    // Consent Banner
    array(
        'section_id'    => 'fupi_cook_main',
        'section_title' => esc_html__( 'Consent banner settings', 'full-picture-analytics-cookie-notice' ),
        'fields'        => $cook_fields,
    ),
    // Saving consents
    array(
        'section_id'    => 'fupi_cook_cdb',
        'section_title' => esc_html__( 'Records of consents', 'full-picture-analytics-cookie-notice' ),
        'fields'        => array(array(
            'type'          => 'text',
            'label'         => esc_html__( 'ConsentsDB secret key', 'full-picture-analytics-cookie-notice' ),
            'field_id'      => 'cdb_key',
            'must_have'     => 'privacy_policy',
            'option_arr_id' => $option_arr_id,
            'after field'   => $install_id_text,
        ), array(
            'type'          => 'toggle',
            'label'         => esc_html__( 'Do not filter bot traffic', 'full-picture-analytics-cookie-notice' ),
            'field_id'      => 'save_all_consents',
            'option_arr_id' => $option_arr_id,
            'must_have'     => 'privacy_policy',
            'popup'         => '<p>' . esc_html__( 'By default, WP Full Picture does not save consents of visitors recognized as bots and those who consented within 1 second from the moment the page has loaded.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . sprintf( esc_html__( 'If, for any reason, this filters too much traffic, please %1$slet us know about it%2$s and either enable this option or change the "Bot detection list" in the General Settings page.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/contact/" target="_blank">', '</a>' ) . '</p>',
        ), array(
            'type'          => 'toggle',
            'label'         => esc_html__( 'Allow site visitors to view consent data (beta)', 'full-picture-analytics-cookie-notice' ),
            'field_id'      => 'consent_access',
            'option_arr_id' => $option_arr_id,
            'must_have'     => 'privacy_policy',
            'popup'         => '<p>' . esc_html__( 'Let your visitors view all the information that was collected about their consent, the same way you see it in the ConsentsDB.com.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . esc_html__( 'When the consent is properly saved, Consent ID that is at the bottom of the consent banner will turn into a link.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . esc_html__( 'Consent may not be properly saved if you run out of available consents to save. In that case, please top up your account by purchasing one of the available plans.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<h3>' . esc_html__( 'How to enable it', 'full-picture-analytics-cookie-notice' ) . '</h3>
				<p>' . sprintf( esc_html__( 'To use this feature, you need to enable two settings - this one and the option to "Allow site visitors to view consent data" in the settings of your website in ConsentsDB. %1$sLearn more%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/contact/" target="_blank">', '</a>' ) . '</p>',
        )),
    ),
);