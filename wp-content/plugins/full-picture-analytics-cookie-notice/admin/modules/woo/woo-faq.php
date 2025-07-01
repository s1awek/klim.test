<?php

    $questions = [
        [
            'id' => 'requirements',
            'title' => esc_html__('Technical requirements to track WooCommerce', 'full-picture-analytics-cookie-notice' ),
        ],
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    $answers = '
    <div id="fupi_requirements_popup" class="fupi_popup_content">
        <p>' . sprintf( esc_html__( 'To track WooCommerce events and data, your theme and plugins need to use standard %1$sWoocommerce hooks and HTML%2$s. Tracking will not work without them.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . '</p>
        <p>' . esc_html__('WP Full Picture uses WooCommerce hooks to output product data in the form that you want (e.g. products with or without tax). WooCommerce HTML classes help WP Full Picture recognize store elements that visitors can interact with.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . sprintf( esc_html__( 'All tracked WooCommerce events can be viewed and debugged in browser\'s console.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/">' . esc_html__( 'Learn how to do it', 'full-picture-analytics-cookie-notice' ) . '</a>.</p>
    </div>
    
    <div id="fupi_adv_tracking_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'Status-Based Order Tracking is an alternative method of tracking purchases. Instead of tracking them on order confirmation pages, orders are tracked when their status changes.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__( 'This method of tracking is recommended for stores that use payment gateways, which do not redirect back to the order confirmation page.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <h3>' . esc_html__('Supported Tools', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__( 'SBOT is supported in Google Analytics and Meta Pixel.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <h3>' . esc_html__('How to enable Status-Based Order Tracking', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <p>' . esc_html__('Visit the settings pages of the Google Analytics and Meta Pixel modules. When you are there, go to the "WooCommerce Tracking" section and follow instructions you find there.', 'full-picture-analytics-cookie-notice' ) . '</p>
        <h3>' . esc_html__('Good to know', 'full-picture-analytics-cookie-notice' ) . '</h3>
        <ol>
            <li>' . esc_html__( 'Purchases are attributed to users and sessions just like with standard tracking.', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'SBOT does not track orders added manually in the WooCommerce admin panel, since they cannot be attributed to any website users.', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>
    </div>';
?>