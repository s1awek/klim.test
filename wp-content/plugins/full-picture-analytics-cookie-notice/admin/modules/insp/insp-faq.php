<?php

    $questions = [];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double

    $answers = '

    <div id="fupi_install_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'To install Inspectlet, paste your Site ID in the required field. To find your Site ID:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . sprintf( esc_html__('Go to %1$sInspectlet\'s dashboard%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://www.inspectlet.com/dashboard" target="_blank">', '</a>' ) . '</li>
            <li>' . esc_html__('choose your website', 'full-picture-analytics-cookie-notice') . ' </li>
            <li>' . esc_html__('click "Settings" (NOT "Account settings")', 'full-picture-analytics-cookie-notice') . ' </li>
            <li>' . esc_html__('find site ID in the page\'s URL, e.g. ', 'full-picture-analytics-cookie-notice') . ' inspectlet.com/dashboard/site_settings/<strong>SITE_ID</strong></li>
        </ol>
    </div>';
?>