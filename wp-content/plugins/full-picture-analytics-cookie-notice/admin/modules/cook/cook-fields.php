<?php

$option_arr_id = 'fupi_cook';
$field_disabled = 'fupi_disabled';
$must_have_html = '<div class="fupi_must_have_pro_ico_round fupi_tooltip"><span class="dashicons dashicons-lock"></span><span class="fupi_tooltiptext">' . esc_html__( 'Requires Pro licence', 'full-picture-analytics-cookie-notice' ) . '</span></div>';
$priv_policy_url = get_privacy_policy_url();
$priv_policy_url_text = ( empty( $priv_policy_url ) ? '<p style="color: red;">' . sprintf( esc_html__( 'Please make sure that your privacy policy page is published and set %1$son this page%2$s page.', 'full-picture-analytics-cookie-notice' ), '<a href="/wp-admin/options-privacy.php" target="_blank">', '</a>' ) . '</p>' : '' );
$current_theme = wp_get_theme();
$is_oceanWP_theme = $current_theme->get( 'Name' ) == 'OceanWP';
// $install_id_text = '';
// if ( fupi_fs()->can_use_premium_code__premium_only() ) {
// 	$install_id_text = '<p>' . sprintf( esc_html__( 'Provide this Install ID %1$s during registration to be able to save 500 proofs/day for free until August 31, 2025.', 'full-picture-analytics-cookie-notice'), '<code>' . fupi_fs()->get_site()->id . '</code>' ) . '</p>';
// 	if ( isset( $this->tools['geo'] ) ) {
// 		$field_disabled = '';
// 		$must_have_html = '';
// 	} else {
// 		$must_have_html = '<div class="fupi_must_have_info">' . esc_html__( 'Requires', 'full-picture-analytics-cookie-notice' ) . ': <span class="fupi_req">Geolocation module</span></div>';
// 	}
// };
// CONSENT BANNER FIELDS
$cook_fields = array(array(
    'type'          => 'select',
    'label'         => esc_html__( 'How should the banner work?', 'full-picture-analytics-cookie-notice' ),
    'field_id'      => 'enable_scripts_after',
    'option_arr_id' => $option_arr_id,
    'options'       => array(
        'optin'  => esc_html__( 'Opt-in mode (GDPR compliant, recommended)', 'full-picture-analytics-cookie-notice' ),
        'optout' => esc_html__( 'Opt-out mode', 'full-picture-analytics-cookie-notice' ),
        'notify' => esc_html__( 'Notification mode', 'full-picture-analytics-cookie-notice' ),
    ),
    'default'       => 'optin',
    'under field'   => '<p>' . esc_html__( 'Enable the Geolocation module to choose geolocation-based modes.', 'full-picture-analytics-cookie-notice' ) . '</strong></p>',
));
$theme_compat_notice = '';
if ( $is_oceanWP_theme ) {
    $theme_compat_notice = '<p style="color: red;">' . sprintf( esc_html__( 'Attention. OceanWP theme breaks the controls for styling the consent banner. They will not be available when using OceanWP. %1$sLearn what to do about it%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-go-around-the-incompatibility-issues-with-oceanwp-theme/" target="_blank">', '</a>' ) . '</p>';
}
$cook_fields = array_merge( $cook_fields, array(
    array(
        'type'          => 'button',
        'label'         => esc_html__( 'Change how the banner looks', 'full-picture-analytics-cookie-notice' ),
        'button_text'   => esc_html__( 'Style the banner', 'full-picture-analytics-cookie-notice' ),
        'icon'          => 'dashicons dashicons-admin-appearance',
        'href'          => wp_customize_url() . '?autofocus[panel]=fupi_notice',
        'class'         => 'fupi_customizer_link_wrap',
        'el_class'      => 'button button-primary fupi_customize_notice_btn',
        'field_id'      => 'customize_notice_btn',
        'option_arr_id' => $option_arr_id,
        'after field'   => $theme_compat_notice,
        'under field'   => '<p>' . esc_html__( 'You must save changes before you can start customizing.', 'full-picture-analytics-cookie-notice' ) . '</p>',
    ),
    // array(
    // 	'type'	 			=> 'toggle',
    // 	'label' 			=> esc_html__( 'Ask visitors for consent again, when new tracking tools get enabled or when privacy policy is updated', 'full-picture-analytics-cookie-notice' ),
    // 	'field_id' 			=> 'ask_for_consent_again',
    // 	'option_arr_id'		=> $option_arr_id,
    // 	'popup2'			=> '<p class="fupi_warning_text">' . esc_html__('Attention. WP Full Picture is not aware of what tools the GTM installs. When new tools get installed inside GTM\'s container, visitors will not be asked for consent again.','full-picture-analytics-cookie-notice').'</p>',
    // 	'under field' 		=> '<p>' . esc_html__( 'Highly recommended. Required in most countries.', 'full-picture-analytics-cookie-notice' ) . '</p>' . $priv_policy_url_text,
    // ),
    array(
        'type'          => 'page_search',
        'field_id'      => 'hide_on_pages',
        'label'         => esc_html__( 'Hide consent banner on these pages', 'full-picture-analytics-cookie-notice' ),
        'option_arr_id' => $option_arr_id,
        'popup'         => '<p>' . esc_html__( 'By default, WP Full Picture hides consent banner on the privacy policy page.', 'full-picture-analytics-cookie-notice' ) . '</p>
		<p>' . esc_html__( 'Hiding the banner only hides it visually. It does not automatically give consent to tracking.', 'full-picture-analytics-cookie-notice' ) . '</p>',
    ),
    array(
        'type'          => 'text',
        'label'         => esc_html__( 'Show consent banner when visitors click this page element', 'full-picture-analytics-cookie-notice' ),
        'field_id'      => 'toggle_selector',
        'class'         => 'fupi_join',
        'option_arr_id' => $option_arr_id,
        'label_for'     => $option_arr_id . '[toggle_selector]',
        'placeholder'   => esc_html__( 'e.g. #some_button', 'full-picture-analytics-cookie-notice' ),
        'under field'   => esc_html__( 'Leave empty to use the default .fp_show_cookie_notice. Alternatively, you can enable a toggle icon in the styling options above.', 'full-picture-analytics-cookie-notice' ),
        'popup'         => '<p>' . esc_html__( 'We recommend that you point at a link or a button in your privacy policy. This is required in most countries that require consent banners.', 'full-picture-analytics-cookie-notice' ) . '</p>',
    ),
) );
// SCRIPT BLOCKING
$scr_fields = array(
    array(
        'type'        => 'text',
        'field_id'    => 'title',
        'el_class'    => 'fupi_internal_title',
        'label'       => esc_html__( 'Tool\'s name', 'full-picture-analytics-cookie-notice' ),
        'placeholder' => esc_html__( 'Name', 'full-picture-analytics-cookie-notice' ),
        'class'       => 'fupi_col_100',
        'required'    => true,
    ),
    array(
        'label'    => esc_html__( 'Control loading of', 'full-picture-analytics-cookie-notice' ),
        'type'     => 'select',
        'field_id' => 'block_by',
        'options'  => array(
            'content'   => esc_html__( 'Script with unique text content', 'full-picture-analytics-cookie-notice' ),
            'src'       => esc_html__( 'Script with a specific filename or URL', 'full-picture-analytics-cookie-notice' ),
            'link_href' => esc_html__( 'HTML <link> tag with a specific filename or URL', 'full-picture-analytics-cookie-notice' ),
            'img_src'   => esc_html__( 'Image with a specific filename or URL', 'full-picture-analytics-cookie-notice' ),
        ),
        'class'    => 'fupi_col_30',
    ),
    array(
        'type'     => 'text',
        'field_id' => 'url_part',
        'label'    => esc_html__( 'Filename, full URL, URL part or unique text content', 'full-picture-analytics-cookie-notice' ),
        'required' => true,
        'class'    => 'fupi_col_50',
    ),
    array(
        'label'    => esc_html__( 'Script ID', 'full-picture-analytics-cookie-notice' ),
        'type'     => 'text',
        'field_id' => 'id',
        'required' => true,
        'class'    => 'fupi_col_20',
    ),
    array(
        'label'             => esc_html__( 'What is visitor\'s data used for?', 'full-picture-analytics-cookie-notice' ),
        'type'              => 'label',
        'field_id'          => 'types_label',
        'start_sub_section' => true,
        'class'             => 'fupi_col_40',
    ),
    array(
        'type'     => 'checkbox',
        'field_id' => 'stats',
        'label'    => esc_html__( 'Statistics', 'full-picture-analytics-cookie-notice' ),
        'class'    => 'fupi_col_20',
    ),
    array(
        'type'     => 'checkbox',
        'field_id' => 'market',
        'label'    => esc_html__( 'Marketing', 'full-picture-analytics-cookie-notice' ),
        'class'    => 'fupi_col_20',
    ),
    array(
        'type'            => 'checkbox',
        'field_id'        => 'pers',
        'label'           => esc_html__( 'Personalisation', 'full-picture-analytics-cookie-notice' ),
        'class'           => 'fupi_col_20',
        'end_sub_section' => true,
    ),
    array(
        'label'    => esc_html__( 'Tool\'s privacy policy URL (for use in [fp_info] shortcode)', 'full-picture-analytics-cookie-notice' ),
        'type'     => 'url',
        'field_id' => 'pp_url',
        'class'    => 'fupi_col_50_grow',
    )
);
if ( !empty( $this->tools ) && isset( $this->tools['geo'] ) ) {
    $geo_scr_fields = array(array(
        'label'             => esc_html__( 'Load only in specific countries (leave blank to use everywhere)', 'full-picture-analytics-cookie-notice' ),
        'type'              => 'label',
        'field_id'          => 'countries_label',
        'start_sub_section' => true,
        'class'             => 'fupi_col_100',
    ), array(
        'type'     => 'select',
        'field_id' => 'method',
        'options'  => array(
            'excl' => esc_html__( 'All except', 'full-picture-analytics-cookie-notice' ),
            'incl' => esc_html__( 'Only in', 'full-picture-analytics-cookie-notice' ),
        ),
        'class'    => 'fupi_col_20',
    ), array(
        'type'            => 'text',
        'field_id'        => 'countries',
        'placeholder'     => esc_html__( 'e.g. GB, DE, FR, AU, etc.', 'full-picture-analytics-cookie-notice' ),
        'end_sub_section' => true,
        'class'           => 'fupi_col_50',
    ));
    $scr_fields = array_merge( $scr_fields, $geo_scr_fields );
}
$scr_fields = array_merge( $scr_fields, array(array(
    'label'    => esc_html__( 'Temporarily stop WP Full Picture from managing this script (blocking, conditional-loading, etc.)', 'full-picture-analytics-cookie-notice' ),
    'type'     => 'checkbox',
    'field_id' => 'force_load',
    'class'    => 'fupi_col_66_grow',
)) );
$sections = array(
    // Consent Banner
    array(
        'section_id'    => 'fupi_cook_main',
        'section_title' => esc_html__( 'Consent banner settings', 'full-picture-analytics-cookie-notice' ),
        'fields'        => $cook_fields,
    ),
    // SCRIPT BLOCKING
    array(
        'section_id'    => 'fupi_cook_scriptblock',
        'section_title' => esc_html__( 'Control tracking tools', 'full-picture-analytics-cookie-notice' ),
        'fields'        => array(array(
            'type'          => 'multi checkbox',
            'label'         => esc_html__( 'Automatically control loading of tracking tools', 'full-picture-analytics-cookie-notice' ),
            'field_id'      => 'scrblk_auto_rules',
            'option_arr_id' => $option_arr_id,
            'options'       => array(
                'jetpack' => esc_html__( 'Jetpack Stats (from the Jetpack plugin)', 'full-picture-analytics-cookie-notice' ),
            ),
            'under field'   => '<label><input type="checkbox" checked disabled>' . esc_html__( 'Google Analytics (always enabled, via Consent Mode v2)', 'full-picture-analytics-cookie-notice' ) . '</label>
					<div class="fupi_spacer"></div>
					<input type="checkbox" checked disabled>' . esc_html__( 'Google Ads (always enabled, via Consent Mode v2)', 'full-picture-analytics-cookie-notice' ) . '</label>
					<div class="fupi_spacer"></div>
					<input type="checkbox" checked disabled>' . esc_html__( 'Microsoft Advertising (always enabled, via MS EUT Consent Mode)', 'full-picture-analytics-cookie-notice' ) . '</label>',
        ), array(
            'type'           => 'toggle',
            'label'          => esc_html__( 'Control other tools', 'full-picture-analytics-cookie-notice' ),
            'field_id'       => 'control_other_tools',
            'el_class'       => 'fupi_condition',
            'el_data_target' => 'fupi_manual_blockscr_cond',
            'option_arr_id'  => $option_arr_id,
        ), array(
            'type'          => 'r3',
            'label'         => esc_html__( 'Manually set up what tools to control', 'full-picture-analytics-cookie-notice' ),
            'field_id'      => 'scrblk_manual_rules',
            'option_arr_id' => $option_arr_id,
            'class'         => 'fupi_sub fupi_fullwidth_tr fupi_disabled fupi_manual_blockscr_cond',
            'is_repeater'   => true,
            'fields'        => $scr_fields,
            'before field'  => '<a href="https://wpfullpicture.com/support/documentation/manual-setup-guide-for-the-tracking-tools-manager-module/" target="_blank">' . esc_html__( 'See the video tutorial how to set it up', 'full-picture-analytics-cookie-notice' ) . '</a>',
        )),
    ),
    // IFRAME BLOCKING
    array(
        'section_id'    => 'fupi_cook_iframes',
        'section_title' => esc_html__( 'Control iframes', 'full-picture-analytics-cookie-notice' ),
        'fields'        => array(
            array(
                'type'          => 'multi checkbox',
                'label'         => esc_html__( 'Control iframes from', 'full-picture-analytics-cookie-notice' ),
                'field_id'      => 'iframe_auto_rules',
                'option_arr_id' => $option_arr_id,
                'options'       => array(
                    'youtube' => esc_html__( 'YouTube', 'full-picture-analytics-cookie-notice' ),
                    'vimeo'   => esc_html__( 'Vimeo', 'full-picture-analytics-cookie-notice' ),
                ),
                'popup'         => '<p>' . esc_html__( 'This will automatically make all iframes on your website load, when visitors agree to cookies (or privacy policies of platforms that host the external content).', 'full-picture-analytics-cookie-notice' ) . '</p>
					<h3>' . esc_html__( 'Good to know', 'full-picture-analytics-cookie-notice' ) . '</h3>
					<p>' . sprintf( esc_html__( 'If you do NOT want this module to manage specific iframes, simply wrap them with these HTML comments %1$s<!-- fp_no_mod_start --> your iframe <!-- fp_no_mod_end -->%2$s', 'full-picture-analytics-cookie-notice' ), '<code>', '</code>' ) . '</p>',
            ),
            array(
                'type'           => 'toggle',
                'label'          => esc_html__( 'Control other iframes', 'full-picture-analytics-cookie-notice' ),
                'field_id'       => 'control_other_iframes',
                'el_class'       => 'fupi_condition',
                'el_data_target' => 'fupi_manual_iframes_cond',
                'option_arr_id'  => $option_arr_id,
            ),
            array(
                'type'          => 'r3',
                'label'         => esc_html__( 'What iframes to control', 'full-picture-analytics-cookie-notice' ),
                'field_id'      => 'iframe_manual_rules',
                'option_arr_id' => $option_arr_id,
                'is_repeater'   => true,
                'class'         => 'fupi_sub fupi_fullwidth_tr fupi_disabled fupi_manual_iframes_cond',
                'btns_class'    => 'fupi_push_right',
                'before field'  => '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-automatic-iframe-blocking/" target="_blank">' . esc_html__( 'Learn how to set it up', 'full-picture-analytics-cookie-notice' ) . '</a>',
                'fields'        => array(
                    array(
                        'label'       => esc_html__( 'Name of the service*', 'full-picture-analytics-cookie-notice' ),
                        'type'        => 'text',
                        'placeholder' => esc_html__( 'e.g. YouTube', 'full-picture-analytics-cookie-notice' ),
                        'field_id'    => 'name',
                        'el_class'    => 'fupi_internal_title',
                        'class'       => 'fupi_col_100',
                        'required'    => true,
                    ),
                    array(
                        'type'        => 'text',
                        'label'       => esc_html__( 'Iframe\'s domain URL*', 'full-picture-analytics-cookie-notice' ),
                        'field_id'    => 'iframe_url',
                        'placeholder' => esc_html__( 'e.g. youtube.com', 'full-picture-analytics-cookie-notice' ),
                        'class'       => 'fupi_col_30',
                        'required'    => true,
                    ),
                    array(
                        'type'     => 'url',
                        'label'    => esc_html__( 'Service\'s privacy policy', 'full-picture-analytics-cookie-notice' ),
                        'field_id' => 'privacy_url',
                        'class'    => 'fupi_col_40',
                    ),
                    array(
                        'type'     => 'url',
                        'label'    => esc_html__( 'Placeholder image URL', 'full-picture-analytics-cookie-notice' ),
                        'field_id' => 'image_url',
                        'class'    => 'fupi_col_30',
                    ),
                    array(
                        'type'              => 'label',
                        'label'             => esc_html__( 'What is visitor\'s data used for?', 'full-picture-analytics-cookie-notice' ),
                        'field_id'          => 'types_label',
                        'start_sub_section' => true,
                        'class'             => 'fupi_col_40',
                    ),
                    array(
                        'type'     => 'checkbox',
                        'label'    => esc_html__( 'Statistics', 'full-picture-analytics-cookie-notice' ),
                        'field_id' => 'stats',
                        'class'    => 'fupi_col_20',
                    ),
                    array(
                        'type'     => 'checkbox',
                        'label'    => esc_html__( 'Marketing', 'full-picture-analytics-cookie-notice' ),
                        'field_id' => 'market',
                        'class'    => 'fupi_col_20',
                    ),
                    array(
                        'type'            => 'checkbox',
                        'label'           => esc_html__( 'Personalisation', 'full-picture-analytics-cookie-notice' ),
                        'field_id'        => 'pers',
                        'class'           => 'fupi_col_20',
                        'end_sub_section' => true,
                    )
                ),
            ),
            array(
                'type'          => 'text',
                'label'         => esc_html__( 'Default image placeholder', 'full-picture-analytics-cookie-notice' ),
                'field_id'      => 'iframe_img',
                'placeholder'   => 'https://...',
                'option_arr_id' => $option_arr_id,
                'label_for'     => $option_arr_id . '[iframe_img]',
                'popup'         => '<p>' . esc_html__( 'This placeholder will be shown instead of the iframe if no other placeholder is available. You can enter a link to a png, jpeg or a gif file here.', 'full-picture-analytics-cookie-notice' ) . '</p>',
            ),
            array(
                'type'          => 'text',
                'label'         => esc_html__( 'Text over the placeholder', 'full-picture-analytics-cookie-notice' ),
                'field_id'      => 'iframe_caption_txt',
                'class'         => 'fupi_join',
                'default'       => esc_html__( 'This content is hosted by [[an external source]]. By loading it, you accept its {{privacy terms}}.', 'full-picture-analytics-cookie-notice' ),
                'option_arr_id' => $option_arr_id,
                'label_for'     => $option_arr_id . '[iframe_caption_txt]',
                'under field'   => '<p>' . esc_html__( 'The default text is "This content is hosted by [[an external source]]. By loading it, you accept its {{privacy terms}}."', 'full-picture-analytics-cookie-notice' ) . '</p>' . $priv_policy_url_text,
                'popup'         => '<p>' . esc_html__( '[[an external source]] will be replaced by the iframe\'s domain URL', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'Words wrapped with double curly brackets {{ ... }} will turn into a link to the privacy policy of the iframe\'s source or, if it is hasn\'t been provided, to the privacy policy of your website.', 'full-picture-analytics-cookie-notice' ) . '</p>' . $priv_policy_url_text,
            ),
            array(
                'type'          => 'text',
                'label'         => esc_html__( 'Text of the button which loads the iframe', 'full-picture-analytics-cookie-notice' ),
                'field_id'      => 'iframe_btn_text',
                'class'         => 'fupi_join',
                'default'       => esc_html__( 'Load content', 'full-picture-analytics-cookie-notice' ),
                'under field'   => esc_html__( 'The default text is "Load content".', 'full-picture-analytics-cookie-notice' ),
                'option_arr_id' => $option_arr_id,
                'label_for'     => $option_arr_id . '[iframe_btn_text]',
            ),
            array(
                'type'          => 'toggle',
                'label'         => esc_html__( 'Lazy load all managed iframes', 'full-picture-analytics-cookie-notice' ),
                'field_id'      => 'iframe_lazy',
                'class'         => 'fupi_join',
                'option_arr_id' => $option_arr_id,
                'after field'   => esc_html__( 'Recommended for improved page-load times', 'full-picture-analytics-cookie-notice' ),
            )
        ),
    ),
);