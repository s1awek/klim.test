<?php

// $folder_path = $is_premium ? $module_id . '__premium_only' : $module_id;
$file_data = apply_filters( 'fupi_' . $module_id . '_get_faq_data', [] );
$popups_html = empty( $file_data['a'] ) ? '' : $popups_html = $file_data['a'];

if ( fupi_fs()->is_not_paying() ) {
    $support_href = 'https://wordpress.org/support/plugin/full-picture-analytics-cookie-notice/';
} else {
    $support_href = 'https://wpfullpicture.com/contact/?utm_source=fp_admin&utm_medium=fp_link';
};

$caching_warning_text = '<p>' . sprintf( esc_html__( '%1$sRead this article%2$s and make sure that everything works as expected.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-fix-issues-with-caching/" target="_blank">', ' <span class="dashicons dashicons-external"></span></a>' ) . '</p>';

if ( defined( 'WP_ROCKET_VERSION' ) ) {
    $caching_warning_text = '<p>' . sprintf( esc_html__('We have detected that you are using WP Rocket. Some of its settings can break WP Full Picture. Please, enable the compatibility mode in %1$sGeneral Settings%2$s > Performance or set up manual exclusions %3$saccording to this article%2$s.','full-picture-analytics-cookie-notice'), '<a href="/wp-admin/admin.php?page=full_picture_main" target="_blank">',' <span class="dashicons dashicons-external"></span></a>', '<a href="https://wpfullpicture.com/support/documentation/how-to-fix-issues-with-caching/" target="_blank">' ) . '<p>';
}

echo $popups_html . '

    <div id="fupi_popup_adv_mode_intro" class="fupi_popup_content" data-style="popup">
        <p>' . esc_html__( 'You can now view all settings in WP Full Picture - basic and advanced. You now also have access to the Google Tag Manager module, the "Custom triggers" module and the Google Tag module (shows automatically when one of Google integration modules is in use).', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_popup_easy_mode_intro" class="fupi_popup_content" data-style="popup">
        <p>' . esc_html__( 'You are now viewing basic settings. All other settings are now hidden, but they are not disabled.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_first_steps_popup" class="fupi_popup_content" data-style="popup">
        <div style="text-align: center;">
            <h3 style="text-align: center; margin: 14px 0 30px; font-size: 24px !important; line-height: 1.1em;">' . esc_html__( 'Hi. Welcome to WP Full Picture.', 'full-picture-analytics-cookie-notice' ) . '</h3>
            <p style="font-size: 18px;">' . esc_html__( 'Here are some things you should know', 'full-picture-analytics-cookie-notice' ) . '</p>
            <button type="button" class="button-primary button-medium fupi_open_popup" data-popup="fupi_first_steps_2_popup" style="margin-top: 16px; font-size: 17px !important;">' . esc_html__( 'Next', 'full-picture-analytics-cookie-notice' ) . '</button>
        </div>
    </div>

    <div id="fupi_first_steps_2_popup" class="fupi_popup_content" data-style="popup">

        <h3><span style="font-weight: 400">1/3</span> ' . esc_html__( 'Some of the settings are hidden', 'full-picture-analytics-cookie-notice' ) . '</h3>
        
        <p>' . esc_html__( 'By default, we only display basic settings and modules.', 'full-picture-analytics-cookie-notice' ) . '</p>
        
        <p>' . esc_html__( 'Click "Show advanced settings" in the top menu to track more data, more user actions and use advanced modules like Google Tag Manager or Custom triggers.', 'full-picture-analytics-cookie-notice' ) . '</p>

        <div style="text-align: center; margin-top: 30px;">
            <button type="button" class="button-primary button-medium fupi_open_popup" data-popup="fupi_first_steps_3_popup">' . esc_html__( 'Next', 'full-picture-analytics-cookie-notice' ) . '</button>
        </div>
    </div>

    <div id="fupi_first_steps_3_popup" class="fupi_popup_content" data-style="popup">

        <h3><span style="font-weight: 400">2/3</span> ' . esc_html__( 'Some settings of caching plugins can break tracking', 'full-picture-analytics-cookie-notice' ) . '</h3>
        ' . $caching_warning_text . '

        <div style="text-align: center; margin-top: 30px;">
            <button type="button" class="button-secondary button-medium fupi_open_popup" data-popup="fupi_first_steps_2_popup">' . esc_html__( 'Previous', 'full-picture-analytics-cookie-notice' ) . '</button> 
            <button type="button" class="button-primary button-medium fupi_open_popup" data-popup="fupi_first_steps_4_popup">' . esc_html__( 'Next', 'full-picture-analytics-cookie-notice' ) . '</button>
        </div>
    </div>

    <div id="fupi_first_steps_4_popup" class="fupi_popup_content" data-style="popup">
        
        <h3><span style="font-weight: 400">3/3</span> ' . esc_html__( 'We have some useful guides for you', 'full-picture-analytics-cookie-notice' ) . '</h3>

        <p>' . esc_html__( 'Click "Guides" in the top menu and see how to set up analytics, make your site GDPR compliant and how to test your setup.', 'full-picture-analytics-cookie-notice' ) . '</p>

        <div style="text-align: center; margin-top: 30px;">
            <button type="button" class="button-secondary button-medium fupi_open_popup" data-popup="fupi_first_steps_3_popup">' . esc_html__( 'Previous', 'full-picture-analytics-cookie-notice' ) . '</button> 
            <button type="button" class="button-primary button-medium fupi_open_popup" data-popup="fupi_first_steps_5_popup">' . esc_html__( 'Next', 'full-picture-analytics-cookie-notice' ) . '</button>
        </div>
    </div>

    <div id="fupi_first_steps_5_popup" class="fupi_popup_content" data-style="popup">
        <div style="text-align: center;">
            <p style="font-size: 18px">' . sprintf( esc_html__( '%1$sAnd that\'s it%2$s. You can open this window at any time by selecting "First steps" from the top menu.', 'full-picture-analytics-cookie-notice' ), '<strong style="font-size: 18px">', '</strong>') . '</p>
            <div style="text-align: center; margin-top: 30px;">
                <button type="button" class="button-primary button-medium fupi_open_popup" data-popup="fupi_first_steps_5_popup">' .esc_html__( 'Close', 'full-picture-analytics-cookie-notice' ) . '</button> 
            </div>
        </div>
    </div>

    <div id="fupi_guides_popup" class="fupi_popup_content">
        <ul class="fupi_to_do_list">
            <li>
                <a href="https://wpfullpicture.com/support/documentation/first-steps-in-full-picture/" target="_blank">
                    <span class="dashicons dashicons-welcome-learn-more"></span>
                    <span class="fupi_sidenav_title">' . esc_html__('Quick start guide', 'full-picture-analytics-cookie-notice') . '</span>
                </a>
            </li>

            <li>
                <a href="https://wpfullpicture.com/support/documentation/how-to-track-users-according-to-gdpr-and-other-privacy-regulations/" target="_blank">
                    <span class="dashicons dashicons-welcome-learn-more"></span>
                    <span class="fupi_sidenav_title">' . esc_html__('How to track your visitors according to privacy laws', 'full-picture-analytics-cookie-notice') . '</span>
                </a>
            </li>

            <li>
                <a href="https://wpfullpicture.com/support/documentation/how-to-translate-consent-banner-in-wpml-polylang/" target="_blank">
                    <span class="dashicons dashicons-welcome-learn-more"></span>
                    <span class="fupi_sidenav_title">' . esc_html__('How to translate the consent banner with WPML and Polylang', 'full-picture-analytics-cookie-notice') . '</span>
                </a>
            </li>

            <li>
                <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/" target="_blank">
                    <span class="dashicons dashicons-welcome-learn-more"></span>
                    <span class="fupi_sidenav_title">' . esc_html__('How to test your setup with the Setup Helper (for advanced users)', 'full-picture-analytics-cookie-notice') . '</span>
                </a>
            </li>
        </ul>
    </div>

    <div id="fupi_help_links_popup" class="fupi_popup_content">
        
        <ul class="fupi_to_do_list">

            <li><a href="https://wpfullpicture.com/support/documentation/?utm_source=fp_admin&utm_medium=fp_link" target="_blank">' . esc_html__('Documentation', 'full-picture-analytics-cookie-notice') . '</a></li>
            
            <li><a href="https://wpfullpicture.com/support/documentation/troubleshooting/?utm_source=fp_admin&utm_medium=fp_link" target="_blank">' . esc_html__('Solutions to common problems', 'full-picture-analytics-cookie-notice') . '</a></li>
            
            <li><a href="' . $support_href . '" target="_blank">' . esc_html__('Support', 'full-picture-analytics-cookie-notice') . '</a></li>
            
            <li><a href="https://wpfullpicture.com/release/wp-full-picture-update-9-4-0/" target="_blank">' . esc_html__('What\'s new in v9.4', 'full-picture-analytics-cookie-notice') . '</a></li>
            
            <li><a href="https://www.youtube.com/@wpfullpicture" target="_blank"><span class="dashicons dashicons-youtube"></span><span class="fupi_sidenav_title">' . esc_html__('Plugin\'s YouTube channel', 'full-picture-analytics-cookie-notice') . '</span></a></li>
            
            <li><a href="https://www.youtube.com/channel/UCHyy-PD_OIV_kebY9HPyPOQ" target="_blank"><span class="dashicons dashicons-youtube"></span><span class="fupi_sidenav_title">' . esc_html__('Additional Youtube channel with reviews', 'full-picture-analytics-cookie-notice') . '</span></a></li>
            
            <li><a href="https://www.facebook.com/groups/onlinegrowthtools" target="_blank"><span class="dashicons dashicons-facebook"></span><span class="fupi_sidenav_title">' . esc_html__('Facebook group', 'full-picture-analytics-cookie-notice') . '</span></a></li>
        </ul>
    </div>

    <div id="fupi_easy_mode_info_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'Advanced settings let you track user actions, custom events, use visitor scoring (to measure the quality of traffic sources), include settings useful for marketers and developers and let you use advanced modules, like Google Tag Manager and Custom triggers.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p class="fupi_warning_text">' . esc_html__( 'Important. When this is off, advanced settings are only hidden, not disabled.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_setup_mode_info_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'Setup helper makes it easy to test configuration of WP Full Picture and its modules. After you enable it, you will see:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__( 'a button to a "testing panel" in the front-end of your site (visible only to logged-in administrators),', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'notifications about the state of your setup (in the browser console and WordPress debug.log file on the server),', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'user meta, post meta and term meta information (in the browser console - accessible via fp_usermeta, fp_postmeta and fp_termmeta variables).', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
        <p style="text-align: center;">' . sprintf( esc_html__('%1$sLearn more%2$s.', 'full-picture-analytics-cookie-notice'), ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/?utm_source=fp_admin&utm_medium=fp_link" target="_blank">', ' <span class="dashicons dashicons-external"></span></a>') . '</p>
    </div>';

?>