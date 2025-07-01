<?php

    $questions = [
        [
            'id' => 'install',
            'title' => esc_html__('How to install Hotjar?', 'full-picture-analytics-cookie-notice' ),
            'url'   => 'https://wpfullpicture.com/support/documentation/how-to-install-hotjar/'
        ],
        [
            'id' => 'hj_verify',
            'title' => esc_html__('How to verify Hotjar\'s installation', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'hj_verify_problem',
            'title' => esc_html__('Why does Hotjar keep telling me there is a problem with my tracking code?', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'privacy',
            'title' => esc_html__('How to use Hotjar in compliance with privacy laws?', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => "warning",
            'classes' => 'fupi_warning',
            'title' => esc_html__('What you should know before you start tagging recordings with events?', 'full-picture-analytics-cookie-notice'),
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
    
    <div id="fupi_user_identif_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'With user identification you will be able to target specific users with polls, widgets and easily search their session recordings in the Hotjar panel.', 'full-picture-analytics-cookie-notice') . '</p>
        <p>' . esc_html__( 'Identification is done with User ID (for logged-in users) or Hotjar ID (for other visitors)', 'full-picture-analytics-cookie-notice') . '</p>
        <p>' . esc_html__( 'To be able to identify users you need to:', 'full-picture-analytics-cookie-notice') . '</p>
        <ol>
            <li style="color: red;">' . esc_html__( 'If your visitors come from a country where they have to consent before they are tracked, then you should enable consent banner in optin or an automatic mode and add to your privacy policy information about sending user information to Hotjar.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Visit', 'full-picture-analytics-cookie-notice') . ' <a href="https://insights.hotjar.com/settings/user-attributes">' . esc_html__( 'this page', 'full-picture-analytics-cookie-notice') . '</a> ' . esc_html__( 'in your Hotjar dashboard.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Select a website where you want to start tracking users (Not available on all plans!)', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Return to this settings page', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Set up options in the "User attributes" section', 'full-picture-analytics-cookie-notice') . '</li>
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
    </div>

    <div id="fupi_hj_verify_problem_popup" class="fupi_popup_content">
        <p>' . esc_html__('HotJar may sometimes show you a notice "There might be an issue with your tracking code. Verify tracking code installation" in your HJ\'s panel.', 'full-picture-analytics-cookie-notice' ). '</p>
        <p>' . esc_html__('We found that this notification shows most often when you reach the limit of recordings in your plan.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('If you followed our installation guide of Hotjar and verified the installation, your recordings should continue to be tracked after the plan\'s limit is reset.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('If this does not happen, please reach out to Hotjar\'s support and report this issue. WP Full Picture keeps loading your HotJar\'s script the same way as when you verified it for the first time, which can be checked at ay time.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_privacy_popup" class="fupi_popup_content">
        <p>' . esc_html__('There are 2 ways to make Hotjar comply with privacy laws.', 'full-picture-analytics-cookie-notice' ) . '</p>
        
        <h3>' . esc_html__('Method #1. With the Consent Banner', 'full-picture-analytics-cookie-notice' ) . '</h3>
        
        <p>' . esc_html__('Advantages: you get higher quality data, can use "user identification" feature and track WooCommerce order IDs (personal information under GDPR).', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('Disadvantages: you get less data because you can track only visitors that agree to cookies.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('To use it, simply enable and set up the Consent Banner module.', 'full-picture-analytics-cookie-notice' ) . '</p>
        
        <h3>' . esc_html__('Method #2. By enabling the Privacy Mode', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('Advantages: you get more data - your visitors are tracked no matter whether they agree to cookies or not.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('Disadvantages: your data is of worse quality, you cannot use the "user identification" feature nor track WooCommerce order IDs.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p><a class="button-secondary" target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-use-hotjar-without-asking-for-consent/">' . esc_html__('Learn how to enable it', 'full-picture-analytics-cookie-notice' ) . '</a></p>
    </div>

    <div id="fupi_warning_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'Before you start tagging recordings with events you need to know that:', 'full-picture-analytics-cookie-notice') . '</p>
        <ol>
            <li>' . esc_html__( 'Filtering recordings by events is available in Hotjar\'s paid plans.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'One recording can have NO MORE than 50 events associated with it. Events over this limit will be ignored.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'There can be a MAXIMUM of 1000 different events per site.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Pay attention to the number of events your site sends. Read descriptions of the settings below for more information regarding possible increase in the number of events that is sent to Hotjar.', 'full-picture-analytics-cookie-notice') . '</li>
        </ol>
    </div>';
?>