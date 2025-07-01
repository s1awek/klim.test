<?php

    $questions = [
        [
            'id' => 'setup',
            'title' => esc_html__('How to install and set up custom scripts', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-enable-consent-mode-for-google-ads-analytics-and-gtm/',
        ],
        [
            'id' => 'privacy',
            'title' => esc_html__('How to load tracking tools in compliance with privacy laws', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'geo',
            'title' => esc_html__('How to load tracking tools only in specific countries', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'custom_data',
            'title' => esc_html__('How to use variables in scripts', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'testing',
            'title' => esc_html__('How to test if everything works fine', 'full-picture-analytics-cookie-notice' ),
        ]
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double    

    $answers = '
    <div id="fupi_privacy_popup" class="fupi_popup_content">
        <p>' . esc_html__('To load tracking tools in compliance with privacy laws:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__('enable and set up the Consent Banner module,', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('refresh this page. You will see new checkboxes under the "Script" field.', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
        <p> ' . esc_html__('If you don\'t select any checkbox, the script will load with the pageload.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_geo_popup" class="fupi_popup_content">
        <p>' . esc_html__('To load tracking tools only in specific countries:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__('enable the geolocation module,', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('refresh this page. You will see new fields where you will be able to choose where the scripts should load.', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
    </div>

    <div id="fupi_custom_data_popup" class="fupi_popup_content">
        <p>' . esc_html__('To add variables/custom data to custom scripts, you need to make sure that the data is available before the script has triggered.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('The easiest way to ensure that, is to use the data provided by the fpdata JavaScript object. Its contents are updated to reflect user actions.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('Please enter "fpdata" in your browser\'s console to see what data is available and how it changes depending on what you do on the site.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . sprintf( esc_html__('%1$sLearn more about the fpdata object%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/what-is-the-fpdata-object-when-and-how-to-use-it/">', '</a>' ) . '</p>
    </div>
    
    <div id="fupi_testing_popup" class="fupi_popup_content">
        <p>' . esc_html__('To test if your custom scripts are installed correctly, you need to visit your website and simulate situations in which they would be triggered. To do this:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <h3>' . esc_html__('Step 1. Preparation', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('First, you need to make sure, that no ad blocker, VPN or other solution will prevent your custom script from working. To do this:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__('If you have geolocation enabled, make sure that your script is set to load in the country you are in.', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Turn off your VPN (if you use it)', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Open incognito mode in your browser', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Turn off your ad blocker (if you have any)', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Visit your website.', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Agree to all cookies in the consent banner (if you use it).', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
        <h3>' . esc_html__('Step 2. Testing', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('Some scripts make visual changes to the website, so it is easy to see if they work correctly. Other scripts are used to work behind the scenes.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('To see if they get triggered, you need to open your browser console (click the right mouse button anywhere on the page > Choose "Inspect element" > Click "Console" tab in the popup)The console log will be filled with information about loaded scripts', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>';
?>