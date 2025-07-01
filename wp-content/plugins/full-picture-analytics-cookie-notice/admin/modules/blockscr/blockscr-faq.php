<?php

// $siteURL = get_bloginfo('url') . '/';
$main_opts = get_option('fupi_main');
$magic_keyword = ! empty ( $main_opts['magic_keyword'] ) ? $main_opts['magic_keyword'] : 'tracking';
$disabled_roles = ! empty ( $main_opts['disable_for_roles'] ) ? join( ', ', $main_opts['disable_for_roles'] ) : 'administrator';

$questions = [
    [
        'id' => 'about',
        'title' => esc_html__('What does the Tracking Tools Manager do?', 'full-picture-analytics-cookie-notice' ),
    ],
    [
        'id' => 'when_to_use',
        'title' => esc_html__('When should I use the Tracking Tools Manager?', 'full-picture-analytics-cookie-notice' ),
    ],
    [
        'id' => 'setup',
        'title' => esc_html__('How to set it up', 'full-picture-analytics-cookie-notice' ),
    ],
    [
        'id' => 'geo',
        'title' => esc_html__('How to load tracking tools only in specific countries', 'full-picture-analytics-cookie-notice' ),
    ],
    [
        'id' => 'privacy',
        'title' => esc_html__('How to make tracking tools comply with privacy laws', 'full-picture-analytics-cookie-notice' ),
    ],
    [
        'id' => 'track_excl',
        'p_id' => 'main',
        'title' => esc_html__('How to make sure that the managed tools will not track specific people?', 'full-picture-analytics-cookie-notice' ),
    ],
    [
        'id' => 'test',
        'title' => esc_html__('How to check if everything works?', 'full-picture-analytics-cookie-notice' ),
    ]
];

// Do not use IDs below!
// The code will be copied to a popup and IDs will double

$answers = '
<div id="fupi_about_popup" class="fupi_popup_content">
    <p>' . esc_html__('The Tracking Tools Manager controls tracking tools installed with other plugins or added directly to HTML of your site. Controlled tools:', 'full-picture-analytics-cookie-notice' ) . '</p>
    <ol>
        <li>' . esc_html__( 'load according to the settings in the Consent Banner module', 'full-picture-analytics-cookie-notice' ) . '</li>
        <li>' . esc_html__( 'load only in specific countries (Geolocation module must be enabled)', 'full-picture-analytics-cookie-notice' ) . '</li>
        <li>' . esc_html__( 'do not track users specified in the General Settings page', 'full-picture-analytics-cookie-notice' ) . ' <button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_track_excl_popup">' . esc_html__("Learn more" ,'full-picture-analytics-cookie-notice' ) . '</button></li>
        <li>' . esc_html__( 'do not track pages that are not viewed, e.g. opened in tabs that were never opened', 'full-picture-analytics-cookie-notice' ) . '</li>
    </ol>
    <p>' . sprintf( esc_html__( '%1$sLearn more about this module%2$s', 'full-picture-analytics-cookie-notice'), '<a target="_blank" href="https://wpfullpicture.com/module/tracking-tools-manager/" class="button-primary">', '</a>' ) . '</p>
</div>

<div id="fupi_when_to_use_popup" class="fupi_popup_content">
    <p>' . esc_html__('You should use the Tracking Tools Manager to control tools that track personal information about your users and/or information that can identify them.', 'full-picture-analytics-cookie-notice' ) . '</p>
    <p>' . esc_html__('This means, most traffic analytics tools, practically all marketing tools and chat applications, like Facebook Messenger.', 'full-picture-analytics-cookie-notice' ) . '</p>
    <p>' . esc_html__('If you are not sure if your tool tracks these information, please refer to their documentation or contact them directly.', 'full-picture-analytics-cookie-notice' ) . '</p>
</div>

<div id="fupi_setup_popup" class="fupi_popup_content">
    <p>' . esc_html__( 'You can set up the Tracking Tools Manager in 2 ways', 'full-picture-analytics-cookie-notice' ) . '</p>
    <ol>
        <li>' . esc_html__( 'Automatically - simply choose what tools you are using to improve them with 1 click', 'full-picture-analytics-cookie-notice' ) . '</li>
        <li>' . esc_html__( 'Manually - by filling in the form with information about the scripts you want to manage', 'full-picture-analytics-cookie-notice' ) . '</li>
    </ol>
    <h3>' . esc_html__( 'Manual setup', 'full-picture-analytics-cookie-notice' ) . '</h3>
    <p>' . esc_html__( 'Here is a guide on how to manually set up the management of tracking tools.', 'full-picture-analytics-cookie-notice' ) . ' <a href="https://wpfullpicture.com/support/documentation/manual-setup-guide-for-the-tracking-tools-manager-module/?utm_source=fp_admin&utm_medium=referral&utm_campaign=documentation_link" target="_blank">' . esc_html__( 'Read the article' , 'full-picture-analytics-cookie-notice' ) . ' <span class="dashicons dashicons-external"></span></a></p>
</div>

<div id="fupi_geo_popup" class="fupi_popup_content">
    <p>' . esc_html__('You can load tracking tools only in specific countries only when you use the manual setup method and have the Geolocation module enabled. You will then see new location fields in the manual setup section.', 'full-picture-analytics-cookie-notice' ) . '</p>
</div>

<div id="fupi_privacy_popup" class="fupi_popup_content">
    <p>' . esc_html__('To use tracking tools in compliance with privacy laws, you must enable the "Consent Banner" module.', 'full-picture-analytics-cookie-notice' ) . '</p>
    <p>' . esc_html__('Automatically managed tools will follow your consent banner rules out-of-the-box.', 'full-picture-analytics-cookie-notice' ) . '</p>
    <p>' . esc_html__('You will also see new fields in the "Manual setup" sections. Provide there information about the type of data that the managed tools collect.', 'full-picture-analytics-cookie-notice' ) . '</p>
    <p>' . esc_html__('This information will be used by the consent banner to load these tools according to privacy laws.', 'full-picture-analytics-cookie-notice' ) . '</p>
</div>

<div id="fupi_track_excl_popup" class="fupi_popup_content">
    <p>' . esc_html__('To make sure that neither you nor your employees are tracked, you can exclude them from tracking in 2 places:', 'full-picture-analytics-cookie-notice' ) . '</p>
    <ol>
        <li>' . esc_html__( 'In the settings of the plugin you manage with WP Full Picture, e.g. MonsterInsights, etc.', 'full-picture-analytics-cookie-notice' ) . '</li>
        <li>' . esc_html__( 'On the "General Settings" page of WP Full Picture (recommend).', 'full-picture-analytics-cookie-notice' ) . '</li>
    </ol>
    <p>' . esc_html__( 'We strongly recommend, however, that you only exclude users with WP Full Picture settings. This is because exclusions set up in both of these places combine.', 'full-picture-analytics-cookie-notice' ) . '</p>
    <p>' . esc_html__( 'This means that, for example, if your tracking plugin excludes users with roles "Administrator" and "Editor" and you set up WP Full Picture to exclude users with roles of "Administrators" and "Shop editors", then users with ANY of these 3 roles will not be tracked.', 'full-picture-analytics-cookie-notice' ) . '</p>
    <h3>' . esc_html__( 'Current WP Full Picture tracking exclusion rules', 'full-picture-analytics-cookie-notice' ) . '</h3>
    <p>' . esc_html__('These are the users who are not tracked according to the current WP Full Picture setup:', 'full-picture-analytics-cookie-notice' ) . '</p>
    <ol>
        <li>' . esc_html__( 'Logged in users with roles: ', 'full-picture-analytics-cookie-notice' ) . $disabled_roles .' </li>
        <li>' . esc_html__( 'Anyone who clicks this special link: ', 'full-picture-analytics-cookie-notice' ) . '<a href="' . get_bloginfo('url') . '/?' . $magic_keyword . '=off"><strong>' . get_bloginfo('url') . '/?' . $magic_keyword . '=off</strong></a>. ' . esc_html__( 'After you click it, you should see an icon in the bottom left corner that indicates that you are not being tracked. This works no matter whether you are logged in or not. You can send it to your employees too.', 'full-picture-analytics-cookie-notice' ) . '</li>
    </ol>
    <p>' . sprintf( esc_html__('Go to the "General Settings" page if you want to change it or %1$slearn more%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-exclude-users-from-tracking/" target="_blank">', '</a>' ). '</p>
</div>

<div id="fupi_test_popup" class="fupi_popup_content">
    <p>' . esc_html__('To check if WP FP properly controls the tools you specified, you need to:', 'full-picture-analytics-cookie-notice' ) . '</p>
    <ol>
        <li>' . esc_html__('open your website in incognito mode in your browser and turn off ad blockers (some of them may block the tools you are trying to control with WP FP).', 'full-picture-analytics-cookie-notice' ) . '</li>
        <li>' . esc_html__('open your browser console (click right mouse button anywhere on the page, choose "inspect element" and click "console" in the panel that will pop up)', 'full-picture-analytics-cookie-notice' ) . '</li>
        <li>' . esc_html__('write in the console "fpdata.loaded" and look for the Script IDs that you put in the settings fields on this page.', 'full-picture-analytics-cookie-notice' ) . '</li>
        <li>' . esc_html__('if you have a consent banner, agree to cookies and check the value of "fpdata.loaded" again. Your script IDs should be there.', 'full-picture-analytics-cookie-notice' ) . '</li>
    </ol>
    <p>' . esc_html__('What to do if they are not?', 'full-picture-analytics-cookie-notice' ) . '</p>
    <ol>
        <li>' . esc_html__('If you use a geolocation module, make sure that the controlled tool is set to load in your country and turn off your VPN (if you are using one)', 'full-picture-analytics-cookie-notice' ) . '</li>
        <li>' . esc_html__('If the tool that you want to load was installed with a plugin, go to its settings and check if there are any conflicting traffic exclusions there.', 'full-picture-analytics-cookie-notice' ) . '</li>
    </ol>
</div>';
?>