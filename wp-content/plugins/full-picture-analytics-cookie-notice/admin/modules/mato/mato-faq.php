<?php

    $questions = [
        [
            'id' => 'register_dimensions',
            'title' => esc_html__('How to register custom dimensions in Matomo to track custom WP data', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-set-up-custom-dimensions-in-matomo/'
        ],
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    $answers = '

    <div id="fupi_enable_ecomm_popup" class="fupi_popup_content">
        <p>' . esc_html__('To enable Ecommerce tracking in Matomo:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__('Log in to your Matomo panel.', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Click the "Settings" icon (cog) in the top right corner.', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('From the left menu choose "Websites" > "Manage".', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Click the "Edit" icon (pencil) next to the section with the website.', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Find the "Ecommerce" setting, choose "Ecommerce enabled" and save the settings.', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
    </div>';
?>