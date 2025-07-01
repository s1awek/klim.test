<?php

    $questions = [
        [
            'id' => 'warnings',
            'title' => esc_html__('Warning. Read this first!', 'full-picture-analytics-cookie-notice' ),
            'classes' => 'fupi_warning',
        ],
        [
            'id' => 'migrate_gtm_to_ga',
            'title' => esc_html__('How to migrate Google Analytics from GTM to WP Full Picture’s module', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-migrate-google-analytics-from-gtm-to-wp-full-pictures-direct-integration-module/',
        ],
        [
            'id' => 'install',
            'title' => esc_html__('How to install GTM', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'sources_mod',
            'p_id' => 'main',
            'title' => esc_html__('How to improve data quality tracked by GTM', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'stop_tracking',
            'title' => esc_html__('How to prevent tracking specific users', 'full-picture-analytics-cookie-notice' ),
        ]
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    $answers = '
    <div id="fupi_warnings_popup" class="fupi_popup_content">
        <p>' . esc_html__('Google Tag Manager module does NOT work like other WP FP modules.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('Some of the functions provided out-of-the-box for other modules, are unavailable for GTM. You will have to reconstruct them using GTM\'s functions. And these are:', 'full-picture-analytics-cookie-notice' ) . '</p>
            <ol>
            <li>' . esc_html__('Tracking exclusions of specific users and user groups', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Loading tracking scripts based on visitor consents', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Preventing tracking pages that are never viewed (e.g. when a user opens multiple pages in tabs but never views them)', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
        <p>' . esc_html__('Please see the next sections of this guide for tips on how to prepare similar functionality in GTM.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('If setting it all up is too complicated for you, please consider using "Custom Scripts" module as an easy-to-use, alternative method of installing custom scripts / tracking tools.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_install_popup" class="fupi_popup_content">
        <p>' . sprintf ( esc_html__('To install Google Tag Manager\'s container on this site, please paste the Container ID in the form. Please %1$sfollow this guide%2$s if you do not know where to find this ID.', 'full-picture-analytics-cookie-notice' ), '<a href="https://www.optimizesmart.com/how-to-get-google-tag-manager-container-id/" target="_blank">','</a>' ) . '</p>
    </div>

    <div id="fupi_sources_mod_popup" class="fupi_popup_content">
        <p>' . esc_html__('WP FP can improve accuracy of data sources reported by all tools installed with GTM by up to 20%. All you have to do is go to the "General settings" page and fill in the fields in the section "Tracking accuracy tweaks"', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('You do not need to take any other actions. All the improved data will be automatically collected by all the tracking tools you install with GTM.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_stop_tracking_popup" class="fupi_popup_content">
        <p>' . esc_html__('To stop tracking users excluded from tracking (set in the General Settings page), we push to the dataLayer a variable "fp_trackCurrentUser". Use it as a requirement to traigger a tag.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>';
?>