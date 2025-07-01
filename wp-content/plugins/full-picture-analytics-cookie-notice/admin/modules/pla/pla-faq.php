<?php

    $questions = [
        [
            'id' => 'install',
            'title' => esc_html__('How to install Plausible', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'setup',
            'title' => esc_html__('How to track custom data with Plausible?', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'stats',
            'title' => esc_html__('How to display Plausible statistics in WP admin', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'sources_mod',
            'p_id' => 'main',
            'title' => esc_html__('How to improve Plausible\'s data quality', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'testing',
            'title' => esc_html__('How to test and debug Plausible setup', 'full-picture-analytics-cookie-notice' ),
        ],
    ];

    // Do not use IDs inside the wrappers below!
    // The internal HTML will be copied to a popup and IDs will double
    
    $answers = '
    <div id="fupi_install_popup" class="fupi_popup_content">
        <p>' . sprintf( esc_html__('All you need to do to install Plausible is %1$senter this domain in your Plausible admin panel%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://plausible.io/docs/add-website" target="_blank">', '</a>' ) . '</p>
        <p>' . esc_html__('WP Full Picture has already installed all the necessary scripts on your website and they will remain active as long as you have this module enabled.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_stats_popup" class="fupi_popup_content">
        <p>' . esc_html__('To display Plausible statistics in WP Admin, you need to:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__( 'enable the "Reports & Statistics" module,', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'return to this page,', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'go to the new section "Statistics in WP admin",', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'and follow instructions you find there.', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
    </div>

    <div id="fupi_setup_popup" class="fupi_popup_content">

        <p>' . esc_html__('WP Full Picture lets you track 2 types of custom data:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__( 'custom goals - these are user actions like form submissions, clicks on page elements, ecommerce events, etc.,', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'and custom properties - they give more info about the events. These can be names of clicked page elements, product ids, etc.', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>

        <p style="color:#e47d00;">' . esc_html__('Attention! Only users of Plausible Business plan can view properties in the reports.', 'full-picture-analytics-cookie-notice' ) . '</p>

        <h3>' . esc_html__('How to track custom events (goals) and properties', 'full-picture-analytics-cookie-notice' ) . '</h3>

        <p>' . esc_html__('To track custom data, simply fill in the fields on this page and register new event names (goal names) and property names in your Plausible analytics account.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('In the Plausible Analytics panel go to the Site Settings page, choose Goals or Custom Properties and click "Add new". The dropdowns you find there should contain names that you entered while setting up tracking. If you cannot see them, enter them manually or take some actions on your site (in incognito mode of your browser) and refresh the registration page.', 'full-picture-analytics-cookie-notice' ) . '</p>

        <p>' . esc_html__('P.S. If you are registering goals for ecommerce purchases, also enable the "revenue tracking" switch in the goal creation popup.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_sources_mod_popup" class="fupi_popup_content">
        <p>' . esc_html__('WP FP can improve accuracy of data sources reported by Plausible by up to 20%. All you have to do is go to the "General settings" page and fill in the fields in the section "Tracking accuracy tweaks"', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('By doing this, you will:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__('see in your Plausible reports traffic from some popular Android applications (normally reported as Direct traffic),', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('properly assign traffic sources to conversions (often assigned to payment gateways)', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('and make analysing traffic from social networks much easier.', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
    </div>
    
    <div id="fupi_testing_popup" class="fupi_popup_content">
        <p>' . esc_html__('To test if your installation of Plausible works correctly:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__('Open your website in incognito mode', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Turn of ad blockers', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Start browsing your website', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
        <p>' . esc_html__('Your actions should show up in Plausible\'s traffic reports.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>';
?>