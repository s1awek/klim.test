<?php

    $questions = [
        [
            'id' => 'install',
            'title' => esc_html__('How to install Matomo', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-install-matomo-on-premise-and-cloud/',
        ],
        [
            'id' => 'sources_mod',
            'p_id' => 'main',
            'title' => esc_html__('How to improve Matomo\'s data quality', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'track_forms',
            'title' => esc_html__('How to track forms the right way', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'register_dimensions',
            'title' => esc_html__('How to register custom dimensions in Matomo to track custom WP data', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-set-up-custom-dimensions-in-matomo/'
        ],
        [
            'id' => 'privacy',
            'title' => esc_html__('How to use Matomo in compliance with privacy laws', 'full-picture-analytics-cookie-notice' ),
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
    </div>

    <div id="fupi_privacy_popup" class="fupi_popup_content">
        <p>' . esc_html__('There are 4 ways to use Matomo in compliance with privacy laws:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__('Cookieless tracking.', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('Cookieless tracking with WP FP\'s Consent Banner (recommended).', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('With a Matomo\'s own Consent Banner', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('With WP Full Picture\'s Consent Banner', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
        <p>' . esc_html__('Let\'s see what their advantages and disadvantages are.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <h3>' . esc_html__('Cookieless tracking.', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('When cookieless tracking is active, Matomo no longer use tracking cookies to track visitors. The advantages of this are obvious- no need for an annoying Consent Banner, your visitors will not have to agree to tracking (which many do not) and, as a result, you will track more people . The disadvantage is one - the accuracy of data collected by Matomo without cookies will be lower.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('In other words, if you use cookieless tracking you sacrifice data quality for data quantity.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('To enable cookieless tracking simply enable the "privacy mode" in settings.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <h3>' . esc_html__('Cookieless tracking wit WP FP\'s Consent Banner (recommended).', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('This is the recommended way to use Matomo in privacy-compliant way. It provides you with the highest possible data quality and quantity.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p><button type="button" class="button-secondary fupi_open_popup" data-popup="fupi_privacy_mode_popup">' . esc_html__('Read the details.', 'full-picture-analytics-cookie-notice' ) . '</button></p>
        <h3>' . esc_html__('With a Matomo\'s own Consent Banner.', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('This method uses Matomo\'s own Consent Banner which can be enabled in its admin panel. We do not recommend this method if you plan to use WP FP for integrating any other tracking tools. Matomo\'s Consent Banner does not control these tools like WP FP\'s solution.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <h3>' . esc_html__('With a WP FP\'s Consent Banner.', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('This method uses WP FP\'s Consent Banner which can be set up to load Matomo according to privacy regulations in many countries. This notice can also control the loading of other tools integrated with WP FP.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('However, the better solution is to use it along with the privacy mode / cookieless tracking (as described above). This will assure that you get the most data of the highest accuracy.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>
    
    <div id="fupi_sources_mod_popup" class="fupi_popup_content">
        <p>' . esc_html__('WP FP can improve accuracy of data sources reported by Matomo by up to 20%. All you have to do is go to the "General settings" page and fill in the fields in the section "Tracking accuracy tweaks"', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('By doing this, you will:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__('see in your Matomo reports traffic from some popular Android applications (normally reported by Matomo as Direct traffic),', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('properly assign traffic sources to conversions (often assigned to payment gateways)', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__('and make analysing traffic from social networks much easier.', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
    </div>
    
    <div id="fupi_track_forms_popup" class="fupi_popup_content">
        <ol>
            <li style="color: #e47d00">' . esc_html__( 'WP Full Picture does not use Matomo\'s Form Analytics module. It uses its own method of tracking form submissions which, unlike the original method, works for both, cloud and the on-premise installations.' , 'full-picture-analytics-cookie-notice' ) . '</li>
            <li style="color: #e47d00">' . esc_html__( 'There are 4 methods to submit forms and every one of them is tracked differently.' , 'full-picture-analytics-cookie-notice' ) . ' <a target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-choose-the-best-way-to-track-form-submissions/">' . esc_html__( 'Learn how to track forms on your website.' , 'full-picture-analytics-cookie-notice' ) . '</a></li>
        </ol>
    </div>
    
    <!--  Also linked from settings -->
    <div id="fupi_privacy_mode_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'We recommend using privacy mode together with a consent banner in order to increase the number of tracked visitors and collected data.' ,'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__( 'When you enable this mode, then everyone will be tracked in an anonymized way until they agree to tracking - then Matomo will track them normally.' ,'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__( 'For anonymized visits' ,'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__( 'Matomo will not use cookies that can identify visitors' ,'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'Matomo will not track visitors across different devices and browsers' ,'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'WP Full Picture will randomize order IDs (for WooCommerce orders)' ,'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
    </div>';
?>