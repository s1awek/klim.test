<?php

// $folder_path = $is_premium ? $module_id . '__premium_only' : $module_id;
$file_data = apply_filters( 'fupi_' . $module_id . '_get_faq_data', [] );
$popups_html = empty( $file_data['a'] ) ? '' : $popups_html = $file_data['a'];

echo $popups_html . '

    <div id="fupi_popup_adv_mode_intro" class="fupi_popup_content" data-style="popup">
        <p>' . esc_html__( 'You can now view all settings in WP Full Picture - basic and advanced. You now also have access to the Google Tag Manager module, the "Custom triggers" module and the Google Tag module (shows automatically when one of Google integration modules is in use).', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_popup_easy_mode_intro" class="fupi_popup_content" data-style="popup">
        <p>' . esc_html__( 'You are now viewing basic settings. All other settings are now hidden, but they are not disabled.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_first_steps_popup" class="fupi_popup_content" data-style="popup">
        <h3 style="margin-top: 0;"><span style="font-weight: 400"></span> ' . esc_html__( 'Advanced modules and settings are hidden', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p style="margin-bottom: 0;">' . esc_html__( 'Click "Show advanced settings" in the top menu to track more data, more user actions and use advanced modules like Google Tag Manager or Custom triggers.', 'full-picture-analytics-cookie-notice' ) . '</p>
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
        <p style="text-align: center;">' . sprintf( esc_html__('%1$sLearn more%2$s.', 'full-picture-analytics-cookie-notice'), ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/" target="_blank">', ' <span class="dashicons dashicons-external"></span></a>') . '</p>
    </div>';

?>