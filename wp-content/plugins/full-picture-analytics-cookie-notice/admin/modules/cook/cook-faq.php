<?php

    $questions = [
        [
            'id' => 'faq',
            'title' => esc_html__('Answers to frequent questions about the consent banner', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/cookie-notice-faq/',
        ],
        [
            'id' => 'impact',
            'title' => esc_html__('What is the impact of using a consent banner on traffic statistics and marketing', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'track_excl',
            'title' => esc_html__('How to make sure that you are not tracked by your own website', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'check_geo',
            'title' => esc_html__('How to check how consent banner behaves in different countries', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'consent_mode_101',
            'title' => esc_html__('How to use consent mode', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-enable-consent-mode-for-google-ads-analytics-and-gtm/',
        ],
        [
            'id' => 'consent_mode_check',
            'title' => esc_html__('How to check if consent mode works', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-check-if-googles-consent-mode-is-properly-installed/',
        ],
        [
            'id' => 'saving_consents',
            'title' => esc_html__('All you need to know about saving records of consents', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/saving-user-consents-in-wp-full-picture/',
        ],
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
        
    $answers = '
    <div id="fupi_impact_popup" class="fupi_popup_content">
        <p>' . esc_html__('Consent banners affect the amount of data collected by tools that track visitors\' personal information and/or information that can identify them.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('After you enable the consent banner, they will start tracking fewer visitors - the difference can be from 20% to even 80% - depending on your users profile and the design of the consent banner you choose. We strongly recommend you place the banner in the central position on the screen to maximize the number of consents.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('Tools that do not use cookies and do not track personal information will not be affected.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_check_geo_popup" class="fupi_popup_content">
        <p>' . esc_html__('WP FP lets you easily test how your consent banner behaves in different countries (when you set it to use one of the modes that use geolocation).', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('To do it, enable geolocation module, visit your webiste in incognito mode and add ?fp_set_country=[Country code] at the end of your website address. For example example.com/?fp_set_country=DE', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_track_excl_popup" class="fupi_popup_content">
        <h3>' . esc_html__('Incorrect', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('Declining tracking in the consent banner. This will only disable tracking tools that do not collect personal information about visitors. All the other ones will still track you.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <h3>' . esc_html__('Correct', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('Exclude specific users and user groups using one of the methods described in the "General Settings" page.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_gdpr_info_popup" class="fupi_popup_content">
        <p style="font-weight: bold">' . esc_html__( 'Under Article 7.1 GDPR, where processing is based on consent, the controller shall be able to demonstrate that the data subject has consented to processing of his or her personal data.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>';
?>