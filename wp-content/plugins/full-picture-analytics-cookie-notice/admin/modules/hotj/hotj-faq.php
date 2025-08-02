<?php

    $questions = [
        [
            'id' => 'hj_verify_problem',
            'title' => esc_html__('Why does Hotjar keep telling me there is a problem with my tracking code?', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => "user_identif",
            'title' => esc_html__('How to identify users and what is it for?', 'full-picture-analytics-cookie-notice'),
        ],
    ];

    /*[
        'id' => 'track_custom_meta',
        'title' => esc_html__('How to track data added by other plugins', 'full-picture-analytics-cookie-notice' ),
    ],*/

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    // Linked from the checklist and settings form

    $answers = '

    <div id="fupi_warning_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'Before you start tagging recordings with events you need to know that:', 'full-picture-analytics-cookie-notice') . '</p>
        <ol>
            <li>' . esc_html__( 'Filtering recordings by events is available in Hotjar\'s paid plans.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'One recording can have NO MORE than 50 events associated with it. Events over this limit will be ignored.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'There can be a MAXIMUM of 1000 different events per site.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Pay attention to the number of events your site sends. Read descriptions of the settings below for more information regarding possible increase in the number of events that is sent to Hotjar.', 'full-picture-analytics-cookie-notice') . '</li>
        </ol>
    </div>';

    // Linked only from the checklist

    $answers .= '

    <div id="fupi_hj_verify_popup" class="fupi_popup_content">
        <p>' . esc_html__('To verify HotJar\'s installation:', 'full-picture-analytics-cookie-notice' ). '</p>
        <ol>
            <li>' . esc_html__( 'Enable "Force load" option in the "Loading section"', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Disable ad blocker in your browser (if you use any)', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Run verification in Hotjar', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Disable "Force load" option and re-enable the ad blocker', 'full-picture-analytics-cookie-notice') . '</li>
        </ol>
        <h3>' . esc_html__('Why do I have to do it?', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('Hotjar verifies if its script is installed on your website, by opening it in your browser. If you use ad blockers or you are logged in as an administrator, then Hotjar will not find this script. If you follow the steps above, you ensure that Hotjar finds it.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>';
?>