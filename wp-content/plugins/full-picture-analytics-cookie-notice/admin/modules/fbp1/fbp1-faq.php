<?php

    $questions = [
        [
            'id' => 'install_pixel',
            'title' => esc_html__('How to install Meta Pixel', 'full-picture-analytics-cookie-notice' ),
            'url'   => 'https://wpfullpicture.com/support/documentation/how-to-install-meta-pixel/'
        ],
        [
            'id' => 'custom_events',
            'title' => esc_html__('How to create powerful retargeting lists with custom events', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-use-advanced-triggers-to-measure-the-quality-of-traffic-and-traffic-sources/',
        ],
        [
            'id' => 'track_forms',
            'title' => esc_html__('How to track forms the right way', 'full-picture-analytics-cookie-notice' ),
            'url'   => 'https://wpfullpicture.com/support/documentation/how-to-choose-the-best-way-to-track-form-submissions/'
        ],
        [
            'id' => 'testing',
            'title' => esc_html__('How to test and debug your Meta Pixel setup', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/3-ways-to-test-and-debug-meta-pixel-integration/',
        ],
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    // Linked from the settings

    $answers = '

    <div id="fupi_servertrack_info_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'Server tracking will increase the usage of your server. If you have issues with your server performance do not track less important events with Conversion API.', 'full-picture-analytics-cookie-notice') . '</p>
    </div>
    ';
?>