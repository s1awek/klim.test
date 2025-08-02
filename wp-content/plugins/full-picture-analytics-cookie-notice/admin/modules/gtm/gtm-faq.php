<?php

    $questions = [];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    $answers = '<div id="fupi_howtouseit_popup" class="fupi_popup_content">
        <p>' . esc_html__('We highly recommend that you do not install Google\'s tools (Analytics and Ads) using different methods.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('You should either install them using WP FP, GTM or a different plugin, but do not mix them. For example, do not install Google Analytics with GTM and Ads with WP Full Picture.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('Although WP FP gives you functions that can fix issues caused by such mixing, we do not promise that they will work.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>';
?>