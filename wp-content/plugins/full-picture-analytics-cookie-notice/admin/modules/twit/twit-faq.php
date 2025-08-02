<?php

    $questions = [
        [
            'id' => 'track_actions',
            'title' => esc_html__('How to track non-WooCommerce events', 'full-picture-analytics-cookie-notice' ),
        ],
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    $answers = '
    <div id="fupi_install_popup" class="fupi_popup_content">
        <p>' . esc_html__('All you need to do to install X Ads is:', 'full-picture-analytics-cookie-notice' ) . '</p>
        <ol>
			<li>' . esc_html__( 'Login to', 'full-picture-analytics-cookie-notice') . ' <a href="https://ads.twitter.com" target="_blank">ads.twitter.com</a></li>
			<li>' . esc_html__( 'In the top menu click "Tools" > "Events manager"', 'full-picture-analytics-cookie-notice') . '</li>
			<li>' . esc_html__( 'If you have created a Pixel before you\'ll see it on the left. If not, click "Add event source" in the top right corner.', 'full-picture-analytics-cookie-notice') . '</li>
			<li>' . esc_html__( 'Copy Pixel ID from the "Install Pixel Code" page and enter it here, in the required field.', 'full-picture-analytics-cookie-notice') . '</li>
		</ol>
    </div>

    <div id="fupi_track_actions_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'To track events like clicks, downloads or form submissions you need to provide event IDs in the correct form fields. To get them:', 'full-picture-analytics-cookie-notice') . '</p>
        <ol>
            <li>' . esc_html__( 'Log in to ', 'full-picture-analytics-cookie-notice') . '<a href="https://ads.twitter.com" target="_blank">ads.twitter.com</a></li>
            <li>' . esc_html__( 'In the top menu click "Tools" > "Events manager"', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Click "Add events" button.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Give your event a name, e.g. Newsletter signup', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Choose "Lead" for tracking form submisions or "Custom" for everything else.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Set other settings according to your preferences.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'In the next step choose "Define event with code"', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'In the last step click "Tag manager" tab and copy the Event ID.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Paste that ID in the fields in the "Single events" section.', 'full-picture-analytics-cookie-notice') . '</li>
        </ol>
    </div>

    <div id="fupi_woo_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'To track WooCommerce events like purchases or checkouts you need to provide event IDs in the correct form fields. To get them:', 'full-picture-analytics-cookie-notice') . '</p>
        <ol>
            <li>' . esc_html__( 'Log in to ', 'full-picture-analytics-cookie-notice') . '<a href="https://ads.twitter.com" target="_blank">ads.twitter.com</a></li>
            <li>' . esc_html__( 'In the top menu click "Tools" > "Events manager"', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Click "Add events" button.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Give your event a name, e.g. Checkout', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Choose event type (WP Full Picture supports: "Add to cart", "Add to wishlist", "Checkout initiated", "Content view" and "Purchase")', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Set other settings according to your preferences.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'In the next step choose "Define event with code"', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'In the last step click "Tag manager" tab and copy the Event ID.', 'full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__( 'Paste that ID in the field below and save settings.', 'full-picture-analytics-cookie-notice') . '</li>
        </ol>
        <p>' . esc_html__('Please remember, that tracking when products are added to a wishlist requires additional setup of the WooCommerce tracking module.','full-picture-analytics-cookie-notice') . '</p>
    </div>';
?>