<?php

    $questions = [
        [
            'id' => 'install',
            'title' => esc_html__('How to install Microsoft Advertising tracking pixel', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-install-microsoft-advertising/',
        ],
        [
            'id' => 'track_user_actions',
            'title' => esc_html__('How to track specific user actions as conversions in Microsoft Advertising', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-track-specific-user-actions-as-conversions-in-microsoft-advertising/'
        ],
        [
            'id' => 'custom_audiences',
            'title' => esc_html__('How to create custom audiences based on user actions', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-create-custom-audiences-based-on-user-actions-in-microsoft-advertising'
        ],
        [
            'id' => 'woo_track_conv',
            'title' => esc_html__('How to track WooCommerce events as conversions', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'woo_define_audience',
            'title' => esc_html__('How to define a custom audience in MS Ads based on WooCommerce events', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'dynamic_remarket',
            'title' => esc_html__('How to start using dynamic remarketing in WooCommerce', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'track_forms',
            'title' => esc_html__('How to track forms the right way', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-to-choose-the-best-way-to-track-form-submissions/',
        ],
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    $answers = '
    <div id="fupi_dynamic_remarket_popup" class="fupi_popup_content">
        <p>' . esc_html__('To use dynamic remarketing on your website you need to:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__('Enable "WooCommerce Tracking" module', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . sprintf( esc_html__('Create and send to Microsoft Advertising a feed with your products. There are many plugins that can do that. We found %1$sCTX Feed%2$s to be easy to use and reliable.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wordpress.org/plugins/webappick-product-feed-for-woocommerce/" target="_blank">', '</a>' ) . '</li>
        </ol>
        <p>' . esc_html__('The "WooCommerce Tracking" module will automatically collect and send to MS Ads information about products that your visitors viewed, added to cart, had in cart while checking out and purchased.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__('This information, combined with product data from the product feed, will let you use Dynamic Ads for remarketing purposes.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_woo_track_conv_popup" class="fupi_popup_content">
        <p>' . esc_html__('To track WooCommerce events as conversions, please follow the steps below.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__( 'Enable and set up the WooCommerce Tracking module.', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'Log in to your Microsoft Advertising panel and create a conversion goal.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'As the "Goal Category" choose either "Purchase", "Add to cart", "Begin Checkout" or "Other".', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'As the "Goal type" choose "Event" .', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Specify a "conversion action":', 'full-picture-analytics-cookie-notice') . '
                <ol>
                    <li>' . esc_html__( '"woo purchase" for "Purchase" event,', 'full-picture-analytics-cookie-notice') . '</li>
                    <li>' . esc_html__( '"woo checkout" for "Begin checkout" event,', 'full-picture-analytics-cookie-notice') . '</li>
                    <li>' . esc_html__( '"woo add to cart" for "Add to cart" event,', 'full-picture-analytics-cookie-notice') . '</li>
                    <li>' . esc_html__( '"woo product view" or "woo list item view" for "Other" event.', 'full-picture-analytics-cookie-notice') . '</li>
                </ol>
            </li>
            <li>' . esc_html__( 'For events "Purchase", "Checkout" and "Add to cart" choose "Conversion value may vary" in the "Revenue" field.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'In the "Set up tagging" section choose "Yes, the UET tag was already installed[...]".', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Your choice in the "Install event tag" does not matter.', 'full-picture-analytics-cookie-notice') . '</li>
        </ol>
    </div>

    <div id="fupi_woo_define_audience_popup" class="fupi_popup_content">
        <p>' . esc_html__('Please follow the steps below to define a custom audience in MS Ads based on WooCommerce events.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
            <li>' . esc_html__( 'Enable and set up the WooCommerce Tracking module', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'Log in to your Microsoft Advertising panel and create an audience.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Choose "dynamic remarketing list" as its type.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Select your UET tag that you created earlier.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'In the field "Who to add to your audience" you can choose either "General Visitors", "Product viewers", "Shopping cart abandoners" and "Past buyers". WP Full Picture sends data to MS Ads for all these groups.', 'full-picture-analytics-cookie-notice') . '</li>
        </ol>
    </div>';
?>