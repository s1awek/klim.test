<?php

    $questions = [
        [
            'id' => 'usage',
            'title' => esc_html__('How to add traffic & marketing dashboards to WP admin panel?', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'problems',
            'title' => esc_html__('Solutions to common problems', 'full-picture-analytics-cookie-notice' ),
            'classes' => 'fupi_warning'
        ],
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double

    $answers = '

    <div id="fupi_usage_popup" class="fupi_popup_content">
        <p>' . esc_html__('To add a dahsboard to your WP Admin panel, you need to:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__('Create the dashboard in a platform of your choice (we recommend Google Looker Studio or Databox).', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Share the report with all the people who must have access to it', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Generate an "embed code" to the dashboard (it should start and ends with <iframe>)', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Paste that piece of code to a field on this page', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
        <p>' . esc_html__('After you save the changes, you will see a link to "Reports" page in the WP admin menu.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_problems_popup" class="fupi_popup_content">
        <h3>' . esc_html__('If you can\'t see the "Reports" page in the WordPress menu, please make sure that:', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <ol>
            <li>' . esc_html__('you added at least 1 <iframe> code of the dashboard to the form on this page, and gave it a name.', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('the person who wants to access the Reports page is logged in as an administrator OR that their email address or user ID has been provided in the correct fields on this page', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
        <h3>' . esc_html__('I see error messages when I try to view specific reports / dashboards.', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <ol>
            <li>' . esc_html__('Make sure that the person who wants to see the report / dashboard is logged in to the platform that provides that dashboard, e.g. Looker Studio', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Make sure that the person who wants to see the report / dashboard has access rights to that dashboard.', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Make sure that your security plugin does not block the source of this report / dashboard through a Content Security Policy. If it does, please make exceptions for the domain of that iframe.', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
    </div>';
?>