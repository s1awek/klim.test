<?php

    $questions = [];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double    

    $answers = '
    <div id="fupi_modes_info_popup" class="fupi_popup_content">
        <h3>' . esc_html__( 'Opt-in mode', 'full-picture-analytics-cookie-notice' ) . ' <span>(' . esc_html__('recommended', 'full-picture-analytics-cookie-notice' ) . ')</span></h3>
        <p>' . esc_html__('When someone visits your site, they\'ll see a popup with options to accept or decline tracking. Tracking will start only after they make a choice.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('This mode is accepted in all countries. It complies with GDPR and similar privacy laws.', 'full-picture-analytics-cookie-notice' ) . '</p>
    
        <h3>' . esc_html__( 'Opt-out mode', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('When someone visits your site, they\'ll see a popup with options to accept or decline tracking. Tracking will start as soon as a visitor lands on the website, but they will be able to turn it off by choosing to decline.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('This mode is not compliant with privacy regulations in 60+ countries (including the whole EU). Use it if your website is visited only from countries where it is legal.', 'full-picture-analytics-cookie-notice' ) . '</p>
    
        <h3>' . esc_html__( 'Notification mode', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('When someone visits your site, they\'ll see a popup that informs them that they are tracked. They will not be able to decline it.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('This mode is not compliant with privacy regulations in almost 70+ countries (including the whole EU). Use it if your website is visited only from countries where it is legal.', 'full-picture-analytics-cookie-notice' ) . '</p>
    
        <h3>' . esc_html__( 'Automatic strict mode', 'full-picture-analytics-cookie-notice' ) . ' <span>(' . esc_html__('with geolocation, recommended', 'full-picture-analytics-cookie-notice' ) . ')</span></h3>
        <p>' . esc_html__('To see this mode, you need to enable the Geolocation function in the General Settings.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('The consent banner will work in opt-in, opt-out or notification mode, depending on the location of the visitor.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p class="fupi_warning_text">' . esc_html__('The Strict mode is for sites that use visitors\' data for marketing or collect sensitive data.', 'full-picture-analytics-cookie-notice' ) . '</p>
    
        <h3>' . esc_html__( 'Automatic Lax mode', 'full-picture-analytics-cookie-notice' ) . ' <span>(' . esc_html__('with geolocation, recommended', 'full-picture-analytics-cookie-notice' ) . ')</span></h3>
        <p>' . esc_html__('To see this mode, you need to enable the Geolocation function in the General Settings.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('The consent banner will work in opt-in, opt-out or notification mode, depending on the location of the visitor.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p class="fupi_warning_text">' . esc_html__('The Lax mode is for sites that don\'t use visitors\' data for marketing or collect sensitive data.', 'full-picture-analytics-cookie-notice' ) . '</p>
    
        <h3>' . esc_html__( 'Manual mode', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('To see this mode, you need to enable the Geolocation function in the General Settings.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('Choose this option to manually choose how the banner should work in different countries.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>';
?>

