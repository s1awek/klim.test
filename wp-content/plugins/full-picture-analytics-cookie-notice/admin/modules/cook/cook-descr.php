<?php

$ret_text = '';

switch( $section_id ){

    case 'fupi_cook_main':
        $ret_text = '<div>
            <p>' . esc_html__( 'Your consent banner is active. It uses Google Consent Mode v2, Microsoft UET Consent Mode and controls the loading of tracking tools installed with WP FP modules. You can set up controlling other tools in the next tab of this page.', 'full-picture-analytics-cookie-notice' ) . '</p>
            <p>' . esc_html__( 'To see the banner in action, test your site in Setup Mode or visit your site from the incognito mode in your browser.', 'full-picture-analytics-cookie-notice' ) . '</p>
            <p style="text-align: center;"><a href="https://wpfullpicture.com/support/documentation/cookie-notice-faq/" class="secondary-button">' . esc_html__( 'Frequently asked questions', 'full-picture-analytics-cookie-notice') . '</a></p>
        </div>';
    break;

    case 'fupi_cook_scriptblock':
        $ret_text = '<p>' . esc_html__('Use these settings if you have any tracking tools installed outside WP FP. This way, your consent banner will load them according to GDPR and other privacy regulations.','full-picture-analytics-cookie-notice') . '</p>
        <p class="fupi_warning_text">' . esc_html__( 'Attention. Before you enable any of the options below, make sure that your caching tool/plugin does NOT combine or minify javascript files. Otherwise, you may break your website (user-facing). ', 'full-picture-analytics-cookie-notice' ) . '</p>';
    break;

    case 'fupi_cook_iframes':
        $ret_text = '<p>' . sprintf( esc_html__('YouTube videos, maps and other content loaded from other websites, can track your visitors. Use iframe blocking to display an image placeholder instead of this consent until visitors agree to tracking %1$sSee example%2$s.','full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/how-iframes-manager-works-and-how-to-set-it-up/" target="_blank">', '</a>' ) . '</p>
        <p class="fupi_warning_text">' . sprintf( esc_html__( 'To manage iframes loaded dynamically (e.g. videos in popups) and those with unique placeholders (like Google maps), please use %1$sthe shortcode method or the HTML method%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-block-iframes-manually/" target="_blank">', '</a>' ) . '</p>';
    break;
};

?>
