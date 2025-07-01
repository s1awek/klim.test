<?php

    $questions = [
        [
            'id' => 'tools',
            'title' => esc_html__('What tools are added to the list?', 'full-picture-analytics-cookie-notice' ),
        ],
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double

    $answers = '

    <div id="fupi_tools_popup" class="fupi_popup_content">
        <p>' . sprintf( esc_html__('The list will contain only tools which %1$strack your visitor\'s personal information%2$s and are:', 'full-picture-analytics-cookie-notice' ), '<strong>', '</strong>' ) . '</p>
        <ol>
            <li>' . esc_html__( 'installed with WP Full Picture\'s modules (except GTM!)', 'full-picture-analytics-cookie-notice') . '</li> 
            <li>' . esc_html__( 'installed with the "Custom Scripts" module', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'managed by the "Tracking Tools Manager" module', 'full-picture-analytics-cookie-notice') . '</li>
        </ol>
        <p style="color: #e47d00">' . esc_html__('Tools installed with GTM need to be added to the list using the fields on this page.', 'full-picture-analytics-cookie-notice' ). '</p>
    </div>';
?>