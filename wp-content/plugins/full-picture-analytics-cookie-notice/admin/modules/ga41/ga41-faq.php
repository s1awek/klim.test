<?php

    $questions = [];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    $answers = '

    <div id="fupi_track_forms_popup" class="fupi_popup_content">
        <p class="fupi_warning_text">' . esc_html__( 'Attention. There are many ways to track forms. Read the information below.', 'full-picture-analytics-cookie-notice') . '</p>
        <p>' . sprintf( esc_html__( 'Standard form tracking in Google Analytics has %1$svery%2$s low tracking accuracy and gives many false-positives. We strongly recommend you disable it and use one of WP Full Picture\'s methods of form tracking. This is how you do it.' , 'full-picture-analytics-cookie-notice' ), '<strong>', '</strong>' ) . '</p>
        <ol>
            <li><a target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-turn-off-automatic-tracking-in-google-analytics-4-enchanced-measurement/?utm_source=fp_admin&utm_medium=fp_link">' . esc_html__( 'Disable GA\'s form tracking', 'full-picture-analytics-cookie-notice' ) . '</a></li>
        <li><a target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-choose-the-best-way-to-track-form-submissions/">' . esc_html__( 'Choose the right method to track your forms.' , 'full-picture-analytics-cookie-notice' ) . '</a></li>
        </ol>
        <p>' . esc_html__( 'If you want to use this method to track form submissions, than you need to make a choice how to send the data to GA.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <h3>' . esc_html__( '"Track every form with a different event" option', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . sprintf( esc_html__( 'When you choose this option, every time someone submits a form specified in the fields below, WP FP will send to GA an event with a name specific for this element. The event names must follow %1$sthese naming rules%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://support.google.com/analytics/answer/13316687?hl=en#zippy=%2Cweb" target="_blank">', '</a>' ) . '</p>
        <p>' . esc_html__( 'This option is recommended if you do not intend to set many event names and/or you are not an advanced GA user.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <h3>' . esc_html__( '"Track as one event with different parameters" option', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__( 'When you choose this option, every time someone submits a form, WP FP will send to your GA event "form_submit".', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__( 'Information about the names of the submitted forms will be sent to GA as event parameters.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . sprintf ( esc_html__( 'To see these parameters / names in your GA reports, you need to %3$sregister a custom dimension in GA%4$s with event parameter %1$ssubmitted_form%2$s and build a custom report.', 'full-picture-analytics-cookie-notice') , ' <span style="background: #fdf3ce;">', '</span>', '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-custom-definitions-in-google-analytics-4/?utm_source=fp_admin&utm_medium=fp_link">', '</a>' ) . '</p>
        <p>' . esc_html__( 'This option is recommended if you want to track clicks on many different elements on the website and you are an advanced GA user.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>
    
    <div id="fupi_mpapi_key_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'To get Measurement Protocol API secret key, go to your Google Analytics account and follow these instructions:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__( 'Click "Admin" in the bottom-left corner of the screen', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'Click "Data Streams"', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'Click the first entry in the table', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'Click "Measurement Protocol API secrets"', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'Click "Create"', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
    </div>
    
    <div id="fupi_howtouseit_popup" class="fupi_popup_content">
        <p>' . esc_html__('We highly recommend that you do not install Google\'s tools (Analytics and Ads) using different methods.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('You should either install them using WP FP, GTM or a different plugin, but do not mix them. For example, do not install Google Analytics with GTM and Ads with WP Full Picture.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>';
?>