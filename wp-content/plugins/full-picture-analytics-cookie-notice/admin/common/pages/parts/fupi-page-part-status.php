<?php

$descr_start = '<div id="fupi_settings_form">
    <h2>' . esc_html__('GDPR setup info', 'full-picture-analytics-cookie-notice' ) . '</h2>
    <div class="fupi_section_descr fupi_el">
    <p>' . sprintf( esc_html__('Here are your personalised recommendations on how to set up tracking that complies with GDPR. They automatically update when you change settings in WP Full Picture. We recommend you use this info after you %1$sread this guide%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-track-users-according-to-gdpr-and-other-privacy-regulations/" target="_blank">', '</a>' ) . '</p>
    <p>' . esc_html__('Attention. This information does not cover other aspects of GDPR nor regulations similar to GDPR from other countries. For full compliance, we recommend consulting with a legal professional.', 'full-picture-analytics-cookie-notice' ) . '</p>';

$legend = '<ol id="fupi_gdpr_helper_legend">
        <li><span class="dashicons dashicons-lightbulb" style="color:#a7a7a7; font-size: 20px;"></span>' . esc_html__('For your information', 'full-picture-analytics-cookie-notice' ) . '</li>
        <li><span class="dashicons dashicons-flag" style="color:orange; font-size: 20px;"></span>' . esc_html__('Check it', 'full-picture-analytics-cookie-notice' ) . '</li>
        <li><span class="dashicons dashicons-warning" style="color:red; font-size: 20px;"></span>' . esc_html__('Fix it', 'full-picture-analytics-cookie-notice' ) . '</li>
        <li><span class="dashicons dashicons-welcome-write-blog" style="font-size: 20px; color: #6d2974"></span>' . esc_html__('Add to the privacy policy', 'full-picture-analytics-cookie-notice' ) . '</li>
    </ol>';

$descr_end = '</div></div>';

if ( empty( $this->tools['cook'] ) ) {
    echo $descr_start . '<section style="margin-top: 30px;">
        <h3>' . esc_html__('First, check if you need a consent banner', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('Enable the Consent Banner module if your website uses:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol style="font-size: 17px">
            <li>' . esc_html__('tracking tools that store cookies and / or track personal information about your visitors,', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('advertising tools,', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('a live chat, a newsletter app or a CRM which track your visitor\'s behavior as they travel on the website,', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('content from other websites, e.g. videos from YouTube, maps from Google Maps, Twits, Facebook Posts, external forms, social buttons, etc.,', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('WooCommerce or Jetpack plugin with the "Stats" module,', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Google reCaptcha or a similar solution which does not comply with GDPR.', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
    </section>' . $descr_end;
} else {
    echo $descr_start . $legend . $descr_end;
    include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
    $gdpr_checker = new Fupi_compliance_status_checker();
    echo $gdpr_checker->get_html();
}