<?php

$ret_text = '';

switch( $section_id ){

    case 'fupi_cook_main':
        $ret_text = '<div>
            <p>' . esc_html__( 'Your consent banner is active. It uses Google Consent Mode v2, Microsoft UET Consent Mode and controls the loading of tracking tools installed with WP FP modules. You can set up controlling other tools in the next tab of this page.', 'full-picture-analytics-cookie-notice' ) . '</p>
            <p>' . esc_html__( 'To see the banner in action, use the Setup Helper or visit your site from the incognito mode in your browser.', 'full-picture-analytics-cookie-notice' ) . '</p>
            <p style="text-align: center;"><a href="https://wpfullpicture.com/support/documentation/cookie-notice-faq/" class="secondary-button">' . esc_html__( 'Frequently asked questions', 'full-picture-analytics-cookie-notice') . '</a></p>
        </div>';
    break;

    case 'fupi_cook_scriptblock':
        $ret_text = '<p>' . esc_html__('The banner controls the loading of all tracking tools installed with WP FP\'s modules as well as Google Analytics and Ads, installed with other plugins. Use options below to control other tools that you use  on this site.','full-picture-analytics-cookie-notice') . '</p>
        <h3>' . esc_html__('Attention','full-picture-analytics-cookie-notice') . '</h3>
        <p class="fupi_warning_text">' . esc_html__( 'This banner uses Google Consent Mode v2 and Microsoft UET Consent Mode. Please disable these modes in any other plugins that you have on this site to avoid conflicts.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p class="fupi_warning_text">' . esc_html__( 'Make sure your caching tool/plugin does NOT combine JavaScript files. Otherwise, your website may break.', 'full-picture-analytics-cookie-notice' ) . '</p>';
    break;

    case 'fupi_cook_iframes':
        $ret_text = '<p>' . sprintf( esc_html__('YouTube videos, maps and other content loaded from other websites, can track your visitors. Use options below to display an image placeholder instead of this content until visitors agree to tracking %1$sSee example%2$s.','full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/how-iframes-manager-works-and-how-to-set-it-up/" target="_blank">', '</a>' ) . '</p>';
    break;
};

?>
