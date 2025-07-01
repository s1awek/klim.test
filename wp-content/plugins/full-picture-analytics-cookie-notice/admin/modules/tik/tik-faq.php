<?php
    
    $questions = [
        [
            'id' => 'install',
            'title' => esc_html__('How to install Tik Tok Pixel', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'track_events',
            'title' => esc_html__('Important information about tracking events (user actions) in TikTok Pixel', 'full-picture-analytics-cookie-notice' ),
            'classes' => 'fupi_warning',
            'url' => 'https://wpfullpicture.com/support/documentation/tracking-events-with-tiktok-pixel/'
        ],
        [
            'id' => 'testing',
            'title' => esc_html__('How to test if TikTok Pixel works correctly', 'full-picture-analytics-cookie-notice' ),
        ],
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    $answers = '

    <div id="fupi_install_popup" class="fupi_popup_content">
        <p>' . sprintf( esc_html__('Please follow %1$sthis guide%2$s to correctly install TikTok Pixel on your site and validate it.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-install-tiktok-pixel/?utm_source=fp_admin&utm_medium=referral&utm_campaign=settings_link">', '</a>' ) . '</p>
    </div>
    
    <div id="fupi_testing_popup" class="fupi_popup_content">
        <h3>' . esc_html__('Choose testing method', 'full-picture-analytics-cookie-notice' ) . '</h3>    
        <p>' . sprintf( esc_html__('To test if TikTok Pixel works without problems we recommend using the %1$sTikTok Pixel Helper extension%2$s for Chrome.', 'full-picture-analytics-cookie-notice' ), '<a href="https://chrome.google.com/webstore/detail/tiktok-pixel-helper/aelgobmabdmlfmiblddjfnjodalhidnn" target="_blank">', '</a>' ) . '</p>
        <p>' . esc_html__('As an alternative (not recommended) you may use the "test events" feature in the Events Manger. To use it log in to ads.tiktok.com > Tools > Events > Web events > Click your Pixel > Click "Test events" tab', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('We have found however, that the data it provides is often inacurate and reports missing values (event names or parameters) even when they are provided.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <h3>' . esc_html__('Next steps', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('After you choose your testing method:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__('Enable the "Force load" option in these settings.', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Open your website in the incognito mode of your browser', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Disable your ad-blocker (if you are using any)', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Browser your website and see how your movements are tracked', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('When you are done, turn off the "Force load" option.', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
    </div>';
?>