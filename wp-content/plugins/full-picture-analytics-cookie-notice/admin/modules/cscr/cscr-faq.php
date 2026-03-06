<?php

    $questions = [];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double    

    $answers = '
    <div id="fupi_custom_data_popup" class="fupi_popup_content">
        <p>' . sprintf( esc_html__('In your scripts you can use variables provided by the %1$sfpdata object%2$s or any other JS variable that can be accessed via "window" object.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/what-is-the-fpdata-object-when-and-how-to-use-it/" target="_blank">', ' <span class="dashicons dashicons-external"></span></a>' ) . '</p>
    </div>
    
    <div id="fupi_testing_popup" class="fupi_popup_content">
        <p>' . esc_html__('To test if your integrations load correctly:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__('If you enabled geolocation, make sure that your script is set to load in the country you are in.', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Turn off your ad blocker (if you have any)', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Enable the setup helper in the top menu', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Visit your website', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Open browser console (click the right mouse button anywhere on the page > Choose "Inspect element" > Click "Console" tab in the popup).', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('The console log will be filled with information about loaded scripts.', 'full-picture-analytics-cookie-notice' ) . '</li>
    </div>';
?>