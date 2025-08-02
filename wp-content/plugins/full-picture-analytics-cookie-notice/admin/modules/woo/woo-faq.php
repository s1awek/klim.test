<?php

    $questions = [];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    $answers = '
    
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