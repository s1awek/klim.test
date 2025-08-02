<?php

    $questions = [];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    $answers = '
    
    <div id="fupi_track_convert_popup" class="fupi_popup_content">
        <p>' . esc_html__('To track user actions and WooCommerce events as conversions, you need to register them in LinkedIn control panel and obtain Conversion IDs for each action you want to track.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('This is how you do it.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . sprintf( esc_html__( 'Log in to %1$sLinkedIn Campaign Manager%2$s', 'full-picture-analytics-cookie-notice'), '<a href="https://www.linkedin.com/campaignmanager/login" target="_blank">', '</a>' ) . '</li>
            <li>' . esc_html__('Choose your advertising account (if it is not chosen yet)', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Click "Analyze" in the left menu', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Choose "Conversion Tracking"', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Choose "Online Conversion"', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('In the 3rd step of configuration click "Event-specific".', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Copy the value of "conversion_id" from the script.', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Click the blue "Create" button', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Paste the conversion ID that you previously copied into one of the fields below', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
        <p>' . esc_html__('Attention! Every field where you paste the "Conversion ID" should contain a different ID.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>';
?>