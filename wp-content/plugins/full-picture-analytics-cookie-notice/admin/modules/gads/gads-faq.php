<?php

    $questions = [
        [
            'id' => 'install',
            'title' => esc_html__('How to install Google Ads conversion tracking', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-install-google-ads/',
        ],
        [
            'id' => 'dynamic_remarket',
            'title' => esc_html__('How to start using dynamic remarketing in WooCommerce', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'custom_events',
            'title' => esc_html__('How to create powerful retargeting lists with custom events', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-use-advanced-triggers-to-measure-the-quality-of-traffic-and-traffic-sources/',
        ],
        [
            'id' => 'privacy',
            'title' => esc_html__('How to use Google Ads in compliance with privacy laws', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'track_forms',
            'title' => esc_html__('How to track forms the right way', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'testing',
            'title' => esc_html__('How to test and debug your Google Ads setup', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-test-and-debug-google-ads-installation/',
        ],
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    $answers = '

    <div id="fupi_dynamic_remarket_popup" class="fupi_popup_content">
        <p>' . esc_html__('To use dynamic remarketing on your website you need to:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__('Enable "WooCommerce Tracking" module', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . sprintf( esc_html__('Create and send to Google Ads a feed with your products. There are many plugins that can do that. We found %1$sCTX Feed%2$s to be easy to use and reliable.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wordpress.org/plugins/webappick-product-feed-for-woocommerce/" target="_blank">', '</a>' ) . '</li>
        </ol>
        <p>' . esc_html__('The "WooCommerce Tracking" module will automatically collect and send to Google Ads information about products that your visitors viewed, added to cart, had in cart while checking out and purchased.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('This information, combined with product data from the product feed, will let you use Dynamic Ads for remarketing purposes.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p><a href="https://support.google.com/google-ads/answer/3124536?hl=en" targt="_blank" class="button-secondary">' . esc_html__('Read Google\'s guide on how to use it', 'full-picture-analytics-cookie-notice' ) . '</a></p>
    </div>

    <div id="fupi_privacy_popup" class="fupi_popup_content">
        <p>' . esc_html__('To use Google Ads in compliance with privacy laws you need to enable and set up the "Consent Banner" module and (optionally) enable the "Consent Mode" in its settings.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__( 'When consent mode is active Google Ads will work in no-cookies mode until visitors agree to cookies in the Consent Banner.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>
    
    <div id="fupi_track_forms_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'WP FP lets you track form submissions as conversions. There are however 4 different methods of tracking forms. Click the button below to learn which one will work with your forms.' , 'full-picture-analytics-cookie-notice' ) . '</p>
        <p><a class="button-secondary" target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-choose-the-best-way-to-track-form-submissions/">' . esc_html__( 'Choose correct method to track your forms.' , 'full-picture-analytics-cookie-notice' ) . '</a></p>
        </ol>
    </div>';
?>