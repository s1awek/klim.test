<?php

echo '
<div id="fupi_settings_form">
    <h2>' . esc_html__('GDPR setup helper', 'full-picture-analytics-cookie-notice' ) . '</h2>
    <div class="fupi_section_descr fupi_el">
        <p>' . esc_html__('Instructions on this page will help you set up tracking tools in a way that complies with GDPR.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('Attention! Information you find here does not cover all aspects of GDPR compliance. For a comprehensive review, we recommend consulting with a legal professional and scanning the site with the tools that we suggest in the text.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol id="fupi_gdpr_helper_legend">
            <li><span class="dashicons dashicons-yes-alt" style="color:green; font-size: 20px;"></span>' . esc_html__('OK', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li><span class="dashicons dashicons-flag" style="color:orange; font-size: 20px;"></span>' . esc_html__('Check it', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li><span class="dashicons dashicons-warning" style="color:red; font-size: 20px;"></span>' . esc_html__('Fix it', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li><span class="dashicons dashicons-welcome-write-blog" style="font-size: 20px; color: #6d2974"></span>' . esc_html__('Add to the privacy policy', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
    </div>
</div>';

include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';

new Fupi_compliance_status_checker( 'html' );